<?php

namespace App\Controllers\Admin;

use App\Core\Database;

class DashboardController
{
    public function index()
    {
        $metrics = [
            'today_sales' => $this->getTodaySales(),
            'total_customers' => $this->getTotalCustomers(),
            'low_stock_count' => $this->getLowStockCount(),
            'total_products' => $this->getTotalProducts(),
        ];
        
        $recent_transactions = $this->getRecentTransactions(10);
        $sales_chart_data = $this->getSalesChartData(7);
        
        require __DIR__ . '/../../Views/dashboard/index.php';
    }
    
    private function getTodaySales(): float
    {
        $result = Database::fetchOne(
            "SELECT COALESCE(SUM(total), 0) as total 
             FROM transactions 
             WHERE DATE(created_at) = CURDATE() AND status = 'completed'"
        );
        return (float)($result['total'] ?? 0);
    }
    
    private function getTotalCustomers(): int
    {
        $result = Database::fetchOne(
            "SELECT COUNT(*) as count FROM customers WHERE is_active = 1"
        );
        return (int)($result['count'] ?? 0);
    }
    
    private function getLowStockCount(): int
    {
        $result = Database::fetchOne(
            "SELECT COUNT(*) as count FROM products 
             WHERE track_inventory = 1 
             AND stock_quantity <= low_stock_threshold 
             AND is_active = 1"
        );
        return (int)($result['count'] ?? 0);
    }
    
    private function getTotalProducts(): int
    {
        $result = Database::fetchOne(
            "SELECT COUNT(*) as count FROM products WHERE is_active = 1"
        );
        return (int)($result['count'] ?? 0);
    }
    
    private function getRecentTransactions(int $limit): array
    {
        return Database::fetchAll(
            "SELECT t.*, c.first_name, c.last_name
             FROM transactions t
             LEFT JOIN customers c ON t.customer_id = c.id
             WHERE t.status = 'completed'
             ORDER BY t.created_at DESC
             LIMIT ?",
            [$limit]
        ) ?? [];
    }
    
    private function getSalesChartData(int $days): array
    {
        return Database::fetchAll(
            "SELECT DATE(created_at) as date, COALESCE(SUM(total), 0) as total
             FROM transactions
             WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
             AND status = 'completed'
             GROUP BY DATE(created_at)
             ORDER BY date",
            [$days]
        ) ?? [];
    }
    
    public function salesMetrics()
    {
        
    }
    
    public function inventoryStatus()
    {
        
    }
    
    public function upcomingCourses()
    {
        
    }
}
