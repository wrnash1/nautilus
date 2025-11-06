# Nautilus Testing - Issues Found & Action Plan

**Date:** November 5, 2025
**Tester Feedback:** Extensive testing across all modules
**Status:** Multiple issues identified, prioritized for fixing

---

## 🚨 Critical Issues (Fix Immediately)

### 1. ✅ Cash Drawer Status Column Missing
**Error:** `Unknown column 'status' in 'WHERE'`
**Impact:** Dashboard won't load
**Fix:** Run `/tmp/quick-fixes.sql`
```bash
mysql -u user -p nautilus < /tmp/quick-fixes.sql
```
**Status:** SQL fix created, ready to apply

### 2. ❌ Customer Tags View - Wrong Header Path
**Error:** `require(../../layouts/header.php): Failed to open stream`
**Location:** `/app/Views/customers/tags/create.php:4`
**Fix Needed:** Change `../../layouts/` to `../../../layouts/`
**Files to Fix:**
- `app/Views/customers/tags/create.php`
- `app/Views/customers/tags/edit.php`
- Any other views in deep subdirectories

---

## 🔴 High Priority - Missing Routes

### Missing Route Handlers
| URL | Feature | Status |
|-----|---------|--------|
| `/categories/6/edit` | Edit Category | Route not found |
| `/vendors/create` | Create Vendor | Route not found |
| `/reports/sales/export` | Export Sales Report | Route not found |
| `/rentals/equipment/create` | Create Rental Equipment | Route not found |
| `/rentals/reservations/create` | Create Reservation | Route not found |
| `/courses/2` | View Course | Route not found |
| `/courses/2/edit` | Edit Course | Route not found |
| `/trips/create` | Create Trip | Route not found |

**Root Cause:** Routes exist in database/menu but handlers not implemented or not in `routes/web.php`

**Action Required:**
1. Audit all menu items vs actual routes
2. Implement missing controllers
3. Add routes to `routes/web.php`
4. Or hide menu items until implemented

---

## 💡 Feature Requests from Testing

### 1. Customer Certifications Display
**Request:** "Customer need to know the type of certification the customer have with logo of the certification"

**Current State:** Certifications stored in database
**Needed:**
- Display certification agency logos
- Show certification level with icon
- Show expiry dates prominently
- Visual certification card/badge layout

**Implementation Plan:**
```
Customer Profile Page:
┌─────────────────────────────────────┐
│ Certifications                      │
├─────────────────────────────────────┤
│ [PADI Logo] Open Water Diver        │
│ Issued: Jan 2024 | Expires: Never   │
│                                      │
│ [SSI Logo] Advanced Open Water      │
│ Issued: Mar 2024 | Expires: Never   │
└─────────────────────────────────────┘
```

**Files to Create/Modify:**
- Add certification agency logos to `public/assets/images/cert-logos/`
- Update customer view to display certifications prominently
- Add certification card component

---

### 2. Customer Photo Capture
**Request:** "Customer photo. Can you use the computer camera to take a photo?"

**Current State:** Photo upload exists, but no camera capture
**Needed:**
- Webcam/device camera integration
- Photo capture button
- Crop/adjust before saving
- Works on desktop, tablet, iPad

**Implementation:**
```html
<button onclick="capturePhoto()">
  <i class="bi bi-camera"></i> Take Photo
</button>
<input type="file" accept="image/*" capture="user">
```

**Technology:**
- HTML5 `<input capture="user">` for mobile
- WebRTC `getUserMedia()` for desktop
- Canvas API for cropping
- Progressive enhancement (fallback to upload)

---

### 3. iPad/Tablet Support
**Request:** "Can this application work on a IPAD or Google Tablet?"

**Current State:** Responsive CSS exists, needs testing/optimization

**Requirements:**
- Touch-friendly buttons (min 44x44px)
- Responsive tables (horizontal scroll or cards)
- Virtual keyboard friendly forms
- Offline capability (PWA)
- Camera access for photos

**Testing Needed:**
- iPad Safari
- Android Chrome
- Touch gestures
- Portrait/landscape modes

**Quick Wins:**
```css
/* Make everything touch-friendly */
.btn { min-height: 44px; min-width: 44px; }
input, select { min-height: 44px; font-size: 16px; /* prevents zoom */ }
```

---

### 4. Waivers - Customer Linking
**Request:** "Waivers how do you know which customer they belong to. Should this be with the customer section?"

**Current State:** Waivers exist but customer link unclear
**Needed:**
- Clear customer → waiver relationship
- Display waivers on customer profile
- Filter waivers by customer
- "No way to add a new Waiver?"

**Recommended Structure:**
```
Customer Profile > Waivers Tab
- List all waivers signed by this customer
- Button: "Add New Waiver"
- Show: Type, Date Signed, Expiry, Status
```

**Database Check:**
```sql
-- Verify waivers table has customer_id
DESCRIBE waivers;
-- Should see customer_id column
```

---

### 5. Course Scheduling & Templates
**Request:** "Need a easy way to schedule courses so the company can plan for the whole year. Maybe create a template and change the dates."

**Feature Requirements:**

**Course Templates:**
```
Template: "Open Water Weekend Course"
- Duration: 3 days (Fri-Sun)
- Times: 9 AM - 5 PM
- Max Students: 8
- Required Equipment: [list]
- Curriculum: PADI OW standards
```

**Bulk Scheduling:**
```
Create from Template:
- Select template
- Choose start dates (multi-select calendar)
- Assign instructors
- Generate 12 months of schedules
```

**Implementation:**
1. Create `course_templates` table
2. Add "Create from Template" button
3. Multi-date picker for bulk creation
4. Auto-assign instructors based on availability

---

### 6. Student Performance Tracking
**Request:** "Need a way to track the student performance for the course. Need to follow PADI instructor document."

**PADI Requirements:**
- Knowledge Development (quizzes, exams)
- Confined Water Skills (pool sessions)
- Open Water Dives (skills checklist)
- Final grades/pass-fail
- Referral forms

**Database Structure Needed:**
```sql
CREATE TABLE student_assessments (
    id INT PRIMARY KEY,
    enrollment_id INT,
    assessment_type ENUM('knowledge', 'confined_water', 'open_water'),
    skill_name VARCHAR(255),
    score DECIMAL(5,2),
    pass_fail ENUM('pass', 'fail', 'pending'),
    assessed_by INT, -- instructor
    assessed_at TIMESTAMP,
    notes TEXT
);
```

**UI Components:**
- Skills checklist per session
- Grade entry forms
- Progress dashboard for instructors
- Student progress report PDF

---

### 7. Referral System
**Request:** "Also need to add a referal section in cause the student finish the course with a different company."

**PADI Referral Form Requirements:**
- Student completes classroom/pool locally
- Finishes open water dives elsewhere
- Referral form documents completed skills
- Partner dive center completes certification

**Implementation:**
```
Enrollment Status: "Referred Out"
- Generate referral form (PDF)
- Track: Referred to (shop name, location)
- Track: Skills completed
- Track: Skills pending
- Upload: Completion confirmation from partner
```

---

### 8. Travel/Trip Customer Information
**Request:** "Need all the information about a customer to send to the destination for example customer name. Certification level last dive last medical and more. Also need a Travel Waiver."

**Trip Manifest Requirements:**
```
Customer Trip Profile:
- Full Name, Passport Info
- Certification Level & Number
- Last Dive Date
- Medical Form Status & Date
- Emergency Contact
- Special Requirements (diet, disabilities)
- Travel Waiver Signed (Y/N)
- Equipment Rental Needs
```

**Documents Needed:**
1. Trip Manifest (all passengers)
2. Individual Travel Waivers
3. Medical Clearance Forms
4. Equipment Rental Agreements
5. Liability Releases

**Database:**
```sql
-- Add to customers table
ALTER TABLE customers
ADD COLUMN last_dive_date DATE,
ADD COLUMN last_medical_date DATE,
ADD COLUMN passport_number VARCHAR(50),
ADD COLUMN passport_expiry DATE,
ADD COLUMN dietary_requirements TEXT,
ADD COLUMN special_needs TEXT;
```

---

## 📊 Priority Matrix

### Must Fix Before Production
1. ✅ Cash drawer status column (blocking dashboard)
2. ❌ Customer tags header path
3. ❌ Missing route handlers (or hide menus)
4. ❌ Customer certification display
5. ❌ Waiver-customer linking

### High Value Features
6. 📸 Camera photo capture
7. 📱 iPad/tablet optimization
8. 📅 Course template/scheduling system
9. 📊 Student performance tracking (PADI compliance)
10. ✈️ Trip manifest & travel waivers

### Future Enhancements
11. Referral system
12. Advanced reporting
13. Rental equipment management
14. Vendor management
15. Category CRUD operations

---

## 🔧 Immediate Action Plan

### Step 1: Apply Critical Fixes (Today)
```bash
# Fix cash drawer status
mysql -u user -p nautilus < /tmp/quick-fixes.sql

# Sync fixed migrations
sudo bash /tmp/sync-fixed-migrations.sh

# Test dashboard loads
# Visit: https://nautilus.local/store
```

### Step 2: Fix View Paths (Today)
- Fix customer tags view header paths
- Audit all other deep subdirectory views
- Test all menu items

### Step 3: Route Audit (Tomorrow)
- List all menu items
- Verify routes exist
- Implement missing controllers OR
- Hide unimplemented features temporarily

### Step 4: Customer Features (This Week)
- Add certification logo display
- Implement camera photo capture
- Test tablet/iPad functionality
- Link waivers to customers

### Step 5: PADI Compliance (Next Week)
- Course templates & bulk scheduling
- Student performance tracking
- Referral system
- Trip manifest generation

---

## 💾 Quick Fixes Available Now

### Fix #1: Cash Drawer Status
```bash
mysql -u user -p nautilus < /tmp/quick-fixes.sql
```

### Fix #2: View Header Paths
Create script: `/tmp/fix-view-paths.sh`
```bash
find /var/www/html/nautilus/app/Views -name "*.php" \
  -exec grep -l "../../layouts/header" {} \; | \
  while read file; do
    sed -i 's|../../layouts/header|../../../layouts/header|g' "$file"
    echo "Fixed: $file"
  done
```

### Fix #3: Hide Unimplemented Routes (Temporary)
Edit menu configuration, comment out:
- Categories CRUD
- Vendors CRUD
- Rental reservations
- Trip creation
- Advanced reports

Until controllers are implemented.

---

## 📞 Questions for You

1. **Priority:** What's most urgent - fixing errors or adding new features?

2. **Tablet:** Do you plan to use this on iPads at the shop? (Affects UI decisions)

3. **PADI:** Do you need full PADI compliance immediately or can it phase in?

4. **Photos:** Webcam capture or just upload from phone/tablet camera?

5. **Waivers:** Should every course automatically require a waiver signature?

6. **Trip Manifests:** What format do destinations need? (PDF, Excel, email?)

---

## ✅ What's Working Well

Based on your testing, these areas work correctly:
- ✅ Installation (after fixes)
- ✅ Course enrollment workflow
- ✅ POS system
- ✅ Customer management (except photos/certs display)
- ✅ Login/authentication
- ✅ Basic navigation

---

**Next Steps:** Let me know which fixes you want me to tackle first, and I'll implement them systematically!
