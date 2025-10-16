# Nautilus v6.0 API Documentation

## Overview

The Nautilus v6.0 system provides a comprehensive RESTful API for all major operations including POS, CRM, Inventory, E-commerce, Rentals, and Courses.

## Authentication

All API requests (except `/auth/login` and `/auth/register`) require authentication via JWT Bearer token.

```
Authorization: Bearer {your-jwt-token}
```

## Base URL

```
https://your-domain.com/api/v1
```

## Endpoints

### Authentication

#### POST /auth/login
Authenticate a user and receive a JWT token.

**Request Body:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 1,
    "email": "user@example.com",
    "first_name": "John",
    "last_name": "Doe",
    "role_id": 2
  }
}
```

#### POST /auth/register
Register a new customer account.

**Request Body:**
```json
{
  "email": "newuser@example.com",
  "password": "password123",
  "first_name": "Jane",
  "last_name": "Smith"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "user": {
    "id": 15,
    "email": "newuser@example.com",
    "first_name": "Jane",
    "last_name": "Smith"
  }
}
```

---

### Customers (CRM)

#### GET /customers
List all customers with pagination.

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 20)

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com",
      "phone": "555-1234",
      "customer_type": "b2c"
    }
  ]
}
```

#### GET /customers/{id}
Get detailed information about a specific customer.

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "first_name": "John",
    "last_name": "Doe",
    "email": "john@example.com",
    "phone": "555-1234",
    "customer_type": "b2c",
    "addresses": [...],
    "certifications": [...]
  }
}
```

#### POST /customers
Create a new customer.

**Request Body:**
```json
{
  "first_name": "Jane",
  "last_name": "Smith",
  "email": "jane@example.com",
  "phone": "555-5678",
  "customer_type": "b2c"
}
```

#### PUT /customers/{id}
Update customer information.

#### DELETE /customers/{id}
Soft delete a customer (sets is_active to 0).

---

### Products (Inventory)

#### GET /products
List all products with optional filtering.

**Query Parameters:**
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 20)
- `category_id` (optional): Filter by category

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Regulator Set",
      "sku": "REG-001",
      "price": 299.99,
      "stock_quantity": 15,
      "category_name": "Equipment"
    }
  ]
}
```

#### GET /products/{id}
Get detailed product information including variants and stock levels.

#### POST /products
Create a new product.

**Request Body:**
```json
{
  "name": "New Wetsuit",
  "sku": "WET-001",
  "price": 199.99,
  "cost": 120.00,
  "stock_quantity": 10,
  "category_id": 2,
  "description": "High-quality 3mm wetsuit"
}
```

#### PUT /products/{id}
Update product information.

#### DELETE /products/{id}
Soft delete a product.

---

### Transactions (POS)

#### GET /transactions
List transactions within a date range.

**Query Parameters:**
- `start_date` (optional): Start date (YYYY-MM-DD, default: first day of current month)
- `end_date` (optional): End date (YYYY-MM-DD, default: last day of current month)

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "transaction_date": "2025-10-15",
      "total_amount": 499.99,
      "payment_method": "credit_card",
      "customer_name": "John Doe",
      "items": [...]
    }
  ]
}
```

#### GET /transactions/{id}
Get detailed transaction information including line items and payments.

#### POST /transactions
Create a new POS transaction.

**Request Body:**
```json
{
  "customer_id": 1,
  "items": [
    {
      "product_id": 5,
      "quantity": 2,
      "price": 99.99
    }
  ],
  "payment_method": "credit_card",
  "payment_amount": 199.98
}
```

---

### Orders (E-commerce)

#### GET /orders
List orders with optional filtering.

**Query Parameters:**
- `customer_id` (optional): Filter by customer
- `status` (optional): Filter by status (pending, processing, shipped, delivered, cancelled)

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "order_number": "ORD-2025-001",
      "customer_id": 1,
      "total_amount": 299.99,
      "status": "processing",
      "order_date": "2025-10-15"
    }
  ]
}
```

#### GET /orders/{id}
Get detailed order information including line items and shipping details.

#### POST /orders
Create a new e-commerce order.

**Request Body:**
```json
{
  "customer_id": 1,
  "items": [
    {
      "product_id": 3,
      "quantity": 1,
      "price": 299.99
    }
  ],
  "shipping_address_id": 1,
  "billing_address_id": 1
}
```

#### PUT /orders/{id}
Update order status.

**Request Body:**
```json
{
  "status": "shipped"
}
```

---

### Rentals

#### GET /rentals
List rental reservations.

**Query Parameters:**
- `status` (optional): Filter by status (reserved, checked_out, checked_in, cancelled)

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "customer_id": 1,
      "rental_start_date": "2025-10-20",
      "rental_end_date": "2025-10-25",
      "status": "reserved",
      "total_amount": 150.00
    }
  ]
}
```

#### GET /rentals/{id}
Get detailed rental reservation information.

#### POST /rentals
Create a new rental reservation.

**Request Body:**
```json
{
  "customer_id": 1,
  "rental_start_date": "2025-10-20",
  "rental_end_date": "2025-10-25",
  "equipment_items": [
    {
      "equipment_id": 5,
      "quantity": 1,
      "daily_rate": 30.00
    }
  ]
}
```

#### PUT /rentals/{id}/checkin
Check in rental equipment.

**Request Body:**
```json
{
  "actual_return_date": "2025-10-25",
  "condition": "good",
  "notes": "Equipment returned in excellent condition"
}
```

---

### Courses

#### GET /courses
List all available courses.

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Open Water Certification",
      "certification_level": "Open Water Diver",
      "price": 499.99,
      "duration_days": 3
    }
  ]
}
```

#### GET /courses/{id}
Get detailed course information including available schedules.

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Open Water Certification",
    "description": "...",
    "price": 499.99,
    "schedules": [
      {
        "id": 1,
        "start_date": "2025-11-01",
        "end_date": "2025-11-03",
        "instructor": "John Smith",
        "max_students": 8,
        "enrolled_students": 5
      }
    ]
  }
}
```

#### POST /courses/{id}/enroll
Enroll a student in a course.

**Request Body:**
```json
{
  "schedule_id": 1,
  "customer_id": 15
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "enrollment_id": 42
}
```

---

## Response Format

### Success Response
All successful responses follow this format:

```json
{
  "success": true,
  "data": { }
}
```

### Error Response
Error responses include HTTP status codes and error details:

```json
{
  "error": "Error Type",
  "message": "Detailed error message"
}
```

## HTTP Status Codes

- `200 OK` - Request succeeded
- `201 Created` - Resource created successfully
- `400 Bad Request` - Invalid request data
- `401 Unauthorized` - Missing or invalid authentication
- `403 Forbidden` - Insufficient permissions
- `404 Not Found` - Resource not found
- `409 Conflict` - Resource conflict (e.g., duplicate email)
- `500 Internal Server Error` - Server error

## Rate Limiting

API requests are limited to 1000 requests per hour per authenticated user. Rate limit headers are included in responses:

```
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 995
X-RateLimit-Reset: 1698765432
```

## Implementation Notes

### JWT Authentication
- Tokens expire after 24 hours (86400 seconds)
- Token format: `Bearer {base64-encoded-json}`
- Token payload includes: `user_id`, `exp` (expiration timestamp)
- **Note:** Current implementation uses basic token generation. For production, integrate a proper JWT library with signature verification and token refresh capability.

### Pagination
Most list endpoints support pagination via `page` and `limit` query parameters. Default page size is 20 items.

### Date Formats
All dates use ISO 8601 format: `YYYY-MM-DD` for dates, `YYYY-MM-DD HH:MM:SS` for timestamps.

## Development Status

**Framework Status:** ✅ All API endpoints have been implemented with controllers and service layer integration.

**Ready for:**
- Integration testing
- JWT library integration for production
- Rate limiting implementation
- API documentation UI (e.g., Swagger/OpenAPI)

**Service Layer Reuse:** All API controllers leverage existing service classes (CustomerService, ProductService, TransactionService, etc.) ensuring consistent business logic between web and API interfaces.
