<?php
$pageTitle = 'Add Product';
$activeMenu = 'products';

ob_start();
?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="/store/products">Products</a></li>
        <li class="breadcrumb-item active">Add Product</li>
    </ol>
</nav>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-box-seam"></i> Add Product</h2>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/store/products">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="sku" class="form-label">SKU *</label>
                        <input type="text" class="form-control" id="sku" name="sku" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-select" id="category_id" name="category_id">
                            <option value="">Select Category...</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="vendor_id" class="form-label">Vendor</label>
                        <select class="form-select" id="vendor_id" name="vendor_id">
                            <option value="">Select Vendor...</option>
                            <?php foreach ($vendors as $vendor): ?>
                            <option value="<?= $vendor['id'] ?>"><?= htmlspecialchars($vendor['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="cost_price" class="form-label">Cost Price *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="cost_price" name="cost_price" 
                                   step="0.01" min="0" value="0.00" required>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="retail_price" class="form-label">Retail Price *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="retail_price" name="retail_price" 
                                   step="0.01" min="0" value="0.00" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="barcode" class="form-label">Barcode</label>
                        <input type="text" class="form-control" id="barcode" name="barcode">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="qr_code" class="form-label">QR Code</label>
                        <input type="text" class="form-control" id="qr_code" name="qr_code">
                        <small class="form-text text-muted">QR code for customer website scanning</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="weight" class="form-label">Weight</label>
                        <input type="number" class="form-control" id="weight" name="weight"
                               step="0.01" min="0">
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="mb-3">
                        <label for="weight_unit" class="form-label">Unit</label>
                        <select class="form-select" id="weight_unit" name="weight_unit">
                            <option value="lb">lb</option>
                            <option value="kg">kg</option>
                            <option value="oz">oz</option>
                            <option value="g">g</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="dimensions" class="form-label">Dimensions</label>
                        <input type="text" class="form-control" id="dimensions" name="dimensions"
                               placeholder="e.g., 10 x 8 x 6 inches">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="color" class="form-label">Color</label>
                        <input type="text" class="form-control" id="color" name="color">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="material" class="form-label">Material</label>
                        <input type="text" class="form-control" id="material" name="material">
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="manufacturer" class="form-label">Manufacturer</label>
                        <input type="text" class="form-control" id="manufacturer" name="manufacturer">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="warranty_info" class="form-label">Warranty Information</label>
                <textarea class="form-control" id="warranty_info" name="warranty_info" rows="2"></textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="location_in_store" class="form-label">Location in Store</label>
                        <input type="text" class="form-control" id="location_in_store" name="location_in_store"
                               placeholder="e.g., Aisle 3, Shelf B">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="expiration_date" class="form-label">Expiration Date</label>
                        <input type="date" class="form-control" id="expiration_date" name="expiration_date">
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <label for="supplier_info" class="form-label">Supplier Information</label>
                <textarea class="form-control" id="supplier_info" name="supplier_info" rows="2"></textarea>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="stock_quantity" class="form-label">Stock Quantity</label>
                        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity"
                               min="0" value="0">
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="low_stock_threshold" class="form-label">Low Stock Threshold</label>
                        <input type="number" class="form-control" id="low_stock_threshold" name="low_stock_threshold" 
                               min="0" value="5">
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="track_inventory" name="track_inventory" checked>
                    <label class="form-check-label" for="track_inventory">
                        Track Inventory
                    </label>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                    <label class="form-check-label" for="is_active">
                        Active
                    </label>
                </div>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Create Product
                </button>
                <a href="/store/products" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/app.php';
?>
