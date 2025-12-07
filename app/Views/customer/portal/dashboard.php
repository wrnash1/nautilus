<?php
$pageTitle = 'My Dashboard';
ob_start();
?>

<div class="customer-portal-dashboard">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-content">
            <h1>Welcome back, <?= htmlspecialchars($data['profile']['first_name']) ?>! 👋</h1>
            <p class="welcome-subtitle">Here's what's happening with your diving journey</p>
        </div>
        <div class="quick-actions">
            <a href="/portal/request-appointment" class="quick-action-btn">
                <i class="fas fa-calendar-plus"></i>
                <span>Book Appointment</span>
            </a>
            <a href="/shop" class="quick-action-btn">
                <i class="fas fa-shopping-cart"></i>
                <span>Shop</span>
            </a>
            <a href="/portal/certifications" class="quick-action-btn">
                <i class="fas fa-certificate"></i>
                <span>Certifications</span>
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon orders">
                <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($data['statistics']['total_orders']) ?></div>
                <div class="stat-label">Orders</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon spending">
                <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value">$<?= number_format($data['statistics']['total_spent'], 2) ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon courses">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($data['statistics']['total_courses']) ?></div>
                <div class="stat-label">Courses</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon trips">
                <i class="fas fa-ship"></i>
            </div>
            <div class="stat-info">
                <div class="stat-value"><?= number_format($data['statistics']['total_trips']) ?></div>
                <div class="stat-label">Dive Trips</div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Left Column -->
        <div class="content-left">
            <!-- Upcoming Courses -->
            <?php if (!empty($data['upcoming_courses'])): ?>
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-graduation-cap"></i> Upcoming Courses</h2>
                    <a href="/portal/courses" class="view-all">View All</a>
                </div>
                <div class="card-body">
                    <?php foreach ($data['upcoming_courses'] as $course): ?>
                    <div class="list-item">
                        <div class="item-icon course-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="item-content">
                            <div class="item-title"><?= htmlspecialchars($course['course_name']) ?></div>
                            <div class="item-meta">
                                <span><i class="fas fa-calendar"></i> <?= date('M j, Y', strtotime($course['start_date'])) ?></span>
                                <span><i class="fas fa-user"></i> <?= htmlspecialchars($course['instructor_name'] ?? 'TBD') ?></span>
                            </div>
                        </div>
                        <div class="item-status">
                            <span class="status-badge status-<?= $course['status'] ?>"><?= ucfirst($course['status']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Upcoming Trips -->
            <?php if (!empty($data['upcoming_trips'])): ?>
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-ship"></i> Upcoming Dive Trips</h2>
                    <a href="/portal/trips" class="view-all">View All</a>
                </div>
                <div class="card-body">
                    <?php foreach ($data['upcoming_trips'] as $trip): ?>
                    <div class="list-item">
                        <div class="item-icon trip-icon">
                            <i class="fas fa-water"></i>
                        </div>
                        <div class="item-content">
                            <div class="item-title"><?= htmlspecialchars($trip['trip_name']) ?></div>
                            <div class="item-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($trip['destination']) ?></span>
                                <span><i class="fas fa-calendar"></i> <?= date('M j', strtotime($trip['departure_date'])) ?> - <?= date('M j, Y', strtotime($trip['return_date'])) ?></span>
                            </div>
                        </div>
                        <div class="item-status">
                            <span class="status-badge status-<?= $trip['status'] ?>"><?= ucfirst($trip['status']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Orders -->
            <?php if (!empty($data['recent_orders'])): ?>
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-shopping-bag"></i> Recent Orders</h2>
                    <a href="/portal/orders" class="view-all">View All</a>
                </div>
                <div class="card-body">
                    <?php foreach ($data['recent_orders'] as $order): ?>
                    <div class="list-item">
                        <div class="item-icon order-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="item-content">
                            <div class="item-title">Order #<?= $order['id'] ?></div>
                            <div class="item-meta">
                                <span><?= $order['item_count'] ?> item(s)</span>
                                <span><?= date('M j, Y', strtotime($order['created_at'])) ?></span>
                            </div>
                        </div>
                        <div class="item-amount">
                            $<?= number_format($order['total'], 2) ?>
                        </div>
                        <a href="/portal/orders/<?= $order['id'] ?>" class="item-action">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right Column -->
        <div class="content-right">
            <!-- Loyalty Points -->
            <?php if (!empty($data['loyalty_points'])): ?>
            <div class="card loyalty-card">
                <div class="card-body">
                    <div class="loyalty-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h3>Loyalty Points</h3>
                    <?php foreach ($data['loyalty_points'] as $loyalty): ?>
                    <div class="loyalty-info">
                        <div class="points-value"><?= number_format($loyalty['points_balance']) ?></div>
                        <div class="points-label"><?= htmlspecialchars($loyalty['points_currency'] ?? 'points') ?></div>
                    </div>
                    <?php endforeach; ?>
                    <p class="loyalty-text">Keep diving to earn more rewards!</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Certifications -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-certificate"></i> Certifications</h2>
                    <a href="/portal/certifications" class="view-all">View All</a>
                </div>
                <div class="card-body">
                    <?php if (!empty($data['certifications'])): ?>
                        <?php foreach (array_slice($data['certifications'], 0, 3) as $cert): ?>
                        <div class="cert-item">
                            <div class="cert-icon">
                                <i class="fas fa-award"></i>
                            </div>
                            <div class="cert-content">
                                <div class="cert-name"><?= htmlspecialchars($cert['course_name']) ?></div>
                                <div class="cert-number"><?= htmlspecialchars($cert['certification_number']) ?></div>
                                <div class="cert-date"><?= date('M Y', strtotime($cert['certification_date'])) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state-small">
                            <p>No certifications yet. Sign up for a course to get started!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Active Rentals -->
            <?php if (!empty($data['active_rentals'])): ?>
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-life-ring"></i> Active Rentals</h2>
                </div>
                <div class="card-body">
                    <?php foreach ($data['active_rentals'] as $rental): ?>
                    <div class="rental-item">
                        <div class="rental-info">
                            <div class="rental-name"><?= htmlspecialchars($rental['equipment_name']) ?></div>
                            <div class="rental-code"><?= htmlspecialchars($rental['equipment_code']) ?></div>
                        </div>
                        <div class="rental-dates">
                            <div class="rental-date">
                                <small>Return by:</small>
                                <strong><?= date('M j, Y', strtotime($rental['end_date'])) ?></strong>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Upcoming Appointments -->
            <?php if (!empty($data['upcoming_appointments'])): ?>
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-calendar-check"></i> Appointments</h2>
                </div>
                <div class="card-body">
                    <?php foreach ($data['upcoming_appointments'] as $apt): ?>
                    <div class="appointment-item">
                        <div class="apt-date">
                            <div class="apt-day"><?= date('d', strtotime($apt['start_time'])) ?></div>
                            <div class="apt-month"><?= date('M', strtotime($apt['start_time'])) ?></div>
                        </div>
                        <div class="apt-info">
                            <div class="apt-type"><?= ucfirst($apt['appointment_type']) ?></div>
                            <div class="apt-time"><?= date('g:i A', strtotime($apt['start_time'])) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.customer-portal-dashboard {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.welcome-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    border-radius: 12px;
    margin-bottom: 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.welcome-content h1 {
    margin: 0 0 5px 0;
    font-size: 32px;
}

.welcome-subtitle {
    margin: 0;
    opacity: 0.9;
}

.quick-actions {
    display: flex;
    gap: 15px;
}

.quick-action-btn {
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    padding: 15px 25px;
    border-radius: 8px;
    text-decoration: none;
    color: white;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s;
}

.quick-action-btn:hover {
    background: rgba(255,255,255,0.3);
    transform: translateY(-2px);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
}

.stat-icon.orders { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.stat-icon.spending { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.stat-icon.courses { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.stat-icon.trips { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }

.stat-value {
    font-size: 28px;
    font-weight: bold;
    color: #333;
}

.stat-label {
    color: #666;
    font-size: 14px;
}

.content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

.card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.card-header {
    padding: 20px 25px;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    margin: 0;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.view-all {
    color: #667eea;
    text-decoration: none;
    font-size: 14px;
}

.view-all:hover {
    text-decoration: underline;
}

.card-body {
    padding: 25px;
}

.list-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 10px;
    background: #f8f9fa;
}

.item-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
}

.item-icon.course-icon { background: #667eea; }
.item-icon.trip-icon { background: #43e97b; }
.item-icon.order-icon { background: #f093fb; }

.item-content {
    flex: 1;
}

.item-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.item-meta {
    display: flex;
    gap: 15px;
    font-size: 13px;
    color: #666;
}

.item-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.item-amount {
    font-weight: bold;
    color: #333;
    font-size: 16px;
}

.item-action {
    color: #999;
}

.status-badge {
    padding: 5px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-enrolled { background: #cfe2ff; color: #084298; }
.status-in_progress { background: #fff3cd; color: #997404; }
.status-confirmed { background: #d1e7dd; color: #0f5132; }
.status-paid { background: #d1e7dd; color: #0f5132; }

.loyalty-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    text-align: center;
}

.loyalty-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.points-value {
    font-size: 48px;
    font-weight: bold;
    margin: 15px 0 5px 0;
}

.points-label {
    font-size: 14px;
    opacity: 0.9;
    margin-bottom: 15px;
}

.loyalty-text {
    opacity: 0.9;
    margin: 15px 0 0 0;
}

.cert-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 10px;
}

.cert-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.cert-name {
    font-weight: 600;
    color: #333;
}

.cert-number, .cert-date {
    font-size: 13px;
    color: #666;
}

.rental-item {
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 10px;
}

.rental-name {
    font-weight: 600;
    color: #333;
}

.rental-code, .rental-dates small {
    font-size: 13px;
    color: #666;
}

.appointment-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 10px;
}

.apt-date {
    text-align: center;
    min-width: 50px;
}

.apt-day {
    font-size: 24px;
    font-weight: bold;
    color: #667eea;
}

.apt-month {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}

.apt-type {
    font-weight: 600;
    color: #333;
}

.apt-time {
    font-size: 13px;
    color: #666;
}

.empty-state-small {
    text-align: center;
    padding: 20px;
    color: #999;
}

@media (max-width: 968px) {
    .content-grid {
        grid-template-columns: 1fr;
    }

    .welcome-section {
        text-align: center;
    }

    .quick-actions {
        width: 100%;
        justify-content: center;
    }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/customer.php';
?>
