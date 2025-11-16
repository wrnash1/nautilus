# Business Intelligence & Analytics Guide

## Overview

The Nautilus Business Intelligence system provides comprehensive analytics, reporting, and data visualization capabilities to help dive shop owners make data-driven decisions. This guide covers dashboards, KPIs, custom reports, customer analytics, and advanced reporting features.

---

## Table of Contents

1. [Dashboards & KPIs](#dashboards--kpis)
2. [Custom Reports](#custom-reports)
3. [Customer Analytics](#customer-analytics)
4. [Revenue Analytics](#revenue-analytics)
5. [Product Analytics](#product-analytics)
6. [Scheduled Reports](#scheduled-reports)
7. [Data Export](#data-export)
8. [API Reference](#api-reference)

---

## Dashboards & KPIs

### Overview

Interactive dashboards provide real-time visibility into your business performance with customizable widgets and key performance indicators (KPIs).

### Pre-Built Dashboards

1. **Executive Overview**
   - Monthly revenue KPI with target tracking
   - New customer acquisition
   - Active bookings count
   - Customer satisfaction score
   - Revenue trend (12-month chart)
   - Revenue by category (pie chart)
   - Top customers table
   - Recent bookings activity

2. **Sales Performance**
   - Daily sales metrics
   - Booking conversion rates
   - Top selling products/courses
   - Sales by employee
   - Sales funnel analysis

3. **Operations Dashboard**
   - Equipment utilization
   - Instructor schedules
   - Inventory alerts
   - Upcoming trips/courses
   - Maintenance reminders

### Creating Custom Dashboards

```php
use App\Services\Analytics\BusinessIntelligenceService;

$bi = new BusinessIntelligenceService($db);

// Get existing dashboard
$dashboard = $bi->getDashboard($dashboardId, $tenantId);

// Dashboard includes:
// - Dashboard configuration
// - All widgets with real-time data
// - Auto-refresh settings
```

### Dashboard Widgets

**Widget Types:**
- **KPI**: Single numeric value with trend indicator
- **Chart**: Bar, line, pie, donut, area, scatter, heatmap
- **Table**: Tabular data with sorting/filtering
- **Gauge**: Progress toward goal
- **Calendar**: Time-based events
- **List**: Simple list view

**Widget Configuration:**
- Data source (report template, custom query, API, static data)
- Visualization type
- Filters and date ranges
- Auto-refresh intervals
- Grid position and size
- Caching settings

### Key Performance Indicators (KPIs)

**Pre-Built KPIs:**

1. **Monthly Revenue** (Financial)
   - Target: $50,000
   - Calculation: SUM of completed bookings
   - Thresholds: Green ≥ $50K, Yellow ≥ $40K, Red < $30K

2. **Customer Acquisition Rate** (Customer)
   - Target: 25 new customers/month
   - Calculation: COUNT of new customers
   - Trend: Compare to previous month

3. **Average Booking Value** (Sales)
   - Target: $500
   - Calculation: AVG booking amount
   - Unit: Currency ($)

4. **Course Completion Rate** (Operational)
   - Target: 95%
   - Calculation: Completed / Total courses
   - Unit: Percentage

5. **Inventory Turnover** (Inventory)
   - Target: 6x per year
   - Calculation: Sales / Avg inventory
   - Unit: Ratio

6. **Customer Retention Rate** (Customer)
   - Target: 75%
   - Calculation: Returning / Total customers
   - Period: Quarterly

### KPI Features

- **Real-time Tracking**: Automatic recalculation
- **Target Setting**: Define goals and thresholds
- **Trend Analysis**: Compare to previous periods
- **Color Coding**: Green/Yellow/Red status indicators
- **Historical Data**: Track performance over time
- **Alerts**: Notifications when thresholds are crossed

---

## Custom Reports

### Report Templates

The system includes 8 pre-built report templates:

1. **Daily Sales Summary**
   - Sales performance overview
   - Transaction breakdown
   - Payment methods
   - Staff performance

2. **Monthly Revenue by Category**
   - Revenue breakdown: courses, retail, rentals, services, travel
   - Category comparisons
   - Pie chart visualization

3. **Customer Lifetime Value Report**
   - Top customers by revenue
   - Booking frequency
   - Average order value
   - Retention metrics

4. **Inventory Stock Levels**
   - Current stock by location
   - Reorder suggestions
   - Stock valuation
   - Turnover rates

5. **Employee Performance**
   - Sales by employee
   - Course completions
   - Customer ratings
   - Commission calculations

6. **Course Booking Trends**
   - 12-month trend analysis
   - Popular courses
   - Seasonal patterns
   - Capacity utilization

7. **Top Selling Products**
   - Best performers by revenue
   - Units sold
   - Profit margins
   - ABC analysis

8. **Customer Acquisition Cost**
   - Marketing spend
   - New customer count
   - CAC calculation
   - Channel effectiveness

### Creating Custom Reports

```php
// Generate a report
$result = $bi->generateReport($templateId, $tenantId, [
    'date_range' => 'last_30_days',
    'user_id' => $userId
]);

// Returns:
// - Report ID
// - Data results
// - Row count
// - Execution time
```

### Report Parameters

- **Date Ranges**: today, yesterday, last_7_days, last_30_days, last_month, last_quarter, this_year, last_year
- **Filters**: Category, location, employee, customer segment
- **Grouping**: Day, week, month, quarter, year
- **Sorting**: Any column, ascending/descending
- **Aggregations**: SUM, AVG, COUNT, MIN, MAX

### Export Formats

Reports can be exported in multiple formats:
- **JSON**: API consumption
- **CSV**: Excel-compatible spreadsheets
- **Excel**: Native .xlsx format
- **PDF**: Print-ready documents
- **HTML**: Web embedding

---

## Customer Analytics

### RFM Analysis

**Recency-Frequency-Monetary (RFM) Segmentation:**

The system automatically calculates RFM scores for all customers:
- **Recency (R)**: Days since last booking (1-5, 5=most recent)
- **Frequency (F)**: Total number of bookings (1-5, 5=most frequent)
- **Monetary (M)**: Total revenue generated (1-5, 5=highest value)

**RFM Score Examples:**
- `555`: Best customers (recent, frequent, high-value)
- `511`: Recent big spenders who don't book often
- `151`: Infrequent but loyal high-value customers
- `111`: Low-value, infrequent, haven't booked recently

### Customer Segments

The system automatically segments customers:

1. **VIP** (10+ bookings, recent activity)
   - Highest priority
   - Premium service
   - Exclusive offers

2. **Loyal** (5+ bookings, active)
   - Regular customers
   - Referral candidates
   - Upsell opportunities

3. **Regular** (3+ bookings)
   - Consistent customers
   - Growth potential
   - Engagement programs

4. **Occasional** (1-2 bookings)
   - Need nurturing
   - Re-engagement campaigns
   - Education opportunities

5. **At Risk** (90+ days since last booking)
   - Churn prevention needed
   - Win-back campaigns
   - Special incentives

6. **Lost** (180+ days inactive)
   - Re-acquisition efforts
   - Survey for feedback
   - Competitive analysis

7. **New** (First booking)
   - Onboarding sequence
   - Educational content
   - Welcome offers

### Customer Metrics

For each customer, the system calculates:

- **Total Bookings**: Lifetime booking count
- **Total Revenue**: Lifetime value (LTV)
- **Average Booking Value**: Revenue per booking
- **Booking Frequency**: Bookings per month
- **Days Since Last Booking**: Recency metric
- **Cancellation Rate**: % of cancelled bookings
- **Churn Risk Score**: 0-100 probability of churn
- **Preferred Booking Method**: Online, phone, in-person
- **Preferred Payment Method**: Credit card, cash, etc.
- **Favorite Activities**: Most booked courses/trips

### Using Customer Analytics

```php
use App\Services\Analytics\CustomerAnalyticsService;

$analytics = new CustomerAnalyticsService($db);

// Calculate analytics for one customer
$result = $analytics->calculateCustomerAnalytics($customerId, $tenantId);

// Get high-value customers
$vips = $analytics->getHighValueCustomers($tenantId, 50);

// Get at-risk customers
$atRisk = $analytics->getAtRiskCustomers($tenantId, 60); // 60% churn risk

// Get segment distribution
$segments = $analytics->getSegmentDistribution($tenantId);

// Cohort analysis
$cohorts = $analytics->getCohortAnalysis($tenantId, 'month');
```

### Churn Risk Calculation

**Churn Risk Score (0-100):**

Formula weights:
- **Recency (40%)**: Days since last booking
  - 0-60 days: 0 points
  - 61-90 days: 10 points
  - 91-180 days: 20 points
  - 181-365 days: 30 points
  - 365+ days: 40 points

- **Frequency (30%)**: Booking frequency
  - 2+ bookings/month: 0 points
  - 1-2 bookings/month: 10 points
  - 0.5-1 booking/month: 20 points
  - <0.5 bookings/month: 30 points

- **Cancellations (30%)**: Cancellation rate
  - 0-10%: 0 points
  - 11-30%: 10 points
  - 31-50%: 20 points
  - 50%+: 30 points

**Risk Levels:**
- **0-30**: Low risk (healthy customer)
- **31-60**: Medium risk (monitor closely)
- **61-100**: High risk (immediate action needed)

---

## Revenue Analytics

### Revenue Breakdown

Automatic tracking of revenue by category:
- **Course Revenue**: Certification courses, training
- **Retail Revenue**: Equipment sales, merchandise
- **Rental Revenue**: Equipment rentals
- **Service Revenue**: Maintenance, repairs, fills
- **Travel Revenue**: Trips, liveaboards, packages
- **Membership Revenue**: Subscriptions, clubs
- **Other Revenue**: Miscellaneous income

### Profitability Metrics

- **Total Revenue**: Sum of all income
- **Total Cost**: COGS + labor + operating expenses
- **Gross Profit**: Revenue - COGS
- **Gross Margin**: (Gross Profit / Revenue) × 100
- **Net Profit**: Revenue - Total Costs
- **Net Margin**: (Net Profit / Revenue) × 100

### Transaction Analytics

- **Total Transactions**: Count of sales
- **Average Transaction Value**: Revenue / Transactions
- **Unique Customers**: Distinct customer count

### Growth Metrics

- **Previous Period Comparison**: Period-over-period growth
- **Revenue Growth %**: (Current - Previous) / Previous × 100
- **Year-over-Year (YoY)**: Same period last year comparison
- **YoY Growth %**: Annual growth rate

### Period Types

- **Daily**: Track daily performance
- **Weekly**: 7-day rolling windows
- **Monthly**: Calendar month totals
- **Quarterly**: Q1, Q2, Q3, Q4
- **Yearly**: Annual summaries

---

## Product Analytics

### Product Performance Metrics

For each product/category, track:

- **Units Sold**: Quantity sold
- **Revenue**: Total sales value
- **Cost**: Total cost of goods
- **Profit**: Revenue - Cost
- **Profit Margin**: (Profit / Revenue) × 100
- **Average Sale Price**: Revenue / Units
- **Discount %**: Average discount applied

### Inventory Metrics

- **Average Inventory Level**: Mean stock level
- **Stockout Days**: Days with zero stock
- **Inventory Turnover**: Sales / Avg Inventory
- **Days of Inventory**: 365 / Turnover

### Product Ranking

Products automatically ranked by:
- **Revenue Rank**: #1, #2, #3 by revenue
- **Profit Rank**: Most profitable products
- **Velocity Rank**: Fastest selling items

### ABC Analysis (Pareto Principle)

Products classified using 80/20 rule:
- **A-Class**: Top 20% of products = 80% of revenue
- **B-Class**: Middle 30% = 15% of revenue
- **C-Class**: Bottom 50% = 5% of revenue
- **D-Class**: Dead stock (no sales)

**Use Cases:**
- Focus marketing on A-class items
- Reduce inventory of C/D-class items
- Identify pricing opportunities in B-class

---

## Scheduled Reports

### Scheduling Options

**Frequency Options:**
- **Daily**: Every day at specified time
- **Weekly**: Specific day of week
- **Monthly**: Specific day of month
- **Quarterly**: Every 3 months
- **Yearly**: Annual reports
- **Custom Cron**: Advanced scheduling

**Time Zones:**
All schedules respect tenant timezone settings (default: America/New_York)

### Date Range Types

For scheduled reports, choose dynamic ranges:
- **yesterday**: Previous day's data
- **last_7_days**: Rolling 7-day window
- **last_30_days**: Rolling 30-day window
- **last_month**: Previous calendar month
- **last_quarter**: Previous quarter
- **last_year**: Previous calendar year

### Delivery Methods

1. **Email Only**: Send to recipients, don't save
2. **Save Only**: Generate and store, no email
3. **Both**: Email and save to system

**Email Configuration:**
- Recipient list (JSON array of emails)
- Custom subject line
- Custom email body
- Attachment format (PDF, Excel, CSV, HTML)

### Example Scheduled Report

```php
// Monthly revenue report, sent on 1st of each month at 8 AM
{
    "template_id": 2, // Monthly Revenue by Category
    "frequency": "monthly",
    "day_of_month": 1,
    "time_of_day": "08:00:00",
    "timezone": "America/New_York",
    "date_range_type": "last_month",
    "delivery_method": "both",
    "recipients": ["owner@diveshop.com", "manager@diveshop.com"],
    "attach_as_format": "pdf"
}
```

### Monitoring Scheduled Reports

- **Last Run**: Timestamp of last execution
- **Next Run**: Calculated next execution time
- **Last Status**: Success, failed, or skipped
- **Consecutive Failures**: Automatic alerting after 3 failures

---

## Data Export

### Export Types

1. **Customers**: Full customer database
2. **Bookings**: All booking records
3. **Inventory**: Stock levels and movements
4. **Financial**: Revenue, expenses, transactions
5. **Custom Query**: User-defined SQL
6. **Full Backup**: Complete database export

### Export Formats

- **CSV**: Universal format, Excel-compatible
- **Excel**: Native .xlsx with formatting
- **JSON**: API integration, data processing
- **XML**: Enterprise system integration
- **SQL**: Database backup/migration
- **PDF**: Print-ready reports

### Export Process

```php
$export = $bi->exportData($tenantId, [
    'name' => 'Customer Export - Q4 2024',
    'type' => 'customers',
    'format' => 'excel',
    'start_date' => '2024-10-01',
    'end_date' => '2024-12-31',
    'filters' => [
        'customer_segment' => ['vip', 'loyal']
    ],
    'user_id' => $userId
]);

// Export job created
// User receives email when ready
// Download link expires in 24 hours
```

### Large Export Handling

For large datasets:
1. Job queued in background
2. Progress tracking
3. Email notification on completion
4. Secure download URL
5. Automatic cleanup after 7 days

---

## API Reference

### Get Dashboard

```php
GET /api/analytics/dashboards/{id}

$bi->getDashboard($dashboardId, $tenantId);

Response:
{
    "success": true,
    "dashboard": {
        "id": 1,
        "dashboard_name": "Executive Overview",
        "layout_config": {...},
        "auto_refresh": true,
        "refresh_interval_seconds": 300
    },
    "widgets": [
        {
            "id": 1,
            "widget_name": "Monthly Revenue",
            "widget_type": "kpi",
            "data": {
                "value": 52450.00,
                "target": 50000.00,
                "status": "green",
                "trend": "up",
                "change_percentage": 12.5
            }
        }
    ]
}
```

### Get KPI Value

```php
GET /api/analytics/kpis/{id}

$bi->getKPIValue($kpiId, $tenantId, 'monthly');

Response:
{
    "success": true,
    "kpi": {
        "kpi_name": "Monthly Revenue",
        "target_value": 50000.00
    },
    "value": {
        "actual_value": 52450.00,
        "variance": 2450.00,
        "variance_percentage": 4.9,
        "previous_period_value": 46700.00,
        "change_percentage": 12.3,
        "trend_direction": "up",
        "status": "green"
    }
}
```

### Generate Report

```php
POST /api/analytics/reports/generate

$bi->generateReport($templateId, $tenantId, [
    'date_range' => 'last_30_days'
]);

Response:
{
    "success": true,
    "report_id": 1234,
    "row_count": 156,
    "execution_time_ms": 245,
    "data": [...]
}
```

### Customer Analytics

```php
// Calculate all customer analytics
POST /api/analytics/customers/calculate

$analytics->calculateAllCustomerAnalytics($tenantId);

// Get high-value customers
GET /api/analytics/customers/high-value?limit=50

$analytics->getHighValueCustomers($tenantId, 50);

// Get at-risk customers
GET /api/analytics/customers/at-risk?min_risk=60

$analytics->getAtRiskCustomers($tenantId, 60);

// Segment distribution
GET /api/analytics/customers/segments

$analytics->getSegmentDistribution($tenantId);
```

---

## Best Practices

### Dashboard Design

1. **Keep It Simple**: 4-8 widgets per dashboard
2. **Prioritize**: Most important metrics top-left
3. **Use Colors Wisely**: Green/yellow/red for status
4. **Update Regularly**: Enable auto-refresh
5. **Mobile-Friendly**: Responsive layouts

### KPI Selection

1. **Aligned with Goals**: Choose metrics that matter
2. **Actionable**: Can you improve it?
3. **Measurable**: Quantifiable data
4. **Timely**: Real-time or daily updates
5. **Limited**: Focus on 5-10 key metrics

### Report Scheduling

1. **Match Cadence**: Daily operations = daily reports
2. **Right Audience**: Send to decision-makers
3. **Actionable Timing**: Monday AM for weekly reviews
4. **Clean Data**: Validate before automating
5. **Test First**: Manual generation before scheduling

### Customer Segmentation

1. **Regular Updates**: Recalculate monthly
2. **Segment Actions**: Different campaigns per segment
3. **Monitor Movement**: Track segment changes
4. **Reward Loyalty**: Special treatment for VIPs
5. **Win Back**: Re-engagement for at-risk

### Performance Optimization

1. **Cache Widgets**: Use 5-minute cache for heavy queries
2. **Limit Rows**: Top 100 instead of all records
3. **Indexed Queries**: Ensure proper database indexes
4. **Async Processing**: Background jobs for large reports
5. **Archive Old Data**: Move historical data to archive tables

---

## Troubleshooting

### Dashboard Not Loading

- Check cache_ttl_seconds (may need refresh)
- Verify report_template_id exists
- Ensure custom_query is valid SQL
- Check database connection
- Review error logs

### KPI Shows Wrong Value

- Verify calculation_formula
- Check date range parameters
- Ensure data_source_query is correct
- Validate aggregation_period
- Recalculate KPI manually

### Scheduled Report Failed

- Check consecutive_failures count
- Review error logs
- Validate email recipients
- Ensure query doesn't timeout
- Test query manually

### Slow Report Generation

- Add database indexes
- Limit date range
- Reduce row count
- Cache results
- Use aggregated tables

---

## Security & Privacy

### Data Access Control

- **Role-Based**: Managers see all data, staff see own data
- **Tenant Isolation**: Multi-tenant security enforced
- **API Authentication**: JWT tokens required
- **Rate Limiting**: Prevent abuse
- **Audit Logging**: Track all analytics access

### Report Sharing

- **Share Tokens**: Secure, expiring links
- **Password Protection**: Optional password for reports
- **Download Limits**: Track export usage
- **IP Restrictions**: Whitelist allowed IPs
- **Watermarks**: Identify report origin

---

## Future Enhancements

Planned features for future releases:

1. **Predictive Analytics**: ML-based forecasting
2. **Anomaly Detection**: Automatic alert for unusual patterns
3. **Natural Language Queries**: "Show me top customers this month"
4. **Mobile App**: Native iOS/Android analytics
5. **Advanced Visualizations**: Heatmaps, geographic maps
6. **A/B Testing**: Campaign effectiveness
7. **Benchmarking**: Compare to industry averages
8. **Data Warehouse**: Optimized analytics database

---

## Support & Resources

- **Documentation**: `/docs/analytics`
- **Video Tutorials**: Available in-app
- **Sample Dashboards**: Pre-configured templates
- **Community Forum**: Share custom reports
- **Professional Services**: Custom analytics development

---

**Last Updated**: January 2025
**Version**: 1.0
**Module**: Business Intelligence (Migration 097)
