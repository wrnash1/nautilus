# RMA & Product Import System - Complete Guide

## Overview

Nautilus now includes two powerful new systems:

1. **RMA (Return Merchandise Authorization)** - Complete returns management for customers and vendors
2. **CSV Product Import** - Bulk import products from vendor spreadsheets with field mapping

---

## 🔄 RMA System Features

### What's Included

**Customer Returns:**
- Return requests with reason tracking
- Automated RMA number generation (RMA-YYYYMMDD-XXXX)
- Refund, exchange, repair, or credit options
- Restocking fee calculations
- Return window enforcement (configurable days)

**Vendor Returns:**
- Send defective items back to vendors
- Vendor RMA tracking
- Credit amount tracking
- Link to original customer RMA

**Workflow Management:**
- Status tracking: Pending → Approved → Received → Refunded/Exchanged → Completed
- Manager approval requirements
- Item condition inspection
- Photo attachment support
- Automatic inventory restocking

**Item Disposition:**
- Restock (good condition items)
- Vendor Return (defective items)
- Scrap (damaged beyond repair)
- Repair (warranty items)

### Database Tables Created

```sql
rma_requests          -- Main RMA records
rma_items             -- Individual products being returned
rma_status_history    -- Audit trail of status changes
```

### RMA Configuration Settings

Location: **Settings → RMA** (after migration)

| Setting | Default | Description |
|---------|---------|-------------|
| Return Window Days | 30 | Days after purchase to allow returns |
| Restocking Fee % | 15 | Percentage charged for non-defective returns |
| Require Manager Approval | Yes | RMAs need manager approval |
| Auto-approve Defective | No | Automatically approve defective items |
| Email Notifications | Yes | Send status change emails |
| Return Shipping Paid By | Customer | Who pays return shipping |

---

## 📊 Product Import System Features

### What's Included

**File Support:**
- CSV files (comma, tab, semicolon delimited)
- Excel files (.xlsx, .xls) - via CSV conversion
- Configurable header row detection

**Smart Field Mapping:**
- Auto-detection of common column names
- Drag-and-drop field mapping interface
- Default values for unmapped fields
- Save mapping templates for future imports

**Import Modes:**
- **Create Only** - Skip existing products
- **Update Existing** - Update products by SKU/barcode match
- **Create & Update** - Both create new and update existing

**Advanced Features:**
- Preview first 10 rows before import
- Validation with error/warning messages
- Progress tracking (rows processed/success/failed)
- Auto-create categories and vendors
- Bulk operations (100s-1000s of products)
- Import history and logging
- Error log with row numbers

**Supported Product Fields:**
- SKU, Name, Description, Barcode
- Cost Price, Retail Price, Sale Price
- Weight, Weight Unit, Dimensions (L/W/H)
- Stock Quantity, Low Stock Threshold
- Category, Vendor, Vendor SKU
- Product Type, Tax Class
- Shipping info (HS Code, Country of Origin)

### Database Tables Created

```sql
product_import_jobs      -- Import job tracking
product_import_preview   -- Preview/staging data
vendor_price_lists       -- Vendor price list tracking
```

### Enhanced Product Fields

New shipping-related fields added to products:

```sql
-- Detailed dimensions
length, width, height, dimension_unit

-- Shipping info
package_weight, ships_separately, free_shipping
shipping_class, harmonized_code, country_of_origin
```

---

## 🚀 Quick Start - RMA System

### 1. Run Migration

```bash
cd /home/wrnash1/development/nautilus
mysql -u root -p nautilus < database/migrations/017_create_rma_and_import_systems.sql
```

### 2. Configure RMA Settings

1. Log in → **Settings → RMA**
2. Set return window days (e.g., 30)
3. Set restocking fee percentage (e.g., 15%)
4. Configure approval requirements
5. Save settings

### 3. Process a Customer Return

**Step 1: Customer requests return**
- Navigate to **RMA → Create New RMA**
- Select customer and original transaction
- Choose items to return
- Select reason (defective, wrong item, buyer remorse, etc.)
- Choose resolution (refund, exchange, repair)
- Submit request

**Step 2: Manager reviews**
- RMA status: **Pending**
- Manager views RMA details
- Approves or rejects with notes
- If approved, status → **Approved**

**Step 3: Customer ships item back**
- Customer receives return label/instructions
- Ships product back to store
- Enter tracking number in RMA

**Step 4: Receive and inspect**
- Product arrives at store
- Staff marks RMA as **Received**
- Inspect item condition:
  - Unopened / Opened unused → Restock
  - Defective / Damaged → Vendor return
  - Used (good/fair) → Manager decision
- Enter inspection notes and photos

**Step 5: Process resolution**
- **If Refund:** Issue refund (minus restocking fee if applicable)
- **If Exchange:** Create new order for replacement
- **If Vendor Return:** Create vendor RMA
- Good condition items auto-restock inventory
- Status → **Completed**

### 4. Vendor RMA (Defective Items)

If customer returned defective item:

1. **Create Vendor RMA** from customer RMA
2. Select items to send to vendor
3. Generate vendor RMA number
4. Ship to vendor with documentation
5. Track vendor credit/replacement
6. Link back to original customer RMA

---

## 🚀 Quick Start - Product Import

### 1. Prepare Your CSV File

**Example CSV Structure:**
```csv
SKU,Product Name,Category,Vendor,Cost,Price,Weight,Stock,Description
TANK-AL80,Aluminum 80cf Tank,Tanks,XS Scuba,125.00,299.99,32,15,Standard aluminum scuba tank
REG-APEX,Apeks XTX50 Regulator,Regulators,Apeks,450.00,799.99,5,8,Cold water regulator
MASK-PRO,Pro Dive Mask,Masks,Scubapro,35.00,89.99,1,25,Professional diving mask
```

**Tips:**
- First row should contain column headers
- Use consistent formatting (all prices as numbers)
- Include SKU for every product (required)
- Weight should be numeric (e.g., 32 not "32 lbs")
- Empty cells are okay for optional fields

### 2. Upload and Map Fields

**Step 1: Upload file**
- Navigate to **Products → Import Products**
- Click **Choose File**
- Select your CSV/Excel file
- Click **Upload**

**Step 2: Auto-detect mapping**
- System analyzes column headers
- Auto-maps common fields:
  - "SKU" → sku
  - "Product Name" → name
  - "Price" → retail_price
  - "Cost" → cost_price
  - etc.

**Step 3: Review/adjust mapping**
- Drag CSV columns to product fields
- Set default values for unmapped fields
  - Default category: "Imported Products"
  - Default tax class: "standard"
  - Track inventory: Yes
  - Is active: Yes

**Step 4: Configure import options**
- **Update existing products:** Yes/No
- **Match by:** SKU (or Barcode)
- **Skip duplicates:** Yes/No
- **Auto-create categories:** Yes (creates categories if they don't exist)
- **Auto-create vendors:** Yes
- Click **Save Mapping**

### 3. Preview and Validate

- System shows first 10 rows
- Preview displays:
  - ✅ Valid rows (will import)
  - ⚠️ Warnings (will import with defaults)
  - ❌ Errors (won't import - fix required)

**Common Validation Errors:**
- Missing SKU → Add SKU to CSV
- Missing product name → Add name
- Invalid price (0 or negative) → Fix price
- Duplicate SKU (if skip duplicates enabled) → Will skip or update

**Fix errors:**
- Edit CSV file
- Re-upload and map again
- OR skip error rows

### 4. Execute Import

- Review preview summary:
  - 200 valid rows
  - 50 will update existing
  - 150 will create new
  - 5 have warnings
  - 2 have errors (will skip)

- Click **Start Import**
- Progress bar shows:
  - Rows processed: 250/252
  - Success: 200
  - Updated: 50
  - Failed: 2

### 5. Review Results

**Import Summary:**
```
Total Rows: 252
✅ Successfully Created: 150 products
🔄 Successfully Updated: 50 products
⚠️ Skipped: 45 duplicates
❌ Failed: 7 (see error log)

Errors:
- Row 45: Missing SKU
- Row 102: Invalid price (must be > 0)
- Row 203: Category 'Accessories' not found (auto-create disabled)
...
```

**View created products:**
- Lists all imported product IDs
- Links to view each product
- Export import results to CSV

---

## 💡 CSV Import Use Cases

### Use Case 1: Initial Product Catalog

**Scenario:** New store, importing 500 products from vendor

**Steps:**
1. Request product catalog CSV from vendor
2. Upload to Nautilus
3. Map vendor columns (they might use "Item#" instead of "SKU")
4. Set defaults:
   - Vendor: XS Scuba
   - Tax class: Standard
   - Track inventory: Yes
5. Enable auto-create categories
6. Import all 500 products at once

### Use Case 2: Price Updates

**Scenario:** Vendor sends quarterly price list with 1000 items

**Steps:**
1. Upload new price list CSV
2. Map columns (SKU, New Cost, New MSRP)
3. Enable **Update Existing Products**
4. Match by: SKU
5. Only map SKU, cost_price, retail_price
6. Import updates prices for all 1000 existing products

### Use Case 3: New Product Line

**Scenario:** Adding new brand (200 products)

**Steps:**
1. Get vendor spreadsheet
2. Upload CSV
3. Auto-detect mapping
4. Set default vendor: "New Brand Inc"
5. Auto-create categories: Yes
6. Import creates 200 new products + categories

---

## 📋 RMA Workflow Examples

### Example 1: Defective Regulator Return

**Day 1 - Customer Request:**
```
RMA #: RMA-20251019-0001
Customer: John Smith
Item: Apeks XTX50 Regulator (purchased 15 days ago)
Reason: Defective - second stage free flows
Resolution: Exchange
Status: Pending
```

**Day 2 - Manager Approval:**
```
Manager: Sarah Johnson
Action: Approved (within return window, valid defect)
Notes: "Free flow is a safety issue, approve immediately"
Status: Approved
Email sent to customer with return instructions
```

**Day 5 - Item Received:**
```
Received by: Mike (tech)
Condition: Defective - confirmed free flow
Disposition: Vendor Return (send back to Apeks)
Photos: [uploaded 3 inspection photos]
Status: Received
```

**Day 5 - Exchange Processed:**
```
Action: Create exchange order for new XTX50
Refund: $0 (exchange, no restocking fee)
Stock: Created vendor RMA to Apeks
Status: Exchanged
```

**Day 20 - Vendor Credit Received:**
```
Vendor RMA: VND-RMA-20251019-0001
Apeks Credit: $450 (wholesale cost)
Original RMA: Closed
Status: Completed
```

### Example 2: Buyer Remorse Return

**Scenario:** Customer bought expensive drysuit, changed mind

```
RMA #: RMA-20251019-0002
Customer: Jane Doe
Item: DUI TLS350 Drysuit - $1,899
Purchase Date: 25 days ago (within 30-day window)
Reason: Buyer remorse
Resolution: Refund
Condition Received: Unopened (still in box with tags)
Restocking Fee: 15% = $284.85
Refund Amount: $1,614.15
Disposition: Restock (mint condition)
Status: Completed

Timeline:
- Day 1: Request submitted
- Day 2: Manager approved (within window, unopened)
- Day 6: Received and inspected
- Day 6: Refund processed ($1,614.15)
- Day 6: Item restocked to inventory
```

---

## 🔧 Advanced Features

### Batch RMA Processing

Process multiple RMAs at once:
- Filter: Status = Received
- Bulk action: "Restock all good condition items"
- Bulk action: "Create vendor RMAs for defective items"

### RMA Reports

Available reports:
- **RMA Summary:** Count by status, reason, resolution
- **Return Rate:** % of sales resulting in returns
- **Top Return Reasons:** Identify product quality issues
- **Vendor Defect Rate:** Which vendors have most defects
- **Restocking Fee Revenue:** Track fees collected
- **Average Processing Time:** Days from request to completion

### Import Templates

Save mapping for repeated imports:

```
Template: XS Scuba Catalog
Mapping:
  Item Number → sku
  Description → name
  List Price → retail_price
  Dealer Price → cost_price
  Weight (lbs) → weight
  Category Code → category

Defaults:
  vendor_id: 5 (XS Scuba)
  weight_unit: lb
  track_inventory: true
```

Reuse template next time:
- Upload new XS Scuba CSV
- Load "XS Scuba Catalog" template
- Mapping pre-filled
- Click Import

### Incremental Updates

Update only specific fields:

**Scenario:** Vendor sends stock availability update

```csv
SKU,Available Stock
TANK-AL80,45
REG-APEX,12
MASK-PRO,0
```

**Import settings:**
- Update existing: Yes
- Match by: SKU
- Only map: SKU → sku, Available Stock → stock_quantity
- Don't map: name, price, description, etc.
- Result: Only stock quantities updated, everything else unchanged

---

## 🔐 Security & Permissions

### RMA Permissions

```
rma.view           -- View RMA requests
rma.create         -- Create new RMAs
rma.approve        -- Approve/reject RMAs (managers only)
rma.process        -- Process refunds/exchanges
rma.vendor_rma     -- Create vendor RMAs
```

### Import Permissions

```
products.import    -- Upload and import products
products.export    -- Export product data
products.bulk_edit -- Bulk update products
```

---

## 📊 API Endpoints

### RMA API

```
GET    /api/rma                 -- List RMAs
POST   /api/rma                 -- Create RMA
GET    /api/rma/{id}            -- Get RMA details
PUT    /api/rma/{id}/approve    -- Approve RMA
PUT    /api/rma/{id}/reject     -- Reject RMA
PUT    /api/rma/{id}/receive    -- Mark as received
POST   /api/rma/{id}/refund     -- Process refund
GET    /api/rma/statistics      -- Get RMA stats
```

### Import API

```
POST   /api/products/import/upload    -- Upload CSV file
POST   /api/products/import/map       -- Save field mapping
GET    /api/products/import/{id}/preview -- Preview import
POST   /api/products/import/{id}/execute -- Execute import
GET    /api/products/import/{id}/status  -- Get import status
```

---

## 📁 File Structure

```
database/migrations/
  017_create_rma_and_import_systems.sql  -- Database migration

app/Services/
  RMA/
    RMAService.php                       -- RMA business logic
  Import/
    ProductImportService.php             -- Import business logic

app/Controllers/
  RMA/
    RMAController.php                    -- RMA web interface
  Inventory/
    ProductImportController.php          -- Import web interface

app/Views/
  rma/
    index.php                            -- RMA list
    create.php                           -- Create RMA form
    show.php                             -- RMA details
    approve.php                          -- Approval interface
  products/
    import/
      upload.php                         -- File upload
      mapping.php                        -- Field mapping
      preview.php                        -- Preview & validate
      results.php                        -- Import results
```

---

## 🐛 Troubleshooting

### Import Issues

**Problem:** CSV won't upload

**Solutions:**
- Check file size (max 50MB)
- Verify file format (CSV, not Excel with formulas)
- Try exporting CSV from Excel as "CSV UTF-8"
- Check server PHP upload limits

**Problem:** Mapping doesn't save

**Solutions:**
- Ensure all required fields are mapped (SKU, Name, Price)
- Check for JavaScript errors in browser console
- Clear browser cache

**Problem:** All rows show errors

**Solutions:**
- Verify CSV has headers in first row
- Check data format (prices as numbers, not "$100.00")
- Ensure SKUs are unique
- Check required fields aren't empty

### RMA Issues

**Problem:** Can't create RMA (return window expired)

**Solution:**
- Manager can override in RMA settings
- Or extend return window days setting

**Problem:** Refund amount incorrect

**Solution:**
- Check restocking fee settings
- Verify item prices match original purchase
- Check for partial refunds

---

## 📝 Best Practices

### CSV Imports

1. **Test with small batch first** (10-20 rows)
2. **Backup database before large import**
3. **Use consistent data format**
4. **Include all required fields** (SKU, Name, Price)
5. **Validate data in Excel first** (no blank SKUs, valid prices)
6. **Save mapping templates** for repeated imports
7. **Review preview carefully** before executing

### RMA Management

1. **Photograph damaged items** (evidence for vendor claims)
2. **Inspect immediately upon receipt** (don't let items sit)
3. **Restock quickly** (return to inventory ASAP)
4. **Track vendor defect patterns** (identify quality issues)
5. **Communicate clearly with customers** (status emails)
6. **Train staff on RMA process** (consistent handling)
7. **Review RMA reports monthly** (identify trends)

---

## 🚀 Next Steps

1. **Run Migration 017**
2. **Configure RMA Settings**
3. **Test CSV Import** with sample file
4. **Train Staff** on both systems
5. **Set Up RMA Workflow** (who approves, who processes)
6. **Create Import Templates** for regular vendors

---

## Version Information

- **Added:** 2025-10-19
- **Nautilus Version:** 6.0
- **Migration:** 017_create_rma_and_import_systems.sql
- **Dependencies:** Migrations 001-016

---

**End of Guide**
