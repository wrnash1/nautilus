# Advanced Analytics Dashboard

## Overview

The Advanced Analytics Dashboard provides comprehensive business intelligence and KPI tracking for your dive shop operations. It offers real-time insights into sales, customers, inventory, courses, and rentals.

## Features

### 1. Sales Metrics
- **Revenue Tracking**: Total revenue, daily averages, and growth rates
- **Transaction Analytics**: Transaction counts, average order values
- **Period Comparisons**: Compare current period vs. previous period
- **Growth Calculations**: Automatic calculation of growth percentages
- **Payment Method Breakdown**: Track sales by payment type

### 2. Customer Analytics
- **New Customer Acquisition**: Track new customer signups
- **Repeat Customer Rate**: Measure customer loyalty
- **Customer Lifetime Value (CLV)**: Calculate average customer value
- **Customer Retention Rate**: Monitor how well you retain customers
- **Top Customers**: Identify your best customers by spend

### 3. Product Performance
- **Best Sellers**: Track top-selling products
- **Category Performance**: Revenue breakdown by product category
- **Product Velocity**: Measure how fast products sell
- **Inventory Health**: Monitor stock levels and turnover

### 4. Inventory Metrics
- **Low Stock Alerts**: Get notified of items needing restock
- **Out of Stock Tracking**: Monitor stockouts
- **Inventory Valuation**: Calculate total inventory value (cost and retail)
- **Turnover Ratio**: Measure inventory efficiency (30-day rolling)

### 5. Course & Training Metrics
- **Enrollment Tracking**: Monitor course enrollments
- **Completion Rates**: Track student completion rates
- **Popular Courses**: Identify best-performing courses
- **Course Revenue**: Track revenue from training programs

### 6. Equipment Rental Analytics
- **Rental Count**: Total number of rentals
- **Equipment Utilization**: Measure how often equipment is rented
- **Rental Revenue**: Track income from rentals
- **Average Rental Value**: Calculate per-rental revenue

### 7. Trend Analysis
- **Daily Sales Trends**: Visualize sales over time
- **Trend Direction**: Identify if business is growing or declining
- **Seasonal Patterns**: Understand seasonal fluctuations

### 8. Key Performance Indicators (KPIs)
- Revenue per Day
- Transactions per Day
- Conversion Rate
- Average Order Value
- Gross Profit Margin

## API Endpoints

### Get Complete Dashboard Overview
```
GET /api/analytics/overview?start_date=2025-01-01&end_date=2025-01-31
```

**Response:**
```json
{
  "success": true,
  "data": {
    "sales_metrics": { ... },
    "customer_metrics": { ... },
    "product_metrics": { ... },
    "inventory_metrics": { ... },
    "course_metrics": { ... },
    "rental_metrics": { ... },
    "trends": { ... },
    "performance": { ... }
  },
  "period": {
    "start": "2025-01-01",
    "end": "2025-01-31",
    "days": 30
  }
}
```

### Get Sales Metrics
```
GET /api/analytics/sales?start_date=2025-01-01&end_date=2025-01-31
```

### Get Customer Metrics
```
GET /api/analytics/customers?start_date=2025-01-01&end_date=2025-01-31
```

### Get Product Metrics
```
GET /api/analytics/products?start_date=2025-01-01&end_date=2025-01-31
```

### Get Inventory Metrics
```
GET /api/analytics/inventory
```

### Get Course Metrics
```
GET /api/analytics/courses?start_date=2025-01-01&end_date=2025-01-31
```

### Get Rental Metrics
```
GET /api/analytics/rentals?start_date=2025-01-01&end_date=2025-01-31
```

### Get Trend Analysis
```
GET /api/analytics/trends?start_date=2025-01-01&end_date=2025-01-31
```

### Get KPIs
```
GET /api/analytics/kpis?start_date=2025-01-01&end_date=2025-01-31
```

## Usage Examples

### PHP Example
```php
use App\Services\Analytics\AdvancedDashboardService;

$dashboardService = new AdvancedDashboardService();

// Get overview for last 30 days
$overview = $dashboardService->getDashboardOverview(
    date('Y-m-d', strtotime('-30 days')),
    date('Y-m-d')
);

// Get just sales metrics
$salesMetrics = $dashboardService->getSalesMetrics(
    date('Y-m-01'), // First day of month
    date('Y-m-d')   // Today
);

echo "Total Revenue: $" . number_format($salesMetrics['current']['total_revenue'], 2);
echo "Revenue Growth: " . $salesMetrics['revenue_growth'] . "%";
```

### JavaScript/AJAX Example
```javascript
// Fetch dashboard overview
fetch('/api/analytics/overview?start_date=2025-01-01&end_date=2025-01-31')
  .then(response => response.json())
  .then(data => {
    console.log('Total Revenue:', data.data.sales_metrics.current.total_revenue);
    console.log('New Customers:', data.data.customer_metrics.new_customers);
    console.log('Low Stock Items:', data.data.inventory_metrics.low_stock_count);
  });
```

## Database Tables

### dashboard_metrics_cache
Stores pre-calculated metrics for faster loading.

### business_kpis
Daily snapshot of all key performance indicators.

### sales_trends
Daily sales trends with breakdown by hour and category.

### customer_analytics
Per-customer analytics and segmentation.

### product_analytics
Product performance metrics over time.

## Performance Optimization

The dashboard uses several optimization techniques:

1. **Metrics Caching**: Frequently accessed metrics are cached
2. **Indexed Queries**: All database queries use proper indexes
3. **Aggregation**: Data is pre-aggregated where possible
4. **Lazy Loading**: Metrics are loaded on-demand via API

## Customization

### Adding New Metrics

To add a new metric to the dashboard:

1. Add the calculation method to `AdvancedDashboardService.php`
2. Add an API endpoint in `AnalyticsDashboardController.php`
3. Update the frontend to display the new metric

Example:
```php
// In AdvancedDashboardService.php
public function getCustomMetric(string $startDate, string $endDate): array
{
    $result = Database::fetchOne(
        "SELECT COUNT(*) as count FROM your_table
         WHERE date_column BETWEEN ? AND ?",
        [$startDate, $endDate]
    );

    return [
        'metric_value' => $result['count'] ?? 0
    ];
}
```

### Dashboard Widgets

Users can customize their dashboard by adding, removing, or rearranging widgets. Widget configurations are stored in the `dashboard_widgets` table.

## Best Practices

1. **Regular Monitoring**: Check dashboard daily for anomalies
2. **Set Benchmarks**: Establish baseline KPIs for comparison
3. **Trend Analysis**: Look for patterns over time, not just snapshots
4. **Action Items**: Use metrics to drive business decisions
5. **Data Quality**: Ensure transactions are properly recorded

## Troubleshooting

### Slow Loading
- Check if metrics cache needs refresh
- Verify database indexes are present
- Consider reducing date range

### Missing Data
- Verify transactions have correct status ('completed')
- Check date ranges are correct
- Ensure database migrations are up to date

### Incorrect Calculations
- Verify data integrity in source tables
- Check for timezone issues
- Ensure all transactions are properly categorized

## Support

For issues or questions about the Analytics Dashboard:
1. Check the logs in `storage/logs/`
2. Review database migrations in `database/migrations/057_*.sql`
3. Contact system administrator
