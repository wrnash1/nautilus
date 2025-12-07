<?php

namespace App\Services\Integrations;

use App\Core\Database;
use DateTime;
use Exception;

/**
 * QuickBooks Export Service
 *
 * Handles exporting Nautilus data to QuickBooks Desktop (IIF format)
 * and QuickBooks Online (QBO XML format).
 *
 * Supported Export Types:
 * - Customers
 * - Invoices (Sales Receipts)
 * - Products/Inventory Items
 * - Payments
 *
 * File Formats:
 * - IIF (Intuit Interchange Format) - QuickBooks Desktop
 * - QBO/QBX (XML) - QuickBooks Online
 */
class QuickBooksExportService
{
    private array $config;

    public function __construct()
    {
        // Load QuickBooks configuration from database
        $this->config = $this->loadConfiguration();
    }

    /**
     * Load QuickBooks configuration from database
     */
    private function loadConfiguration(): array
    {
        $config = Database::fetchOne(
            "SELECT config_data FROM integration_configs
             WHERE integration_type = 'quickbooks' AND is_active = TRUE
             ORDER BY created_at DESC LIMIT 1"
        );

        if ($config && !empty($config['config_data'])) {
            return json_decode($config['config_data'], true);
        }

        // Default configuration
        return [
            'company_name' => '',
            'format' => 'iif', // 'iif' or 'qbo'
            'account_mappings' => [
                'revenue_account' => 'Sales',
                'cogs_account' => 'Cost of Goods Sold',
                'inventory_asset_account' => 'Inventory Asset',
                'sales_tax_account' => 'Sales Tax Payable',
                'accounts_receivable' => 'Accounts Receivable',
                'deposit_to_account' => 'Undeposited Funds'
            ],
            'tax_rate' => 8.0,
            'include_customers' => true,
            'include_products' => true,
            'include_invoices' => true
        ];
    }

    /**
     * Save QuickBooks configuration to database
     */
    public function saveConfiguration(array $config): bool
    {
        try {
            // Deactivate existing configs
            Database::query(
                "UPDATE integration_configs
                 SET is_active = FALSE
                 WHERE integration_type = 'quickbooks'"
            );

            // Insert new config
            Database::query(
                "INSERT INTO integration_configs
                 (integration_type, config_data, is_active, created_at, updated_at)
                 VALUES (?, ?, TRUE, NOW(), NOW())",
                ['quickbooks', json_encode($config)]
            );

            $this->config = $config;
            return true;
        } catch (Exception $e) {
            error_log("QuickBooks config save failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Export customers to QuickBooks format
     */
    public function exportCustomers(?DateTime $startDate = null, ?DateTime $endDate = null): array
    {
        $sql = "SELECT
                    id,
                    first_name,
                    last_name,
                    company_name,
                    email,
                    phone,
                    created_at
                FROM customers
                WHERE 1=1";

        $params = [];

        if ($startDate) {
            $sql .= " AND created_at >= ?";
            $params[] = $startDate->format('Y-m-d H:i:s');
        }

        if ($endDate) {
            $sql .= " AND created_at <= ?";
            $params[] = $endDate->format('Y-m-d H:i:s');
        }

        $sql .= " ORDER BY id ASC";

        $customers = Database::fetchAll($sql, $params);

        return $customers ?? [];
    }

    /**
     * Export invoices (sales receipts) to QuickBooks format
     */
    public function exportInvoices(?DateTime $startDate = null, ?DateTime $endDate = null): array
    {
        $sql = "SELECT
                    t.id,
                    t.transaction_number,
                    t.customer_id,
                    c.first_name,
                    c.last_name,
                    c.company_name,
                    t.transaction_date,
                    t.subtotal,
                    t.tax_amount,
                    t.total,
                    t.payment_method,
                    t.payment_status,
                    t.notes
                FROM transactions t
                LEFT JOIN customers c ON t.customer_id = c.id
                WHERE t.transaction_type = 'sale'";

        $params = [];

        if ($startDate) {
            $sql .= " AND t.transaction_date >= ?";
            $params[] = $startDate->format('Y-m-d');
        }

        if ($endDate) {
            $sql .= " AND t.transaction_date <= ?";
            $params[] = $endDate->format('Y-m-d');
        }

        $sql .= " ORDER BY t.transaction_date ASC, t.id ASC";

        $invoices = Database::fetchAll($sql, $params);

        // Get line items for each invoice
        foreach ($invoices as &$invoice) {
            $invoice['items'] = Database::fetchAll(
                "SELECT
                    ti.product_id,
                    p.name as product_name,
                    p.sku,
                    ti.quantity,
                    ti.unit_price,
                    ti.line_total,
                    ti.tax_amount
                FROM transaction_items ti
                LEFT JOIN products p ON ti.product_id = p.id
                WHERE ti.transaction_id = ?
                ORDER BY ti.id ASC",
                [$invoice['id']]
            ) ?? [];
        }

        return $invoices ?? [];
    }

    /**
     * Export products/inventory items to QuickBooks format
     */
    public function exportProducts(?DateTime $startDate = null, ?DateTime $endDate = null): array
    {
        $sql = "SELECT
                    id,
                    name,
                    sku,
                    description,
                    category_id,
                    cost_price,
                    retail_price,
                    stock_quantity,
                    track_inventory,
                    is_active,
                    created_at
                FROM products
                WHERE 1=1";

        $params = [];

        if ($startDate) {
            $sql .= " AND created_at >= ?";
            $params[] = $startDate->format('Y-m-d H:i:s');
        }

        if ($endDate) {
            $sql .= " AND created_at <= ?";
            $params[] = $endDate->format('Y-m-d H:i:s');
        }

        $sql .= " ORDER BY sku ASC";

        $products = Database::fetchAll($sql, $params);

        return $products ?? [];
    }

    /**
     * Generate IIF (Intuit Interchange Format) file
     * Compatible with QuickBooks Desktop
     */
    public function generateIIFFile(?DateTime $startDate = null, ?DateTime $endDate = null): string
    {
        $iifContent = "";

        // Header
        $iifContent .= "!ACCNT\tNAME\tACCNTTYPE\tDESC\n";
        $iifContent .= "!END\n";

        // Export Customers
        if ($this->config['include_customers']) {
            $customers = $this->exportCustomers($startDate, $endDate);

            if (!empty($customers)) {
                $iifContent .= "!CUST\tNAME\tBADDR1\tBADDR2\tBADDR3\tPHONE1\tEMAIL\n";

                foreach ($customers as $customer) {
                    $name = !empty($customer['company_name'])
                        ? $customer['company_name']
                        : $customer['first_name'] . ' ' . $customer['last_name'];

                    $iifContent .= sprintf(
                        "CUST\t%s\t%s\t\t\t%s\t%s\n",
                        $this->escapeIIF($name),
                        $this->escapeIIF($customer['first_name'] . ' ' . $customer['last_name']),
                        $this->escapeIIF($customer['phone'] ?? ''),
                        $this->escapeIIF($customer['email'] ?? '')
                    );
                }

                $iifContent .= "!END\n";
            }
        }

        // Export Products
        if ($this->config['include_products']) {
            $products = $this->exportProducts($startDate, $endDate);

            if (!empty($products)) {
                $iifContent .= "!INVITEM\tNAME\tINVITEMTYPE\tDESC\tPURCHASECOST\tSALESPRICE\tCOGSACCT\tASSETACCT\tINCOMEACCT\n";

                foreach ($products as $product) {
                    $iifContent .= sprintf(
                        "INVITEM\t%s\t%s\t%s\t%s\t%s\t%s\t%s\t%s\n",
                        $this->escapeIIF($product['sku']),
                        $product['track_inventory'] ? 'PART' : 'SERV',
                        $this->escapeIIF($product['name']),
                        number_format($product['cost_price'] ?? 0, 2, '.', ''),
                        number_format($product['retail_price'] ?? 0, 2, '.', ''),
                        $this->escapeIIF($this->config['account_mappings']['cogs_account']),
                        $this->escapeIIF($this->config['account_mappings']['inventory_asset_account']),
                        $this->escapeIIF($this->config['account_mappings']['revenue_account'])
                    );
                }

                $iifContent .= "!END\n";
            }
        }

        // Export Invoices (Sales Receipts)
        if ($this->config['include_invoices']) {
            $invoices = $this->exportInvoices($startDate, $endDate);

            if (!empty($invoices)) {
                $iifContent .= "!TRNS\tTRNSID\tTRNSTYPE\tDATE\tACCNT\tNAME\tAMOUNT\tDOCNUM\tMEMO\n";
                $iifContent .= "!SPL\tSPLID\tTRNSTYPE\tDATE\tACCNT\tNAME\tAMOUNT\tQNTY\tPRICE\tINVITEM\tMEMO\n";
                $iifContent .= "!ENDTRNS\n";

                foreach ($invoices as $invoice) {
                    $customerName = !empty($invoice['company_name'])
                        ? $invoice['company_name']
                        : $invoice['first_name'] . ' ' . $invoice['last_name'];

                    $trnsId = $invoice['id'];
                    $date = date('m/d/Y', strtotime($invoice['transaction_date']));

                    // Transaction header (Sales Receipt)
                    $iifContent .= sprintf(
                        "TRNS\t%d\tSALES RECEIPT\t%s\t%s\t%s\t%s\t%s\t%s\n",
                        $trnsId,
                        $date,
                        $this->escapeIIF($this->config['account_mappings']['deposit_to_account']),
                        $this->escapeIIF($customerName),
                        number_format($invoice['total'], 2, '.', ''),
                        $this->escapeIIF($invoice['transaction_number']),
                        $this->escapeIIF($invoice['notes'] ?? '')
                    );

                    // Line items
                    foreach ($invoice['items'] as $item) {
                        $iifContent .= sprintf(
                            "SPL\t%d\tSALES RECEIPT\t%s\t%s\t%s\t-%s\t%s\t%s\t%s\t\n",
                            $trnsId,
                            $date,
                            $this->escapeIIF($this->config['account_mappings']['revenue_account']),
                            $this->escapeIIF($customerName),
                            number_format($item['line_total'], 2, '.', ''),
                            $item['quantity'],
                            number_format($item['unit_price'], 2, '.', ''),
                            $this->escapeIIF($item['sku'])
                        );
                    }

                    // Sales tax line (if applicable)
                    if ($invoice['tax_amount'] > 0) {
                        $iifContent .= sprintf(
                            "SPL\t%d\tSALES RECEIPT\t%s\t%s\t%s\t-%s\t\t\t\tSales Tax\n",
                            $trnsId,
                            $date,
                            $this->escapeIIF($this->config['account_mappings']['sales_tax_account']),
                            $this->escapeIIF($customerName),
                            number_format($invoice['tax_amount'], 2, '.', '')
                        );
                    }

                    $iifContent .= "ENDTRNS\n";
                }
            }
        }

        return $iifContent;
    }

    /**
     * Generate QBO XML file
     * Compatible with QuickBooks Online
     */
    public function generateQBOFile(?DateTime $startDate = null, ?DateTime $endDate = null): string
    {
        // QBO XML format (simplified version)
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><QBXML/>');
        $xml->addAttribute('version', '13.0');

        $qbxml = $xml->addChild('QBXMLMsgsRq');
        $qbxml->addAttribute('onError', 'stopOnError');

        // Export Customers
        if ($this->config['include_customers']) {
            $customers = $this->exportCustomers($startDate, $endDate);

            foreach ($customers as $customer) {
                $custAddRq = $qbxml->addChild('CustomerAddRq');
                $custAdd = $custAddRq->addChild('CustomerAdd');

                $name = !empty($customer['company_name'])
                    ? $customer['company_name']
                    : $customer['first_name'] . ' ' . $customer['last_name'];

                $custAdd->addChild('Name', htmlspecialchars($name, ENT_XML1, 'UTF-8'));
                $custAdd->addChild('CompanyName', htmlspecialchars($customer['company_name'] ?? '', ENT_XML1, 'UTF-8'));
                $custAdd->addChild('FirstName', htmlspecialchars($customer['first_name'], ENT_XML1, 'UTF-8'));
                $custAdd->addChild('LastName', htmlspecialchars($customer['last_name'], ENT_XML1, 'UTF-8'));

                if (!empty($customer['email'])) {
                    $custAdd->addChild('Email', htmlspecialchars($customer['email'], ENT_XML1, 'UTF-8'));
                }

                if (!empty($customer['phone'])) {
                    $custAdd->addChild('Phone', htmlspecialchars($customer['phone'], ENT_XML1, 'UTF-8'));
                }
            }
        }

        // Export Products (Items)
        if ($this->config['include_products']) {
            $products = $this->exportProducts($startDate, $endDate);

            foreach ($products as $product) {
                $itemAddRq = $qbxml->addChild('ItemInventoryAddRq');
                $itemAdd = $itemAddRq->addChild('ItemInventoryAdd');

                $itemAdd->addChild('Name', htmlspecialchars($product['sku'], ENT_XML1, 'UTF-8'));
                $itemAdd->addChild('SalesDesc', htmlspecialchars($product['name'], ENT_XML1, 'UTF-8'));
                $itemAdd->addChild('SalesPrice', number_format($product['retail_price'] ?? 0, 2, '.', ''));
                $itemAdd->addChild('PurchaseCost', number_format($product['cost_price'] ?? 0, 2, '.', ''));

                if ($product['track_inventory']) {
                    $itemAdd->addChild('QuantityOnHand', $product['stock_quantity']);
                }
            }
        }

        return $xml->asXML();
    }

    /**
     * Export to file and log the export
     */
    public function exportToFile(string $format, ?DateTime $startDate = null, ?DateTime $endDate = null): array
    {
        try {
            $content = '';
            $extension = '';

            if ($format === 'iif') {
                $content = $this->generateIIFFile($startDate, $endDate);
                $extension = 'iif';
            } elseif ($format === 'qbo') {
                $content = $this->generateQBOFile($startDate, $endDate);
                $extension = 'qbo';
            } else {
                throw new Exception("Invalid export format: $format");
            }

            // Generate filename
            $dateStr = date('Y-m-d_His');
            $filename = "quickbooks_export_{$dateStr}.{$extension}";
            $filepath = __DIR__ . '/../../../storage/exports/' . $filename;

            // Ensure directory exists
            if (!is_dir(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }

            // Write file
            file_put_contents($filepath, $content);

            // Log export
            $this->logExport($format, $filename, $startDate, $endDate);

            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => filesize($filepath)
            ];

        } catch (Exception $e) {
            error_log("QuickBooks export failed: " . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Log export to database
     */
    private function logExport(string $format, string $filename, ?DateTime $startDate, ?DateTime $endDate): void
    {
        try {
            Database::query(
                "INSERT INTO export_logs
                 (export_type, format, filename, start_date, end_date, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())",
                [
                    'quickbooks',
                    $format,
                    $filename,
                    $startDate ? $startDate->format('Y-m-d') : null,
                    $endDate ? $endDate->format('Y-m-d') : null
                ]
            );
        } catch (Exception $e) {
            error_log("Failed to log export: " . $e->getMessage());
        }
    }

    /**
     * Get export history
     */
    public function getExportHistory(int $limit = 50): array
    {
        return Database::fetchAll(
            "SELECT * FROM export_logs
             WHERE export_type = 'quickbooks'
             ORDER BY created_at DESC
             LIMIT ?",
            [$limit]
        ) ?? [];
    }

    /**
     * Escape special characters for IIF format
     */
    private function escapeIIF(string $value): string
    {
        // Remove tabs and newlines, truncate long values
        $value = str_replace(["\t", "\n", "\r"], ' ', $value);
        $value = substr($value, 0, 255);
        return $value;
    }

    /**
     * Get configuration
     */
    public function getConfiguration(): array
    {
        return $this->config;
    }
}
