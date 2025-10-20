# Staff Management & Settings Guide

## Overview

This guide explains how to manage employees, create schedules, and configure application settings including Google Calendar integration for the Nautilus Dive Shop management system.

---

## Table of Contents

1. [Employee/Staff Management](#employeestaff-management)
2. [Staff Scheduling](#staff-scheduling)
3. [Google Calendar Integration](#google-calendar-integration)
4. [Settings Configuration](#settings-configuration)
5. [Quick Reference](#quick-reference)

---

## Employee/Staff Management

### Adding New Employees

There are two ways to add employees to the system:

#### Method 1: User Management (Recommended)

1. **Navigate to User Management**
   - Click **Settings** in the sidebar (bottom section)
   - Or go directly to `/admin/users`

2. **Create New User**
   - Click the **"Add User"** button
   - Fill in the required fields:
     - First Name
     - Last Name
     - Email (will be used for login)
     - Password
     - **Role** - Select appropriate role (not "Customer"):
       - Admin
       - Manager
       - Cashier
       - Instructor
       - Sales Associate
       - etc.
   - Click **"Save"**

3. **Result**
   - User is now in the system
   - Will appear in Staff list (filtered by non-customer roles)
   - Can log in with email/password
   - Has permissions based on assigned role

#### Method 2: Viewing Existing Staff

1. **Navigate to Staff Section**
   - Click **"Staff"** in the sidebar (new menu item)
   - Click **"Employees"** from submenu
   - Or go to `/staff`

2. **View Staff Members**
   - See all users with non-customer roles
   - View: Name, Email, Role, Status
   - Click on staff member to view details and performance

### Key Points

- **Staff = Users with non-customer roles**
- All employees need a user account first
- Assign appropriate roles for correct permissions
- Active/inactive status controls login ability

---

## Staff Scheduling

### Overview

The new scheduling system uses a modern calendar interface powered by FullCalendar.

### Accessing Staff Schedules

1. **Navigate to Schedules**
   - Sidebar → **Staff** → **Schedules**
   - Or go directly to `/staff/schedules`

2. **Calendar Views**
   - **Month View** - Overview of entire month
   - **Week View** - Detailed weekly schedule (default)
   - **Day View** - Single day detailed view
   - **List View** - List format of schedules

### Creating a Schedule

1. **Open Add Schedule Modal**
   - Click **"+ Add Schedule"** button (top right)
   - Or click any date on the calendar

2. **Fill in Schedule Details**
   - **Staff Member*** - Select employee from dropdown
   - **Date*** - Schedule date
   - **Start Time*** - Shift start time
   - **End Time*** - Shift end time
   - **Break Duration** - Break time in minutes (default: 30)
   - **Role/Position** - Specific role for this shift:
     - Cashier
     - Instructor
     - Dive Master
     - Equipment Technician
     - Sales Associate
     - Manager
   - **Location** - Work location (e.g., "Main Store", "Boat")
   - **Notes** - Additional information

3. **Save**
   - Click **"Save Schedule"**
   - System checks for conflicts automatically
   - Schedule appears on calendar

### Viewing Schedule Details

- Click any calendar event
- Modal shows:
  - Staff member name
  - Date and time
  - Duration
  - Role and location
  - Break duration
  - Notes

### Editing/Deleting Schedules

1. **View Details** - Click event on calendar
2. **Delete** - Click "Delete" button in modal (requires `staff.delete` permission)

### Exporting Schedules

- Click **"Export"** button
- Downloads current calendar view (month/week)
- Format: CSV or iCal (for import into other calendars)

### Calendar Features

- **Drag & Drop** - Planned for future update
- **Recurring Schedules** - Planned for future update
- **Conflict Detection** - Automatically prevents double-booking
- **Time Display** - 6 AM to 10 PM (customizable)
- **Color Coding** - Different colors for different roles (planned)

---

## Google Calendar Integration

### Overview

Sync staff schedules, course schedules, trip schedules, and customer appointments with Google Calendar.

### Setup Process

#### Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. Click **"Create Project"**
3. Name it (e.g., "Nautilus Dive Shop")
4. Click **"Create"**

#### Step 2: Enable Google Calendar API

1. In your project, go to **"APIs & Services" → "Library"**
2. Search for **"Google Calendar API"**
3. Click on it and press **"Enable"**

#### Step 3: Create OAuth 2.0 Credentials

1. Go to **"APIs & Services" → "Credentials"**
2. Click **"Create Credentials" → "OAuth 2.0 Client ID"**
3. If prompted, configure OAuth consent screen:
   - User Type: Internal (for organization) or External
   - App name: "Nautilus Dive Shop"
   - Support email: Your email
   - Scopes: Add Google Calendar API scope
4. Application type: **"Web application"**
5. Add **Authorized redirect URIs**:
   ```
   https://pangolin.local/admin/settings/integrations/google/callback
   ```
   (Replace with your actual domain)
6. Click **"Create"**
7. **Copy Client ID and Client Secret** - You'll need these!

#### Step 4: Configure in Nautilus

1. **Navigate to Integrations**
   - Sidebar → **Settings**
   - Click **"Integrations"** card
   - Or go to `/admin/settings/integrations`

2. **Find Google Calendar Section**
   - Scroll to the green "Google Calendar Integration" card

3. **Enter Credentials**
   - **Enable Google Calendar Sync** - Toggle ON
   - **Google Client ID** - Paste from Google Cloud Console
   - **Google Client Secret** - Paste from Google Cloud Console
   - **Default Calendar ID** - Enter "primary" (or specific calendar ID)

4. **Configure Auto-Sync**
   - Check which schedules to sync:
     - ☑ Sync staff schedules
     - ☑ Sync course schedules
     - ☑ Sync trip schedules
     - ☑ Sync customer appointments

5. **Save Settings**
   - Click **"Save Google Calendar Settings"**

6. **Authorize**
   - Click **"Authorize Google Calendar"** button (appears after saving)
   - Sign in with Google account
   - Grant calendar permissions
   - You'll be redirected back

#### Step 5: Test

1. Create a test staff schedule
2. Check your Google Calendar
3. Event should appear automatically

### What Gets Synced

| Item | Sync Trigger | Calendar Event Title |
|------|--------------|---------------------|
| Staff Schedule | Create/Update/Delete | "[Staff Name] - [Role]" |
| Course Schedule | Create/Update/Delete | "[Course Name] - Instructor: [Name]" |
| Trip Schedule | Create/Update/Delete | "[Trip Name] - Departure" |
| Customer Appointment | Create/Update/Delete | "[Customer Name] - [Type]" |

### Troubleshooting

**Issue**: "Authorization failed"
- **Solution**: Check redirect URI matches exactly
- Ensure HTTPS is configured properly
- Verify Client ID and Secret are correct

**Issue**: "Events not syncing"
- **Solution**: Check that sync toggle is enabled
- Verify you authorized the correct Google account
- Check application logs for errors

**Issue**: "Duplicate events"
- **Solution**: Each event has unique ID - shouldn't happen
- If it does, clear calendar and re-sync

---

## Settings Configuration

### Accessing Settings

- Sidebar → **Settings**
- Or go to `/admin/settings`

### Settings Categories

#### 1. General Settings (`/admin/settings/general`)

**Company Information:**
- Business Name
- Address, City, State, ZIP
- Phone, Email
- Timezone
- Currency

**Branding:**
- Company Logo (Main)
- Company Logo (Small Icon)
- Company Favicon
- Brand Primary Color
- Brand Secondary Color
- Company Tagline

#### 2. Tax Settings (`/admin/settings/tax`)

**Tax Configuration:**
- Add/Edit/Delete tax rates
- Default tax rate selection
- Tax type (percentage/fixed)
- Location-based tax rates

#### 3. Email Settings (`/admin/settings/email`)

**SMTP Configuration:**
- SMTP Host
- SMTP Port (default: 587)
- SMTP Username
- SMTP Password (encrypted)
- SMTP Encryption (TLS/SSL)
- From Email
- From Name
- Test Email functionality

#### 4. Payment Settings (`/admin/settings/payment`)

**Payment Gateways:**
- **Stripe**
  - Publishable Key
  - Secret Key (encrypted)
  - Enable/Disable
- **Square**
  - Access Token (encrypted)
  - Enable/Disable
- **BTCPay Server**
  - API Key (encrypted)
  - Enable/Disable
- **Traditional Methods**
  - Cash (enable/disable)
  - Check (enable/disable)
  - Credit Card (enable/disable)

#### 5. Rental Settings (`/admin/settings/rental`)

**Rental Policies:**
- Deposit Required (yes/no)
- Deposit Percentage
- Late Fee Enabled (yes/no)
- Late Fee Amount
- Late Fee Type (per day/flat)
- Damage Fee Enabled (yes/no)
- Inspection Reminder Days

#### 6. Air Fill Pricing (`/admin/settings/air-fills`)

**Pricing Configuration:**
- Air Fill Price
- Air Pressure (PSI)
- Nitrox Fill Price
- Nitrox Pressure (PSI)
- Trimix Fill Price
- Trimix Pressure (PSI)
- Oxygen Fill Price
- Oxygen Pressure (PSI)

#### 7. Integrations (`/admin/settings/integrations`)

**Available Integrations:**

1. **Wave Apps (Accounting)**
   - Access Token (encrypted)
   - Business ID
   - Auto-sync transactions

2. **Google Calendar** (NEW!)
   - Client ID
   - Client Secret (encrypted)
   - Calendar ID
   - Sync options

3. **PADI**
   - API Key (encrypted)
   - API Secret (encrypted)
   - API Endpoint

4. **SSI**
   - API Key (encrypted)
   - API Endpoint

5. **Twilio (SMS)**
   - Account SID
   - Auth Token (encrypted)
   - From Phone Number
   - Test SMS feature

### Security Features

**Encrypted Settings:**
All sensitive API keys and passwords are automatically encrypted using AES-256-CBC:
- stripe_secret_key
- square_access_token
- btcpay_api_key
- google_client_secret
- padi_api_key / padi_api_secret
- ssi_api_key
- twilio_auth_token
- smtp_password
- wave_access_token

**Audit Logging:**
- Access to encrypted settings is logged
- Tracks: user, IP address, action, timestamp
- View audit log in database: `settings_audit` table

---

## Quick Reference

### Navigation Menu Structure

```
Dashboard
Point of Sale
Customers
Products
Categories
Vendors
Reports ▼
  - Sales Report
  - Customer Report
  - Product Report
  - Payment Report
  - Inventory Report
  - Low Stock Alert
Rentals ▼
  - Equipment
  - Reservations
Air Fills
Courses ▼
  - Course Catalog
  - Schedules
  - Enrollments
Trips ▼
  - Trip Catalog
  - Schedules
  - Bookings
Work Orders
Orders
Online Store

───────────────────

Staff ▼ (NEW!)
  - Employees
  - Schedules
  - Time Clock
  - Commissions
Settings
User Management
```

### Common Tasks

| Task | Location | Permission Required |
|------|----------|-------------------|
| Add Employee | User Management | `admin.users` |
| View Staff | Staff → Employees | `staff.view` |
| Create Schedule | Staff → Schedules | `staff.create` |
| Clock In/Out | Staff → Time Clock | `staff.view` |
| View Commissions | Staff → Commissions | `staff.view` |
| Configure Settings | Settings | `admin.settings` |
| Setup Google Calendar | Settings → Integrations | `admin.settings` |

### API Endpoints

**Staff Schedules:**
- `GET /staff/schedules` - View schedules page
- `POST /staff/schedules` - Create new schedule
- `GET /staff/schedules/data` - Get calendar events (JSON)
- `POST /staff/schedules/{id}/delete` - Delete schedule
- `GET /staff/schedules/export` - Export schedules

**Settings:**
- `GET /admin/settings` - Settings dashboard
- `GET /admin/settings/{category}` - View category
- `POST /admin/settings/update` - Save settings
- `POST /admin/settings/upload-logo` - Upload branding
- `GET /admin/settings/integrations/google/authorize` - Google OAuth
- `GET /admin/settings/integrations/google/callback` - OAuth callback

### File Locations

**Views:**
- `/app/Views/staff/schedules/index.php` - Schedule calendar
- `/app/Views/admin/settings/integrations.php` - Integrations page
- `/app/Views/layouts/app.php` - Main navigation

**Controllers:**
- `/app/Controllers/Staff/ScheduleController.php`
- `/app/Controllers/Admin/SettingsController.php`
- `/app/Controllers/Admin/UserController.php`

**Services:**
- `/app/Services/Staff/ScheduleService.php`
- `/app/Services/Admin/SettingsService.php`

**Database:**
- `users` - All application users
- `staff_schedules` - Work schedules
- `settings` - Configuration key-value store
- `settings_audit` - Security audit log

---

## Support

For questions or issues:
- Check application logs: `/storage/logs/`
- Review database migrations: `/database/migrations/`
- Contact system administrator

---

**Last Updated:** October 2025
**Version:** Nautilus v6
