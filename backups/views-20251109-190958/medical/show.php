<?php
/**
 * PADI Medical Form Review (Staff View)
 * Shows completed medical form with all answers
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
        .medical-review-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        .answer-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
        }
        .answer-yes {
            background: #f8d7da;
            color: #721c24;
        }
        .answer-no {
            background: #d4edda;
            color: #155724;
        }
        .clearance-status {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .clearance-required {
            background: #fff3cd;
            border: 2px solid #ffc107;
        }
        .clearance-obtained {
            background: #d4edda;
            border: 2px solid #28a745;
        }
        .signature-image {
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            background: white;
            max-width: 400px;
        }
        .question-row {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .question-row:last-child {
            border-bottom: none;
        }
        .question-row:hover {
            background: #f8f9fa;
        }
    </style>
</head>
<body class="bg-light">

<div class="medical-review-container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>
                <i class="bi bi-clipboard2-pulse text-primary"></i>
                Medical Form Review
            </h2>
            <p class="text-muted mb-0">
                Customer: <strong><?= htmlspecialchars($form['customer_name']) ?></strong>
            </p>
        </div>
        <a href="/store/customers/<?= $form['customer_id'] ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <!-- Summary Card -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-2">
                        <strong>Submitted:</strong>
                        <?= date('F j, Y g:i A', strtotime($form['submitted_at'])) ?>
                    </p>
                    <p class="mb-2">
                        <strong>Submitted By:</strong>
                        <?= htmlspecialchars($form['submitted_by'] ?? 'Customer') ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2">
                        <strong>Expires:</strong>
                        <?= date('F j, Y', strtotime($form['expiry_date'])) ?>
                    </p>
                    <p class="mb-2">
                        <strong>Status:</strong>
                        <?php if (strtotime($form['expiry_date']) > time()): ?>
                            <span class="badge bg-success">Valid</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Expired</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Clearance Status -->
    <?php if ($form['requires_physician_clearance']): ?>
    <div class="clearance-status <?= $form['physician_clearance_obtained'] ? 'clearance-obtained' : 'clearance-required' ?>">
        <h5>
            <i class="bi bi-<?= $form['physician_clearance_obtained'] ? 'check-circle-fill' : 'exclamation-triangle-fill' ?>"></i>
            Physician Clearance <?= $form['physician_clearance_obtained'] ? 'Obtained' : 'Required' ?>
        </h5>
        <?php if ($form['physician_clearance_obtained']): ?>
            <p class="mb-2">
                <strong>Clearance Date:</strong>
                <?= date('F j, Y', strtotime($form['physician_clearance_date'])) ?>
            </p>
            <?php if ($form['physician_clearance_file']): ?>
            <a href="/uploads/medical_clearances/<?= htmlspecialchars($form['physician_clearance_file']) ?>"
               target="_blank" class="btn btn-sm btn-success">
                <i class="bi bi-file-earmark-medical"></i>
                View Clearance Document
            </a>
            <?php endif; ?>
        <?php else: ?>
            <p class="mb-0">
                Customer answered "Yes" to one or more questions and must obtain physician clearance before diving.
            </p>
            <button class="btn btn-sm btn-warning mt-2" onclick="document.getElementById('upload-clearance-modal').style.display='block'">
                <i class="bi bi-upload"></i>
                Upload Clearance
            </button>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Questions & Answers -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="bi bi-list-check"></i>
                Medical Questions (34)
            </h5>
        </div>
        <div class="card-body p-0">
            <?php foreach ($questions as $index => $question):
                $questionId = $question['id'];
                $answer = $form[$questionId] ?? 'no';
            ?>
            <div class="question-row">
                <div class="flex-grow-1">
                    <strong><?= $index + 1 ?>.</strong>
                    <?= htmlspecialchars($question['text']) ?>
                </div>
                <span class="answer-badge answer-<?= $answer ?>">
                    <?= strtoupper($answer) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Signature -->
    <?php if ($form['participant_signature_data']): ?>
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-pencil-square"></i>
                Participant Signature
            </h5>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Signed on: <?= date('F j, Y', strtotime($form['participant_signature_date'])) ?>
            </p>
            <img src="<?= htmlspecialchars($form['participant_signature_data']) ?>"
                 alt="Participant Signature"
                 class="signature-image">
        </div>
    </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="mt-4">
        <a href="/store/customers/<?= $form['customer_id'] ?>" class="btn btn-primary">
            <i class="bi bi-person"></i>
            View Customer Profile
        </a>
        <button class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer"></i>
            Print Form
        </button>
    </div>
</div>

<!-- Upload Clearance Modal -->
<?php if ($form['requires_physician_clearance'] && !$form['physician_clearance_obtained']): ?>
<div id="upload-clearance-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 500px;">
        <h5 class="mb-3">Upload Physician Clearance</h5>
        <form id="clearance-upload-form" enctype="multipart/form-data">
            <input type="hidden" name="form_id" value="<?= $form['id'] ?>">
            <div class="mb-3">
                <label class="form-label">Clearance Document</label>
                <input type="file" class="form-control" name="clearance_file" required
                       accept=".pdf,.jpg,.jpeg,.png">
                <small class="text-muted">PDF, JPG, or PNG (Max 10MB)</small>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-upload"></i> Upload
                </button>
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('upload-clearance-modal').style.display='none'">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('clearance-upload-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const response = await fetch('/medical/upload-clearance', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            alert('Clearance uploaded successfully!');
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error uploading file: ' + error.message);
    }
});
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
