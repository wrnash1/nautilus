<?php

namespace App\Services\Integrations;

use App\Core\Database;

/**
 * Wave Apps Integration Service
 * Syncs transactions, customers, and invoices with Wave Accounting
 * API Docs: https://developer.waveapps.com/hc/en-us/articles/360019968212-API-Reference
 */
class WaveService
{
    private string $apiUrl = 'https://gql.waveapps.com/graphql/public';
    private ?string $accessToken;
    private ?string $businessId;

    public function __construct()
    {
        // Get Wave credentials from settings
        $settings = $this->getWaveSettings();
        $this->accessToken = $settings['wave_access_token'] ?? null;
        $this->businessId = $settings['wave_business_id'] ?? null;
    }

    /**
     * Get Wave settings from database
     */
    private function getWaveSettings(): array
    {
        $results = Database::fetchAll(
            "SELECT setting_key, setting_value FROM settings WHERE category = 'wave'"
        );

        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }

        return $settings;
    }

    /**
     * Make GraphQL request to Wave API
     */
    private function makeRequest(string $query, array $variables = []): ?array
    {
        if (!$this->accessToken || !$this->businessId) {
            throw new \Exception('Wave integration not configured. Please set up Wave credentials in Settings.');
        }

        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json'
        ];

        $data = [
            'query' => $query,
            'variables' => $variables
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \Exception('Wave API request failed: HTTP ' . $httpCode);
        }

        $result = json_decode($response, true);

        if (isset($result['errors'])) {
            throw new \Exception('Wave API error: ' . json_encode($result['errors']));
        }

        return $result['data'] ?? null;
    }

    /**
     * Sync a transaction to Wave as an invoice
     */
    public function syncTransaction(int $transactionId): ?string
    {
        // Get transaction details
        $transaction = Database::fetchOne(
            "SELECT t.*, c.first_name, c.last_name, c.email
             FROM transactions t
             LEFT JOIN customers c ON t.customer_id = c.id
             WHERE t.id = ?",
            [$transactionId]
        );

        if (!$transaction) {
            throw new \Exception('Transaction not found');
        }

        // Get transaction items
        $items = Database::fetchAll(
            "SELECT ti.*, p.name as product_name
             FROM transaction_items ti
             LEFT JOIN products p ON ti.product_id = p.id
             WHERE ti.transaction_id = ?",
            [$transactionId]
        );

        // Check if customer exists in Wave, if not create them
        $waveCustomerId = $this->getOrCreateCustomer(
            $transaction['customer_id'],
            $transaction['first_name'] . ' ' . $transaction['last_name'],
            $transaction['email']
        );

        // Create invoice in Wave
        $invoiceId = $this->createInvoice($transaction, $items, $waveCustomerId);

        // Store Wave invoice ID
        if ($invoiceId) {
            Database::execute(
                "UPDATE transactions SET wave_invoice_id = ? WHERE id = ?",
                [$invoiceId, $transactionId]
            );

            logAudit('wave_sync', 'transaction_synced', $transactionId, [
                'wave_invoice_id' => $invoiceId
            ]);
        }

        return $invoiceId;
    }

    /**
     * Get or create customer in Wave
     */
    private function getOrCreateCustomer(?int $customerId, string $name, ?string $email): ?string
    {
        if (!$customerId) {
            return null; // Walk-in customer
        }

        // Check if customer already synced
        $existing = Database::fetchOne(
            "SELECT wave_customer_id FROM customers WHERE id = ?",
            [$customerId]
        );

        if ($existing && $existing['wave_customer_id']) {
            return $existing['wave_customer_id'];
        }

        // Create customer in Wave
        $query = '
            mutation($input: CustomerCreateInput!) {
                customerCreate(input: $input) {
                    customer {
                        id
                        name
                        email
                    }
                }
            }
        ';

        $variables = [
            'input' => [
                'businessId' => $this->businessId,
                'name' => $name,
                'email' => $email
            ]
        ];

        $result = $this->makeRequest($query, $variables);
        $waveCustomerId = $result['customerCreate']['customer']['id'] ?? null;

        if ($waveCustomerId) {
            // Store Wave customer ID
            Database::execute(
                "UPDATE customers SET wave_customer_id = ? WHERE id = ?",
                [$waveCustomerId, $customerId]
            );
        }

        return $waveCustomerId;
    }

    /**
     * Create invoice in Wave
     */
    private function createInvoice(array $transaction, array $items, ?string $customerId): ?string
    {
        $invoiceItems = [];

        foreach ($items as $item) {
            $invoiceItems[] = [
                'productId' => null, // Wave product ID (optional)
                'description' => $item['description'] ?: $item['product_name'],
                'quantity' => (float)$item['quantity'],
                'unitPrice' => (float)$item['price']
            ];
        }

        $query = '
            mutation($input: InvoiceCreateInput!) {
                invoiceCreate(input: $input) {
                    invoice {
                        id
                        invoiceNumber
                        total
                        status
                    }
                }
            }
        ';

        $variables = [
            'input' => [
                'businessId' => $this->businessId,
                'customerId' => $customerId,
                'invoiceDate' => date('Y-m-d', strtotime($transaction['created_at'])),
                'dueDate' => date('Y-m-d', strtotime($transaction['created_at'])),
                'items' => $invoiceItems,
                'memo' => 'Nautilus POS Transaction #' . $transaction['id']
            ]
        ];

        $result = $this->makeRequest($query, $variables);

        return $result['invoiceCreate']['invoice']['id'] ?? null;
    }

    /**
     * Export transactions to Wave CSV format
     */
    public function exportTransactionsToCSV(string $dateFrom, string $dateTo): string
    {
        $transactions = Database::fetchAll(
            "SELECT t.*, c.first_name, c.last_name,
                    p.payment_method, p.amount as payment_amount
             FROM transactions t
             LEFT JOIN customers c ON t.customer_id = c.id
             LEFT JOIN payments p ON t.id = p.transaction_id
             WHERE DATE(t.created_at) BETWEEN ? AND ?
             AND t.status = 'completed'
             ORDER BY t.created_at",
            [$dateFrom, $dateTo]
        );

        $filename = sys_get_temp_dir() . '/wave_export_' . date('Y-m-d_His') . '.csv';
        $fp = fopen($filename, 'w');

        // Wave CSV format headers
        fputcsv($fp, [
            'Date',
            'Description',
            'Amount',
            'Account',
            'Category',
            'Customer',
            'Notes'
        ]);

        foreach ($transactions as $t) {
            fputcsv($fp, [
                date('Y-m-d', strtotime($t['created_at'])),
                'Sale Transaction #' . $t['id'],
                number_format($t['total'], 2, '.', ''),
                'Sales',
                'Sales Revenue',
                $t['first_name'] ? $t['first_name'] . ' ' . $t['last_name'] : 'Walk-in Customer',
                'Payment: ' . ($t['payment_method'] ?? 'Unknown')
            ]);
        }

        fclose($fp);

        return $filename;
    }

    /**
     * Bulk sync transactions to Wave
     */
    public function bulkSyncTransactions(string $dateFrom, string $dateTo): array
    {
        $transactions = Database::fetchAll(
            "SELECT id FROM transactions
             WHERE DATE(created_at) BETWEEN ? AND ?
             AND status = 'completed'
             AND (wave_invoice_id IS NULL OR wave_invoice_id = '')
             ORDER BY created_at",
            [$dateFrom, $dateTo]
        );

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($transactions as $t) {
            try {
                $this->syncTransaction($t['id']);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = 'Transaction #' . $t['id'] . ': ' . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Test Wave connection
     */
    public function testConnection(): bool
    {
        try {
            $query = '
                query($businessId: ID!) {
                    business(id: $businessId) {
                        id
                        name
                        currency {
                            code
                        }
                    }
                }
            ';

            $variables = ['businessId' => $this->businessId];
            $result = $this->makeRequest($query, $variables);

            return isset($result['business']['id']);

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get Wave business info
     */
    public function getBusinessInfo(): ?array
    {
        $query = '
            query($businessId: ID!) {
                business(id: $businessId) {
                    id
                    name
                    currency {
                        code
                    }
                    timezone
                }
            }
        ';

        $variables = ['businessId' => $this->businessId];
        $result = $this->makeRequest($query, $variables);

        return $result['business'] ?? null;
    }
}
