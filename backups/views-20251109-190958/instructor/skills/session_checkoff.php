<?php
/**
 * Skills Checkoff View (Tablet-Optimized)
 * Large touch targets, easy-to-use interface for dive sites
 */

$sessionTitle = ucfirst(str_replace('_', ' ', $session_type)) . ' Session ' . $session_number;
$studentName = htmlspecialchars($record['first_name'] . ' ' . $record['last_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $sessionTitle ?> - <?= $studentName ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Tablet Optimization */
        body {
            font-size: 16px; /* Prevent iOS zoom */
            padding-bottom: 80px; /* Space for bottom nav */
        }

        /* Large touch targets */
        .btn-touch {
            min-height: 56px;
            min-width: 56px;
            font-size: 1.1rem;
            padding: 14px 24px;
        }

        .skill-card {
            margin-bottom: 16px;
            border: 2px solid #dee2e6;
            border-radius: 12px;
            transition: all 0.2s;
        }

        .skill-card.completed {
            border-color: #198754;
            background-color: #d1e7dd;
        }

        .skill-card.needs-improvement {
            border-color: #ffc107;
            background-color: #fff3cd;
        }

        .skill-card.not-performed {
            border-color: #dee2e6;
            background-color: #ffffff;
        }

        /* Performance buttons */
        .performance-btn {
            min-height: 64px;
            width: 100%;
            font-size: 0.9rem;
            border: 2px solid transparent;
            transition: all 0.2s;
        }

        .performance-btn.selected {
            border-width: 3px;
            transform: scale(1.05);
        }

        .performance-btn.proficient {
            background-color: #198754;
            color: white;
        }

        .performance-btn.adequate {
            background-color: #0dcaf0;
            color: white;
        }

        .performance-btn.needs-improvement {
            background-color: #ffc107;
            color: #000;
        }

        .performance-btn.not-performed {
            background-color: #6c757d;
            color: white;
        }

        /* Student header */
        .student-header {
            background: linear-gradient(135deg, #0066CC 0%, #004C99 100%);
            color: white;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 12px;
        }

        .student-photo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid white;
            object-fit: cover;
        }

        /* Progress indicator */
        .progress-ring {
            transform: rotate(-90deg);
        }

        .progress-text {
            font-size: 2rem;
            font-weight: bold;
        }

        /* Fixed bottom navigation */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 2px solid #dee2e6;
            padding: 12px;
            z-index: 1000;
            box-shadow: 0 -4px 12px rgba(0,0,0,0.1);
        }

        /* Checkbox styling */
        .form-check-input {
            width: 32px;
            height: 32px;
            cursor: pointer;
        }

        .form-check-label {
            font-size: 1.1rem;
            cursor: pointer;
            padding-left: 12px;
        }

        /* Collapsible skill details */
        .skill-details {
            background-color: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            margin-top: 12px;
        }

        /* Session info badge */
        .session-badge {
            font-size: 1.2rem;
            padding: 12px 24px;
            border-radius: 24px;
        }

        /* Loading indicator */
        .saving-indicator {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #0dcaf0;
            color: white;
            padding: 12px 24px;
            border-radius: 24px;
            display: none;
            z-index: 2000;
        }

        @media (max-width: 768px) {
            .student-header {
                padding: 16px;
            }

            .student-photo {
                width: 60px;
                height: 60px;
            }

            .performance-btn {
                font-size: 0.8rem;
                min-height: 56px;
            }
        }

        /* Offline indicator */
        .offline-badge {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            border-radius: 24px;
            display: none;
            z-index: 2000;
        }
    </style>
</head>
<body>

<!-- Offline Indicator -->
<div class="offline-badge" id="offlineBadge">
    <i class="bi bi-wifi-off"></i> Offline Mode
</div>

<!-- Saving Indicator -->
<div class="saving-indicator" id="savingIndicator">
    <i class="bi bi-cloud-arrow-up"></i> Saving...
</div>

<div class="container-fluid px-3 py-3">
    <!-- Student Header -->
    <div class="student-header">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <?php if (!empty($record['photo_path'])): ?>
                    <img src="<?= htmlspecialchars($record['photo_path']) ?>" alt="<?= $studentName ?>" class="student-photo me-3">
                <?php else: ?>
                    <div class="student-photo me-3 bg-light d-flex align-items-center justify-content-center">
                        <i class="bi bi-person-circle" style="font-size: 3rem; color: #6c757d;"></i>
                    </div>
                <?php endif; ?>
                <div>
                    <h2 class="mb-1"><?= $studentName ?></h2>
                    <p class="mb-0 opacity-75"><?= htmlspecialchars($record['course_name']) ?></p>
                </div>
            </div>
            <div class="text-end">
                <span class="session-badge bg-white text-primary">
                    <?= $sessionTitle ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Progress Overview -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-2">Session Progress</h4>
                    <div class="progress" style="height: 32px;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: 0%;" id="sessionProgress">0%</div>
                    </div>
                    <p class="mt-2 mb-0">
                        <span id="completedCount">0</span> of <span id="totalCount"><?= count($skills) ?></span> skills completed
                    </p>
                </div>
                <div class="col-md-4 text-center">
                    <div class="progress-text" id="progressPercent">0%</div>
                    <small class="text-muted">Complete</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Skills Checkoff List -->
    <div id="skillsList">
        <?php foreach ($skills as $index => $skill): ?>
        <div class="skill-card <?= $skill['pass'] ? 'completed' : 'not-performed' ?>"
             data-skill-id="<?= $skill['id'] ?>"
             data-pass="<?= $skill['pass'] ? '1' : '0' ?>">
            <div class="card-body">
                <div class="row align-items-center">
                    <!-- Skill Name & Pass Checkbox -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="form-check me-3">
                                <input class="form-check-input pass-checkbox"
                                       type="checkbox"
                                       id="pass_<?= $skill['id'] ?>"
                                       <?= $skill['pass'] ? 'checked' : '' ?>
                                       onchange="togglePass(<?= $skill['id'] ?>)">
                            </div>
                            <div>
                                <strong class="d-block"><?= htmlspecialchars($skill['skill_name']) ?></strong>
                                <small class="text-muted">
                                    <?= htmlspecialchars($skill['skill_code'] ?? '') ?>
                                    <?php if ($skill['skill_category']): ?>
                                        • <?= htmlspecialchars($skill['skill_category']) ?>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Buttons -->
                    <div class="col-md-6">
                        <div class="row g-2">
                            <div class="col-6 col-md-3">
                                <button type="button"
                                        class="performance-btn proficient <?= $skill['performance'] === 'proficient' ? 'selected' : '' ?>"
                                        onclick="setPerformance(<?= $skill['id'] ?>, 'proficient')">
                                    <i class="bi bi-star-fill"></i><br>
                                    <small>Proficient</small>
                                </button>
                            </div>
                            <div class="col-6 col-md-3">
                                <button type="button"
                                        class="performance-btn adequate <?= $skill['performance'] === 'adequate' ? 'selected' : '' ?>"
                                        onclick="setPerformance(<?= $skill['id'] ?>, 'adequate')">
                                    <i class="bi bi-check-circle-fill"></i><br>
                                    <small>Adequate</small>
                                </button>
                            </div>
                            <div class="col-6 col-md-3">
                                <button type="button"
                                        class="performance-btn needs-improvement <?= $skill['performance'] === 'needs_improvement' ? 'selected' : '' ?>"
                                        onclick="setPerformance(<?= $skill['id'] ?>, 'needs_improvement')">
                                    <i class="bi bi-exclamation-triangle-fill"></i><br>
                                    <small>Needs Work</small>
                                </button>
                            </div>
                            <div class="col-6 col-md-3">
                                <button type="button"
                                        class="performance-btn not-performed <?= $skill['performance'] === 'not_performed' ? 'selected' : '' ?>"
                                        onclick="setPerformance(<?= $skill['id'] ?>, 'not_performed')">
                                    <i class="bi bi-dash-circle"></i><br>
                                    <small>Not Done</small>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Collapsible Notes Section -->
                <div class="mt-3">
                    <button class="btn btn-sm btn-outline-secondary" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#notes_<?= $skill['id'] ?>">
                        <i class="bi bi-pencil"></i> Add Notes
                    </button>
                    <div class="collapse mt-2" id="notes_<?= $skill['id'] ?>">
                        <div class="skill-details">
                            <textarea class="form-control"
                                      rows="2"
                                      placeholder="Assessment notes..."
                                      onblur="saveNotes(<?= $skill['id'] ?>, this.value)"><?= htmlspecialchars($skill['assessment_notes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Session Notes -->
    <div class="card mt-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-journal-text"></i> Session Notes</h5>
        </div>
        <div class="card-body">
            <textarea class="form-control" rows="4" id="sessionNotes"
                      placeholder="Overall session notes, conditions, observations..."></textarea>
        </div>
    </div>
</div>

<!-- Fixed Bottom Navigation -->
<div class="bottom-nav">
    <div class="d-flex gap-2">
        <a href="/instructor/skills/record/<?= $record['id'] ?>"
           class="btn btn-outline-secondary btn-touch flex-fill">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <button type="button" class="btn btn-success btn-touch flex-fill"
                onclick="completeSession()">
            <i class="bi bi-check-circle-fill"></i> Complete Session
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const recordId = <?= $record['id'] ?>;
const sessionType = '<?= $session_type ?>';
const sessionNumber = <?= $session_number ?>;

// Track changes for offline support
let pendingChanges = [];

// Check online/offline status
window.addEventListener('online', () => {
    document.getElementById('offlineBadge').style.display = 'none';
    syncPendingChanges();
});

window.addEventListener('offline', () => {
    document.getElementById('offlineBadge').style.display = 'block';
});

// Toggle pass checkbox
function togglePass(skillId) {
    const checkbox = document.getElementById(`pass_${skillId}`);
    const skillCard = checkbox.closest('.skill-card');

    const data = {
        assessment_id: skillId,
        pass: checkbox.checked,
        record_id: recordId,
        session_type: sessionType,
        session_number: sessionNumber
    };

    updateSkill(data);

    // Update UI
    skillCard.dataset.pass = checkbox.checked ? '1' : '0';
    skillCard.classList.toggle('completed', checkbox.checked);
    updateProgress();
}

// Set performance level
function setPerformance(skillId, performance) {
    const skillCard = document.querySelector(`[data-skill-id="${skillId}"]`);
    const buttons = skillCard.querySelectorAll('.performance-btn');

    // Update button states
    buttons.forEach(btn => btn.classList.remove('selected'));
    event.target.closest('.performance-btn').classList.add('selected');

    // Auto-check if proficient or adequate
    const pass = (performance === 'proficient' || performance === 'adequate');
    const checkbox = document.getElementById(`pass_${skillId}`);
    if (pass && !checkbox.checked) {
        checkbox.checked = true;
        skillCard.classList.add('completed');
        skillCard.dataset.pass = '1';
    }

    const data = {
        assessment_id: skillId,
        performance: performance,
        pass: pass,
        remediation_needed: (performance === 'needs_improvement'),
        record_id: recordId,
        session_type: sessionType,
        session_number: sessionNumber
    };

    updateSkill(data);
    updateProgress();
}

// Save notes
function saveNotes(skillId, notes) {
    const data = {
        assessment_id: skillId,
        notes: notes,
        record_id: recordId,
        session_type: sessionType,
        session_number: sessionNumber
    };

    updateSkill(data);
}

// Update skill via AJAX
async function updateSkill(data) {
    showSaving();

    try {
        if (!navigator.onLine) {
            pendingChanges.push(data);
            localStorage.setItem('pendingChanges', JSON.stringify(pendingChanges));
            hideSaving();
            return;
        }

        const response = await fetch('/instructor/skills/update-skill', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) {
            throw new Error('Update failed');
        }

        hideSaving();
    } catch (error) {
        console.error('Error updating skill:', error);
        pendingChanges.push(data);
        localStorage.setItem('pendingChanges', JSON.stringify(pendingChanges));
        hideSaving();
    }
}

// Sync pending changes when back online
async function syncPendingChanges() {
    const stored = localStorage.getItem('pendingChanges');
    if (!stored) return;

    const changes = JSON.parse(stored);

    for (const change of changes) {
        await updateSkill(change);
    }

    localStorage.removeItem('pendingChanges');
    pendingChanges = [];
}

// Complete session
async function completeSession() {
    if (!confirm('Mark this session as complete?')) {
        return;
    }

    showSaving();

    try {
        const response = await fetch('/instructor/skills/complete-session', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                record_id: recordId,
                session_type: sessionType,
                session_number: sessionNumber
            })
        });

        if (response.ok) {
            window.location.href = `/instructor/skills/record/${recordId}?success=session_complete`;
        } else {
            alert('Failed to complete session');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error completing session');
    } finally {
        hideSaving();
    }
}

// Update progress display
function updateProgress() {
    const totalCount = document.querySelectorAll('.skill-card').length;
    const completedCount = document.querySelectorAll('.skill-card[data-pass="1"]').length;
    const percent = Math.round((completedCount / totalCount) * 100);

    document.getElementById('completedCount').textContent = completedCount;
    document.getElementById('totalCount').textContent = totalCount;
    document.getElementById('progressPercent').textContent = percent + '%';

    const progressBar = document.getElementById('sessionProgress');
    progressBar.style.width = percent + '%';
    progressBar.textContent = percent + '%';
}

// Show/hide saving indicator
function showSaving() {
    document.getElementById('savingIndicator').style.display = 'block';
}

function hideSaving() {
    setTimeout(() => {
        document.getElementById('savingIndicator').style.display = 'none';
    }, 500);
}

// Initialize progress on page load
updateProgress();

// Load pending changes from localStorage
const stored = localStorage.getItem('pendingChanges');
if (stored) {
    pendingChanges = JSON.parse(stored);
    if (pendingChanges.length > 0 && navigator.onLine) {
        syncPendingChanges();
    }
}
</script>

</body>
</html>
