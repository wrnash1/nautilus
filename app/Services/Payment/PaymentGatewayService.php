<?php

namespace App\Services\Payment;

use App\Core\TenantDatabase;
use App\Middleware\TenantMiddleware;
use App\Core\Logger;

/**
 * Payment Gateway Service
 *
 * Unified interface for multiple payment gateways (Stripe, PayPal, Square)
 */
class PaymentGatewayService
{
    private Logger $logger;
    private array $config;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->config = [
            'stripe' => [
                'secret_key' => $_ENV['STRIPE_SECRET_KEY'] ?? '',
                'public_key' => $_ENV['STRIPE_PUBLIC_KEY'] ?? '',
                'webhook_secret' => $_ENV['STRIPE_WEBHOOK_SECRET'] ?? ''
            ],
            'square' => [
                'access_token' => $_ENV['SQUARE_ACCESS_TOKEN'] ?? '',
                'application_id' => $_ENV['SQUARE_APPLICATION_ID'] ?? '',
                'location_id' => $_ENV['SQUARE_LOCATION_ID'] ?? ''
            ],
            'paypal' => [
                'client_id' => $_ENV['PAYPAL_CLIENT_ID'] ?? '',
                'secret' => $_ENV['PAYPAL_SECRET'] ?? '',
                'mode' => $_ENV['PAYPAL_MODE'] ?? 'sandbox' // sandbox or live
            ]
        ];
    }

    /**
     * Process payment through specified gateway
     */
    public function processPayment(string $gateway, array $paymentData): array
    {
        try {
            $this->validatePaymentData($paymentData);

            $result = match($gateway) {
                'stripe' => $this->processStripePayment($paymentData),
                'square' => $this->processSquarePayment($paymentData),
                'paypal' => $this->processPayPalPayment($paymentData),
                default => ['success' => false, 'error' => 'Unsupported payment gateway']
            };

            // Log transaction
            if ($result['success']) {
                $this->logTransaction($gateway, $paymentData, $result);
            }

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Payment processing failed', [
                'gateway' => $gateway,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Stripe payment
     */
    private function processStripePayment(array $data): array
    {
        try {
            if (empty($this->config['stripe']['secret_key'])) {
                return ['success' => false, 'error' => 'Stripe not configured'];
            }

            // Initialize Stripe
            \Stripe\Stripe::setApiKey($this->config['stripe']['secret_key']);

            // Create payment intent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => round($data['amount'] * 100), // Convert to cents
                'currency' => $data['currency'] ?? 'usd',
                'payment_method' => $data['payment_method_id'],
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never'
                ],
                'description' => $data['description'] ?? 'Order payment',
                'metadata' => [
                    'order_id' => $data['order_id'] ?? null,
                    'customer_id' => $data['customer_id'] ?? null
                ]
            ]);

            if ($paymentIntent->status === 'succeeded') {
                return [
                    'success' => true,
                    'transaction_id' => $paymentIntent->id,
                    'amount' => $data['amount'],
                    'status' => 'completed',
                    'gateway_response' => $paymentIntent->toArray()
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Payment not completed',
                    'status' => $paymentIntent->status
                ];
            }

        } catch (\Stripe\Exception\CardException $e) {
            return [
                'success' => false,
                'error' => $e->getError()->message,
                'decline_code' => $e->getError()->decline_code
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process Square payment
     */
    private function processSquarePayment(array $data): array
    {
        try {
            if (empty($this->config['square']['access_token'])) {
                return ['success' => false, 'error' => 'Square not configured'];
            }

            $client = new \Square\SquareClient([
                'accessToken' => $this->config['square']['access_token'],
                'environment' => $_ENV['SQUARE_ENVIRONMENT'] ?? 'sandbox'
            ]);

            $paymentsApi = $client->getPaymentsApi();

            $body = new \Square\Models\CreatePaymentRequest(
                $data['source_id'], // nonce from Square payment form
                \uniqid(), // idempotency key
                new \Square\Models\Money(
                    round($data['amount'] * 100), // Convert to cents
                    $data['currency'] ?? 'USD'
                )
            );

            $body->setLocationId($this->config['square']['location_id']);
            $body->setNote($data['description'] ?? 'Order payment');

            $response = $paymentsApi->createPayment($body);

            if ($response->isSuccess()) {
                $payment = $response->getResult()->getPayment();

                return [
                    'success' => true,
                    'transaction_id' => $payment->getId(),
                    'amount' => $data['amount'],
                    'status' => 'completed',
                    'gateway_response' => $payment
                ];
            } else {
                $errors = $response->getErrors();
                return [
                    'success' => false,
                    'error' => $errors[0]->getDetail() ?? 'Payment failed'
                ];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process PayPal payment
     */
    private function processPayPalPayment(array $data): array
    {
        try {
            if (empty($this->config['paypal']['client_id'])) {
                return ['success' => false, 'error' => 'PayPal not configured'];
            }

            // Get PayPal access token
            $accessToken = $this->getPayPalAccessToken();

            if (!$accessToken) {
                return ['success' => false, 'error' => 'Failed to get PayPal access token'];
            }

            // Create order
            $url = $this->config['paypal']['mode'] === 'live'
                ? 'https://api-m.paypal.com/v2/checkout/orders'
                : 'https://api-m.sandbox.paypal.com/v2/checkout/orders';

            $orderData = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => $data['currency'] ?? 'USD',
                        'value' => number_format($data['amount'], 2, '.', '')
                    ],
                    'description' => $data['description'] ?? 'Order payment'
                ]]
            ];

            $response = $this->makePayPalRequest($url, $orderData, $accessToken, 'POST');

            if (isset($response['id'])) {
                // Capture the order
                $captureUrl = $url . '/' . $response['id'] . '/capture';
                $captureResponse = $this->makePayPalRequest($captureUrl, [], $accessToken, 'POST');

                if ($captureResponse['status'] === 'COMPLETED') {
                    return [
                        'success' => true,
                        'transaction_id' => $response['id'],
                        'amount' => $data['amount'],
                        'status' => 'completed',
                        'gateway_response' => $captureResponse
                    ];
                }
            }

            return ['success' => false, 'error' => 'PayPal payment failed'];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create refund
     */
    public function createRefund(string $gateway, string $transactionId, float $amount): array
    {
        try {
            $result = match($gateway) {
                'stripe' => $this->createStripeRefund($transactionId, $amount),
                'square' => $this->createSquareRefund($transactionId, $amount),
                'paypal' => $this->createPayPalRefund($transactionId, $amount),
                default => ['success' => false, 'error' => 'Unsupported payment gateway']
            };

            if ($result['success']) {
                $this->logRefund($gateway, $transactionId, $amount, $result);
            }

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('Refund processing failed', [
                'gateway' => $gateway,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create Stripe refund
     */
    private function createStripeRefund(string $paymentIntentId, float $amount): array
    {
        try {
            \Stripe\Stripe::setApiKey($this->config['stripe']['secret_key']);

            $refund = \Stripe\Refund::create([
                'payment_intent' => $paymentIntentId,
                'amount' => round($amount * 100)
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'amount' => $amount,
                'status' => $refund->status
            ];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create Square refund
     */
    private function createSquareRefund(string $paymentId, float $amount): array
    {
        try {
            $client = new \Square\SquareClient([
                'accessToken' => $this->config['square']['access_token'],
                'environment' => $_ENV['SQUARE_ENVIRONMENT'] ?? 'sandbox'
            ]);

            $refundsApi = $client->getRefundsApi();

            $body = new \Square\Models\RefundPaymentRequest(
                \uniqid(), // idempotency key
                new \Square\Models\Money(
                    round($amount * 100),
                    'USD'
                ),
                $paymentId
            );

            $response = $refundsApi->refundPayment($body);

            if ($response->isSuccess()) {
                $refund = $response->getResult()->getRefund();

                return [
                    'success' => true,
                    'refund_id' => $refund->getId(),
                    'amount' => $amount,
                    'status' => $refund->getStatus()
                ];
            } else {
                return ['success' => false, 'error' => 'Refund failed'];
            }

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create PayPal refund
     */
    private function createPayPalRefund(string $captureId, float $amount): array
    {
        try {
            $accessToken = $this->getPayPalAccessToken();

            $url = ($this->config['paypal']['mode'] === 'live'
                ? 'https://api-m.paypal.com'
                : 'https://api-m.sandbox.paypal.com') . '/v2/payments/captures/' . $captureId . '/refund';

            $refundData = [
                'amount' => [
                    'value' => number_format($amount, 2, '.', ''),
                    'currency_code' => 'USD'
                ]
            ];

            $response = $this->makePayPalRequest($url, $refundData, $accessToken, 'POST');

            if ($response['status'] === 'COMPLETED') {
                return [
                    'success' => true,
                    'refund_id' => $response['id'],
                    'amount' => $amount,
                    'status' => 'completed'
                ];
            }

            return ['success' => false, 'error' => 'PayPal refund failed'];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Helper methods

    private function validatePaymentData(array $data): void
    {
        if (empty($data['amount']) || $data['amount'] <= 0) {
            throw new \Exception('Invalid payment amount');
        }
    }

    private function getPayPalAccessToken(): ?string
    {
        $url = $this->config['paypal']['mode'] === 'live'
            ? 'https://api-m.paypal.com/v1/oauth2/token'
            : 'https://api-m.sandbox.paypal.com/v1/oauth2/token';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_USERPWD, $this->config['paypal']['client_id'] . ':' . $this->config['paypal']['secret']);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['access_token'] ?? null;
    }

    private function makePayPalRequest(string $url, array $data, string $accessToken, string $method = 'POST'): array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $accessToken
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (!empty($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }

    private function logTransaction(string $gateway, array $paymentData, array $result): void
    {
        try {
            TenantDatabase::insertTenant('payment_transactions', [
                'gateway' => $gateway,
                'transaction_id' => $result['transaction_id'],
                'order_id' => $paymentData['order_id'] ?? null,
                'customer_id' => $paymentData['customer_id'] ?? null,
                'amount' => $paymentData['amount'],
                'currency' => $paymentData['currency'] ?? 'USD',
                'status' => $result['status'],
                'gateway_response' => json_encode($result['gateway_response'] ?? []),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to log transaction', ['error' => $e->getMessage()]);
        }
    }

    private function logRefund(string $gateway, string $transactionId, float $amount, array $result): void
    {
        try {
            TenantDatabase::insertTenant('payment_refunds', [
                'gateway' => $gateway,
                'original_transaction_id' => $transactionId,
                'refund_id' => $result['refund_id'],
                'amount' => $amount,
                'status' => $result['status'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to log refund', ['error' => $e->getMessage()]);
        }
    }
}
