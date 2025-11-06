<?php

namespace App\Controllers;

use App\Core\Controller;
use PDO;

class WaiverSigningController extends Controller
{
    /**
     * Show waiver signing page
     */
    public function create(): void
    {
        $customerId = $_GET['customer_id'] ?? null;
        $waiverType = $_GET['type'] ?? 'general_liability';

        if (!$customerId) {
            $_SESSION['error'] = 'Customer ID is required';
            redirect('/store/customers');
            return;
        }

        $db = $this->db();

        // Get customer info
        $stmt = $db->prepare("
            SELECT c.*,
                   CONCAT(c.first_name, ' ', c.last_name) as full_name,
                   c.date_of_birth
            FROM customers c
            WHERE c.id = ?
        ");
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$customer) {
            $_SESSION['error'] = 'Customer not found';
            redirect('/store/customers');
            return;
        }

        // Calculate if minor (under 18)
        $isMinor = false;
        if ($customer['date_of_birth']) {
            $dob = new \DateTime($customer['date_of_birth']);
            $now = new \DateTime();
            $age = $now->diff($dob)->y;
            $isMinor = $age < 18;
        }

        // Get waiver text
        $waiverText = $this->getWaiverText($waiverType);

        $this->view('waivers/sign', [
            'customer' => $customer,
            'waiverType' => $waiverType,
            'waiverText' => $waiverText,
            'isMinor' => $isMinor,
            'pageTitle' => 'Sign Waiver - ' . $customer['full_name']
        ]);
    }

    /**
     * Submit signed waiver
     */
    public function store(): void
    {
        $customerId = $_POST['customer_id'] ?? null;
        $waiverType = $_POST['waiver_type'] ?? 'general_liability';

        if (!$customerId) {
            $_SESSION['error'] = 'Customer ID is required';
            redirect('/store/customers');
            return;
        }

        $db = $this->db();

        try {
            $db->beginTransaction();

            // Insert waiver record
            $stmt = $db->prepare("
                INSERT INTO padi_liability_waivers (
                    customer_id,
                    waiver_type,
                    participant_signature_data,
                    participant_signature_date,
                    participant_name_typed,
                    participant_date_signed,
                    witness_signature_data,
                    witness_signature_date,
                    witness_name,
                    parent_guardian_signature_data,
                    parent_guardian_signature_date,
                    parent_guardian_name,
                    parent_guardian_relationship,
                    waiver_text,
                    acknowledgment_1,
                    acknowledgment_2,
                    acknowledgment_3,
                    ip_address,
                    user_agent,
                    signed_at,
                    expiry_date,
                    status
                ) VALUES (
                    ?, ?, ?, NOW(), ?, ?, ?, NOW(), ?, ?, NOW(), ?, ?,
                    ?, ?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR), 'active'
                )
            ");

            $stmt->execute([
                $customerId,
                $waiverType,
                $_POST['participant_signature'] ?? null,
                $_POST['participant_name'] ?? null,
                $_POST['participant_date'] ?? date('Y-m-d'),
                $_POST['witness_signature'] ?? null,
                $_POST['witness_name'] ?? null,
                $_POST['parent_signature'] ?? null,
                $_POST['parent_name'] ?? null,
                $_POST['parent_relationship'] ?? null,
                $_POST['waiver_text'] ?? '',
                isset($_POST['ack1']) ? 1 : 0,
                isset($_POST['ack2']) ? 1 : 0,
                isset($_POST['ack3']) ? 1 : 0,
                $_SERVER['REMOTE_ADDR'] ?? null,
                $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);

            $waiverId = $db->lastInsertId();

            // TODO: Generate PDF of signed waiver

            $db->commit();

            $_SESSION['success'] = 'Waiver signed successfully';
            redirect('/store/customers/' . $customerId);

        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Failed to save waiver: ' . $e->getMessage();
            redirect('/waivers/sign?customer_id=' . $customerId);
        }
    }

    /**
     * View signed waiver
     */
    public function show(): void
    {
        $waiverId = $this->getRouteParam('id');

        $db = $this->db();
        $stmt = $db->prepare("
            SELECT w.*,
                   c.first_name, c.last_name,
                   CONCAT(c.first_name, ' ', c.last_name) as customer_name
            FROM padi_liability_waivers w
            JOIN customers c ON w.customer_id = c.id
            WHERE w.id = ?
        ");
        $stmt->execute([$waiverId]);
        $waiver = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$waiver) {
            $_SESSION['error'] = 'Waiver not found';
            redirect('/store/customers');
            return;
        }

        $this->view('waivers/show', [
            'waiver' => $waiver,
            'pageTitle' => 'Signed Waiver - ' . $waiver['customer_name']
        ]);
    }

    /**
     * Get waiver text by type
     */
    private function getWaiverText(string $type): string
    {
        $waivers = [
            'general_liability' => "
                <h4>GENERAL LIABILITY RELEASE AND ASSUMPTION OF RISK</h4>
                <p>I understand and agree that skin and scuba diving are physically strenuous activities and that I will be exerting myself during this diving activity, and that if I am injured as a result of heart attack, panic, hyperventilation, drowning or any other cause, that I expressly assume the risk of said injuries and that I will not hold the released parties responsible for the same.</p>

                <p>I further state that I am of lawful age and legally competent to sign this liability release, or that I have acquired the written consent of my parent or guardian. I understand the terms herein are contractual and not a mere recital, and that I have signed this document as my own free act.</p>

                <p>I HAVE FULLY INFORMED MYSELF OF THE CONTENTS OF THIS LIABILITY RELEASE AND ASSUMPTION OF RISK BY READING IT BEFORE I SIGNED IT.</p>
            ",
            'scuba_liability' => "
                <h4>SAFE DIVING PRACTICES STATEMENT OF UNDERSTANDING</h4>
                <p>I understand that skin and scuba diving have inherent risks which may result in serious injury or death.</p>

                <p>I will only participate in skin/scuba diving activities if I am in good health. I am aware that there are certain medical conditions that may be dangerous when skin/scuba diving. I will confirm that I have completed the PADI Medical Statement and have been cleared for diving by a physician if any medical conditions apply.</p>

                <p>I understand and agree that neither my instructor(s), the facility through which I receive my instruction, PADI Americas, Inc., nor PADI Worldwide Corp. may be held liable or responsible for any injuries that may occur as a result of my participation in scuba diving activities.</p>
            "
        ];

        return $waivers[$type] ?? $waivers['general_liability'];
    }
}
