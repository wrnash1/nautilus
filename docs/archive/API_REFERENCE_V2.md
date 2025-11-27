# Nautilus API Reference v2.0

Complete API documentation for the Nautilus Dive Shop Management System including all new enterprise features.

## Table of Contents
1. [Authentication](#authentication)
2. [Dashboard Widgets](#dashboard-widgets)
3. [Search & Filtering](#search--filtering)
4. [Audit Trail](#audit-trail)
5. [Notification Preferences](#notification-preferences)
6. [Backup Management](#backup-management)
7. [Customer Portal (Admin)](#customer-portal-admin)
8. [Customer Portal (Public)](#customer-portal-public)

---

## Authentication

All API endpoints (except customer portal public endpoints) require authentication via Bearer token.

### Headers
```
Authorization: Bearer {your_api_token}
Content-Type: application/json
```

### Rate Limiting
- **Authenticated Requests**: 1000 requests per hour
- **Unauthenticated Requests**: 100 requests per hour

---

## Dashboard Widgets

Manage customizable dashboard widgets for users.

### Get User Dashboard Widgets

```http
GET /api/v1/dashboard/widgets
```

**Response 200**
```json
{
  "success": true,
  "widgets": [
    {
      "id": 1,
      "widget_code": "sales_today",
      "widget_name": "Sales Today",
      "position": 1,
      "size": "medium",
      "settings": {},
      "data": {
        "total_sales": 1250.50,
        "transaction_count": 15,
        "average_sale": 83.37,
        "percent_change": 12.5,
        "trend": "up"
      }
    }
  ]
}
```

### Add Widget to Dashboard

```http
POST /api/v1/dashboard/widgets
```

**Request Body**
```json
{
  "widget_code": "low_stock_alerts",
  "settings": {
    "limit": 10,
    "size": "medium"
  }
}
```

**Response 200**
```json
{
  "success": true,
  "widget_id": 5,
  "message": "Widget added successfully"
}
```

### Update Widget Settings

```http
PUT /api/v1/dashboard/widgets/{id}
```

**Request Body**
```json
{
  "settings": {
    "limit": 20,
    "size": "large"
  }
}
```

### Remove Widget

```http
DELETE /api/v1/dashboard/widgets/{id}
```

### Reorder Widgets

```http
POST /api/v1/dashboard/widgets/reorder
```

**Request Body**
```json
{
  "widget_order": [3, 1, 5, 2, 4]
}
```

---

## Search & Filtering

Universal search across all entities with advanced filtering.

### Universal Search

```http
GET /api/v1/search?q={query}&entities=products,customers,transactions&limit=10
```

**Query Parameters**
- `q` (required): Search query
- `entities` (optional): Comma-separated entity types (default: all)
- `limit` (optional): Results per entity (default: 10)

**Response 200**
```json
{
  "success": true,
  "query": "john",
  "total_results": 15,
  "results": {
    "products": [...],
    "customers": [...],
    "transactions": [...]
  }
}
```

### Search Products

```http
GET /api/v1/search/products?q={query}&category_id=1&min_price=50&max_price=500&stock_status=in_stock
```

**Query Parameters**
- `q`: Search term
- `category_id`: Filter by category
- `min_price`: Minimum price
- `max_price`: Maximum price
- `stock_status`: in_stock, low_stock, out_of_stock
- `limit`: Results limit (default: 50)
- `offset`: Pagination offset
- `order_by`: Sort field (default: name)
- `order_dir`: ASC or DESC

**Response 200**
```json
{
  "success": true,
  "products": [
    {
      "id": 123,
      "sku": "REG-001",
      "name": "Dive Regulator",
      "price": 299.99,
      "stock_quantity": 15,
      "category_name": "Equipment"
    }
  ],
  "count": 42
}
```

### Search Customers

```http
GET /api/v1/search/customers?q={query}&certification_level=advanced&registered_from=2024-01-01
```

**Query Parameters**
- `q`: Search term (name, email, phone)
- `certification_level`: Filter by cert level
- `registered_from`: Registration start date
- `registered_to`: Registration end date
- `limit`: Results limit
- `offset`: Pagination offset

### Search Transactions

```http
GET /api/v1/search/transactions?q={query}&date_from=2024-01-01&date_to=2024-12-31&payment_method=credit_card
```

**Query Parameters**
- `q`: Search term (transaction number, customer name)
- `date_from`: Start date
- `date_to`: End date
- `min_amount`: Minimum amount
- `max_amount`: Maximum amount
- `payment_method`: Payment method filter
- `customer_id`: Specific customer
- `user_id`: Specific cashier

### Get Autocomplete Suggestions

```http
GET /api/v1/search/suggestions?q={query}&entity=products&limit=10
```

**Response 200**
```json
{
  "success": true,
  "suggestions": [
    {
      "id": 123,
      "name": "Dive Regulator Pro",
      "sku": "REG-PRO-001",
      "price": 599.99
    }
  ]
}
```

### Get Recent Searches

```http
GET /api/v1/search/recent
```

### Get Popular Searches

```http
GET /api/v1/search/popular?days=30&limit=10
```

---

## Audit Trail

Complete audit logging and security event tracking.

### Get Audit Trail

```http
GET /api/v1/audit?user_id=1&action=product_updated&entity_type=product&date_from=2024-01-01&limit=100
```

**Query Parameters**
- `user_id`: Filter by user
- `action`: Filter by action type
- `entity_type`: Filter by entity
- `entity_id`: Specific entity ID
- `date_from`: Start date
- `date_to`: End date
- `ip_address`: Filter by IP
- `limit`: Results limit (default: 100)
- `offset`: Pagination offset

**Response 200**
```json
{
  "success": true,
  "audit_logs": [
    {
      "id": 12345,
      "created_at": "2024-11-09 10:30:00",
      "user_name": "John Doe",
      "user_email": "john@example.com",
      "action": "product_updated",
      "entity_type": "product",
      "entity_id": 123,
      "old_values": {
        "price": 250.00
      },
      "new_values": {
        "price": 299.99
      },
      "ip_address": "192.168.1.100"
    }
  ],
  "total": 1523,
  "limit": 100,
  "offset": 0
}
```

### Get Entity Audit History

```http
GET /api/v1/audit/entity/{entity_type}/{entity_id}
```

**Example**
```http
GET /api/v1/audit/entity/product/123
```

### Get Audit Statistics

```http
GET /api/v1/audit/statistics?days=30
```

**Response 200**
```json
{
  "success": true,
  "period_days": 30,
  "total_events": 5234,
  "events_by_action": [
    {"action": "product_updated", "count": 1523},
    {"action": "user_login", "count": 892}
  ],
  "events_by_user": [
    {"user_id": 1, "user_name": "Admin User", "event_count": 1234}
  ],
  "events_over_time": [
    {"date": "2024-11-01", "event_count": 145}
  ]
}
```

### Get Security Events

```http
GET /api/v1/audit/security-events?date_from=2024-01-01&user_id=1
```

Returns login attempts, password changes, permission changes, etc.

### Get Failed Login Attempts

```http
GET /api/v1/audit/failed-logins?hours=24
```

**Response 200**
```json
{
  "success": true,
  "hours": 24,
  "attempts": [
    {
      "ip_address": "192.168.1.100",
      "attempt_count": 5,
      "last_attempt": "2024-11-09 10:45:00"
    }
  ]
}
```

### Get User Activity Summary

```http
GET /api/v1/audit/user-activity/{user_id}?days=30
```

### Export Audit Trail

```http
GET /api/v1/audit/export?date_from=2024-01-01&date_to=2024-12-31
```

Returns CSV file download.

---

## Notification Preferences

Manage user notification settings across multiple channels.

### Get User Notification Preferences

```http
GET /api/v1/notifications/preferences
```

**Response 200**
```json
{
  "success": true,
  "preferences": [
    {
      "id": 1,
      "notification_type": "low_stock_alert",
      "notification_name": "Low Stock Alert",
      "description": "Product stock below threshold",
      "category": "inventory",
      "email_enabled": true,
      "sms_enabled": false,
      "in_app_enabled": true,
      "push_enabled": false,
      "frequency": "daily"
    }
  ],
  "grouped": {
    "inventory": [...],
    "sales": [...],
    "customers": [...]
  }
}
```

### Update Notification Preference

```http
PUT /api/v1/notifications/preferences/{id}
```

**Request Body**
```json
{
  "email_enabled": true,
  "sms_enabled": true,
  "in_app_enabled": true,
  "push_enabled": false,
  "frequency": "instant"
}
```

### Bulk Update Preferences

```http
POST /api/v1/notifications/preferences/bulk
```

**Request Body**
```json
{
  "preferences": {
    "1": {"email_enabled": true, "frequency": "instant"},
    "2": {"email_enabled": false, "frequency": "never"}
  }
}
```

### Get Notification History

```http
GET /api/v1/notifications/history?channel=email&status=sent&limit=50
```

**Query Parameters**
- `channel`: email, sms, in_app, push
- `status`: sent, delivered, read, failed
- `limit`: Results limit
- `offset`: Pagination offset

### Disable All Notifications

```http
POST /api/v1/notifications/disable-all
```

### Enable All Notifications

```http
POST /api/v1/notifications/enable-all
```

---

## Backup Management

Create, manage, and restore system backups.

### Create Database Backup

```http
POST /api/v1/backups/database
```

**Request Body**
```json
{
  "tenant_id": 1,
  "compress": true,
  "description": "Monthly backup"
}
```

**Response 200**
```json
{
  "success": true,
  "backup_id": 123,
  "filename": "tenant_1_db_backup_20241109_103000.sql.gz",
  "file_size": 25600000,
  "message": "Database backup created successfully"
}
```

### Create File Backup

```http
POST /api/v1/backups/files
```

**Request Body**
```json
{
  "directories": ["public/uploads", "storage/logs"],
  "compress": true
}
```

### List Backups

```http
GET /api/v1/backups?type=database&limit=50
```

**Response 200**
```json
{
  "success": true,
  "backups": [
    {
      "id": 123,
      "backup_type": "database",
      "filename": "backup_20241109.sql.gz",
      "file_size": 25600000,
      "created_at": "2024-11-09 10:30:00",
      "status": "completed"
    }
  ]
}
```

### Delete Backup

```http
DELETE /api/v1/backups/{id}
```

### Restore Backup

```http
POST /api/v1/backups/{id}/restore
```

**Request Body**
```json
{
  "confirmation": "RESTORE"
}
```

---

## Customer Portal (Admin)

Staff endpoints for managing customer portal access.

### Get Customer Portal Access

```http
GET /api/v1/customer-portal/access/{customer_id}
```

**Response 200**
```json
{
  "success": true,
  "access": {
    "id": 1,
    "customer_id": 123,
    "email": "customer@example.com",
    "is_active": true,
    "last_login_at": "2024-11-09 09:00:00",
    "login_count": 45
  }
}
```

### Create Portal Access

```http
POST /api/v1/customer-portal/access
```

**Request Body**
```json
{
  "customer_id": 123,
  "email": "customer@example.com",
  "password": "securePassword123!",
  "send_welcome_email": true
}
```

### Update Portal Access

```http
PUT /api/v1/customer-portal/access/{id}
```

**Request Body**
```json
{
  "is_active": false,
  "email": "newemail@example.com"
}
```

### Revoke Portal Access

```http
DELETE /api/v1/customer-portal/access/{id}
```

---

## Customer Portal (Public)

Customer-facing API endpoints for the self-service portal.

**Base URL**: `/api/portal`

### Customer Login

```http
POST /api/portal/auth/login
```

**Request Body**
```json
{
  "email": "customer@example.com",
  "password": "customerPassword123"
}
```

**Response 200**
```json
{
  "success": true,
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "customer": {
    "id": 123,
    "first_name": "John",
    "last_name": "Doe",
    "email": "customer@example.com"
  }
}
```

### Forgot Password

```http
POST /api/portal/auth/forgot-password
```

**Request Body**
```json
{
  "email": "customer@example.com"
}
```

### Reset Password

```http
POST /api/portal/auth/reset-password
```

**Request Body**
```json
{
  "token": "reset_token_here",
  "password": "newSecurePassword123",
  "password_confirmation": "newSecurePassword123"
}
```

### Get Customer Dashboard

```http
GET /api/portal/dashboard
```

**Headers**
```
Authorization: Bearer {customer_portal_token}
```

**Response 200**
```json
{
  "success": true,
  "customer": {
    "id": 123,
    "name": "John Doe",
    "certification_level": "Advanced Open Water"
  },
  "purchase_stats": {
    "total_transactions": 42,
    "total_spent": 5234.50,
    "average_order": 124.63
  },
  "course_stats": {
    "enrolled_count": 3,
    "completed_count": 2
  },
  "active_rentals": 1,
  "unread_notifications": 5
}
```

### Get Purchase History

```http
GET /api/portal/purchases?limit=20&offset=0
```

**Response 200**
```json
{
  "success": true,
  "purchases": [
    {
      "id": 456,
      "transaction_number": "TXN-20241109-001",
      "transaction_date": "2024-11-09",
      "total_amount": 299.99,
      "payment_method": "credit_card",
      "status": "completed",
      "items": [
        {
          "product_name": "Dive Mask",
          "quantity": 1,
          "price": 99.99
        }
      ]
    }
  ]
}
```

### Get Purchase Details

```http
GET /api/portal/purchases/{id}
```

### Get Course Enrollments

```http
GET /api/portal/courses
```

### Request Course Enrollment

```http
POST /api/portal/courses/{id}/enroll
```

### Get Rental History

```http
GET /api/portal/rentals
```

### Get Certifications

```http
GET /api/portal/certifications
```

### Get Profile

```http
GET /api/portal/profile
```

### Update Profile

```http
PUT /api/portal/profile
```

**Request Body**
```json
{
  "email": "newemail@example.com",
  "phone": "555-1234",
  "emergency_contact_name": "Jane Doe",
  "emergency_contact_phone": "555-5678"
}
```

### Get Notifications

```http
GET /api/portal/notifications?limit=20
```

### Mark Notification as Read

```http
PUT /api/portal/notifications/{id}/read
```

### Get Support Tickets

```http
GET /api/portal/support-tickets
```

### Create Support Ticket

```http
POST /api/portal/support-tickets
```

**Request Body**
```json
{
  "subject": "Question about my order",
  "category": "orders",
  "priority": "normal",
  "description": "I have a question about order #12345..."
}
```

### Get Support Ticket Details

```http
GET /api/portal/support-tickets/{id}
```

### Add Message to Support Ticket

```http
POST /api/portal/support-tickets/{id}/messages
```

**Request Body**
```json
{
  "message": "Thank you for the response..."
}
```

---

## Error Responses

All endpoints return consistent error responses:

**400 Bad Request**
```json
{
  "success": false,
  "error": "Invalid input data",
  "errors": {
    "email": ["Email is required"]
  }
}
```

**401 Unauthorized**
```json
{
  "success": false,
  "error": "Authentication required"
}
```

**403 Forbidden**
```json
{
  "success": false,
  "error": "Permission denied: products.create"
}
```

**404 Not Found**
```json
{
  "success": false,
  "error": "Resource not found"
}
```

**429 Too Many Requests**
```json
{
  "success": false,
  "error": "Rate limit exceeded. Please try again later.",
  "retry_after": 3600
}
```

**500 Internal Server Error**
```json
{
  "success": false,
  "error": "An internal error occurred. Please contact support."
}
```

---

## Webhooks

Configure webhooks to receive real-time notifications for events.

### Available Events
- `transaction.completed`
- `customer.created`
- `course.enrolled`
- `rental.created`
- `rental.returned`
- `product.low_stock`
- `backup.completed`
- `backup.failed`

### Webhook Payload Example
```json
{
  "event": "transaction.completed",
  "timestamp": "2024-11-09T10:30:00Z",
  "data": {
    "transaction_id": 123,
    "total_amount": 299.99,
    "customer_id": 456
  }
}
```

---

## Changelog

### v2.0 (November 2024)
- Added Dashboard Widgets API
- Added Universal Search & Filtering
- Added Audit Trail & Security Monitoring
- Added Notification Preferences
- Added Backup Management
- Added Customer Portal (Admin & Public APIs)
- Enhanced authentication with customer portal support
- Added webhook support for real-time events

### v1.0 (Initial Release)
- Basic CRUD operations for products, customers, transactions
- Course and rental management
- Basic reporting

---

## Support

For API support, contact:
- Email: api-support@nautilus.com
- Documentation: https://docs.nautilus.com/api
- Status Page: https://status.nautilus.com
