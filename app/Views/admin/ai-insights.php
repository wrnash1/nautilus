<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Inventory Insights - Nautilus</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .header h1 {
            color: #667eea;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
            font-size: 1.1em;
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
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-card .number {
            font-size: 3em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        .stat-card .label {
            color: #666;
            font-size: 1.1em;
        }
        .urgency-high { color: #e74c3c; }
        .urgency-medium { color: #f39c12; }
        .urgency-normal { color: #27ae60; }
        .recommendations {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .recommendations h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.8em;
        }
        .recommendation-item {
            padding: 20px;
            border-left: 4px solid #667eea;
            background: #f8f9fa;
            margin-bottom: 15px;
            border-radius: 8px;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 20px;
            align-items: center;
        }
        .recommendation-item.high {
            border-left-color: #e74c3c;
            background: #fee;
        }
        .recommendation-item.medium {
            border-left-color: #f39c12;
            background: #ffe;
        }
        .product-name {
            font-weight: bold;
            font-size: 1.1em;
        }
        .product-sku {
            color: #999;
            font-size: 0.9em;
        }
        .metric {
            text-align: center;
        }
        .metric-value {
            font-size: 1.5em;
            font-weight: bold;
            color: #667eea;
        }
        .metric-label {
            font-size: 0.9em;
            color: #666;
        }
        .loading {
            text-align: center;
            padding: 50px;
            color: white;
            font-size: 1.5em;
        }
        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .btn {
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.1em;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
        .seasonal-chart {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🤖 AI Inventory Insights</h1>
            <p>Machine learning-powered demand forecasting and inventory recommendations</p>
        </div>

        <div id="loading" class="loading">
            <div class="spinner"></div>
            <p>Analyzing sales data and generating recommendations...</p>
        </div>

        <div id="content" style="display: none;">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="number" id="total-recommendations">0</div>
                    <div class="label">Products Need Restock</div>
                </div>
                <div class="stat-card">
                    <div class="number urgency-high" id="urgent-items">0</div>
                    <div class="label">Urgent Orders</div>
                </div>
                <div class="stat-card">
                    <div class="number" id="peak-month">Loading...</div>
                    <div class="label">Peak Season Month</div>
                </div>
            </div>

            <div class="recommendations">
                <h2>📦 Restock Recommendations</h2>
                <div id="recommendations-list"></div>
            </div>

            <div class="seasonal-chart">
                <h2 style="color: #667eea; margin-bottom: 20px;">📊 Top Selling Products (Last 30 Days)</h2>
                <div id="top-products"></div>
            </div>
        </div>
    </div>

    <script>
        // Fetch AI insights
        fetch('/api/ai/insights')
            .then(response => response.json())
            .then(data => {
                displayInsights(data.data);
                document.getElementById('loading').style.display = 'none';
                document.getElementById('content').style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching AI insights:', error);
                document.getElementById('loading').innerHTML = '<p style="color: #e74c3c;">Error loading AI insights. Please check console.</p>';
            });

        function displayInsights(data) {
            // Update stats
            document.getElementById('total-recommendations').textContent = data.summary.products_needing_restock;
            document.getElementById('urgent-items').textContent = data.summary.urgent_items;
            document.getElementById('peak-month').textContent = data.summary.peak_season_month;

            // Display recommendations
            const recommendationsList = document.getElementById('recommendations-list');
            if (data.reorder_recommendations.length === 0) {
                recommendationsList.innerHTML = '<p style="text-align: center; padding: 30px; color: #27ae60;">✅ All inventory levels look good!</p>';
            } else {
                recommendationsList.innerHTML = data.reorder_recommendations.map(rec => `
                    <div class="recommendation-item ${rec.urgency}">
                        <div>
                            <div class="product-name">${rec.product_name}</div>
                            <div class="product-sku">SKU: ${rec.sku}</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">${rec.current_stock}</div>
                            <div class="metric-label">Current Stock</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value urgency-${rec.urgency}">${rec.days_until_stockout}</div>
                            <div class="metric-label">Days Until Out</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">${rec.recommended_quantity}</div>
                            <div class="metric-label">Order Quantity</div>
                        </div>
                    </div>
                `).join('');
            }

            // Display top products
            const topProducts = document.getElementById('top-products');
            topProducts.innerHTML = data.top_products.map((product, index) => `
                <div style="padding: 15px; background: ${index % 2 === 0 ? '#f8f9fa' : 'white'}; border-radius: 8px; margin-bottom: 10px; display: grid; grid-template-columns: 50px 2fr 1fr 1fr 1fr; gap: 20px; align-items: center;">
                    <div style="font-size: 1.5em; font-weight: bold; color: #667eea;">#${index + 1}</div>
                    <div>
                        <div style="font-weight: bold;">${product.name}</div>
                        <div style="color: #999; font-size: 0.9em;">${product.sku}</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.3em; font-weight: bold;">${product.total_sold}</div>
                        <div style="font-size: 0.9em; color: #666;">Units Sold</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.3em; font-weight: bold; color: #27ae60;">$${parseFloat(product.total_revenue).toFixed(2)}</div>
                        <div style="font-size: 0.9em; color: #666;">Revenue</div>
                    </div>
                    <div style="text-align: center;">
                        <div style="font-size: 1.3em; font-weight: bold;">${product.days_sold}</div>
                        <div style="font-size: 0.9em; color: #666;">Days Active</div>
                    </div>
                </div>
            `).join('');
        }
    </script>
</body>
</html>
