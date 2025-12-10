<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #ffc107; color: #000; padding: 20px; text-align: center; }
        .content { background: #f8f9fa; padding: 30px; }
        .alert-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 20px 0; }
        .product-details { background: white; padding: 15px; margin: 15px 0; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Low Stock Alert</h1>
        </div>
        <div class="content">
            <div class="alert-box">
                <h2>Product Running Low on Stock</h2>

                <div class="product-details">
                    <p><strong>Product:</strong> <?= htmlspecialchars($product_name) ?></p>
                    <p><strong>SKU:</strong> <?= htmlspecialchars($sku) ?></p>
                    <p><strong>Current Stock:</strong> <?= $current_stock ?> units</p>
                    <p><strong>Low Stock Threshold:</strong> <?= $threshold ?> units</p>
                </div>

                <p><strong>Action Required:</strong> Please review inventory and consider reordering this product.</p>
            </div>
        </div>
        <div class="footer">
            <p>&copy; <?= date('Y') ?> Nautilus Dive Shop - Inventory Management System</p>
        </div>
    </div>
</body>
</html>
