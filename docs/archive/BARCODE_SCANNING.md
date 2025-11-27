# Barcode Scanning Guide

## Overview

Nautilus supports barcode scanning for quick product lookup and adding items to transactions in the Point of Sale (POS) system. This guide explains how barcode scanning works, how to set it up, and how to troubleshoot common issues.

## How It Works

### Barcode Field in Products

Every product in Nautilus has a `barcode` field that stores the product's barcode number. This field was added in Migration 035 along with other product enhancements.

**Barcode Field Location:**
- Database: `products.barcode` (VARCHAR 100)
- Forms: Product Create/Edit pages
- Display: Product detail pages, POS

### Scanning Methods

Nautilus supports two types of barcode scanning:

#### 1. USB Barcode Scanner (Recommended)

**How It Works:**
- USB barcode scanners act as keyboard input devices
- When you scan a barcode, it "types" the barcode number into the focused input field
- Works automatically with no special configuration needed

**Compatible Scanners:**
- Any USB HID (Human Interface Device) barcode scanner
- Most common brands: Honeywell, Zebra, Symbol, Datalogic
- 1D scanners (standard barcodes)
- 2D scanners (QR codes + standard barcodes)

#### 2. Product Search in POS

**How It Works:**
- Enter barcode number in product search field
- System searches `barcode` column
- Matching product displayed
- Click to add to cart

## Setup Instructions

### Setting Up USB Barcode Scanner

**Requirements:**
- USB barcode scanner
- Computer with USB port
- Linux, Windows, or macOS

**Setup Steps:**

1. **Plug in Scanner**
   - Connect USB scanner to computer
   - Most scanners work immediately (plug-and-play)
   - Scanner LED should light up

2. **Test Scanner**
   - Open text editor or terminal
   - Scan a barcode
   - Numbers should appear where cursor is
   - Scanner should "press Enter" after barcode

3. **Configure Scanner Settings** (Optional)

   Most scanners work out of the box, but you can configure:

   **Suffix Character:**
   - Default: "Enter" key after each scan
   - Recommended: Keep default
   - Alternative: "Tab" key (moves to next field)

   **Prefix Character:**
   - Default: None
   - Can add prefix if needed
   - Not usually necessary

   **Scan Mode:**
   - Trigger mode: Press button to scan
   - Presentation mode: Auto-scan when barcode presented
   - Continuous mode: Keeps scanning

   *Refer to your scanner's manual for configuration barcodes*

### Adding Barcodes to Products

**Method 1: Manual Entry**

1. Navigate to: Products → [Product Name] → Edit
2. Find "Barcode" field
3. Enter barcode number
4. Save

**Method 2: Scan During Entry**

1. Navigate to: Products → Add Product
2. Click in "Barcode" field
3. Scan product barcode with scanner
4. Barcode number auto-fills
5. Continue filling other fields
6. Save

**Method 3: Bulk Import**

```sql
-- Update products with barcodes via SQL
UPDATE products SET barcode = '012345678901' WHERE id = 123;

-- Or bulk import from CSV
LOAD DATA INFILE '/path/to/barcodes.csv'
INTO TABLE products
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\n'
(id, barcode);
```

## Using Barcodes in POS

### Quick Product Add

**Method 1: Scan to Search**

1. Open Point of Sale
2. Click in product search field (top of screen)
3. Scan product barcode
4. Product appears in search results
5. Click product or press Enter to add to cart

**Method 2: Type Barcode**

1. Click in product search field
2. Type barcode number manually
3. Press Enter or click Search
4. Select product from results

**Method 3: Product Tiles**

- Products without barcodes can still be added via product tiles
- Click product tile to add to cart

### Barcode Search Behavior

**Search Priority:**
1. Exact barcode match (fastest)
2. SKU match
3. Product name match

**Search Features:**
- Case-insensitive
- Searches across multiple fields simultaneously
- Returns results immediately

## Barcode Types Supported

### Standard Formats

Nautilus stores barcodes as text strings, supporting all common formats:

**1D Barcodes:**
- UPC-A (12 digits) - Most common in retail
- UPC-E (8 digits) - Short version of UPC-A
- EAN-13 (13 digits) - International standard
- EAN-8 (8 digits) - Short version of EAN-13
- Code 39 - Alphanumeric
- Code 128 - Full ASCII
- ITF (Interleaved 2 of 5)

**2D Barcodes:**
- QR Code (see QR Code section)
- Data Matrix
- PDF417

### QR Codes

Nautilus has a separate `qr_code` field for customer-facing QR codes:

**Use Cases:**
- Link to product page on your website
- Product information for customers
- Inventory tracking
- Custom data storage

**QR Code vs Barcode:**
- `barcode`: For internal POS scanning
- `qr_code`: For customer website scanning
- Both can be used simultaneously

## Troubleshooting

### Scanner Not Working

**Problem:** Scanner doesn't type anything

**Solutions:**
1. Check USB connection
2. Test in text editor (Notepad, TextEdit)
3. Check scanner LED is on
4. Try different USB port
5. Check scanner configuration

**Problem:** Wrong characters appearing

**Solutions:**
1. Check keyboard layout (scanner uses same as computer)
2. Reconfigure scanner for correct character set
3. Some scanners need to be configured for US keyboard layout

### Search Not Finding Product

**Problem:** Barcode scanned but no product found

**Solutions:**
1. Verify barcode is in database:
   ```sql
   SELECT id, name, barcode FROM products WHERE barcode = 'YOUR_BARCODE';
   ```
2. Check for extra characters (spaces, dashes)
3. Check barcode field is not empty
4. Try typing barcode manually
5. Check product is active: `is_active = 1`

### Multiple Products with Same Barcode

**Problem:** Multiple products have identical barcodes

**Solutions:**
1. Find duplicates:
   ```sql
   SELECT barcode, COUNT(*) as count
   FROM products
   WHERE barcode IS NOT NULL AND barcode != ''
   GROUP BY barcode
   HAVING count > 1;
   ```
2. Assign unique barcodes to each product
3. Use SKU field for additional identification

### Scanner Adding Extra Characters

**Problem:** Scanner adds prefix/suffix you don't want

**Solutions:**
1. Scan configuration barcode to disable prefix/suffix
2. Check scanner manual for setup barcodes
3. Most scanners have "restore defaults" barcode
4. Configure suffix to "Enter" key only

## Best Practices

### Barcode Assignment

1. **Use Standard Formats:**
   - UPC for retail products (get from manufacturer)
   - EAN for international products
   - Code 128 for custom internal barcodes

2. **Unique Barcodes:**
   - Each product should have unique barcode
   - Don't reuse barcodes
   - Check for duplicates before assigning

3. **Document Barcodes:**
   - Keep list of assigned barcodes
   - Document barcode scheme
   - Track custom barcode ranges

### Scanner Placement

1. **POS Station:**
   - Place scanner within easy reach
   - Presentation scanner: On counter facing up
   - Handheld scanner: On right side of keyboard

2. **Receiving Area:**
   - Have scanner at receiving desk
   - For quick product check-in
   - Update inventory as items arrive

3. **Inventory Counts:**
   - Use wireless scanner for stockroom
   - Scan products during counts
   - Speeds up inventory process

### Data Entry

1. **Always Add Barcode:**
   - Add barcode when creating product
   - Scan from product packaging if available
   - Generate custom barcode if needed

2. **Verify Barcode:**
   - Test scan after entering
   - Ensure barcode is correct
   - Check no duplicates

3. **Backup SKU:**
   - Always fill SKU field too
   - Use as backup lookup method
   - Some products may not have barcodes

## Generating Custom Barcodes

If you need to generate barcodes for products without them:

### Online Tools

- **Barcode Generator**: https://barcode.tec-it.com/
- **Free Barcode Generator**: https://www.barcodesinc.com/generator/
- Select format (Code 128 recommended)
- Enter your product code
- Download and print

### Barcode Numbering Scheme

**Suggested Format for Custom Barcodes:**
```
Company Code (3 digits) + Category (2 digits) + Sequence (4 digits) + Check digit

Example: 100 01 0001 7
         └─┘ └┘ └──┘ └── Check digit
          │   │   └────── Sequential number
          │   └────────── Category code
          └────────────── Your company code
```

### Printing Barcodes

**Options:**
1. **Thermal Label Printer:**
   - Zebra, Dymo, Brother
   - Fast, professional labels
   - Recommended for high volume

2. **Standard Printer:**
   - Use label sheets (Avery, etc.)
   - Design in Word/Excel
   - Good for low volume

3. **Online Printing:**
   - Upload barcode list
   - Professional printing service
   - Order pre-printed labels

## Integration with Inventory

### Receiving Process

1. Receive shipment
2. Scan each product barcode
3. System updates stock quantity
4. Fast check-in process

### Inventory Counts

1. Walk through store with scanner
2. Scan each product
3. System records count
4. Compare to expected quantities

### Stock Adjustments

1. Scan product to find it
2. Adjust quantity
3. Add adjustment reason
4. System logs change

## API for Barcode Lookup

### Product Lookup Endpoint

```http
GET /api/products/barcode/{barcode}

Response:
{
    "id": 123,
    "name": "Product Name",
    "sku": "SKU123",
    "barcode": "012345678901",
    "price": 29.99,
    "stock_quantity": 15
}
```

### Search Endpoint

```http
GET /api/products/search?q={barcode}

Response:
{
    "results": [
        {
            "id": 123,
            "name": "Product Name",
            "barcode": "012345678901",
            ...
        }
    ]
}
```

## Database Schema

### Barcode Field

```sql
-- Products table
CREATE TABLE products (
    ...
    barcode VARCHAR(100),
    qr_code VARCHAR(255),
    ...
    INDEX idx_barcode (barcode)
);
```

### Searching Barcodes

```sql
-- Find product by barcode
SELECT * FROM products
WHERE barcode = '012345678901'
AND is_active = TRUE;

-- Search with fallback to SKU
SELECT * FROM products
WHERE (barcode = '012345678901' OR sku = '012345678901')
AND is_active = TRUE
LIMIT 1;
```

## Support

### Common Questions

**Q: Can I use the same barcode for multiple products?**
A: Not recommended. Each product should have a unique barcode for accurate inventory tracking.

**Q: What if product doesn't have a barcode?**
A: Generate a custom barcode using Code 128 format, or use SKU/product search instead.

**Q: Does scanner work on tablets/iPads?**
A: USB scanners work on tablets with USB port or USB adapter. Bluetooth scanners also available.

**Q: Can I scan barcodes with phone camera?**
A: Not currently supported in Nautilus web interface. Use dedicated barcode scanner.

**Q: Scanner works in other programs but not Nautilus?**
A: Scanner should work the same everywhere. Test by clicking in search field and scanning.

### Getting Help

If you have issues with barcode scanning:

1. Test scanner in simple text editor
2. Check product has barcode in database
3. Verify barcode format is supported
4. Check application logs for errors
5. Contact support with:
   - Scanner model
   - Barcode number
   - Product ID
   - Error message (if any)

---

## Quick Reference

**Add Barcode to Product:**
Products → Edit Product → Barcode field → Save

**Scan in POS:**
Click product search → Scan barcode → Product added

**Check for Duplicates:**
```sql
SELECT barcode, COUNT(*) as cnt
FROM products
WHERE barcode IS NOT NULL
GROUP BY barcode
HAVING cnt > 1;
```

**Scanner Not Working:**
1. Check USB connection
2. Test in text editor
3. Check barcode in database
4. Try typing manually

**Recommended Scanner:**
Any USB HID scanner that acts as keyboard (most do)

---

Last Updated: November 2, 2025
Version: 1.0
