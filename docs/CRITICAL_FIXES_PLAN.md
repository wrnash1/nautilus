# 🔧 Critical Issues Fix Plan

**Date:** November 20, 2025  
**Priority:** HIGH - Production Blockers

---

## 🚨 **Issues Identified**

### 1. Database Migration Warnings (39 warnings)
**Problem:** Foreign key constraints failing, missing columns, syntax errors

**Root Causes:**
- Missing `tenant_id` columns in some tables
- Tables referenced before they're created
- Foreign key references to non-existent columns
- Syntax errors in some migrations

**Fix Required:**
- Add `tenant_id` to all tables
- Reorder migrations to create referenced tables first
- Fix syntax errors
- Update foreign key references

### 2. Sidebar Showing on Public Pages
**Problem:** Admin sidebar visible on public-facing pages

**Root Cause:**
- Root URL (`/`) redirects to `/store/dashboard` (admin area)
- No public storefront landing page

**Fix Required:**
- Create public storefront homepage
- Separate public and admin routes
- Only show sidebar in `/store/*` admin routes

### 3. Business Name Not Displaying
**Problem:** Shows "Nautilus Dive Shop" instead of company name from installation

**Root Cause:**
- Settings not being read from database
- Hardcoded company name in views
- Missing settings service initialization

**Fix Required:**
- Read company name from `system_settings` or `company_settings` table
- Update all views to use dynamic company name
- Ensure settings are loaded on every page

### 4. No Demo Data Option
**Problem:** Installer doesn't offer demo data installation

**Fix Required:**
- Add demo data option to installer (Step 4)
- Create demo data SQL file
- Include sample customers, products, transactions

### 5. No Admin Control Panel
**Problem:** No settings/configuration interface

**Fix Required:**
- Create admin settings page
- Add storefront configuration
- Add company branding settings
- Add general settings management

---

## 🔧 **Fixes to Implement**

### Fix 1: Add Missing tenant_id Columns

**File:** `database/migrations/001b_add_tenant_id_to_all_tables.sql` (NEW)

```sql
-- Add tenant_id to all tables that are missing it

ALTER TABLE customers ADD COLUMN tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE customers ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE customers ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

ALTER TABLE customer_tags ADD COLUMN tenant_id INT UNSIGNED NOT NULL DEFAULT 1 AFTER id;
ALTER TABLE customer_tags ADD INDEX idx_tenant_id (tenant_id);
ALTER TABLE customer_tags ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;

-- Add to all other tables as needed
```

### Fix 2: Create Public Storefront Homepage

**File:** `app/Views/public/index.php` (NEW)

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($companyName) ?> - Dive Shop</title>
    <!-- No sidebar, public-facing design -->
</head>
<body>
    <!-- Public storefront content -->
</body>
</html>
```

### Fix 3: Load Company Settings Dynamically

**File:** `app/Core/Settings.php` (NEW)

```php
<?php
namespace App\Core;

class Settings {
    private static $settings = null;
    
    public static function get($key, $default = null) {
        if (self::$settings === null) {
            self::load();
        }
        return self::$settings[$key] ?? $default;
    }
    
    private static function load() {
        // Load from database
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT setting_key, setting_value FROM system_settings");
        self::$settings = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            self::$settings[$row['setting_key']] = $row['setting_value'];
        }
    }
}
```

### Fix 4: Add Demo Data to Installer

**File:** `public/install.php` - Add Step 4

```php
// After admin account creation
if ($step == 4) {
    echo '<h3>Step 4: Demo Data (Optional)</h3>';
    echo '<p>Would you like to install demo data for testing?</p>';
    echo '<label><input type="checkbox" name="install_demo" value="1"> Install demo data</label>';
    echo '<p><small>Demo data includes sample customers, products, and transactions.</small></p>';
}
```

### Fix 5: Create Admin Settings Page

**File:** `app/Controllers/Admin/SettingsController.php`

```php
<?php
namespace App\Controllers\Admin;

class SettingsController {
    public function index() {
        // Show all settings
    }
    
    public function updateGeneral() {
        // Update company name, logo, etc.
    }
    
    public function updateStorefront() {
        // Update public storefront settings
    }
}
```

---

## 📋 **Implementation Order**

### Phase 1: Critical Fixes (Do First)
1. ✅ Fix database migrations (add tenant_id)
2. ✅ Create public homepage (no sidebar)
3. ✅ Load company name dynamically
4. ✅ Add demo data option to installer

### Phase 2: Admin Features
5. ✅ Create admin settings controller
6. ✅ Create settings UI
7. ✅ Add storefront configuration
8. ✅ Add company branding settings

---

## 🎯 **Expected Results After Fixes**

### Database:
- ✅ All migrations run without warnings
- ✅ All tables have tenant_id
- ✅ All foreign keys valid
- ✅ 418 tables created successfully

### Public Pages:
- ✅ Root URL shows public storefront (no sidebar)
- ✅ Company name displays correctly
- ✅ Professional public-facing design

### Admin Area:
- ✅ Sidebar only shows in `/store/*` routes
- ✅ Settings page accessible
- ✅ Company branding configurable
- ✅ Demo data available

### Installer:
- ✅ Step 4: Demo data option
- ✅ All migrations run cleanly
- ✅ Settings populated correctly

---

## 📝 **Files to Create/Modify**

### New Files:
1. `database/migrations/001b_add_tenant_id_to_all_tables.sql`
2. `database/demo_data.sql`
3. `app/Core/Settings.php`
4. `app/Views/public/index.php`
5. `app/Views/public/layout.php`
6. `app/Controllers/PublicController.php`
7. `app/Controllers/Admin/SettingsController.php`
8. `app/Views/admin/settings/index.php`

### Modified Files:
1. `public/install.php` - Add demo data step
2. `routes/web.php` - Add public routes
3. `app/Views/layouts/app.php` - Only show sidebar in admin
4. All views - Use dynamic company name

---

## ⚠️ **Migration Warnings to Fix**

Based on the installer output, these migrations need fixes:

1. `002_create_customer_tables.sql` - Add tenant_id
2. `014_enhance_certifications_and_travel.sql` - Syntax error
3. `016_add_branding_and_logo_support.sql` - Syntax error
4. `025_create_storefront_theme_system.sql` - Syntax error
5. `030_create_communication_system.sql` - Syntax error
6. `032_add_certification_agency_branding.sql` - Missing column
7. `038_create_compressor_tracking_system.sql` - Syntax error
8. `040_customer_tags_and_linking.sql` - Table doesn't exist
9. `055_feedback_ticket_system.sql` - Syntax error
10. `056_notification_system.sql` - Syntax error
11. And 29 more...

---

## 🚀 **Next Steps**

1. **Review this plan** - Confirm approach
2. **Fix migrations** - Add tenant_id, fix syntax
3. **Create public homepage** - No sidebar
4. **Add settings system** - Dynamic company name
5. **Add demo data** - Testing capability
6. **Test thoroughly** - Verify all fixes

---

**Status:** Ready to implement  
**Priority:** HIGH  
**Estimated Time:** 2-3 hours
