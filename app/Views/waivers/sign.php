<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($waiverData['title']) ?> - Nautilus Dive Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .signature-pad {
            border: 2px solid #dee2e6;
            border-radius: 4px;
            background: #fff;
        }
        .waiver-content {
            max-height: 400px;
            overflow-y: auto;
            border: 1px solid #dee2e6;
            padding: 1rem;
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0"><?= htmlspecialchars($waiverData['title']) ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Dear <?= htmlspecialchars($waiverData['first_name']) ?>,</strong>
                            Please read the following waiver carefully and sign below to proceed with your service.
                        </div>

                        <form id="waiverForm">
                            <!-- Personal Information -->
                            <h5 class="mt-4">Personal Information</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="customer_name"
                                           value="<?= htmlspecialchars($waiverData['first_name'] . ' ' . $waiverData['last_name']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="customer_email"
                                           value="<?= htmlspecialchars($waiverData['customer_email']) ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" name="customer_phone" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" class="form-control" name="customer_dob">
                                </div>
                            </div>

                            <?php if ($waiverData['requires_emergency_contact']): ?>
                            <!-- Emergency Contact -->
                            <h5 class="mt-4">Emergency Contact</h5>
                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">Emergency Contact Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="emergency_contact_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Emergency Contact Phone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" name="emergency_contact_phone" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Relationship <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="emergency_contact_relationship"
                                           placeholder="e.g., Spouse, Parent, Sibling" required>
                                </div>
                            </div>
                            <?php endif; ?>

                            <?php if ($waiverData['requires_medical_info']): ?>
                            <!-- Medical Information -->
                            <h5 class="mt-4">Medical Information</h5>
                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="has_medical_conditions" id="hasMedical">
                                    <label class="form-check-label" for="hasMedical">
                                        I have medical conditions, take medications, or have allergies
                                    </label>
                                </div>

                                <div id="medicalFields" style="display: none;" class="mt-3">
                                    <div class="mb-3">
                                        <label class="form-label">Medical Conditions</label>
                                        <textarea class="form-control" name="medical_conditions" rows="2"
                                                  placeholder="List any medical conditions"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Medications</label>
                                        <textarea class="form-control" name="medications" rows="2"
                                                  placeholder="List any medications you're currently taking"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Allergies</label>
                                        <textarea class="form-control" name="allergies" rows="2"
                                                  placeholder="List any allergies"></textarea>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Waiver Content -->
                            <h5 class="mt-4">Agreement</h5>
                            <div class="waiver-content mb-4">
                                <?= nl2br(htmlspecialchars($waiverData['content'])) ?>
                            </div>

                            <h5 class="mt-4">Legal Terms</h5>
                            <div class="waiver-content mb-4">
                                <?= nl2br(htmlspecialchars($waiverData['legal_text'])) ?>
                            </div>

                            <!-- Agreement Checkbox -->
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="agreeTerms" required>
                                <label class="form-check-label" for="agreeTerms">
                                    <strong>I have read, understand, and agree to all terms and conditions above</strong>
                                </label>
                            </div>

                            <!-- Signature -->
                            <h5>Signature <span class="text-danger">*</span></h5>
                            <p class="text-muted small">Please sign in the box below using your mouse or finger (on touch devices)</p>
                            <div class="mb-3">
                                <canvas id="signaturePad" class="signature-pad" width="700" height="200"></canvas>
                                <input type="hidden" name="signature_data" id="signatureData">
                            </div>
                            <div class="mb-4">
                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSignature">
                                    <i class="bi bi-arrow-counterclockwise"></i> Clear Signature
                                </button>
                            </div>

                            <!-- Submit -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                    <i class="bi bi-pen"></i> Sign and Submit Waiver
                                </button>
                            </div>
                        </form>

                        <div id="successMessage" class="alert alert-success mt-4" style="display: none;">
                            <h5><i class="bi bi-check-circle"></i> Waiver Signed Successfully!</h5>
                            <p class="mb-0">Thank you for signing the waiver. A confirmation has been sent to your email.</p>
                        </div>

                        <div id="errorMessage" class="alert alert-danger mt-4" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script>
        // Signature Pad
        const canvas = document.getElementById('signaturePad');
        const signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)',
            penColor: 'rgb(0, 0, 0)'
        });

        // Clear signature
        document.getElementById('clearSignature').addEventListener('click', () => {
            signaturePad.clear();
        });

        // Medical conditions toggle
        const hasMedical = document.getElementById('hasMedical');
        const medicalFields = document.getElementById('medicalFields');
        if (hasMedical) {
            hasMedical.addEventListener('change', (e) => {
                medicalFields.style.display = e.target.checked ? 'block' : 'none';
            });
        }

        // Form submission
        document.getElementById('waiverForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            if (signaturePad.isEmpty()) {
                alert('Please provide your signature');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Submitting...';

            // Get signature as base64
            const signatureData = signaturePad.toDataURL();
            document.getElementById('signatureData').value = signatureData;

            // Collect form data
            const formData = new FormData(e.target);

            try {
                const response = await fetch('/waivers/sign/<?= $token ?>', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    document.getElementById('waiverForm').style.display = 'none';
                    document.getElementById('successMessage').style.display = 'block';
                } else {
                    document.getElementById('errorMessage').textContent = result.error || 'An error occurred';
                    document.getElementById('errorMessage').style.display = 'block';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-pen"></i> Sign and Submit Waiver';
                }
            } catch (error) {
                document.getElementById('errorMessage').textContent = 'An error occurred. Please try again.';
                document.getElementById('errorMessage').style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-pen"></i> Sign and Submit Waiver';
            }
        });

        // Responsive canvas
        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const canvasWidth = canvas.offsetWidth;
            canvas.width = canvasWidth * ratio;
            canvas.height = 200 * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            signaturePad.clear();
        }

        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();
    </script>
</body>
</html>
