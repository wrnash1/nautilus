<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-folder2-open"></i> Employee Documents</h2>
    <a href="/store/staff" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Staff
    </a>
</div>

<?php if (!empty($expiringDocs)): ?>
    <div class="alert alert-warning">
        <h6><i class="bi bi-exclamation-triangle"></i> Documents Expiring Soon</h6>
        <ul class="mb-0">
            <?php foreach ($expiringDocs as $doc): ?>
                <li>
                    <strong>
                        <?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?>
                    </strong> -
                    <?= strtoupper($doc['document_type']) ?> expires
                    <?= date('M j, Y', strtotime($doc['expires_at'])) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!empty($pendingDocs)): ?>
    <div class="alert alert-info">
        <h6><i class="bi bi-clock"></i>
            <?= count($pendingDocs) ?> Documents Pending Verification
        </h6>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="mb-0">
                    <?= count($employees) ?>
                </h3>
                <small class="text-muted">Total Employees</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="mb-0 text-warning">
                    <?= count($pendingDocs) ?>
                </h3>
                <small class="text-muted">Pending Documents</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="mb-0 text-danger">
                    <?= count($expiringDocs) ?>
                </h3>
                <small class="text-muted">Expiring Soon</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="mb-0">
                    <?= date('Y') ?>
                </h3>
                <small class="text-muted">Tax Year</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Employee Document Status</h5>
        <div class="input-group" style="max-width: 300px;">
            <input type="text" class="form-control" placeholder="Search employees..." id="employeeSearch">
            <button class="btn btn-outline-secondary" type="button">
                <i class="bi bi-search"></i>
            </button>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>W-4</th>
                    <th>I-9</th>
                    <th>W-2/1099</th>
                    <th>Direct Deposit</th>
                    <th>Total Docs</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td>
                            <strong>
                                <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                            </strong>
                            <br><small class="text-muted">
                                <?= htmlspecialchars($emp['email']) ?>
                            </small>
                        </td>
                        <td><span class="badge bg-secondary"><i class="bi bi-question"></i></span></td>
                        <td><span class="badge bg-secondary"><i class="bi bi-question"></i></span></td>
                        <td><span class="badge bg-secondary"><i class="bi bi-question"></i></span></td>
                        <td><span class="badge bg-secondary"><i class="bi bi-question"></i></span></td>
                        <td>
                            <?= $emp['doc_count'] ?? 0 ?>
                        </td>
                        <td>
                            <a href="/store/staff/documents/<?= $emp['id'] ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-folder2-open"></i> View
                            </a>
                            <a href="/store/staff/documents/<?= $emp['id'] ?>/upload" class="btn btn-sm btn-success">
                                <i class="bi bi-upload"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> IRS Form Reference</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td><strong>W-4</strong></td>
                        <td>Employee's Withholding Certificate (new hire)</td>
                    </tr>
                    <tr>
                        <td><strong>I-9</strong></td>
                        <td>Employment Eligibility Verification (within 3 days of hire)</td>
                    </tr>
                    <tr>
                        <td><strong>W-2</strong></td>
                        <td>Wage and Tax Statement (annually by Jan 31)</td>
                    </tr>
                    <tr>
                        <td><strong>W-9</strong></td>
                        <td>Request for TIN (contractors only)</td>
                    </tr>
                    <tr>
                        <td><strong>1099-NEC</strong></td>
                        <td>Nonemployee Compensation (contractors, by Jan 31)</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calendar"></i> Important Deadlines</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li><i class="bi bi-calendar-event text-primary"></i> <strong>Jan 31:</strong> W-2s and 1099s to
                        employees/contractors</li>
                    <li><i class="bi bi-calendar-event text-primary"></i> <strong>Jan 31:</strong> W-2s and 1099s to
                        IRS/SSA</li>
                    <li><i class="bi bi-calendar-event text-primary"></i> <strong>Within 3 days:</strong> I-9 for new
                        hires</li>
                    <li><i class="bi bi-calendar-event text-primary"></i> <strong>Quarterly:</strong> Form 941 payroll
                        tax</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('employeeSearch').addEventListener('input', function () {
        const query = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
</script>