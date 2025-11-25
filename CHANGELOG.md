# Changelog

## Version 1.0.1 - Bug Fixes (2025-11-25)

### Critical Bug Fixes
All bugs discovered during installation testing have been permanently fixed in the codebase.

#### Database Connection Issues
- **Fixed:** 43 Service classes incorrectly using `Database::getInstance()` instead of `Database::getPdo()`
- **Fixed:** Type errors where PDO was expected but Database object was provided
- **Impact:** POS system, enrollment services, analytics, and 40+ other services now work correctly

#### Router Base Path Detection
- **Fixed:** Router using `empty()` instead of `isset()` for APP_BASE_PATH
- **Impact:** Application now correctly handles empty base path configuration

#### Translator Database Query
- **Fixed:** Translator using incorrect database method
- **Impact:** Multi-language support now works without errors

#### Error Handler Headers
- **Fixed:** Error handler attempting to set headers after output started
- **Impact:** Error pages now display correctly

#### Dashboard Null Safety
- **Fixed:** Dashboard accessing array keys on null database results
- **Impact:** Admin dashboard loads without errors

### Known Limitations
- Database error logging is temporarily disabled in Logger.php
- Will be re-enabled in future update with correct implementation

### Files Modified
- `app/Core/Router.php` - Line 47
- `app/Core/Translator.php` - Line 203
- `app/Core/ErrorHandler.php` - Lines 107-109
- `app/Controllers/Admin/DashboardController.php` - Lines 394, 411
- `app/Core/Logger.php` - Line 133
- 43 Service class files
- 3 Middleware files
- `.env` configuration

---

## Version 1.0.0 - Initial Release

Complete dive shop management system with 210+ database tables, comprehensive business intelligence, and club management features.
