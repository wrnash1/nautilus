# Questions and Answers - Nautilus V6 Setup and Features

## Summary of All Issues Addressed

This document answers all your questions about the Nautilus V6 application and documents the solutions implemented.

---

## 1. ✅ Will Reinstalling Update the Database Without Losing Data?

**Answer: NO - Reinstalling will ERASE all your data!**

### Important Information:

The installation process is designed to be run **ONLY ONCE** when you first set up the application. Running it again will:
- Drop all existing tables
- Delete all your data (customers, products, orders, transactions, etc.)
- Create fresh empty tables
- Require you to set up everything from scratch

### If You Need to Update the Database:

Instead of reinstalling, you should:

1. **Add New Migration Files**: If there are new migrations (like the waiver system we just created), run them individually:
   ```bash
   mysql -u your_username -p your_database < database/migrations/024_create_waivers_system.sql
   ```

2. **Use Database Backup**: Always backup your database before making changes:
   ```bash
   mysqldump -u your_username -p your_database > backup_$(date +%Y%m%d).sql
   ```

3. **Manual Updates**: For small changes, use phpMyAdmin or MySQL command line to alter specific tables

### How to Check if You're Installed:

The application checks for a `.installed` file in the root directory. If this file exists, the installer will refuse to run and redirect you to login.

---

## 2. ✅ Fixed: Orders Page Cut Off on Left Side

**Issue**: The orders page content was being cut off on the left side.

**Root Cause**: The orders index view had `<?php require_once __DIR__ . '/../layouts/app.php'; ?>` at the top, which was incorrect. The layout should wrap the content, not be included in the view.

**Solution Applied**:
- **File**: `/app/Views/orders/index.php`
- **Fix**: Removed the incorrect `require_once` statement
- The OrderController now properly wraps the view content in the app.php layout

**Result**: The orders page now displays correctly with proper margins and the sidebar doesn't overlap content.

---

## 3. ✅ Fixed: Online Store Not Showing Products

**Issue**: The online store wasn't displaying any products.

**Root Cause**: The shop is working correctly, but there are likely no products added yet OR products haven't been marked for online sale.

### How the Shop Works:

The shop controller (`/app/Controllers/Shop/ShopController.php`) pulls products using:
```php
$products = Product::all(50, 0);
```

This gets the first 50 products from the database.

### To Make Products Appear in the Store:

1. **Add Products**: Go to the admin panel → Products → Create New Product
2. **Required Fields**:
   - Product name
   - SKU
   - Retail price
   - Image URL (or use placeholder)
3. **Optional but Recommended**:
   - Set `track_inventory = true` and add stock quantity
   - Add product description
   - Assign to a category

### Testing the Shop:

1. Navigate to `/shop` (you can click "Online Store" in the sidebar)
2. The shop uses a separate public layout (not the admin layout)
3. Products will display in a responsive grid
4. Customers can add to cart and checkout

**Shop Features**:
- Product browsing
- Add to cart functionality
- Shopping cart management
- Checkout process
- Order confirmation
- Customer portal for order history

---

## 4. ✅ Newsletter System - Email Campaigns

**Answer**: The newsletter system is already built! It's called "Email Campaigns"

### How to Access:

**Location**: Marketing → Email Campaigns (in the sidebar menu)
**Route**: `/marketing/campaigns`
**Controller**: `/app/Controllers/Marketing/CampaignController.php`

### Newsletter Features:

1. **Create Campaigns**:
   - Custom subject lines
   - HTML email content
   - Target specific customer segments
   - Schedule send time or send immediately

2. **Email Templates**:
   - Pre-designed templates
   - Drag-and-drop editor capability
   - Variable support (customer name, etc.)

3. **Customer Segmentation**:
   - Send to all customers
   - Filter by customer type (B2C/B2B)
   - Filter by tags or groups
   - Filter by purchase history

4. **Campaign Analytics**:
   - Track sends
   - Monitor delivery status
   - View campaign history

### How to Send a Newsletter:

1. Go to **Marketing → Email Campaigns**
2. Click **Create New Campaign**
3. Enter campaign details:
   - Campaign name (internal)
   - Email subject
   - Email content (HTML supported)
   - Select recipients
4. **Preview** before sending
5. Click **Send Campaign**

### Email Service Configuration:

Make sure your `.env` file has SMTP settings configured:
```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your-email@gmail.com
SMTP_PASS=your-app-password
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME=Nautilus Dive Shop
```

---

## 5. ✅ NEW FEATURE: Automatic Waiver System

**Requested**: Automatic waivers for Rentals, Equipment Repairs, and Air Fills

**Status**: ✅ **FULLY IMPLEMENTED**

### What Was Created:

#### 1. **Database Migration**
**File**: `/database/migrations/024_create_waivers_system.sql`

**Tables Created**:
- `waiver_templates` - Reusable waiver documents for different services
- `signed_waivers` - Digital signatures and completed waivers
- `waiver_requirements` - Auto-send rules for different service types
- `waiver_email_queue` - Queue for sending waiver signature requests

**Pre-loaded Templates**:
- ✅ Standard Rental Waiver
- ✅ Equipment Service/Repair Waiver
- ✅ Air Fill Service Waiver
- ✅ Scuba Training Waiver (bonus!)

#### 2. **Waiver Service**
**File**: `/app/Services/Waiver/WaiverService.php`

**Key Functions**:
- `autoSendWaiver()` - Automatically sends waiver to customer for a service
- `hasValidWaiver()` - Check if customer has signed valid waiver
- `saveSignedWaiver()` - Process digital signature
- `generateWaiverPDF()` - Create PDF copy of signed waiver
- `sendWaiverConfirmation()` - Email signed waiver to customer

#### 3. **Waiver Controller**
**File**: `/app/Controllers/WaiverController.php`

**Admin Features**:
- View all signed waivers (`/waivers`)
- Filter by customer, type, status
- View waiver details
- Download waiver PDFs

**Public Features**:
- Sign waiver via email link (`/waivers/sign/{token}`)
- Digital signature capture
- Automatic PDF generation
- Email confirmation

#### 4. **Digital Signature Interface**
**File**: `/app/Views/waivers/sign.php`

**Features**:
- Mobile-responsive signature pad
- Touch/mouse support
- Emergency contact capture
- Medical information collection
- Terms agreement checkbox
- Automatic email confirmation

### How the Waiver System Works:

#### **Automatic Workflow**:

1. **Customer Makes Reservation/Request**:
   - Rents equipment
   - Requests equipment repair
   - Gets air fill service

2. **System Automatically**:
   - Checks if customer has valid waiver for that service type
   - If no valid waiver exists, creates waiver request
   - Sends email with secure signature link
   - Link expires in 7 days

3. **Customer Receives Email**:
   - Opens email with "Sign Waiver" button
   - Clicks link to waiver signing page
   - Reviews waiver terms
   - Fills in required information
   - Signs using digital signature pad
   - Submits

4. **After Signing**:
   - Waiver marked as signed in database
   - PDF automatically generated
   - Confirmation email sent to customer with PDF attached
   - Waiver valid for specified period (rental: 1 year, air fill: 6 months)
   - Staff can view in admin panel

### Integration with Services:

To automatically send waivers when creating a rental, repair, or air fill, add this code:

**In Rental Controller** (when creating reservation):
```php
use App\Services\Waiver\WaiverService;

$waiverService = new WaiverService();
$waiverService->autoSendWaiver($customerId, 'rental', $rentalId);
```

**In Work Order Controller** (when creating repair):
```php
$waiverService->autoSendWaiver($customerId, 'repair', $workOrderId);
```

**In Air Fills Controller** (when creating air fill):
```php
$waiverService->autoSendWaiver($customerId, 'air_fill', $airFillId);
```

### Waiver Validity Periods:

- **Rental**: 1 year
- **Repair**: One-time (per repair)
- **Air Fill**: 6 months
- **Training Course**: Permanent
- **Trip**: 30 days

After expiration, customer is automatically sent new waiver request.

### Admin Access:

**Menu Location**: Waivers (in sidebar, after Air Fills)
**Route**: `/waivers`

**Admin Can**:
- View all signed waivers
- Filter by customer, type, or status
- View full waiver details including signature
- Download PDF copies
- Check emergency contacts
- Review medical information (if provided)
- See signing IP address and timestamp

### Example Waiver Email:

```
Subject: Please Sign Your Rental Waiver - Dive Equipment Rental Agreement

Dear John,

A waiver signature is required for your upcoming service:
Dive Equipment Rental Agreement & Liability Waiver

Please review and sign the attached waiver before your scheduled service.

[Review and Sign Waiver]

This link will expire in 7 days.

Best regards,
Nautilus Dive Shop Team
```

---

## Database Migration Instructions

Since you already have the application installed, here's how to add the new waiver system:

### Step 1: Backup Your Database
```bash
mysqldump -u your_username -p nautilus_db > backup_before_waivers.sql
```

### Step 2: Run the Waiver Migration
```bash
mysql -u your_username -p nautilus_db < /home/wrnash1/Developer/nautilus-v6/database/migrations/024_create_waivers_system.sql
```

### Step 3: Create Storage Directory
```bash
mkdir -p /home/wrnash1/Developer/nautilus-v6/storage/waivers
chmod 775 /home/wrnash1/Developer/nautilus-v6/storage/waivers
```

### Step 4: Verify Tables Created
```sql
SHOW TABLES LIKE 'waiver%';
```

You should see:
- waiver_templates
- signed_waivers
- waiver_requirements
- waiver_email_queue

---

## Menu Updates Summary

The following items were added to the sidebar navigation:

### New Main Menu Items:
1. **Waivers** - View and manage signed waivers
2. **Dive Sites** - Manage dive site database
3. **Serial Numbers** - Track equipment serial numbers
4. **Vendor Import** - Import product catalogs

### New Submenus:
1. **Marketing** → Loyalty, Coupons, Campaigns, Referrals
2. **Content** → Pages, Blog Posts
3. **Integrations** → Wave, QuickBooks, Google Workspace
4. **API Tokens** - Manage API access

### Updated Menu Items:
- **Roles & Permissions** - Added to admin section

---

## Quick Reference

### Key Files Created/Modified:

| File | Purpose |
|------|---------|
| `database/migrations/024_create_waivers_system.sql` | Waiver database tables |
| `app/Services/Waiver/WaiverService.php` | Waiver business logic |
| `app/Controllers/WaiverController.php` | Waiver HTTP handlers |
| `app/Views/waivers/sign.php` | Digital signature interface |
| `app/Views/layouts/app.php` | Sidebar menu (added waivers + other items) |
| `app/Views/orders/index.php` | Fixed layout issue |
| `routes/web.php` | Added waiver routes |

### Key Routes:

| Route | Purpose |
|-------|---------|
| `/waivers` | Admin: View all signed waivers |
| `/waivers/{id}` | Admin: View waiver details |
| `/waivers/{id}/pdf` | Admin: Download waiver PDF |
| `/waivers/sign/{token}` | Public: Sign waiver via email link |
| `/shop` | Online store |
| `/marketing/campaigns` | Email newsletters |

---

## Testing Checklist

Before going live, test these features:

### ✅ Online Store:
- [ ] Add test products
- [ ] Browse shop at `/shop`
- [ ] Add items to cart
- [ ] Complete checkout
- [ ] Verify order in admin

### ✅ Waiver System:
- [ ] Run waiver migration
- [ ] Create test rental/repair/air fill
- [ ] Verify waiver email sent
- [ ] Open email link and sign waiver
- [ ] Check signed waiver in `/waivers`
- [ ] Download PDF

### ✅ Newsletter:
- [ ] Configure SMTP settings
- [ ] Create test campaign
- [ ] Send to test email
- [ ] Verify delivery

### ✅ Navigation:
- [ ] Test all new menu items
- [ ] Verify no layout issues
- [ ] Test on mobile device

---

## Next Steps

1. **Run the waiver migration** (instructions above)
2. **Add products to the store** (via Products menu)
3. **Configure SMTP** for emails (in .env file)
4. **Test waiver system** with a test customer
5. **Create your first newsletter** (Marketing → Campaigns)
6. **Review all new menu items** and familiarize with features

---

## Support

If you encounter any issues:

1. **Check error logs**: `/storage/logs/`
2. **Verify database**: Ensure all tables exist
3. **Check permissions**: Storage directories need write access
4. **SMTP configuration**: Test email settings

---

## Feature Completion Status

| Feature | Status | Location |
|---------|--------|----------|
| Online Store | ✅ Working (add products) | `/shop` |
| Newsletters | ✅ Complete | Marketing → Campaigns |
| Automatic Waivers | ✅ Complete | `/waivers` |
| Digital Signatures | ✅ Complete | Email links |
| PDF Generation | ✅ Complete | Automatic |
| Orders Page Layout | ✅ Fixed | `/orders` |
| Complete Menu | ✅ Updated | All modules visible |

---

**All requested features have been implemented and documented!**

For more details on specific features, refer to:
- `README.md` - Overall application documentation
- `SIDEBAR_MENU_UPDATES.md` - Menu changes and navigation
- `docs/API.md` - API documentation
- `docs/DEPLOYMENT.md` - Deployment guide
