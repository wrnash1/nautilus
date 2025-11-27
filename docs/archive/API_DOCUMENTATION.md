# Nautilus REST API Documentation

Complete API reference for programmatic access to Nautilus.

## Base URL

```
https://your-subdomain.nautilus.com/api/v1
```

## Authentication

All API requests require authentication using an API key.

### Getting an API Key

1. Log into your Nautilus dashboard
2. Navigate to Settings → API Keys
3. Click "Create New API Key"
4. Set permissions and save
5. Copy the API key and secret (shown only once!)

### Authentication Methods

#### Bearer Token (Recommended)

```http
Authorization: Bearer nautilus_abc123...
```

#### API Key Header

```http
X-API-Key: nautilus_abc123...
```

#### Query Parameter (Not recommended)

```http
GET /api/v1/products?api_key=nautilus_abc123...
```

## Rate Limiting

- **Standard**: 1000 requests/hour
- **Professional**: 5000 requests/hour
- **Enterprise**: Unlimited

Rate limit headers are included in all responses:

```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 995
X-RateLimit-Reset: 1640995200
```

## Response Format

All responses are in JSON format:

### Success Response

```json
{
  "success": true,
  "data": { ... },
  "pagination": { ... }
}
```

### Error Response

```json
{
  "error": "Error message",
  "details": ["Additional error info"]
}
```

## HTTP Status Codes

- `200 OK` - Request successful
- `201 Created` - Resource created
- `400 Bad Request` - Invalid parameters
- `401 Unauthorized` - Missing or invalid API key
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `429 Too Many Requests` - Rate limit exceeded
- `500 Internal Server Error` - Server error

---

## Products API

### List Products

Get a paginated list of products.

**Endpoint:** `GET /api/v1/products`

**Permissions Required:** `products.read`

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| page | integer | Page number (default: 1) |
| per_page | integer | Records per page (max: 100, default: 50) |
| search | string | Search in name, SKU, or description |
| category_id | integer | Filter by category |
| min_price | decimal | Minimum price filter |
| max_price | decimal | Maximum price filter |
| in_stock | boolean | Show only in-stock products |

**Example Request:**

```bash
curl -X GET \
  'https://acme.nautilus.com/api/v1/products?page=1&per_page=20&in_stock=true' \
  -H 'Authorization: Bearer nautilus_abc123...'
```

**Example Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "sku": "MASK-PRO-001",
      "name": "Professional Dive Mask",
      "description": "High-quality silicone dive mask",
      "category_id": 2,
      "category_name": "Diving Equipment",
      "price": "79.99",
      "cost": "45.00",
      "stock_quantity": 15,
      "low_stock_threshold": 5,
      "barcode": "1234567890123",
      "is_active": 1,
      "created_at": "2025-01-08 10:00:00",
      "updated_at": "2025-01-08 10:00:00"
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 145,
    "total_pages": 8
  }
}
```

### Get Single Product

**Endpoint:** `GET /api/v1/products/{id}`

**Permissions Required:** `products.read`

**Example Request:**

```bash
curl -X GET \
  'https://acme.nautilus.com/api/v1/products/1' \
  -H 'Authorization: Bearer nautilus_abc123...'
```

**Example Response:**

```json
{
  "success": true,
  "data": {
    "id": 1,
    "sku": "MASK-PRO-001",
    "name": "Professional Dive Mask",
    "description": "High-quality silicone dive mask",
    "category_id": 2,
    "category_name": "Diving Equipment",
    "price": "79.99",
    "cost": "45.00",
    "stock_quantity": 15,
    "low_stock_threshold": 5,
    "barcode": "1234567890123",
    "is_active": 1
  }
}
```

### Create Product

**Endpoint:** `POST /api/v1/products`

**Permissions Required:** `products.write`

**Request Body:**

```json
{
  "sku": "MASK-PRO-001",
  "name": "Professional Dive Mask",
  "description": "High-quality silicone dive mask",
  "category_id": 2,
  "price": 79.99,
  "cost": 45.00,
  "stock_quantity": 15,
  "low_stock_threshold": 5,
  "barcode": "1234567890123",
  "is_active": 1
}
```

**Example Request:**

```bash
curl -X POST \
  'https://acme.nautilus.com/api/v1/products' \
  -H 'Authorization: Bearer nautilus_abc123...' \
  -H 'Content-Type: application/json' \
  -d '{
    "sku": "MASK-PRO-001",
    "name": "Professional Dive Mask",
    "price": 79.99,
    "stock_quantity": 15
  }'
```

**Example Response:**

```json
{
  "success": true,
  "message": "Product created successfully",
  "data": {
    "id": 1,
    "sku": "MASK-PRO-001",
    "name": "Professional Dive Mask",
    "price": "79.99",
    "stock_quantity": 15
  }
}
```

### Update Product

**Endpoint:** `PUT /api/v1/products/{id}`

**Permissions Required:** `products.write`

**Request Body:** (all fields optional)

```json
{
  "name": "Updated Product Name",
  "price": 89.99,
  "stock_quantity": 20
}
```

**Example Request:**

```bash
curl -X PUT \
  'https://acme.nautilus.com/api/v1/products/1' \
  -H 'Authorization: Bearer nautilus_abc123...' \
  -H 'Content-Type: application/json' \
  -d '{"price": 89.99}'
```

**Example Response:**

```json
{
  "success": true,
  "message": "Product updated successfully",
  "data": {
    "id": 1,
    "sku": "MASK-PRO-001",
    "name": "Professional Dive Mask",
    "price": "89.99",
    "stock_quantity": 15
  }
}
```

### Delete Product

**Endpoint:** `DELETE /api/v1/products/{id}`

**Permissions Required:** `products.delete`

**Example Request:**

```bash
curl -X DELETE \
  'https://acme.nautilus.com/api/v1/products/1' \
  -H 'Authorization: Bearer nautilus_abc123...'
```

**Example Response:**

```json
{
  "success": true,
  "message": "Product deleted successfully"
}
```

### Update Product Stock

**Endpoint:** `POST /api/v1/products/{id}/stock`

**Permissions Required:** `products.write`

**Request Body:**

```json
{
  "quantity": 10,
  "type": "restock",
  "reason": "Received shipment"
}
```

**Types:**
- `adjustment` - Manual adjustment
- `restock` - Received new inventory
- `sale` - Sold items
- `damage` - Damaged items

**Example Request:**

```bash
curl -X POST \
  'https://acme.nautilus.com/api/v1/products/1/stock' \
  -H 'Authorization: Bearer nautilus_abc123...' \
  -H 'Content-Type: application/json' \
  -d '{
    "quantity": 10,
    "type": "restock",
    "reason": "Received shipment"
  }'
```

**Example Response:**

```json
{
  "success": true,
  "message": "Stock updated successfully",
  "data": {
    "old_quantity": 15,
    "new_quantity": 25,
    "change": 10
  }
}
```

### Get Low Stock Products

**Endpoint:** `GET /api/v1/products/low-stock`

**Permissions Required:** `products.read`

**Example Request:**

```bash
curl -X GET \
  'https://acme.nautilus.com/api/v1/products/low-stock' \
  -H 'Authorization: Bearer nautilus_abc123...'
```

**Example Response:**

```json
{
  "success": true,
  "count": 3,
  "data": [
    {
      "id": 5,
      "sku": "FIN-001",
      "name": "Dive Fins",
      "stock_quantity": 2,
      "low_stock_threshold": 5
    }
  ]
}
```

---

## Export/Import API

### Export Products

**Endpoint:** `POST /api/v1/export/products`

**Permissions Required:** `products.read`

**Request Body:**

```json
{
  "format": "csv"
}
```

**Formats:** `csv`, `json`, `excel`

**Example Response:**

```json
{
  "success": true,
  "filename": "products_export_tenant123_2025-01-08_15-30-00.csv",
  "download_url": "/api/v1/exports/download/products_export_tenant123_2025-01-08_15-30-00.csv",
  "records": 145
}
```

### Export Customers

**Endpoint:** `POST /api/v1/export/customers`

**Permissions Required:** `customers.read`

**Request Body:**

```json
{
  "format": "json"
}
```

### Export Transactions

**Endpoint:** `POST /api/v1/export/transactions`

**Permissions Required:** `transactions.read`

**Request Body:**

```json
{
  "start_date": "2025-01-01",
  "end_date": "2025-01-31",
  "format": "csv"
}
```

### Import Products

**Endpoint:** `POST /api/v1/import/products`

**Permissions Required:** `products.write`

**Request:** Multipart form data

```bash
curl -X POST \
  'https://acme.nautilus.com/api/v1/import/products' \
  -H 'Authorization: Bearer nautilus_abc123...' \
  -F 'file=@products.csv' \
  -F 'format=csv'
```

**Example Response:**

```json
{
  "success": true,
  "imported": 45,
  "updated": 12,
  "skipped": 3,
  "total": 60,
  "errors": [
    "Row 5: SKU is required",
    "Row 12: Invalid price format"
  ],
  "warnings": []
}
```

### Validate Import File

**Endpoint:** `POST /api/v1/import/validate`

**Request Body:**

```bash
curl -X POST \
  'https://acme.nautilus.com/api/v1/import/validate' \
  -H 'Authorization: Bearer nautilus_abc123...' \
  -F 'file=@products.csv' \
  -F 'type=products' \
  -F 'format=csv'
```

**Example Response:**

```json
{
  "valid": true,
  "record_count": 60,
  "sample_size": 10,
  "issues": [],
  "preview": [
    {
      "sku": "PROD-001",
      "name": "Sample Product",
      "price": "29.99"
    }
  ]
}
```

### Get Import Template

**Endpoint:** `GET /api/v1/import/template/{type}`

**Parameters:**
- `type`: `products`, `customers`
- `format`: `csv`, `json` (query parameter)

**Example Request:**

```bash
curl -X GET \
  'https://acme.nautilus.com/api/v1/import/template/products?format=csv' \
  -H 'Authorization: Bearer nautilus_abc123...'
```

**Response:** CSV or JSON template file

---

## API Key Management

### Create API Key

**Endpoint:** `POST /api/v1/auth/keys`

**Permissions Required:** Admin access

**Request Body:**

```json
{
  "key_name": "Production Integration",
  "permissions": {
    "products.read": true,
    "products.write": true,
    "customers.read": true
  },
  "expires_at": "2026-01-01"
}
```

**Example Response:**

```json
{
  "success": true,
  "api_key": "nautilus_abc123...",
  "api_secret": "secret_xyz789...",
  "message": "API key created successfully. Save the secret - it will not be shown again!"
}
```

### List API Keys

**Endpoint:** `GET /api/v1/auth/keys`

**Example Response:**

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "key_name": "Production Integration",
      "api_key": "nautilus_...xyz",
      "permissions": {
        "products.read": true,
        "products.write": true
      },
      "is_active": true,
      "last_used_at": "2025-01-08 14:30:00",
      "expires_at": "2026-01-01",
      "created_at": "2025-01-01 10:00:00"
    }
  ]
}
```

### Revoke API Key

**Endpoint:** `POST /api/v1/auth/keys/revoke`

**Request Body:**

```json
{
  "key_id": 1
}
```

**Example Response:**

```json
{
  "success": true,
  "message": "API key revoked successfully"
}
```

---

## Permissions

Available permissions for API keys:

### Products
- `products.read` - View products
- `products.write` - Create/update products
- `products.delete` - Delete products

### Customers
- `customers.read` - View customers
- `customers.write` - Create/update customers
- `customers.delete` - Delete customers

### Transactions
- `transactions.read` - View transactions
- `transactions.write` - Create transactions
- `transactions.refund` - Process refunds

### Reports
- `reports.read` - Access reports
- `reports.export` - Export data

### Wildcard
- `*` - Full access (admin)

---

## SDKs and Libraries

### PHP

```php
require 'vendor/autoload.php';

$client = new Nautilus\Client([
    'api_key' => 'nautilus_abc123...',
    'subdomain' => 'acme'
]);

// List products
$products = $client->products()->list([
    'per_page' => 20,
    'in_stock' => true
]);

// Create product
$product = $client->products()->create([
    'sku' => 'PROD-001',
    'name' => 'Sample Product',
    'price' => 29.99
]);
```

### JavaScript/Node.js

```javascript
const Nautilus = require('nautilus-client');

const client = new Nautilus({
  apiKey: 'nautilus_abc123...',
  subdomain: 'acme'
});

// List products
const products = await client.products.list({
  perPage: 20,
  inStock: true
});

// Create product
const product = await client.products.create({
  sku: 'PROD-001',
  name: 'Sample Product',
  price: 29.99
});
```

### Python

```python
from nautilus import Client

client = Client(
    api_key='nautilus_abc123...',
    subdomain='acme'
)

# List products
products = client.products.list(
    per_page=20,
    in_stock=True
)

# Create product
product = client.products.create(
    sku='PROD-001',
    name='Sample Product',
    price=29.99
)
```

---

## Webhooks

Configure webhooks to receive real-time notifications of events.

### Available Events

- `product.created`
- `product.updated`
- `product.deleted`
- `product.low_stock`
- `customer.created`
- `customer.updated`
- `transaction.completed`
- `transaction.refunded`

### Webhook Payload

```json
{
  "event": "product.created",
  "timestamp": "2025-01-08T15:30:00Z",
  "data": {
    "id": 1,
    "sku": "PROD-001",
    "name": "New Product"
  }
}
```

---

## Support

For API support:
- Email: api-support@nautilus.com
- Documentation: https://docs.nautilus.com/api
- Status Page: https://status.nautilus.com

## Changelog

### v1.0.0 (2025-01-08)
- Initial API release
- Products, Customers, Transactions endpoints
- Export/Import functionality
- API key management
