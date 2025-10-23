# Nautilus Storefront Theme System

## Overview

The Nautilus v6 Storefront Theme System is a comprehensive, database-driven solution that allows you to create a fully customizable online store. Customers visiting your domain will see a beautifully themed e-commerce site, while staff access the admin backend at `/admin`.

---

## Architecture

### Public vs Admin

- **Public Storefront** (`/`) - Customer-facing online store with products, courses, trips, and rentals
- **Admin Backend** (`/admin`) - Staff portal for POS, inventory, CRM, repairs, and storefront configuration

### Key Components

1. **ThemeEngine** - Manages theme configuration and rendering
2. **StorefrontSettingsService** - Handles store settings and navigation
3. **HomeController** - Powers the public homepage
4. **StorefrontController** - Admin interface for theme customization

---

## Installation

### 1. Run Database Migration

Execute the migration file to create theme tables:

```bash
mysql -u root -p nautilus < database/migrations/025_create_storefront_theme_system.sql
```

Or via PHP admin panel or phpMyAdmin.

### 2. Verify Installation

The migration creates these tables:
- `storefront_settings` - General store configuration
- `theme_config` - Visual theme settings
- `homepage_sections` - Drag-and-drop homepage builder
- `navigation_menus` - Custom navigation
- `promotional_banners` - Site-wide banners
- `theme_assets` - Logos, images, fonts

### 3. Access Admin Panel

Navigate to `/admin/storefront` to start configuring your store.

---

## Features

### 1. Theme Designer

**Location:** `/admin/storefront/theme-designer`

Configure all visual aspects:

#### Colors
- Primary, secondary, accent colors
- Background colors (body, header, footer, hero)
- Text and link colors
- Success, danger, warning, info colors

#### Typography
- Font families (primary and heading)
- Font sizes (base, h1, h2, h3)
- Line height

#### Layout
- Container max width
- Border radius
- Spacing units
- Hero height
- Products per row

#### Header/Navigation
- Header style (transparent, solid, gradient, sticky)
- Show/hide search bar
- Show/hide cart icon
- Show/hide account icon

#### Product Display
- Product card style (classic, modern, minimal, overlay)
- Show/hide ratings
- Show/hide quick view
- Show/hide add to cart button
- Show/hide wishlist button

#### Footer
- Footer style (simple, detailed, mega)
- Show/hide newsletter signup
- Show/hide social links
- Show/hide payment icons

#### Custom Code
- Custom CSS
- Custom JavaScript
- Custom HTML in `<head>`

### 2. Homepage Builder

**Location:** `/admin/storefront/homepage-builder`

Drag-and-drop sections:

- **Hero** - Large banner with CTA buttons
- **Featured Products** - Showcase specific products
- **Categories** - Display all or featured categories
- **Courses** - Upcoming PADI courses
- **Trips** - Dive trip packages
- **Testimonials** - Customer reviews
- **Blog Posts** - Latest articles
- **Brands** - Partner/vendor logos
- **Newsletter** - Email signup form
- **Custom HTML** - Any custom content
- **Video** - Embedded videos
- **Image Banner** - Promotional images
- **Countdown Timer** - Limited-time offers

Each section has:
- Title and subtitle
- Display order (drag-and-drop)
- Active/inactive toggle
- Section-specific configuration (JSON)
- Background color/image
- Padding customization

### 3. Store Settings

**Location:** `/admin/storefront/settings`

#### General
- Store name and tagline
- Contact email and phone
- Physical address

#### SEO
- Meta title and description
- Keywords
- Google Analytics ID
- Facebook Pixel ID

#### Features
- Enable/disable reviews
- Enable/disable wishlist
- Enable/disable guest checkout
- Enable/disable live chat
- Show stock quantities
- Low stock threshold

#### Checkout
- Require account for checkout
- Enable coupons
- Enable gift cards
- Tax calculation
- Default tax rate

#### Shipping
- Free shipping threshold
- Enable local pickup
- Shipping rate calculator

#### Social Media
- Facebook, Instagram, Twitter, YouTube URLs

#### Integrations
- Google Analytics
- Facebook Pixel
- Google Tag Manager

### 4. Navigation Manager

**Location:** `/admin/storefront/navigation`

Create custom menus for:
- Header navigation
- Footer links
- Sidebar (if theme supports)
- Mobile menu

Features:
- Nested menus (dropdowns)
- Icons for menu items
- Link targets (_self, _blank)
- Visibility controls (all, customers, guests)
- Authentication requirements

### 5. Theme Assets

Upload and manage:
- **Logo** - Primary site logo
- **Favicon** - Browser tab icon
- **Hero Images** - Homepage backgrounds
- **Category Images** - Category thumbnails

---

## Usage Guide

### Quick Start

1. **Install the migration** (see Installation section)

2. **Access admin panel:**
   ```
   https://yourdomain.com/admin/storefront
   ```

3. **Configure theme colors:**
   - Go to Theme Designer
   - Set primary color (e.g., #0d6efd for blue)
   - Set accent color
   - Upload logo

4. **Build homepage:**
   - Go to Homepage Builder
   - Hero section is already created
   - Customize title and CTAs
   - Add Featured Products section
   - Add Categories section
   - Save changes

5. **View your store:**
   ```
   https://yourdomain.com/
   ```

### Customizing the Default Theme

The migration includes a default "Ascuba-inspired" theme with:
- Navy blue hero sections (#01012e)
- Bootstrap primary colors
- 4 products per row
- Featured categories grid
- Newsletter signup
- Brand showcase

To modify:
1. Go to `/admin/storefront/theme-designer`
2. Update colors, fonts, or layout
3. Click "Save Theme"
4. Preview at `/admin/storefront/preview`

### Adding Products to Homepage

Products displayed in "Featured Products" section must have:
- `is_active = TRUE`
- `is_featured = TRUE` (for featured filter)
- Stock quantity > 0
- At least one product image

Update products in `/products` admin panel.

### Creating New Themes

1. Go to `/admin/storefront/theme-designer`
2. Click "Create New Theme"
3. Configure all settings
4. Click "Activate Theme" when ready

---

## File Structure

```
app/
├── Controllers/
│   ├── HomeController.php                    # Public homepage
│   └── Admin/Storefront/
│       └── StorefrontController.php          # Admin theme config
├── Services/Storefront/
│   ├── ThemeEngine.php                       # Theme management
│   └── StorefrontSettingsService.php         # Settings service
├── Views/
│   ├── storefront/
│   │   ├── layouts/main.php                  # Main layout
│   │   ├── home.php                          # Homepage template
│   │   ├── partials/
│   │   │   ├── header.php                    # Header/nav
│   │   │   ├── footer.php                    # Footer
│   │   │   └── banner.php                    # Promo banners
│   │   └── sections/
│   │       ├── hero.php                      # Hero section
│   │       ├── featured_products.php         # Product grid
│   │       ├── featured_categories.php       # Category grid
│   │       ├── courses.php                   # Course listings
│   │       ├── trips.php                     # Trip listings
│   │       ├── newsletter.php                # Newsletter form
│   │       └── brands.php                    # Brand logos
│   └── admin/storefront/
│       └── index.php                         # Admin dashboard
public/
├── assets/
│   ├── css/storefront.css                    # Storefront styles
│   └── js/storefront.js                      # Storefront JS
database/migrations/
└── 025_create_storefront_theme_system.sql    # Migration file
routes/
└── web.php                                   # Routes configuration
```

---

## API Reference

### ThemeEngine Methods

```php
use App\Services\Storefront\ThemeEngine;

$theme = new ThemeEngine();

// Get active theme
$activeTheme = $theme->getActiveTheme();

// Get all themes
$allThemes = $theme->getAllThemes();

// Create theme
$themeId = $theme->createTheme([
    'theme_name' => 'My Custom Theme',
    'primary_color' => '#FF5733'
]);

// Update theme
$theme->updateTheme($themeId, [
    'primary_color' => '#FF0000'
]);

// Set as active
$theme->setActiveTheme($themeId);

// Generate CSS
$css = $theme->generateThemeCSS();

// Get homepage sections
$sections = $theme->getHomepageSections();

// Upload asset
$assetId = $theme->uploadAsset($themeId, 'logo', '/path/to/logo.png', [
    'is_primary' => true
]);
```

### StorefrontSettingsService Methods

```php
use App\Services\Storefront\StorefrontSettingsService;

$settings = new StorefrontSettingsService();

// Get single setting
$storeName = $settings->get('store_name', 'Default Name');

// Get multiple settings
$values = $settings->getMany(['store_name', 'store_tagline']);

// Get by category
$general = $settings->getByCategory('general');

// Set setting
$settings->set('store_name', 'My Dive Shop');

// Set multiple
$settings->setMany([
    'store_name' => 'My Dive Shop',
    'contact_email' => 'info@mydiveshop.com'
]);

// Navigation
$headerMenu = $settings->getNavigationMenu('header');

// Banners
$banners = $settings->getActiveBanners('top_bar', 'home');
```

---

## CSS Variables

The theme system generates CSS custom properties (variables) from database config:

```css
:root {
  /* Colors */
  --primary-color: #0d6efd;
  --secondary-color: #6c757d;
  --accent-color: #0dcaf0;

  /* Backgrounds */
  --body-bg: #ffffff;
  --header-bg: #ffffff;
  --footer-bg: #212529;
  --hero-bg: #01012e;

  /* Typography */
  --font-primary: system-ui, sans-serif;
  --font-heading: system-ui, sans-serif;
  --font-size-base: 16px;
  --line-height: 1.5;

  /* Layout */
  --container-max-width: 1200px;
  --border-radius: 0.375rem;
  --spacing-unit: 1rem;
}
```

Use in custom CSS:

```css
.my-element {
    background-color: var(--primary-color);
    font-family: var(--font-primary);
    border-radius: var(--border-radius);
}
```

---

## JavaScript Functions

### Add to Cart

```javascript
addToCart(productId, quantity = 1)
```

### Add to Wishlist

```javascript
addToWishlist(productId)
```

### Show Notification

```javascript
showNotification('Message here', 'success')
// Types: 'success', 'error', 'warning', 'info'
```

### Quick View

```javascript
quickView(productId)
```

---

## Troubleshooting

### Theme not loading

1. Check database - ensure `theme_config` table exists
2. Verify active theme: `SELECT * FROM theme_config WHERE is_active = TRUE`
3. Clear cache (if caching enabled)
4. Check file permissions on `/public/assets/`

### Homepage sections not showing

1. Verify sections exist: `SELECT * FROM homepage_sections WHERE is_active = TRUE`
2. Check section data is being loaded in HomeController
3. Ensure section template files exist in `/app/Views/storefront/sections/`

### Products not displaying

1. Check product status: `is_active = TRUE`
2. Verify stock: `stock_quantity > 0`
3. For featured products: `is_featured = TRUE`
4. Check product images exist

### Navigation menus missing

1. Check navigation data: `SELECT * FROM navigation_menus WHERE is_active = TRUE`
2. Verify menu location (header, footer)
3. Check parent/child relationships

---

## Advanced Customization

### Creating Custom Sections

1. Create new section file: `/app/Views/storefront/sections/my_section.php`

2. Add section type to database:
```sql
INSERT INTO homepage_sections (theme_id, section_type, section_title, display_order, is_active)
VALUES (1, 'my_section', 'My Custom Section', 10, TRUE);
```

3. Load data in HomeController:
```php
case 'my_section':
    $data[$sectionId] = $this->getMyCustomData();
    break;
```

### Custom Fonts

1. Upload font files to `/public/assets/fonts/`

2. Add @font-face in theme custom CSS:
```css
@font-face {
    font-family: 'MyFont';
    src: url('/assets/fonts/myfont.woff2');
}
```

3. Set in theme config: `font_family_primary = "'MyFont', sans-serif"`

### Multi-language Support

The Nautilus system includes translation support. To enable for storefront:

1. Add translation strings in `/app/Lang/{locale}/storefront.php`
2. Use Translator service in views:
```php
<?= __('storefront.welcome') ?>
```

---

## Performance Optimization

### Caching

Enable settings cache:
```php
$this->cache->set('storefront_settings', $settings, 3600);
```

### Image Optimization

1. Use compressed images (WebP format recommended)
2. Implement lazy loading: `<img class="lazy" data-src="...">`
3. Set proper image dimensions

### CSS/JS Minification

Minify custom CSS/JS before saving to theme config.

---

## Security Considerations

1. **CSRF Protection** - All POST routes use `CsrfMiddleware`
2. **Input Sanitization** - All user input is sanitized via `htmlspecialchars()`
3. **File Uploads** - Use `FileUploadService` with proper validation
4. **XSS Prevention** - Escape all output in templates
5. **SQL Injection** - Use prepared statements (PDO)

---

## Support & Documentation

- **Main Documentation:** [README.md](README.md)
- **Database Schema:** [database/migrations/](database/migrations/)
- **API Documentation:** See "API Reference" section above

---

## License

Proprietary - Nautilus Dive Shop Management System v6

---

## Changelog

### v1.0.0 (2024)
- Initial release
- Database-driven theme system
- Homepage builder with 14 section types
- Theme designer with 60+ customization options
- Navigation manager
- Settings management
- Asset uploader
- Default "Ascuba-inspired" theme
- CSS variable system
- Responsive design
- SEO optimization
- Analytics integration

---

## Credits

Inspired by modern e-commerce platforms and scuba diving industry leaders like ascubadiving.com.

Built with:
- PHP 8.2+
- MySQL 8.0+
- Bootstrap 5.3.2
- Bootstrap Icons 1.11.1
- Vanilla JavaScript
