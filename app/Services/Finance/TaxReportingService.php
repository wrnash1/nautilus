<?php

namespace App\Services\Finance;

use PDO;

/**
 * Tax Reporting Service
 * Calculate and report sales tax, 1099s, and other tax obligations
 */
class TaxReportingService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Calculate tax for a transaction
     */
    public function calculateTax(array $transactionData): array
    {
        $tenantId = $transactionData['tenant_id'];
        $subtotal = $transactionData['subtotal'];
        $location = $transactionData['location'] ?? 'default';
        $customerId = $transactionData['customer_id'] ?? null;

        // Check if customer is tax exempt
        if ($customerId && $this->isCustomerTaxExempt($customerId, $tenantId)) {
            return [
                'success' => true,
                'taxable_amount' => 0,
                'tax_amount' => 0,
                'tax_rate' => 0,
                'tax_exempt' => true
            ];
        }

        // Get applicable tax jurisdiction
        $jurisdiction = $this->getTaxJurisdiction($tenantId, $location);

        if (!$jurisdiction) {
            // No tax jurisdiction found, default to 0
            return [
                'success' => true,
                'taxable_amount' => $subtotal,
                'tax_amount' => 0,
                'tax_rate' => 0,
                'jurisdiction' => null
            ];
        }

        // Calculate tax
        $taxRate = $jurisdiction['sales_tax_rate'];
        $taxAmount = round($subtotal * $taxRate, 2);

        return [
            'success' => true,
            'taxable_amount' => $subtotal,
            'tax_amount' => $taxAmount,
            'tax_rate' => $taxRate,
            'jurisdiction' => $jurisdiction,
            'tax_exempt' => false
        ];
    }

    /**
     * Record tax transaction
     */
    public function recordTaxTransaction(array $transactionData): array
    {
        $taxCalc = $this->calculateTax($transactionData);

        $stmt = $this->db->prepare("
            INSERT INTO tax_transactions (
                tenant_id, transaction_id, transaction_type, transaction_date,
                customer_id, customer_tax_exempt, subtotal, taxable_amount,
                sales_tax, total_tax, tax_rate_applied, grand_total, reporting_period
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $reportingPeriod = date('Y-m', strtotime($transactionData['transaction_date']));
        $grandTotal = $transactionData['subtotal'] + $taxCalc['tax_amount'];

        $stmt->execute([
            $transactionData['tenant_id'],
            $transactionData['transaction_id'],
            $transactionData['transaction_type'],
            $transactionData['transaction_date'],
            $transactionData['customer_id'] ?? null,
            $taxCalc['tax_exempt'] ? 1 : 0,
            $transactionData['subtotal'],
            $taxCalc['taxable_amount'],
            $taxCalc['tax_amount'],
            $taxCalc['tax_amount'],
            $taxCalc['tax_rate'],
            $grandTotal,
            $reportingPeriod
        ]);

        return [
            'success' => true,
            'tax_transaction_id' => $this->db->lastInsertId(),
            'tax_details' => $taxCalc
        ];
    }

    /**
     * Generate sales tax report for period
     */
    public function generateSalesTaxReport(int $tenantId, string $reportingPeriod): array
    {
        // Get all transactions for the period
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as transaction_count,
                SUM(subtotal) as gross_sales,
                SUM(CASE WHEN customer_tax_exempt = FALSE THEN taxable_amount ELSE 0 END) as taxable_sales,
                SUM(CASE WHEN customer_tax_exempt = TRUE THEN subtotal ELSE 0 END) as exempt_sales,
                SUM(sales_tax) as total_sales_tax,
                SUM(use_tax) as total_use_tax,
                SUM(total_tax) as total_tax_collected
            FROM tax_transactions
            WHERE tenant_id = ?
              AND reporting_period = ?
              AND transaction_type != 'refund'
        ");

        $stmt->execute([$tenantId, $reportingPeriod]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get refunds
        $refundStmt = $this->db->prepare("
            SELECT
                COUNT(*) as refund_count,
                SUM(total_tax) as total_refunded_tax
            FROM tax_transactions
            WHERE tenant_id = ?
              AND reporting_period = ?
              AND transaction_type = 'refund'
        ");

        $refundStmt->execute([$tenantId, $reportingPeriod]);
        $refunds = $refundStmt->fetch(PDO::FETCH_ASSOC);

        // Calculate net tax due
        $netTaxDue = $summary['total_tax_collected'] - ($refunds['total_refunded_tax'] ?? 0);

        return [
            'success' => true,
            'reporting_period' => $reportingPeriod,
            'summary' => [
                'transaction_count' => $summary['transaction_count'],
                'gross_sales' => $summary['gross_sales'],
                'taxable_sales' => $summary['taxable_sales'],
                'exempt_sales' => $summary['exempt_sales'],
                'total_tax_collected' => $summary['total_tax_collected'],
                'total_refunds' => $refunds['total_refunded_tax'] ?? 0,
                'net_tax_due' => $netTaxDue
            ]
        ];
    }

    /**
     * Create tax return for filing
     */
    public function createTaxReturn(int $tenantId, string $returnType, string $reportingPeriod, string $filingFrequency): array
    {
        // Generate report
        $report = $this->generateSalesTaxReport($tenantId, $reportingPeriod);

        if (!$report['success']) {
            return $report;
        }

        // Calculate due date
        $dueDate = $this->calculateDueDate($reportingPeriod, $filingFrequency);

        // Create tax return record
        $stmt = $this->db->prepare("
            INSERT INTO tax_returns (
                tenant_id, return_type, reporting_period, filing_frequency, due_date,
                total_gross_sales, total_taxable_sales, total_exempt_sales,
                total_tax_collected, total_refunds, net_tax_due, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')
        ");

        $stmt->execute([
            $tenantId,
            $returnType,
            $reportingPeriod,
            $filingFrequency,
            $dueDate,
            $report['summary']['gross_sales'],
            $report['summary']['taxable_sales'],
            $report['summary']['exempt_sales'],
            $report['summary']['total_tax_collected'],
            $report['summary']['total_refunds'],
            $report['summary']['net_tax_due']
        ]);

        $returnId = $this->db->lastInsertId();

        // Mark transactions as reported
        $this->db->prepare("
            UPDATE tax_transactions
            SET reported = TRUE,
                reported_at = NOW(),
                tax_return_id = ?
            WHERE tenant_id = ?
              AND reporting_period = ?
              AND reported = FALSE
        ")->execute([$returnId, $tenantId, $reportingPeriod]);

        return [
            'success' => true,
            'tax_return_id' => $returnId,
            'due_date' => $dueDate,
            'summary' => $report['summary']
        ];
    }

    /**
     * Generate 1099 report for contractors
     */
    public function generate1099Report(int $tenantId, int $taxYear): array
    {
        // Get all contractor payments for the year that exceed $600 threshold
        $stmt = $this->db->prepare("
            SELECT
                contractor_name,
                contractor_tin,
                SUM(payment_amount) as total_payments,
                SUM(box_1_nonemployee_comp) as box_1_total,
                SUM(box_4_federal_income_tax) as box_4_total,
                COUNT(*) as payment_count
            FROM contractor_1099_payments
            WHERE tenant_id = ?
              AND tax_year = ?
            GROUP BY contractor_tin
            HAVING total_payments >= 600
            ORDER BY total_payments DESC
        ");

        $stmt->execute([$tenantId, $taxYear]);
        $contractors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalPayments = array_sum(array_column($contractors, 'total_payments'));
        $contractorCount = count($contractors);

        return [
            'success' => true,
            'tax_year' => $taxYear,
            'contractor_count' => $contractorCount,
            'total_payments' => $totalPayments,
            'contractors' => $contractors
        ];
    }

    /**
     * Record contractor payment
     */
    public function recordContractorPayment(array $paymentData): array
    {
        $stmt = $this->db->prepare("
            INSERT INTO contractor_1099_payments (
                tenant_id, contractor_name, contractor_tin, tax_year,
                payment_date, payment_amount, payment_type, payment_description,
                box_1_nonemployee_comp
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $paymentData['tenant_id'],
            $paymentData['contractor_name'],
            $paymentData['contractor_tin'],
            $paymentData['tax_year'],
            $paymentData['payment_date'],
            $paymentData['payment_amount'],
            $paymentData['payment_type'] ?? 'non_employee_compensation',
            $paymentData['payment_description'] ?? null,
            $paymentData['payment_amount'] // Assuming it all goes to box 1
        ]);

        return [
            'success' => true,
            'payment_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Get year-to-date tax summary
     */
    public function getYTDTaxSummary(int $tenantId): array
    {
        $currentYear = date('Y');

        $stmt = $this->db->prepare("
            SELECT
                SUM(subtotal) as ytd_gross_sales,
                SUM(CASE WHEN customer_tax_exempt = FALSE THEN taxable_amount ELSE 0 END) as ytd_taxable_sales,
                SUM(total_tax) as ytd_tax_collected,
                COUNT(DISTINCT reporting_period) as periods_with_sales
            FROM tax_transactions
            WHERE tenant_id = ?
              AND YEAR(transaction_date) = ?
              AND transaction_type != 'refund'
        ");

        $stmt->execute([$tenantId, $currentYear]);
        $ytd = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'year' => $currentYear,
            'ytd_summary' => $ytd
        ];
    }

    /**
     * Get upcoming tax deadlines
     */
    public function getUpcomingDeadlines(int $tenantId, int $daysAhead = 90): array
    {
        $stmt = $this->db->prepare("
            SELECT *
            FROM tax_returns
            WHERE tenant_id = ?
              AND status NOT IN ('filed', 'amended')
              AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
            ORDER BY due_date ASC
        ");

        $stmt->execute([$tenantId, $daysAhead]);
        $deadlines = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'upcoming_deadlines' => $deadlines,
            'count' => count($deadlines)
        ];
    }

    /**
     * Helper: Check if customer is tax exempt
     */
    private function isCustomerTaxExempt(int $customerId, int $tenantId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM tax_exempt_customers
            WHERE customer_id = ?
              AND tenant_id = ?
              AND is_active = TRUE
              AND (expiration_date IS NULL OR expiration_date >= CURDATE())
        ");

        $stmt->execute([$customerId, $tenantId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Helper: Get tax jurisdiction
     */
    private function getTaxJurisdiction(int $tenantId, string $location): ?array
    {
        // Simplified - in production would geocode location and match to jurisdictions
        $stmt = $this->db->prepare("
            SELECT * FROM tax_jurisdictions
            WHERE tenant_id = ?
              AND is_active = TRUE
              AND effective_from <= CURDATE()
              AND (effective_to IS NULL OR effective_to >= CURDATE())
            ORDER BY jurisdiction_type DESC
            LIMIT 1
        ");

        $stmt->execute([$tenantId]);
        $jurisdiction = $stmt->fetch(PDO::FETCH_ASSOC);

        return $jurisdiction ?: null;
    }

    /**
     * Helper: Calculate tax return due date
     */
    private function calculateDueDate(string $reportingPeriod, string $filingFrequency): string
    {
        list($year, $month) = explode('-', $reportingPeriod);

        switch ($filingFrequency) {
            case 'monthly':
                // Due on the 20th of following month
                return date('Y-m-20', strtotime("$year-$month-01 +1 month"));

            case 'quarterly':
                // Due on the last day of the month following the quarter
                $quarterEndMonth = ceil($month / 3) * 3;
                return date('Y-m-t', strtotime("$year-$quarterEndMonth-01 +1 month"));

            case 'annual':
                // Due April 15 of following year
                return ($year + 1) . '-04-15';

            default:
                return date('Y-m-d', strtotime('+30 days'));
        }
    }

    /**
     * Export tax data to CSV
     */
    public function exportTaxDataCSV(int $tenantId, string $reportingPeriod): string
    {
        $stmt = $this->db->prepare("
            SELECT
                transaction_id,
                transaction_date,
                transaction_type,
                customer_name,
                subtotal,
                taxable_amount,
                sales_tax,
                total_tax,
                grand_total,
                customer_tax_exempt
            FROM tax_transactions
            WHERE tenant_id = ?
              AND reporting_period = ?
            ORDER BY transaction_date ASC
        ");

        $stmt->execute([$tenantId, $reportingPeriod]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Generate CSV
        $csv = "Transaction ID,Date,Type,Customer,Subtotal,Taxable Amount,Sales Tax,Total Tax,Grand Total,Tax Exempt\n";

        foreach ($transactions as $txn) {
            $csv .= sprintf(
                "%s,%s,%s,%s,%.2f,%.2f,%.2f,%.2f,%.2f,%s\n",
                $txn['transaction_id'],
                $txn['transaction_date'],
                $txn['transaction_type'],
                $txn['customer_name'] ?? 'N/A',
                $txn['subtotal'],
                $txn['taxable_amount'],
                $txn['sales_tax'],
                $txn['total_tax'],
                $txn['grand_total'],
                $txn['customer_tax_exempt'] ? 'Yes' : 'No'
            );
        }

        return $csv;
    }
}
