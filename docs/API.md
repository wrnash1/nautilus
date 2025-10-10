# Nautilus v6.0 API Documentation

## Overview

The Nautilus v6.0 system provides a comprehensive RESTful API for all operations.

## Authentication

All API requests require authentication via JWT token or session-based auth.

```
Authorization: Bearer {token}
```

## Base URL

```
https://your-domain.com/api/v1
```

## Endpoints

### Authentication
- `POST /auth/login` - Authenticate user
- `POST /auth/logout` - End session
- `POST /auth/refresh` - Refresh JWT token

### Customers (CRM)
- `GET /customers` - List customers
- `GET /customers/{id}` - Get customer details
- `POST /customers` - Create customer
- `PUT /customers/{id}` - Update customer
- `DELETE /customers/{id}` - Delete customer

### Products (Inventory)
- `GET /products` - List products
- `GET /products/{id}` - Get product details
- `POST /products` - Create product
- `PUT /products/{id}` - Update product
- `PATCH /products/{id}/stock` - Adjust stock

### Transactions (POS)
- `POST /transactions` - Create transaction
- `GET /transactions/{id}` - Get transaction details
- `POST /transactions/{id}/void` - Void transaction
- `POST /transactions/{id}/refund` - Refund transaction

### Orders (E-commerce)
- `GET /orders` - List orders
- `GET /orders/{id}` - Get order details
- `POST /orders` - Create order
- `PATCH /orders/{id}/status` - Update order status

### Rentals
- `GET /rentals/equipment` - List rental equipment
- `POST /rentals/reservations` - Create reservation
- `POST /rentals/checkout` - Check out equipment
- `POST /rentals/checkin` - Check in equipment

### Courses
- `GET /courses` - List courses
- `GET /courses/{id}/schedules` - Get course schedules
- `POST /enrollments` - Enroll student
- `POST /attendance` - Record attendance

## Response Format

All responses follow this format:

```json
{
  "success": true,
  "data": {},
  "message": "Operation successful"
}
```

## Error Handling

Errors return appropriate HTTP status codes with details:

```json
{
  "success": false,
  "error": "Error message",
  "code": "ERROR_CODE"
}
```

## Rate Limiting

API requests are limited to 1000 requests per hour per user.
