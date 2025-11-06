<?php

namespace App\Controllers;

use App\Core\Controller;
use PDO;

class IncidentReportController extends Controller
{
    /**
     * Show incident report form (PADI Form 10120)
     * Mobile-optimized for use at incident scene
     */
    public function create(): void
    {
        $customerId = $_GET['customer_id'] ?? null;

        $db = $this->db();
        $customer = null;

        if ($customerId) {
            $stmt = $db->prepare("
                SELECT c.*,
                       CONCAT(c.first_name, ' ', c.last_name) as full_name
                FROM customers c
                WHERE c.id = ?
            ");
            $stmt->execute([$customerId]);
            $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        $this->view('incidents/create', [
            'customer' => $customer,
            'pageTitle' => 'Incident Report'
        ]);
    }

    /**
     * Submit incident report
     */
    public function store(): void
    {
        $db = $this->db();

        try {
            $db->beginTransaction();

            // Insert incident report
            $stmt = $db->prepare("
                INSERT INTO padi_incident_reports (
                    incident_date,
                    incident_time,
                    incident_location,
                    incident_gps_latitude,
                    incident_gps_longitude,
                    incident_type,
                    severity_level,
                    injured_person_name,
                    injured_person_customer_id,
                    injured_person_age,
                    injured_person_certification,
                    incident_description,
                    immediate_actions_taken,
                    medical_treatment_provided,
                    emergency_services_called,
                    emergency_service_details,
                    equipment_involved,
                    equipment_serial_numbers,
                    environmental_conditions,
                    water_temperature,
                    depth_at_incident,
                    visibility,
                    current_conditions,
                    witness_1_name,
                    witness_1_contact,
                    witness_1_statement,
                    witness_2_name,
                    witness_2_contact,
                    witness_2_statement,
                    instructor_name,
                    instructor_padi_number,
                    reported_by_user_id,
                    reported_by_name,
                    report_submission_date,
                    photos_attached,
                    photo_filenames,
                    follow_up_required,
                    follow_up_notes,
                    padi_notified,
                    padi_notification_date,
                    insurance_notified,
                    insurance_claim_number,
                    created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?,
                    ?, ?, ?, ?, NOW()
                )
            ");

            $stmt->execute([
                $_POST['incident_date'] ?? date('Y-m-d'),
                $_POST['incident_time'] ?? date('H:i:s'),
                $_POST['incident_location'] ?? null,
                $_POST['gps_latitude'] ?? null,
                $_POST['gps_longitude'] ?? null,
                $_POST['incident_type'] ?? 'other',
                $_POST['severity_level'] ?? 'medium',
                $_POST['injured_person_name'] ?? null,
                $_POST['customer_id'] ?? null,
                $_POST['injured_person_age'] ?? null,
                $_POST['injured_person_certification'] ?? null,
                $_POST['incident_description'] ?? null,
                $_POST['immediate_actions'] ?? null,
                $_POST['medical_treatment'] ?? null,
                isset($_POST['emergency_services_called']) ? 1 : 0,
                $_POST['emergency_service_details'] ?? null,
                $_POST['equipment_involved'] ?? null,
                $_POST['equipment_serial_numbers'] ?? null,
                $_POST['environmental_conditions'] ?? null,
                $_POST['water_temperature'] ?? null,
                $_POST['depth_at_incident'] ?? null,
                $_POST['visibility'] ?? null,
                $_POST['current_conditions'] ?? null,
                $_POST['witness_1_name'] ?? null,
                $_POST['witness_1_contact'] ?? null,
                $_POST['witness_1_statement'] ?? null,
                $_POST['witness_2_name'] ?? null,
                $_POST['witness_2_contact'] ?? null,
                $_POST['witness_2_statement'] ?? null,
                $_POST['instructor_name'] ?? null,
                $_POST['instructor_padi_number'] ?? null,
                $_SESSION['user_id'] ?? null,
                $_POST['reported_by_name'] ?? null,
                $_POST['report_submission_date'] ?? date('Y-m-d'),
                !empty($_FILES['photos']['name'][0]) ? 1 : 0,
                null, // Will update after photo upload
                isset($_POST['follow_up_required']) ? 1 : 0,
                $_POST['follow_up_notes'] ?? null,
                isset($_POST['padi_notified']) ? 1 : 0,
                $_POST['padi_notification_date'] ?? null,
                isset($_POST['insurance_notified']) ? 1 : 0,
                $_POST['insurance_claim_number'] ?? null
            ]);

            $incidentId = $db->lastInsertId();

            // Handle photo uploads
            if (!empty($_FILES['photos']['name'][0])) {
                $photoFilenames = $this->handlePhotoUploads($incidentId, $_FILES['photos']);

                $stmt = $db->prepare("
                    UPDATE padi_incident_reports
                    SET photo_filenames = ?
                    WHERE id = ?
                ");
                $stmt->execute([json_encode($photoFilenames), $incidentId]);
            }

            $db->commit();

            $_SESSION['success'] = 'Incident report submitted successfully';
            redirect('/store/incidents/' . $incidentId);

        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Failed to submit incident report: ' . $e->getMessage();
            redirect('/incidents/report');
        }
    }

    /**
     * View incident report
     */
    public function show(): void
    {
        $incidentId = $this->getRouteParam('id');

        $db = $this->db();
        $stmt = $db->prepare("
            SELECT ir.*,
                   c.first_name, c.last_name,
                   u.username as reported_by_username
            FROM padi_incident_reports ir
            LEFT JOIN customers c ON ir.injured_person_customer_id = c.id
            LEFT JOIN users u ON ir.reported_by_user_id = u.id
            WHERE ir.id = ?
        ");
        $stmt->execute([$incidentId]);
        $incident = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$incident) {
            $_SESSION['error'] = 'Incident report not found';
            redirect('/store/incidents');
            return;
        }

        $this->view('incidents/show', [
            'incident' => $incident,
            'pageTitle' => 'Incident Report #' . $incidentId
        ]);
    }

    /**
     * List all incident reports
     */
    public function index(): void
    {
        $db = $this->db();
        $stmt = $db->query("
            SELECT ir.*,
                   c.first_name, c.last_name
            FROM padi_incident_reports ir
            LEFT JOIN customers c ON ir.injured_person_customer_id = c.id
            ORDER BY ir.incident_date DESC, ir.incident_time DESC
        ");
        $incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('incidents/index', [
            'incidents' => $incidents,
            'pageTitle' => 'Incident Reports'
        ]);
    }

    /**
     * Handle photo uploads from incident scene
     */
    private function handlePhotoUploads(int $incidentId, array $files): array
    {
        $uploadDir = __DIR__ . '/../../public/uploads/incidents/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $uploadedFiles = [];
        $fileCount = count($files['name']);

        for ($i = 0; $i < $fileCount; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $extension = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                $filename = 'incident_' . $incidentId . '_' . $i . '_' . time() . '.' . $extension;
                $filepath = $uploadDir . $filename;

                if (move_uploaded_file($files['tmp_name'][$i], $filepath)) {
                    $uploadedFiles[] = $filename;
                }
            }
        }

        return $uploadedFiles;
    }
}
