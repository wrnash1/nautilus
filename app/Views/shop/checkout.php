<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Nautilus Dive Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/shop">
                <i class="bi bi-water"></i> Nautilus Dive Shop
            </a>
        </div>
    </nav>

    <div class="container my-5">
        <h1 class="mb-4">Checkout</h1>
        
        <div class="row">
            <div class="col-md-8">
                <form method="POST" action="/shop/checkout">
                    <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                    <input type="hidden" name="customer_id" value="1">
                    
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-truck"></i> Shipping Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label for="shipping_address" class="form-label">Address *</label>
                                    <input type="text" class="form-control" id="shipping_address" name="shipping_address" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="shipping_city" class="form-label">City *</label>
                                    <input type="text" class="form-control" id="shipping_city" name="shipping_city" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="shipping_state" class="form-label">State *</label>
                                    <input type="text" class="form-control" id="shipping_state" name="shipping_state" maxlength="2" required>
                                </div>
                                <div class="col-md-3">
                                    <label for="shipping_zip" class="form-label">ZIP *</label>
                                    <input type="text" class="form-control" id="shipping_zip" name="shipping_zip" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-credit-card"></i> Billing Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="same_as_shipping" checked>
                                <label class="form-check-label" for="same_as_shipping">
                                    Same as shipping address
                                </label>
                            </div>
                            
                            <div id="billing_fields" style="display: none;">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="billing_address" class="form-label">Address</label>
                                        <input type="text" class="form-control" id="billing_address" name="billing_address">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="billing_city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="billing_city" name="billing_city">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="billing_state" class="form-label">State</label>
                                        <input type="text" class="form-control" id="billing_state" name="billing_state" maxlength="2">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="billing_zip" class="form-label">ZIP</label>
                                        <input type="text" class="form-control" id="billing_zip" name="billing_zip">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="bi bi-credit-card-2-front"></i> Payment Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> This is a demo checkout. Payment processing is simulated.
                            </div>
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label">Card Number</label>
                                    <input type="text" class="form-control" placeholder="4242 4242 4242 4242" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Expiration</label>
                                    <input type="text" class="form-control" placeholder="MM/YY" disabled>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">CVV</label>
                                    <input type="text" class="form-control" placeholder="123" disabled>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="/shop/cart" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Cart
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            Place Order <i class="bi bi-check-circle"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($cart as $item): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= htmlspecialchars($item['product_name']) ?> × <?= $item['quantity'] ?></span>
                            <span>$<?= number_format($item['price'] * $item['quantity'], 2) ?></span>
                        </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong>$<?= number_format($cartTotals['subtotal'], 2) ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <strong>
                                <?php if ($cartTotals['shipping'] == 0): ?>
                                    <span class="text-success">FREE</span>
                                <?php else: ?>
                                    $<?= number_format($cartTotals['shipping'], 2) ?>
                                <?php endif; ?>
                            </strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <strong>$<?= number_format($cartTotals['tax'], 2) ?></strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong class="text-primary h4">$<?= number_format($cartTotals['total'], 2) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('same_as_shipping').addEventListener('change', function() {
            const billingFields = document.getElementById('billing_fields');
            if (this.checked) {
                billingFields.style.display = 'none';
                document.querySelectorAll('#billing_fields input').forEach(input => {
                    input.removeAttribute('required');
                });
            } else {
                billingFields.style.display = 'block';
                document.querySelectorAll('#billing_fields input').forEach(input => {
                    input.setAttribute('required', 'required');
                });
            }
        });
    </script>
</body>
</html>
