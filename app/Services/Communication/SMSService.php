<?php

namespace App\Services\Communication;

use App\Core\Logger;
use Twilio\Rest\Client;
use Exception;

/**
 * SMS Service
 * Handles SMS sending via Twilio
 */
class SMSService
{
    private Logger $logger;
    private ?Client $client = null;
    private string $fromNumber;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->fromNumber = $_ENV['TWILIO_FROM_NUMBER'] ?? '';

        $sid = $_ENV['TWILIO_SID'] ?? '';
        $token = $_ENV['TWILIO_AUTH_TOKEN'] ?? '';

        if ($sid && $token) {
            try {
                $this->client = new Client($sid, $token);
            } catch (Exception $e) {
                $this->logger->error('Twilio client initialization failed', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Send SMS message
     */
    public function send(string $to, string $message): array
    {
        if (!$this->client) {
            return [
                'success' => false,
                'error' => 'SMS service not configured'
            ];
        }

        try {
            // Format phone number (ensure it starts with +)
            if (!str_starts_with($to, '+')) {
                $to = '+1' . preg_replace('/[^0-9]/', '', $to);
            }

            $result = $this->client->messages->create($to, [
                'from' => $this->fromNumber,
                'body' => $message
            ]);

            $this->logger->info('SMS sent successfully', [
                'to' => $to,
                'sid' => $result->sid
            ]);

            return [
                'success' => true,
                'message_sid' => $result->sid,
                'status' => $result->status
            ];

        } catch (Exception $e) {
            $this->logger->error('SMS sending failed', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Send bulk SMS
     */
    public function sendBulk(array $recipients, string $message): array
    {
        $results = [
            'sent' => 0,
            'failed' => 0,
            'errors' => []
        ];

        foreach ($recipients as $recipient) {
            $result = $this->send($recipient, $message);

            if ($result['success']) {
                $results['sent']++;
            } else {
                $results['failed']++;
                $results['errors'][$recipient] = $result['error'];
            }
        }

        return $results;
    }

    /**
     * Test SMS configuration
     */
    public function testConnection(): array
    {
        if (!$this->client) {
            return [
                'success' => false,
                'error' => 'Twilio credentials not configured'
            ];
        }

        try {
            // Try to fetch account details
            $account = $this->client->api->v2010->accounts($_ENV['TWILIO_SID'])->fetch();

            return [
                'success' => true,
                'message' => 'Twilio connection successful',
                'account_name' => $account->friendlyName,
                'status' => $account->status
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
