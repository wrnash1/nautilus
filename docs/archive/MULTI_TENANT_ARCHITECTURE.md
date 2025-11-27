# Multi-Tenant Architecture Guide

Complete guide to the multi-tenant SaaS architecture implementation in Nautilus.

## Table of Contents

1. [Overview](#overview)
2. [Architecture](#architecture)
3. [Database Schema](#database-schema)
4. [Tenant Isolation](#tenant-isolation)
5. [Tenant Identification](#tenant-identification)
6. [Usage & Examples](#usage--examples)
7. [Subscription Plans](#subscription-plans)
8. [Quotas & Limits](#quotas--limits)
9. [White-Label Features](#white-label-features)
10. [API Reference](#api-reference)

## Overview

Nautilus implements a **multi-tenant SaaS architecture** that allows multiple companies (tenants) to use the same application instance while maintaining complete data isolation. Each tenant has:

- Isolated data (products, customers, transactions)
- Custom subdomain (e.g., `acme.nautilus.com`)
- Optional custom domain (e.g., `shop.acmediving.com`)
- Subscription-based access with different plans
- Usage quotas and limits
- White-label customization options

### Key Features

- **Data Isolation**: Automatic tenant scoping for all queries
- **Subdomain Routing**: Each tenant gets a unique subdomain
- **Custom Domains**: Support for custom domain mapping
- **Subscription Management**: Flexible plans with different features
- **Usage Tracking**: Monitor resource usage for billing
- **Quota Enforcement**: Automatic limit checking
- **Activity Logging**: Complete audit trail per tenant
- **Onboarding Flow**: Guided setup for new tenants

## Architecture

### Components

```
┌─────────────────────────────────────────┐
│         TenantMiddleware                │
│  (Identifies tenant from domain)        │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│         TenantService                   │
│  (Manages tenant operations)            │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│         TenantDatabase                  │
│  (Automatic data isolation)             │
└──────────────┬──────────────────────────┘
               │
               ▼
┌─────────────────────────────────────────┐
│         Database Layer                  │
│  (Shared tables with tenant_id)         │
└─────────────────────────────────────────┘
```

### Request Flow

1. **Request arrives** → `https://acme.nautilus.com/products`
2. **TenantMiddleware** extracts subdomain "acme"
3. **Tenant identified** from database by subdomain
4. **Tenant context set** in session and constants
5. **All queries scoped** automatically by tenant_id
6. **Response returned** with tenant-specific data

## Database Schema

### Core Tables

#### tenants
Main tenant information table.

```sql
CREATE TABLE tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_uuid VARCHAR(36) UNIQUE NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    subdomain VARCHAR(100) UNIQUE,
    custom_domain VARCHAR(255) UNIQUE,
    status ENUM('active', 'suspended', 'trial', 'cancelled'),

    -- Subscription
    plan_id INT,
    subscription_status ENUM('active', 'past_due', 'cancelled', 'trialing'),
    trial_ends_at TIMESTAMP NULL,

    -- Limits
    max_users INT DEFAULT 10,
    max_storage_mb INT DEFAULT 1000,
    max_products INT DEFAULT 500,
    max_transactions_per_month INT DEFAULT 1000,

    -- White-label
    logo_url VARCHAR(500),
    primary_color VARCHAR(7) DEFAULT '#0066cc',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### subscription_plans
Available subscription plans.

```sql
CREATE TABLE subscription_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    plan_name VARCHAR(100) NOT NULL,
    plan_code VARCHAR(50) UNIQUE NOT NULL,
    monthly_price DECIMAL(10, 2) NOT NULL,
    yearly_price DECIMAL(10, 2) NOT NULL,

    max_users INT DEFAULT 10,
    max_storage_mb INT DEFAULT 1000,
    max_products INT DEFAULT 500,
    max_transactions_per_month INT DEFAULT 1000,

    features JSON
);
```

#### tenant_users
Maps users to tenants (supports users in multiple tenants).

```sql
CREATE TABLE tenant_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    user_id INT NOT NULL,
    role VARCHAR(50) DEFAULT 'user',
    is_owner BOOLEAN DEFAULT FALSE
);
```

#### tenant_usage
Tracks daily usage for billing.

```sql
CREATE TABLE tenant_usage (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    usage_date DATE NOT NULL,

    active_users INT DEFAULT 0,
    transactions_count INT DEFAULT 0,
    storage_used_mb INT DEFAULT 0,
    api_calls INT DEFAULT 0
);
```

### Tenant-Scoped Tables

These tables have `tenant_id` column added for isolation:

- users
- customers
- products
- product_categories
- pos_transactions
- courses
- equipment
- equipment_rentals

## Tenant Isolation

### Automatic Query Scoping

The `TenantDatabase` class automatically adds `tenant_id` conditions to queries:

```php
// Without scoping (old way)
$products = Database::fetchAll("SELECT * FROM products WHERE is_active = 1");

// With automatic scoping (new way)
$products = TenantDatabase::fetchAllTenant("SELECT * FROM products WHERE is_active = 1");
// Automatically becomes: SELECT * FROM products WHERE tenant_id = 123 AND is_active = 1
```

### Manual Tenant Context

```php
use App\Middleware\TenantMiddleware;

// Get current tenant ID
$tenantId = TenantMiddleware::getCurrentTenantId();

// Get full tenant data
$tenant = TenantMiddleware::getCurrentTenant();

// Require tenant context (throws exception if not present)
TenantMiddleware::requireTenant();
```

## Tenant Identification

### Subdomain-Based

```
https://acme.nautilus.com
        └── subdomain
```

The middleware extracts "acme" and looks up the tenant.

### Custom Domain

```
https://shop.acmediving.com
        └── custom domain
```

The middleware checks for exact domain match first.

### Configuration

Set the base domain in `.env`:

```env
BASE_DOMAIN=nautilus.com
```

## Usage & Examples

### Creating a New Tenant

```php
use App\Services\Tenant\TenantService;

$tenantService = new TenantService();

$result = $tenantService->createTenant([
    'company_name' => 'Acme Dive Shop',
    'subdomain' => 'acme', // Optional, auto-generated if not provided
    'contact_name' => 'John Doe',
    'contact_email' => 'john@acmediving.com',
    'contact_phone' => '+1-555-0100',
    'plan_id' => 2, // Professional plan
    'timezone' => 'America/New_York',
    'currency' => 'USD',
    'locale' => 'en_US'
]);

if ($result['success']) {
    echo "Tenant created with ID: " . $result['tenant_id'];
    echo "Subdomain: " . $result['subdomain'];
    echo "UUID: " . $result['tenant_uuid'];
}
```

### Querying with Tenant Scope

```php
use App\Core\TenantDatabase;

// Fetch products for current tenant only
$products = TenantDatabase::fetchAllTenant(
    "SELECT * FROM products WHERE is_active = 1 ORDER BY name"
);

// Insert product for current tenant
$productId = TenantDatabase::insertTenant('products', [
    'name' => 'Dive Mask',
    'sku' => 'MASK-001',
    'price' => 49.99,
    'is_active' => 1
]);
// tenant_id is automatically added

// Update product (only within tenant)
TenantDatabase::updateTenant(
    'products',
    ['price' => 54.99],
    'id = ?',
    [$productId]
);
// Automatically adds: AND tenant_id = ?
```

### Checking Quotas

```php
$tenantService = new TenantService();

// Check if tenant can add more users
if ($tenantService->checkQuota($tenantId, 'users')) {
    // Can add user
    $userService->createUser($userData);
} else {
    throw new Exception('User limit reached. Please upgrade your plan.');
}

// Check other quotas
$tenantService->checkQuota($tenantId, 'products');
$tenantService->checkQuota($tenantId, 'transactions');
$tenantService->checkQuota($tenantId, 'storage');
```

### Tracking Usage

```php
// Track usage (run daily via cron)
$tenantService->trackUsage($tenantId);

// This records:
// - Active user count
// - Transactions count and value
// - Products count
// - Customers count
// - Storage usage
```

### Activity Logging

```php
$tenantService->logActivity(
    $tenantId,
    $userId,
    'product_created',
    'product',
    $productId,
    'Created new product: Dive Mask',
    ['sku' => 'MASK-001', 'price' => 49.99]
);
```

## Subscription Plans

### Default Plans

#### Starter Plan ($29/month)
- 5 users
- 500 MB storage
- 250 products
- 500 transactions/month
- Basic analytics

#### Professional Plan ($79/month)
- 15 users
- 2 GB storage
- 1,000 products
- 2,500 transactions/month
- Advanced analytics
- Reports
- API access

#### Enterprise Plan ($199/month)
- 50 users
- 10 GB storage
- 5,000 products
- 10,000 transactions/month
- All features
- White-label
- Priority support

### Managing Subscriptions

```php
// Get all available plans
$plans = $tenantService->getAllPlans();

// Get specific plan
$plan = $tenantService->getSubscriptionPlan($planId);

// Upgrade tenant plan
$tenantService->updateSubscription($tenantId, $newPlanId);
```

### Plan Features

Plans can have feature flags stored in JSON:

```json
{
  "analytics": true,
  "reports": true,
  "api_access": true,
  "advanced_dashboard": true,
  "white_label": true,
  "priority_support": true
}
```

Check features:

```php
$plan = $tenantService->getSubscriptionPlan($tenant['plan_id']);
$features = json_decode($plan['features'], true);

if ($features['white_label']) {
    // Allow white-label customization
}
```

## Quotas & Limits

### Enforcing Limits

```php
use App\Services\Tenant\TenantService;

class ProductService {
    private TenantService $tenantService;

    public function createProduct($data) {
        $tenantId = TenantMiddleware::getCurrentTenantId();

        // Check quota before creating
        if (!$this->tenantService->checkQuota($tenantId, 'products')) {
            throw new Exception('Product limit reached for your plan. Please upgrade.');
        }

        // Proceed with creation
        $productId = TenantDatabase::insertTenant('products', $data);

        return $productId;
    }
}
```

### Quota Types

- **users**: Max active users
- **products**: Max active products
- **transactions**: Max transactions per month
- **storage**: Max storage in MB

### Soft vs Hard Limits

- **Soft limit**: Warning shown at 80%
- **Hard limit**: Operation blocked at 100%

```php
$usage = $tenantService->checkQuota($tenantId, 'users');
$current = $usage['current'];
$limit = $usage['limit'];
$percentage = ($current / $limit) * 100;

if ($percentage >= 100) {
    // Hard limit - block
    throw new Exception('Limit reached');
} elseif ($percentage >= 80) {
    // Soft limit - warn
    $this->showWarning('Approaching limit');
}
```

## White-Label Features

### Customization Options

Tenants can customize:

- Company logo
- Favicon
- Primary color
- Secondary color
- Email templates
- Invoice templates

### Setting Customizations

```php
// Update logo
$tenantService->updateTenant($tenantId, [
    'logo_url' => 'https://cdn.example.com/logos/acme.png',
    'favicon_url' => 'https://cdn.example.com/favicons/acme.ico',
    'primary_color' => '#1a5490',
    'secondary_color' => '#0d2b48'
]);
```

### Using Customizations

```php
$tenant = TenantMiddleware::getCurrentTenant();

// In HTML/CSS
echo '<style>
    :root {
        --primary-color: ' . htmlspecialchars($tenant['primary_color']) . ';
        --secondary-color: ' . htmlspecialchars($tenant['secondary_color']) . ';
    }
</style>';

// Logo
if ($tenant['logo_url']) {
    echo '<img src="' . htmlspecialchars($tenant['logo_url']) . '" alt="Logo">';
}
```

## API Reference

### TenantService

#### createTenant(array $data): array
Creates a new tenant.

**Parameters:**
- `company_name` (required): Company name
- `contact_email` (required): Contact email
- `contact_name`: Contact person name
- `contact_phone`: Phone number
- `plan_id`: Subscription plan ID (default: 1)
- `subdomain`: Custom subdomain (auto-generated if not provided)
- `custom_domain`: Custom domain
- `timezone`: Timezone (default: UTC)
- `locale`: Locale (default: en_US)
- `currency`: Currency (default: USD)

**Returns:**
```php
[
    'success' => true,
    'tenant_id' => 123,
    'tenant_uuid' => 'abc-def-ghi',
    'subdomain' => 'acme'
]
```

#### getTenantById(int $tenantId): ?array
Get tenant by ID.

#### getTenantByUUID(string $uuid): ?array
Get tenant by UUID.

#### getTenantBySubdomain(string $subdomain): ?array
Get tenant by subdomain.

#### updateTenant(int $tenantId, array $data): bool
Update tenant information.

#### updateSubscription(int $tenantId, int $planId): bool
Update subscription plan.

#### checkQuota(int $tenantId, string $quotaType): bool
Check if tenant is within quota limits.

#### trackUsage(int $tenantId): void
Track daily usage metrics.

#### logActivity(...): void
Log tenant activity for audit trail.

#### getSetting(int $tenantId, string $key, $default = null): mixed
Get tenant setting value.

#### setSetting(int $tenantId, string $key, $value, string $type = 'string'): void
Set tenant setting value.

### TenantMiddleware

#### handle(): bool
Process incoming request and identify tenant.

#### getCurrentTenant(): ?array
Get current tenant data (static).

#### getCurrentTenantId(): ?int
Get current tenant ID (static).

#### requireTenant(): void
Throw exception if not in tenant context.

### TenantDatabase

#### fetchAllTenant(string $sql, array $params = []): ?array
Fetch all records with automatic tenant scoping.

#### fetchOneTenant(string $sql, array $params = []): ?array
Fetch one record with automatic tenant scoping.

#### insertTenant(string $table, array $data): int
Insert record with automatic tenant_id.

#### updateTenant(string $table, array $data, string $where, array $params = []): int
Update records within tenant scope.

#### deleteTenant(string $table, string $where, array $params = []): int
Delete records within tenant scope.

## Best Practices

### 1. Always Use Tenant-Scoped Queries

```php
// ✅ Good
$products = TenantDatabase::fetchAllTenant("SELECT * FROM products");

// ❌ Bad
$products = Database::fetchAll("SELECT * FROM products");
```

### 2. Check Quotas Before Operations

```php
// ✅ Good
if ($tenantService->checkQuota($tenantId, 'products')) {
    $productService->create($data);
}

// ❌ Bad
$productService->create($data); // Might exceed quota
```

### 3. Log Important Activities

```php
// ✅ Good
$tenantService->logActivity(
    $tenantId,
    $userId,
    'product_deleted',
    'product',
    $productId,
    'Product deleted: ' . $productName
);
```

### 4. Handle Tenant Context Errors

```php
// ✅ Good
try {
    TenantMiddleware::requireTenant();
    // Tenant-specific operation
} catch (Exception $e) {
    // Handle missing tenant context
    header('Location: /login');
    exit;
}
```

### 5. Use Tenant Settings for Customization

```php
// ✅ Good - Stored in database, customizable per tenant
$taxRate = $tenantService->getSetting($tenantId, 'default_tax_rate', 0);

// ❌ Bad - Hard-coded, same for all tenants
$taxRate = 0.08;
```

## Migration Guide

### Adding Tenant Support to Existing Tables

```sql
-- Add tenant_id column
ALTER TABLE your_table ADD COLUMN tenant_id INT NULL AFTER id;

-- Add foreign key
ALTER TABLE your_table ADD CONSTRAINT fk_your_table_tenant
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

-- Add index
ALTER TABLE your_table ADD INDEX idx_tenant_id (tenant_id);

-- Update TenantDatabase to include this table
```

### Migrating Existing Data

```php
// If you have existing data, assign it to a default tenant
$defaultTenantId = 1;

Database::query(
    "UPDATE your_table SET tenant_id = ? WHERE tenant_id IS NULL",
    [$defaultTenantId]
);
```

## Troubleshooting

### Tenant Not Found

**Symptom**: "Tenant context required" error

**Solution**:
1. Verify subdomain is correct
2. Check tenants table for matching subdomain
3. Ensure BASE_DOMAIN is set in .env

### Data Showing from Wrong Tenant

**Symptom**: Seeing data from other tenants

**Solution**:
1. Use TenantDatabase methods, not raw Database
2. Check that table has tenant_id column
3. Verify table is in getTenantScopedTables() list

### Quota Not Enforcing

**Symptom**: Can exceed limits

**Solution**:
1. Add checkQuota() calls before operations
2. Verify tenant limits in database
3. Check subscription plan limits

## Security Considerations

1. **Data Isolation**: Never bypass tenant scoping in production
2. **Quota Enforcement**: Always check before allowing operations
3. **Activity Logging**: Log all sensitive operations
4. **Access Control**: Verify user belongs to tenant
5. **Input Validation**: Validate all tenant data inputs

## Performance Tips

1. **Index tenant_id**: Ensure all tenant-scoped tables have index on tenant_id
2. **Cache Tenant Data**: Cache frequently accessed tenant settings
3. **Optimize Queries**: Use proper indexes and query structure
4. **Monitor Usage**: Track slow queries in tenant context

## License

Part of Nautilus Dive Shop Management System
Copyright (c) 2025
