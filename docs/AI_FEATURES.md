# AI-Powered Inventory Forecasting

## Features Implemented

### SalesDataCollector
- Historical sales data extraction
- Seasonal pattern analysis  
- Trend detection
- Top-selling products identification

### InventoryForecaster
- **Demand prediction** with confidence intervals
- **Reorder point** calculation
- **EOQ** (Economic Order Quantity)
- **Seasonal insights** identification

### API Endpoints
```
GET /api/ai/forecast/{productId}?days=30
GET /api/ai/recommendations?limit=20
GET /api/ai/reorder/{productId}?lead_time=7
GET /api/ai/seasonal?product_id=123
GET /api/ai/top-products?limit=20&days=90
GET /api/ai/insights
```

### Admin Dashboard
Beautiful UI at `/admin/ai-insights` showing:
- Products needing restock
- Urgent orders count
- Peak season identification
- Top selling products
- Actionable recommendations

## Quick Start

1. **Install PHP-ML**:
   ```bash
   composer require php-ai/php-ml
   ```

2. **Test API**:
   ```bash
   curl http://localhost/api/ai/insights
   ```

3. **View Dashboard**:
   Open: `http://localhost/admin/ai-insights`

## Example Response

```json
{
  "recommendations": [
    {
      "product_name": "Regulator Pro",
      "urgency": "high",
      "current_stock": 5,
      "days_until_stockout": 3,
      "recommended_quantity": 30
    }
  ]
}
```

## Next Steps

### Phase 2: Community Learning
- Create GitHub repo for model sharing
- Implement federated learning
- Add differential privacy
- Build model aggregation system

### Phase 3: Advanced Features
- Real-time predictions
- Price optimization
- Customer behavior prediction
- Equipment maintenance forecasting

## Configuration

Edit `.env`:
```env
AI_ENABLED=true
AI_CONTRIBUTE=false  # Phase 2
AI_PRIVACY=true
```

## Cron Job Setup

Add to crontab for daily retraining:
```bash
0 2 * * * cd /var/www/html/nautilus && php artisan ai:retrain
```

## Privacy

- All data stays local (Phase 1)
- Multi-tenant isolation enforced
- No customer data exposed
- GDPR compliant
