# Nautilus Dive Shop - Implementation Roadmap

**Date:** November 5, 2025
**Priority:** PADI Compliance & Tablet Optimization
**Goal:** Production-ready dive shop management system

---

## 🎯 Implementation Priorities

### Phase 1: Critical Fixes (Today - 2 hours)
1. ✅ Fix cash drawer status column
2. ✅ Fix view path errors
3. ✅ Add missing routes or hide unimplemented features
4. ✅ Deploy all fixes

### Phase 2: PADI Compliance System (This Week - 3 days)
1. Student assessment & skills tracking
2. Course record & referral forms
3. Medical form management
4. Liability waivers integration
5. Training completion documentation

### Phase 3: Tablet Optimization (This Week - 2 days)
1. Touch-friendly UI across all screens
2. Offline capability (PWA)
3. Camera integration for all devices
4. Dive site data entry optimization

### Phase 4: Enhanced Features (Next Week)
1. Course scheduling templates
2. Customer certification display with logos
3. Trip manifest generation
4. Advanced reporting

---

## 📋 PADI Forms Integration Plan

### Forms Available (from `/Padi_Forms/`)

**Pre-Training Forms:**
- 10346 Diver Medical Form.pdf
- 10072 Release of Liability (General Training).pdf
- 10060 Standard Safe Diving Practices.pdf
- 10334 Non-Agency Disclosure.pdf
- 10348 Florida Minor Child Parent Agreement.pdf
- 10615 Youth Diving Responsibility.pdf

**Course Forms:**
- 10056 Open Water Diver Course Record and Referral.pdf
- 007DT Preregistration and Team Teaching Tracking.pdf
- 10081 PADI Water Skills Checkoff.pdf
- 10062 PADI Scuba Diver Statement.pdf

**Training Completion:**
- 10234 Training Completion Form.pdf
- Entry-level Diver Referrals Guidelines.pdf

**Specialty Forms:**
- 10078 Enriched Air (Nitrox) Training Release.pdf
- 71876 Enriched Air EU Version.pdf
- 10083 Repetitive Dive Worksheet.pdf

**Safety & Incidents:**
- 10120 Incident Report Form.pdf
- 384DT PIC Online Worksheet.pdf
- 752DT Predive Safety Check Poster.pdf

### Database Structure for PADI Compliance

```sql
-- Student course records
CREATE TABLE course_student_records (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT UNSIGNED NOT NULL,
    form_type VARCHAR(100), -- 'course_record', 'referral', etc.

    -- Knowledge Development
    knowledge_review_scores JSON, -- Quiz scores per module
    final_exam_score DECIMAL(5,2),
    knowledge_status ENUM('pending', 'in_progress', 'completed', 'failed'),

    -- Confined Water (Pool)
    confined_water_sessions JSON, -- Skills per session
    confined_water_status ENUM('pending', 'in_progress', 'completed', 'failed'),

    -- Open Water
    open_water_dives JSON, -- Skills per dive
    open_water_status ENUM('pending', 'in_progress', 'completed', 'failed'),

    -- Overall
    overall_status ENUM('enrolled', 'in_training', 'completed', 'referred', 'failed'),
    completion_date DATE,
    certification_number VARCHAR(50),

    -- Referral Information
    is_referral BOOLEAN DEFAULT FALSE,
    referral_shop_name VARCHAR(255),
    referral_shop_location VARCHAR(255),
    referral_instructor VARCHAR(255),
    referred_date DATE,
    referral_notes TEXT,

    instructor_id INT UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (enrollment_id) REFERENCES course_enrollments(id),
    FOREIGN KEY (instructor_id) REFERENCES users(id)
);

-- Skills assessment
CREATE TABLE student_skills_assessment (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    record_id INT UNSIGNED NOT NULL,
    session_type ENUM('confined_water', 'open_water'),
    session_number INT,
    session_date DATE,

    skill_name VARCHAR(255), -- e.g., "Mask removal and replacement"
    skill_code VARCHAR(50),  -- e.g., "CW1", "OW1"
    performance ENUM('not_performed', 'needs_improvement', 'adequate', 'proficient'),
    pass BOOLEAN,
    attempts INT DEFAULT 1,

    assessed_by INT UNSIGNED,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (record_id) REFERENCES course_student_records(id),
    FOREIGN KEY (assessed_by) REFERENCES users(id),

    INDEX idx_record_session (record_id, session_type, session_number)
);

-- Medical forms
CREATE TABLE customer_medical_forms (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    form_type VARCHAR(100) DEFAULT 'padi_medical',

    -- Medical questions (all NO = cleared to dive)
    medical_questions JSON, -- 10+ questions with YES/NO
    any_yes_answers BOOLEAN DEFAULT FALSE,
    physician_clearance_required BOOLEAN DEFAULT FALSE,

    -- Physician clearance (if needed)
    physician_name VARCHAR(255),
    physician_signature_path VARCHAR(255),
    physician_date DATE,
    cleared_to_dive BOOLEAN DEFAULT FALSE,

    -- Participant signature
    participant_signature_path VARCHAR(255),
    participant_date DATE,

    -- Validity
    form_date DATE NOT NULL,
    expiry_date DATE, -- Typically 1 year
    status ENUM('pending', 'cleared', 'needs_physician', 'expired') DEFAULT 'pending',

    uploaded_pdf_path VARCHAR(255),
    notes TEXT,

    FOREIGN KEY (customer_id) REFERENCES customers(id),
    INDEX idx_customer_status (customer_id, status),
    INDEX idx_expiry (expiry_date)
);

-- Liability waivers
CREATE TABLE customer_waivers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    waiver_type ENUM('general_training', 'nitrox', 'travel', 'minor', 'non_agency') NOT NULL,

    course_id INT UNSIGNED, -- If course-specific
    trip_id INT UNSIGNED,   -- If trip-specific

    waiver_text TEXT, -- Full waiver text
    signature_path VARCHAR(255),
    signature_date DATE,
    witness_name VARCHAR(255),
    witness_signature_path VARCHAR(255),

    -- For minors
    parent_guardian_name VARCHAR(255),
    parent_signature_path VARCHAR(255),

    valid_from DATE NOT NULL,
    valid_until DATE,
    status ENUM('pending', 'signed', 'expired') DEFAULT 'pending',

    pdf_path VARCHAR(255), -- Signed PDF

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    INDEX idx_customer_type (customer_id, waiver_type),
    INDEX idx_status (status)
);
```

---

## 📱 Tablet Optimization Plan

### UI Requirements for Shop & Dive Site

**Shop Use Cases:**
- Customer check-in (large buttons, quick access)
- Course enrollment (simplified workflow)
- Equipment issue/return (barcode scanner)
- Quick POS for gear sales
- Student roster view

**Dive Site Use Cases:**
- Student attendance (checkboxes, large touch targets)
- Skills assessment (quick skill checkoff)
- Incident reporting (offline capability)
- Pre-dive safety check
- Photo capture (equipment, students, incidents)

### Technical Implementation

```css
/* tablet-optimized.css */

/* Touch-friendly minimum sizes */
.btn, button, .clickable {
    min-height: 48px;
    min-width: 48px;
    padding: 12px 24px;
}

input, select, textarea {
    min-height: 48px;
    font-size: 16px; /* Prevents iOS zoom */
    padding: 12px;
}

/* Large tap targets for checkboxes */
.checkbox-lg {
    width: 32px;
    height: 32px;
    cursor: pointer;
}

/* Tablet-specific layouts */
@media (min-width: 768px) and (max-width: 1024px) {
    .sidebar { width: 200px; }
    .table-responsive {
        display: block;
        overflow-x: auto;
    }
}

/* Portrait mode optimization */
@media (orientation: portrait) {
    .nav-horizontal { flex-direction: column; }
}

/* Hide desktop-only elements on tablets */
@media (max-width: 1024px) {
    .desktop-only { display: none !important; }
    .mobile-menu-toggle { display: block; }
}
```

---

## 📸 Universal Camera Capture

### HTML5 Camera Integration

```html
<!-- Customer photo capture component -->
<div class="photo-capture">
    <div class="photo-preview">
        <img id="photoPreview" src="" alt="Preview" />
        <video id="videoPreview" autoplay style="display:none;"></video>
        <canvas id="photoCanvas" style="display:none;"></canvas>
    </div>

    <div class="photo-actions">
        <!-- Mobile/Tablet: Use device camera -->
        <input
            type="file"
            id="cameraInput"
            accept="image/*"
            capture="user"
            class="d-none"
        />
        <button onclick="openCamera()" class="btn btn-primary btn-lg">
            <i class="bi bi-camera-fill"></i> Take Photo
        </button>

        <!-- Desktop: Use webcam -->
        <button onclick="startWebcam()" class="btn btn-secondary btn-lg">
            <i class="bi bi-webcam"></i> Use Webcam
        </button>

        <!-- Upload from files -->
        <button onclick="document.getElementById('fileInput').click()" class="btn btn-outline-primary btn-lg">
            <i class="bi bi-upload"></i> Upload Photo
        </button>
        <input type="file" id="fileInput" accept="image/*" class="d-none" />
    </div>
</div>

<script>
// Mobile/tablet camera
function openCamera() {
    if (/Android|webOS|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
        // Mobile device - use file input with capture
        document.getElementById('cameraInput').click();
    } else {
        // Desktop - use webcam
        startWebcam();
    }
}

// Webcam for desktop
async function startWebcam() {
    try {
        const video = document.getElementById('videoPreview');
        const stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: 'user' }
        });
        video.srcObject = stream;
        video.style.display = 'block';

        // Show capture button
        document.getElementById('captureBtn').style.display = 'block';
    } catch(err) {
        alert('Camera access denied: ' + err.message);
    }
}

// Capture from webcam
function capturePhoto() {
    const video = document.getElementById('videoPreview');
    const canvas = document.getElementById('photoCanvas');
    const context = canvas.getContext('2d');

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    context.drawImage(video, 0, 0);

    // Convert to blob and upload
    canvas.toBlob(blob => {
        uploadPhoto(blob);
    }, 'image/jpeg', 0.9);

    // Stop webcam
    video.srcObject.getTracks().forEach(track => track.stop());
    video.style.display = 'none';
}

// Handle file input (mobile capture or upload)
document.getElementById('cameraInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        uploadPhoto(file);
    }
});

// Upload photo to server
function uploadPhoto(blob) {
    const formData = new FormData();
    formData.append('photo', blob, 'customer_photo.jpg');
    formData.append('customer_id', customerId);

    fetch('/store/api/upload-photo', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('photoPreview').src = data.photo_url;
            alert('Photo saved successfully!');
        }
    });
}
</script>
```

---

## 🔧 Quick Fixes Script

```bash
#!/bin/bash
# fix-all-issues.sh

echo "Applying all critical fixes..."

# 1. Fix database
echo "→ Fixing database columns..."
mysql -u user -p nautilus < /tmp/quick-fixes.sql

# 2. Fix view paths
echo "→ Fixing view header paths..."
find /var/www/html/nautilus/app/Views -type f -name "*.php" \
    -exec grep -l "DIR__ . '/../../layouts/" {} \; | \
    while read file; do
        sed -i "s|DIR__ . '/../../layouts/|DIR__ . '/../../../layouts/|g" "$file"
        echo "  Fixed: $(basename $file)"
    done

# 3. Set permissions
echo "→ Setting permissions..."
sudo chown -R apache:apache /var/www/html/nautilus
sudo chmod -R 755 /var/www/html/nautilus/app
sudo chmod -R 775 /var/www/html/nautilus/storage

echo "✅ All fixes applied!"
```

---

## 📅 Implementation Timeline

### Today (2-3 hours)
- [x] Apply database fixes
- [ ] Fix view paths
- [ ] Add missing routes
- [ ] Test on tablet (Safari/Chrome)

### Tomorrow (Full day)
- [ ] Create student assessment UI
- [ ] Implement skills checkoff system
- [ ] Add medical form management
- [ ] Camera capture component

### Day 3-4
- [ ] Course record & referral system
- [ ] Waiver digital signing
- [ ] Tablet UI optimization
- [ ] Offline capability (PWA)

### Day 5
- [ ] Testing with real PADI course
- [ ] Documentation
- [ ] Training materials
- [ ] Deploy to production

---

## 🎓 PADI Compliance Features

### Must-Have (Week 1)
1. ✅ Course record tracking
2. ✅ Skills assessment (CW & OW)
3. ✅ Medical form management
4. ✅ Liability waivers
5. ✅ Referral system

### Should-Have (Week 2)
6. Digital signature capture
7. PDF generation for all forms
8. Instructor documentation
9. Student progress dashboard
10. Certification issuance

### Nice-to-Have (Week 3+)
11. E-learning integration
12. Video upload for skill demos
13. Equipment tracking per student
14. Dive log integration
15. Automatic PADI reporting

---

## ✅ Success Criteria

**System is ready when:**
1. ✅ All errors fixed (no 500 errors)
2. ✅ Works on iPad/Android tablet
3. ✅ Camera works on all devices
4. ✅ Can track complete OW course
5. ✅ Generates all required PADI forms
6. ✅ Instructors can use at dive site
7. ✅ Meets PADI standards compliance

---

**Next Steps:**
1. Run `/tmp/quick-fixes.sql` to fix database
2. I'll implement PADI student assessment system
3. Add camera capture to customer profile
4. Optimize for tablet use

**Ready to proceed?**
