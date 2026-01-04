<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2><i class="bi bi-calculator"></i> Tax Information</h2>
        <p class="text-muted mb-0">
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
            <div class="card-header">
                <h5 class="mb-0">Tax Year
                    <?= $taxYear ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="tax_year" value="<?= $taxYear ?>">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="filing_status" class="form-label">Filing Status</label>
                                <select class="form-select" id="filing_status" name="filing_status">
                                    <option value="">Not specified</option>
                                    <option value="single" <?= ($taxInfo['filing_status'] ?? '') === 'single' ? 'selected' : '' ?>>Single</option>
                                    <option value="married_filing_jointly" <?= ($taxInfo['filing_status'] ?? '') === 'married_filing_jointly' ? 'selected' : '' ?>>Married Filing Jointly
                                    </option>
                                    <option value="married_filing_separately" <?= ($taxInfo['filing_status'] ?? '') === 'married_filing_separately' ? 'selected' : '' ?>>Married Filing Separately
                                    </option>
                                    <option value="head_of_household" <?= ($taxInfo['filing_status'] ?? '') === 'head_of_household' ? 'selected' : '' ?>>Head of Household</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check mt-4">
                                    <input class="form-check-input" type="checkbox" id="is_contractor"
                                        name="is_contractor" value="1" <?= ($taxInfo['is_contractor'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="is_contractor">
                                        <strong>1099 Contractor</strong> (not W-2 employee)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="federal_allowances" class="form-label">Federal Allowances</label>
                                <input type="number" class="form-control" id="federal_allowances"
                                    name="federal_allowances" value="<?= $taxInfo['federal_allowances'] ?? 0 ?>" min="0"
                                    max="99">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="state_allowances" class="form-label">State Allowances</label>
                                <input type="number" class="form-control" id="state_allowances" name="state_allowances"
                                    value="<?= $taxInfo['state_allowances'] ?? 0 ?>" min="0" max="99">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="additional_withholding" class="form-label">Additional Withholding
                                    ($)</label>
                                <input type="number" class="form-control" id="additional_withholding"
                                    name="additional_withholding" value="<?= $taxInfo['additional_withholding'] ?? 0 ?>"
                                    min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="exempt_from_withholding"
                                name="exempt_from_withholding" value="1" <?= ($taxInfo['exempt_from_withholding'] ?? 0) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="exempt_from_withholding">
                                Exempt from withholding
                            </label>
                            <small class="d-block text-muted">Only if they had no tax liability last year and expect
                                none this year</small>
                        </div>
                    </div>

                    <hr>

                    <h5 class="mb-3">Compensation</h5>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="hourly_rate" class="form-label">Hourly Rate ($)</label>
                                <input type="number" class="form-control" id="hourly_rate" name="hourly_rate"
                                    value="<?= $taxInfo['hourly_rate'] ?? '' ?>" min="0" step="0.01"
                                    placeholder="Leave blank if salaried">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="salary_annual" class="form-label">Annual Salary ($)</label>
                                <input type="number" class="form-control" id="salary_annual" name="salary_annual"
                                    value="<?= $taxInfo['salary_annual'] ?? '' ?>" min="0" step="0.01"
                                    placeholder="Leave blank if hourly">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Save Tax Information
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
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Current Year Summary</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td>YTD Wages</td>
                        <td class="text-end"><strong>$
                                <?= number_format($taxInfo['ytd_wages'] ?? 0, 2) ?>
                            </strong></td>
                    </tr>
                    <tr>
                        <td>Federal Tax</td>
                        <td class="text-end">$
                            <?= number_format($taxInfo['ytd_federal_tax'] ?? 0, 2) ?>
                        </td>
                    </tr>
                    <tr>
                        <td>State Tax</td>
                        <td class="text-end">$
                            <?= number_format($taxInfo['ytd_state_tax'] ?? 0, 2) ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Social Security</td>
                        <td class="text-end">$
                            <?= number_format($taxInfo['ytd_social_security'] ?? 0, 2) ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Medicare</td>
                        <td class="text-end">$
                            <?= number_format($taxInfo['ytd_medicare'] ?? 0, 2) ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Tax Rates (2026)</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0 small">
                    <tr>
                        <td>Social Security</td>
                        <td class="text-end">6.2%</td>
                    </tr>
                    <tr>
                        <td>Medicare</td>
                        <td class="text-end">1.45%</td>
                    </tr>
                    <tr>
                        <td>SS Wage Base</td>
                        <td class="text-end">$176,100</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // Disable salary if hourly is filled and vice versa
    document.getElementById('hourly_rate').addEventListener('input', function () {
        document.getElementById('salary_annual').disabled = this.value !== '';
    });
    document.getElementById('salary_annual').addEventListener('input', function () {
        document.getElementById('hourly_rate').disabled = this.value !== '';
    });
</script>