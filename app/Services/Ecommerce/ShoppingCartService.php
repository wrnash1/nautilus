<?php

namespace App\Services\Ecommerce;

class ShoppingCartService
{
    public function getCart(): array
    {
        if (!isset($_SESSION['shopping_cart'])) {
            $_SESSION['shopping_cart'] = [];
        }
        return $_SESSION['shopping_cart'];
    }
    
    public function addItem(int $productId, string $productName, string $sku, float $price, int $quantity = 1): void
    {
        $cart = $this->getCart();
        
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'product_id' => $productId,
                'product_name' => $productName,
                'sku' => $sku,
                'price' => $price,
                'quantity' => $quantity
            ];
        }
        
        $_SESSION['shopping_cart'] = $cart;
    }
    
    public function updateQuantity(int $productId, int $quantity): void
    {
        $cart = $this->getCart();
        
        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId]['quantity'] = $quantity;
        }
        
        $_SESSION['shopping_cart'] = $cart;
    }
    
    public function removeItem(int $productId): void
    {
        $cart = $this->getCart();
        unset($cart[$productId]);
        $_SESSION['shopping_cart'] = $cart;
    }
    
    public function clearCart(): void
    {
        $_SESSION['shopping_cart'] = [];
    }
    
    public function getCartTotal(): array
    {
        $cart = $this->getCart();
        $subtotal = 0;
        $itemCount = 0;
        
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
            $itemCount += $item['quantity'];
        }
        
        $shipping = $subtotal > 100 ? 0 : 10;
        $tax = $subtotal * 0.07;
        $total = $subtotal + $shipping + $tax;
        
        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => $total,
            'item_count' => $itemCount
        ];
    }
}
