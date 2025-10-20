# Company Branding & Logo Guide - Nautilus v6.0

## Overview

Nautilus now supports comprehensive company branding including logo uploads, custom colors, and taglines that automatically appear across your entire application - invoices, receipts, emails, newsletters, and the website interface.

---

## Features

### ✅ What's Included

**Logo Management:**
- Main company logo (for invoices, receipts, emails)
- Small logo/icon (for navigation bar)
- Favicon support (browser tab icon)
- Automatic sizing and responsive display

**Brand Customization:**
- Company tagline/slogan
- Primary brand color (hex color picker)
- Secondary brand color
- Customizable logo display sizes

**Where Your Logo Appears:**
- ✅ Navigation bar/header
- ✅ Receipts & invoices (POS)
- ✅ Email templates & newsletters
- ✅ Customer communications
- ✅ Browser tab (favicon)
- ✅ Printable documents

---

## Quick Setup Guide

### Step 1: Run Database Migration

```bash
cd /home/wrnash1/development/nautilus
mysql -u root -p nautilus < database/migrations/016_add_branding_and_logo_support.sql
```

### Step 2: Upload Your Logo

1. Log in to Nautilus admin panel
2. Navigate to **Settings → General**
3. Scroll to **Company Branding & Logo** section
4. Upload your logo files:
   - **Main Logo**: Recommended 400x100px (used on invoices)
   - **Logo Icon**: Recommended 100x100px square (used in navbar)
   - **Favicon** (optional): 32x32px or 16x16px `.ico` or `.png`

5. Fill in optional branding:
   - Company tagline (e.g., "Dive Into Adventure")
   - Primary brand color (hex code)
   - Secondary brand color

6. Click **Upload Logo & Save Branding**

### Step 3: Verify

Your logo should now appear:
- In the top navigation bar
- On printed receipts
- In email templates (when configured)

---

## Logo Specifications

### Recommended Dimensions

| Logo Type | Recommended Size | Max File Size | Formats | Usage |
|-----------|------------------|---------------|---------|-------|
| Main Logo | 400x100px | 5MB | JPG, PNG, SVG, WebP | Invoices, receipts, emails |
| Icon/Small Logo | 100x100px (square) | 5MB | JPG, PNG, SVG, WebP | Navigation bar, small displays |
| Favicon | 32x32px or 16x16px | 1MB | ICO, PNG | Browser tab icon |

### Best Practices

**✅ DO:**
- Use high-resolution images (2x for retina displays)
- Save logos with transparent backgrounds (PNG or SVG)
- Use SVG for scalable, crisp logos at any size
- Keep file sizes reasonable (< 500KB ideal)
- Test logos on both light and dark backgrounds

**❌ DON'T:**
- Upload extremely large files (will slow down page load)
- Use logos with text that's hard to read when scaled down
- Forget to upload both main and small logos
- Use copyrighted images without permission

---

## Files & Structure

### Database Migration
**File:** [`database/migrations/016_add_branding_and_logo_support.sql`](database/migrations/016_add_branding_and_logo_support.sql)

Creates:
- Branding fields in `settings` table
- `file_uploads` tracking table
- Email template branding support

### File Upload Service
**File:** [`app/Services/FileUploadService.php`](app/Services/FileUploadService.php)

Handles:
- Secure file uploads with validation
- Automatic file type checking
- Size limits and MIME type validation
- Unique filename generation
- Old file cleanup

### Upload Directory
**Location:** [`public/uploads/`](public/uploads/)

Structure:
```
public/uploads/
├── logos/              # Company logos
├── products/           # Product images
├── customers/          # Customer photos
├── certifications/     # Certification cards
├── documents/          # General documents
├── .htaccess          # Security rules
└── index.php          # Prevents directory listing
```

### Settings UI
**File:** [`app/Views/admin/settings/general.php`](app/Views/admin/settings/general.php)

Features:
- Logo upload form with preview
- Current logo display
- Color pickers for brand colors
- Tagline input field

### Controller
**File:** [`app/Controllers/Admin/SettingsController.php`](app/Controllers/Admin/SettingsController.php)

New method: `uploadLogo()` - Handles logo uploads and branding updates

### Routes
**File:** [`routes/web.php`](routes/web.php)

New route:
```php
$router->post('/admin/settings/upload-logo', 'Admin\SettingsController@uploadLogo', [AuthMiddleware::class, CsrfMiddleware::class]);
```

---

## Usage in Templates

### Accessing Branding Settings

```php
<?php
use App\Services\Admin\SettingsService;

$settingsService = new SettingsService();
$brandingSettings = $settingsService->getSettingsByCategory('general');

// Available settings:
$companyName = $brandingSettings['business_name'];
$logoPath = $brandingSettings['company_logo_path'];
$logoSmallPath = $brandingSettings['company_logo_small_path'];
$faviconPath = $brandingSettings['company_favicon_path'];
$tagline = $brandingSettings['company_tagline'];
$primaryColor = $brandingSettings['brand_primary_color'];
$secondaryColor = $brandingSettings['brand_secondary_color'];
$logoWidth = $brandingSettings['invoice_logo_width']; // pixels
$emailLogoWidth = $brandingSettings['email_logo_width']; // pixels
?>
```

### Displaying Logo in Templates

**Example: Invoice/Receipt Header**
```php
<div class="text-center mb-4">
    <?php if (!empty($brandingSettings['company_logo_path'])): ?>
        <img src="<?= htmlspecialchars($brandingSettings['company_logo_path']) ?>"
             alt="<?= htmlspecialchars($brandingSettings['business_name']) ?>"
             style="max-width: <?= intval($brandingSettings['invoice_logo_width'] ?? 200) ?>px; height: auto;">
    <?php else: ?>
        <h2><?= htmlspecialchars($brandingSettings['business_name']) ?></h2>
    <?php endif; ?>

    <?php if (!empty($brandingSettings['company_tagline'])): ?>
        <p class="text-muted fst-italic"><?= htmlspecialchars($brandingSettings['company_tagline']) ?></p>
    <?php endif; ?>
</div>
```

**Example: Email Template Header**
```html
<table style="width: 100%; background-color: <?= htmlspecialchars($brandingSettings['brand_primary_color'] ?? '#0066CC') ?>;">
    <tr>
        <td style="text-align: center; padding: 20px;">
            <?php if (!empty($brandingSettings['company_logo_path'])): ?>
                <img src="<?= htmlspecialchars($brandingSettings['company_logo_path']) ?>"
                     alt="<?= htmlspecialchars($brandingSettings['business_name']) ?>"
                     style="max-width: <?= intval($brandingSettings['email_logo_width'] ?? 150) ?>px; height: auto;">
            <?php else: ?>
                <h1 style="color: white; margin: 0;">
                    <?= htmlspecialchars($brandingSettings['business_name']) ?>
                </h1>
            <?php endif; ?>
        </td>
    </tr>
</table>
```

**Example: Navbar Logo**
```php
<span class="navbar-brand">
    <?php if (!empty($brandingSettings['company_logo_small_path'])): ?>
        <img src="<?= htmlspecialchars($brandingSettings['company_logo_small_path']) ?>"
             alt="<?= htmlspecialchars($brandingSettings['business_name']) ?>"
             style="height: 32px; width: auto;">
    <?php else: ?>
        <i class="bi bi-water"></i>
    <?php endif; ?>
    <?= htmlspecialchars($brandingSettings['business_name']) ?>
</span>
```

---

## Security

### File Upload Security

The `FileUploadService` implements multiple security layers:

1. **File Type Validation**
   - Whitelist of allowed extensions (jpg, jpeg, png, svg, webp)
   - MIME type verification
   - Image format validation using `getimagesize()`

2. **File Size Limits**
   - Logo uploads: 5MB maximum
   - Configurable per file type

3. **Filename Sanitization**
   - Special characters removed
   - Timestamp + random string added
   - Prevents file overwrites and path traversal

4. **Upload Directory Protection**
   - `.htaccess` prevents PHP execution
   - Directory listing disabled
   - Only specific file types accessible

5. **Database Tracking**
   - All uploads logged in `file_uploads` table
   - User attribution for accountability
   - File size and type recorded

### Example `.htaccess` Protection

```apache
# Prevent PHP execution in uploads directory
<FilesMatch "\.php$">
    Require all denied
</FilesMatch>

# Allow only specific file types
<FilesMatch "\.(jpg|jpeg|png|gif|svg|webp|pdf)$">
    Require all granted
</FilesMatch>

# Disable directory listings
Options -Indexes
```

---

## Advanced Customization

### Custom Logo Sizes

You can adjust logo display sizes via settings:

```php
// Update logo width for invoices
$settingsService->updateSetting('general', 'invoice_logo_width', 300);

// Update logo width for emails
$settingsService->updateSetting('general', 'email_logo_width', 180);
```

### Programmatic Logo Upload

```php
use App\Services\FileUploadService;

$uploadService = new FileUploadService();

// Upload from $_FILES
$result = $uploadService->upload($_FILES['logo'], 'logo', 'custom_logo_name');

if ($result['success']) {
    echo "Logo uploaded: " . $result['path'];
    // Save to settings
    $settingsService->updateSetting('general', 'company_logo_path', $result['path']);
} else {
    echo "Error: " . $result['error'];
}
```

### Delete Old Logo

```php
use App\Services\FileUploadService;

$uploadService = new FileUploadService();
$oldLogoPath = $settingsService->getSetting('general', 'company_logo_path');

if ($oldLogoPath && $uploadService->delete($oldLogoPath)) {
    echo "Old logo deleted successfully";
}
```

---

## Email Newsletter Integration

### Adding Logo to Email Campaigns

When creating email campaigns, the logo is automatically included if `use_company_logo` is enabled.

**Database Fields** (from migration 016):
```sql
ALTER TABLE email_templates
ADD COLUMN use_company_logo BOOLEAN DEFAULT TRUE,
ADD COLUMN header_color VARCHAR(7) DEFAULT '#0066CC';

ALTER TABLE email_campaigns
ADD COLUMN use_custom_branding BOOLEAN DEFAULT FALSE,
ADD COLUMN custom_logo_path VARCHAR(255),
ADD COLUMN custom_header_color VARCHAR(7);
```

**Example Email Template with Logo:**
```php
<?php if ($campaign['use_custom_branding'] && $campaign['custom_logo_path']): ?>
    <img src="<?= $campaign['custom_logo_path'] ?>">
<?php elseif ($brandingSettings['company_logo_path']): ?>
    <img src="<?= $brandingSettings['company_logo_path'] ?>">
<?php else: ?>
    <h1><?= $brandingSettings['business_name'] ?></h1>
<?php endif; ?>
```

---

## Troubleshooting

### Logo Not Appearing

**Problem:** Logo uploaded but not showing

**Solutions:**
1. **Check file permissions:**
   ```bash
   chmod 755 /home/wrnash1/development/nautilus/public/uploads
   chmod 644 /home/wrnash1/development/nautilus/public/uploads/logos/*
   ```

2. **Verify file path:**
   ```sql
   SELECT setting_value FROM settings WHERE setting_key = 'company_logo_path';
   ```

3. **Check web server access:**
   - Visit `http://yoursite.com/uploads/logos/your-logo.png` directly
   - Ensure `.htaccess` isn't blocking access

4. **Clear browser cache:**
   - Hard refresh: Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)

### Upload Fails

**Problem:** "Upload failed" error message

**Solutions:**
1. **Check file size:**
   - Logo files must be < 5MB
   - Check PHP upload limits: `upload_max_filesize` and `post_max_size` in `php.ini`

2. **Verify file type:**
   - Only JPG, PNG, SVG, WebP allowed
   - Check MIME type is correct

3. **Directory permissions:**
   ```bash
   # Make uploads directory writable
   chmod 755 /home/wrnash1/development/nautilus/public/uploads/logos
   chown www-data:www-data /home/wrnash1/development/nautilus/public/uploads/logos
   ```

4. **Check PHP errors:**
   ```bash
   tail -f /var/log/php_errors.log
   ```

### Logo Quality Issues

**Problem:** Logo looks blurry or pixelated

**Solutions:**
1. Upload higher resolution image (2x recommended size)
2. Use SVG format for perfect scaling
3. Use PNG with transparency instead of JPG

---

## Migrating from Old System

If you had hardcoded business information:

1. **Extract existing values:**
   - Business name, address, phone from old templates
   - Logo images from `/assets/` or old locations

2. **Upload via admin panel:**
   - Settings → General → Company Branding
   - Upload new logo files
   - Fill in business information

3. **Old files can be deleted:**
   - After confirming new logos work
   - Remove hardcoded references in templates

---

## Future Enhancements

Planned features:
- [ ] Multiple logo variants (dark/light mode)
- [ ] Logo watermarks for images
- [ ] Automated logo optimization
- [ ] Logo usage analytics
- [ ] Brand kit management (fonts, additional colors)
- [ ] Social media logo variations

---

## Support

**Questions or Issues:**
- Check this guide first
- Review [`docs/SECURITY.md`](docs/SECURITY.md) for security concerns
- Check file upload logs in database: `SELECT * FROM file_uploads WHERE file_type = 'logo'`

---

## Version Information

- **Feature Added:** 2025-10-19
- **Nautilus Version:** 6.0
- **Migration:** 016_add_branding_and_logo_support.sql
- **Related Migrations:** 015 (encryption), 016 (branding)

---

## Files Reference

### Created Files:
- `database/migrations/016_add_branding_and_logo_support.sql`
- `app/Services/FileUploadService.php`
- `app/Views/components/secure-input.php` (from security feature)
- `public/uploads/.htaccess`
- `public/uploads/index.php`
- `BRANDING_GUIDE.md` (this file)

### Modified Files:
- `app/Controllers/Admin/SettingsController.php` - Added `uploadLogo()` method
- `app/Views/admin/settings/general.php` - Added branding upload section
- `app/Views/pos/receipt.php` - Dynamic logo display
- `app/Views/layouts/app.php` - Navbar logo and favicon
- `routes/web.php` - Added `/admin/settings/upload-logo` route

### Database Tables:
- `settings` - Added branding fields
- `file_uploads` - New table for tracking uploads
- `email_templates` - Added logo fields
- `email_campaigns` - Added custom branding fields

---

**End of Branding Guide**
