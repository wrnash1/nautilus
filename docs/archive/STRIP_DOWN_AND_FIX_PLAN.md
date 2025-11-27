# 🔧 STRIP DOWN & FIX - Implementation Plan

**Date:** November 20, 2025  
**Time:** 9:22 AM CST  
**Goal:** Minimal installer + Working core application

---

## 📋 **PHASE 1: Strip Down Installer**

### **Current Installer Steps:**
1. System Requirements Check
2. Database Setup
3. Company Information ← REMOVE THIS
4. Admin Account Creation

### **New Installer Steps:**
1. System Requirements Check
2. Database Setup
3. Admin Account Creation
4. Done! → Redirect to /store/admin/settings

### **What to Remove:**
- Company name input
- Company email input
- Company phone input
- Company address inputs
- All company-related fields

### **What to Keep:**
- Database host, port, name, user, password
- Admin email, password, name
- System checks

---

## 📋 **PHASE 2: Fix Core Application**

### **Fix 1: Auto-Login Security Issue**
**Problem:** Clicking "Staff Login" goes directly to dashboard

**Root Cause:** Need to investigate AuthMiddleware

**Fix:**
- Check session handling
- Ensure login page shows first
- Require credentials

### **Fix 2: Settings Page**
**Problem:** Redirect loops on /store/admin/settings/tax and /integrations

**Root Cause:** Controllers redirecting to themselves

**Fix:**
- Remove redirect loops
- Create proper views
- Or remove non-working pages

### **Fix 3: Remove Broken Features**
**Remove:**
- Demo data controller (tenant_id errors)
- Non-working settings pages
- Customer portal routes (Phase 2)

**Keep:**
- Basic settings page
- Company info form
- Logo upload

### **Fix 4: Database Warnings**
**Problem:** Still 40 warnings

**Root Cause:** Migration 100 has syntax error

**Fix:**
- Debug migration 100
- Fix SQL syntax
- Ensure it runs successfully

---

## 📋 **PHASE 3: First-Time Setup Wizard**

### **After Installation:**
```
User installs → Creates admin account
  ↓
First login → Redirect to /store/admin/settings
  ↓
Show: "Welcome! Complete your setup"
  ↓
User enters:
  - Company name
  - Email
  - Phone
  - Address
  - Logo (optional)
  ↓
Save to system_settings
  ↓
Redirect to dashboard
```

---

## 🎯 **SUCCESS CRITERIA**

### **Installer:**
- ✅ 3 steps only (System, Database, Admin)
- ✅ No company info questions
- ✅ Fast and simple
- ✅ 0 migration warnings

### **Application:**
- ✅ Login page shows (no auto-login)
- ✅ Requires username/password
- ✅ Settings page works
- ✅ Can update company info
- ✅ No redirect loops
- ✅ No errors

### **Database:**
- ✅ All migrations run successfully
- ✅ 0 warnings
- ✅ system_settings table exists
- ✅ Default values populated

---

## 📝 **FILES TO MODIFY**

### **Installer:**
1. `public/install.php` - Remove company info step
2. `database/migrations/100_fix_all_migration_warnings.sql` - Fix syntax

### **Application:**
3. `app/Controllers/Auth/AuthController.php` - Fix auto-login
4. `app/Controllers/Admin/SettingsController.php` - Fix redirects
5. `app/Controllers/Admin/DemoDataController.php` - Remove or fix
6. `routes/web.php` - Clean up routes

### **Views:**
7. `app/Views/admin/settings/index.php` - Ensure it works
8. `app/Views/auth/login.php` - Ensure it shows

---

## ⏱️ **ESTIMATED TIME**

- Phase 1 (Installer): 30 minutes
- Phase 2 (Core fixes): 2-3 hours
- Phase 3 (Testing): 30 minutes

**Total: 3-4 hours**

---

## 🚀 **EXECUTION ORDER**

1. ✅ Create this plan document
2. ⏳ Strip down installer
3. ⏳ Fix migration 100 syntax
4. ⏳ Fix auto-login issue
5. ⏳ Fix settings page
6. ⏳ Remove broken features
7. ⏳ Test clean install
8. ✅ Done!

---

**Status:** Starting implementation now...
