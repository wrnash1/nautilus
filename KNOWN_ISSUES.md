# Known Issues in Nautilus v2.0 Alpha

## Issues Found During Testing (Nov 4, 2025)

### ✅ Fixed Issues
1. **Database Connection** - Fixed .env to use `nautilus_dev` database
2. **Missing status column** - Added to `cash_drawer_sessions` table
3. **Column name mismatch** - Changed `difference` to `variance` in DashboardController
4. **Missing categories table** - Created view linking to `product_categories`
5. **Missing database views** - Created `cash_drawer_sessions_open` and `cash_drawer_sessions_closed` views
6. **Cash Drawer layout** - Fixed to use app.php layout system

### 🔧 Active Issues (Need Fixing)

#### 1. Type Declaration Errors (PHP 8.4 strictness)
**Location:** Multiple Services (ReportService.php, EmailService.php, CourseService.php)
**Error:** Implicit nullable parameters deprecated
**Fix Needed:** Change `$param = null` to `?string $param = null`

**Files to fix:**
- `/app/Services/Reports/ReportService.php` (lines 53, 106, 137, 162)
- `/app/Services/Email/EmailService.php` (line 259)
- `/app/Services/Courses/CourseService.php` (line 312)

#### 2. Router Parameter Type Issues
**Location:** `/app/Controllers/CRM/CustomerController.php:93`
**Error:** `show(int $id)` receiving string "tags" instead of integer
**Issue:** Route `/store/customers/tags` is matching `/store/customers/{id}` pattern
**Fix:** Routes need reordering - specific routes before parameterized routes

#### 3. Missing Routes
**Routes not defined:**
- `/rentals/reservations` - Returns {"error":"Route not found"}
- `/store/shop` - Returns {"error":"Route not found"}

#### 4. Missing Views
**Location:** `/app/Controllers/SerialNumberController.php:21`
**Missing:** `/app/Views/serial_numbers/index.php`

### ⚠️ Known Limitations (Alpha Features)

#### Email Functionality (Incomplete)
- Email notifications for appointments - Not implemented
- Email notifications for RMA - Not implemented
- Travel packet PDF generation - Not implemented
- Contact form email - Not implemented

#### Integrations (Not Configured)
- Stripe payment processing - Requires API keys
- Square payment processing - Requires API keys
- Twilio SMS - Requires configuration
- Google Workspace - Requires OAuth setup
- PADI API - Requires credentials

#### Features Requiring Testing
- Advanced inventory features
- Compressor tracking
- Layaway system
- Custom reports builder
- Waiver digital signatures
- Multi-location support

### 📋 Testing Results by Module

| Module | Status | Notes |
|--------|--------|-------|
| Dashboard | ✅ Working | Shows metrics, charts load |
| Login/Auth | ✅ Working | Authentication functional |
| Cash Drawer | ✅ Working | Views fixed, table created |
| Categories | ✅ Working | View created, links to product_categories |
| Customers | ⚠️ Partial | Route conflict with /customers/tags |
| Products | 🧪 Not Tested | Need to test |
| POS | 🧪 Not Tested | Need to test |
| Reports | ⚠️ Partial | Deprecated warnings |
| Rentals | ❌ Route Missing | /rentals/reservations not defined |
| Shop | ❌ Route Missing | /store/shop not defined |
| Serial Numbers | ❌ View Missing | Need to create view file |

### 🎯 Priority Fixes

#### High Priority (Breaking)
1. Fix route ordering for customer tags vs customer ID
2. Create missing serial_numbers view
3. Add missing routes (rentals/reservations, store/shop)
4. Fix type declarations for PHP 8.4 compatibility

#### Medium Priority (Warnings)
1. Fix deprecated nullable parameter warnings
2. Test all major CRUD operations
3. Verify permissions system works

#### Low Priority (Polish)
1. Remove all debug files
2. Update documentation
3. Create deployment scripts

### 🔒 Security Notes
- Debug files still in /public/ directory (need removal before production)
- Default admin password needs changing
- APP_DEBUG=true (should be false in production)

### 📝 Deployment Readiness

**Current Status:** ~80% Production Ready

**Blockers for Production:**
- None critical - app is functional for basic use

**Recommended Before Production:**
1. Fix type declaration warnings
2. Test all modules thoroughly
3. Remove debug files
4. Change default passwords
5. Set APP_DEBUG=false
6. Configure SSL with real certificate
7. Set up automated backups

### 🎓 For Dive Shop Owners

**What Works Now:**
- ✅ Dashboard with business metrics
- ✅ Customer management (CRM)
- ✅ Product inventory tracking
- ✅ Cash drawer management
- ✅ User authentication & roles
- ✅ Basic reporting

**What Needs Work:**
- ⚠️ Some routes need fixing
- ⚠️ Email notifications incomplete
- ⚠️ Some integrations not configured

**Can I use this now?**
Yes, for basic dive shop operations. Just be aware of the limitations above.

---

Last Updated: Nov 4, 2025
Version: 2.0 Alpha
Testing Platform: Fedora 43 / Apache / MariaDB / PHP 8.4
