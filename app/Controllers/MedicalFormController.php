<?php

namespace App\Controllers;

use App\Core\Controller;
use PDO;

class MedicalFormController extends Controller
{
    /**
     * Show medical form for customer
     */
    public function create(): void
    {
        // Get customer ID from query string or session
        $customerId = $_GET['customer_id'] ?? null;

        if (!$customerId) {
            $_SESSION['error'] = 'Customer ID is required';
            redirect('/store/customers');
            return;
        }

        // Get customer info
        $db = $this->db();
        $stmt = $db->prepare("
            SELECT c.*,
                   CONCAT(c.first_name, ' ', c.last_name) as full_name
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

        // Check if customer already has a valid medical form
        $stmt = $db->prepare("
            SELECT * FROM padi_medical_forms
            WHERE customer_id = ?
            AND expiry_date > CURDATE()
            ORDER BY submitted_at DESC
            LIMIT 1
        ");
        $stmt->execute([$customerId]);
        $existingForm = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get the 34 PADI medical questions
        $questions = $this->getMedicalQuestions();

        $this->view('medical/create', [
            'customer' => $customer,
            'existingForm' => $existingForm,
            'questions' => $questions,
            'pageTitle' => 'PADI Medical Form - ' . $customer['full_name']
        ]);
    }

    /**
     * Submit medical form
     */
    public function store(): void
    {
        $customerId = $_POST['customer_id'] ?? null;

        if (!$customerId) {
            $_SESSION['error'] = 'Customer ID is required';
            redirect('/store/customers');
            return;
        }

        $db = $this->db();

        try {
            $db->beginTransaction();

            // Calculate if physician clearance is required
            $requiresClearance = $this->requiresPhysicianClearance($_POST);

            // Insert medical form
            $stmt = $db->prepare("
                INSERT INTO padi_medical_forms (
                    customer_id,
                    q1_asthma, q2_heart_disease, q3_ear_problems, q4_sinus_problems,
                    q5_diabetes, q6_epilepsy, q7_chest_pain, q8_behavioral_health,
                    q9_head_injury, q10_high_blood_pressure, q11_lung_disease,
                    q12_nervous_system, q13_back_problems, q14_hernia, q15_ulcers,
                    q16_pregnancy, q17_smoking, q18_age_over_45, q19_difficulty_exercising,
                    q20_heart_surgery, q21_dive_accident, q22_medications,
                    q23_respiratory_problems, q24_behavioral_meds, q25_motion_sickness,
                    q26_dysentery, q27_dehydration, q28_family_history,
                    q29_fainting, q30_insomnia, q31_menstrual_problems,
                    q32_surgery_recent, q33_blood_disorders, q34_other_conditions,
                    requires_physician_clearance,
                    physician_clearance_obtained,
                    participant_signature_data,
                    participant_signature_date,
                    submitted_by_user_id,
                    submitted_at,
                    expiry_date
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, NOW(), ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 YEAR)
                )
            ");

            $stmt->execute([
                $customerId,
                $_POST['q1'] ?? 'no',
                $_POST['q2'] ?? 'no',
                $_POST['q3'] ?? 'no',
                $_POST['q4'] ?? 'no',
                $_POST['q5'] ?? 'no',
                $_POST['q6'] ?? 'no',
                $_POST['q7'] ?? 'no',
                $_POST['q8'] ?? 'no',
                $_POST['q9'] ?? 'no',
                $_POST['q10'] ?? 'no',
                $_POST['q11'] ?? 'no',
                $_POST['q12'] ?? 'no',
                $_POST['q13'] ?? 'no',
                $_POST['q14'] ?? 'no',
                $_POST['q15'] ?? 'no',
                $_POST['q16'] ?? 'no',
                $_POST['q17'] ?? 'no',
                $_POST['q18'] ?? 'no',
                $_POST['q19'] ?? 'no',
                $_POST['q20'] ?? 'no',
                $_POST['q21'] ?? 'no',
                $_POST['q22'] ?? 'no',
                $_POST['q23'] ?? 'no',
                $_POST['q24'] ?? 'no',
                $_POST['q25'] ?? 'no',
                $_POST['q26'] ?? 'no',
                $_POST['q27'] ?? 'no',
                $_POST['q28'] ?? 'no',
                $_POST['q29'] ?? 'no',
                $_POST['q30'] ?? 'no',
                $_POST['q31'] ?? 'no',
                $_POST['q32'] ?? 'no',
                $_POST['q33'] ?? 'no',
                $_POST['q34'] ?? 'no',
                $requiresClearance ? 1 : 0,
                0, // Will be updated when physician clearance is uploaded
                $_POST['signature_data'] ?? null,
                $_SESSION['user_id'] ?? null
            ]);

            $formId = $db->lastInsertId();

            // Handle physician clearance upload if provided
            if (!empty($_FILES['physician_clearance']['name'])) {
                $uploadResult = $this->handlePhysicianClearanceUpload($formId, $_FILES['physician_clearance']);

                if ($uploadResult['success']) {
                    // Update form with clearance info
                    $stmt = $db->prepare("
                        UPDATE padi_medical_forms
                        SET physician_clearance_obtained = 1,
                            physician_clearance_file = ?,
                            physician_clearance_date = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([$uploadResult['filename'], $formId]);
                }
            }

            $db->commit();

            $_SESSION['success'] = 'Medical form submitted successfully';

            if ($requiresClearance && empty($_FILES['physician_clearance']['name'])) {
                $_SESSION['warning'] = 'Physician clearance is required before diving. Please upload clearance form.';
            }

            redirect('/store/customers/' . $customerId);

        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Failed to submit medical form: ' . $e->getMessage();
            redirect('/medical/create?customer_id=' . $customerId);
        }
    }

    /**
     * View medical form
     */
    public function show(): void
    {
        $formId = $this->getRouteParam('id');

        $db = $this->db();
        $stmt = $db->prepare("
            SELECT mf.*,
                   c.first_name, c.last_name,
                   CONCAT(c.first_name, ' ', c.last_name) as customer_name,
                   u.username as submitted_by
            FROM padi_medical_forms mf
            JOIN customers c ON mf.customer_id = c.id
            LEFT JOIN users u ON mf.submitted_by_user_id = u.id
            WHERE mf.id = ?
        ");
        $stmt->execute([$formId]);
        $form = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$form) {
            $_SESSION['error'] = 'Medical form not found';
            redirect('/store/customers');
            return;
        }

        $questions = $this->getMedicalQuestions();

        $this->view('medical/show', [
            'form' => $form,
            'questions' => $questions,
            'pageTitle' => 'Medical Form - ' . $form['customer_name']
        ]);
    }

    /**
     * Upload physician clearance
     */
    public function uploadClearance(): void
    {
        $formId = $_POST['form_id'] ?? null;

        if (!$formId) {
            echo json_encode(['success' => false, 'message' => 'Form ID required']);
            return;
        }

        if (empty($_FILES['clearance_file']['name'])) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
            return;
        }

        $db = $this->db();

        try {
            $uploadResult = $this->handlePhysicianClearanceUpload($formId, $_FILES['clearance_file']);

            if ($uploadResult['success']) {
                $stmt = $db->prepare("
                    UPDATE padi_medical_forms
                    SET physician_clearance_obtained = 1,
                        physician_clearance_file = ?,
                        physician_clearance_date = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$uploadResult['filename'], $formId]);

                echo json_encode([
                    'success' => true,
                    'message' => 'Physician clearance uploaded successfully',
                    'filename' => $uploadResult['filename']
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
            }

        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Check if any answer requires physician clearance
     */
    private function requiresPhysicianClearance(array $answers): bool
    {
        // Any "yes" answer requires physician clearance per PADI standards
        foreach ($answers as $key => $value) {
            if (strpos($key, 'q') === 0 && $value === 'yes') {
                return true;
            }
        }
        return false;
    }

    /**
     * Handle physician clearance file upload
     */
    private function handlePhysicianClearanceUpload(int $formId, array $file): array
    {
        $uploadDir = __DIR__ . '/../../public/uploads/medical_clearances/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];

        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Only PDF and image files allowed'];
        }

        if ($file['size'] > 10 * 1024 * 1024) { // 10MB max
            return ['success' => false, 'message' => 'File size must be less than 10MB'];
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'medical_clearance_' . $formId . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'filename' => $filename];
        } else {
            return ['success' => false, 'message' => 'Failed to upload file'];
        }
    }

    /**
     * Get 34 PADI medical questions
     */
    private function getMedicalQuestions(): array
    {
        return [
            ['id' => 'q1', 'text' => 'Asthma, or wheezing with breathing, or wheezing with exercise?'],
            ['id' => 'q2', 'text' => 'Frequent or severe suffering from hay fever or allergy attacks?'],
            ['id' => 'q3', 'text' => 'Frequent colds, sinusitis or bronchitis?'],
            ['id' => 'q4', 'text' => 'Any form of lung disease?'],
            ['id' => 'q5', 'text' => 'Pneumothorax (collapsed lung)?'],
            ['id' => 'q6', 'text' => 'Other chest disease or chest surgery?'],
            ['id' => 'q7', 'text' => 'Behavioral health, mental or psychological problems (panic attack, fear of closed or open spaces)?'],
            ['id' => 'q8', 'text' => 'Epilepsy, seizures, convulsions or take medications to prevent them?'],
            ['id' => 'q9', 'text' => 'Recurring complicated migraine headaches or take medications to prevent them?'],
            ['id' => 'q10', 'text' => 'Blackouts or fainting (full/partial loss of consciousness)?'],
            ['id' => 'q11', 'text' => 'Frequent or severe suffering from motion sickness (seasick, carsick, etc.)?'],
            ['id' => 'q12', 'text' => 'Dysentery or dehydration requiring medical intervention?'],
            ['id' => 'q13', 'text' => 'Any dive injury or decompression sickness?'],
            ['id' => 'q14', 'text' => 'Inability to perform moderate exercise (e.g., walk 1.6 km/one mile in 14 minutes or swim 200 m/yards in 14 minutes without resting)?'],
            ['id' => 'q15', 'text' => 'Head injury with loss of consciousness within the past five years?'],
            ['id' => 'q16', 'text' => 'Persistent neurological injury or disease?'],
            ['id' => 'q17', 'text' => 'Recurrent back problems?'],
            ['id' => 'q18', 'text' => 'Back or spinal surgery?'],
            ['id' => 'q19', 'text' => 'Diabetes?'],
            ['id' => 'q20', 'text' => 'Back, arm or leg problems following surgery, injury or fracture?'],
            ['id' => 'q21', 'text' => 'High blood pressure or take medicine to control blood pressure?'],
            ['id' => 'q22', 'text' => 'Heart disease?'],
            ['id' => 'q23', 'text' => 'Heart attack?'],
            ['id' => 'q24', 'text' => 'Angina, heart surgery or blood vessel surgery?'],
            ['id' => 'q25', 'text' => 'Sinus surgery?'],
            ['id' => 'q26', 'text' => 'Ear disease or surgery, hearing loss or problems with balance?'],
            ['id' => 'q27', 'text' => 'Recurrent ear problems?'],
            ['id' => 'q28', 'text' => 'Bleeding or other blood disorders?'],
            ['id' => 'q29', 'text' => 'Hernia?'],
            ['id' => 'q30', 'text' => 'Ulcers or ulcer surgery?'],
            ['id' => 'q31', 'text' => 'A colostomy or ileostomy?'],
            ['id' => 'q32', 'text' => 'Recreational drug use or treatment for, or alcoholism within the past five years?'],
            ['id' => 'q33', 'text' => 'Pregnant, or attempting to become pregnant?'],
            ['id' => 'q34', 'text' => 'Take prescription medications? (except birth control or anti-malarial)']
        ];
    }
}
