<?php

namespace App\Controllers\Staff;

use App\Core\Controller;
use App\Core\Database;

/**
 * Employee Documents Controller
 * Handles IRS forms, HR documents, and payroll records
 */
class EmployeeDocumentsController extends Controller
{
    /**
     * Employee documents dashboard
     */
    public function index()
    {
        $this->requireAuth();
        $this->requirePermission('hr.view');

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        // Get all employees with document status
        $stmt = $db->prepare("
            SELECT u.id, u.first_name, u.last_name, u.email, u.created_at,
                   (SELECT COUNT(*) FROM employee_documents WHERE user_id = u.id) as doc_count,
                   (SELECT MAX(created_at) FROM employee_documents WHERE user_id = u.id) as last_doc_upload
            FROM users u
            WHERE u.tenant_id = ? AND u.is_active = 1
            ORDER BY u.first_name, u.last_name
        ");
        $stmt->execute([$tenantId]);
        $employees = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get pending documents
        $stmt = $db->prepare("
            SELECT ed.*, u.first_name, u.last_name
            FROM employee_documents ed
            JOIN users u ON ed.user_id = u.id
            WHERE ed.tenant_id = ? AND ed.status = 'pending'
            ORDER BY ed.created_at DESC
        ");
        $stmt->execute([$tenantId]);
        $pendingDocs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get expiring documents (within 30 days)
        $stmt = $db->prepare("
            SELECT ed.*, u.first_name, u.last_name
            FROM employee_documents ed
            JOIN users u ON ed.user_id = u.id
            WHERE ed.tenant_id = ? 
            AND ed.expires_at IS NOT NULL 
            AND ed.expires_at <= DATE_ADD(NOW(), INTERVAL 30 DAY)
            AND ed.expires_at >= NOW()
            ORDER BY ed.expires_at
        ");
        $stmt->execute([$tenantId]);
        $expiringDocs = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $this->view('staff/documents/index', [
            'pageTitle' => 'Employee Documents',
            'employees' => $employees,
            'pendingDocs' => $pendingDocs,
            'expiringDocs' => $expiringDocs
        ]);
    }

    /**
     * View employee documents
     */
    public function show($userId)
    {
        $this->requireAuth();
        $this->requirePermission('hr.view');

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        // Get employee
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$userId, $tenantId]);
        $employee = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$employee) {
            $_SESSION['error'] = 'Employee not found.';
            redirect('/store/staff/documents');
            return;
        }

        // Get documents
        $stmt = $db->prepare("
            SELECT ed.*, v.first_name as verified_by_name, v.last_name as verified_by_last
            FROM employee_documents ed
            LEFT JOIN users v ON ed.verified_by = v.id
            WHERE ed.user_id = ?
            ORDER BY ed.document_type, ed.tax_year DESC
        ");
        $stmt->execute([$userId]);
        $documents = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get tax info
        $currentYear = date('Y');
        $stmt = $db->prepare("
            SELECT * FROM employee_tax_info 
            WHERE user_id = ? AND tax_year = ?
        ");
        $stmt->execute([$userId, $currentYear]);
        $taxInfo = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Get payroll summary
        $stmt = $db->prepare("
            SELECT 
                SUM(gross_pay) as ytd_gross,
                SUM(net_pay) as ytd_net,
                SUM(federal_tax) as ytd_federal,
                SUM(state_tax) as ytd_state,
                COUNT(*) as pay_periods
            FROM payroll_records
            WHERE user_id = ? AND YEAR(pay_date) = ?
        ");
        $stmt->execute([$userId, $currentYear]);
        $payrollSummary = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->view('staff/documents/show', [
            'pageTitle' => 'Documents: ' . $employee['first_name'] . ' ' . $employee['last_name'],
            'employee' => $employee,
            'documents' => $documents,
            'taxInfo' => $taxInfo,
            'payrollSummary' => $payrollSummary
        ]);
    }

    /**
     * Upload document form
     */
    public function create($userId)
    {
        $this->requireAuth();
        $this->requirePermission('hr.edit');

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$userId, $tenantId]);
        $employee = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$employee) {
            $_SESSION['error'] = 'Employee not found.';
            redirect('/store/staff/documents');
            return;
        }

        $this->view('staff/documents/create', [
            'pageTitle' => 'Upload Document',
            'employee' => $employee,
            'documentTypes' => [
                'w4' => 'W-4 (Employee Withholding)',
                'i9' => 'I-9 (Employment Eligibility)',
                'w2' => 'W-2 (Wage Statement)',
                'w9' => 'W-9 (TIN Request)',
                '1099' => '1099-NEC (Contractor)',
                'state_w4' => 'State W-4',
                'direct_deposit' => 'Direct Deposit Form',
                'other' => 'Other'
            ]
        ]);
    }

    /**
     * Store uploaded document
     */
    public function store($userId)
    {
        $this->requireAuth();
        $this->requirePermission('hr.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/store/staff/documents/' . $userId);
            return;
        }

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        // Validate employee
        $stmt = $db->prepare("SELECT id FROM users WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$userId, $tenantId]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = 'Employee not found.';
            redirect('/store/staff/documents');
            return;
        }

        $documentType = $_POST['document_type'] ?? '';
        $taxYear = $_POST['tax_year'] ?? date('Y');
        $notes = trim($_POST['notes'] ?? '');
        $ssn = $_POST['ssn'] ?? '';

        // Handle file upload
        if (!isset($_FILES['document']) || $_FILES['document']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = 'Please select a document to upload.';
            redirect('/store/staff/documents/' . $userId . '/upload');
            return;
        }

        $file = $_FILES['document'];
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];

        if (!in_array($file['type'], $allowedTypes)) {
            $_SESSION['error'] = 'Invalid file type. Please upload PDF, JPG, or PNG.';
            redirect('/store/staff/documents/' . $userId . '/upload');
            return;
        }

        // Create storage directory
        $uploadDir = APP_PATH . '/storage/employee_documents/' . $userId;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate safe filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $documentType . '_' . $taxYear . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            $_SESSION['error'] = 'Failed to save document.';
            redirect('/store/staff/documents/' . $userId . '/upload');
            return;
        }

        // Encrypt SSN if provided
        $encryptedSsn = null;
        $ssnLastFour = null;
        if (!empty($ssn) && strlen($ssn) >= 4) {
            $ssnLastFour = substr(preg_replace('/[^0-9]/', '', $ssn), -4);
            // In production, use proper encryption key from environment
            $encryptionKey = hash('sha256', 'nautilus_ssn_key');
            $encryptedSsn = openssl_encrypt($ssn, 'aes-256-cbc', $encryptionKey, 0, substr($encryptionKey, 0, 16));
        }

        // Save to database
        $stmt = $db->prepare("
            INSERT INTO employee_documents (
                tenant_id, user_id, document_type, tax_year, 
                file_path, file_name, file_size, mime_type,
                encrypted_ssn, ssn_last_four, status, notes, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted', ?, NOW())
        ");
        $stmt->execute([
            $tenantId,
            $userId,
            $documentType,
            $taxYear,
            'employee_documents/' . $userId . '/' . $filename,
            $file['name'],
            $file['size'],
            $file['type'],
            $encryptedSsn,
            $ssnLastFour,
            $notes
        ]);

        $_SESSION['success'] = 'Document uploaded successfully.';
        redirect('/store/staff/documents/' . $userId);
    }

    /**
     * Verify document
     */
    public function verify($id)
    {
        $this->requireAuth();
        $this->requirePermission('hr.edit');

        $tenantId = $_SESSION['tenant_id'];
        $verifiedBy = $_SESSION['user']['id'];
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            UPDATE employee_documents 
            SET status = 'verified', verified_by = ?, verified_at = NOW()
            WHERE id = ? AND tenant_id = ?
        ");
        $stmt->execute([$verifiedBy, $id, $tenantId]);

        $_SESSION['success'] = 'Document verified.';
        redirect($_SERVER['HTTP_REFERER'] ?? '/store/staff/documents');
    }

    /**
     * Download document
     */
    public function download($id)
    {
        $this->requireAuth();
        $this->requirePermission('hr.view');

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("
            SELECT * FROM employee_documents 
            WHERE id = ? AND tenant_id = ?
        ");
        $stmt->execute([$id, $tenantId]);
        $doc = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$doc) {
            $_SESSION['error'] = 'Document not found.';
            redirect('/store/staff/documents');
            return;
        }

        $filePath = APP_PATH . '/storage/' . $doc['file_path'];

        if (!file_exists($filePath)) {
            $_SESSION['error'] = 'File not found.';
            redirect('/store/staff/documents');
            return;
        }

        header('Content-Type: ' . $doc['mime_type']);
        header('Content-Disposition: attachment; filename="' . $doc['file_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    /**
     * Tax info form
     */
    public function taxInfo($userId)
    {
        $this->requireAuth();
        $this->requirePermission('hr.edit');

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$userId, $tenantId]);
        $employee = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$employee) {
            $_SESSION['error'] = 'Employee not found.';
            redirect('/store/staff/documents');
            return;
        }

        $currentYear = date('Y');
        $stmt = $db->prepare("SELECT * FROM employee_tax_info WHERE user_id = ? AND tax_year = ?");
        $stmt->execute([$userId, $currentYear]);
        $taxInfo = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->view('staff/documents/tax-info', [
            'pageTitle' => 'Tax Information',
            'employee' => $employee,
            'taxInfo' => $taxInfo,
            'taxYear' => $currentYear
        ]);
    }

    /**
     * Save tax info
     */
    public function saveTaxInfo($userId)
    {
        $this->requireAuth();
        $this->requirePermission('hr.edit');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/store/staff/documents/' . $userId . '/tax-info');
            return;
        }

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        $taxYear = $_POST['tax_year'] ?? date('Y');

        $stmt = $db->prepare("
            INSERT INTO employee_tax_info (
                tenant_id, user_id, tax_year, filing_status,
                federal_allowances, state_allowances, additional_withholding,
                exempt_from_withholding, is_contractor, hourly_rate, salary_annual
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                filing_status = VALUES(filing_status),
                federal_allowances = VALUES(federal_allowances),
                state_allowances = VALUES(state_allowances),
                additional_withholding = VALUES(additional_withholding),
                exempt_from_withholding = VALUES(exempt_from_withholding),
                is_contractor = VALUES(is_contractor),
                hourly_rate = VALUES(hourly_rate),
                salary_annual = VALUES(salary_annual),
                updated_at = NOW()
        ");

        $stmt->execute([
            $tenantId,
            $userId,
            $taxYear,
            $_POST['filing_status'] ?? null,
            $_POST['federal_allowances'] ?? 0,
            $_POST['state_allowances'] ?? 0,
            $_POST['additional_withholding'] ?? 0,
            isset($_POST['exempt_from_withholding']) ? 1 : 0,
            isset($_POST['is_contractor']) ? 1 : 0,
            $_POST['hourly_rate'] ?: null,
            $_POST['salary_annual'] ?: null
        ]);

        $_SESSION['success'] = 'Tax information saved.';
        redirect('/store/staff/documents/' . $userId);
    }

    /**
     * Generate W-2 preview
     */
    public function generateW2($userId, $taxYear)
    {
        $this->requireAuth();
        $this->requirePermission('hr.edit');

        $tenantId = $_SESSION['tenant_id'];
        $db = Database::getInstance()->getConnection();

        // Get employee
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$userId, $tenantId]);
        $employee = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Get payroll totals
        $stmt = $db->prepare("
            SELECT 
                SUM(gross_pay) as total_wages,
                SUM(federal_tax) as federal_withheld,
                SUM(state_tax) as state_withheld,
                SUM(social_security) as ss_withheld,
                SUM(medicare) as medicare_withheld,
                SUM(tips) as total_tips
            FROM payroll_records
            WHERE user_id = ? AND YEAR(pay_date) = ?
        ");
        $stmt->execute([$userId, $taxYear]);
        $payroll = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Get company info
        $stmt = $db->prepare("
            SELECT setting_key, setting_value 
            FROM settings 
            WHERE tenant_id = ? AND setting_key LIKE 'company_%'
        ");
        $stmt->execute([$tenantId]);
        $company = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $company[$row['setting_key']] = $row['setting_value'];
        }

        $this->view('staff/documents/w2-preview', [
            'pageTitle' => 'W-2 Preview - ' . $taxYear,
            'employee' => $employee,
            'payroll' => $payroll,
            'company' => $company,
            'taxYear' => $taxYear
        ]);
    }
}
