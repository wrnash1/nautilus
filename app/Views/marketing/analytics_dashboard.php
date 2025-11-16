<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketing Analytics Dashboard</title>
    <link rel="stylesheet" href="/assets/css/professional-theme.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .marketing-dashboard {
            padding: 2rem;
            background: var(--background-gray);
        }

        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-header h1 {
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
        }

        .date-filter {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .metric-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            border-left: 4px solid var(--primary-blue);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .metric-card.positive {
            border-left-color: var(--success-green);
        }

        .metric-card.negative {
            border-left-color: var(--danger-red);
        }

        .metric-label {
            font-size: 0.875rem;
            color: var(--text-gray);
            margin-bottom: 0.5rem;
        }

        .metric-value {
            font-size: 2rem;
            font-weight: bold;
            color: var(--text-dark);
        }

        .metric-change {
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .metric-change.positive {
            color: var(--success-green);
        }

        .metric-change.negative {
            color: var(--danger-red);
        }

        .charts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .chart-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .chart-card h3 {
            margin-bottom: 1.5rem;
            color: var(--text-dark);
        }

        .campaign-list {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .campaign-item {
            padding: 1rem;
            border-bottom: 1px solid var(--border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .campaign-item:last-child {
            border-bottom: none;
        }

        .campaign-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .campaign-stats {
            display: flex;
            gap: 2rem;
            font-size: 0.875rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-label {
            color: var(--text-gray);
            display: block;
            font-size: 0.75rem;
        }

        .stat-value {
            font-weight: 600;
            color: var(--text-dark);
        }

        .funnel-chart {
            max-width: 600px;
            margin: 0 auto;
        }

        .funnel-stage {
            background: linear-gradient(to right, var(--primary-blue), var(--ocean-teal));
            color: white;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: transform 0.2s;
        }

        .funnel-stage:hover {
            transform: translateX(5px);
        }

        .funnel-stage:nth-child(2) {
            width: 80%;
            margin-left: 10%;
        }

        .funnel-stage:nth-child(3) {
            width: 60%;
            margin-left: 20%;
        }

        .funnel-stage:nth-child(4) {
            width: 40%;
            margin-left: 30%;
        }

        .funnel-stage:nth-child(5) {
            width: 20%;
            margin-left: 40%;
        }

        .segment-performance {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }

        .segment-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid var(--border-light);
        }

        .segment-row:hover {
            background: var(--background-gray);
        }

        .progress-bar {
            height: 8px;
            background: var(--border-light);
            border-radius: 4px;
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, var(--primary-blue), var(--ocean-teal));
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <div class="marketing-dashboard">
        <div class="dashboard-header">
            <h1>Marketing Analytics Dashboard</h1>
            <p>Real-time insights into your marketing performance</p>

            <div class="date-filter">
                <select class="pro-input" id="dateRange">
                    <option value="7">Last 7 days</option>
                    <option value="30" selected>Last 30 days</option>
                    <option value="90">Last 90 days</option>
                    <option value="365">Last year</option>
                </select>
                <button class="btn btn-primary" onclick="refreshDashboard()">
                    Refresh Data
                </button>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="metrics-grid">
            <div class="metric-card positive">
                <div class="metric-label">Total Campaigns</div>
                <div class="metric-value" id="totalCampaigns">24</div>
                <div class="metric-change positive">↑ 3 new this month</div>
            </div>

            <div class="metric-card positive">
                <div class="metric-label">Email Open Rate</div>
                <div class="metric-value" id="openRate">42.5%</div>
                <div class="metric-change positive">↑ 5.2% vs last period</div>
            </div>

            <div class="metric-card positive">
                <div class="metric-label">Click-Through Rate</div>
                <div class="metric-value" id="clickRate">12.8%</div>
                <div class="metric-change positive">↑ 2.1% vs last period</div>
            </div>

            <div class="metric-card positive">
                <div class="metric-label">Conversion Rate</div>
                <div class="metric-value" id="conversionRate">8.3%</div>
                <div class="metric-change positive">↑ 1.5% vs last period</div>
            </div>

            <div class="metric-card positive">
                <div class="metric-label">Revenue Generated</div>
                <div class="metric-value" id="totalRevenue">$47,320</div>
                <div class="metric-change positive">↑ $8,245 vs last period</div>
            </div>

            <div class="metric-card positive">
                <div class="metric-label">Marketing ROI</div>
                <div class="metric-value" id="roi">385%</div>
                <div class="metric-change positive">↑ 42% vs last period</div>
            </div>
        </div>

        <!-- Charts -->
        <div class="charts-grid">
            <!-- Campaign Performance Over Time -->
            <div class="chart-card" style="grid-column: 1 / -1;">
                <h3>Campaign Performance Trend</h3>
                <canvas id="performanceTrendChart"></canvas>
            </div>

            <!-- Channel Performance -->
            <div class="chart-card">
                <h3>Performance by Channel</h3>
                <canvas id="channelPerformanceChart"></canvas>
            </div>

            <!-- Campaign Types -->
            <div class="chart-card">
                <h3>Revenue by Campaign Type</h3>
                <canvas id="campaignTypeChart"></canvas>
            </div>
        </div>

        <!-- Conversion Funnel -->
        <div class="chart-card">
            <h3>Marketing Funnel</h3>
            <div class="funnel-chart">
                <div class="funnel-stage">
                    <span>Email Sent</span>
                    <strong>15,420</strong>
                </div>
                <div class="funnel-stage">
                    <span>Delivered</span>
                    <strong>14,998 (97.3%)</strong>
                </div>
                <div class="funnel-stage">
                    <span>Opened</span>
                    <strong>6,374 (42.5%)</strong>
                </div>
                <div class="funnel-stage">
                    <span>Clicked</span>
                    <strong>1,920 (12.8%)</strong>
                </div>
                <div class="funnel-stage">
                    <span>Converted</span>
                    <strong>159 (8.3%)</strong>
                </div>
            </div>
        </div>

        <!-- Top Performing Campaigns -->
        <div class="campaign-list">
            <h3>Top Performing Campaigns</h3>
            <div class="campaign-item">
                <div>
                    <div class="campaign-name">Spring Open Water Promotion</div>
                    <div style="font-size: 0.875rem; color: var(--text-gray);">Email • Active</div>
                </div>
                <div class="campaign-stats">
                    <div class="stat-item">
                        <span class="stat-label">Open Rate</span>
                        <span class="stat-value">48.2%</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">CTR</span>
                        <span class="stat-value">15.3%</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Conversions</span>
                        <span class="stat-value">42</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Revenue</span>
                        <span class="stat-value">$16,758</span>
                    </div>
                </div>
            </div>

            <div class="campaign-item">
                <div>
                    <div class="campaign-name">Equipment Sale - Summer Clearance</div>
                    <div style="font-size: 0.875rem; color: var(--text-gray);">Email + SMS • Completed</div>
                </div>
                <div class="campaign-stats">
                    <div class="stat-item">
                        <span class="stat-label">Open Rate</span>
                        <span class="stat-value">52.1%</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">CTR</span>
                        <span class="stat-value">18.7%</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Conversions</span>
                        <span class="stat-value">67</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Revenue</span>
                        <span class="stat-value">$13,420</span>
                    </div>
                </div>
            </div>

            <div class="campaign-item">
                <div>
                    <div class="campaign-name">Win-Back Inactive Customers</div>
                    <div style="font-size: 0.875rem; color: var(--text-gray);">Email • Active</div>
                </div>
                <div class="campaign-stats">
                    <div class="stat-item">
                        <span class="stat-label">Open Rate</span>
                        <span class="stat-value">35.8%</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">CTR</span>
                        <span class="stat-value">9.2%</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Conversions</span>
                        <span class="stat-value">23</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Revenue</span>
                        <span class="stat-value">$9,177</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Segment Performance -->
        <div class="segment-performance">
            <h3>Customer Segment Performance</h3>

            <div class="segment-row">
                <div>
                    <strong>VIP Customers</strong>
                    <div style="font-size: 0.875rem; color: var(--text-gray);">850 members</div>
                </div>
                <div style="text-align: right;">
                    <strong>$28,420 revenue</strong>
                    <div class="progress-bar" style="width: 200px;">
                        <div class="progress-fill" style="width: 95%;"></div>
                    </div>
                </div>
            </div>

            <div class="segment-row">
                <div>
                    <strong>Recent Divers</strong>
                    <div style="font-size: 0.875rem; color: var(--text-gray);">1,240 members</div>
                </div>
                <div style="text-align: right;">
                    <strong>$12,850 revenue</strong>
                    <div class="progress-bar" style="width: 200px;">
                        <div class="progress-fill" style="width: 68%;"></div>
                    </div>
                </div>
            </div>

            <div class="segment-row">
                <div>
                    <strong>Open Water Graduates</strong>
                    <div style="font-size: 0.875rem; color: var(--text-gray);">620 members</div>
                </div>
                <div style="text-align: right;">
                    <strong>$8,920 revenue</strong>
                    <div class="progress-bar" style="width: 200px;">
                        <div class="progress-fill" style="width: 52%;"></div>
                    </div>
                </div>
            </div>

            <div class="segment-row">
                <div>
                    <strong>At-Risk Customers</strong>
                    <div style="font-size: 0.875rem; color: var(--text-gray);">340 members</div>
                </div>
                <div style="text-align: right;">
                    <strong>$3,240 revenue</strong>
                    <div class="progress-bar" style="width: 200px;">
                        <div class="progress-fill" style="width: 28%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Sample data for charts
        const performanceData = {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [
                {
                    label: 'Open Rate (%)',
                    data: [38.5, 41.2, 39.8, 42.5],
                    borderColor: '#0066CC',
                    backgroundColor: 'rgba(0, 102, 204, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Click Rate (%)',
                    data: [10.2, 11.8, 12.1, 12.8],
                    borderColor: '#00BCD4',
                    backgroundColor: 'rgba(0, 188, 212, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Conversion Rate (%)',
                    data: [6.5, 7.2, 7.8, 8.3],
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    tension: 0.4
                }
            ]
        };

        const performanceTrendChart = new Chart(
            document.getElementById('performanceTrendChart'),
            {
                type: 'line',
                data: performanceData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 50
                        }
                    }
                }
            }
        );

        const channelData = {
            labels: ['Email', 'SMS', 'Social Media', 'Push Notifications'],
            datasets: [{
                label: 'Conversions',
                data: [159, 42, 28, 15],
                backgroundColor: [
                    '#0066CC',
                    '#00BCD4',
                    '#4CAF50',
                    '#FF6B35'
                ]
            }]
        };

        const channelChart = new Chart(
            document.getElementById('channelPerformanceChart'),
            {
                type: 'doughnut',
                data: channelData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            }
        );

        const campaignTypeData = {
            labels: ['Course Promotions', 'Equipment Sales', 'Dive Trips', 'Win-Back', 'Upsells'],
            datasets: [{
                label: 'Revenue ($)',
                data: [18750, 14200, 8920, 3240, 2210],
                backgroundColor: '#0066CC'
            }]
        };

        const campaignTypeChart = new Chart(
            document.getElementById('campaignTypeChart'),
            {
                type: 'bar',
                data: campaignTypeData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            }
        );

        function refreshDashboard() {
            const dateRange = document.getElementById('dateRange').value;
            console.log('Refreshing dashboard for last ' + dateRange + ' days');
            // In production, fetch new data from API
            alert('Dashboard refreshed for last ' + dateRange + ' days');
        }
    </script>
</body>
</html>
