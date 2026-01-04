<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-person-badge"></i>
            <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>
        </h2>
        <p class="text-muted mb-0">
            <?= htmlspecialchars($employee['email']) ?>
        </p>
    </div>
    <div>
        <a href="/store/staff/documents" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> All Employees
        </a>
        <a href="/store/staff/documents/<?= $employee['id'] ?>/upload" class="btn btn-success">
            <i class="bi bi-upload"></i> Upload Document
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="mb-0">
                    <?= count($documents) ?>
                </h3>
                <small class="text-muted">Total Documents</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="mb-0">$
                    <?= number_format($payrollSummary['ytd_gross'] ?? 0, 2) ?>
                </h3>
                <small class="text-muted">YTD Gross Pay</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="mb-0">$
                    <?= number_format($payrollSummary['ytd_federal'] ?? 0, 2) ?>
                </h3>
                <small class="text-muted">YTD Federal Tax</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="mb-0">
                    <?= $payrollSummary['pay_periods'] ?? 0 ?>
                </h3>
                <small class="text-muted">Pay Periods</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Documents on File</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Tax Year</th>
                            <th>Status</th>
                            <th>Upload Date</th>
                            <th>Expires</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($documents)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-folder-x" style="font-size: 2rem;"></i>
                                    <p class="mb-0">No documents uploaded yet</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <?= strtoupper($doc['document_type']) ?>
                                        </strong>
                                        <?php if ($doc['ssn_last_four']): ?>
                                            <br><small class="text-muted">SSN: ***-**-
                                                <?= $doc['ssn_last_four'] ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $doc['tax_year'] ?: '-' ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'submitted' => 'info',
                                            'verified' => 'success',
                                            'rejected' => 'danger',
                                            'expired' => 'secondary'
                                        ];
                                        $color = $statusColors[$doc['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $color ?>">
                                            <?= ucfirst($doc['status']) ?>
                                        </span>
                                        <?php if ($doc['verified_by_name']): ?>
                                            <br><small class="text-muted">by
                                                <?= htmlspecialchars($doc['verified_by_name']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('M j, Y', strtotime($doc['created_at'])) ?>
                                    </td>
                                    <td>
                                        <?php if ($doc['expires_at']): ?>
                                            <?php
                                            $expires = strtotime($doc['expires_at']);
                                            $isExpiring = $expires < strtotime('+30 days');
                                            ?>
                                            <span class="<?= $isExpiring ? 'text-danger fw-bold' : '' ?>">
                                                <?= date('M j, Y', $expires) ?>
                                            </span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="/store/staff/documents/download/<?= $doc['id'] ?>"
                                            class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <?php if ($doc['status'] === 'submitted'): ?>
                                            <form method="POST" action="/store/staff/documents/verify/<?= $doc['id'] ?>"
                                                style="display:inline;">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="bi bi-check-circle"></i> Verify
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Tax Information</h5>
                <a href="/store/staff/documents/<?= $employee['id'] ?>/tax-info" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            </div>
            <div class="card-body">
                <?php if ($taxInfo): ?>
                    <table class="table table-sm mb-0">
                        <tr>
                            <td>Filing Status</td>
                            <td><strong>
                                    <?= ucwords(str_replace('_', ' ', $taxInfo['filing_status'] ?? 'Not set')) ?>
                                </strong></td>
                        </tr>
                        <tr>
                            <td>Fed. Allowances</td>
                            <td><strong>
                                    <?= $taxInfo['federal_allowances'] ?? 0 ?>
                                </strong></td>
                        </tr>
                        <tr>
                            <td>State Allowances</td>
                            <td><strong>
                                    <?= $taxInfo['state_allowances'] ?? 0 ?>
                                </strong></td>
                        </tr>
                        <tr>
                            <td>Additional W/H</td>
                            <td><strong>$
                                    <?= number_format($taxInfo['additional_withholding'] ?? 0, 2) ?>
                                </strong></td>
                        </tr>
                        <tr>
                            <td>Worker Type</td>
                            <td><strong>
                                    <?= ($taxInfo['is_contractor'] ?? 0) ? '1099 Contractor' : 'W-2 Employee' ?>
                                </strong></td>
                        </tr>
                    </table>
                <?php else: ?>
                    <p class="text-muted mb-0">No tax information on file. <a
                            href="/store/staff/documents/<?= $employee['id'] ?>/tax-info">Add now</a></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="/store/staff/documents/<?= $employee['id'] ?>/upload"
                    class="list-group-item list-group-item-action">
                    <i class="bi bi-upload"></i> Upload Document
                </a>
                <a href="/store/staff/documents/<?= $employee['id'] ?>/tax-info"
                    class="list-group-item list-group-item-action">
                    <i class="bi bi-calculator"></i> Update Tax Info
                </a>
                <a href="/store/staff/documents/<?= $employee['id'] ?>/w2/<?= date('Y') - 1 ?>"
                    class="list-group-item list-group-item-action">
                    <i class="bi bi-file-earmark-text"></i> Generate W-2 (
                    <?= date('Y') - 1 ?>)
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Required Documents</h5>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item d-flex justify-content-between">
                    W-4 Form
                    <span class="badge bg-secondary"><i class="bi bi-question"></i></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    I-9 Form
                    <span class="badge bg-secondary"><i class="bi bi-question"></i></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    Direct Deposit
                    <span class="badge bg-secondary"><i class="bi bi-question"></i></span>
                </li>
            </ul>
        </div>
    </div>
</div>