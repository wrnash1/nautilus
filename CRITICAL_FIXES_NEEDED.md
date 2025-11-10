# Critical Fixes Needed for Production Release

## Issues Identified

### 1. Broken Routes (High Priority)
The following URLs return "Route not found" errors:

- `/air-fills/create` → Should be `/store/air-fills/create`
- `/air-fills/quick-fill` → Should be `/store/air-fills/quick-fill`
- `/waivers` → Should be `/store/waivers`
- `/dive-sites/create` → Should be `/store/dive-sites/create`
- `/inventory/serial-numbers/create` → Should be `/store/serial-numbers/create`
- `/inventory/serial-numbers/scan` → Should be `/store/serial-numbers/scan` (route missing)
- `/courses` → Should be `/store/courses`

**Root Cause:** Frontend views are using URLs without the `/store/` prefix

### 2. Missing Features (High Priority)

#### A. Company Settings Management
- No UI to enter company information (address, phone, email)
- Need: Settings page under `/store/admin/settings/company`
- Required fields:
  - Company name
  - Business address
  - Phone number(s)
  - Email address(es)
  - Website
  - Tax ID
  - Logo upload
  - Operating hours

#### B. Newsletter Subscription
- Subscribe button exists but functionality broken
- Need: NewsletterController with:
  - Email collection
  - Subscription confirmation
  - Unsubscribe functionality
  - Integration with email campaigns

#### C. Help/Support System
- No help pages or documentation links
- No "Contact Support" functionality
- Need:
  - Help center with FAQ
  - Support ticket system (exists but not linked)
  - Documentation pages
  - Live chat (future)

#### D. Feedback System
- Routes exist but may not be working properly
- Need to test and verify:
  - Feedback submission form
  - Feedback management (staff)
  - Status updates
  - Email notifications

### 3. URL Structure Issues (Medium Priority)

The application has inconsistent URL structure:
- Public storefront: `/`, `/shop`, `/about`
- Staff backend: `/store/*`
- Customer portal: `/account/*`, `/customer/portal/*` (redundant)
- Install: `/install`

**Recommendation:** Standardize on:
- Public: `/`, `/shop/*`, `/about`, `/contact`
- Staff: `/admin/*` (change from `/store/*`)
- Customer: `/portal/*` (consolidate)
- Install: `/install.php` (standalone)

### 4. Missing Controllers/Views

Need to create:
1. **CompanySettingsController** - Manage business information
2. **NewsletterController** - Newsletter subscriptions
3. **HelpController** - Help center and documentation
4. **ScannerController** - Barcode/serial number scanning

### 5. Integration Issues

- Views linking to non-existent routes
- Middleware redirecting incorrectly
- Missing error handling for "Route not found"

---

## Recommended Fix Approach

### Phase 1: Fix Critical Routes (1-2 hours)
1. Update all view files to use correct `/store/` prefix
2. Add missing routes for scanning features
3. Test all navigation links

### Phase 2: Add Company Settings (1 hour)
1. Create CompanySettingsController
2. Add database migration for company_settings table
3. Create settings form view
4. Link from main settings page

### Phase 3: Implement Newsletter (30 min)
1. Create NewsletterController
2. Add newsletter_subscriptions table
3. Create subscription form
4. Add confirmation emails

### Phase 4: Add Help System (1 hour)
1. Create HelpController with FAQ
2. Add help pages
3. Link from navigation
4. Create support ticket submission form

### Phase 5: Testing (2 hours)
1. Test every navigation link
2. Test all forms
3. Test user workflows end-to-end
4. Fix any remaining issues

### Phase 6: Documentation Update
1. Update installation guides
2. Update feature list
3. Create admin user guide
4. Create troubleshooting guide

---

## Immediate Action Items

1. ✅ Document all issues (this file)
2. ⏭️ Fix URL prefixes in views
3. ⏭️ Add missing routes
4. ⏭️ Create company settings functionality
5. ⏭️ Implement newsletter subscription
6. ⏭️ Add help/support system
7. ⏭️ Comprehensive testing
8. ⏭️ Deploy to production

---

**Priority:** CRITICAL
**Target Completion:** Before distributing to other dive shops
**Status:** Work in Progress
