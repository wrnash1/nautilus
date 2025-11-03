# Nautilus - Action Plan to Production Ready
**Date**: November 2, 2025
**Goal**: Get application production-ready as fast as possible

## Critical Fixes Completed ✅

1. **POS getSettingValue() Error** - FIXED
   - Added helper function to `app/helpers.php`
   - POS should now load without errors

2. **Installation Process** - VERIFIED WORKING
   - InstallService properly creates database and runs migrations
   - Can safely drop database and reinstall

## Immediate Actions Required (Priority 1 - Today)

### 1. Add Settings Menu to Sidebar
**File to Modify**: `app/Views/layouts/app.php` or navigation include file
**Add**:
```html
<li class="nav-item">
    <a class="nav-link" href="/store/admin/settings">
        <i class="bi bi-gear"></i> Settings
    </a>
</li>
```

### 2. Fix Customer Edit URL Documentation
**Issue**: URLs need `/store` prefix
**Solution**: Document correct URLs and optionally add convenience redirects

### 3. Add Customer Certification Functionality
**Status**: Need to investigate if routes/methods exist
**Files to Check**:
- CustomerController - add certification methods
- Add routes for certification management
- Customer view needs certification form

### 4. Test and Fix Non-Working Menu Items
**Methodology**:
- Systematically test each menu link
- Document which are broken
- Fix one by one

## Phase 1 - Core Functionality (Week 1)

### Day 1-2: Fix Existing Features
- [ ] Add Settings to sidebar menu
- [ ] Fix customer certification addition
- [ ] Test all menu items and document status
- [ ] Fix online store menu item
- [ ] Create comprehensive menu status report

### Day 3-4: Customer Enhancements (High Priority)
- [ ] Migration: Add travel information fields to customers table
  - passport_number, passport_expiration, passport_country
  - weight, height, height_unit, weight_unit
  - allergies, medications, medical_notes
- [ ] Migration: Create customer_addresses table (multiple addresses)
- [ ] Migration: Create customer_phones table (multiple phones)
- [ ] Migration: Create customer_emails table (multiple emails)
- [ ] Migration: Create customer_contacts table (emergency contacts)
- [ ] Migration: Create customer_tags table and customer_tag_assignments
- [ ] Update CustomerController with new methods
- [ ] Update customer views (create, edit, show)

### Day 5: POS Customer Notifications
- [ ] Add notification widget to POS when customer selected
- [ ] Show upcoming: courses, trips, rentals, work orders
- [ ] Show customer notes
- [ ] Show pending items

## Phase 2 - Operations Features (Week 2)

### Day 6-7: Cash Drawer Management
- [ ] Migration: Create cash_drawer tables
  - cash_drawers (id, name, location, current_balance)
  - cash_drawer_sessions (open, close, counting)
  - cash_drawer_transactions (deposits, withdrawals, sales)
- [ ] Create CashDrawerController
- [ ] Create cash drawer management views
- [ ] Add to POS workflow (open drawer at start of shift)

### Day 8-9: Configuration Pages
- [ ] Settings page for Google integration
- [ ] Settings page for Wave apps
- [ ] Settings page for Payment gateways
- [ ] Settings page for Email services
- [ ] Settings page for SMS (Twilio, etc.)
- [ ] Settings page for Shipping
- [ ] Settings page for Tax rules

### Day 10: Employee Permissions & Scheduling
- [ ] Review existing permissions system
- [ ] Add granular permissions if needed
- [ ] Create employee schedule tables
- [ ] Create ScheduleController
- [ ] Create schedule views

## Phase 3 - Inventory & Purchasing (Week 3)

### Day 11-13: Enhanced Inventory Management
- [ ] Add reorder points to products
- [ ] Add auto-reorder functionality
- [ ] Create low stock alerts
- [ ] Create inventory reports dashboard

### Day 14-15: Purchase Orders
- [ ] Migration: Create purchase_orders tables
  - purchase_orders
  - purchase_order_items
  - purchase_order_history
- [ ] Create PurchaseOrderController
- [ ] Create PO views (create, list, receive)
- [ ] Link to vendors
- [ ] Email PO to vendors

## Phase 4 - Customer Features (Week 4)

### Day 16-17: Customer Management Enhancements
- [ ] Customer linking (families, businesses)
- [ ] Customer merge functionality
- [ ] Customer import (CSV, Excel)
- [ ] Customer export (CSV, Excel)
- [ ] Custom fields system

### Day 18-19: Customer Portal
- [ ] Customer login/registration
- [ ] View certifications
- [ ] View upcoming courses/trips
- [ ] View rental history
- [ ] Upload documents
- [ ] Online waiver signing

### Day 20: Rental Equipment Assignment
- [ ] Add rental equipment to POS checkout
- [ ] Rental equipment calendar
- [ ] Availability checking
- [ ] Rental reminders (due back)

## Quick Wins (Can Do Anytime)

### Settings Menu Addition (15 minutes)
1. Find layout file: `app/Views/layouts/app.php`
2. Add Settings link to navigation
3. Test access

### Fix Customer Edit URL (5 minutes)
1. Document correct URL in user guide
2. Or add redirect from `/customers/*` to `/store/customers/*`

### Online Store Fix (30 minutes)
1. Check if ShopController exists
2. Check if routes are registered
3. Test `/shop` endpoint
4. Fix any errors

## Database Migrations Needed

Create these migration files in order:

### Migration 039: Customer Travel & Contact Information
```sql
-- Add travel fields to customers table
ALTER TABLE customers ADD COLUMN passport_number VARCHAR(50);
ALTER TABLE customers ADD COLUMN passport_expiration DATE;
ALTER TABLE customers ADD COLUMN passport_country VARCHAR(3);
ALTER TABLE customers ADD COLUMN weight DECIMAL(5,2);
ALTER TABLE customers ADD COLUMN weight_unit ENUM('lb', 'kg') DEFAULT 'lb';
ALTER TABLE customers ADD COLUMN height DECIMAL(5,2);
ALTER TABLE customers ADD COLUMN height_unit ENUM('in', 'cm') DEFAULT 'in';
ALTER TABLE customers ADD COLUMN allergies TEXT;
ALTER TABLE customers ADD COLUMN medications TEXT;
ALTER TABLE customers ADD COLUMN medical_notes TEXT;

-- Create multiple addresses table
CREATE TABLE customer_addresses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    address_type ENUM('billing', 'shipping', 'home', 'work') NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    address_line1 VARCHAR(255),
    address_line2 VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(50),
    postal_code VARCHAR(20),
    country VARCHAR(3) DEFAULT 'US',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id),
    INDEX idx_type (address_type)
);

-- Create multiple phones table
CREATE TABLE customer_phones (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    phone_type ENUM('home', 'work', 'mobile', 'fax') NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id)
);

-- Create multiple emails table
CREATE TABLE customer_emails (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    email_type ENUM('personal', 'work', 'other') NOT NULL,
    email VARCHAR(255) NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id)
);

-- Create customer contacts table
CREATE TABLE customer_contacts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    contact_type ENUM('spouse', 'emergency', 'assistant', 'other') NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(255),
    relationship VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer (customer_id)
);
```

### Migration 040: Customer Tags and Linking
```sql
-- Customer tags
CREATE TABLE customer_tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    color VARCHAR(7) DEFAULT '#3498db',
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE customer_tag_assignments (
    customer_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    assigned_by INT UNSIGNED,
    PRIMARY KEY (customer_id, tag_id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES customer_tags(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Customer linking
CREATE TABLE customer_relationships (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id_1 INT UNSIGNED NOT NULL,
    customer_id_2 INT UNSIGNED NOT NULL,
    relationship_type ENUM('family', 'business', 'friend', 'other') NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED,
    FOREIGN KEY (customer_id_1) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id_2) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_customer_1 (customer_id_1),
    INDEX idx_customer_2 (customer_id_2)
);

-- Insert default tags
INSERT INTO customer_tags (name, color, description) VALUES
('VIP', '#f39c12', 'VIP Customer'),
('Wholesale', '#3498db', 'Wholesale Customer'),
('Instructor', '#2ecc71', 'Diving Instructor'),
('Regular', '#95a5a6', 'Regular Customer'),
('New', '#e74c3c', 'New Customer');
```

### Migration 041: Cash Drawer Management
```sql
CREATE TABLE cash_drawers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    location VARCHAR(100),
    current_balance DECIMAL(10,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE cash_drawer_sessions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    drawer_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    opened_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    closed_at TIMESTAMP NULL,
    starting_balance DECIMAL(10,2) NOT NULL,
    ending_balance DECIMAL(10,2) NULL,
    expected_balance DECIMAL(10,2) NULL,
    difference DECIMAL(10,2) NULL,
    notes TEXT,
    status ENUM('open', 'closed') DEFAULT 'open',
    FOREIGN KEY (drawer_id) REFERENCES cash_drawers(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_drawer (drawer_id),
    INDEX idx_status (status),
    INDEX idx_opened (opened_at)
);

CREATE TABLE cash_drawer_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id INT UNSIGNED NOT NULL,
    transaction_type ENUM('sale', 'return', 'deposit', 'withdrawal', 'adjustment') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'check', 'other') DEFAULT 'cash',
    reference_type ENUM('sale', 'expense', 'other') NULL,
    reference_id INT UNSIGNED NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED,
    FOREIGN KEY (session_id) REFERENCES cash_drawer_sessions(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_session (session_id),
    INDEX idx_type (transaction_type),
    INDEX idx_created (created_at)
);

-- Insert default drawer
INSERT INTO cash_drawers (name, location, current_balance)
VALUES ('Main Register', 'Front Counter', 200.00);
```

### Migration 042: Purchase Orders
```sql
CREATE TABLE purchase_orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    po_number VARCHAR(50) NOT NULL UNIQUE,
    vendor_id INT UNSIGNED NOT NULL,
    status ENUM('draft', 'sent', 'acknowledged', 'partial', 'received', 'cancelled') DEFAULT 'draft',
    order_date DATE NOT NULL,
    expected_date DATE,
    received_date DATE NULL,
    subtotal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    shipping_amount DECIMAL(10,2) DEFAULT 0.00,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    notes TEXT,
    internal_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by INT UNSIGNED NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (vendor_id) REFERENCES vendors(id) ON DELETE RESTRICT,
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_vendor (vendor_id),
    INDEX idx_status (status),
    INDEX idx_order_date (order_date)
);

CREATE TABLE purchase_order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    po_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    product_name VARCHAR(255) NOT NULL,
    sku VARCHAR(100),
    quantity_ordered INT NOT NULL,
    quantity_received INT DEFAULT 0,
    unit_cost DECIMAL(10,2) NOT NULL,
    total_cost DECIMAL(10,2) NOT NULL,
    notes TEXT,
    FOREIGN KEY (po_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_po (po_id),
    INDEX idx_product (product_id)
);

CREATE TABLE purchase_order_receipts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    po_id INT UNSIGNED NOT NULL,
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    received_by INT UNSIGNED NOT NULL,
    notes TEXT,
    FOREIGN KEY (po_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id),
    INDEX idx_po (po_id)
);
```

## Testing Protocol

### After Each Migration
1. Backup database
2. Run migration: `php8.2 scripts/migrate.php`
3. Check migration table for success
4. Test new functionality
5. Check logs for errors

### Before Production
- [ ] Full database backup
- [ ] Test installation from scratch
- [ ] Test all menu items
- [ ] Test POS transactions
- [ ] Test customer operations
- [ ] Test inventory operations
- [ ] Load test with multiple users

## Deployment Checklist

### Pre-Deployment
- [ ] All migrations tested
- [ ] All new features tested
- [ ] Documentation updated
- [ ] User guide created
- [ ] Training materials prepared

### Deployment
- [ ] Backup production database
- [ ] Deploy code
- [ ] Run migrations
- [ ] Test critical functions
- [ ] Monitor logs

### Post-Deployment
- [ ] Train staff
- [ ] Monitor for issues
- [ ] Collect feedback
- [ ] Plan next iteration

## Success Criteria

### Week 1
- POS working without errors ✅
- Settings accessible from menu
- Customer CRUD operations working
- All menu items functional or documented as not ready

### Week 2
- Travel information in customer records
- Multiple addresses/phones/emails working
- Cash drawer management operational
- Configuration pages created

### Week 3
- Purchase order system operational
- Enhanced inventory management working
- Vendor management complete

### Week 4
- Customer portal functional
- All features from error.txt addressed
- Application production-ready
- Staff trained

## Priority Matrix

### Critical (Must Have Before Production)
- POS functionality
- Customer management
- Product/Inventory management
- Settings accessibility
- Cash drawer management

### High Priority (Should Have)
- Travel information
- Multiple contact methods
- Customer tags
- Purchase orders
- Rental equipment assignment

### Medium Priority (Nice to Have)
- Customer portal
- Customer linking
- Import/Export
- Advanced reporting

### Low Priority (Future Enhancement)
- Customer merge
- Advanced scheduling
- Mobile app integration

## Resources Needed

### Development
- Access to test environment (pangolin.local) ✅
- Database access ✅
- Code editor ✅
- Git repository ✅

### Testing
- Test data
- User accounts with different roles
- Sample products, customers, transactions

### Documentation
- User guides for each feature
- Video tutorials
- FAQ document
- Troubleshooting guide

## Timeline Summary

- **Week 1**: Core fixes and customer enhancements
- **Week 2**: Operations features and configuration
- **Week 3**: Inventory and purchasing
- **Week 4**: Customer portal and final testing

**Target Production Date**: December 1, 2025 (4 weeks from now)

## Daily Standups

Track progress daily:
- What was completed yesterday?
- What will be completed today?
- Any blockers?

## Risk Management

### Risks
1. Database migration failures
2. Integration issues with existing data
3. Performance issues with new features
4. User adoption challenges

### Mitigation
1. Always backup before migrations
2. Test with production data copy
3. Performance testing before deployment
4. Comprehensive training program

## Next Immediate Action

**Right Now**: Test the POS fix
1. Access https://pangolin.local/store/pos
2. Verify no `getSettingValue()` error
3. Try to make a test transaction
4. Report results

Then move to next priority item based on what's blocking production most.
