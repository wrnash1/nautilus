# DiveShop360 to Nautilus Field Mapping

This document provides a comprehensive mapping of fields from DiveShop360 to the Nautilus dive shop management system.

## Customer Data Mapping

| DiveShop360 Field | Nautilus Field | Table | Notes |
|------------------|----------------|-------|-------|
| Customer ID | id | customers | Auto-increment in Nautilus |
| First Name | first_name | customers | |
| Last Name | last_name | customers | |
| Email | email | customers | Unique in Nautilus |
| Phone | phone | customers | |
| Mobile | phone | customers | Use primary contact number |
| Address Line 1 | address_line1 | customers | |
| Address Line 2 | address_line2 | customers | |
| City | city | customers | |
| State/Province | state | customers | |
| Zip/Postal Code | postal_code | customers | |
| Country | country | customers | Default: 'US' |
| Date of Birth | date_of_birth | customers | Format: YYYY-MM-DD |
| Emergency Contact Name | emergency_contact_name | customers | |
| Emergency Contact Phone | emergency_contact_phone | customers | |
| Photo | photo_path | customers | Store file path |
| Notes | notes | customers | |
| Customer Since | created_at | customers | |

## Certification Data Mapping

| DiveShop360 Field | Nautilus Field | Table | Notes |
|------------------|----------------|-------|-------|
| Certification ID | id | customer_certifications | Auto-increment |
| Customer ID | customer_id | customer_certifications | FK to customers |
| Certification Type | certification_type_id | customer_certifications | Map to certification_types |
| Certification Number | certification_number | customer_certifications | |
| Issue Date | issue_date | customer_certifications | Format: YYYY-MM-DD |
| Certifying Agency | agency_id | customer_certifications | Map to certification_agencies |
| Instructor Name | instructor_name | customer_certifications | |

### Certification Agencies Mapping

Map DiveShop360 agencies to Nautilus:
- PADI → agency_id for PADI
- SSI → agency_id for SSI
- NAUI → agency_id for NAUI
- SDI → agency_id for SDI
- TDI → agency_id for TDI

## Product/Inventory Data Mapping

| DiveShop360 Field | Nautilus Field | Table | Notes |
|------------------|----------------|-------|-------|
| Product ID | id | products | Auto-increment |
| SKU | sku | products | Must be unique |
| Product Name | name | products | |
| Description | description | products | |
| Category | category_id | products | Map to product_categories |
| Manufacturer | manufacturer | products | New field in migration 035 |
| Vendor/Supplier | vendor_id | products | FK to vendors table |
| Cost Price | cost_price | products | Decimal(10,2) |
| Retail Price | retail_price | products | Decimal(10,2) |
| Sale Price | sale_price | products | Optional |
| Stock Quantity | stock_quantity | products | Integer |
| Low Stock Alert | low_stock_threshold | products | Default: 5 |
| Barcode | barcode | products | |
| QR Code | qr_code | products | New field in migration 035 |
| Weight | weight | products | Decimal(8,2) |
| Weight Unit | weight_unit | products | ENUM: lb, kg, oz, g |
| Dimensions | dimensions | products | New field in migration 035 |
| Color | color | products | New field in migration 035 |
| Material | material | products | New field in migration 035 |
| Warranty | warranty_info | products | New field in migration 035 |
| Location in Store | location_in_store | products | New field in migration 035 |
| Supplier Info | supplier_info | products | New field in migration 035 |
| Expiration Date | expiration_date | products | New field in migration 035 |
| Product Image | - | product_images | Store as separate records |
| Active/Inactive | is_active | products | Boolean |

## Sales/Transaction Data Mapping

| DiveShop360 Field | Nautilus Field | Table | Notes |
|------------------|----------------|-------|-------|
| Transaction ID | id | transactions | Auto-increment |
| Customer ID | customer_id | transactions | FK to customers |
| Transaction Date | transaction_date | transactions | Format: YYYY-MM-DD HH:MM:SS |
| Total Amount | total | transactions | Decimal(10,2) |
| Subtotal | subtotal | transactions | |
| Tax Amount | tax | transactions | |
| Discount | discount_amount | transactions | |
| Payment Method | payment_method | transactions | ENUM: cash, credit, debit, check |
| Transaction Type | transaction_type | transactions | ENUM: sale, rental, service, etc. |
| Status | status | transactions | ENUM: pending, completed, cancelled, refunded |
| Notes | notes | transactions | |

### Transaction Items Mapping

| DiveShop360 Field | Nautilus Field | Table | Notes |
|------------------|----------------|-------|-------|
| Line Item ID | id | transaction_items | Auto-increment |
| Transaction ID | transaction_id | transaction_items | FK to transactions |
| Product ID | product_id | transaction_items | FK to products |
| Product Name | product_name | transaction_items | Snapshot at time of sale |
| Quantity | quantity | transaction_items | Integer |
| Unit Price | unit_price | transaction_items | Price at time of sale |
| Discount | discount | transaction_items | Line item discount |
| Total | total_price | transaction_items | Calculated |

## Rental Equipment Mapping

| DiveShop360 Field | Nautilus Field | Table | Notes |
|------------------|----------------|-------|-------|
| Equipment ID | id | rental_equipment | Auto-increment |
| Equipment Type | equipment_type_id | rental_equipment | FK to rental_equipment_types |
| Serial Number | serial_number | rental_equipment | |
| Size | size | rental_equipment | |
| Condition | condition | rental_equipment | ENUM: excellent, good, fair, poor |
| Status | status | rental_equipment | ENUM: available, rented, maintenance, retired |
| Purchase Date | purchase_date | rental_equipment | |
| Last Service Date | last_service_date | rental_equipment | |
| Daily Rate | daily_rate | rental_equipment | |
| Notes | notes | rental_equipment | |

## Vendor/Supplier Data Mapping

| DiveShop360 Field | Nautilus Field | Table | Notes |
|------------------|----------------|-------|-------|
| Vendor ID | id | vendors | Auto-increment |
| Vendor Name | name | vendors | |
| Contact Name | contact_name | vendors | |
| Email | email | vendors | |
| Phone | phone | vendors | |
| Website | website | vendors | |
| Address Line 1 | address_line1 | vendors | |
| Address Line 2 | address_line2 | vendors | |
| City | city | vendors | |
| State | state | vendors | |
| Postal Code | postal_code | vendors | |
| Country | country | vendors | Default: 'US' |
| Payment Terms | payment_terms | vendors | |
| Notes | notes | vendors | |
| Active/Inactive | is_active | vendors | Boolean |

## Data Migration Process

### Step 1: Export from DiveShop360
1. Export customer data to CSV
2. Export product/inventory data to CSV
3. Export transaction history to CSV
4. Export certification data to CSV
5. Export vendor data to CSV

### Step 2: Prepare Nautilus Database
1. Run all migrations: `php scripts/migrate.php`
2. Ensure migration 035 is applied (new product fields)
3. Verify all tables are created

### Step 3: Import Data
1. **Import Vendors First** (products depend on vendors)
2. **Import Product Categories**
3. **Import Certification Agencies** (certifications depend on agencies)
4. **Import Products** (transactions depend on products)
5. **Import Customers** (transactions depend on customers)
6. **Import Customer Certifications**
7. **Import Transactions and Transaction Items**
8. **Import Rental Equipment**

### Step 4: Data Validation
1. Verify customer count matches
2. Verify product count matches
3. Check for orphaned records
4. Validate foreign key relationships
5. Test sample transactions

## Important Notes

### Data Type Conversions
- **Dates**: Convert to MySQL DATE format (YYYY-MM-DD)
- **DateTimes**: Convert to MySQL DATETIME format (YYYY-MM-DD HH:MM:SS)
- **Prices**: Store as DECIMAL(10,2)
- **Phone Numbers**: Store with country code if available
- **Booleans**: Use 1 for TRUE, 0 for FALSE

### Special Considerations
1. **SKU Uniqueness**: Ensure all SKUs are unique before import
2. **Email Uniqueness**: Customer emails must be unique
3. **File Paths**: Update photo/image paths to match Nautilus directory structure
4. **Category Mapping**: Create product categories in Nautilus before importing products
5. **Certification Agency Mapping**: Verify all agencies exist in Nautilus before importing certifications

### SQL Import Template

```sql
-- Example: Import Customers
INSERT INTO customers (
    first_name, last_name, email, phone,
    address_line1, city, state, postal_code, country,
    date_of_birth, emergency_contact_name, emergency_contact_phone,
    notes, created_at
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);

-- Example: Import Products
INSERT INTO products (
    category_id, vendor_id, sku, name, description,
    cost_price, retail_price, stock_quantity, low_stock_threshold,
    barcode, qr_code, weight, dimensions, color, material,
    manufacturer, warranty_info, location_in_store,
    supplier_info, expiration_date, is_active
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
```

## Contact & Support

For assistance with data migration, contact the Nautilus development team.
