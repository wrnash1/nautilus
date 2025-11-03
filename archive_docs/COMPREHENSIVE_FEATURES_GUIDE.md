# Nautilus Dive Shop Management System - Complete Features Guide

## Overview

This document provides a comprehensive overview of all features added to the Nautilus Dive Shop Management System across three development phases. The system now includes 10 major feature sets that enhance both staff productivity and customer experience.

## Table of Contents

1. [Phase 1: Core Management Features](#phase-1-core-management-features)
2. [Phase 2: Business Intelligence & Administration](#phase-2-business-intelligence--administration)
3. [Phase 3: Customer Portal & Productivity Tools](#phase-3-customer-portal--productivity-tools)
4. [Installation & Configuration](#installation--configuration)
5. [Security Features](#security-features)
6. [Performance Optimizations](#performance-optimizations)
7. [API Endpoints Reference](#api-endpoints-reference)
8. [Testing Checklist](#testing-checklist)

---

## Phase 1: Core Management Features

### 1. Notifications System

**Purpose**: In-app notification management for staff members

**Files Created**:
- [app/Controllers/NotificationsController.php](app/Controllers/NotificationsController.php)
- [app/Views/notifications/index.php](app/Views/notifications/index.php)

**Features**:
- Real-time in-app notifications
- Bulk actions (mark all as read, delete selected)
- AJAX-powered updates without page reload
- Notification types: info, success, warning, error
- Automatic cleanup of old notifications
- Unread notification counter

**Key Endpoints**:
```
GET  /store/notifications           - List all notifications
POST /store/notifications/{id}/read - Mark as read
POST /store/notifications/mark-all-read - Mark all as read
POST /store/notifications/{id}/delete - Delete notification
```

**Usage Example**:
```php
// Create a notification
$stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
$stmt->execute([1, 'Low Stock Alert', 'Product XYZ is running low', 'warning']);
```

---

### 2. Appointments System

**Purpose**: Scheduling system for equipment servicing, training sessions, and consultations

**Files Created**:
- [app/Services/Appointments/AppointmentService.php](app/Services/Appointments/AppointmentService.php)
- [app/Controllers/AppointmentsController.php](app/Controllers/AppointmentsController.php)
- [app/Views/appointments/index.php](app/Views/appointments/index.php)
- [app/Views/appointments/create.php](app/Views/appointments/create.php)
- [app/Views/appointments/edit.php](app/Views/appointments/edit.php)
- [app/Views/appointments/view.php](app/Views/appointments/view.php)

**Features**:
- Conflict detection to prevent double-booking
- Multiple appointment types (equipment_service, consultation, training, other)
- Status tracking (scheduled, confirmed, completed, cancelled, no_show)
- Staff assignment with availability checking
- Calendar integration data export
- Email reminders (when EmailService is configured)
- Notes and internal comments

**Key Methods**:
```php
// Check for scheduling conflicts
$service->hasConflict($startTime, $endTime, $assignedTo, $excludeId);

// Get appointments for a specific date
$service->getByDate($date, $assignedTo);

// Get calendar data for integration
$controller->calendar(); // Returns JSON for frontend calendars
```

**Appointment Types**:
- `equipment_service` - Equipment maintenance/servicing
- `consultation` - Customer consultations
- `training` - Training sessions
- `other` - Other appointment types

---

### 3. Documents Management System

**Purpose**: Secure file storage with version control and access tracking

**Files Created**:
- [app/Services/Documents/DocumentService.php](app/Services/Documents/DocumentService.php)
- [app/Controllers/DocumentsController.php](app/Controllers/DocumentsController.php)
- [app/Views/documents/index.php](app/Views/documents/index.php)
- [app/Views/documents/create.php](app/Views/documents/create.php)
- [app/Views/documents/view.php](app/Views/documents/view.php)

**Features**:
- Secure file storage outside web root
- Version control for document updates
- Tag-based organization
- Full-text search capability
- Access logging for compliance
- File type validation and size limits (50MB default)
- Related entity linking (customers, orders, courses, trips)
- Expiration date tracking
- Public/private access control

**Security Features**:
- Files stored in `/storage/documents/` (outside public directory)
- Unique filename generation with timestamps
- Extension whitelist validation
- Permission-based access control
- Download tracking in audit log

**Supported File Types**:
- Documents: PDF, DOC, DOCX, XLS, XLSX
- Images: JPG, JPEG, PNG, GIF
- Text: TXT, CSV

**Usage Example**:
```php
// Upload a document
$documentId = $documentService->create([
    'name' => 'Liability Waiver',
    'description' => 'Customer liability waiver form',
    'tags' => 'waiver,legal',
    'entity_type' => 'customer',
    'entity_id' => 123,
    'uploaded_by' => Auth::userId()
], $_FILES['file']);

// Download a document
$controller->download($documentId);
```

---

## Phase 2: Business Intelligence & Administration

### 4. Email Service

**Purpose**: Centralized email delivery with template support

**Files Created**:
- [app/Services/Email/EmailService.php](app/Services/Email/EmailService.php)
- [app/Views/emails/base.php](app/Views/emails/base.php)
- [app/Views/emails/appointment_reminder.php](app/Views/emails/appointment_reminder.php)
- [app/Views/emails/low_stock_alert.php](app/Views/emails/low_stock_alert.php)
- [app/Views/emails/order_confirmation.php](app/Views/emails/order_confirmation.php)
- [app/Views/emails/trip_reminder.php](app/Views/emails/trip_reminder.php)

**Features**:
- Dual mode: PHPMailer (SMTP) or native PHP mail()
- HTML email templates with responsive design
- Template variable substitution
- Attachment support
- Email queue for bulk sending
- Configuration via environment variables
- Test email functionality

**Configuration** (.env):
```env
MAIL_DRIVER=smtp              # smtp or mail
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls           # tls or ssl
MAIL_FROM_ADDRESS=noreply@nautilus.com
MAIL_FROM_NAME="Nautilus Dive Shop"
```

**Email Templates**:
1. **appointment_reminder.php** - 24-hour appointment reminders
2. **low_stock_alert.php** - Inventory alerts for staff
3. **order_confirmation.php** - Customer order receipts
4. **trip_reminder.php** - Dive trip reminders

**Usage Example**:
```php
$emailService = new EmailService();

// Send using template
$emailService->sendTemplate(
    'customer@example.com',
    'appointment_reminder',
    [
        'firstName' => 'John',
        'appointmentDate' => '2025-11-01',
        'appointmentTime' => '10:00 AM',
        'appointmentType' => 'Equipment Service'
    ]
);

// Send plain email
$emailService->send(
    'manager@nautilus.com',
    'Low Stock Alert',
    '<p>Product <strong>Tank Rental</strong> is running low.</p>'
);
```

---

### 5. Advanced Reports Dashboard

**Purpose**: Comprehensive business intelligence and analytics

**Files Created**:
- [app/Controllers/Reports/ReportsDashboardController.php](app/Controllers/Reports/ReportsDashboardController.php)
- [app/Views/reports/dashboard.php](app/Views/reports/dashboard.php)

**Features**:
- Multi-module revenue tracking (retail, rentals, courses, trips, air fills)
- Date range filtering with quick presets (Today, This Week, This Month, This Year)
- Top performers analysis (products, customers, courses)
- Interactive charts using Chart.js
- Export to CSV functionality
- Inventory insights (low stock, turnover)
- Customer analytics (new vs returning, lifetime value)
- Responsive dashboard layout

**Metrics Tracked**:
- **Revenue**: Total revenue breakdown by module
- **Growth**: Period-over-period comparison
- **Top Products**: Best-selling items by revenue and quantity
- **Top Customers**: Highest spending customers
- **Inventory**: Stock levels and turnover rates
- **Courses**: Enrollment and completion rates
- **Trips**: Booking rates and popular destinations
- **Customers**: New registrations and retention

**Chart Types**:
- Line charts for revenue trends
- Doughnut charts for revenue distribution
- Bar charts for top performers

**Access**: `/store/reports/dashboard`

**Export Example**:
```
GET /store/reports/dashboard?export=csv&start_date=2025-01-01&end_date=2025-12-31
```

---

### 6. Audit Log System

**Purpose**: Complete audit trail for compliance and security

**Files Created**:
- [app/Services/Audit/AuditService.php](app/Services/Audit/AuditService.php)
- [app/Controllers/Admin/AuditLogController.php](app/Controllers/Admin/AuditLogController.php)
- [app/Views/admin/audit/index.php](app/Views/admin/audit/index.php)
- [app/Views/admin/audit/view.php](app/Views/admin/audit/view.php)

**Features**:
- Comprehensive activity logging
- Change tracking (old values → new values in JSON)
- IP address and user agent capture
- Search and filter by user, action, entity type, date
- Pagination for large datasets
- CSV export for compliance reporting
- Automatic cleanup of old logs (configurable retention)
- Entity history view

**Actions Logged**:
- `create` - Record creation
- `update` - Record modifications (with diff)
- `delete` - Record deletion
- `view` - Sensitive record access
- `login` / `logout` - Authentication events
- `export` - Data exports

**Helper Methods**:
```php
$auditService = new AuditService();

// Log different actions
$auditService->logCreate($userId, 'customer', $customerId, $customerData);
$auditService->logUpdate($userId, 'order', $orderId, $oldData, $newData);
$auditService->logDelete($userId, 'product', $productId, $productData);
$auditService->logLogin($userId);
$auditService->logView($userId, 'customer', $customerId);
$auditService->logExport($userId, 'orders', ['status' => 'completed']);
```

**Filtering Example**:
```php
// Get logs for specific user
$logs = $auditService->getLogs(['user_id' => 5], 50, 0);

// Get entity history
$history = $auditService->getEntityHistory('order', 123, 20);

// Get activity summary
$summary = $auditService->getActionSummary('2025-01-01', '2025-12-31');
```

**Data Retention**:
```php
// Delete logs older than 365 days
$deletedCount = $auditService->deleteOldLogs(365);
```

---

### 7. Settings Management

**Purpose**: Centralized application configuration

**Files Created**:
- [app/Services/Settings/SettingsService.php](app/Services/Settings/SettingsService.php)
- [app/Controllers/Admin/SystemSettingsController.php](app/Controllers/Admin/SystemSettingsController.php)
- [app/Views/admin/settings/index.php](app/Views/admin/settings/index.php)

**Features**:
- Category-based organization
- Type-safe storage (string, integer, boolean, json, encrypted)
- In-memory caching for performance
- Bulk update capability
- Setting descriptions and validation
- Email test functionality
- Backup and restore settings

**Setting Categories**:
- `general` - Site name, timezone, language
- `email` - SMTP configuration
- `notifications` - Notification preferences
- `business` - Tax rates, currency, business hours
- `inventory` - Low stock thresholds, reorder points
- `security` - Password policies, session timeout
- `features` - Feature flags and toggles

**Usage Example**:
```php
$settingsService = new SettingsService();

// Get a setting
$siteName = $settingsService->get('site_name', 'general', 'Nautilus Dive Shop');
$lowStockThreshold = $settingsService->get('low_stock_threshold', 'inventory', 10);

// Set a setting
$settingsService->set('site_name', 'Nautilus Dive Center', 'general', 'string');

// Get all settings in a category
$emailSettings = $settingsService->getCategory('email');

// Set encrypted value (for sensitive data)
$settingsService->set('api_key', 'secret123', 'integrations', 'encrypted');
```

**Performance Note**: Settings are cached in memory after first retrieval, reducing database queries.

---

## Phase 3: Customer Portal & Productivity Tools

### 8. Customer Portal Dashboard

**Purpose**: Self-service portal for customers to manage their account and view activity

**Files Created**:
- [app/Services/Customer/CustomerPortalService.php](app/Services/Customer/CustomerPortalService.php)
- [app/Controllers/Customer/PortalDashboardController.php](app/Controllers/Customer/PortalDashboardController.php)
- [app/Views/customer/portal/dashboard.php](app/Views/customer/portal/dashboard.php)

**Features**:
- Personalized welcome dashboard
- Recent orders with status tracking
- Upcoming courses and enrollment
- Scheduled dive trips
- Active equipment rentals
- Certification history and digital certificates
- Loyalty points balance
- Upcoming appointments
- Customer statistics (lifetime spending, courses completed, trips taken)
- Profile management
- Quick action buttons

**Dashboard Components**:
1. **Welcome Section**: Personalized greeting with customer name and member since date
2. **Statistics Cards**:
   - Total orders
   - Lifetime spending
   - Courses completed
   - Trips taken
3. **Recent Orders**: Last 5 orders with status badges
4. **Upcoming Events**: Next 5 courses and trips
5. **Active Rentals**: Current equipment rentals with due dates
6. **Certifications**: Earned certifications with issue dates
7. **Quick Actions**: Common tasks (book course, rent equipment, view documents)

**Access**: `/customer/portal/dashboard` (requires customer authentication)

**Statistics Displayed**:
```php
[
    'total_orders' => 15,
    'total_spent' => 2450.00,
    'courses_completed' => 3,
    'trips_taken' => 5,
    'active_rentals' => 2,
    'loyalty_points' => 245
]
```

**Modern Design Features**:
- Gradient backgrounds
- Card-based layout
- Status badges with color coding
- Responsive grid system
- Empty states with helpful messages
- Interactive hover effects

---

### 9. Global Search System

**Purpose**: Unified search across all system modules

**Files Created**:
- [app/Services/Search/GlobalSearchService.php](app/Services/Search/GlobalSearchService.php)
- [app/Controllers/SearchController.php](app/Controllers/SearchController.php)
- [app/Views/search/results.php](app/Views/search/results.php)

**Features**:
- Search across 7 modules simultaneously
- Autocomplete suggestions with debouncing
- Module-based filtering
- Quick ID lookup
- Relevance scoring
- Result grouping by module
- Click-through to detail pages
- Recent searches tracking

**Searchable Modules**:
1. **Customers**: Name, email, phone, member number
2. **Products**: Name, SKU, description, category
3. **Orders**: Order number, customer name, status
4. **Courses**: Name, description, instructor, location
5. **Trips**: Name, destination, description
6. **Documents**: Title, description, tags, filename
7. **Rentals**: Equipment name, customer, rental number

**Search Endpoints**:
```
GET /store/search/results?q=diving+gear&modules[]=products&modules[]=orders
GET /store/search/suggestions?q=tank
GET /store/search/quickFind?id=ORD-12345
```

**Usage Example**:
```php
$searchService = new GlobalSearchService();

// Search all modules
$results = $searchService->search('john doe');

// Search specific modules
$results = $searchService->search('wetsuit', ['products', 'rentals'], 10);

// Get autocomplete suggestions
$suggestions = $searchService->getSuggestions('div');
```

**Autocomplete Implementation**:
```javascript
// Debounced autocomplete with 300ms delay
searchInput.addEventListener('input', function(e) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        fetch(`/store/search/suggestions?q=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(suggestions => displaySuggestions(suggestions));
    }, 300);
});
```

**Result Metadata**:
Each result includes:
- `title` - Display title
- `description` - Brief description
- `url` - Detail page link
- `module` - Source module
- `icon` - FontAwesome icon class
- `metadata` - Additional contextual info

---

### 10. Quick Actions / Shortcuts System

**Purpose**: Keyboard-driven productivity tool for staff

**Files Created**:
- [app/Services/QuickActions/QuickActionsService.php](app/Services/QuickActions/QuickActionsService.php)
- [app/Views/components/quick_actions_modal.php](app/Views/components/quick_actions_modal.php)

**Features**:
- Command palette modal (Ctrl+K / Cmd+K)
- 15+ predefined keyboard shortcuts
- Permission-based filtering
- Search within actions
- Keyboard navigation (arrows, Enter, Esc)
- Recent actions from audit log
- Category grouping
- Visual shortcut hints

**Keyboard Shortcuts**:

| Shortcut | Action | Permission |
|----------|--------|------------|
| `Ctrl+K` | Open Quick Actions | - |
| `Alt+C` | New Customer | customers.create |
| `Alt+P` | New Product | products.create |
| `Alt+O` | New Order | orders.create |
| `Alt+R` | New Rental | rentals.create |
| `Alt+T` | New Trip | trips.create |
| `Alt+D` | Dashboard | - |
| `Alt+S` | Search | - |
| `Alt+I` | Inventory | products.view |
| `Alt+A` | Appointments | appointments.view |
| `Alt+N` | Notifications | - |
| `Alt+H` | Reports | reports.view |
| `Alt+U` | Customers List | customers.view |
| `Alt+G` | Settings | settings.manage |
| `/` | Focus Global Search | - |

**Action Categories**:
- General (Dashboard, Search)
- Customers (New, List)
- Sales (New Order, POS)
- Inventory (Products, Stock)
- Rentals (New, Active)
- Courses (New, Enrollments)
- Appointments (New, Calendar)
- Documents (Upload, Browse)
- Reports (Dashboard, Analytics)
- Navigation (various quick links)

**Modal Features**:
- Backdrop blur effect
- Slide-down animation
- Recent actions section
- Icon-based visual design
- Responsive grid layout
- Empty state handling

**Implementation**:
```javascript
// Open with Ctrl+K
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        openQuickActions();
    }
});

// Navigate with arrow keys
if (e.key === 'ArrowDown') {
    selectedActionIndex = Math.min(selectedActionIndex + 1, visibleActions.length - 1);
    updateSelection();
}

// Execute with Enter
if (e.key === 'Enter' && selectedActionIndex >= 0) {
    visibleActions[selectedActionIndex].click();
}
```

**Recent Actions**:
Shows last 5 actions taken by user from audit log, providing quick access to frequently used features.

---

## Installation & Configuration

### Step 1: Verify Database Tables

All required tables already exist from migrations 001-017. Verify with:

```bash
sqlite3 database/nautilus.db ".tables"
```

Required tables:
- `notifications`
- `appointments`
- `documents`
- `audit_logs`
- `settings`
- `quick_actions_history`

### Step 2: Create Storage Directory

```bash
mkdir -p storage/documents
chmod 755 storage/documents
```

### Step 3: Configure Environment

Add to `.env`:

```env
# Email Configuration
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@nautilus.com
MAIL_FROM_NAME="Nautilus Dive Shop"

# Application
APP_URL=http://localhost:8000
STORAGE_PATH=/path/to/nautilus/storage
```

### Step 4: Update Routes

Add to `routes/web.php`:

```php
// Notifications
Route::get('/notifications', 'NotificationsController@index');
Route::post('/notifications/{id}/read', 'NotificationsController@markAsRead');
Route::post('/notifications/mark-all-read', 'NotificationsController@markAllAsRead');

// Appointments
Route::resource('/appointments', 'AppointmentsController');
Route::get('/appointments/calendar/data', 'AppointmentsController@calendar');

// Documents
Route::resource('/documents', 'DocumentsController');
Route::get('/documents/{id}/download', 'DocumentsController@download');

// Search
Route::get('/search/results', 'SearchController@results');
Route::get('/search/suggestions', 'SearchController@suggestions');

// Reports
Route::get('/reports/dashboard', 'Reports\\ReportsDashboardController@index');

// Audit Logs
Route::get('/admin/audit', 'Admin\\AuditLogController@index');
Route::get('/admin/audit/{id}', 'Admin\\AuditLogController@show');

// Settings
Route::get('/admin/settings', 'Admin\\SystemSettingsController@index');
Route::post('/admin/settings', 'Admin\\SystemSettingsController@update');

// Customer Portal
Route::get('/customer/portal/dashboard', 'Customer\\PortalDashboardController@index');
```

### Step 5: Include Quick Actions Modal

Add to main layout file (e.g., `app/Views/layouts/main.php`):

```php
<?php include __DIR__ . '/../components/quick_actions_modal.php'; ?>
```

### Step 6: Set Up Permissions

Ensure these permissions exist in `permissions` table:
- `customers.create`, `customers.view`, `customers.update`
- `products.create`, `products.view`
- `orders.create`, `orders.view`
- `appointments.create`, `appointments.view`
- `documents.create`, `documents.view`
- `reports.view`
- `audit.view`
- `settings.manage`

### Step 7: Test Installation

```bash
# Test email configuration
curl http://localhost:8000/admin/settings/test-email

# Verify search is working
curl http://localhost:8000/search/suggestions?q=test

# Check audit logging
# Create a test record and verify it appears in audit log
```

---

## Security Features

### Authentication & Authorization
- ✅ Separate authentication for staff (Auth) and customers (CustomerAuth)
- ✅ Permission-based access control on all routes
- ✅ Session management with timeout
- ✅ Password hashing (bcrypt)

### Input Validation
- ✅ CSRF token validation on all forms
- ✅ XSS prevention via `htmlspecialchars()` in views
- ✅ SQL injection prevention via prepared statements
- ✅ File upload validation (type, size, extension)

### Data Protection
- ✅ Encrypted settings storage for sensitive data
- ✅ Files stored outside public web root
- ✅ Audit logging for sensitive operations
- ✅ IP address and user agent tracking

### Email Security
- ✅ TLS/SSL encryption for SMTP
- ✅ Email address validation
- ✅ Rate limiting (implement in production)

---

## Performance Optimizations

### Caching
- ✅ Settings cached in memory after first load
- ✅ Search results use pagination
- ✅ Audit logs use indexed queries

### Database
- ✅ Prepared statements for all queries
- ✅ Indexes on foreign keys and search fields
- ✅ Pagination on all list views (100 items default)
- ✅ Lazy loading for related data

### Frontend
- ✅ Debounced search autocomplete (300ms)
- ✅ Lazy chart loading
- ✅ AJAX for non-critical updates
- ✅ Minimal JavaScript dependencies

### File Handling
- ✅ Unique filenames prevent collisions
- ✅ File size limits (50MB)
- ✅ Streaming downloads for large files

---

## API Endpoints Reference

### Notifications
```
GET    /notifications                    - List all notifications
POST   /notifications/{id}/read          - Mark as read
POST   /notifications/mark-all-read      - Mark all as read
DELETE /notifications/{id}               - Delete notification
```

### Appointments
```
GET    /appointments                     - List appointments
GET    /appointments/create              - Create form
POST   /appointments                     - Store new appointment
GET    /appointments/{id}                - View appointment
GET    /appointments/{id}/edit           - Edit form
PUT    /appointments/{id}                - Update appointment
DELETE /appointments/{id}                - Delete appointment
GET    /appointments/calendar/data       - Calendar JSON data
```

### Documents
```
GET    /documents                        - List documents
GET    /documents/create                 - Upload form
POST   /documents                        - Store document
GET    /documents/{id}                   - View document
GET    /documents/{id}/download          - Download file
PUT    /documents/{id}                   - Update document
DELETE /documents/{id}                   - Delete document
```

### Search
```
GET    /search/results?q={query}         - Search results page
GET    /search/suggestions?q={query}     - Autocomplete suggestions
GET    /search/quickFind?id={id}         - Quick ID lookup
```

### Reports
```
GET    /reports/dashboard                - Reports dashboard
GET    /reports/dashboard?export=csv     - Export to CSV
```

### Audit Logs
```
GET    /admin/audit                      - List audit logs
GET    /admin/audit?export=csv           - Export audit logs
GET    /admin/audit/{id}                 - View log detail
POST   /admin/audit/cleanup              - Delete old logs
```

### Settings
```
GET    /admin/settings                   - Settings page
POST   /admin/settings                   - Update settings
POST   /admin/settings/test-email        - Test email config
```

### Customer Portal
```
GET    /customer/portal/dashboard        - Customer dashboard
POST   /customer/portal/profile          - Update profile
GET    /customer/portal/orders           - View orders
GET    /customer/portal/courses          - View courses
```

---

## Testing Checklist

### Notifications System
- [ ] Create notification via database insert
- [ ] View unread notifications
- [ ] Mark single notification as read
- [ ] Mark all notifications as read
- [ ] Delete notification
- [ ] AJAX updates work without page reload
- [ ] Unread counter updates correctly

### Appointments System
- [ ] Create new appointment
- [ ] Verify conflict detection prevents double-booking
- [ ] Edit existing appointment
- [ ] Change appointment status (confirm, complete, cancel)
- [ ] Delete appointment
- [ ] Filter appointments by date
- [ ] Calendar data endpoint returns valid JSON
- [ ] Email reminders sent (if configured)

### Documents Management
- [ ] Upload document (PDF, image, text file)
- [ ] Verify file stored in `/storage/documents/`
- [ ] Download document
- [ ] Search documents by name/tags
- [ ] Link document to entity (customer, order, etc.)
- [ ] Delete document (file and database record)
- [ ] Check permission-based access
- [ ] Verify audit log tracks uploads/downloads

### Email Service
- [ ] Configure SMTP settings in .env
- [ ] Test email from settings page
- [ ] Send appointment reminder email
- [ ] Send order confirmation email
- [ ] Verify HTML rendering in email client
- [ ] Test with attachments
- [ ] Verify error handling for failed sends

### Reports Dashboard
- [ ] View dashboard with date range
- [ ] Verify revenue calculations are accurate
- [ ] Test quick date presets (Today, This Week, etc.)
- [ ] View top products/customers
- [ ] Export report to CSV
- [ ] Verify charts render correctly
- [ ] Test responsive layout on mobile

### Audit Log System
- [ ] Perform create/update/delete operations
- [ ] Verify audit log entries created
- [ ] Filter logs by user, action, entity type
- [ ] View entity history
- [ ] Export audit logs to CSV
- [ ] Run cleanup to delete old logs
- [ ] Verify old/new values captured correctly

### Settings Management
- [ ] View settings by category
- [ ] Update setting values
- [ ] Verify type casting (boolean, integer, json)
- [ ] Test encrypted setting storage
- [ ] Verify settings cache updates
- [ ] Bulk update multiple settings

### Customer Portal
- [ ] Log in as customer
- [ ] View dashboard with personalized data
- [ ] Check statistics accuracy
- [ ] View recent orders with correct status
- [ ] View upcoming courses/trips
- [ ] Update profile information
- [ ] View certifications
- [ ] Check loyalty points balance

### Global Search
- [ ] Search across all modules
- [ ] Verify autocomplete suggestions appear
- [ ] Test debouncing (no excessive queries)
- [ ] Filter results by module
- [ ] Quick find by ID (order number, etc.)
- [ ] Click through to detail pages
- [ ] Test with special characters
- [ ] Verify permission-based result filtering

### Quick Actions System
- [ ] Press Ctrl+K to open modal
- [ ] Press Esc to close modal
- [ ] Search within actions
- [ ] Navigate with arrow keys
- [ ] Select action with Enter
- [ ] Test Alt+Key shortcuts (Alt+C, Alt+P, etc.)
- [ ] Verify permission-based filtering
- [ ] Check recent actions displayed
- [ ] Test on macOS (Cmd+K)

---

## Troubleshooting

### Email Not Sending
1. Verify SMTP credentials in `.env`
2. Check `MAIL_PORT` matches encryption (587 for TLS, 465 for SSL)
3. Enable "Less secure app access" or use app-specific password
4. Check logs in `storage/logs/error.log`
5. Test with `/admin/settings/test-email` endpoint

### Documents Not Uploading
1. Verify `storage/documents/` directory exists and is writable
2. Check PHP `upload_max_filesize` and `post_max_size` in `php.ini`
3. Verify file extension is in whitelist
4. Check disk space
5. Review error logs

### Search Not Working
1. Verify database tables have data
2. Check permission settings for current user
3. Clear browser cache
4. Test with simple query (single word)
5. Verify JavaScript console for errors

### Quick Actions Not Appearing
1. Verify modal component included in layout
2. Check JavaScript console for errors
3. Test Ctrl+K shortcut explicitly
4. Verify user has permissions for actions
5. Clear browser cache

### Audit Logs Not Created
1. Verify `audit_logs` table exists
2. Check AuditService instantiation
3. Verify user ID is valid
4. Review database connection
5. Check error logs for exceptions

---

## Future Enhancement Suggestions

1. **Dashboard Widgets**: Customizable dashboard with draggable widgets
2. **Backup & Restore**: Automated database backups with restore functionality
3. **Advanced Reporting**: Custom report builder with saved queries
4. **Mobile App**: Native mobile app using customer portal API
5. **Integrations**: QuickBooks, Stripe, Mailchimp integrations
6. **Advanced Scheduling**: Recurring appointments and resource management
7. **Multi-language Support**: Internationalization (i18n)
8. **Two-Factor Authentication**: Enhanced security with 2FA
9. **Customer Dive Log**: Digital dive log with statistics
10. **Equipment Maintenance Tracking**: Service intervals and maintenance history

---

## Support & Documentation

### Related Documentation
- [README.md](README.md) - Project overview
- [Database Schema](database/schema.md) - Database structure
- [API Documentation](docs/api.md) - API reference
- [Deployment Guide](docs/deployment.md) - Production deployment

### Getting Help
- GitHub Issues: Report bugs and request features
- Documentation: Check inline code comments
- Audit Logs: Review system activity for debugging

---

## Changelog

### Version 1.3.0 - Phase 3 (2025-10-29)
- Added Customer Portal Dashboard
- Added Global Search System
- Added Quick Actions/Shortcuts System

### Version 1.2.0 - Phase 2 (2025-10-29)
- Added Email Service with templates
- Added Advanced Reports Dashboard
- Added Audit Log System
- Added Settings Management

### Version 1.1.0 - Phase 1 (2025-10-29)
- Added Notifications System
- Added Appointments System
- Added Documents Management System

---

## License

This software is part of the Nautilus Dive Shop Management System.
All rights reserved.

---

**Document Generated**: 2025-10-29
**Total Features**: 10 major systems
**Lines of Code Added**: ~5,000+
**Files Created**: 30+
