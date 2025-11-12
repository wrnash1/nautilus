<?php

namespace App\Services\Reports;

use App\Core\Database;

class ReportService
{
    public function getSalesByDateRange(string $startDate, string $endDate): array
    {
        return Database::fetchAll(
            "SELECT DATE(transaction_date) as date, 
                    COUNT(*) as transaction_count,
                    SUM(subtotal) as subtotal,
                    SUM(tax) as tax,
                    SUM(total) as total,
                    AVG(total) as avg_order_value
             FROM transactions
             WHERE DATE(transaction_date) BETWEEN ? AND ?
             AND status = 'completed'
             GROUP BY DATE(transaction_date)
             ORDER BY date",
            [$startDate, $endDate]
        ) ?? [];
    }
    
    public function getSalesMetrics(string $startDate, string $endDate): array
    {
        $result = Database::fetchOne(
            "SELECT 
                COUNT(*) as total_transactions,
                SUM(total) as total_revenue,
                SUM(subtotal) as total_subtotal,
                SUM(tax) as total_tax,
                SUM(discount) as total_discount,
                AVG(total) as avg_order_value
             FROM transactions
             WHERE DATE(transaction_date) BETWEEN ? AND ?
             AND status = 'completed'",
            [$startDate, $endDate]
        );
        
        return $result ?? [
            'total_transactions' => 0,
            'total_revenue' => 0,
            'total_subtotal' => 0,
            'total_tax' => 0,
            'total_discount' => 0,
            'avg_order_value' => 0
        ];
    }
    
    public function getTopCustomers(int $limit = 20, ?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT 
                    c.id,
                    c.first_name,
                    c.last_name,
                    c.company_name,
                    c.customer_type,
                    COUNT(t.id) as transaction_count,
                    SUM(t.total) as total_spent,
                    AVG(t.total) as avg_order_value,
                    MAX(t.transaction_date) as last_purchase_date
                FROM customers c
                INNER JOIN transactions t ON c.id = t.customer_id
                WHERE t.status = 'completed'";
        
        $params = [];
        if ($startDate && $endDate) {
            $sql .= " AND DATE(t.transaction_date) BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }
        
        $sql .= " GROUP BY c.id
                  ORDER BY total_spent DESC
                  LIMIT ?";
        $params[] = $limit;
        
        return Database::fetchAll($sql, $params) ?? [];
    }
    
    public function getCustomerMetrics(): array
    {
        $result = Database::fetchOne(
            "SELECT 
                COUNT(DISTINCT c.id) as total_customers,
                COUNT(DISTINCT CASE WHEN c.customer_type = 'B2C' THEN c.id END) as b2c_count,
                COUNT(DISTINCT CASE WHEN c.customer_type = 'B2B' THEN c.id END) as b2b_count,
                COUNT(DISTINCT t.customer_id) as customers_with_purchases,
                AVG(customer_totals.total_spent) as avg_customer_value
             FROM customers c
             LEFT JOIN transactions t ON c.id = t.customer_id AND t.status = 'completed'
             LEFT JOIN (
                 SELECT customer_id, SUM(total) as total_spent
                 FROM transactions
                 WHERE status = 'completed'
                 GROUP BY customer_id
             ) customer_totals ON c.id = customer_totals.customer_id
             WHERE c.is_active = 1"
        );
        
        return $result ?? [];
    }
    
    public function getBestSellingProducts(int $limit = 20, ?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT 
                    p.id,
                    p.name,
                    p.sku,
                    p.retail_price,
                    pc.name as category_name,
                    SUM(ti.quantity) as units_sold,
                    SUM(ti.total) as revenue,
                    COUNT(DISTINCT ti.transaction_id) as order_count
                FROM products p
                INNER JOIN transaction_items ti ON p.id = ti.product_id
                INNER JOIN transactions t ON ti.transaction_id = t.id
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                WHERE t.status = 'completed'";
        
        $params = [];
        if ($startDate && $endDate) {
            $sql .= " AND DATE(t.transaction_date) BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }
        
        $sql .= " GROUP BY p.id
                  ORDER BY units_sold DESC
                  LIMIT ?";
        $params[] = $limit;
        
        return Database::fetchAll($sql, $params) ?? [];
    }
    
    public function getRevenueByCategory(?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT 
                    COALESCE(pc.name, 'Uncategorized') as category,
                    SUM(ti.total) as revenue,
                    SUM(ti.quantity) as units_sold,
                    COUNT(DISTINCT t.id) as transaction_count
                FROM transaction_items ti
                INNER JOIN transactions t ON ti.transaction_id = t.id
                LEFT JOIN products p ON ti.product_id = p.id
                LEFT JOIN product_categories pc ON p.category_id = pc.id
                WHERE t.status = 'completed'";
        
        $params = [];
        if ($startDate && $endDate) {
            $sql .= " AND DATE(t.transaction_date) BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }
        
        $sql .= " GROUP BY pc.id
                  ORDER BY revenue DESC";
        
        return Database::fetchAll($sql, $params) ?? [];
    }
    
    public function getPaymentMethodBreakdown(?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT 
                    p.payment_method,
                    COUNT(*) as transaction_count,
                    SUM(p.amount) as total_amount
                FROM payments p
                INNER JOIN transactions t ON p.transaction_id = t.id
                WHERE p.status = 'completed'
                AND t.status = 'completed'";
        
        $params = [];
        if ($startDate && $endDate) {
            $sql .= " AND DATE(t.transaction_date) BETWEEN ? AND ?";
            $params = [$startDate, $endDate];
        }
        
        $sql .= " GROUP BY p.payment_method
                  ORDER BY total_amount DESC";
        
        return Database::fetchAll($sql, $params) ?? [];
    }
}
