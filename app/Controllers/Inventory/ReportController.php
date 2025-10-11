<?php

namespace App\Controllers\Inventory;

use App\Models\Product;

class ReportController
{
    public function lowStock()
    {
        if (!hasPermission('products.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }
        
        $products = Product::getLowStock();
        
        require __DIR__ . '/../../Views/reports/low_stock.php';
    }
    
    public function inventory()
    {
        if (!hasPermission('products.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }
        
        $products = Product::getInventoryReport();
        
        require __DIR__ . '/../../Views/reports/inventory.php';
    }
    
    public function exportInventoryCsv()
    {
        if (!hasPermission('products.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/reports/inventory');
        }
        
        $products = Product::getInventoryReport();
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="inventory-report-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        fputcsv($output, [
            'SKU', 'Product Name', 'Category', 'Stock Qty', 'Low Stock Threshold', 
            'Cost Price', 'Retail Price', 'Inventory Value'
        ]);
        
        foreach ($products as $product) {
            fputcsv($output, [
                $product['sku'],
                $product['name'],
                $product['category_name'] ?? '',
                $product['stock_quantity'],
                $product['low_stock_threshold'],
                $product['cost_price'],
                $product['retail_price'],
                $product['inventory_value']
            ]);
        }
        
        fclose($output);
        exit;
    }
}
