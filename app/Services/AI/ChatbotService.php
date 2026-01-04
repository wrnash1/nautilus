<?php

namespace App\Services\AI;

use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Core\Logger;

/**
 * AI Chatbot Service
 *
 * Intelligent customer support chatbot with natural language processing
 */
class ChatbotService
{
    private Logger $logger;
    private array $intents;
    private array $entities;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->initializeIntents();
        $this->initializeEntities();
    }

    /**
     * Process user message and generate response
     */
    public function processMessage(string $message, array $context = []): array
    {
        try {
            // Clean and normalize message
            $normalizedMessage = $this->normalizeMessage($message);

            // Detect intent
            $intent = $this->detectIntent($normalizedMessage);

            // Extract entities
            $entities = $this->extractEntities($normalizedMessage);

            // Generate response based on intent
            $response = $this->generateResponse($intent, $entities, $context);

            // Log conversation
            $this->logConversation($message, $response, $intent, $context);

            return [
                'success' => true,
                'response' => $response['message'],
                'intent' => $intent,
                'entities' => $entities,
                'confidence' => $response['confidence'],
                'suggestions' => $response['suggestions'] ?? [],
                'requires_human' => $response['requires_human'] ?? false
            ];

        } catch (\Exception $e) {
            $this->logger->error('Chatbot processing failed', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'response' => "I apologize, but I'm having trouble processing your request. Let me connect you with a human agent.",
                'requires_human' => true
            ];
        }
    }

    /**
     * Initialize intent patterns
     */
    private function initializeIntents(): void
    {
        $this->intents = [
            'greeting' => [
                'patterns' => ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'greetings'],
                'confidence' => 0.9
            ],
            'order_status' => [
                'patterns' => ['order status', 'track order', 'where is my order', 'order tracking', 'check order'],
                'confidence' => 0.85
            ],
            'product_inquiry' => [
                'patterns' => ['what is', 'tell me about', 'do you have', 'looking for', 'need', 'want to buy'],
                'confidence' => 0.8
            ],
            'price_inquiry' => [
                'patterns' => ['how much', 'price', 'cost', 'expensive', 'cheap'],
                'confidence' => 0.85
            ],
            'course_inquiry' => [
                'patterns' => ['course', 'class', 'certification', 'training', 'lesson', 'learn to dive'],
                'confidence' => 0.85
            ],
            'hours_location' => [
                'patterns' => ['hours', 'open', 'close', 'location', 'address', 'where are you'],
                'confidence' => 0.9
            ],
            'equipment_rental' => [
                'patterns' => ['rent', 'rental', 'hire', 'borrow', 'equipment'],
                'confidence' => 0.85
            ],
            'return_refund' => [
                'patterns' => ['return', 'refund', 'exchange', 'send back', 'not happy'],
                'confidence' => 0.85
            ],
            'technical_support' => [
                'patterns' => ['not working', 'broken', 'issue', 'problem', 'help with', 'support'],
                'confidence' => 0.8
            ],
            'farewell' => [
                'patterns' => ['bye', 'goodbye', 'thanks', 'thank you', 'have a good'],
                'confidence' => 0.9
            ]
        ];
    }

    /**
     * Initialize entity extraction patterns
     */
    private function initializeEntities(): void
    {
        $this->entities = [
            'order_number' => '/\b(TXN|ORD|INV)-?\d{8,12}\b/i',
            'product_name' => '/\b(regulator|mask|fins|wetsuit|bcd|tank|dive computer|snorkel)\b/i',
            'certification_level' => '/\b(open water|advanced|rescue|divemaster|instructor)\b/i',
            'price_range' => '/\$?\d+(?:\.\d{2})?(?:\s*-\s*\$?\d+(?:\.\d{2})?)?/i',
            'date' => '/\b\d{1,2}\/\d{1,2}\/\d{2,4}\b/i'
        ];
    }

    /**
     * Normalize message for processing
     */
    private function normalizeMessage(string $message): string
    {
        $normalized = strtolower(trim($message));
        $normalized = preg_replace('/[^a-z0-9\s\-\.\/\$]/', '', $normalized);
        return $normalized;
    }

    /**
     * Detect user intent from message
     */
    private function detectIntent(string $message): array
    {
        $scores = [];

        foreach ($this->intents as $intent => $config) {
            $score = 0;
            foreach ($config['patterns'] as $pattern) {
                if (strpos($message, $pattern) !== false) {
                    $score = max($score, $config['confidence']);
                }
            }
            $scores[$intent] = $score;
        }

        arsort($scores);
        $topIntent = array_key_first($scores);
        $confidence = $scores[$topIntent];

        return [
            'name' => $topIntent,
            'confidence' => $confidence,
            'fallback' => $confidence < 0.5
        ];
    }

    /**
     * Extract entities from message
     */
    private function extractEntities(string $message): array
    {
        $extracted = [];

        foreach ($this->entities as $entityType => $pattern) {
            if (preg_match_all($pattern, $message, $matches)) {
                $extracted[$entityType] = $matches[0];
            }
        }

        return $extracted;
    }

    /**
     * Generate response based on intent
     */
    private function generateResponse(array $intent, array $entities, array $context): array
    {
        $intentName = $intent['name'];

        // Low confidence - ask for clarification
        if ($intent['fallback']) {
            return [
                'message' => "I'm not quite sure I understand. Could you please rephrase that? I can help you with:\n" .
                            "• Order tracking\n" .
                            "• Product information\n" .
                            "• Course inquiries\n" .
                            "• Equipment rentals\n" .
                            "• Store hours and location",
                'confidence' => $intent['confidence'],
                'suggestions' => ['Check order status', 'Browse products', 'View courses']
            ];
        }

        return match($intentName) {
            'greeting' => $this->handleGreeting($context),
            'order_status' => $this->handleOrderStatus($entities, $context),
            'product_inquiry' => $this->handleProductInquiry($entities),
            'price_inquiry' => $this->handlePriceInquiry($entities),
            'course_inquiry' => $this->handleCourseInquiry($entities),
            'hours_location' => $this->handleHoursLocation(),
            'equipment_rental' => $this->handleEquipmentRental($entities),
            'return_refund' => $this->handleReturnRefund($entities, $context),
            'technical_support' => $this->handleTechnicalSupport($entities),
            'farewell' => $this->handleFarewell(),
            default => [
                'message' => "I can help you with that! What specifically would you like to know?",
                'confidence' => 0.5
            ]
        };
    }

    /**
     * Handle greeting intent
     */
    private function handleGreeting(array $context): array
    {
        $greetings = [
            "Hello! Welcome to Nautilus Dive Shop. How can I help you today?",
            "Hi there! I'm here to help with your diving needs. What can I assist you with?",
            "Greetings! How can I make your diving experience better today?"
        ];

        $message = $greetings[array_rand($greetings)];

        if (!empty($context['customer_name'])) {
            $message = "Hello, {$context['customer_name']}! " . $message;
        }

        return [
            'message' => $message,
            'confidence' => 0.95,
            'suggestions' => ['Check my orders', 'Browse products', 'Find a course']
        ];
    }

    /**
     * Handle order status inquiries
     */
    private function handleOrderStatus(array $entities, array $context): array
    {
        if (empty($entities['order_number']) && empty($context['customer_id'])) {
            return [
                'message' => "I'd be happy to check your order status! Could you please provide your order number? It usually starts with 'TXN-' or 'ORD-'.",
                'confidence' => 0.8,
                'requires_input' => 'order_number'
            ];
        }

        // Look up order
        if (!empty($entities['order_number'])) {
            $orderNumber = $entities['order_number'][0];
            $order = $this->lookupOrder($orderNumber);

            if ($order) {
                return [
                    'message' => "I found your order {$orderNumber}!\n\n" .
                                "Status: {$order['status']}\n" .
                                "Date: {$order['date']}\n" .
                                "Total: \${$order['total']}\n\n" .
                                "Is there anything else you'd like to know about this order?",
                    'confidence' => 0.9,
                    'data' => $order
                ];
            } else {
                return [
                    'message' => "I couldn't find an order with number {$orderNumber}. Please double-check the order number, or I can connect you with a human agent for assistance.",
                    'confidence' => 0.7,
                    'suggestions' => ['Try another order number', 'Talk to agent']
                ];
            }
        }

        // Show recent orders for logged-in customer
        if (!empty($context['customer_id'])) {
            $orders = $this->getCustomerRecentOrders($context['customer_id']);

            if (count($orders) > 0) {
                $orderList = "Here are your recent orders:\n\n";
                foreach (array_slice($orders, 0, 3) as $order) {
                    $orderList .= "• {$order['transaction_number']} - {$order['date']} - \${$order['total']} ({$order['status']})\n";
                }
                $orderList .= "\nWhich order would you like to know more about?";

                return [
                    'message' => $orderList,
                    'confidence' => 0.85,
                    'data' => $orders
                ];
            }
        }

        return [
            'message' => "I don't see any orders associated with your account yet. Would you like to browse our products?",
            'confidence' => 0.7,
            'suggestions' => ['Browse products', 'View courses']
        ];
    }

    /**
     * Handle product inquiries
     */
    private function handleProductInquiry(array $entities): array
    {
        if (!empty($entities['product_name'])) {
            $productName = $entities['product_name'][0];
            $products = $this->searchProducts($productName);

            if (count($products) > 0) {
                $response = "I found these products related to '{$productName}':\n\n";
                foreach (array_slice($products, 0, 5) as $product) {
                    $response .= "• {$product['name']} - \${$product['price']}";
                    $response .= $product['stock'] > 0 ? " (In stock)\n" : " (Out of stock)\n";
                }
                $response .= "\nWould you like more details about any of these?";

                return [
                    'message' => $response,
                    'confidence' => 0.85,
                    'data' => $products,
                    'suggestions' => ['Add to cart', 'Compare products', 'Talk to expert']
                ];
            }
        }

        return [
            'message' => "I'd be happy to help you find the perfect diving equipment! What specific product are you looking for? We have regulators, masks, fins, wetsuits, BCDs, and much more.",
            'confidence' => 0.75,
            'suggestions' => ['Browse all products', 'Talk to expert']
        ];
    }

    /**
     * Handle price inquiries
     */
    private function handlePriceInquiry(array $entities): array
    {
        if (!empty($entities['product_name'])) {
            return $this->handleProductInquiry($entities);
        }

        return [
            'message' => "Our prices are competitive and we offer great value for quality diving equipment! Which product would you like to know the price for?",
            'confidence' => 0.7,
            'suggestions' => ['Browse products', 'View price ranges']
        ];
    }

    /**
     * Handle course inquiries
     */
    private function handleCourseInquiry(array $entities): array
    {
        $courses = $this->getUpcomingCourses();

        if (!empty($entities['certification_level'])) {
            $level = $entities['certification_level'][0];
            $courses = array_filter($courses, function($course) use ($level) {
                return stripos($course['title'], $level) !== false;
            });
        }

        if (count($courses) > 0) {
            $response = "Here are our upcoming diving courses:\n\n";
            foreach (array_slice($courses, 0, 5) as $course) {
                $response .= "• {$course['title']}\n";
                $response .= "  Start Date: {$course['start_date']}\n";
                $response .= "  Price: \${$course['price']}\n";
                $response .= "  Spots Available: {$course['spots_available']}\n\n";
            }
            $response .= "Would you like to enroll in any of these courses?";

            return [
                'message' => $response,
                'confidence' => 0.9,
                'data' => $courses,
                'suggestions' => ['Enroll now', 'Get more details', 'Talk to instructor']
            ];
        }

        return [
            'message' => "We offer a variety of diving certifications from beginner to advanced levels! Let me connect you with one of our instructors who can help you choose the right course.",
            'confidence' => 0.7,
            'requires_human' => true
        ];
    }

    /**
     * Handle hours and location inquiries
     */
    private function handleHoursLocation(): array
    {
        $tenantInfo = $this->getTenantInfo();

        return [
            'message' => "Here's our store information:\n\n" .
                        "📍 Location: {$tenantInfo['address']}\n" .
                        "🕐 Hours: {$tenantInfo['hours']}\n" .
                        "📞 Phone: {$tenantInfo['phone']}\n" .
                        "📧 Email: {$tenantInfo['email']}\n\n" .
                        "We'd love to see you!",
            'confidence' => 0.95,
            'suggestions' => ['Get directions', 'Call us', 'Visit website']
        ];
    }

    /**
     * Handle equipment rental inquiries
     */
    private function handleEquipmentRental(array $entities): array
    {
        $equipment = $this->getAvailableRentalEquipment();

        if (count($equipment) > 0) {
            $response = "We have rental equipment available! Here's what we offer:\n\n";
            foreach ($equipment as $item) {
                $response .= "• {$item['name']} - \${$item['daily_rate']}/day\n";
            }
            $response .= "\nWould you like to reserve any equipment?";

            return [
                'message' => $response,
                'confidence' => 0.85,
                'data' => $equipment,
                'suggestions' => ['Reserve equipment', 'Check availability', 'Talk to staff']
            ];
        }

        return [
            'message' => "We offer equipment rentals! Let me connect you with our rental desk to check current availability and make a reservation.",
            'confidence' => 0.75,
            'requires_human' => true
        ];
    }

    /**
     * Handle return and refund inquiries
     */
    private function handleReturnRefund(array $entities, array $context): array
    {
        return [
            'message' => "I understand you'd like to make a return or get a refund. Our return policy allows returns within 30 days with receipt.\n\n" .
                        "To process your return, I'll need to connect you with our customer service team. They'll help you with:\n" .
                        "• Return authorization\n" .
                        "• Refund processing\n" .
                        "• Exchange options\n\n" .
                        "Would you like me to create a support ticket for you?",
            'confidence' => 0.8,
            'requires_human' => true,
            'suggestions' => ['Create support ticket', 'View return policy']
        ];
    }

    /**
     * Handle technical support
     */
    private function handleTechnicalSupport(array $entities): array
    {
        return [
            'message' => "I'm sorry you're experiencing technical issues. For technical support, I recommend:\n\n" .
                        "1. Check our troubleshooting guide\n" .
                        "2. Watch our instructional videos\n" .
                        "3. Connect with our technical support team\n\n" .
                        "Would you like me to connect you with a technician?",
            'confidence' => 0.75,
            'requires_human' => true,
            'suggestions' => ['View troubleshooting guide', 'Talk to technician']
        ];
    }

    /**
     * Handle farewell
     */
    private function handleFarewell(): array
    {
        return [
            'message' => "Thank you for chatting with me! If you need anything else, I'm here to help. Have a great day and happy diving! 🤿",
            'confidence' => 0.95
        ];
    }

    // Helper methods for database lookups

    private function lookupOrder(string $orderNumber): ?array
    {
        return TenantDatabase::fetchOneTenant(
            "SELECT transaction_number, transaction_date as date, total_amount as total, status
             FROM transactions
             WHERE transaction_number = ?",
            [$orderNumber]
        );
    }

    private function getCustomerRecentOrders(int $customerId): array
    {
        return TenantDatabase::fetchAllTenant(
            "SELECT transaction_number, transaction_date as date, total_amount as total, status
             FROM transactions
             WHERE customer_id = ?
             ORDER BY transaction_date DESC
             LIMIT 5",
            [$customerId]
        ) ?? [];
    }

    private function searchProducts(string $query): array
    {
        return TenantDatabase::fetchAllTenant(
            "SELECT id, name, price, stock_quantity as stock
             FROM products
             WHERE name LIKE ? OR description LIKE ?
             AND is_active = 1
             LIMIT 10",
            ["%{$query}%", "%{$query}%"]
        ) ?? [];
    }

    private function getUpcomingCourses(): array
    {
        return TenantDatabase::fetchAllTenant(
            "SELECT c.id, c.title, c.start_date, c.price,
                    (c.max_participants - COUNT(e.id)) as spots_available
             FROM courses c
             LEFT JOIN course_enrollments e ON c.id = e.course_id AND e.status = 'enrolled'
             WHERE c.start_date >= CURDATE()
             AND c.is_active = 1
             GROUP BY c.id
             ORDER BY c.start_date
             LIMIT 10",
            []
        ) ?? [];
    }

    private function getAvailableRentalEquipment(): array
    {
        return TenantDatabase::fetchAllTenant(
            "SELECT name, daily_rate
             FROM equipment
             WHERE status = 'available'
             AND is_active = 1
             LIMIT 10",
            []
        ) ?? [];
    }

    private function getTenantInfo(): array
    {
        $tenant = TenantDatabase::fetchOneTenant(
            "SELECT company_name, address, phone, email
             FROM tenants
             WHERE id = ?",
            [TenantMiddleware::getCurrentTenantId()]
        );

        return [
            'name' => $tenant['company_name'] ?? 'Nautilus Dive Shop',
            'address' => $tenant['address'] ?? '123 Ocean Drive',
            'hours' => 'Mon-Fri: 9AM-6PM, Sat: 10AM-5PM, Sun: Closed',
            'phone' => $tenant['phone'] ?? '555-DIVE',
            'email' => $tenant['email'] ?? 'info@nautilus.com'
        ];
    }

    private function logConversation(string $message, array $response, array $intent, array $context): void
    {
        try {
            TenantDatabase::insertTenant('chatbot_conversations', [
                'customer_id' => $context['customer_id'] ?? null,
                'user_message' => $message,
                'bot_response' => $response['message'],
                'intent_detected' => $intent['name'],
                'confidence_score' => $intent['confidence'],
                'requires_human' => $response['requires_human'] ?? false,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to log chatbot conversation', ['error' => $e->getMessage()]);
        }
    }
}
