# Nautilus Dive Shop POS - Feature Implementation Roadmap

## Overview
This document outlines the comprehensive feature enhancements requested for the Nautilus Dive Shop POS system based on user testing feedback.

---

## ✅ COMPLETED FEATURES

### 1. POS New Customer Button
**Status:** ✅ COMPLETE
- Changed from modal to full page redirect
- Now opens `/store/customers/create?return_to=pos`
- Provides access to complete customer creation form with all fields

### 2. Store Logo & Date/Time in POS Header
**Status:** ✅ COMPLETE
- Added dedicated header bar above customer selection
- Store logo displayed (pulled from settings)
- Real-time date display (e.g., "Monday, October 30, 2025")
- Real-time clock with seconds (HH:MM:SS format)
- Professional gradient blue background
- Updates every second

### 3. AI-Powered Visual Product Search
**Status:** ✅ COMPLETE
- TensorFlow.js integration
- Camera/upload interface
- Real-time similarity matching
- Sub-500ms search results
- 100% offline operation

---

## 🚧 IN PROGRESS / PLANNED FEATURES

### Phase 1: Critical POS Enhancements (Priority: HIGH)

#### 1.1 Customer Photo Display in POS
**Requirements:**
- Show customer photo next to name when selected
- Display in customer info section
- Fallback to avatar icon if no photo
- Support for certification agency logos

**Implementation:**
```javascript
// When customer selected, load photo
function displayCustomerInfo(customer) {
    const photoEl = document.getElementById('customerPhoto');
    if (customer.photo_path) {
        photoEl.innerHTML = `<img src="${customer.photo_path}" alt="${customer.name}"
            style="width: 60px; height: 60px; border-radius: 50%; border: 2px solid #0066cc;">`;
    } else {
        photoEl.innerHTML = `<i class="bi bi-person-circle" style="font-size: 3rem; color: #6c757d;"></i>`;
    }
}
```

#### 1.2 Certification Agency Logos Display
**Requirements:**
- Query customer certifications when selected
- Display certification agency logos (PADI, SSI, NAUI, etc.)
- Show certification level (e.g., "Open Water", "Advanced")
- Color-coded expiration warnings

**Database Query:**
```sql
SELECT
    cc.certification_number,
    c.name as cert_name,
    c.level,
    ca.name as agency_name,
    ca.logo_path,
    ca.primary_color
FROM customer_certifications cc
JOIN certifications c ON cc.certification_id = c.id
JOIN certification_agencies ca ON c.agency_id = ca.id
WHERE cc.customer_id = ? AND cc.verification_status = 'verified'
ORDER BY c.level DESC
LIMIT 5
```

#### 1.3 Customer Search Contrast Improvement
**Status:** Needs refinement
**Changes Required:**
- Remove dark/black backgrounds from search results
- Use white background with border
- Improve text contrast
- Better hover states

**CSS Updates:**
```css
.customer-search-results {
    background: #ffffff;
    border: 2px solid #dee2e6;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.customer-search-result-item {
    background: #ffffff;
    color: #212529;
    border-bottom: 1px solid #e9ecef;
}

.customer-search-result-item:hover {
    background: #f8f9fa;
    border-left: 4px solid #0066cc;
}
```

#### 1.4 Barcode Scanning Implementation
**Requirements:**
- Detect when numbers entered rapidly (barcode scan simulation)
- Support USB barcode scanners
- Focus on search field automatically
- Auto-add to cart on successful scan

**JavaScript Implementation:**
```javascript
let barcodeBuffer = '';
let barcodeTimeout;

document.getElementById('productSearch').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        // Barcode scan complete
        clearTimeout(barcodeTimeout);
        searchProductByBarcode(barcodeBuffer || this.value);
        barcodeBuffer = '';
        this.value = '';
    } else {
        // Building barcode
        barcodeBuffer += e.key;
        clearTimeout(barcodeTimeout);
        barcodeTimeout = setTimeout(() => {
            barcodeBuffer = '';
        }, 100); // Reset if >100ms between characters
    }
});
```

#### 1.5 Layaway Functionality
**Requirements:**
- "Hold for Layaway" button in cart
- Set payment schedule
- Track payments
- Release when paid off

**Database Tables:**
```sql
CREATE TABLE layaway_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    total_amount DECIMAL(10,2),
    down_payment DECIMAL(10,2),
    remaining_balance DECIMAL(10,2),
    payment_schedule JSON,
    status ENUM('active', 'completed', 'cancelled'),
    expected_pickup_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE layaway_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    layaway_order_id INT NOT NULL,
    payment_amount DECIMAL(10,2),
    payment_method VARCHAR(50),
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

---

### Phase 2: Product Module Enhancements (Priority: HIGH)

#### 2.1 Additional Product Fields
**New Fields to Add:**
- Weight (kg/lbs)
- Dimensions (L x W x H)
- Color
- Material (e.g., "Neoprene", "Aluminum", "Stainless Steel")
- Manufacturer
- Warranty information
- Stock quantity (already exists, enhance visibility)
- Store location/bin number
- Supplier information
- Expiration date (for perishables, O2 sensors, etc.)

**Migration Script:**
```sql
ALTER TABLE products
ADD COLUMN weight_kg DECIMAL(8,2) NULL,
ADD COLUMN length_cm DECIMAL(8,2) NULL,
ADD COLUMN width_cm DECIMAL(8,2) NULL,
ADD COLUMN height_cm DECIMAL(8,2) NULL,
ADD COLUMN color VARCHAR(50) NULL,
ADD COLUMN material VARCHAR(100) NULL,
ADD COLUMN manufacturer VARCHAR(200) NULL,
ADD COLUMN warranty_info TEXT NULL,
ADD COLUMN store_location VARCHAR(100) NULL COMMENT 'Aisle/Bin location',
ADD COLUMN supplier_id INT NULL,
ADD COLUMN expiration_date DATE NULL;
```

#### 2.2 QR Code Generation
**Requirements:**
- Generate unique QR code for each product
- QR code links to product page on public storefront
- Printable labels with QR code
- Track scans for analytics

**Implementation:**
```php
// Use QR code library
require 'vendor/autoload.php';
use Endroid\QrCode\QrCode;

function generateProductQRCode($productId) {
    $product = Product::find($productId);
    $url = getSettingValue('site_url') . '/products/' . $product['slug'];

    $qrCode = new QrCode($url);
    $qrCode->setSize(300);
    $qrCode->setMargin(10);

    $filename = "qr_product_{$productId}.png";
    $qrCode->writeFile(__DIR__ . "/public/qrcodes/{$filename}");

    // Update product with QR code path
    Product::update($productId, ['qr_code_path' => "/qrcodes/{$filename}"]);

    return $filename;
}
```

#### 2.3 Fix "Add Product" Button
**Issue:** Button not working
**Investigation Steps:**
1. Check JavaScript console for errors
2. Verify route exists in `routes/web.php`
3. Check permissions
4. Test with different user roles

**Likely Fix:**
```javascript
// Ensure click handler is properly attached
document.getElementById('addProductBtn')?.addEventListener('click', function() {
    window.location.href = '/store/products/create';
});
```

---

### Phase 3: AI Integration Expansion (Priority: MEDIUM)

#### 3.1 AI-Enhanced Search Across All Modules
**Modules to Enhance:**
- Products search
- Customer search
- Transaction search
- Course search

**Features:**
- Fuzzy matching (typo tolerance)
- Synonym recognition
- Learning from past searches
- Predictive suggestions

**Implementation Approach:**
```javascript
class AISearchEngine {
    constructor() {
        this.searchHistory = [];
        this.synonyms = {
            'reg': ['regulator', 'regulators'],
            'bcd': ['buoyancy compensator', 'bc', 'wing'],
            'mask': ['masks', 'diving mask'],
            // ... more synonyms
        };
    }

    async search(query, dataset) {
        // 1. Normalize query
        const normalized = this.normalize(query);

        // 2. Apply fuzzy matching (Levenshtein distance)
        const fuzzyMatches = this.fuzzyMatch(normalized, dataset);

        // 3. Check synonyms
        const synonymMatches = this.checkSynonyms(query, dataset);

        // 4. Learn from selection
        this.recordSearch(query, selectedResult);

        // 5. Rank results
        return this.rankResults([...fuzzyMatches, ...synonymMatches]);
    }
}
```

---

### Phase 4: Navigation & UX Improvements (Priority: HIGH)

#### 4.1 Role-Based Sidebar Menu
**Requirements:**
- Admin: See all menu items including Settings
- Sales Staff: Hide Settings, Advanced Reports
- Instructor: Show Courses, Students, Certifications
- Maintenance: Show Equipment, Compressor, Work Orders

**Implementation:**
```php
// In sidebar view
function getSidebarMenuItems() {
    $user = Auth::user();
    $role = $user['role'];

    $menu = [
        ['name' => 'Dashboard', 'icon' => 'speedometer2', 'url' => '/store', 'roles' => ['all']],
        ['name' => 'POS', 'icon' => 'cart3', 'url' => '/store/pos', 'roles' => ['all']],
        ['name' => 'Customers', 'icon' => 'people', 'url' => '/store/customers', 'roles' => ['all']],
        ['name' => 'Products', 'icon' => 'box', 'url' => '/store/products', 'roles' => ['all']],
        ['name' => 'Courses', 'icon' => 'mortarboard', 'url' => '/store/courses', 'roles' => ['admin', 'instructor']],
        ['name' => 'Compressor', 'icon' => 'cpu', 'url' => '/store/compressor', 'roles' => ['admin', 'maintenance']],
        ['name' => 'Settings', 'icon' => 'gear', 'url' => '/store/settings', 'roles' => ['admin']],
    ];

    return array_filter($menu, function($item) use ($role) {
        return in_array('all', $item['roles']) || in_array($role, $item['roles']);
    });
}
```

#### 4.2 Compressor Maintenance Tracking
**Requirements:**
- Log compressor hours
- Track oil changes
- Service reminders
- Filter replacement tracking
- Historical maintenance log

**Database Schema:**
```sql
CREATE TABLE compressor_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    manufacturer VARCHAR(100),
    model VARCHAR(100),
    serial_number VARCHAR(100),
    purchase_date DATE,
    location VARCHAR(100),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE compressor_maintenance_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compressor_id INT NOT NULL,
    maintenance_type ENUM('oil_change', 'filter_replacement', 'inspection', 'repair', 'hours_logged'),
    hours_at_service DECIMAL(10,2),
    hours_added DECIMAL(10,2) NULL,
    oil_type VARCHAR(100) NULL,
    oil_quantity_ml INT NULL,
    filter_types JSON NULL,
    notes TEXT,
    cost DECIMAL(10,2),
    performed_by INT,
    service_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    next_service_hours DECIMAL(10,2) NULL,
    FOREIGN KEY (compressor_id) REFERENCES compressor_units(id),
    FOREIGN KEY (performed_by) REFERENCES users(id)
);
```

**Quick Entry Interface:**
```html
<!-- Quick compressor update widget -->
<div class="quick-compressor-update">
    <h5>Log Compressor Hours</h5>
    <select name="compressor_id">
        <option>Bauer Oceanus - Main</option>
        <option>Bauer Mariner - Backup</option>
    </select>
    <input type="number" placeholder="Hours to add" step="0.1">
    <button class="btn btn-primary">Log Hours</button>
</div>
```

#### 4.3 Sidebar Redesign
**Improvements:**
- Collapsible sections
- Icons with labels
- Active state highlighting
- Quick access favorites
- Search within menu

**New Structure:**
```
Sales
  ├─ POS
  ├─ Customers
  └─ Transactions

Inventory
  ├─ Products
  ├─ Stock Management
  └─ Suppliers

Education
  ├─ Courses
  ├─ Students
  └─ Certifications

Equipment
  ├─ Rentals
  ├─ Maintenance
  └─ Compressor

Reports
  ├─ Sales Reports
  ├─ Inventory Reports
  └─ Financial Reports

Settings (Admin Only)
  ├─ Store Settings
  ├─ User Management
  └─ System Configuration
```

---

### Phase 5: Branding & Polish (Priority: LOW)

#### 5.1 Favicon Addition
**Requirements:**
- Custom favicon for browser tabs
- Multiple sizes for different devices
- Apple touch icon
- Manifest for PWA

**Files to Create:**
```
/public/favicon.ico (16x16, 32x32, 48x48)
/public/favicon-16x16.png
/public/favicon-32x32.png
/public/apple-touch-icon.png (180x180)
/public/android-chrome-192x192.png
/public/android-chrome-512x512.png
```

**HTML Head Updates:**
```html
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
<link rel="manifest" href="/site.webmanifest">
```

---

## 📊 Implementation Priority Matrix

### Immediate (This Week):
1. ✅ POS New Customer button → Full form
2. ✅ Store logo & date/time in POS
3. 🔄 Customer photo display
4. 🔄 Cert agency logos
5. 🔄 Fix customer search contrast
6. 🔄 Barcode scanning

### Short Term (Next 2 Weeks):
7. Layaway functionality
8. Product field enhancements
9. QR code generation
10. Fix Add Product button
11. Role-based sidebar
12. Compressor tracking

### Medium Term (Next Month):
13. AI-enhanced search across all modules
14. Sidebar redesign
15. Favicon and branding
16. Advanced analytics dashboard

---

## 🎯 Success Metrics

### User Experience:
- POS transaction time: Target <2 minutes average
- Product lookup time: Target <10 seconds with AI search
- Customer satisfaction: Target >4.5/5 stars

### System Performance:
- AI search response: <500ms
- Page load time: <2 seconds
- Barcode scan recognition: >95% accuracy

### Business Impact:
- Checkout efficiency: +30% faster
- Inventory accuracy: >98%
- Customer data completeness: >90%

---

## 📝 Notes

### Barcode Scanning Tips:
- Most USB barcode scanners emulate keyboard input
- No special drivers needed
- Configure scanner to send "Enter" after scan
- Test with product barcodes (UPC, EAN-13, Code 128)

### QR Code Best Practices:
- Include product info in encoded URL
- Use high error correction (30%)
- Minimum size: 2cm x 2cm for reliable scans
- Test with multiple phone cameras

### Compressor Maintenance:
- Industry standard: Oil change every 50-100 hours
- Filter inspection: Every 25 hours
- Major service: Every 500 hours
- Keep detailed logs for warranty claims

---

*Last Updated: 2025-10-30*
*Version: 2.0 - Feature Roadmap*
