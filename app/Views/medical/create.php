<?php
/**
 * PADI Medical Form Submission
 * 34 PADI medical questions with digital signature
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
        .medical-form-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        .question-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.2s;
        }
        .question-card:hover {
            border-color: #0066cc;
            box-shadow: 0 4px 12px rgba(0, 102, 204, 0.1);
        }
        .question-number {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            background: #0066cc;
            color: white;
            border-radius: 50%;
            font-weight: bold;
            margin-right: 15px;
        }
        .question-text {
            flex: 1;
            font-size: 16px;
            line-height: 1.6;
        }
        .answer-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .answer-btn {
            flex: 1;
            padding: 12px;
            border: 2px solid #dee2e6;
            background: white;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            min-height: 60px;
        }
        .answer-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .answer-btn.yes {
            border-color: #dc3545;
        }
        .answer-btn.yes.active {
            background: #dc3545;
            color: white;
            border-color: #dc3545;
        }
        .answer-btn.no {
            border-color: #28a745;
        }
        .answer-btn.no.active {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }
        .signature-pad {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            background: white;
            cursor: crosshair;
        }
        .clearance-warning {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        .existing-form-alert {
            background: #d1ecf1;
            border: 2px solid #17a2b8;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .progress-indicator {
            position: sticky;
            top: 20px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            z-index: 100;
        }
        .progress-bar-custom {
            height: 8px;
            border-radius: 4px;
            background: #e9ecef;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            transition: width 0.3s ease;
        }
    </style>
</head>
<body class="bg-light">

<div class="medical-form-container">
    <!-- Header -->
    <div class="text-center mb-4">
        <h1 class="mb-2">
            <i class="bi bi-clipboard2-pulse text-primary"></i>
            PADI Medical Statement
        </h1>
        <p class="text-muted">For: <strong><?= htmlspecialchars($customer['full_name']) ?></strong></p>
    </div>

    <?php if ($existingForm): ?>
    <div class="existing-form-alert">
        <h5><i class="bi bi-info-circle"></i> Existing Medical Form Found</h5>
        <p class="mb-0">
            This customer has a valid medical form on file (expires: <?= date('F j, Y', strtotime($existingForm['expiry_date'])) ?>).
            <br>
            <small class="text-muted">Submitting a new form will replace the existing one.</small>
        </p>
    </div>
    <?php endif; ?>

    <!-- Progress Indicator -->
    <div class="progress-indicator">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-bold">Progress</span>
            <span id="progress-text" class="text-muted">0 / 34</span>
        </div>
        <div class="progress-bar-custom">
            <div id="progress-bar-fill" class="progress-bar-fill" style="width: 0%"></div>
        </div>
    </div>

    <!-- Important Notice -->
    <div class="alert alert-info">
        <h6 class="alert-heading">
            <i class="bi bi-info-circle-fill"></i> Important Medical Information
        </h6>
        <p class="mb-0">
            Please answer all questions honestly. Your safety depends on it.
            <strong>Any "Yes" answer requires physician approval before diving.</strong>
        </p>
    </div>

    <!-- Form -->
    <form id="medical-form" method="POST" action="/medical/submit" enctype="multipart/form-data">
        <input type="hidden" name="customer_id" value="<?= $customer['id'] ?>">

        <!-- Questions -->
        <div id="questions-container">
            <?php foreach ($questions as $index => $question): ?>
            <div class="question-card" data-question="<?= $question['id'] ?>">
                <div class="d-flex align-items-start">
                    <span class="question-number"><?= $index + 1 ?></span>
                    <div class="question-text">
                        <?= htmlspecialchars($question['text']) ?>
                    </div>
                </div>
                <div class="answer-buttons">
                    <button type="button" class="answer-btn yes"
                            onclick="selectAnswer('<?= $question['id'] ?>', 'yes', this)">
                        <i class="bi bi-x-circle"></i> YES
                    </button>
                    <button type="button" class="answer-btn no"
                            onclick="selectAnswer('<?= $question['id'] ?>', 'no', this)">
                        <i class="bi bi-check-circle"></i> NO
                    </button>
                </div>
                <input type="hidden" name="<?= $question['id'] ?>" id="<?= $question['id'] ?>" value="">
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Clearance Warning (shown if any yes) -->
        <div id="clearance-warning" class="clearance-warning" style="display: none;">
            <h5>
                <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                Physician Clearance Required
            </h5>
            <p>
                You answered "Yes" to one or more questions. Per PADI standards, you must obtain
                physician clearance before diving.
            </p>
            <div class="mb-3">
                <label class="form-label fw-bold">Upload Physician Clearance (Optional - can add later)</label>
                <input type="file" class="form-control" name="physician_clearance"
                       accept=".pdf,.jpg,.jpeg,.png">
                <small class="text-muted">
                    Accepted formats: PDF, JPG, PNG (Max 10MB)
                </small>
            </div>
        </div>

        <!-- Digital Signature -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">
                    <i class="bi bi-pencil-square"></i>
                    Participant Signature
                </h5>
                <p class="text-muted">
                    By signing below, I confirm that the information provided above is accurate and complete.
                </p>

                <canvas id="signature-pad" class="signature-pad" width="800" height="200"></canvas>
                <input type="hidden" name="signature_data" id="signature-data">

                <div class="mt-3">
                    <button type="button" class="btn btn-outline-secondary" onclick="clearSignature()">
                        <i class="bi bi-eraser"></i> Clear Signature
                    </button>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-primary btn-lg" id="submit-btn" disabled>
                <i class="bi bi-check-circle-fill"></i>
                Submit Medical Form
            </button>
            <a href="/store/customers/<?= $customer['id'] ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i>
                Cancel
            </a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Answers tracking
const answers = {};
const totalQuestions = <?= count($questions) ?>;
let hasYesAnswer = false;

// Signature pad
const canvas = document.getElementById('signature-pad');
const ctx = canvas.getContext('2d');
let isDrawing = false;
let hasSignature = false;

// Initialize canvas
ctx.strokeStyle = '#000';
ctx.lineWidth = 2;
ctx.lineCap = 'round';

// Mouse events
canvas.addEventListener('mousedown', startDrawing);
canvas.addEventListener('mousemove', draw);
canvas.addEventListener('mouseup', stopDrawing);
canvas.addEventListener('mouseout', stopDrawing);

// Touch events for mobile/tablet
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
    const mouseEvent = new MouseEvent('mouseup', {});
    canvas.dispatchEvent(mouseEvent);
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
        // Save signature data
        document.getElementById('signature-data').value = canvas.toDataURL();
    }
}

function clearSignature() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    document.getElementById('signature-data').value = '';
    hasSignature = false;
    checkFormComplete();
}

// Answer selection
function selectAnswer(questionId, answer, button) {
    // Update answer
    answers[questionId] = answer;
    document.getElementById(questionId).value = answer;

    // Update button states
    const card = button.closest('.question-card');
    card.querySelectorAll('.answer-btn').forEach(btn => btn.classList.remove('active'));
    button.classList.add('active');

    // Check for yes answers
    hasYesAnswer = Object.values(answers).some(a => a === 'yes');
    document.getElementById('clearance-warning').style.display = hasYesAnswer ? 'block' : 'none';

    // Update progress
    updateProgress();
    checkFormComplete();
}

function updateProgress() {
    const answered = Object.keys(answers).length;
    const percentage = (answered / totalQuestions) * 100;

    document.getElementById('progress-text').textContent = `${answered} / ${totalQuestions}`;
    document.getElementById('progress-bar-fill').style.width = percentage + '%';
}

function checkFormComplete() {
    const allAnswered = Object.keys(answers).length === totalQuestions;
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = !(allAnswered && hasSignature);
}

// Form submission
document.getElementById('medical-form').addEventListener('submit', function(e) {
    if (!hasSignature) {
        e.preventDefault();
        alert('Please sign the form before submitting');
        return;
    }

    if (Object.keys(answers).length < totalQuestions) {
        e.preventDefault();
        alert('Please answer all questions');
        return;
    }
});
</script>

</body>
</html>
