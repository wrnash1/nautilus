# Nautilus Dive Shop Management System v3.0
## Enterprise SaaS - Production Deployment Guide

**Status:** ✅ **100% PRODUCTION READY**

**Date:** 2025-11-09

---

## Table of Contents

1. [Overview](#overview)
2. [New Enterprise Features](#new-enterprise-features)
3. [System Requirements](#system-requirements)
4. [Installation Guide](#installation-guide)
5. [Configuration](#configuration)
6. [Security Hardening](#security-hardening)
7. [Performance Optimization](#performance-optimization)
8. [Monitoring & Maintenance](#monitoring--maintenance)
9. [Troubleshooting](#troubleshooting)
10. [API Documentation](#api-documentation)

---

## Overview

Nautilus v3.0 is a complete enterprise SaaS platform for dive shop management with the following capabilities:

### Core Features (v2.0)
- ✅ Multi-tenant architecture
- ✅ POS system with rental integration
- ✅ Course and certification management
- ✅ Inventory and warehouse management
- ✅ Customer relationship management (CRM)
- ✅ E-commerce storefront with AI recommendations
- ✅ Payment processing (Stripe, PayPal, Square)
- ✅ Marketing automation and loyalty programs
- ✅ Staff management and commission tracking
- ✅ Comprehensive reporting and analytics

### New Enterprise Features (v3.0)
- ✅ **Enterprise SSO & SAML Authentication**
- ✅ **Multi-Currency & Tax Management**
- ✅ **Advanced Analytics Dashboard**
- ✅ **White-Label Customization**
- ✅ **Subscription Billing & Metering**
- ✅ **Automated Tenant Provisioning**
- ✅ **Real-Time WebSocket Notifications**
- ✅ **API Rate Limiting & Usage Tracking**
- ✅ **SaaS Administration Panel**
- ✅ **Scheduled Data Import/Export**
- ✅ **Redis Caching Layer**
- ✅ **Health Check & Monitoring Endpoints**

---

## New Enterprise Features

### 1. Enterprise SSO & SAML Authentication

**Location:** [app/Services/Auth/SsoService.php](app/Services/Auth/SsoService.php)

**Supported Providers:**
- SAML 2.0 (Generic)
- Microsoft Azure AD
- Google Workspace
- Okta
- OneLogin
- OAuth 2.0 / OpenID Connect

**Setup Example:**

```php
use App\Services\Auth\SsoService;

$ssoService = new SsoService();

// Configure SAML
$ssoService->configureSaml($tenantId, [
    'entity_id' => 'https://idp.example.com',
    'idp_sso_url' => 'https://idp.example.com/sso',
    'idp_certificate' => '-----BEGIN CERTIFICATE-----...',
    'sp_entity_id' => 'https://yourdomain.com/saml',
    'sp_acs_url' => 'https://yourdomain.com/saml/acs'
]);

// Initiate SSO login
$result = $ssoService->initiateSamlAuth($tenantId);
header('Location: ' . $result['redirect_url']);
```

**Benefits:**
- Centralized authentication
- Single sign-on experience
- Automatic user provisioning
- Enhanced security

---

### 2. Multi-Currency & Tax Management

**Location:** [app/Services/Payment/MultiCurrencyService.php](app/Services/Payment/MultiCurrencyService.php)

**Supported Currencies:**
- USD, EUR, GBP, JPY, CAD, AUD, CHF, CNY, INR, MXN

**Tax Systems:**
- US Sales Tax (state + local)
- EU VAT
- Canadian GST/PST/HST
- Australian GST

**Usage Example:**

```php
use App\Services\Payment\MultiCurrencyService;

$currencyService = new MultiCurrencyService();

// Convert prices
$priceInEUR = $currencyService->convert(100.00, 'USD', 'EUR');

// Get localized pricing
$localPrice = $currencyService->getLocalizedPrice($productId, 'EUR');

// Calculate tax
$taxCalculation = $currencyService->calculateTax($items, $billingAddress, $shippingAddress);

// Update exchange rates (run daily via cron)
$currencyService->updateExchangeRates();
```

---

### 3. Advanced Analytics Dashboard

**Location:** [app/Services/Analytics/AdvancedAnalyticsService.php](app/Services/Analytics/AdvancedAnalyticsService.php)

**Features:**
- Customer Lifetime Value (LTV) calculation
- Cohort analysis
- Churn prediction
- Revenue forecasting
- Product performance analysis
- Sales funnel tracking

**Usage Example:**

```php
use App\Services\Analytics\AdvancedAnalyticsService;

$analytics = new AdvancedAnalyticsService();

// Get dashboard metrics
$metrics = $analytics->getDashboardMetrics('30d');

// Calculate LTV
$ltv = $analytics->calculateCustomerLTV();

// Predict churn
$atRisk = $analytics->predictChurn(90);

// Forecast revenue
$forecast = $analytics->forecastRevenue(6);
```

---

### 4. White-Label Customization

**Location:** [app/Services/Tenant/WhiteLabelService.php](app/Services/Tenant/WhiteLabelService.php)

**Customization Options:**
- Custom logo and favicon
- Color scheme (primary, secondary, accent)
- Custom CSS
- Custom domain with verification
- Email template customization
- Custom terminology

**Setup Example:**

```php
use App\Services\Tenant\WhiteLabelService;

$whiteLabel = new WhiteLabelService();

// Update branding
$whiteLabel->updateBranding($tenantId, [
    'company_name' => 'Ocean Divers Pro',
    'logo_url' => '/uploads/logos/ocean-divers-logo.png',
    'primary_color' => '#0066CC',
    'secondary_color' => '#FF6600',
    'theme_mode' => 'light'
]);

// Set custom domain
$result = $whiteLabel->setCustomDomain($tenantId, 'diving.oceandivers.com');

// Verify domain
$whiteLabel->verifyCustomDomain($tenantId);
```

---

### 5. Subscription Billing & Metering

**Location:** [app/Services/Payment/SubscriptionBillingService.php](app/Services/Payment/SubscriptionBillingService.php)

**Features:**
- Flexible subscription plans
- Usage-based billing
- Metered billing (API calls, storage, users)
- Automatic payment retry
- Dunning management
- Proration for upgrades/downgrades

**Subscription Plans:**

| Plan | Price | Features |
|------|-------|----------|
| Starter | $29.99/mo | 5 users, 500 products, Basic features |
| Professional | $79.99/mo | 20 users, 2000 products, Advanced features |
| Enterprise | $199.99/mo | Unlimited, All features, White-label |

**Usage Example:**

```php
use App\Services\Payment\SubscriptionBillingService;

$billing = new SubscriptionBillingService();

// Create subscription
$subscription = $billing->createSubscription($tenantId, $planId, $paymentMethod);

// Record usage
$billing->recordUsage($tenantId, 'api_calls', 1000);

// Process recurring billing (cron job)
$results = $billing->processRecurringBilling();

// Upgrade subscription
$billing->upgradeSubscription($tenantId, $newPlanId);
```

---

### 6. Automated Tenant Provisioning

**Location:** [app/Services/Tenant/TenantProvisioningService.php](app/Services/Tenant/TenantProvisioningService.php)

**Features:**
- Automated tenant creation
- Default data seeding
- Onboarding workflow
- Demo data generation
- Tenant suspension/reactivation

**Provisioning Example:**

```php
use App\Services\Tenant\TenantProvisioningService;

$provisioning = new TenantProvisioningService();

// Create new tenant
$result = $provisioning->provisionTenant([
    'company_name' => 'Coral Reef Divers',
    'subdomain' => 'coralreef',
    'email' => 'admin@coralreef.com',
    'plan_id' => 1,
    'include_demo_data' => true
]);

// Track onboarding progress
$progress = $provisioning->getOnboardingProgress($tenantId);
```

---

### 7. Real-Time WebSocket Notifications

**Location:** [app/Services/Notifications/WebSocketService.php](app/Services/Notifications/WebSocketService.php)

**Features:**
- Real-time push notifications
- User presence tracking
- Live updates
- Chat functionality
- Broadcasting to channels

**Usage Example:**

```php
use App\Services\Notifications\WebSocketService;

$ws = new WebSocketService();

// Notify user
$ws->notifyUser($userId, 'new_order', $orderData);

// Broadcast to tenant
$ws->broadcastToTenant($tenantId, 'low_stock', $productData);

// Update presence
$ws->updatePresence($userId, 'online');

// Get online users
$onlineUsers = $ws->getOnlineUsers($tenantId);
```

---

### 8. API Rate Limiting & Usage Tracking

**Location:** [app/Services/API/RateLimitService.php](app/Services/API/RateLimitService.php)

**Features:**
- Token bucket algorithm
- Per-tenant rate limits
- Usage tracking and analytics
- Automatic throttling
- Burst allowance

**Default Limits:**
- Starter: 500 requests/hour
- Professional: 1,000 requests/hour
- Enterprise: 5,000 requests/hour

**Usage Example:**

```php
use App\Services\API\RateLimitService;

$rateLimit = new RateLimitService();

// Check rate limit
$check = $rateLimit->checkLimit($tenantId, '/api/products');

if (!$check['allowed']) {
    http_response_code(429);
    die('Rate limit exceeded');
}

// Get usage stats
$stats = $rateLimit->getUsageStats($tenantId, '24h');
```

---

### 9. Health Check & Monitoring

**Location:** [app/Services/System/HealthCheckService.php](app/Services/System/HealthCheckService.php)

**Endpoints:**

| Endpoint | Purpose |
|----------|---------|
| `/health` | Comprehensive health check |
| `/health/liveness` | Kubernetes liveness probe |
| `/health/readiness` | Kubernetes readiness probe |

**Usage Example:**

```php
use App\Services\System\HealthCheckService;

$health = new HealthCheckService();

// Comprehensive health check
$status = $health->checkHealth();

// Performance metrics
$performance = $health->getPerformanceMetrics();
```

---

## System Requirements

### Production Server

- **OS:** Ubuntu 20.04 LTS or higher, Rocky Linux 8+
- **Web Server:** Apache 2.4+ or Nginx 1.18+
- **PHP:** 8.1 or higher
- **Database:** MySQL 8.0+ or MariaDB 10.6+
- **Redis:** 6.0+ (recommended)
- **Memory:** Minimum 4GB RAM (8GB+ recommended)
- **Storage:** 50GB+ SSD
- **CPU:** 2+ cores

### PHP Extensions Required

```bash
php -m | grep -E 'pdo|mysqli|mbstring|openssl|curl|json|gd|zip|redis|intl'
```

Required extensions:
- pdo_mysql
- mysqli
- mbstring
- openssl
- curl
- json
- gd
- zip
- redis (optional but recommended)
- intl
- xml
- fileinfo

---

## Installation Guide

### 1. Clone Repository

```bash
cd /var/www
git clone https://github.com/yourusername/nautilus.git
cd nautilus
```

### 2. Install Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Set Permissions

```bash
chown -R www-data:www-data /var/www/nautilus
chmod -R 755 /var/www/nautilus
chmod -R 775 storage/
chmod -R 775 public/uploads/
```

### 4. Configure Environment

```bash
cp .env.example .env
nano .env
```

**Required `.env` configuration:**

```ini
# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nautilus_prod
DB_USERNAME=nautilus_user
DB_PASSWORD=your_secure_password

# Redis (optional but recommended)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
REDIS_DATABASE=0

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Security
ENCRYPTION_KEY=your_32_character_encryption_key
SESSION_SECURE=true
SESSION_HTTPONLY=true
SESSION_SAMESITE=Strict

# Email
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="Nautilus System"

# Payment Gateways
STRIPE_PUBLIC_KEY=pk_live_xxx
STRIPE_SECRET_KEY=sk_live_xxx
PAYPAL_CLIENT_ID=xxx
PAYPAL_SECRET=xxx
SQUARE_ACCESS_TOKEN=xxx

# Twilio (SMS)
TWILIO_SID=xxx
TWILIO_AUTH_TOKEN=xxx
TWILIO_PHONE_NUMBER=+1234567890

# WebSocket
WEBSOCKET_URL=wss://yourdomain.com:8080
```

### 5. Run Migrations

```bash
php scripts/run-migrations.php
```

### 6. Create Platform Admin

```bash
php scripts/create-platform-admin.php
```

### 7. Configure Web Server

**Apache Configuration:**

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias *.yourdomain.com

    DocumentRoot /var/www/nautilus/public

    <Directory /var/www/nautilus/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/nautilus-error.log
    CustomLog ${APACHE_LOG_DIR}/nautilus-access.log combined

    # Redirect to HTTPS
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}$1 [R=301,L]
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    ServerAlias *.yourdomain.com

    DocumentRoot /var/www/nautilus/public

    SSLEngine on
    SSLCertificateFile /etc/ssl/certs/yourdomain.crt
    SSLCertificateKeyFile /etc/ssl/private/yourdomain.key
    SSLCertificateChainFile /etc/ssl/certs/chain.crt

    <Directory /var/www/nautilus/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/nautilus-ssl-error.log
    CustomLog ${APACHE_LOG_DIR}/nautilus-ssl-access.log combined
</VirtualHost>
```

### 8. Install SSL Certificate

```bash
# Using Let's Encrypt
certbot --apache -d yourdomain.com -d *.yourdomain.com
```

### 9. Set Up Cron Jobs

```bash
crontab -e
```

Add the following:

```cron
# Update exchange rates (daily at 2 AM)
0 2 * * * cd /var/www/nautilus && php scripts/update-exchange-rates.php

# Process recurring billing (daily at 3 AM)
0 3 * * * cd /var/www/nautilus && php scripts/process-billing.php

# Process scheduled exports (every hour)
0 * * * * cd /var/www/nautilus && php scripts/process-exports.php

# Clean old cache files (daily at 4 AM)
0 4 * * * cd /var/www/nautilus && php scripts/clean-cache.php

# Database backup (daily at 1 AM)
0 1 * * * cd /var/www/nautilus && php scripts/backup-database.php

# Log rotation (weekly)
0 0 * * 0 cd /var/www/nautilus && php scripts/rotate-logs.php
```

---

## Configuration

### Redis Configuration

Install Redis:

```bash
sudo apt install redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
```

Configure Redis for production in `/etc/redis/redis.conf`:

```conf
maxmemory 2gb
maxmemory-policy allkeys-lru
save 900 1
save 300 10
save 60 10000
```

---

## Security Hardening

### 1. File Permissions

```bash
find /var/www/nautilus -type d -exec chmod 755 {} \;
find /var/www/nautilus -type f -exec chmod 644 {} \;
chmod -R 775 storage/
chmod -R 775 public/uploads/
```

### 2. Disable Directory Listing

In `.htaccess`:

```apache
Options -Indexes
```

### 3. Configure Firewall

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable
```

### 4. Enable Security Headers

Add to Apache config:

```apache
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-Content-Type-Options "nosniff"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Content-Security-Policy "default-src 'self'"
```

---

## Performance Optimization

### 1. Enable OPcache

In `php.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.revalidate_freq=0
```

### 2. Enable Gzip Compression

In Apache config:

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

### 3. Browser Caching

In `.htaccess`:

```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## Monitoring & Maintenance

### Health Check Endpoints

- **Full Health Check:** `GET /health`
- **Liveness Probe:** `GET /health/liveness`
- **Readiness Probe:** `GET /health/readiness`

### Monitoring Tools

1. **Application Monitoring:**
   - New Relic
   - Datadog
   - Sentry (error tracking)

2. **Server Monitoring:**
   - Prometheus + Grafana
   - Nagios
   - Zabbix

3. **Log Aggregation:**
   - ELK Stack (Elasticsearch, Logstash, Kibana)
   - Splunk
   - Papertrail

---

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check DB credentials in `.env`
   - Verify MySQL is running: `sudo systemctl status mysql`
   - Check firewall rules

2. **Redis Not Available**
   - Check if Redis is running: `sudo systemctl status redis`
   - Verify Redis host/port in `.env`
   - Application will fallback to file-based caching

3. **Slow Performance**
   - Enable OPcache
   - Configure Redis caching
   - Optimize database indexes
   - Check slow query log

---

## Success Metrics

The application is ready for production when:

- ✅ All health checks pass
- ✅ SSL certificate is installed
- ✅ Cron jobs are configured
- ✅ Redis is operational
- ✅ Database backups are automated
- ✅ Monitoring is configured
- ✅ Error tracking is enabled
- ✅ Performance metrics are acceptable (<200ms average response time)

---

## Support

For production support:
- Email: support@nautilus.com
- Documentation: https://docs.nautilus.com
- GitHub Issues: https://github.com/yourusername/nautilus/issues

---

**Application Version:** 3.0.0
**Last Updated:** 2025-11-09
**License:** Proprietary

---

## 🚀 YOU ARE NOW 100% READY FOR PRODUCTION! 🚀
