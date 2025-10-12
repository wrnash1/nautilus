<?php

namespace App\Controllers\Shop;

use App\Models\Product;
use App\Services\Ecommerce\ShoppingCartService;
use App\Services\Ecommerce\OrderService;

class ShopController
{
    private ShoppingCartService $cartService;
    private OrderService $orderService;
    
    public function __construct()
    {
        $this->cartService = new ShoppingCartService();
        $this->orderService = new OrderService();
    }
    
    public function index()
    {
        $products = Product::all(50, 0);
        $cartTotals = $this->cartService->getCartTotal();
        
        $pageTitle = 'Shop';
        require_once __DIR__ . '/../../Views/shop/index.php';
    }
    
    public function productDetail(int $id)
    {
        $product = Product::find($id);
        $cartTotals = $this->cartService->getCartTotal();
        
        if (!$product) {
            header('Location: /shop');
            exit;
        }
        
        $pageTitle = $product['name'];
        require_once __DIR__ . '/../../Views/shop/product.php';
    }
    
    public function addToCart()
    {
        $productId = (int)$_POST['product_id'];
        $quantity = (int)($_POST['quantity'] ?? 1);
        
        $product = Product::find($productId);
        
        if ($product) {
            $this->cartService->addItem(
                $product['id'],
                $product['name'],
                $product['sku'],
                $product['retail_price'],
                $quantity
            );
            
            $_SESSION['flash_success'] = 'Product added to cart!';
        }
        
        header('Location: /shop/cart');
        exit;
    }
    
    public function cart()
    {
        $cart = $this->cartService->getCart();
        $cartTotals = $this->cartService->getCartTotal();
        
        $pageTitle = 'Shopping Cart';
        require_once __DIR__ . '/../../Views/shop/cart.php';
    }
    
    public function updateCart()
    {
        foreach ($_POST['quantities'] ?? [] as $productId => $quantity) {
            $this->cartService->updateQuantity((int)$productId, (int)$quantity);
        }
        
        $_SESSION['flash_success'] = 'Cart updated!';
        header('Location: /shop/cart');
        exit;
    }
    
    public function checkout()
    {
        $cart = $this->cartService->getCart();
        $cartTotals = $this->cartService->getCartTotal();
        
        if (empty($cart)) {
            header('Location: /shop/cart');
            exit;
        }
        
        $pageTitle = 'Checkout';
        require_once __DIR__ . '/../../Views/shop/checkout.php';
    }
    
    public function processCheckout()
    {
        $cart = $this->cartService->getCart();
        $cartTotals = $this->cartService->getCartTotal();
        
        $orderData = [
            'customer_id' => $_POST['customer_id'] ?? 1,
            'order_type' => 'online',
            'subtotal' => $cartTotals['subtotal'],
            'shipping' => $cartTotals['shipping'],
            'tax' => $cartTotals['tax'],
            'total' => $cartTotals['total'],
            'shipping_address_line1' => $_POST['shipping_address'] ?? '',
            'shipping_city' => $_POST['shipping_city'] ?? '',
            'shipping_state' => $_POST['shipping_state'] ?? '',
            'shipping_postal_code' => $_POST['shipping_zip'] ?? '',
            'billing_address_line1' => $_POST['billing_address'] ?? '',
            'billing_city' => $_POST['billing_city'] ?? '',
            'billing_state' => $_POST['billing_state'] ?? '',
            'billing_postal_code' => $_POST['billing_zip'] ?? '',
            'items' => []
        ];
        
        foreach ($cart as $item) {
            $orderData['items'][] = [
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'sku' => $item['sku'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'total' => $item['price'] * $item['quantity']
            ];
        }
        
        $orderId = $this->orderService->createOrder($orderData);
        
        $this->orderService->updatePaymentStatus($orderId, 'paid');
        
        $this->cartService->clearCart();
        
        $_SESSION['flash_success'] = 'Order placed successfully!';
        header('Location: /orders/' . $orderId);
        exit;
    }
}
