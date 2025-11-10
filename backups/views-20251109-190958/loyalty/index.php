<?php
$pageTitle = 'Loyalty Program';
$activeMenu = 'loyalty';

ob_start();
?>

<!-- Page Header -->
<div class="hero-section slide-up" style="background: linear-gradient(135deg, var(--warning), #d97706); color: white; padding: 2rem; border-radius: var(--radius-xl); margin-bottom: 2rem; position: relative; overflow: hidden;">
    <div style="content: ''; position: absolute; top: 0; right: 0; width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); border-radius: 50%;"></div>
    <div style="position: relative; z-index: 1;">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 style="font-size: 2rem; font-weight: 700; margin: 0 0 0.5rem 0;"><i class="bi bi-award-fill"></i> Loyalty Program Dashboard</h1>
                <p style="font-size: 1rem; opacity: 0.9; margin: 0;">Manage customer rewards and engagement across 4 tiers</p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <button onclick="showLoyaltySettings()" class="btn-modern btn-secondary">
                    <i class="bi bi-gear"></i> Settings
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Program Statistics -->
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="stat-card-modern slide-up">
        <div class="stat-icon-modern" style="background: linear-gradient(135deg, var(--primary-400), var(--primary-600));">
            <i class="bi bi-people"></i>
        </div>
        <div class="stat-value-modern"><?= number_format($statistics['total_members'] ?? 0) ?></div>
        <div class="stat-label-modern">Total Members</div>
        <?php if (isset($statistics['members_growth']) && $statistics['members_growth'] != 0): ?>
        <div class="stat-change-modern stat-change-positive">
            <i class="bi bi-arrow-up"></i>
            <span><?= abs(number_format($statistics['members_growth'], 1)) ?>% this month</span>
        </div>
        <?php endif; ?>
    </div>

    <div class="stat-card-modern slide-up" style="animation-delay: 0.05s;">
        <div class="stat-icon-modern" style="background: linear-gradient(135deg, var(--warning), #d97706);">
            <i class="bi bi-star-fill"></i>
        </div>
        <div class="stat-value-modern"><?= number_format($statistics['total_points_issued'] ?? 0) ?></div>
        <div class="stat-label-modern">Points Issued</div>
    </div>

    <div class="stat-card-modern slide-up" style="animation-delay: 0.1s;">
        <div class="stat-icon-modern" style="background: linear-gradient(135deg, var(--success), #059669);">
            <i class="bi bi-gift"></i>
        </div>
        <div class="stat-value-modern"><?= number_format($statistics['rewards_redeemed'] ?? 0) ?></div>
        <div class="stat-label-modern">Rewards Redeemed</div>
    </div>

    <div class="stat-card-modern slide-up" style="animation-delay: 0.15s;">
        <div class="stat-icon-modern" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
            <i class="bi bi-cash-coin"></i>
        </div>
        <div class="stat-value-modern"><?= formatCurrency($statistics['total_value_redeemed'] ?? 0) ?></div>
        <div class="stat-label-modern">Total Value Redeemed</div>
    </div>
</div>

<!-- Tier Distribution -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="modern-card slide-up" style="animation-delay: 0.2s;">
            <div class="modern-card-header">
                <h2 class="modern-card-title">
                    <i class="bi bi-trophy"></i> Membership Tier Distribution
                </h2>
            </div>
            <div class="modern-card-body">
                <div class="row g-4">
                    <?php
                    $tiers = [
                        'bronze' => ['color' => '#cd7f32', 'icon' => 'bi-award', 'label' => 'Bronze'],
                        'silver' => ['color' => '#c0c0c0', 'icon' => 'bi-award-fill', 'label' => 'Silver'],
                        'gold' => ['color' => '#ffd700', 'icon' => 'bi-trophy', 'label' => 'Gold'],
                        'platinum' => ['color' => '#e5e4e2', 'icon' => 'bi-trophy-fill', 'label' => 'Platinum']
                    ];

                    foreach ($tiers as $tierKey => $tierData):
                        $count = $statistics['tier_distribution'][$tierKey] ?? 0;
                        $percentage = $statistics['total_members'] > 0 ? ($count / $statistics['total_members']) * 100 : 0;
                    ?>
                    <div class="col-md-6 col-lg-3">
                        <div style="padding: 1.5rem; border: 2px solid var(--border-color); border-radius: var(--radius-lg); text-align: center; transition: all var(--transition-base); cursor: pointer;" onmouseover="this.style.borderColor='<?= $tierData['color'] ?>'" onmouseout="this.style.borderColor='var(--border-color)'">
                            <div style="width: 3.5rem; height: 3.5rem; margin: 0 auto 1rem; background: <?= $tierData['color'] ?>; border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center;">
                                <i class="bi <?= $tierData['icon'] ?>" style="font-size: 1.75rem; color: white;"></i>
                            </div>
                            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary);"><?= $tierData['label'] ?></h3>
                            <div style="font-size: 1.75rem; font-weight: 700; margin-bottom: 0.25rem; color: var(--text-primary);"><?= number_format($count) ?></div>
                            <div style="font-size: 0.875rem; color: var(--text-secondary);"><?= number_format($percentage, 1) ?>% of members</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="modern-card slide-up" style="animation-delay: 0.25s;">
            <div class="modern-card-header">
                <h2 class="modern-card-title">
                    <i class="bi bi-clock-history"></i> Recent Activity
                </h2>
            </div>
            <div class="modern-card-body">
                <?php if (!empty($statistics['recent_activity'])): ?>
                    <?php foreach ($statistics['recent_activity'] as $activity): ?>
                    <div style="padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);">
                        <div style="font-weight: 600; margin-bottom: 0.25rem; color: var(--text-primary);"><?= htmlspecialchars($activity['customer_name']) ?></div>
                        <div style="font-size: 0.875rem; color: var(--text-secondary);">
                            <?= htmlspecialchars($activity['action']) ?>
                            <span style="color: var(--warning); font-weight: 600;"><?= number_format($activity['points']) ?> points</span>
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-tertiary); margin-top: 0.25rem;">
                            <i class="bi bi-clock"></i> <?= htmlspecialchars($activity['time_ago']) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: var(--text-tertiary); margin: 0; text-align: center; padding: 2rem;">No recent activity</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Top Loyalty Members -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="modern-card slide-up" style="animation-delay: 0.3s;">
            <div class="modern-card-header">
                <h2 class="modern-card-title">
                    <i class="bi bi-star-fill"></i> Top Loyalty Members
                </h2>
            </div>
            <div class="modern-card-body" style="padding: 0;">
                <?php if (!empty($statistics['top_members'])): ?>
                <div class="table-responsive">
                    <table class="table-modern" style="margin-bottom: 0;">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Member</th>
                                <th>Tier</th>
                                <th class="text-end">Points Balance</th>
                                <th class="text-end">Lifetime Points</th>
                                <th class="text-end">Total Spent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($statistics['top_members'] as $index => $member): ?>
                            <tr>
                                <td>
                                    <?php if ($index < 3): ?>
                                        <span style="font-size: 1.5rem;">
                                            <?= $index === 0 ? '🥇' : ($index === 1 ? '🥈' : '🥉') ?>
                                        </span>
                                    <?php else: ?>
                                        <strong style="color: var(--text-secondary);">#<?= $index + 1 ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 2.5rem; height: 2.5rem; border-radius: var(--radius-full); background: linear-gradient(135deg, var(--primary-400), var(--primary-600)); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                            <?= strtoupper(substr($member['name'], 0, 2)) ?>
                                        </div>
                                        <div>
                                            <div style="font-weight: 600;"><?= htmlspecialchars($member['name']) ?></div>
                                            <div style="font-size: 0.875rem; color: var(--text-tertiary);"><?= htmlspecialchars($member['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $tierColors = [
                                        'bronze' => '#cd7f32',
                                        'silver' => '#c0c0c0',
                                        'gold' => '#ffd700',
                                        'platinum' => '#e5e4e2'
                                    ];
                                    $tierColor = $tierColors[$member['tier']] ?? '#6b7280';
                                    ?>
                                    <span style="display: inline-flex; align-items: center; gap: 0.25rem; padding: 0.25rem 0.75rem; background: <?= $tierColor ?>20; border-radius: var(--radius-md); font-weight: 600; font-size: 0.875rem;">
                                        <i class="bi bi-award-fill" style="color: <?= $tierColor ?>;"></i>
                                        <?= ucfirst($member['tier']) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <strong style="color: var(--warning);"><?= number_format($member['points_balance']) ?></strong>
                                </td>
                                <td class="text-end">
                                    <span class="badge-modern badge-secondary"><?= number_format($member['lifetime_points']) ?></span>
                                </td>
                                <td class="text-end" style="color: var(--success);">
                                    <strong><?= formatCurrency($member['total_spent']) ?></strong>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p style="color: var(--text-tertiary); margin: 0; padding: 2rem; text-align: center;">No loyalty members yet</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Program Features -->
<div class="grid-modern grid-cols-3" style="gap: 1.5rem;">
    <div class="modern-card slide-up" style="animation-delay: 0.35s;">
        <div class="modern-card-body" style="text-align: center;">
            <div style="width: 4rem; height: 4rem; margin: 0 auto 1rem; background: linear-gradient(135deg, var(--primary-400), var(--primary-600)); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-cash-coin" style="font-size: 2rem; color: white;"></i>
            </div>
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary);">Points Configuration</h3>
            <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 1rem;">$1 = 10 points<br>1 point = $0.01</p>
            <button onclick="toast.info('Points configuration can be adjusted in settings')" class="btn-modern btn-ghost">
                <i class="bi bi-gear"></i> Configure
            </button>
        </div>
    </div>

    <div class="modern-card slide-up" style="animation-delay: 0.4s;">
        <div class="modern-card-body" style="text-align: center;">
            <div style="width: 4rem; height: 4rem; margin: 0 auto 1rem; background: linear-gradient(135deg, var(--success), #059669); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-gift" style="font-size: 2rem; color: white;"></i>
            </div>
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary);">Rewards Catalog</h3>
            <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 1rem;">Manage available rewards<br>and redemption options</p>
            <a href="/loyalty/rewards" class="btn-modern btn-success">
                <i class="bi bi-arrow-right"></i> View Catalog
            </a>
        </div>
    </div>

    <div class="modern-card slide-up" style="animation-delay: 0.45s;">
        <div class="modern-card-body" style="text-align: center;">
            <div style="width: 4rem; height: 4rem; margin: 0 auto 1rem; background: linear-gradient(135deg, var(--warning), #d97706); border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-graph-up" style="font-size: 2rem; color: white;"></i>
            </div>
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-primary);">Program Analytics</h3>
            <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 1rem;">View detailed reports<br>and engagement metrics</p>
            <button onclick="toast.info('Advanced analytics coming soon!')" class="btn-modern btn-secondary">
                <i class="bi bi-bar-chart"></i> View Reports
            </button>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Additional CSS
$additionalCss = '<link rel="stylesheet" href="/assets/css/modern-theme.css">';

// Additional JavaScript
$additionalJs = '<script src="/assets/js/theme-manager.js"></script>
<script>
function showLoyaltySettings() {
    toast.info("Loyalty program settings can be configured in the admin panel");
}

// Show welcome message
setTimeout(() => {
    toast.info("Welcome to the Loyalty Program Dashboard! Track customer engagement across 4 tiers.", 5000);
}, 1000);
</script>';

require __DIR__ . '/../layouts/app.php';
?>
