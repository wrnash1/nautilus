# Critical Issues to Fix Before Reinstall

## Issues Found

You've identified several important missing pieces in the application:

### 1. ✅ Staff/Employee Management is Incomplete
**Issue**: Can't add employees, instructors, or contractors
**Status**: Controllers and database tables exist but missing CREATE/EDIT views

### 2. ✅ Customer Certification Tracking Missing
**Issue**: No way to enter customer's training organization or certifications
**Status**: Database tables exist but missing UI to manage certifications

### 3. ✅ POS Customer Form vs CRM Customer Form Mismatch
**Issue**: POS "Add Customer" modal is simplified, CRM form is comprehensive
**Status**: POS modal needs to match full customer form OR save partial data properly

---

## What EXISTS in Database (Already Created)

### Staff/Employee Tables ✅
- `staff` table with fields for employees/instructors/contractors
- `staff_schedules` for scheduling
- `staff_timeclock` for time tracking
- `staff_commissions` for commission tracking
- `staff_performance_metrics` for KPIs

### Certification Tables ✅
- `certification_agencies` (PADI, SSI, SDI, NAUI, etc.) - **PRE-LOADED**
- `certifications` (60+ certs across all agencies) - **PRE-LOADED**
- `customer_certifications` to track customer certs
- Includes agency logos, colors, verification URLs

### Customer Tables ✅
- Full customer management system exists
- B2C and B2B support
- Emergency contacts
- Addresses

---

## What's MISSING (Need to Create)

### 1. Staff Management Views

**Missing Files**:
- `/app/Views/staff/create.php` - Add new employee/instructor
- `/app/Views/staff/edit.php` - Edit employee details
- `/app/Views/staff/show.php` - View employee profile

**What Needs to be Added to Staff Form**:
```
- Employee Type: (Employee, Instructor, Contractor, Manager)
- Personal Info: First/Last Name, Email, Phone
- Employment Details: Hire Date, Position, Department
- Instructor Certifications: (if instructor)
  - Certification Agency (PADI, SSI, etc.)
  - Instructor Number
  - Expiration Date
  - Specialties
- Pay Rate: Hourly/Salary
- Commission Rate: Percentage
- Emergency Contact
- Notes
```

### 2. Customer Certification Management

**Missing Files**:
- UI to add/edit customer certifications in customer profile
- Certification display on customer detail page

**What Needs to be on Customer Profile**:
```
- Certification Agency dropdown (PADI, SSI, SDI, etc.)
- Certification Level dropdown (Open Water, Advanced, Rescue, etc.)
- Certification Number
- Issue Date
- Expiration Date (if applicable)
- Upload certification card image
- Multiple certifications per customer
- Display with agency logo and badge
```

### 3. Unified Customer Forms

**Issue**: Two different customer creation forms:

**POS Modal** (Simple - Lines 224-274 in pos/index.php):
- First Name, Last Name
- Email, Phone
- Company Name
- Newsletter opt-in
- **MISSING**: Birth date, emergency contact, certifications, full address

**CRM Form** (Complete - customers/create.php):
- Everything above PLUS:
- Customer Type (B2C/B2B)
- Birth Date
- Emergency Contact
- Full Billing Address
- B2B fields (Credit limit, terms, tax exempt)

**Solution Needed**:
- Option A: Make POS modal save to customers table with partial data, allow editing later
- Option B: Make POS modal include essential fields (birth date, emergency contact)
- Option C: Make POS "Add Customer" button open full form instead of modal

---

## Files to Create Before Reinstall

I'll create these for you now so they're included when you reinstall:

### 1. Staff Create View
### 2. Staff Edit View
### 3. Staff Show View (Profile)
### 4. Customer Certifications Component
### 5. Updated POS Customer Modal

---

## Post-Install Checklist

After reinstalling, verify these work:

### Staff Management:
- [ ] Navigate to Staff → Employees
- [ ] Click "Add Employee"
- [ ] Fill in employee details with type: Employee
- [ ] Save and verify appears in list
- [ ] Click "Add Employee" again
- [ ] Select type: Instructor
- [ ] Fill in instructor certification details
- [ ] Save and verify

### Customer Certifications:
- [ ] Navigate to Customers
- [ ] Click on a customer
- [ ] See "Certifications" section
- [ ] Click "Add Certification"
- [ ] Select agency (PADI)
- [ ] Select certification (Open Water Diver)
- [ ] Enter certification number
- [ ] Save and verify shows with PADI logo

### Unified Customer Creation:
- [ ] Go to POS
- [ ] Click "New Customer"
- [ ] Enter minimal info (name, phone, email)
- [ ] Save customer
- [ ] Verify customer created
- [ ] Go to Customers → Find customer
- [ ] Edit to add full details (birth date, emergency contact, certifications)

---

## Recommended Approach

Since you're going to reinstall anyway, here's the best approach:

### Step 1: I'll Create Missing Views Now
I'll create all the missing views with proper forms before you reinstall.

### Step 2: You Reinstall Fresh
Run the installer with a clean database.

### Step 3: Test Everything
Use the post-install checklist above to verify all features work.

### Step 4: Add Test Data
- Add 2-3 employees
- Add 1-2 instructors with certifications
- Add customers with certifications
- Create test rental, repair, air fill to test waivers

---

## Additional Enhancements to Include

While we're at it, let me add a few more things that would be useful:

### 1. Instructor Availability Calendar
Show which instructors are available for courses

### 2. Customer Diving Log
Track customer dive count for prerequisite checking

### 3. Equipment Size Tracking
Track customer's equipment sizes (wetsuit, fins, etc.) for rentals

### 4. Quick Actions on Customer Profile
- Send waiver
- View signed waivers
- View rental history
- View course history
- View certifications

---

## Database Schema Verification

Before reinstalling, let's verify these tables will be created:

### Staff Tables:
```sql
- staff (employees, instructors, contractors)
- staff_schedules
- staff_timeclock
- staff_commissions
- staff_performance_metrics
- staff_certifications (for instructors)
```

### Certification Tables:
```sql
- certification_agencies (10 agencies pre-loaded)
- certifications (60+ certs pre-loaded)
- customer_certifications
- certification_prerequisites
```

### Customer Tables:
```sql
- customers
- customer_addresses
- customer_communications
- customer_certifications
- customer_medical_info
- customer_travel_documents
```

All these should exist in the migration files. Let me verify and create the missing views now.

---

## Ready to Proceed?

I'll now create:
1. ✅ Staff create/edit/show views with instructor certification support
2. ✅ Customer certification management component
3. ✅ Updated POS customer modal with essential fields
4. ✅ Routes for all new functionality

Then you can safely reinstall with everything in place!

Shall I proceed with creating these files?
