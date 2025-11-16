<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quality Control Dashboard - Nautilus</title>
    <link href="/assets/css/professional-theme.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-lg);
            margin-bottom: var(--spacing-lg);
        }

        .metric-card {
            background: linear-gradient(135deg, var(--primary-blue), var(--deep-blue));
            color: white;
            padding: var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-md);
            transition: transform var(--transition-fast);
        }

        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .metric-card.success {
            background: linear-gradient(135deg, var(--success-green), #2E7D32);
        }

        .metric-card.warning {
            background: linear-gradient(135deg, var(--warning-yellow), #F57C00);
            color: var(--text-primary);
        }

        .metric-card.danger {
            background: linear-gradient(135deg, var(--error-red), #C62828);
        }

        .metric-value {
            font-size: 48px;
            font-weight: 700;
            margin: var(--spacing-sm) 0;
        }

        .metric-label {
            font-size: var(--font-size-sm);
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .metric-change {
            display: flex;
            align-items: center;
            gap: var(--spacing-xs);
            margin-top: var(--spacing-sm);
            font-size: var(--font-size-sm);
        }

        .metric-change.positive { color: #4CAF50; }
        .metric-change.negative { color: #F44336; }

        .chart-container {
            background: white;
            padding: var(--spacing-lg);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
            margin-bottom: var(--spacing-lg);
        }

        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--spacing-lg);
            padding-bottom: var(--spacing-md);
            border-bottom: 2px solid var(--gray-200);
        }

        .chart-title {
            font-size: var(--font-size-xl);
            font-weight: 600;
            margin: 0;
        }

        .incident-list {
            background: white;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .incident-item {
            padding: var(--spacing-md);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            transition: background var(--transition-fast);
        }

        .incident-item:hover {
            background: var(--bg-hover);
        }

        .incident-severity {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .incident-severity.minor { background: #E3F2FD; color: var(--info-blue); }
        .incident-severity.moderate { background: #FFF3E0; color: var(--warning-yellow); }
        .incident-severity.serious { background: #FFE0B2; color: var(--coral-orange); }
        .incident-severity.critical { background: #FFEBEE; color: var(--error-red); }

        .incident-content {
            flex: 1;
        }

        .incident-title {
            font-weight: 600;
            margin-bottom: 4px;
        }

        .incident-meta {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
        }

        .satisfaction-meter {
            height: 80px;
            background: var(--gray-100);
            border-radius: var(--border-radius);
            position: relative;
            overflow: hidden;
            margin: var(--spacing-md) 0;
        }

        .satisfaction-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--error-red), var(--warning-yellow), var(--success-green));
            transition: width 1s ease;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: var(--spacing-md);
            color: white;
            font-weight: 700;
            font-size: var(--font-size-2xl);
        }

        .tabs {
            display: flex;
            gap: var(--spacing-sm);
            border-bottom: 2px solid var(--gray-200);
            margin-bottom: var(--spacing-lg);
        }

        .tab {
            padding: var(--spacing-md) var(--spacing-lg);
            border: none;
            background: none;
            cursor: pointer;
            font-weight: 500;
            color: var(--text-secondary);
            border-bottom: 3px solid transparent;
            transition: all var(--transition-fast);
        }

        .tab:hover {
            color: var(--primary-blue);
        }

        .tab.active {
            color: var(--primary-blue);
            border-bottom-color: var(--primary-blue);
        }

        .alert-banner {
            background: linear-gradient(135deg, #FF6B35, #E74C3C);
            color: white;
            padding: var(--spacing-md);
            border-radius: var(--border-radius-lg);
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            box-shadow: var(--shadow-md);
        }

        .alert-banner i {
            font-size: 32px;
        }
    </style>
</head>
<body>

<div class="container" style="padding-top: var(--spacing-xl); padding-bottom: var(--spacing-xl);">

    <!-- Header -->
    <div style="margin-bottom: var(--spacing-xl);">
        <h1 style="display: flex; align-items: center; gap: var(--spacing-md); margin-bottom: var(--spacing-sm);">
            <i class="bi bi-shield-check" style="color: var(--primary-blue);"></i>
            Quality Control Dashboard
        </h1>
        <p style="color: var(--text-secondary); font-size: var(--font-size-lg);">
            Safety metrics, student satisfaction, and operational excellence
        </p>
    </div>

    <!-- Alert Banner -->
    <div class="alert-banner">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div style="flex: 1;">
            <strong>Action Required:</strong> 2 critical incidents pending review, 3 equipment maintenance items overdue
        </div>
        <button class="btn btn-sm" style="background: white; color: var(--error-red);">
            View Details
        </button>
    </div>

    <!-- Key Metrics -->
    <div class="dashboard-grid">
        <div class="metric-card success">
            <div class="metric-label">Overall Safety Rating</div>
            <div class="metric-value">98.5%</div>
            <div class="metric-change positive">
                <i class="bi bi-arrow-up"></i>
                <span>+2.3% from last month</span>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-label">Student Satisfaction</div>
            <div class="metric-value">4.8<span style="font-size: 24px;">/5.0</span></div>
            <div class="metric-change positive">
                <i class="bi bi-arrow-up"></i>
                <span>+0.3 this quarter</span>
            </div>
        </div>

        <div class="metric-card warning">
            <div class="metric-label">Incidents (30 Days)</div>
            <div class="metric-value">3</div>
            <div class="metric-change negative">
                <i class="bi bi-exclamation-triangle"></i>
                <span>2 awaiting review</span>
            </div>
        </div>

        <div class="metric-card success">
            <div class="metric-label">Course Completion Rate</div>
            <div class="metric-value">94%</div>
            <div class="metric-change positive">
                <i class="bi bi-trophy"></i>
                <span>Above industry avg</span>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="tabs">
        <button class="tab active" onclick="switchTab('overview')">
            <i class="bi bi-speedometer2"></i> Overview
        </button>
        <button class="tab" onclick="switchTab('incidents')">
            <i class="bi bi-exclamation-octagon"></i> Incidents
        </button>
        <button class="tab" onclick="switchTab('satisfaction')">
            <i class="bi bi-star"></i> Satisfaction
        </button>
        <button class="tab" onclick="switchTab('equipment')">
            <i class="bi bi-tools"></i> Equipment
        </button>
        <button class="tab" onclick="switchTab('instructors')">
            <i class="bi bi-person-badge"></i> Instructors
        </button>
    </div>

    <!-- Overview Tab Content -->
    <div id="tab-overview">
        <div class="row">
            <div class="col-8">
                <!-- Incident Trends Chart -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h3 class="chart-title">Incident Trends (6 Months)</h3>
                        <div class="filter-buttons">
                            <button class="filter-button active">All</button>
                            <button class="filter-button">DCI</button>
                            <button class="filter-button">Equipment</button>
                            <button class="filter-button">Near Miss</button>
                        </div>
                    </div>
                    <canvas id="incidentTrendsChart" height="80"></canvas>
                </div>

                <!-- Student Satisfaction Trends -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h3 class="chart-title">Student Satisfaction Score Trends</h3>
                        <select class="form-control" style="width: auto;">
                            <option>Last 6 Months</option>
                            <option>Last Year</option>
                            <option>All Time</option>
                        </select>
                    </div>
                    <canvas id="satisfactionTrendsChart" height="80"></canvas>
                </div>
            </div>

            <div class="col-4">
                <!-- Recent Incidents -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h3 class="chart-title">Recent Incidents</h3>
                        <a href="/incidents" class="btn btn-sm btn-ghost">View All</a>
                    </div>
                    <div class="incident-list">
                        <div class="incident-item">
                            <div class="incident-severity critical">⚠️</div>
                            <div class="incident-content">
                                <div class="incident-title">Diver Distress - Rapid Ascent</div>
                                <div class="incident-meta">2 days ago • Under Review</div>
                            </div>
                        </div>
                        <div class="incident-item">
                            <div class="incident-severity moderate">⚡</div>
                            <div class="incident-content">
                                <div class="incident-title">Equipment Malfunction - Regulator</div>
                                <div class="incident-meta">5 days ago • Closed</div>
                            </div>
                        </div>
                        <div class="incident-item">
                            <div class="incident-severity minor">ℹ️</div>
                            <div class="incident-content">
                                <div class="incident-title">Near Miss - Boat Anchor</div>
                                <div class="incident-meta">1 week ago • Closed</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="chart-container">
                    <div class="chart-header">
                        <h3 class="chart-title">30-Day Summary</h3>
                    </div>
                    <div style="display: grid; gap: var(--spacing-md);">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-secondary);">Total Dives</span>
                            <strong style="font-size: var(--font-size-xl);">247</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-secondary);">Students Certified</span>
                            <strong style="font-size: var(--font-size-xl);">42</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-secondary);">Safety Checks</span>
                            <strong style="font-size: var(--font-size-xl); color: var(--success-green);">247/247</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="color: var(--text-secondary);">Equipment Issues</span>
                            <strong style="font-size: var(--font-size-xl); color: var(--warning-yellow);">3</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Satisfaction Meter -->
        <div class="chart-container">
            <div class="chart-header">
                <h3 class="chart-title">Overall Customer Satisfaction</h3>
            </div>
            <div class="satisfaction-meter">
                <div class="satisfaction-fill" style="width: 96%;">96%</div>
            </div>
            <div class="row">
                <div class="col-3 text-center">
                    <div style="font-size: var(--font-size-2xl); font-weight: 700;">98%</div>
                    <div style="color: var(--text-secondary);">Course Quality</div>
                </div>
                <div class="col-3 text-center">
                    <div style="font-size: var(--font-size-2xl); font-weight: 700;">95%</div>
                    <div style="color: var(--text-secondary);">Equipment</div>
                </div>
                <div class="col-3 text-center">
                    <div style="font-size: var(--font-size-2xl); font-weight: 700;">97%</div>
                    <div style="color: var(--text-secondary);">Safety</div>
                </div>
                <div class="col-3 text-center">
                    <div style="font-size: var(--font-size-2xl); font-weight: 700;">94%</div>
                    <div style="color: var(--text-secondary);">Facilities</div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// Incident Trends Chart
const incidentCtx = document.getElementById('incidentTrendsChart').getContext('2d');
const incidentTrendsChart = new Chart(incidentCtx, {
    type: 'line',
    data: {
        labels: ['June', 'July', 'August', 'September', 'October', 'November'],
        datasets: [{
            label: 'Total Incidents',
            data: [2, 1, 3, 2, 1, 3],
            borderColor: '#FF6B35',
            backgroundColor: 'rgba(255, 107, 53, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Preventable',
            data: [1, 1, 2, 1, 0, 2],
            borderColor: '#FFC107',
            backgroundColor: 'rgba(255, 193, 7, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Satisfaction Trends Chart
const satisfactionCtx = document.getElementById('satisfactionTrendsChart').getContext('2d');
const satisfactionTrendsChart = new Chart(satisfactionCtx, {
    type: 'bar',
    data: {
        labels: ['June', 'July', 'August', 'September', 'October', 'November'],
        datasets: [{
            label: 'Average Rating',
            data: [4.5, 4.6, 4.7, 4.8, 4.7, 4.8],
            backgroundColor: '#0066CC',
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                min: 4.0,
                max: 5.0,
                ticks: {
                    stepSize: 0.2
                }
            }
        }
    }
});

function switchTab(tab) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    event.target.closest('.tab').classList.add('active');

    // Hide all tab content
    document.querySelectorAll('[id^="tab-"]').forEach(t => t.style.display = 'none');

    // Show selected tab
    const selectedTab = document.getElementById('tab-' + tab);
    if (selectedTab) {
        selectedTab.style.display = 'block';
    } else {
        alert('Tab content for ' + tab + ' coming soon!');
    }
}
</script>

</body>
</html>
