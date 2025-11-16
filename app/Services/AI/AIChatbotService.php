<?php

namespace App\Services\AI;

use PDO;

/**
 * AI-Powered Chatbot Service
 * Handles customer conversations with natural language understanding
 */
class AIChatbotService
{
    private PDO $db;
    private string $apiKey;
    private string $apiEndpoint = 'https://api.openai.com/v1/chat/completions';
    private string $model = 'gpt-4';

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->apiKey = $_ENV['OPENAI_API_KEY'] ?? '';
    }

    /**
     * Process user message and generate AI response
     */
    public function chat(string $message, string $sessionId, ?int $customerId = null): array
    {
        // Get or create conversation
        $conversation = $this->getOrCreateConversation($sessionId, $customerId);

        // Store user message
        $this->storeMessage($conversation['id'], 'user', $message);

        // Get conversation context
        $context = $this->getConversationContext($conversation['id']);

        // Detect intent
        $intent = $this->detectIntent($message);

        // Generate AI response
        $aiResponse = $this->generateResponse($message, $context, $intent);

        // Store AI message
        $this->storeMessage($conversation['id'], 'ai', $aiResponse['message'], $aiResponse);

        // Extract entities
        $entities = $this->extractEntities($message);

        // Analyze sentiment
        $sentiment = $this->analyzeSentiment($message);

        // Check if escalation needed
        $needsEscalation = $this->shouldEscalate($intent, $sentiment);

        return [
            'success' => true,
            'message' => $aiResponse['message'],
            'intent' => $intent,
            'sentiment' => $sentiment,
            'entities' => $entities,
            'needs_escalation' => $needsEscalation,
            'conversation_id' => $conversation['id']
        ];
    }

    /**
     * Generate AI response using GPT
     */
    private function generateResponse(string $userMessage, array $context, array $intent): array
    {
        // Build conversation history for context
        $messages = [
            [
                'role' => 'system',
                'content' => $this->getSystemPrompt()
            ]
        ];

        // Add recent conversation history
        foreach ($context as $msg) {
            $messages[] = [
                'role' => $msg['message_type'] === 'user' ? 'user' : 'assistant',
                'content' => $msg['message']
            ];
        }

        // Add current message
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];

        // Call OpenAI API (simplified - would need actual API call)
        // For demonstration, using rule-based responses
        $response = $this->generateRuleBasedResponse($userMessage, $intent);

        return [
            'message' => $response,
            'model' => $this->model,
            'tokens' => 0
        ];
    }

    /**
     * Rule-based response generation (fallback/demo)
     */
    private function generateRuleBasedResponse(string $message, array $intent): string
    {
        $intentType = $intent['intent'];

        switch ($intentType) {
            case 'booking':
                return "I'd be happy to help you book a course or trip! We offer Open Water, Advanced, and specialty courses, as well as exciting dive trips. Which are you interested in?";

            case 'pricing':
                return "Our Open Water course is $399, Advanced Open Water is $299, and specialty courses range from $199-$299. We also offer package deals! Would you like details on a specific course?";

            case 'schedule':
                return "We have courses starting every weekend! Our next Open Water class begins this Saturday. Would you like me to check availability for you?";

            case 'equipment':
                return "We rent full scuba equipment sets for $75/day, or individual pieces starting at $15/day. We also sell new and used equipment. What are you looking for?";

            case 'certification':
                return "We offer PADI certifications from beginner to professional levels. Have you dived before, or are you just starting?";

            case 'complaint':
                return "I'm sorry to hear you're having an issue. Let me connect you with a manager who can help resolve this right away.";

            case 'question':
            default:
                return "I'm here to help! Could you provide more details about what you're looking for? I can assist with bookings, pricing, schedules, equipment, and general questions about diving.";
        }
    }

    /**
     * Detect user intent from message
     */
    private function detectIntent(string $message): array
    {
        $message = strtolower($message);

        // Intent keywords
        $intents = [
            'booking' => ['book', 'schedule', 'sign up', 'enroll', 'register', 'reserve'],
            'pricing' => ['price', 'cost', 'how much', 'fee', 'rate', '$'],
            'schedule' => ['when', 'schedule', 'date', 'time', 'available', 'next'],
            'equipment' => ['equipment', 'gear', 'rent', 'buy', 'tank', 'regulator', 'bcd'],
            'certification' => ['certif', 'license', 'card', 'padi', 'open water', 'advanced'],
            'complaint' => ['problem', 'issue', 'complain', 'unhappy', 'disappointed', 'refund'],
            'greeting' => ['hello', 'hi', 'hey', 'good morning', 'good afternoon'],
        ];

        $scores = [];
        foreach ($intents as $intent => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                if (strpos($message, $keyword) !== false) {
                    $score++;
                }
            }
            $scores[$intent] = $score;
        }

        arsort($scores);
        $topIntent = key($scores);
        $confidence = $scores[$topIntent] > 0 ? min(0.95, 0.5 + ($scores[$topIntent] * 0.15)) : 0.3;

        return [
            'intent' => $topIntent ?: 'question',
            'confidence' => $confidence,
            'all_scores' => $scores
        ];
    }

    /**
     * Extract entities (dates, products, names) from message
     */
    private function extractEntities(string $message): array
    {
        $entities = [];

        // Extract dates (simplified)
        if (preg_match('/\b(today|tomorrow|next week|this weekend|saturday|sunday|monday)\b/i', $message, $matches)) {
            $entities['dates'] = $matches;
        }

        // Extract course names
        $courses = ['open water', 'advanced', 'rescue', 'divemaster', 'nitrox'];
        foreach ($courses as $course) {
            if (stripos($message, $course) !== false) {
                $entities['courses'][] = $course;
            }
        }

        // Extract numbers (prices, quantities)
        if (preg_match_all('/\$?\d+/', $message, $matches)) {
            $entities['numbers'] = $matches[0];
        }

        return $entities;
    }

    /**
     * Analyze sentiment of message
     */
    private function analyzeSentiment(string $message): array
    {
        $message = strtolower($message);

        $positive = ['great', 'good', 'excellent', 'amazing', 'love', 'perfect', 'happy', 'thanks'];
        $negative = ['bad', 'terrible', 'awful', 'hate', 'worst', 'unhappy', 'disappointed', 'problem'];

        $positiveScore = 0;
        $negativeScore = 0;

        foreach ($positive as $word) {
            if (strpos($message, $word) !== false) $positiveScore++;
        }

        foreach ($negative as $word) {
            if (strpos($message, $word) !== false) $negativeScore++;
        }

        if ($positiveScore > $negativeScore) {
            $sentiment = 'positive';
            $score = 0.5 + ($positiveScore * 0.1);
        } elseif ($negativeScore > $positiveScore) {
            $sentiment = 'negative';
            $score = -0.5 - ($negativeScore * 0.1);
        } else {
            $sentiment = 'neutral';
            $score = 0;
        }

        return [
            'sentiment' => $sentiment,
            'score' => max(-1, min(1, $score)),
            'confidence' => 0.70
        ];
    }

    /**
     * Determine if conversation needs human escalation
     */
    private function shouldEscalate(array $intent, array $sentiment): bool
    {
        // Escalate complaints
        if ($intent['intent'] === 'complaint') {
            return true;
        }

        // Escalate very negative sentiment
        if ($sentiment['sentiment'] === 'negative' && $sentiment['score'] < -0.6) {
            return true;
        }

        // Escalate low confidence responses
        if ($intent['confidence'] < 0.4) {
            return true;
        }

        return false;
    }

    /**
     * Get system prompt for AI
     */
    private function getSystemPrompt(): string
    {
        return "You are a helpful assistant for a dive shop. You help customers with:
- Booking courses (Open Water, Advanced, Rescue, specialties)
- Answering questions about diving
- Equipment rentals and sales
- Pricing information
- Scheduling

Be friendly, professional, and concise. If you don't know something, say so and offer to connect them with a staff member.";
    }

    /**
     * Get or create conversation
     */
    private function getOrCreateConversation(string $sessionId, ?int $customerId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM ai_chatbot_conversations
            WHERE session_id = ?
              AND ended_at IS NULL
            ORDER BY started_at DESC
            LIMIT 1
        ");
        $stmt->execute([$sessionId]);
        $conversation = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$conversation) {
            $stmt = $this->db->prepare("
                INSERT INTO ai_chatbot_conversations (session_id, customer_id, ai_model)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$sessionId, $customerId, $this->model]);

            $conversation = [
                'id' => $this->db->lastInsertId(),
                'session_id' => $sessionId,
                'customer_id' => $customerId
            ];
        }

        return $conversation;
    }

    /**
     * Store message in conversation
     */
    private function storeMessage(int $conversationId, string $type, string $message, array $metadata = []): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO ai_chatbot_messages (
                conversation_id,
                message_type,
                message,
                detected_intent,
                confidence_score,
                sentiment,
                sentiment_score,
                model_used,
                prompt_tokens,
                completion_tokens
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $conversationId,
            $type,
            $message,
            $metadata['intent'] ?? null,
            $metadata['confidence'] ?? null,
            $metadata['sentiment'] ?? null,
            $metadata['sentiment_score'] ?? null,
            $metadata['model'] ?? $this->model,
            $metadata['prompt_tokens'] ?? 0,
            $metadata['completion_tokens'] ?? 0
        ]);

        // Update conversation message count
        $this->db->prepare("
            UPDATE ai_chatbot_conversations
            SET total_messages = total_messages + 1,
                " . ($type === 'ai' ? 'ai_messages' : 'human_messages') . " = " . ($type === 'ai' ? 'ai_messages' : 'human_messages') . " + 1
            WHERE id = ?
        ")->execute([$conversationId]);
    }

    /**
     * Get conversation context (recent messages)
     */
    private function getConversationContext(int $conversationId, int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT message_type, message
            FROM ai_chatbot_messages
            WHERE conversation_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$conversationId, $limit]);
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
