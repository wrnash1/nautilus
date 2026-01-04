<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-upload"></i> Upload Document</h2>
        <p class="text-muted mb-0">For:
            <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>
        </p>
    </div>
    <a href="/store/staff/documents/<?= $employee['id'] ?>" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                    <div class="mb-4">
                        <label for="document_type" class="form-label">Document Type *</label>
                        <select class="form-select" id="document_type" name="document_type" required>
                            <option value="">Select document type...</option>
                            <?php foreach ($documentTypes as $value => $label): ?>
                                <option value="<?= $value ?>">
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="tax_year" class="form-label">Tax Year</label>
                        <select class="form-select" id="tax_year" name="tax_year">
                            <?php for ($year = date('Y'); $year >= date('Y') - 5; $year--): ?>
                                <option value="<?= $year ?>">
                                    <?= $year ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="document" class="form-label">Document File *</label>
                        <input type="file" class="form-control" id="document" name="document"
                            accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted">Accepted formats: PDF, JPG, PNG (max 10MB)</small>
                    </div>

                    <div class="mb-4" id="ssnField" style="display: none;">
                        <label for="ssn" class="form-label">Social Security Number</label>
                        <input type="text" class="form-control" id="ssn" name="ssn" placeholder="XXX-XX-XXXX"
                            maxlength="11">
                        <small class="text-muted">This will be encrypted and stored securely</small>
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"
                            placeholder="Optional notes about this document..."></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Upload Document
                        </button>
                        <a href="/store/staff/documents/<?= $employee['id'] ?>" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Document Guidelines</h5>
            </div>
            <div class="card-body">
                <h6>W-4 Form</h6>
                <p class="text-muted small">Complete by all new employees. Updated when withholding changes are needed.
                </p>

                <h6>I-9 Form</h6>
                <p class="text-muted small">Must be completed within 3 business days of hire. Requires List A or List B
                    + C documents.</p>

                <h6>W-2 Form</h6>
                <p class="text-muted small">Employer provides to employee by January 31. Shows annual wages and taxes
                    withheld.</p>

                <h6>1099-NEC</h6>
                <p class="text-muted small">For independent contractors paid $600+ during the year.</p>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Security</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-0">
                    All documents are stored securely. SSN is encrypted using AES-256 encryption.
                    Access is logged and audited.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('document_type').addEventListener('change', function () {
        const ssnField = document.getElementById('ssnField');
        const ssnTypes = ['w4', 'i9', 'w2', '1099'];
        ssnField.style.display = ssnTypes.includes(this.value) ? 'block' : 'none';
    });

    // Format SSN input
    document.getElementById('ssn').addEventListener('input', function () {
        let value = this.value.replace(/\D/g, '');
        if (value.length >= 5) {
            value = value.slice(0, 3) + '-' + value.slice(3, 5) + '-' + value.slice(5, 9);
        } else if (value.length >= 3) {
            value = value.slice(0, 3) + '-' + value.slice(3);
        }
        this.value = value;
    });
</script>