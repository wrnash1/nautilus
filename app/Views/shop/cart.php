<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Nautilus Dive Shop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/shop">
                <i class="bi bi-water"></i> Nautilus Dive Shop
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if (\App\Core\CustomerAuth::check()): ?>
                        <?php $customer = \App\Core\CustomerAuth::customer(); ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($customer['first_name']) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/account">Dashboard</a></li>
                                <li><a class="dropdown-item" href="/account/orders">My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="/account/logout" class="px-3">
                                        <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">Logout</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/account/login">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/account/register">Register</a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="/shop/cart" class="nav-link active">
                            <i class="bi bi-cart"></i> Cart 
                            <?php if ($cartTotals['item_count'] > 0): ?>
                                <span class="badge bg-danger"><?= $cartTotals['item_count'] ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h1 class="mb-4">Shopping Cart</h1>
        
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= htmlspecialchars($_SESSION['flash_success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (empty($cart)): ?>
            <div class="alert alert-info">
                <h4><i class="bi bi-cart-x"></i> Your cart is empty</h4>
                <p class="mb-0">Browse our products and add items to your cart.</p>
            </div>
            <a href="/shop" class="btn btn-primary">
                <i class="bi bi-shop"></i> Continue Shopping
            </a>
        <?php else: ?>
            <form method="POST" action="/shop/cart/update">
                <input type="hidden" name="csrf_token" value="<?= \App\Middleware\CsrfMiddleware::generateToken() ?>">
                
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th>Subtotal</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cart as $item): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($item['product_name']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($item['sku']) ?></small>
                                        </td>
                                        <td>$<?= number_format($item['price'], 2) ?></td>
                                        <td>
                                            <input type="number" class="form-control" style="width: 80px;" 
                                                   name="quantities[<?= $item['product_id'] ?>]" 
                                                   value="<?= $item['quantity'] ?>" 
                                                   min="0" max="99">
                                        </td>
                                        <td>$<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                                        <td>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                    onclick="this.form.elements['quantities[<?= $item['product_id'] ?>]'].value=0">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Update Cart
                            </button>
                            <a href="/shop" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left"></i> Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </form>

            <div class="row">
                <div class="col-md-8"></div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Cart Totals</h5>
                        </div>
                        <div class="card-body">
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
                                <span>Tax (7%):</span>
                                <strong>$<?= number_format($cartTotals['tax'], 2) ?></strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total:</strong>
                                <strong class="text-primary h4">$<?= number_format($cartTotals['total'], 2) ?></strong>
                            </div>
                            
                            <?php if ($cartTotals['subtotal'] < 100): ?>
                                <p class="text-muted small">
                                    <i class="bi bi-info-circle"></i> Free shipping on orders over $100
                                </p>
                            <?php endif; ?>
                            
                            <div class="d-grid">
                                <a href="/shop/checkout" class="btn btn-primary btn-lg">
                                    Proceed to Checkout <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
