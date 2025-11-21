# 🏢 Nautilus - Enterprise Multi-Tenant SaaS Platform

**Architecture:** Multi-tenant dive shop management system  
**Model:** Each dive shop = 1 tenant with complete isolation  
**Components:** Public Storefront + Customer Portal + Staff Backend

---

## 🎯 **Application Structure**

### **3 Main Areas:**

#### 1. Public Storefront (`/`)
- **Purpose:** Customer-facing e-commerce
- **Features:**
  - Browse products (dive gear, courses, trips)
  - Online booking
  - Course registration
  - Trip reservations
  - Contact forms
- **Branding:** Uses tenant's company name, logo, colors
- **No Sidebar:** Clean public design

#### 2. Customer Portal (`/portal`)
- **Purpose:** Logged-in customer self-service
- **Features:**
  - View certifications
  - Booking history
  - Upcoming courses/trips
  - Download waivers
  - Update profile
  - Loyalty points
- **Branding:** Tenant-specific
- **Navigation:** Customer-friendly menu

#### 3. Staff Backend (`/store`)
- **Purpose:** Internal operations management
- **Features:**
  - POS system
  - Inventory management
  - Customer management
  - Course scheduling
  - Reporting & analytics
  - Settings & configuration
- **Branding:** Tenant-specific
- **Navigation:** Admin sidebar (what you're seeing now)

---

## 🔧 **Critical Fixes Required**

### **Fix 1: Route Structure** ✅
**Problem:** Root URL (`/`) goes to admin dashboard

**Solution:**
```
/                    → Public storefront homepage
/shop               → Product catalog
/courses            → Course listings
/trips              → Trip listings
/portal/*           → Customer portal (login required)
/store/*            → Staff backend (admin sidebar)
/install.php        → Installer
```

### **Fix 2: Tenant-Specific Branding** ✅
**Problem:** Hardcoded "Nautilus Dive Shop" everywhere

**Solution:**
- Load company name from `system_settings` table
- Each tenant has their own settings
- Settings populated during installation
- Dynamic loading in all views

**Implementation:**
```php
// In all views, replace hardcoded name with:
<?php $company = getCompanyInfo(); ?>
<title><?= $company['name'] ?></title>
<h1><?= $company['name'] ?></h1>
```

### **Fix 3: Database Migration Warnings** ⚠️
**Problem:** 39 migrations with foreign key warnings

**Root Cause:**
- Missing `tenant_id` in some tables
- Tables created out of order
- Foreign key references to non-existent columns

**Solution:**
- Create migration fix file
- Add missing `tenant_id` columns
- Fix foreign key constraints
- Ensure multi-tenant isolation

### **Fix 4: Demo Data** 📊
**Problem:** No sample data for testing

**Solution:**
- Add demo data option to installer
- Create sample tenant with:
  - Products (BCDs, regulators, wetsuits, fins)
  - Courses (Open Water, Advanced, Rescue)
  - Trips (local dives, liveaboards)
  - Customers (10-20 sample)
  - Staff (admin, instructor, sales)
  - Transactions

### **Fix 5: Admin Settings Panel** ⚙️
**Problem:** No way to configure tenant settings

**Solution:**
- Create `/store/admin/settings` page
- Sections:
  - **General:** Company name, contact info
  - **Branding:** Logo, colors, favicon
  - **Storefront:** Theme, homepage content
  - **Email:** SMTP settings
  - **Payments:** Stripe, Square, BTCPay
  - **Integrations:** PADI, Google, QuickBooks

---

## 📋 **Implementation Plan**

### **Phase 1: Critical Fixes (2-3 hours)**

#### 1.1 Fix Routing ✅
- Create public homepage controller
- Create customer portal controller
- Update routes to separate public/portal/admin
- Only show sidebar in `/store/*` routes

#### 1.2 Fix Tenant Branding ✅
- Update all views to use `getCompanyInfo()`
- Remove hardcoded "Nautilus Dive Shop"
- Load settings from database
- Test with different tenant names

#### 1.3 Fix Database Warnings ⚠️
- Create `100_fix_multi_tenant_constraints.sql`
- Add missing `tenant_id` columns
- Fix foreign key constraints
- Re-run on existing database

### **Phase 2: Essential Features (3-4 hours)**

#### 2.1 Public Storefront
- Homepage with hero section
- Product catalog
- Course listings
- Trip calendar
- Contact page
- Tenant-specific branding

#### 2.2 Customer Portal
- Login/registration
- Dashboard
- Certifications
- Bookings
- Profile management
- Loyalty points

#### 2.3 Admin Settings
- Settings controller
- Settings views
- Company info form
- Branding upload (logo, favicon)
- Color picker
- Save to database

### **Phase 3: Demo Data (1 hour)**

#### 3.1 Demo Data SQL
- Sample products
- Sample courses
- Sample trips
- Sample customers
- Sample transactions
- Sample staff

#### 3.2 Installer Update
- Add Step 4: Demo Data
- Checkbox to enable
- Run demo SQL if selected
- Success message

---

## 🚀 **Starting Implementation Now**

I'll implement in this order:

1. ✅ **Fix routing** - Separate public/portal/admin
2. ✅ **Create public homepage** - No sidebar
3. ✅ **Fix tenant branding** - Dynamic company name
4. ✅ **Create admin settings** - Configuration panel
5. ✅ **Fix database warnings** - Multi-tenant constraints
6. ✅ **Add demo data** - Sample content

**Estimated completion:** 5-6 hours

---

## 📊 **Expected Results**

### **After Fixes:**

**Visiting `https://nautilus.local/`:**
- ✅ Public storefront homepage
- ✅ No sidebar
- ✅ Tenant's company name
- ✅ Professional design
- ✅ Shop, Courses, Trips menus

**Visiting `https://nautilus.local/portal`:**
- ✅ Customer login
- ✅ Customer dashboard
- ✅ Certifications, bookings
- ✅ Tenant branding

**Visiting `https://nautilus.local/store`:**
- ✅ Staff login required
- ✅ Admin sidebar (current view)
- ✅ POS, inventory, customers
- ✅ Settings panel

**Database:**
- ✅ No migration warnings
- ✅ All foreign keys valid
- ✅ Multi-tenant isolation
- ✅ 418 tables working

---

## 🎯 **Multi-Tenant Features**

### **Tenant Isolation:**
- ✅ Each tenant has own data
- ✅ `tenant_id` on all tables
- ✅ Queries filtered by tenant
- ✅ Complete data separation

### **Tenant Customization:**
- ✅ Company name
- ✅ Logo & favicon
- ✅ Brand colors
- ✅ Email templates
- ✅ Domain mapping (future)

### **Tenant Management:**
- ✅ Create new tenants
- ✅ Manage subscriptions
- ✅ Usage analytics
- ✅ Backup per tenant

---

**Starting implementation now!** 🚀
