<?php
/**
 * PADI Training Completion Form (Form 10234)
 * Records course completion and certification issuance
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Nautilus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .completion-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        .completion-card {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 20px;
        }
        .section-header {
            background: linear-gradient(135deg, #0066cc 0%, #0052a3 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .checkbox-card {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            transition: all 0.2s;
        }
        .checkbox-card:hover {
            border-color: #0066cc;
            background: #e7f3ff;
        }
        .checkbox-card input[type="checkbox"] {
            width: 24px;
            height: 24px;
            margin-right: 12px;
            cursor: pointer;
        }
        .signature-canvas {
            border: 3px solid #0066cc;
            border-radius: 8px;
            background: white;
            cursor: crosshair;
            width: 100%;
            height: 200px;
        }
        .skills-summary {
            background: #e7f3ff;
            border: 2px solid #0066cc;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .alert-existing {
            background: #fff3cd;
            border: 2px solid #ffc107;
        }
    </style>
</head>
<body class="bg-light">

<div class="completion-container">
    <!-- Header -->
    <div class="text-center mb-4">
        <h1 class="mb-2">
            <i class="bi bi-award text-primary"></i>
            PADI Training Completion
        </h1>
        <p class="text-muted">Form 10234 - Training Record</p>
    </div>

    <?php if ($existingCompletion): ?>
    <div class="alert alert-existing">
        <h5><i class="bi bi-info-circle"></i> Existing Completion Record</h5>
        <p class="mb-2">
            This course was previously marked as complete on
            <?= date('F j, Y', strtotime($existingCompletion['completion_date'])) ?>.
        </p>
        <a href="/training/completion/<?= $existingCompletion['id'] ?>" class="btn btn-sm btn-warning">
            <i class="bi bi-eye"></i> View Record
        </a>
    </div>
    <?php endif; ?>

    <!-- Student Info Card -->
    <div class="completion-card">
        <div class="row">
            <div class="col-md-6">
                <h5 class="text-primary mb-3">Student Information</h5>
                <p class="mb-2">
                    <strong>Name:</strong> <?= htmlspecialchars($enrollment['student_name']) ?>
                </p>
                <p class="mb-2">
                    <strong>Date of Birth:</strong>
                    <?= $enrollment['date_of_birth'] ? date('F j, Y', strtotime($enrollment['date_of_birth'])) : 'Not provided' ?>
                </p>
            </div>
            <div class="col-md-6">
                <h5 class="text-primary mb-3">Course Information</h5>
                <p class="mb-2">
                    <strong>Course:</strong> <?= htmlspecialchars($enrollment['course_name']) ?>
                </p>
                <p class="mb-2">
                    <strong>Level:</strong> <?= htmlspecialchars($enrollment['certification_level'] ?? 'N/A') ?>
                </p>
                <p class="mb-2">
                    <strong>Instructor:</strong> <?= htmlspecialchars($enrollment['instructor_full_name'] ?? 'Not assigned') ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Skills Summary -->
    <div class="skills-summary">
        <h5 class="mb-3">
            <i class="bi bi-check2-circle"></i>
            Skills Completion Status
        </h5>
        <div class="row">
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center">
                    <span>Total Skills:</span>
                    <strong class="fs-4"><?= $skillsStatus['total_skills'] ?? 0 ?></strong>
                </div>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center">
                    <span>Proficient:</span>
                    <strong class="fs-4 text-success"><?= $skillsStatus['proficient_count'] ?? 0 ?></strong>
                </div>
            </div>
        </div>
        <?php if (($skillsStatus['proficient_count'] ?? 0) < ($skillsStatus['total_skills'] ?? 0)): ?>
        <div class="alert alert-warning mt-3 mb-0">
            <i class="bi bi-exclamation-triangle"></i>
            Not all skills are marked as proficient. Review required before certification.
        </div>
        <?php endif; ?>
    </div>

    <form id="completion-form" method="POST" action="/training/submit-completion">
        <input type="hidden" name="enrollment_id" value="<?= $enrollment['id'] ?>">

        <!-- Training Completion Sections -->
        <div class="completion-card">
            <div class="section-header">
                <i class="bi bi-check-square"></i>
                <h5 class="mb-0">Training Components</h5>
            </div>

            <div class="checkbox-card">
                <label class="d-flex align-items-center">
                    <input type="checkbox" name="theory_completed" required>
                    <div>
                        <strong>Knowledge Development Completed</strong>
                        <p class="text-muted mb-0">Student completed all academic requirements</p>
                    </div>
                </label>
            </div>

            <div class="checkbox-card">
                <label class="d-flex align-items-center">
                    <input type="checkbox" name="confined_water_completed" required>
                    <div>
                        <strong>Confined Water Training Completed</strong>
                        <p class="text-muted mb-0">All confined water sessions completed successfully</p>
                    </div>
                </label>
            </div>

            <div class="checkbox-card">
                <label class="d-flex align-items-center">
                    <input type="checkbox" name="open_water_completed" required>
                    <div>
                        <strong>Open Water Training Completed</strong>
                        <p class="text-muted mb-0">All open water dives completed successfully</p>
                    </div>
                </label>
            </div>

            <div class="checkbox-card">
                <label class="d-flex align-items-center">
                    <input type="checkbox" name="all_skills_proficient" required>
                    <div>
                        <strong>All Skills Proficient</strong>
                        <p class="text-muted mb-0">Student demonstrated proficiency in all required skills</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Certification Details -->
        <div class="completion-card">
            <div class="section-header">
                <i class="bi bi-award"></i>
                <h5 class="mb-0">Certification Details</h5>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Completion Date *</label>
                    <input type="date" class="form-control" name="completion_date"
                           value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Certification Number</label>
                    <input type="text" class="form-control" name="certification_number"
                           placeholder="PADI-XXXXXXXX">
                    <small class="text-muted">Will be assigned by PADI if left blank</small>
                </div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="ecard_issued" id="ecard-issued">
                <label class="form-check-label" for="ecard-issued">
                    <strong>eCard Issued</strong>
                </label>
            </div>

            <div id="ecard-fields" style="display: none;">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">eCard Number</label>
                        <input type="text" class="form-control" name="ecard_number">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">eCard Issue Date</label>
                        <input type="date" class="form-control" name="ecard_issue_date">
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Training -->
        <div class="completion-card">
            <div class="section-header">
                <i class="bi bi-exclamation-circle"></i>
                <h5 class="mb-0">Additional Training (Optional)</h5>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="additional_training_required"
                       id="additional-training">
                <label class="form-check-label" for="additional-training">
                    <strong>Additional Training Required</strong>
                </label>
            </div>

            <div id="additional-training-fields" style="display: none;">
                <label class="form-label">Notes</label>
                <textarea class="form-control" name="additional_training_notes" rows="3"
                          placeholder="Describe what additional training or practice is needed..."></textarea>
            </div>
        </div>

        <!-- Instructor Information -->
        <div class="completion-card">
            <div class="section-header">
                <i class="bi bi-person-badge"></i>
                <h5 class="mb-0">Instructor Information</h5>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Instructor PADI Number *</label>
                <input type="text" class="form-control" name="instructor_padi_number"
                       placeholder="123456" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Instructor Signature *</label>
                <p class="text-muted">Sign below to certify that the student has met all requirements</p>
                <canvas id="instructor-signature" class="signature-canvas"></canvas>
                <input type="hidden" name="instructor_signature" id="instructor-signature-data">
                <button type="button" class="btn btn-outline-secondary mt-2" onclick="clearSignature()">
                    <i class="bi bi-eraser"></i> Clear Signature
                </button>
            </div>
        </div>

        <!-- PADI Submission -->
        <div class="completion-card">
            <div class="section-header">
                <i class="bi bi-cloud-upload"></i>
                <h5 class="mb-0">PADI Submission</h5>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="padi_submitted" id="padi-submitted">
                <label class="form-check-label" for="padi-submitted">
                    <strong>Already Submitted to PADI</strong>
                </label>
            </div>

            <div id="padi-submission-fields" style="display: none;">
                <label class="form-label">PADI Submission Date</label>
                <input type="date" class="form-control" name="padi_submission_date">
            </div>
        </div>

        <!-- Submit -->
        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-success btn-lg" id="submit-btn" disabled>
                <i class="bi bi-check-circle-fill"></i>
                Submit Training Completion
            </button>
            <a href="/store/courses/enrollments/<?= $enrollment['id'] ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
                Cancel
            </a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Signature pad
const canvas = document.getElementById('instructor-signature');
const ctx = canvas.getContext('2d');
let isDrawing = false;
let hasSignature = false;

canvas.width = canvas.offsetWidth;
canvas.height = 200;

ctx.strokeStyle = '#000';
ctx.lineWidth = 2;
ctx.lineCap = 'round';

// Drawing events
canvas.addEventListener('mousedown', startDrawing);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDrawing);
canvas.addEventListener('mouseout', stopDrawing);

// Touch events
canvas.addEventListener('touchstart', (e) => {
    e.preventDefault();
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent('mousedown', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    canvas.dispatchEvent(mouseEvent);
});

canvas.addEventListener('touchmove', (e) => {
    e.preventDefault();
    const touch = e.touches[0];
    const mouseEvent = new MouseEvent('mousemove', {
        clientX: touch.clientX,
        clientY: touch.clientY
    });
    canvas.dispatchEvent(mouseEvent);
});

canvas.addEventListener('touchend', (e) => {
    e.preventDefault();
    canvas.dispatchEvent(new MouseEvent('mouseup'));
});

function startDrawing(e) {
    isDrawing = true;
    const rect = canvas.getBoundingClientRect();
    ctx.beginPath();
    ctx.moveTo(e.clientX - rect.left, e.clientY - rect.top);
    hasSignature = true;
    checkFormComplete();
}

function draw(e) {
    if (!isDrawing) return;
    const rect = canvas.getBoundingClientRect();
    ctx.lineTo(e.clientX - rect.left, e.clientY - rect.top);
    ctx.stroke();
}

function stopDrawing() {
    if (isDrawing) {
        isDrawing = false;
        document.getElementById('instructor-signature-data').value = canvas.toDataURL();
    }
}

function clearSignature() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    document.getElementById('instructor-signature-data').value = '';
    hasSignature = false;
    checkFormComplete();
}

// Show/hide conditional fields
document.getElementById('ecard-issued').addEventListener('change', function() {
    document.getElementById('ecard-fields').style.display = this.checked ? 'block' : 'none';
});

document.getElementById('additional-training').addEventListener('change', function() {
    document.getElementById('additional-training-fields').style.display = this.checked ? 'block' : 'none';
});

document.getElementById('padi-submitted').addEventListener('change', function() {
    document.getElementById('padi-submission-fields').style.display = this.checked ? 'block' : 'none';
});

// Check required checkboxes
function checkFormComplete() {
    const theory = document.querySelector('input[name="theory_completed"]').checked;
    const confined = document.querySelector('input[name="confined_water_completed"]').checked;
    const openWater = document.querySelector('input[name="open_water_completed"]').checked;
    const skills = document.querySelector('input[name="all_skills_proficient"]').checked;

    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = !(theory && confined && openWater && skills && hasSignature);
}

// Add listeners to checkboxes
document.querySelectorAll('input[type="checkbox"][required]').forEach(checkbox => {
    checkbox.addEventListener('change', checkFormComplete);
});

// Form submission
document.getElementById('completion-form').addEventListener('submit', function(e) {
    if (!hasSignature) {
        e.preventDefault();
        alert('Instructor signature is required');
        return false;
    }
});
</script>

</body>
</html>
