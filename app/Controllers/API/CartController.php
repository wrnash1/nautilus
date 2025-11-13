<?php

namespace App\Controllers\API;

use App\Core\Controller;
use App\Core\Database;
use App\Services\Ecommerce\StorefrontService;

class CartController extends Controller
{
    private StorefrontService $storefront;

    public function __construct()
    {
        parent::__construct();
        $this->storefront = new StorefrontService();
    }

    /**
     * Add item to cart
     */
    public function add(): void
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $productId = $data['product_id'] ?? 0;
            $quantity = $data['quantity'] ?? 1;

            if (!$productId || $quantity < 1) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid product or quantity'
                ]);
                return;
            }

            $sessionId = session_id();
            $customerId = $_SESSION['customer_id'] ?? null;

            $result = $this->storefront->addToCart($sessionId, $productId, $quantity, $customerId);

            echo json_encode($result);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Error adding to cart: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get cart count
     */
    public function count(): void
    {
        header('Content-Type: application/json');

        try {
            $sessionId = session_id();
            $customerId = $_SESSION['customer_id'] ?? null;

            $cart = $this->storefront->getCart($sessionId, $customerId);
            $count = array_sum(array_column($cart['items'] ?? [], 'quantity'));

            echo json_encode([
                'success' => true,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'count' => 0
            ]);
        }
    }

    /**
     * Get full cart
     */
    public function get(): void
    {
        header('Content-Type: application/json');

        try {
            $sessionId = session_id();
            $customerId = $_SESSION['customer_id'] ?? null;

            $cart = $this->storefront->getCart($sessionId, $customerId);

            echo json_encode([
                'success' => true,
                'cart' => $cart
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(): void
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $cartItemId = $data['cart_item_id'] ?? 0;
            $quantity = $data['quantity'] ?? 1;

            if (!$cartItemId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid cart item'
                ]);
                return;
            }

            $result = $this->storefront->updateCartItem($cartItemId, $quantity);

            echo json_encode($result);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove item from cart
     */
    public function remove(): void
    {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $cartItemId = $data['cart_item_id'] ?? 0;

            if (!$cartItemId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid cart item'
                ]);
                return;
            }

            $result = $this->storefront->removeFromCart($cartItemId);

            echo json_encode($result);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clear entire cart
     */
    public function clear(): void
    {
        header('Content-Type: application/json');

        try {
            $sessionId = session_id();
            $customerId = $_SESSION['customer_id'] ?? null;

            // Delete cart
            if ($customerId) {
                Database::query("DELETE FROM shopping_carts WHERE customer_id = ?", [$customerId]);
            } else {
                Database::query("DELETE FROM shopping_carts WHERE session_id = ?", [$sessionId]);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Cart cleared'
            ]);

        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
