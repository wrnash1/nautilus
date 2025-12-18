<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 text-gray-800">Staff Time Clock</h1>
            <p class="text-muted">Manage your shift status and view recent activity.</p>
        </div>
    </div>

    <!-- Status Card -->
    <div class="row mb-5 justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center p-5">
                    <h5 class="text-uppercase text-muted fw-bold mb-4">Current Status</h5>
                    
                    <?php if ($currentShift): ?>
                        <div class="mb-4">
                            <i class="bi bi-clock-history text-success" style="font-size: 4rem;"></i>
                            <h2 class="mt-3 text-success">Clocked In</h2>
                            <p class="text-muted">Since <?= date('g:i A', strtotime($currentShift['clock_in'])) ?></p>
                            <small class="text-muted d-block">Shift Duration: <span id="shiftTimer">Loading...</span></small>
                        </div>
                        
                        <form action="/store/staff/timeclock/clockout" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="btn btn-danger btn-lg w-100 rounded-pill py-3">
                                <i class="bi bi-box-arrow-right me-2"></i> Clock Out
                            </button>
                        </form>
                    <?php else: ?>
                        <div class="mb-4">
                            <i class="bi bi-pause-circle text-secondary" style="font-size: 4rem;"></i>
                            <h2 class="mt-3 text-secondary">Clocked Out</h2>
                            <p class="text-muted">Ready to start your shift?</p>
                        </div>
                        
                        <form action="/store/staff/timeclock/clockin" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                            <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill py-3">
                                <i class="bi bi-play-circle me-2"></i> Clock In
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                 <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="list-group list-group-flush">
                    <?php if (empty($recentEntries)): ?>
                        <div class="list-group-item text-center py-4 text-muted">No recent activity</div>
                    <?php else: ?>
                        <?php foreach (array_slice($recentEntries, 0, 5) as $entry): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1 text-dark">
                                            <?= date('M j, Y', strtotime($entry['clock_in'])) ?>
                                        </h6>
                                        <small class="text-muted">
                                            <?= date('g:i A', strtotime($entry['clock_in'])) ?> - 
                                            <?= $entry['clock_out'] ? date('g:i A', strtotime($entry['clock_out'])) : 'Active' ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <?php if ($entry['clock_out']): ?>
                                            <?php 
                                            $duration = (strtotime($entry['clock_out']) - strtotime($entry['clock_in'])) / 3600;
                                            ?>
                                            <span class="badge bg-light text-dark border"><?= number_format($duration, 2) ?> hrs</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="card-footer bg-white text-center">
                    <a href="/store/staff/timeclock/reports" class="btn btn-link btn-sm text-decoration-none">View History & Reports &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($currentShift): ?>
<script>
    // Simple Timer for Active Shift
    const startTime = new Date('<?= $currentShift['clock_in'] ?>').getTime();
    
    function updateTimer() {
        const now = new Date().getTime();
        const diff = now - startTime;
        
        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        
        document.getElementById('shiftTimer').innerText = 
            `${hours}h ${minutes}m ${seconds}s`;
    }
    
    setInterval(updateTimer, 1000);
    updateTimer();
</script>
<?php endif; ?>
