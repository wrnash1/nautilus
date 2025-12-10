<?php
/**
 * Feedback Submission Form
 * Accessible to all users (logged in or not)
 */
$pageTitle = 'Submit Feedback';
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
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }

        .feedback-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .feedback-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
        }

        .feedback-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .feedback-header h1 {
            color: #667eea;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .feedback-header p {
            color: #6c757d;
            font-size: 1.1rem;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .ticket-type-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }

        .ticket-type-option {
            position: relative;
        }

        .ticket-type-option input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .ticket-type-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .ticket-type-option input:checked + .ticket-type-label {
            border-color: #667eea;
            background: #f0f3ff;
        }

        .ticket-type-label i {
            font-size: 2rem;
            margin-bottom: 8px;
        }

        .ticket-type-label span {
            font-size: 0.9rem;
            font-weight: 600;
        }

        .severity-badges .badge {
            cursor: pointer;
            padding: 12px 20px;
            font-size: 1rem;
            margin-right: 8px;
            margin-bottom: 8px;
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .severity-badges input[type="radio"] {
            display: none;
        }

        .severity-badges input:checked + .badge {
            border-color: currentColor;
            transform: scale(1.05);
        }

        .screenshot-upload {
            border: 2px dashed #e0e0e0;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .screenshot-upload:hover {
            border-color: #667eea;
            background: #f0f3ff;
        }

        .screenshot-upload i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 12px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 14px 32px;
            font-size: 1.1rem;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .context-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 16px;
            font-size: 0.9rem;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .feedback-card {
                padding: 24px;
            }

            .feedback-header h1 {
                font-size: 2rem;
            }

            .ticket-type-selector {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="feedback-container">
        <div class="feedback-card">
            <!-- Header -->
            <div class="feedback-header">
                <h1><i class="bi bi-chat-left-text-fill"></i> Submit Feedback</h1>
                <p>Help us improve Nautilus! Report bugs, request features, or ask questions.</p>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible">
                <?= htmlspecialchars($_SESSION['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); endif; ?>

            <form method="POST" action="/feedback/store" enctype="multipart/form-data" id="feedbackForm">
                <!-- Ticket Type -->
                <div class="mb-4">
                    <label class="form-label">What type of feedback is this? *</label>
                    <div class="ticket-type-selector">
                        <div class="ticket-type-option">
                            <input type="radio" name="ticket_type" value="bug" id="type_bug" required checked>
                            <label for="type_bug" class="ticket-type-label">
                                <i class="bi bi-bug-fill text-danger"></i>
                                <span>Bug Report</span>
                            </label>
                        </div>
                        <div class="ticket-type-option">
                            <input type="radio" name="ticket_type" value="feature_request" id="type_feature">
                            <label for="type_feature" class="ticket-type-label">
                                <i class="bi bi-lightbulb-fill text-warning"></i>
                                <span>Feature Request</span>
                            </label>
                        </div>
                        <div class="ticket-type-option">
                            <input type="radio" name="ticket_type" value="improvement" id="type_improvement">
                            <label for="type_improvement" class="ticket-type-label">
                                <i class="bi bi-arrow-up-circle-fill text-success"></i>
                                <span>Improvement</span>
                            </label>
                        </div>
                        <div class="ticket-type-option">
                            <input type="radio" name="ticket_type" value="question" id="type_question">
                            <label for="type_question" class="ticket-type-label">
                                <i class="bi bi-question-circle-fill text-info"></i>
                                <span>Question</span>
                            </label>
                        </div>
                        <div class="ticket-type-option">
                            <input type="radio" name="ticket_type" value="documentation" id="type_docs">
                            <label for="type_docs" class="ticket-type-label">
                                <i class="bi bi-file-text-fill text-primary"></i>
                                <span>Documentation</span>
                            </label>
                        </div>
                        <div class="ticket-type-option">
                            <input type="radio" name="ticket_type" value="other" id="type_other">
                            <label for="type_other" class="ticket-type-label">
                                <i class="bi bi-three-dots text-secondary"></i>
                                <span>Other</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Title -->
                <div class="mb-4">
                    <label for="title" class="form-label">Summary (one sentence) *</label>
                    <input type="text" class="form-control" id="title" name="title" required
                           placeholder="e.g., Skills checkoff not saving on iPad">
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="description" class="form-label">Detailed Description *</label>
                    <textarea class="form-control" id="description" name="description" rows="6" required
                              placeholder="Please provide as much detail as possible. What happened? What were you trying to do?"></textarea>
                </div>

                <!-- Severity (for bugs) -->
                <div class="mb-4" id="severitySection">
                    <label class="form-label">How severe is this issue?</label>
                    <div class="severity-badges">
                        <input type="radio" name="severity" value="critical" id="sev_critical">
                        <label for="sev_critical" class="badge bg-danger">Critical - System Down</label>

                        <input type="radio" name="severity" value="high" id="sev_high">
                        <label for="sev_high" class="badge bg-warning text-dark">High - Major Impact</label>

                        <input type="radio" name="severity" value="medium" id="sev_medium" checked>
                        <label for="sev_medium" class="badge bg-info">Medium - Some Impact</label>

                        <input type="radio" name="severity" value="low" id="sev_low">
                        <label for="sev_low" class="badge bg-secondary">Low - Minor Issue</label>
                    </div>
                </div>

                <!-- Steps to Reproduce (for bugs) -->
                <div class="mb-4" id="stepsSection">
                    <label for="steps" class="form-label">Steps to Reproduce (for bugs)</label>
                    <textarea class="form-control" id="steps" name="steps_to_reproduce" rows="4"
                              placeholder="1. Go to...&#10;2. Click on...&#10;3. See error..."></textarea>
                </div>

                <!-- Expected vs Actual Behavior (for bugs) -->
                <div class="row mb-4" id="behaviorSection">
                    <div class="col-md-6">
                        <label for="expected" class="form-label">Expected Behavior</label>
                        <textarea class="form-control" id="expected" name="expected_behavior" rows="3"
                                  placeholder="What should happen?"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="actual" class="form-label">Actual Behavior</label>
                        <textarea class="form-control" id="actual" name="actual_behavior" rows="3"
                                  placeholder="What actually happens?"></textarea>
                    </div>
                </div>

                <!-- Error Logs -->
                <div class="mb-4">
                    <label for="error_logs" class="form-label">Error Messages (if any)</label>
                    <textarea class="form-control" id="error_logs" name="error_logs" rows="3"
                              placeholder="Paste any error messages you saw here..."></textarea>
                </div>

                <!-- Screenshots -->
                <div class="mb-4">
                    <label class="form-label">Screenshots (optional)</label>
                    <div class="screenshot-upload" onclick="document.getElementById('screenshots').click()">
                        <i class="bi bi-image"></i>
                        <p class="mb-0"><strong>Click to upload screenshots</strong></p>
                        <small class="text-muted">PNG, JPG, or GIF (max 5MB each)</small>
                    </div>
                    <input type="file" id="screenshots" name="screenshots[]" accept="image/*" multiple style="display:none">
                    <div id="screenshotPreview" class="mt-2"></div>
                </div>

                <!-- Your Information -->
                <hr class="my-4">
                <h5 class="mb-3"><i class="bi bi-person-fill"></i> Your Information</h5>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Your Name *</label>
                        <input type="text" class="form-control" id="name" name="submitter_name" required
                               value="<?= htmlspecialchars($user['first_name'] ?? '') ?> <?= htmlspecialchars($user['last_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Your Email *</label>
                        <input type="email" class="form-control" id="email" name="submitter_email" required
                               value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                               placeholder="For follow-up updates">
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="phone" class="form-label">Phone Number (optional)</label>
                        <input type="tel" class="form-control" id="phone" name="submitter_phone"
                               value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="dive_shop" class="form-label">Dive Shop Name (optional)</label>
                        <input type="text" class="form-control" id="dive_shop" name="dive_shop_name"
                               placeholder="Your dive shop name">
                    </div>
                </div>

                <!-- System Context (auto-filled, collapsible) -->
                <div class="accordion mb-4" id="contextAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#contextInfo">
                                <i class="bi bi-info-circle me-2"></i> System Information (automatically captured)
                            </button>
                        </h2>
                        <div id="contextInfo" class="accordion-collapse collapse">
                            <div class="accordion-body context-info">
                                <input type="hidden" name="page_url" value="<?= htmlspecialchars($pageUrl) ?>">
                                <input type="hidden" name="browser" value="<?= htmlspecialchars($browser) ?>">
                                <input type="hidden" name="operating_system" value="<?= htmlspecialchars($os) ?>">
                                <input type="hidden" name="nautilus_version" value="<?= htmlspecialchars($nautilus_version) ?>">
                                <input type="hidden" name="php_version" value="<?= htmlspecialchars($php_version) ?>">
                                <input type="hidden" name="mysql_version" value="<?= htmlspecialchars($mysql_version) ?>">
                                <input type="hidden" name="screen_resolution" id="screenResolution">

                                <p><strong>Page URL:</strong> <?= htmlspecialchars($pageUrl) ?: 'N/A' ?></p>
                                <p><strong>Browser:</strong> <?= htmlspecialchars($browser) ?></p>
                                <p><strong>Operating System:</strong> <?= htmlspecialchars($os) ?></p>
                                <p><strong>Nautilus Version:</strong> <?= htmlspecialchars($nautilus_version) ?></p>
                                <p><strong>PHP Version:</strong> <?= htmlspecialchars($php_version) ?></p>
                                <p><strong>MySQL Version:</strong> <?= htmlspecialchars($mysql_version) ?></p>
                                <p class="mb-0"><strong>Screen Resolution:</strong> <span id="resolutionDisplay"></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-submit">
                        <i class="bi bi-send-fill"></i> Submit Feedback
                    </button>
                    <a href="<?= $_SERVER['HTTP_REFERER'] ?? '/' ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Capture screen resolution
        const resolution = `${screen.width}x${screen.height}`;
        document.getElementById('screenResolution').value = resolution;
        document.getElementById('resolutionDisplay').textContent = resolution;

        // Show/hide sections based on ticket type
        const ticketTypeInputs = document.querySelectorAll('input[name="ticket_type"]');
        const severitySection = document.getElementById('severitySection');
        const stepsSection = document.getElementById('stepsSection');
        const behaviorSection = document.getElementById('behaviorSection');

        ticketTypeInputs.forEach(input => {
            input.addEventListener('change', function() {
                const isBug = this.value === 'bug';
                severitySection.style.display = isBug ? 'block' : 'none';
                stepsSection.style.display = isBug ? 'block' : 'none';
                behaviorSection.style.display = isBug ? 'flex' : 'none';
            });
        });

        // Screenshot preview
        document.getElementById('screenshots').addEventListener('change', function(e) {
            const preview = document.getElementById('screenshotPreview');
            preview.innerHTML = '';

            Array.from(e.target.files).forEach(file => {
                const div = document.createElement('div');
                div.className = 'badge bg-primary me-2 mb-2';
                div.innerHTML = `<i class="bi bi-image"></i> ${file.name}`;
                preview.appendChild(div);
            });
        });

        // Form validation
        document.getElementById('feedbackForm').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();

            if (!title || !description || !name || !email) {
                e.preventDefault();
                alert('Please fill in all required fields (marked with *)');
            }
        });
    </script>
</body>
</html>
