<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Timesheet Reports</h1>
        <a href="/store/staff/timeclock" class="btn btn-sm btn-secondary shadow-sm">
            <i class="bi bi-arrow-left fa-sm text-white-50"></i> Back to Time Clock
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Options</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($_GET['start_date'] ?? date('Y-m-01')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($_GET['end_date'] ?? date('Y-m-t')) ?>">
                </div>
                <!-- Staff Filter could go here if data available -->
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-filter"></i> Apply
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="?" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Stats -->
    <?php
    $totalHours = 0;
    foreach ($timesheets as $entry) {
        if ($entry['clock_out']) {
            $totalHours += (strtotime($entry['clock_out']) - strtotime($entry['clock_in'])) / 3600;
        }
    }
    ?>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2 border-0 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Hours (Period)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($totalHours, 2) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-clock fa-2x text-gray-300 fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2 border-0 border-start border-4 border-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Shifts Worked</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($timesheets) ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="bi bi-calendar-check fa-2x text-gray-300 fs-2"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timesheet Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary">Detailed Activity</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Staff Member</th>
                            <th>Clock In</th>
                            <th>Clock Out</th>
                            <th>Duration</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($timesheets)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No records found for this period.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($timesheets as $entry): ?>
                            <tr>
                                <td><?= date('M j, Y', strtotime($entry['clock_in'])) ?></td>
                                <td><?= htmlspecialchars($entry['staff_name'] ?? 'Me') ?></td>
                                <td><?= date('g:i A', strtotime($entry['clock_in'])) ?></td>
                                <td>
                                    <?php if ($entry['clock_out']): ?>
                                        <?= date('g:i A', strtotime($entry['clock_out'])) ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($entry['clock_out']): ?>
                                        <?php 
                                        $h = (strtotime($entry['clock_out']) - strtotime($entry['clock_in'])) / 3600;
                                        echo number_format($h, 2) . ' hrs';
                                        ?>
                                    <?php else: ?>
                                        Running...
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($entry['clock_out']): ?>
                                        <span class="badge bg-secondary">Completed</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Active</span>
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
</div>
