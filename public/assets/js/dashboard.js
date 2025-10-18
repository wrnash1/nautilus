/**
 * NAUTILUS V6 - MODERN DASHBOARD JAVASCRIPT
 * Scuba Diving Business Management System
 */

(function() {
    'use strict';

    // ============================================
    // ANIMATED COUNTER
    // ============================================
    function animateCounter(element, start, end, duration) {
        const range = end - start;
        const increment = range / (duration / 16); // 60fps
        let current = start;
        const isCurrency = element.dataset.currency === 'true';
        const isDecimal = element.dataset.decimal === 'true';

        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }

            let displayValue;
            if (isCurrency) {
                displayValue = '$' + current.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            } else if (isDecimal) {
                displayValue = current.toFixed(1);
            } else {
                displayValue = Math.floor(current).toLocaleString();
            }

            element.textContent = displayValue;
        }, 16);
    }

    // ============================================
    // INITIALIZE COUNTERS ON PAGE LOAD
    // ============================================
    function initializeCounters() {
        const counters = document.querySelectorAll('.metric-value[data-count]');

        counters.forEach(counter => {
            const targetValue = parseFloat(counter.dataset.count);
            const startValue = 0;
            const duration = 1500; // 1.5 seconds

            // Add count-up animation class
            counter.classList.add('count-up');

            // Start animation after a brief delay
            setTimeout(() => {
                animateCounter(counter, startValue, targetValue, duration);
            }, 100);
        });
    }

    // ============================================
    // FADE IN ANIMATIONS
    // ============================================
    function initializeFadeIns() {
        const elements = document.querySelectorAll('.metric-card, .chart-card, .upcoming-card, .alert-card');

        elements.forEach((element, index) => {
            element.classList.add('fade-in-up');
            element.style.animationDelay = `${index * 0.1}s`;
        });
    }

    // ============================================
    // CHART CONFIGURATIONS
    // ============================================

    // Default chart options
    const defaultChartOptions = {
        responsive: true,
        maintainAspectRatio: true,
        interaction: {
            intersect: false,
            mode: 'index'
        },
        plugins: {
            legend: {
                display: true,
                position: 'top',
                labels: {
                    usePointStyle: true,
                    padding: 15,
                    font: {
                        size: 12,
                        family: "'Inter', 'Segoe UI', sans-serif"
                    }
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                padding: 12,
                cornerRadius: 8,
                titleFont: {
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    size: 13
                },
                bodySpacing: 6,
                usePointStyle: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)',
                    drawBorder: false
                },
                ticks: {
                    font: {
                        size: 11
                    },
                    color: '#718096'
                }
            },
            x: {
                grid: {
                    display: false,
                    drawBorder: false
                },
                ticks: {
                    font: {
                        size: 11
                    },
                    color: '#718096'
                }
            }
        }
    };

    // ============================================
    // SALES CHART
    // ============================================
    window.createSalesChart = function(elementId, salesData) {
        const ctx = document.getElementById(elementId);
        if (!ctx) return;

        const labels = salesData.map(d => {
            const date = new Date(d.date);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        });

        const data = salesData.map(d => parseFloat(d.total));

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Sales ($)',
                    data: data,
                    borderColor: '#088395',
                    backgroundColor: 'rgba(8, 131, 149, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: '#088395',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointHoverBackgroundColor: '#05bfdb',
                    pointHoverBorderColor: '#ffffff'
                }]
            },
            options: {
                ...defaultChartOptions,
                plugins: {
                    ...defaultChartOptions.plugins,
                    legend: {
                        display: false
                    }
                },
                scales: {
                    ...defaultChartOptions.scales,
                    y: {
                        ...defaultChartOptions.scales.y,
                        ticks: {
                            ...defaultChartOptions.scales.y.ticks,
                            callback: function(value) {
                                return '$' + value.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
    };

    // ============================================
    // REVENUE BREAKDOWN CHART (Donut)
    // ============================================
    window.createRevenueBreakdownChart = function(elementId, revenueData) {
        const ctx = document.getElementById(elementId);
        if (!ctx) return;

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: revenueData.labels,
                datasets: [{
                    data: revenueData.values,
                    backgroundColor: [
                        '#088395',  // Retail Sales
                        '#06d6a0',  // Rentals
                        '#ff6b6b',  // Courses
                        '#f4a261',  // Trips
                        '#05bfdb'   // Air Fills
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'right',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 12,
                                family: "'Inter', 'Segoe UI', sans-serif"
                            },
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return {
                                            text: `${label}: ${percentage}%`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: $${value.toFixed(2)} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    };

    // ============================================
    // EQUIPMENT STATUS CHART (Bar)
    // ============================================
    window.createEquipmentStatusChart = function(elementId, statusData) {
        const ctx = document.getElementById(elementId);
        if (!ctx) return;

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: statusData.labels,
                datasets: [{
                    label: 'Equipment Count',
                    data: statusData.values,
                    backgroundColor: [
                        'rgba(6, 214, 160, 0.8)',    // Available
                        'rgba(255, 193, 7, 0.8)',     // Rented
                        'rgba(8, 131, 149, 0.8)',     // Maintenance
                        'rgba(239, 71, 111, 0.8)'     // Damaged
                    ],
                    borderColor: [
                        '#06d6a0',
                        '#ffc107',
                        '#088395',
                        '#ef476f'
                    ],
                    borderWidth: 2,
                    borderRadius: 8,
                    barThickness: 40
                }]
            },
            options: {
                ...defaultChartOptions,
                plugins: {
                    ...defaultChartOptions.plugins,
                    legend: {
                        display: false
                    }
                },
                scales: {
                    ...defaultChartOptions.scales,
                    y: {
                        ...defaultChartOptions.scales.y,
                        ticks: {
                            ...defaultChartOptions.scales.y.ticks,
                            stepSize: 1
                        }
                    }
                }
            }
        });
    };

    // ============================================
    // MULTI-LINE COMPARISON CHART
    // ============================================
    window.createComparisonChart = function(elementId, comparisonData) {
        const ctx = document.getElementById(elementId);
        if (!ctx) return;

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: comparisonData.labels,
                datasets: [
                    {
                        label: 'Current Period',
                        data: comparisonData.current,
                        borderColor: '#088395',
                        backgroundColor: 'rgba(8, 131, 149, 0.1)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Previous Period',
                        data: comparisonData.previous,
                        borderColor: '#cbd5e0',
                        backgroundColor: 'rgba(203, 213, 224, 0.1)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        tension: 0.4,
                        fill: false,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    }
                ]
            },
            options: {
                ...defaultChartOptions,
                scales: {
                    ...defaultChartOptions.scales,
                    y: {
                        ...defaultChartOptions.scales.y,
                        ticks: {
                            ...defaultChartOptions.scales.y.ticks,
                            callback: function(value) {
                                return '$' + value.toFixed(0);
                            }
                        }
                    }
                }
            }
        });
    };

    // ============================================
    // REFRESH DASHBOARD DATA (AJAX)
    // ============================================
    window.refreshDashboard = function() {
        const refreshButton = document.getElementById('refreshDashboard');
        if (refreshButton) {
            refreshButton.disabled = true;
            refreshButton.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Refreshing...';
        }

        // Add spin animation for refresh icon
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
            .spin { animation: spin 1s linear infinite; }
        `;
        document.head.appendChild(style);

        // Simulate AJAX request (replace with actual endpoint)
        setTimeout(() => {
            location.reload();
        }, 1000);
    };

    // ============================================
    // TIME RANGE FILTER
    // ============================================
    window.filterDashboardByRange = function(range) {
        // Update active button
        document.querySelectorAll('.range-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');

        // Add loading state to cards
        document.querySelectorAll('.metric-card').forEach(card => {
            card.style.opacity = '0.6';
        });

        // Simulate loading (replace with actual AJAX call)
        setTimeout(() => {
            window.location.href = `/dashboard?range=${range}`;
        }, 300);
    };

    // ============================================
    // EXPORT DASHBOARD
    // ============================================
    window.exportDashboard = function(format) {
        const url = `/dashboard/export?format=${format}`;
        window.location.href = url;
    };

    // ============================================
    // INITIALIZE ON DOM READY
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize animations
        initializeFadeIns();
        initializeCounters();

        // Add smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';

        // Initialize tooltips if Bootstrap is available
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        console.log('🌊 Nautilus V6 Dashboard Initialized');
    });

})();
