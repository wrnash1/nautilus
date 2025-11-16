<?php

namespace App\Services\Marketing;

use PDO;

/**
 * A/B Testing Service
 * Create and manage marketing A/B tests
 */
class ABTestingService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create A/B test experiment
     */
    public function createExperiment(array $experimentData): array
    {
        $stmt = $this->db->prepare("
            INSERT INTO ab_test_experiments (
                tenant_id, name, description, experiment_type, test_channel,
                traffic_split, sample_size, primary_metric, auto_declare_winner
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $experimentData['tenant_id'],
            $experimentData['name'],
            $experimentData['description'] ?? null,
            $experimentData['experiment_type'],
            $experimentData['test_channel'] ?? 'email',
            json_encode($experimentData['traffic_split']),
            $experimentData['sample_size'] ?? 1000,
            $experimentData['primary_metric'] ?? 'conversion_rate',
            $experimentData['auto_declare_winner'] ?? true
        ]);

        return [
            'success' => true,
            'experiment_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Add variant to experiment
     */
    public function addVariant(int $experimentId, array $variantData): array
    {
        $stmt = $this->db->prepare("
            INSERT INTO ab_test_variants (
                experiment_id, tenant_id, variant_name, is_control,
                variant_config, email_subject_line, email_content,
                sms_message, traffic_percentage
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $experimentId,
            $variantData['tenant_id'],
            $variantData['variant_name'],
            $variantData['is_control'] ?? false,
            json_encode($variantData['variant_config'] ?? []),
            $variantData['email_subject_line'] ?? null,
            $variantData['email_content'] ?? null,
            $variantData['sms_message'] ?? null,
            $variantData['traffic_percentage']
        ]);

        return [
            'success' => true,
            'variant_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Start experiment
     */
    public function startExperiment(int $experimentId): array
    {
        // Validate experiment has variants
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM ab_test_variants WHERE experiment_id = ?
        ");
        $stmt->execute([$experimentId]);

        if ($stmt->fetchColumn() < 2) {
            return [
                'success' => false,
                'error' => 'Experiment must have at least 2 variants'
            ];
        }

        // Validate traffic split adds up to 100
        $trafficStmt = $this->db->prepare("
            SELECT SUM(traffic_percentage) FROM ab_test_variants WHERE experiment_id = ?
        ");
        $trafficStmt->execute([$experimentId]);
        $totalTraffic = $trafficStmt->fetchColumn();

        if (abs($totalTraffic - 100) > 0.01) {
            return [
                'success' => false,
                'error' => 'Traffic split must add up to 100%'
            ];
        }

        // Start experiment
        $this->db->prepare("
            UPDATE ab_test_experiments
            SET status = 'running',
                started_at = NOW()
            WHERE id = ?
        ")->execute([$experimentId]);

        return [
            'success' => true,
            'message' => 'Experiment started successfully'
        ];
    }

    /**
     * Assign customer to variant
     */
    public function assignToVariant(int $experimentId, int $customerId, int $tenantId): array
    {
        // Check if already assigned
        $existing = $this->db->prepare("
            SELECT variant_id FROM ab_test_participants
            WHERE experiment_id = ? AND customer_id = ?
        ");
        $existing->execute([$experimentId, $customerId]);
        $existingAssignment = $existing->fetch(PDO::FETCH_ASSOC);

        if ($existingAssignment) {
            return [
                'success' => true,
                'variant_id' => $existingAssignment['variant_id'],
                'already_assigned' => true
            ];
        }

        // Get variants with traffic percentages
        $variants = $this->db->prepare("
            SELECT * FROM ab_test_variants
            WHERE experiment_id = ?
            ORDER BY id ASC
        ");
        $variants->execute([$experimentId]);
        $variantsList = $variants->fetchAll(PDO::FETCH_ASSOC);

        // Weighted random selection
        $selectedVariant = $this->weightedRandomSelection($variantsList);

        // Assign to variant
        $this->db->prepare("
            INSERT INTO ab_test_participants (
                experiment_id, variant_id, customer_id, tenant_id, assignment_method
            ) VALUES (?, ?, ?, ?, 'weighted')
        ")->execute([
            $experimentId,
            $selectedVariant['id'],
            $customerId,
            $tenantId
        ]);

        return [
            'success' => true,
            'variant_id' => $selectedVariant['id'],
            'variant_name' => $selectedVariant['variant_name'],
            'already_assigned' => false
        ];
    }

    /**
     * Weighted random selection based on traffic percentages
     */
    private function weightedRandomSelection(array $variants): array
    {
        $rand = mt_rand(1, 10000) / 100; // 0.00 to 100.00
        $cumulative = 0;

        foreach ($variants as $variant) {
            $cumulative += $variant['traffic_percentage'];
            if ($rand <= $cumulative) {
                return $variant;
            }
        }

        return $variants[0]; // Fallback
    }

    /**
     * Track participant engagement
     */
    public function trackEngagement(int $experimentId, int $customerId, string $eventType, array $eventData = []): bool
    {
        // Get participant
        $participant = $this->db->prepare("
            SELECT * FROM ab_test_participants
            WHERE experiment_id = ? AND customer_id = ?
        ");
        $participant->execute([$experimentId, $customerId]);
        $participantData = $participant->fetch(PDO::FETCH_ASSOC);

        if (!$participantData) {
            return false;
        }

        // Update participant based on event type
        switch ($eventType) {
            case 'email_sent':
                $this->db->prepare("
                    UPDATE ab_test_participants
                    SET email_sent = TRUE
                    WHERE id = ?
                ")->execute([$participantData['id']]);

                $this->db->prepare("
                    UPDATE ab_test_variants
                    SET total_sent = total_sent + 1
                    WHERE id = ?
                ")->execute([$participantData['variant_id']]);
                break;

            case 'email_delivered':
                $this->db->prepare("
                    UPDATE ab_test_participants
                    SET email_delivered = TRUE
                    WHERE id = ?
                ")->execute([$participantData['id']]);

                $this->db->prepare("
                    UPDATE ab_test_variants
                    SET total_delivered = total_delivered + 1
                    WHERE id = ?
                ")->execute([$participantData['variant_id']]);
                break;

            case 'email_opened':
                $this->db->prepare("
                    UPDATE ab_test_participants
                    SET email_opened = TRUE
                    WHERE id = ?
                ")->execute([$participantData['id']]);

                $this->db->prepare("
                    UPDATE ab_test_variants
                    SET total_opened = total_opened + 1
                    WHERE id = ?
                ")->execute([$participantData['variant_id']]);
                break;

            case 'email_clicked':
                $this->db->prepare("
                    UPDATE ab_test_participants
                    SET email_clicked = TRUE
                    WHERE id = ?
                ")->execute([$participantData['id']]);

                $this->db->prepare("
                    UPDATE ab_test_variants
                    SET total_clicked = total_clicked + 1
                    WHERE id = ?
                ")->execute([$participantData['variant_id']]);
                break;

            case 'converted':
                $value = $eventData['conversion_value'] ?? 0;

                $this->db->prepare("
                    UPDATE ab_test_participants
                    SET converted = TRUE,
                        converted_at = NOW(),
                        conversion_value = ?
                    WHERE id = ?
                ")->execute([$value, $participantData['id']]);

                $this->db->prepare("
                    UPDATE ab_test_variants
                    SET total_conversions = total_conversions + 1,
                        total_revenue = total_revenue + ?
                    WHERE id = ?
                ")->execute([$value, $participantData['variant_id']]);
                break;
        }

        // Update calculated rates
        $this->updateVariantRates($participantData['variant_id']);

        // Check if experiment should end
        $this->checkExperimentCompletion($experimentId);

        return true;
    }

    /**
     * Update variant calculated rates
     */
    private function updateVariantRates(int $variantId): void
    {
        $this->db->prepare("
            UPDATE ab_test_variants
            SET open_rate = CASE WHEN total_delivered > 0
                    THEN ROUND((total_opened / total_delivered) * 100, 2)
                    ELSE 0 END,
                click_rate = CASE WHEN total_delivered > 0
                    THEN ROUND((total_clicked / total_delivered) * 100, 2)
                    ELSE 0 END,
                conversion_rate = CASE WHEN total_delivered > 0
                    THEN ROUND((total_conversions / total_delivered) * 100, 2)
                    ELSE 0 END,
                avg_revenue_per_recipient = CASE WHEN total_sent > 0
                    THEN ROUND(total_revenue / total_sent, 2)
                    ELSE 0 END
            WHERE id = ?
        ")->execute([$variantId]);
    }

    /**
     * Check if experiment should be completed
     */
    private function checkExperimentCompletion(int $experimentId): void
    {
        // Get experiment details
        $experiment = $this->db->prepare("
            SELECT * FROM ab_test_experiments WHERE id = ?
        ");
        $experiment->execute([$experimentId]);
        $experimentData = $experiment->fetch(PDO::FETCH_ASSOC);

        if (!$experimentData || $experimentData['status'] !== 'running') {
            return;
        }

        // Check if minimum sample size reached
        $totalParticipants = $this->db->prepare("
            SELECT COUNT(*) FROM ab_test_participants WHERE experiment_id = ?
        ");
        $totalParticipants->execute([$experimentId]);
        $participantCount = $totalParticipants->fetchColumn();

        if ($participantCount < $experimentData['min_sample_size']) {
            return;
        }

        // Check if auto-declare winner is enabled
        if ($experimentData['auto_declare_winner']) {
            $this->analyzeAndDeclareWinner($experimentId);
        }
    }

    /**
     * Analyze results and declare winner
     */
    public function analyzeAndDeclareWinner(int $experimentId): array
    {
        // Get experiment
        $experiment = $this->db->prepare("
            SELECT * FROM ab_test_experiments WHERE id = ?
        ");
        $experiment->execute([$experimentId]);
        $experimentData = $experiment->fetch(PDO::FETCH_ASSOC);

        if (!$experimentData) {
            return ['success' => false, 'error' => 'Experiment not found'];
        }

        $primaryMetric = $experimentData['primary_metric'];

        // Get all variants with performance
        $variants = $this->db->prepare("
            SELECT * FROM ab_test_variants
            WHERE experiment_id = ?
            ORDER BY $primaryMetric DESC
        ");
        $variants->execute([$experimentId]);
        $variantsList = $variants->fetchAll(PDO::FETCH_ASSOC);

        if (count($variantsList) < 2) {
            return ['success' => false, 'error' => 'Not enough variants'];
        }

        $winner = $variantsList[0];
        $control = null;

        // Find control variant
        foreach ($variantsList as $variant) {
            if ($variant['is_control']) {
                $control = $variant;
                break;
            }
        }

        // Calculate statistical significance (simplified)
        $significance = $this->calculateStatisticalSignificance($winner, $control ?? $variantsList[1]);

        // Declare winner if statistically significant
        if ($significance >= $experimentData['confidence_level']) {
            $this->db->prepare("
                UPDATE ab_test_experiments
                SET winner_variant = ?,
                    winner_declared_at = NOW(),
                    statistical_significance = ?,
                    status = 'completed',
                    ended_at = NOW()
                WHERE id = ?
            ")->execute([
                $winner['variant_name'],
                $significance,
                $experimentId
            ]);

            $this->db->prepare("
                UPDATE ab_test_variants
                SET is_winner = TRUE
                WHERE id = ?
            ")->execute([$winner['id']]);

            return [
                'success' => true,
                'winner' => $winner['variant_name'],
                'significance' => $significance,
                'winner_metric_value' => $winner[$primaryMetric]
            ];
        }

        return [
            'success' => false,
            'message' => 'No statistically significant winner yet',
            'current_significance' => $significance
        ];
    }

    /**
     * Calculate statistical significance (simplified chi-square test)
     */
    private function calculateStatisticalSignificance(array $variantA, array $variantB): float
    {
        // Simplified calculation - in production use proper statistical tests
        $convA = $variantA['total_conversions'];
        $totalA = $variantA['total_sent'];
        $convB = $variantB['total_conversions'];
        $totalB = $variantB['total_sent'];

        if ($totalA == 0 || $totalB == 0) {
            return 0;
        }

        $rateA = $convA / $totalA;
        $rateB = $convB / $totalB;

        $pooledRate = ($convA + $convB) / ($totalA + $totalB);
        $se = sqrt($pooledRate * (1 - $pooledRate) * (1 / $totalA + 1 / $totalB));

        if ($se == 0) {
            return 0;
        }

        $zScore = abs($rateA - $rateB) / $se;

        // Convert z-score to confidence level (approximation)
        if ($zScore >= 2.58) return 99.0;
        if ($zScore >= 1.96) return 95.0;
        if ($zScore >= 1.65) return 90.0;

        return round(50 + ($zScore / 2.58) * 49, 2);
    }

    /**
     * Get experiment results
     */
    public function getExperimentResults(int $experimentId): array
    {
        $experiment = $this->db->prepare("
            SELECT * FROM ab_test_experiments WHERE id = ?
        ");
        $experiment->execute([$experimentId]);
        $experimentData = $experiment->fetch(PDO::FETCH_ASSOC);

        if (!$experimentData) {
            return ['success' => false, 'error' => 'Experiment not found'];
        }

        $variants = $this->db->prepare("
            SELECT * FROM ab_test_variants
            WHERE experiment_id = ?
            ORDER BY {$experimentData['primary_metric']} DESC
        ");
        $variants->execute([$experimentId]);
        $variantsList = $variants->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'experiment' => $experimentData,
            'variants' => $variantsList
        ];
    }

    /**
     * Pause experiment
     */
    public function pauseExperiment(int $experimentId): bool
    {
        $this->db->prepare("
            UPDATE ab_test_experiments
            SET status = 'paused'
            WHERE id = ?
        ")->execute([$experimentId]);

        return true;
    }

    /**
     * Resume experiment
     */
    public function resumeExperiment(int $experimentId): bool
    {
        $this->db->prepare("
            UPDATE ab_test_experiments
            SET status = 'running'
            WHERE id = ?
        ")->execute([$experimentId]);

        return true;
    }
}
