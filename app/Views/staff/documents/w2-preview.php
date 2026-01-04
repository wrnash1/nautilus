<style>
    @media print {
        .no-print {
            display: none !important;
        }

        body {
            margin: 0;
            padding: 20px;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }

    .w2-box {
        border: 1px solid #000;
        padding: 8px;
        margin-bottom: 0;
        min-height: 60px;
    }

    .w2-box-header {
        font-size: 10px;
        color: #666;
        margin-bottom: 4px;
    }

    .w2-box-value {
        font-size: 14px;
        font-weight: bold;
    }

    .w2-form {
        font-family: 'Courier New', monospace;
        font-size: 12px;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 no-print">
    <div>
        <h2><i class="bi bi-file-earmark-text"></i> W-2 Preview</h2>
        <p class="text-muted mb-0">Tax Year:
            <?= $taxYear ?>
        </p>
    </div>
    <div>
        <a href="/store/staff/documents/<?= $employee['id'] ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <button class="btn btn-primary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print
        </button>
        <a href="/store/staff/documents/<?= $employee['id'] ?>/w2/<?= $taxYear ?>/download" class="btn btn-success">
            <i class="bi bi-download"></i> Download PDF
        </a>
    </div>
</div>

<div class="alert alert-warning no-print">
    <i class="bi bi-exclamation-triangle"></i> <strong>Preview Only:</strong> This is a preview of W-2 data. For
    official tax filing,
    please use IRS-approved W-2 forms or payroll software.
</div>

<div class="card w2-form">
    <div class="card-body">
        <div class="text-center mb-3">
            <h4>Form W-2 Wage and Tax Statement
                <?= $taxYear ?>
            </h4>
            <small>Copy B - To Be Filed With Employee's FEDERAL Tax Return</small>
        </div>

        <div class="row g-0">
            <!-- Left Column - Employer Info -->
            <div class="col-md-6">
                <div class="w2-box" style="height: 100px;">
                    <div class="w2-box-header">a Employee's social security number</div>
                    <div class="w2-box-value">XXX-XX-
                        <?= $employee['ssn_last_four'] ?? 'XXXX' ?>
                    </div>
                </div>

                <div class="w2-box" style="height: 80px;">
                    <div class="w2-box-header">b Employer identification number (EIN)</div>
                    <div class="w2-box-value">
                        <?= $company['company_ein'] ?? 'XX-XXXXXXX' ?>
                    </div>
                </div>

                <div class="w2-box" style="height: 120px;">
                    <div class="w2-box-header">c Employer's name, address, and ZIP code</div>
                    <div class="w2-box-value">
                        <?= htmlspecialchars($company['company_name'] ?? 'Company Name') ?><br>
                        <?= htmlspecialchars($company['company_address'] ?? '123 Main St') ?><br>
                        <?= htmlspecialchars($company['company_city'] ?? 'City') ?>,
                        <?= htmlspecialchars($company['company_state'] ?? 'ST') ?>
                        <?= htmlspecialchars($company['company_zip'] ?? '00000') ?>
                    </div>
                </div>

                <div class="w2-box" style="height: 80px;">
                    <div class="w2-box-header">d Control number</div>
                    <div class="w2-box-value">
                        <?= str_pad($employee['id'], 8, '0', STR_PAD_LEFT) ?>
                    </div>
                </div>

                <div class="w2-box" style="height: 100px;">
                    <div class="w2-box-header">e Employee's first name and initial &nbsp;&nbsp;&nbsp;&nbsp; Last name
                    </div>
                    <div class="w2-box-value">
                        <?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?>
                    </div>
                </div>

                <div class="w2-box" style="height: 80px;">
                    <div class="w2-box-header">f Employee's address and ZIP code</div>
                    <div class="w2-box-value">
                        <?= htmlspecialchars($employee['address'] ?? 'Address on file') ?>
                    </div>
                </div>
            </div>

            <!-- Right Column - Wage Data -->
            <div class="col-md-6">
                <div class="row g-0">
                    <div class="col-6">
                        <div class="w2-box">
                            <div class="w2-box-header">1 Wages, tips, other compensation</div>
                            <div class="w2-box-value">$
                                <?= number_format($payroll['total_wages'] ?? 0, 2) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="w2-box">
                            <div class="w2-box-header">2 Federal income tax withheld</div>
                            <div class="w2-box-value">$
                                <?= number_format($payroll['federal_withheld'] ?? 0, 2) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-0">
                    <div class="col-6">
                        <div class="w2-box">
                            <div class="w2-box-header">3 Social security wages</div>
                            <div class="w2-box-value">$
                                <?= number_format($payroll['total_wages'] ?? 0, 2) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="w2-box">
                            <div class="w2-box-header">4 Social security tax withheld</div>
                            <div class="w2-box-value">$
                                <?= number_format($payroll['ss_withheld'] ?? 0, 2) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-0">
                    <div class="col-6">
                        <div class="w2-box">
                            <div class="w2-box-header">5 Medicare wages and tips</div>
                            <div class="w2-box-value">$
                                <?= number_format($payroll['total_wages'] ?? 0, 2) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="w2-box">
                            <div class="w2-box-header">6 Medicare tax withheld</div>
                            <div class="w2-box-value">$
                                <?= number_format($payroll['medicare_withheld'] ?? 0, 2) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-0">
                    <div class="col-6">
                        <div class="w2-box">
                            <div class="w2-box-header">7 Social security tips</div>
                            <div class="w2-box-value">$
                                <?= number_format($payroll['total_tips'] ?? 0, 2) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="w2-box">
                            <div class="w2-box-header">8 Allocated tips</div>
                            <div class="w2-box-value">$0.00</div>
                        </div>
                    </div>
                </div>

                <div class="row g-0">
                    <div class="col-6">
                        <div class="w2-box">
                            <div class="w2-box-header">9 </div>
                            <div class="w2-box-value">&nbsp;</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="w2-box">
                            <div class="w2-box-header">10 Dependent care benefits</div>
                            <div class="w2-box-value">$0.00</div>
                        </div>
                    </div>
                </div>

                <div class="row g-0">
                    <div class="col-6">
                        <div class="w2-box">
                            <div class="w2-box-header">11 Nonqualified plans</div>
                            <div class="w2-box-value">&nbsp;</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="w2-box">
                            <div class="w2-box-header">12a-d See instructions for box 12</div>
                            <div class="w2-box-value">&nbsp;</div>
                        </div>
                    </div>
                </div>

                <div class="row g-0">
                    <div class="col-3">
                        <div class="w2-box text-center">
                            <div class="w2-box-header">13</div>
                            <div class="small">
                                ☐ Statutory<br>
                                ☐ Retirement<br>
                                ☐ Third-party sick
                            </div>
                        </div>
                    </div>
                    <div class="col-9">
                        <div class="w2-box">
                            <div class="w2-box-header">14 Other</div>
                            <div class="w2-box-value">&nbsp;</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- State Information -->
        <div class="row g-0 mt-3">
            <div class="col-2">
                <div class="w2-box">
                    <div class="w2-box-header">15 State</div>
                    <div class="w2-box-value">
                        <?= $company['company_state'] ?? 'TX' ?>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="w2-box">
                    <div class="w2-box-header">Employer's state ID number</div>
                    <div class="w2-box-value">
                        <?= $company['company_state_id'] ?? '' ?>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="w2-box">
                    <div class="w2-box-header">16 State wages, tips, etc.</div>
                    <div class="w2-box-value">$
                        <?= number_format($payroll['total_wages'] ?? 0, 2) ?>
                    </div>
                </div>
            </div>
            <div class="col-2">
                <div class="w2-box">
                    <div class="w2-box-header">17 State income tax</div>
                    <div class="w2-box-value">$
                        <?= number_format($payroll['state_withheld'] ?? 0, 2) ?>
                    </div>
                </div>
            </div>
            <div class="col-2">
                <div class="w2-box">
                    <div class="w2-box-header">18 Local wages</div>
                    <div class="w2-box-value">&nbsp;</div>
                </div>
            </div>
        </div>

        <div class="text-center mt-4">
            <small class="text-muted">
                Department of the Treasury—Internal Revenue Service
            </small>
        </div>
    </div>
</div>

<div class="card mt-4 no-print">
    <div class="card-header">
        <h5 class="mb-0">Payroll Summary -
            <?= $taxYear ?>
        </h5>
    </div>
    <div class="card-body">
        <table class="table table-sm">
            <tr>
                <td>Total Gross Pay</td>
                <td class="text-end"><strong>$
                        <?= number_format($payroll['total_wages'] ?? 0, 2) ?>
                    </strong></td>
            </tr>
            <tr>
                <td>Federal Tax Withheld</td>
                <td class="text-end">$
                    <?= number_format($payroll['federal_withheld'] ?? 0, 2) ?>
                </td>
            </tr>
            <tr>
                <td>State Tax Withheld</td>
                <td class="text-end">$
                    <?= number_format($payroll['state_withheld'] ?? 0, 2) ?>
                </td>
            </tr>
            <tr>
                <td>Social Security (6.2%)</td>
                <td class="text-end">$
                    <?= number_format($payroll['ss_withheld'] ?? 0, 2) ?>
                </td>
            </tr>
            <tr>
                <td>Medicare (1.45%)</td>
                <td class="text-end">$
                    <?= number_format($payroll['medicare_withheld'] ?? 0, 2) ?>
                </td>
            </tr>
            <tr>
                <td>Tips Reported</td>
                <td class="text-end">$
                    <?= number_format($payroll['total_tips'] ?? 0, 2) ?>
                </td>
            </tr>
        </table>
    </div>
</div>