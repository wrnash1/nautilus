# Sidebar Menu Updates - Complete Feature Navigation

## Summary
Updated the Nautilus V6 sidebar menu to include ALL available features and modules. The menu now provides complete navigation to every functional area of the application.

## Changes Made

### 1. Added New Menu Items

#### **Dive Sites Management**
- **Menu Item:** Dive Sites
- **Icon:** `bi-geo-alt`
- **Route:** `/dive-sites`
- **Controller:** `DiveSitesController.php`
- **Features:**
  - Manage dive site database
  - Track site details (depth, conditions, marine life)
  - Real-time weather integration
  - Link sites to dive trips

#### **Serial Number Tracking**
- **Menu Item:** Serial Numbers
- **Icon:** `bi-upc-scan`
- **Route:** `/serial-numbers`
- **Controller:** `SerialNumberController.php`
- **Features:**
  - Track serial numbers for high-value equipment
  - Full item lifecycle management
  - Transfer history
  - Warranty tracking

#### **Vendor Catalog Import**
- **Menu Item:** Vendor Import
- **Icon:** `bi-cloud-download`
- **Route:** `/vendor-catalog/import`
- **Controller:** `VendorCatalogController.php`
- **Features:**
  - Import product catalogs from vendors
  - Support for CSV, Excel, JSON, and API formats
  - Auto-detection and field mapping
  - Pre-configured templates for major brands (Scubapro, Aqua Lung, Mares)

---

### 2. Added Marketing Submenu
- **Menu Item:** Marketing (collapsible)
- **Icon:** `bi-megaphone`
- **Subitems:**
  - **Loyalty Program** → `/marketing/loyalty`
  - **Coupons** → `/marketing/coupons`
  - **Email Campaigns** → `/marketing/campaigns`
  - **Referrals** → `/marketing/referrals`

**Controllers:**
- `Marketing/LoyaltyController.php`
- `Marketing/CouponController.php`
- `Marketing/CampaignController.php`
- `Marketing/ReferralController.php`

---

### 3. Added Content Management (CMS) Submenu
- **Menu Item:** Content (collapsible)
- **Icon:** `bi-file-earmark-text`
- **Subitems:**
  - **Pages** → `/cms/pages`
  - **Blog Posts** → `/cms/blog`

**Controllers:**
- `CMS/PageController.php`
- `CMS/BlogController.php`

**Features:**
- Static page management
- Blog post management with categories and tags
- Publishing workflow
- Media library

---

### 4. Added Integrations Submenu
- **Menu Item:** Integrations (collapsible)
- **Icon:** `bi-plugin`
- **Subitems:**
  - **Wave Accounting** → `/integrations/wave`
  - **QuickBooks** → `/integrations/quickbooks`
  - **Google Workspace** → `/integrations/google-workspace` **(NEW)**

**Controllers:**
- `Integrations/WaveController.php`
- `Integrations/QuickBooksController.php`
- `Integrations/GoogleWorkspaceController.php` **(CREATED)**

**Google Workspace Features:**
- Google Calendar integration (sync course schedules and trips)
- Gmail integration (send emails through Google)
- Google Drive integration (backups and documents)
- Service account authentication
- Configuration management

---

### 5. Added API Management
- **Menu Item:** API Tokens
- **Icon:** `bi-key`
- **Route:** `/api/tokens`
- **Controller:** `API/TokenController.php` **(CREATED)**

**Features:**
- Create and manage API tokens
- Token scopes (read, write, delete)
- Expiration management
- Token revocation
- Usage tracking
- Link to API Documentation

**Also Added:**
- **API Documentation** → `/api/docs`
- **Controller:** `API/DocumentationController.php` **(CREATED)**

---

### 6. Added Roles & Permissions
- **Menu Item:** Roles & Permissions
- **Icon:** `bi-shield-lock`
- **Route:** `/admin/roles`
- **Controller:** `Admin/RoleController.php`

**Features:**
- Role-based access control (RBAC) management
- Create and edit roles
- Assign granular permissions
- User role assignment

---

### 7. Menu Organization Improvements

#### **Section Dividers Added**
The menu is now organized into logical sections with visual dividers:

1. **Core Operations** (no divider)
   - Dashboard, POS, Customers, Products, Categories, Vendors

2. **Reports** (collapsible submenu)
   - Sales, Customer, Product, Payment, Inventory, Low Stock

3. **Services & Operations**
   - Rentals (submenu), Air Fills, Courses (submenu), Trips (submenu), Work Orders, Orders, Online Store

4. **Specialized Features** (divider)
   - Dive Sites, Serial Numbers, Vendor Import

5. **Marketing & Content** (divider)
   - Marketing (submenu), CMS (submenu), Staff (submenu)

6. **System & Administration** (divider)
   - Integrations (submenu), API Tokens, Settings, User Management, Roles & Permissions

---

## Routes Added

All new menu items have corresponding routes added to `routes/web.php`:

### Google Workspace Integration Routes
```php
$router->get('/integrations/google-workspace', 'Integrations\GoogleWorkspaceController@index', [AuthMiddleware::class]);
$router->post('/integrations/google-workspace/config', 'Integrations\GoogleWorkspaceController@saveConfig', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/integrations/google-workspace/test', 'Integrations\GoogleWorkspaceController@testConnection', [AuthMiddleware::class, CsrfMiddleware::class]);
```

### Dive Sites Routes
```php
$router->get('/dive-sites', 'DiveSitesController@index', [AuthMiddleware::class]);
$router->get('/dive-sites/create', 'DiveSitesController@create', [AuthMiddleware::class]);
$router->post('/dive-sites', 'DiveSitesController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/dive-sites/{id}', 'DiveSitesController@show', [AuthMiddleware::class]);
$router->get('/dive-sites/{id}/edit', 'DiveSitesController@edit', [AuthMiddleware::class]);
$router->post('/dive-sites/{id}', 'DiveSitesController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/dive-sites/{id}/delete', 'DiveSitesController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/dive-sites/{id}/weather', 'DiveSitesController@getWeather', [AuthMiddleware::class]);
```

### Serial Number Routes
```php
$router->get('/serial-numbers', 'SerialNumberController@index', [AuthMiddleware::class]);
$router->get('/serial-numbers/create', 'SerialNumberController@create', [AuthMiddleware::class]);
$router->post('/serial-numbers', 'SerialNumberController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/serial-numbers/{id}', 'SerialNumberController@show', [AuthMiddleware::class]);
$router->get('/serial-numbers/{id}/edit', 'SerialNumberController@edit', [AuthMiddleware::class]);
$router->post('/serial-numbers/{id}', 'SerialNumberController@update', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/serial-numbers/{id}/delete', 'SerialNumberController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/serial-numbers/search', 'SerialNumberController@search', [AuthMiddleware::class]);
$router->get('/serial-numbers/{id}/history', 'SerialNumberController@history', [AuthMiddleware::class]);
```

### Vendor Catalog Import Routes
```php
$router->get('/vendor-catalog/import', 'VendorCatalogController@import', [AuthMiddleware::class]);
$router->post('/vendor-catalog/upload', 'VendorCatalogController@upload', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/vendor-catalog/preview', 'VendorCatalogController@preview', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/vendor-catalog/process', 'VendorCatalogController@process', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/vendor-catalog/templates', 'VendorCatalogController@templates', [AuthMiddleware::class]);
$router->get('/vendor-catalog/download-template/{vendor}', 'VendorCatalogController@downloadTemplate', [AuthMiddleware::class]);
```

### API Token Management Routes
```php
$router->get('/api/tokens', 'API\TokenController@index', [AuthMiddleware::class]);
$router->get('/api/tokens/create', 'API\TokenController@create', [AuthMiddleware::class]);
$router->post('/api/tokens', 'API\TokenController@store', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/api/tokens/{id}/revoke', 'API\TokenController@revoke', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->post('/api/tokens/{id}/delete', 'API\TokenController@delete', [AuthMiddleware::class, CsrfMiddleware::class]);
$router->get('/api/docs', 'API\DocumentationController@index', [AuthMiddleware::class]);
```

---

## New Controllers Created

### 1. GoogleWorkspaceController.php
**Path:** `/app/Controllers/Integrations/GoogleWorkspaceController.php`

**Methods:**
- `index()` - Display integration dashboard
- `saveConfig()` - Save Google credentials and settings
- `testConnection()` - Test API connection

**Features:**
- Upload Google service account JSON credentials
- Enable/disable Calendar, Gmail, and Drive services
- Test connection functionality
- Status indicators for each service
- Setup instructions

---

### 2. TokenController.php
**Path:** `/app/Controllers/API/TokenController.php`

**Methods:**
- `index()` - Display all API tokens
- `create()` - Show create token form
- `store()` - Create new API token
- `revoke()` - Revoke a token
- `delete()` - Delete a token permanently

**Features:**
- Generate secure random tokens (64 characters, SHA-256 hashed)
- Token expiration management (30 days, 90 days, 1 year, 2 years, never)
- Scope-based permissions (read, write, delete)
- One-time token display (security best practice)
- Usage tracking (last used timestamp)
- Token status badges (Active, Revoked, Expired)

---

### 3. DocumentationController.php
**Path:** `/app/Controllers/API/DocumentationController.php`

**Methods:**
- `index()` - Display API documentation

**Features:**
- Complete API endpoint reference
- Authentication instructions
- Request/response format examples
- Rate limiting documentation
- Interactive endpoint accordion
- Copy-paste cURL examples

---

## Permission Structure

All menu items respect the existing permission system via `hasPermission()` checks:

- `dashboard.view` - Dashboard, Reports, Dive Sites
- `pos.view` - Point of Sale
- `customers.view` - Customers
- `products.view` - Products, Categories, Vendors, Serial Numbers, Vendor Import
- `rentals.view` - Rental equipment and reservations
- `air_fills.view` - Air Fills
- `courses.view` - Course catalog, schedules, enrollments
- `trips.view` - Trip catalog, schedules, bookings
- `workorders.view` - Work Orders
- `orders.view` - Orders
- `staff.view` - Staff management
- `admin.settings` - Settings
- `admin.users` - User Management
- `admin.roles` - Roles & Permissions
- `admin.integrations` - Integrations submenu
- `admin.api` - API Tokens

---

## Complete Menu Structure

```
Navigation Menu
├── Dashboard
├── Point of Sale
├── Customers
├── Products
├── Categories
├── Vendors
├── Reports ▼
│   ├── Sales Report
│   ├── Customer Report
│   ├── Product Report
│   ├── Payment Report
│   ├── Inventory Report
│   └── Low Stock Alert
├── Rentals ▼
│   ├── Equipment
│   └── Reservations
├── Air Fills
├── Courses ▼
│   ├── Course Catalog
│   ├── Schedules
│   └── Enrollments
├── Trips ▼
│   ├── Trip Catalog
│   ├── Schedules
│   └── Bookings
├── Work Orders
├── Orders
├── Online Store
├── ──────────────
├── Dive Sites (NEW)
├── Serial Numbers (NEW)
├── Vendor Import (NEW)
├── ──────────────
├── Marketing ▼ (NEW)
│   ├── Loyalty Program
│   ├── Coupons
│   ├── Email Campaigns
│   └── Referrals
├── Content ▼ (NEW)
│   ├── Pages
│   └── Blog Posts
├── Staff ▼
│   ├── Employees
│   ├── Schedules
│   ├── Time Clock
│   └── Commissions
├── ──────────────
├── Integrations ▼ (EXPANDED)
│   ├── Wave Accounting
│   ├── QuickBooks
│   └── Google Workspace (NEW)
├── API Tokens (NEW)
├── Settings
├── User Management
└── Roles & Permissions (NEW)
```

---

## Files Modified

1. **app/Views/layouts/app.php** - Updated sidebar menu with all new items
2. **routes/web.php** - Added routes for all new menu items

---

## Files Created

1. **app/Controllers/Integrations/GoogleWorkspaceController.php** - Google Workspace integration management
2. **app/Controllers/API/TokenController.php** - API token management
3. **app/Controllers/API/DocumentationController.php** - API documentation viewer

---

## Testing Recommendations

Before deploying to production, test the following:

### 1. Navigation Testing
- [ ] Click each menu item and verify it loads without errors
- [ ] Verify collapsible submenus expand/collapse properly
- [ ] Check mobile responsiveness of the menu
- [ ] Test sidebar collapse/expand functionality

### 2. Permission Testing
- [ ] Test with different user roles to ensure menu items show/hide correctly
- [ ] Verify unauthorized users cannot access restricted pages

### 3. New Feature Testing
- [ ] **Google Workspace:** Upload credentials, test connection
- [ ] **API Tokens:** Create, revoke, and delete tokens
- [ ] **API Docs:** Verify documentation displays correctly
- [ ] **Dive Sites:** CRUD operations, weather fetching
- [ ] **Serial Numbers:** CRUD operations, history tracking
- [ ] **Vendor Import:** Upload test file, preview, process

### 4. Integration Testing
- [ ] Verify routes work correctly
- [ ] Check that controllers load views properly
- [ ] Test form submissions with CSRF protection
- [ ] Verify database tables exist for new features

---

## Database Requirements

Ensure the following tables exist (should be created by migrations):

- `api_tokens` - For API token management
- `dive_sites` - For dive site management
- `dive_site_conditions` - For weather tracking
- `serial_numbers` - For serial number tracking
- `serial_number_history` - For transfer history
- `pages` - For CMS pages
- `blog_posts` - For blog management
- `loyalty_programs` - For loyalty management
- `coupons` - For coupon management
- `campaigns` - For email campaigns
- `referrals` - For referral tracking

---

## Next Steps

1. **Run the application** and test all new menu items
2. **Verify permissions** are properly configured in the database
3. **Test new controllers** to ensure they work correctly
4. **Check for missing views** that may need to be created
5. **Update user documentation** with new features
6. **Train staff** on new functionality

---

## Completion Status

✅ **Sidebar Menu:** Complete - All features represented
✅ **Routes:** Complete - All routes added
✅ **Controllers:** Complete - Missing controllers created
✅ **Documentation:** Complete - This summary document

The Nautilus V6 sidebar menu now provides comprehensive navigation to all 30+ major features and subsystems of the application!
