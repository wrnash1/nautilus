<?php

namespace App\Services\Marketing;

use PDO;

/**
 * Marketing Automation Service
 * Manage automated workflow execution and customer journeys
 */
class AutomationService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Create automation workflow
     */
    public function createWorkflow(array $workflowData): array
    {
        $stmt = $this->db->prepare("
            INSERT INTO automation_workflows (
                tenant_id, name, description, workflow_type, trigger_type,
                trigger_config, entry_criteria, can_re_enter, send_time_optimization
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $workflowData['tenant_id'],
            $workflowData['name'],
            $workflowData['description'] ?? null,
            $workflowData['workflow_type'],
            $workflowData['trigger_type'],
            json_encode($workflowData['trigger_config']),
            json_encode($workflowData['entry_criteria'] ?? []),
            $workflowData['can_re_enter'] ?? false,
            $workflowData['send_time_optimization'] ?? false
        ]);

        return [
            'success' => true,
            'workflow_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Add step to workflow
     */
    public function addWorkflowStep(int $workflowId, array $stepData): array
    {
        $stmt = $this->db->prepare("
            INSERT INTO automation_workflow_steps (
                workflow_id, tenant_id, step_order, step_name, step_type,
                delay_amount, delay_unit, send_time, config,
                email_template_id, subject_line, email_content, sms_content
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $workflowId,
            $stepData['tenant_id'],
            $stepData['step_order'],
            $stepData['step_name'],
            $stepData['step_type'],
            $stepData['delay_amount'] ?? 0,
            $stepData['delay_unit'] ?? 'days',
            $stepData['send_time'] ?? null,
            json_encode($stepData['config'] ?? []),
            $stepData['email_template_id'] ?? null,
            $stepData['subject_line'] ?? null,
            $stepData['email_content'] ?? null,
            $stepData['sms_content'] ?? null
        ]);

        return [
            'success' => true,
            'step_id' => $this->db->lastInsertId()
        ];
    }

    /**
     * Activate workflow
     */
    public function activateWorkflow(int $workflowId): array
    {
        // Validate workflow has steps
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM automation_workflow_steps WHERE workflow_id = ?
        ");
        $stmt->execute([$workflowId]);

        if ($stmt->fetchColumn() == 0) {
            return [
                'success' => false,
                'error' => 'Workflow must have at least one step'
            ];
        }

        // Activate
        $this->db->prepare("
            UPDATE automation_workflows
            SET status = 'active',
                activated_at = NOW()
            WHERE id = ?
        ")->execute([$workflowId]);

        return [
            'success' => true,
            'message' => 'Workflow activated'
        ];
    }

    /**
     * Enroll customer in workflow
     */
    public function enrollCustomer(int $workflowId, int $customerId, int $tenantId, string $entryTrigger): array
    {
        // Get workflow details
        $workflow = $this->db->prepare("
            SELECT * FROM automation_workflows WHERE id = ?
        ");
        $workflow->execute([$workflowId]);
        $workflowData = $workflow->fetch(PDO::FETCH_ASSOC);

        if (!$workflowData || $workflowData['status'] !== 'active') {
            return [
                'success' => false,
                'error' => 'Workflow not found or not active'
            ];
        }

        // Check if can re-enter
        if (!$workflowData['can_re_enter']) {
            $existing = $this->db->prepare("
                SELECT id FROM automation_workflow_members
                WHERE workflow_id = ? AND customer_id = ?
            ");
            $existing->execute([$workflowId, $customerId]);

            if ($existing->fetch()) {
                return [
                    'success' => false,
                    'error' => 'Customer already enrolled and workflow does not allow re-entry'
                ];
            }
        }

        // Get first step
        $firstStep = $this->db->prepare("
            SELECT * FROM automation_workflow_steps
            WHERE workflow_id = ?
            ORDER BY step_order ASC
            LIMIT 1
        ");
        $firstStep->execute([$workflowId]);
        $step = $firstStep->fetch(PDO::FETCH_ASSOC);

        // Calculate next action time
        $nextActionAt = $this->calculateNextActionTime(
            $step['delay_amount'],
            $step['delay_unit'],
            $step['send_time']
        );

        // Enroll customer
        $stmt = $this->db->prepare("
            INSERT INTO automation_workflow_members (
                workflow_id, customer_id, tenant_id, entry_trigger,
                current_step_id, current_step_entered_at, next_action_at, is_waiting
            ) VALUES (?, ?, ?, ?, ?, NOW(), ?, TRUE)
        ");

        $stmt->execute([
            $workflowId,
            $customerId,
            $tenantId,
            $entryTrigger,
            $step['id'],
            $nextActionAt
        ]);

        $memberId = $this->db->lastInsertId();

        // Update workflow stats
        $this->db->prepare("
            UPDATE automation_workflows
            SET total_entries = total_entries + 1,
                active_members = active_members + 1
            WHERE id = ?
        ")->execute([$workflowId]);

        return [
            'success' => true,
            'member_id' => $memberId,
            'next_action_at' => $nextActionAt
        ];
    }

    /**
     * Process pending workflow actions
     */
    public function processPendingActions(int $limit = 100): array
    {
        // Get members with pending actions
        $stmt = $this->db->prepare("
            SELECT
                wm.*,
                w.workflow_type,
                w.tenant_id as workflow_tenant_id,
                s.*
            FROM automation_workflow_members wm
            JOIN automation_workflows w ON wm.workflow_id = w.id
            JOIN automation_workflow_steps s ON wm.current_step_id = s.id
            WHERE wm.status = 'active'
              AND wm.is_waiting = TRUE
              AND wm.next_action_at <= NOW()
            LIMIT ?
        ");

        $stmt->execute([$limit]);
        $pendingMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $processed = 0;
        $errors = [];

        foreach ($pendingMembers as $member) {
            try {
                $this->executeWorkflowStep($member);
                $processed++;
            } catch (\Exception $e) {
                $errors[] = [
                    'member_id' => $member['id'],
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'success' => true,
            'processed' => $processed,
            'errors' => $errors
        ];
    }

    /**
     * Execute a workflow step for a member
     */
    private function executeWorkflowStep(array $member): void
    {
        $stepType = $member['step_type'];

        // Execute step based on type
        switch ($stepType) {
            case 'email':
                $this->executeEmailStep($member);
                break;
            case 'sms':
                $this->executeSMSStep($member);
                break;
            case 'wait':
                $this->executeWaitStep($member);
                break;
            case 'condition':
                $this->executeConditionStep($member);
                break;
            case 'webhook':
                $this->executeWebhookStep($member);
                break;
            default:
                throw new \Exception("Unknown step type: $stepType");
        }

        // Log execution
        $this->logStepExecution($member, 'success');

        // Move to next step
        $this->advanceToNextStep($member);
    }

    /**
     * Execute email step
     */
    private function executeEmailStep(array $member): void
    {
        // Get customer email
        $customer = $this->db->prepare("
            SELECT * FROM customers WHERE id = ?
        ");
        $customer->execute([$member['customer_id']]);
        $customerData = $customer->fetch(PDO::FETCH_ASSOC);

        if (!$customerData || !$customerData['email']) {
            throw new \Exception("Customer email not found");
        }

        // Personalize content
        $subject = $this->personalizeContent($member['subject_line'], $customerData);
        $content = $this->personalizeContent($member['email_content'], $customerData);

        // Queue email
        $this->db->prepare("
            INSERT INTO email_queue (
                tenant_id, to_email, to_name, subject, html_body,
                from_email, from_name, workflow_id, customer_id, priority, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'normal', 'pending')
        ")->execute([
            $member['tenant_id'],
            $customerData['email'],
            $customerData['first_name'] . ' ' . $customerData['last_name'],
            $subject,
            $content,
            $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@diveshop.com',
            $_ENV['MAIL_FROM_NAME'] ?? 'Dive Shop',
            $member['workflow_id'],
            $member['customer_id']
        ]);

        // Update member stats
        $this->db->prepare("
            UPDATE automation_workflow_members
            SET emails_sent = emails_sent + 1
            WHERE id = ?
        ")->execute([$member['id']]);

        // Update step stats
        $this->db->prepare("
            UPDATE automation_workflow_steps
            SET total_sent = total_sent + 1
            WHERE id = ?
        ")->execute([$member['current_step_id']]);
    }

    /**
     * Execute SMS step
     */
    private function executeSMSStep(array $member): void
    {
        // Get customer phone
        $customer = $this->db->prepare("
            SELECT * FROM customers WHERE id = ?
        ");
        $customer->execute([$member['customer_id']]);
        $customerData = $customer->fetch(PDO::FETCH_ASSOC);

        if (!$customerData || !$customerData['phone']) {
            throw new \Exception("Customer phone not found");
        }

        // Personalize content
        $message = $this->personalizeContent($member['sms_content'], $customerData);

        // Queue SMS (would integrate with SMS provider)
        // For now, just log it
        $this->db->prepare("
            UPDATE automation_workflow_members
            SET sms_sent = sms_sent + 1
            WHERE id = ?
        ")->execute([$member['id']]);

        // In production, you would call an SMS API here
        // Example: $smsService->send($customerData['phone'], $message);
    }

    /**
     * Execute wait step
     */
    private function executeWaitStep(array $member): void
    {
        // Wait step is just a delay, move to next step
        // The delay is already calculated in advanceToNextStep
    }

    /**
     * Execute condition step
     */
    private function executeConditionStep(array $member): void
    {
        $conditionRules = json_decode($member['condition_rules'], true);

        // Evaluate condition (simplified)
        $conditionMet = $this->evaluateCondition($conditionRules, $member['customer_id']);

        // Set next step based on condition
        $nextStepId = $conditionMet
            ? $member['true_next_step_id']
            : $member['false_next_step_id'];

        if ($nextStepId) {
            $this->db->prepare("
                UPDATE automation_workflow_members
                SET current_step_id = ?
                WHERE id = ?
            ")->execute([$nextStepId, $member['id']]);
        }
    }

    /**
     * Execute webhook step
     */
    private function executeWebhookStep(array $member): void
    {
        $config = json_decode($member['config'], true);
        $webhookUrl = $config['webhook_url'] ?? null;

        if (!$webhookUrl) {
            throw new \Exception("Webhook URL not configured");
        }

        // Get customer data
        $customer = $this->db->prepare("
            SELECT * FROM customers WHERE id = ?
        ");
        $customer->execute([$member['customer_id']]);
        $customerData = $customer->fetch(PDO::FETCH_ASSOC);

        // Send webhook
        $payload = [
            'customer' => $customerData,
            'workflow_id' => $member['workflow_id'],
            'step_id' => $member['current_step_id']
        ];

        // In production, actually send the webhook
        // $this->sendWebhook($webhookUrl, $payload);
    }

    /**
     * Evaluate condition rules
     */
    private function evaluateCondition(array $rules, int $customerId): bool
    {
        // Simplified condition evaluation
        // In production, this would be more sophisticated

        foreach ($rules as $rule) {
            $field = $rule['field'];
            $operator = $rule['operator'];
            $value = $rule['value'];

            // Get customer field value
            $stmt = $this->db->prepare("
                SELECT $field FROM customers WHERE id = ?
            ");
            $stmt->execute([$customerId]);
            $actualValue = $stmt->fetchColumn();

            // Evaluate based on operator
            switch ($operator) {
                case 'equals':
                    if ($actualValue != $value) return false;
                    break;
                case 'greater_than':
                    if ($actualValue <= $value) return false;
                    break;
                case 'less_than':
                    if ($actualValue >= $value) return false;
                    break;
            }
        }

        return true;
    }

    /**
     * Advance member to next step
     */
    private function advanceToNextStep(array $member): void
    {
        // Get next step
        $nextStep = $this->db->prepare("
            SELECT * FROM automation_workflow_steps
            WHERE workflow_id = ?
              AND step_order > ?
            ORDER BY step_order ASC
            LIMIT 1
        ");

        $nextStep->execute([$member['workflow_id'], $member['step_order']]);
        $step = $nextStep->fetch(PDO::FETCH_ASSOC);

        if (!$step) {
            // No more steps, complete workflow
            $this->completeWorkflowForMember($member['id'], $member['workflow_id']);
            return;
        }

        // Calculate next action time
        $nextActionAt = $this->calculateNextActionTime(
            $step['delay_amount'],
            $step['delay_unit'],
            $step['send_time']
        );

        // Update member
        $this->db->prepare("
            UPDATE automation_workflow_members
            SET current_step_id = ?,
                current_step_entered_at = NOW(),
                next_action_at = ?,
                steps_completed = steps_completed + 1,
                is_waiting = TRUE
            WHERE id = ?
        ")->execute([
            $step['id'],
            $nextActionAt,
            $member['id']
        ]);
    }

    /**
     * Complete workflow for member
     */
    private function completeWorkflowForMember(int $memberId, int $workflowId): void
    {
        $this->db->prepare("
            UPDATE automation_workflow_members
            SET status = 'completed',
                completed_at = NOW(),
                is_waiting = FALSE
            WHERE id = ?
        ")->execute([$memberId]);

        // Update workflow stats
        $this->db->prepare("
            UPDATE automation_workflows
            SET active_members = active_members - 1,
                completed_members = completed_members + 1
            WHERE id = ?
        ")->execute([$workflowId]);
    }

    /**
     * Calculate next action time based on delay and send time
     */
    private function calculateNextActionTime(int $delayAmount, string $delayUnit, ?string $sendTime): string
    {
        $intervals = [
            'minutes' => "+$delayAmount minutes",
            'hours' => "+$delayAmount hours",
            'days' => "+$delayAmount days",
            'weeks' => "+$delayAmount weeks"
        ];

        $baseTime = strtotime($intervals[$delayUnit] ?? '+1 day');

        // If specific send time is set, adjust to that time
        if ($sendTime) {
            $sendHour = intval(substr($sendTime, 0, 2));
            $sendMinute = intval(substr($sendTime, 3, 2));

            $baseTime = strtotime(date('Y-m-d', $baseTime) . " $sendHour:$sendMinute:00");
        }

        return date('Y-m-d H:i:s', $baseTime);
    }

    /**
     * Personalize content with customer data
     */
    private function personalizeContent(string $content, array $customer): string
    {
        $replacements = [
            '{{first_name}}' => $customer['first_name'] ?? '',
            '{{last_name}}' => $customer['last_name'] ?? '',
            '{{email}}' => $customer['email'] ?? '',
            '{{phone}}' => $customer['phone'] ?? '',
            '{{full_name}}' => trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? ''))
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Log step execution
     */
    private function logStepExecution(array $member, string $status, ?string $error = null): void
    {
        $this->db->prepare("
            INSERT INTO automation_step_executions (
                workflow_member_id, workflow_id, step_id, customer_id, tenant_id,
                execution_status, action_type, error_message
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ")->execute([
            $member['id'],
            $member['workflow_id'],
            $member['current_step_id'],
            $member['customer_id'],
            $member['tenant_id'],
            $status,
            $member['step_type'],
            $error
        ]);
    }

    /**
     * Track workflow goal achievement
     */
    public function trackGoalAchievement(int $workflowId, int $customerId, int $goalId, float $value): array
    {
        // Find active workflow member
        $member = $this->db->prepare("
            SELECT * FROM automation_workflow_members
            WHERE workflow_id = ? AND customer_id = ? AND status = 'active'
            LIMIT 1
        ");
        $member->execute([$workflowId, $customerId]);
        $memberData = $member->fetch(PDO::FETCH_ASSOC);

        if (!$memberData) {
            return ['success' => false, 'error' => 'Member not found'];
        }

        // Mark as converted
        $this->db->prepare("
            UPDATE automation_workflow_members
            SET converted = TRUE,
                converted_at = NOW(),
                conversion_value = ?
            WHERE id = ?
        ")->execute([$value, $memberData['id']]);

        // Update workflow stats
        $this->db->prepare("
            UPDATE automation_workflows
            SET total_conversions = total_conversions + 1,
                total_revenue = total_revenue + ?
            WHERE id = ?
        ")->execute([$value, $workflowId]);

        // Update goal stats
        $this->db->prepare("
            UPDATE automation_workflow_goals
            SET total_achieved = total_achieved + 1
            WHERE id = ?
        ")->execute([$goalId]);

        return [
            'success' => true,
            'message' => 'Goal achievement tracked'
        ];
    }

    /**
     * Get workflow performance
     */
    public function getWorkflowPerformance(int $workflowId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                w.*,
                COUNT(DISTINCT wm.id) as total_members,
                COUNT(DISTINCT CASE WHEN wm.status = 'active' THEN wm.id END) as active,
                COUNT(DISTINCT CASE WHEN wm.status = 'completed' THEN wm.id END) as completed,
                COUNT(DISTINCT CASE WHEN wm.converted = TRUE THEN wm.id END) as conversions,
                COALESCE(SUM(wm.conversion_value), 0) as revenue
            FROM automation_workflows w
            LEFT JOIN automation_workflow_members wm ON w.id = wm.workflow_id
            WHERE w.id = ?
            GROUP BY w.id
        ");

        $stmt->execute([$workflowId]);
        $performance = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$performance) {
            return ['success' => false, 'error' => 'Workflow not found'];
        }

        // Calculate conversion rate
        $performance['conversion_rate'] = $performance['total_members'] > 0
            ? round(($performance['conversions'] / $performance['total_members']) * 100, 2)
            : 0;

        return [
            'success' => true,
            'performance' => $performance
        ];
    }
}
