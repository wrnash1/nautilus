# Remaining Features Implementation Plan

## Status Summary

### ✅ COMPLETED (from error.txt)

1. **Point of Sale**
   - ✅ Date and clock displaying (updates every second)
   - ✅ Store logo displayed
   - ✅ Customer photo displayed
   - ✅ Certification agency logos displayed
   - ✅ Customer search functional
   - ✅ New customer button links to customer creation

2. **Customer Module**
   - ✅ Customer photo upload in create form
   - ✅ Customer photo upload in edit form
   - ✅ Photo display on customer pages

3. **Products Module**
   - ✅ QR code field added
   - ✅ Weight field added (with unit selection)
   - ✅ Dimensions field added
   - ✅ Color field added
   - ✅ Material field added
   - ✅ Manufacturer field added
   - ✅ Warranty information field added
   - ✅ Stock quantity field (already existed)
   - ✅ Location in store field added
   - ✅ Supplier information field added
   - ✅ Expiration date field added
   - ✅ "Add Product" button fixed (routing corrected)

4. **System Features**
   - ✅ Favicon support added
   - ✅ Settings button role-based access (admin only)

5. **Installation & Documentation**
   - ✅ Automated installation script created (install.sh)
   - ✅ Fedora Server 43 deployment guide created
   - ✅ DiveShop360 field mapping document created

6. **Course Workflows (NEW!)**
   - ✅ Automated enrollment workflow
   - ✅ Student requirement tracking
   - ✅ Instructor notifications
   - ✅ Email automation
   - ✅ Roster management with progress tracking

###  🟡 NEEDS CLARIFICATION

1. **POS Customer Search Readability**
   - **Current State**: Search dropdown has white background, clear text, good hover states
   - **Question**: What specific "black" issue are you referring to?
   - **Possible Issues**:
     - Badges (B2B/B2C) too dark?
     - Text contrast not high enough?
     - Dark mode interfering?
   - **Action**: Please provide screenshot or more specific description

2. **Barcode Scanning in POS**
   - **Current State**: Barcode field exists in products
   - **Question**: How should barcode scanning work in POS?
   - **Possible Implementations**:
     - USB barcode scanner input?
     - Camera-based scanning?
     - Manual barcode entry?
   - **Action**: Need requirements clarification

### 🔴 TO BE IMPLEMENTED

#### 1. Layaway Functionality

**Description**: Add layaway to Point of Sale

**Requirements:**
- Store items in layaway status
- Track payments over time
- Set payment schedules
- Hold inventory for layaway items
- Release when fully paid

**Implementation Plan:**
1. Create `layaway` table (migration 037)
2. Create `layaway_payments` table
3. Add "Layaway" button to POS
4. Create layaway management interface
5. Add layaway reports

**Estimated Effort**: 4-6 hours

**Database Schema:**
```sql
CREATE TABLE layaway (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    customer_id INT UNSIGNED NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) DEFAULT 0,
    balance_due DECIMAL(10,2) NOT NULL,
    deposit_amount DECIMAL(10,2) NOT NULL,
    payment_schedule ENUM('weekly', 'biweekly', 'monthly') DEFAULT 'weekly',
    due_date DATE NOT NULL,
    status ENUM('active', 'completed', 'cancelled', 'defaulted') DEFAULT 'active',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);

CREATE TABLE layaway_items (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    layaway_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (layaway_id) REFERENCES layaway(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE layaway_payments (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    layaway_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    received_by INT UNSIGNED,
    FOREIGN KEY (layaway_id) REFERENCES layaway(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by) REFERENCES users(id)
);
```

#### 2. AI-Powered Product Search

**Description**: Add AI to all search fields to help find products faster

**Requirements:**
- Fuzzy matching for typos
- Synonyms and related terms
- Natural language queries ("blue fins for cold water")
- Learn from search patterns

**Implementation Options:**

**Option A: Simple Enhancement (Quick Win)**
- Use existing `LIKE` with multiple columns
- Add fuzzy matching with Levenshtein distance
- Search across multiple fields simultaneously
- No external AI required

**Option B: Full AI Integration**
- Integrate OpenAI API for semantic search
- Vector embeddings for products
- Natural language understanding
- Requires API keys and ongoing costs

**Recommended**: Start with Option A, upgrade to B later if needed

**Implementation Plan (Option A):**
1. Update product search to check multiple fields
2. Add phonetic matching (Soundex/Metaphone)
3. Add search scoring/ranking
4. Cache popular searches
5. Add search analytics

**Estimated Effort**: 3-4 hours (Option A), 10-15 hours (Option B)

#### 3. Compressor Tracking System

**Description**: Easy way to update compressor hours and oil fills

**Requirements:**
- Track multiple compressors
- Log running hours
- Track oil changes
- Maintenance reminders
- Service history
- Quick-add interface

**Implementation Plan:**
1. Create compressor tables (migration 038)
2. Create compressor management interface
3. Add quick-entry widget to sidebar or dashboard
4. Add maintenance alerts
5. Create compressor reports

**Database Schema:**
```sql
CREATE TABLE compressors (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    serial_number VARCHAR(100),
    manufacturer VARCHAR(100),
    model VARCHAR(100),
    purchase_date DATE,
    current_hours DECIMAL(10,2) DEFAULT 0,
    last_oil_change_hours DECIMAL(10,2) DEFAULT 0,
    oil_change_interval_hours INT DEFAULT 100,
    last_service_date DATE,
    next_service_due_hours DECIMAL(10,2),
    is_active BOOLEAN DEFAULT TRUE,
    location VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE compressor_logs (
    id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    compressor_id INT UNSIGNED NOT NULL,
    log_type ENUM('hours', 'oil_change', 'service', 'repair', 'note') NOT NULL,
    hours_logged DECIMAL(10,2),
    description TEXT,
    logged_by INT UNSIGNED,
    logged_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (compressor_id) REFERENCES compressors(id) ON DELETE CASCADE,
    FOREIGN KEY (logged_by) REFERENCES users(id)
);
```

**UI Features:**
- Dashboard widget showing "Add Hours" quick button
- Sidebar quick-add button
- Full compressor management page
- Maintenance alerts when oil change due
- Service history timeline

**Estimated Effort**: 5-7 hours

#### 4. Sidebar Navigation Improvements

**Description**: Rework sidebar to make it easier to navigate

**Current Issues:**
- Too many items?
- Not well organized?
- Hard to find things?
- Need icons?

**Recommended Improvements:**
1. Group related items into collapsible sections
2. Add icons to all menu items
3. Add search functionality to sidebar
4. Add recent/favorites section
5. Add quick actions section
6. Improve mobile responsiveness

**Implementation Plan:**
1. Analyze current sidebar structure
2. Create new grouped structure
3. Add collapsible sections
4. Add icons (Bootstrap Icons)
5. Add sidebar search
6. Test on mobile

**Estimated Effort**: 3-4 hours

**Proposed Structure:**
```
📊 Dashboard
├─ 📈 Analytics
└─ 📋 Reports

💰 Sales
├─ 🛒 Point of Sale
├─ 🧾 Transactions
└─ 💳 Payments

👥 Customers
├─ 📇 All Customers
├─ ➕ Add Customer
└─ 🎯 Marketing

📦 Inventory
├─ 📦 Products
├─ 📊 Categories
├─ 🏷️ Vendors
└─ 📍 Stock Locations

🎓 Courses
├─ 📚 All Courses
├─ 📅 Schedules
├─ 👨‍🎓 Enrollments
└─ ✅ Attendance

🔧 Services
├─ 🔧 Work Orders
├─ 🎒 Rentals
├─ 💨 Air Fills
└─ ⚙️ Compressor Log

✈️ Trips
├─ 🌴 All Trips
├─ 📅 Schedules
└─ 📝 Bookings

📊 Reports
├─ 📈 Sales Reports
├─ 📦 Inventory Reports
├─ 👥 Customer Reports
└─ 🎓 Course Reports

⚙️ Settings (Admin Only)
├─ ⚙️ General Settings
├─ 👤 User Management
├─ 🎨 Branding
└─ 🔒 Security
```

### 📝 Documentation Needs

#### Barcode Scanning Guide

**To Be Created:** `docs/BARCODE_SCANNING.md`

**Contents:**
- How barcode scanning works in POS
- Supported barcode scanners
- Setup instructions
- Troubleshooting
- Barcode generation for products

## Priority Recommendations

### High Priority (Do First)
1. **Compressor Tracking** - Requested specifically, useful feature
2. **Sidebar Improvements** - Affects daily usability
3. **Barcode Scanning Documentation** - Clarify how it currently works

### Medium Priority (Do Next)
4. **Layaway Functionality** - Good for customer service
5. **POS Search Clarification** - Fix if there's actually an issue

### Lower Priority (Future Enhancement)
6. **AI-Powered Search** - Nice to have, start with simple improvements

## Implementation Timeline

### Week 1
- Day 1-2: Compressor tracking system
- Day 3: Sidebar navigation improvements
- Day 4: Barcode scanning documentation
- Day 5: Testing and refinement

### Week 2
- Day 1-2: Layaway functionality
- Day 3: POS search improvements (once clarified)
- Day 4-5: AI search enhancements (Option A)

## Questions Needing Answers

1. **POS Customer Search**: Can you provide a screenshot of the "black" issue? Or describe which part is hard to read?

2. **Barcode Scanning**: Do you have USB barcode scanners? Should they work automatically or need setup?

3. **Sidebar**: What specific navigation issues are you experiencing? Too cluttered? Items hard to find?

4. **AI Search**: Do you want full AI integration (requires API costs) or enhanced smart search (free)?

5. **Layaway**: Any specific payment schedule requirements? Weekly, biweekly, monthly? Deposit percentage?

6. **Compressor**: How many compressors do you have? What information is most important to track?

## Next Steps

Please review this document and provide:
1. Priority order (which features are most important)
2. Answers to the clarification questions above
3. Any additional requirements or changes needed

Once priorities are confirmed, we'll proceed with implementation!

---

## Already Implemented - Quick Reference

**Course Workflow System** - Just completed! See:
- `/docs/COURSE_WORKFLOW_SYSTEM.md` - Full documentation
- `/STREAMLINED_WORKFLOW_SUMMARY.md` - Overview
- `/WORKFLOW_QUICK_START.md` - Quick start guide

**Product Fields** - All added in Migration 035:
- QR Code, Barcode
- Weight with units (lb/kg/oz/g)
- Dimensions, Color, Material
- Manufacturer, Warranty
- Location in store
- Supplier information
- Expiration date

**Deployment Ready** - Production-ready with:
- Automated installation script
- Fedora Server 43 specific guide
- DiveShop360 migration mapping
- Complete documentation
