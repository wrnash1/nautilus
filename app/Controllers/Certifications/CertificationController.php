<?php

namespace App\Controllers\Certifications;

use App\Core\Database;

class CertificationController
{
    public function __construct()
    {
        // Ensure user is authenticated
        if (!isLoggedIn()) {
            redirect('/login');
        }
    }

    /**
     * Display list of all certifications
     */
    public function index()
    {
        if (!hasPermission('certifications.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        $page = (int)($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $search = sanitizeInput($_GET['search'] ?? '');

        $sql = "SELECT c.*, ca.name as agency_name, ca.code as agency_code,
                       COUNT(DISTINCT cc.id) as students_certified
                FROM certifications c
                LEFT JOIN certification_agencies ca ON c.agency_id = ca.id
                LEFT JOIN customer_certifications cc ON c.id = cc.certification_id
                WHERE 1=1";

        $params = [];

        if (!empty($search)) {
            $sql .= " AND (c.name LIKE ? OR c.code LIKE ? OR ca.name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = [$searchTerm, $searchTerm, $searchTerm];
        }

        $sql .= " GROUP BY c.id ORDER BY c.name ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $certifications = Database::fetchAll($sql, $params);

        // Get total count for pagination
        $countSql = "SELECT COUNT(DISTINCT c.id) as total FROM certifications c";
        if (!empty($search)) {
            $countSql .= " LEFT JOIN certification_agencies ca ON c.agency_id = ca.id
                           WHERE c.name LIKE ? OR c.code LIKE ? OR ca.name LIKE ?";
            $total = Database::fetchOne($countSql, [$searchTerm, $searchTerm, $searchTerm])['total'] ?? 0;
        } else {
            $total = Database::fetchOne($countSql)['total'] ?? 0;
        }

        $totalPages = ceil($total / $limit);

        require __DIR__ . '/../../Views/certifications/index.php';
    }

    /**
     * Show form to create new certification
     */
    public function create()
    {
        if (!hasPermission('certifications.create')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/certifications');
        }

        // Get active certification agencies
        $agencies = Database::fetchAll(
            "SELECT id, name, code FROM certification_agencies WHERE is_active = 1 ORDER BY name"
        );

        require __DIR__ . '/../../Views/certifications/create.php';
    }

    /**
     * Store new certification
     */
    public function store()
    {
        if (!hasPermission('certifications.create')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $data = [
                'agency_id' => (int)($_POST['agency_id'] ?? 0),
                'name' => sanitizeInput($_POST['name'] ?? ''),
                'code' => sanitizeInput($_POST['code'] ?? ''),
                'level' => sanitizeInput($_POST['level'] ?? ''),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'prerequisites' => sanitizeInput($_POST['prerequisites'] ?? ''),
                'minimum_age' => (int)($_POST['minimum_age'] ?? 0),
                'course_duration_days' => (int)($_POST['course_duration_days'] ?? 0),
                'max_depth_meters' => (float)($_POST['max_depth_meters'] ?? 0),
                'price' => (float)($_POST['price'] ?? 0),
                'certification_fee' => (float)($_POST['certification_fee'] ?? 0),
                'materials_cost' => (float)($_POST['materials_cost'] ?? 0),
                'expiration_months' => !empty($_POST['expiration_months']) ? (int)$_POST['expiration_months'] : null,
                'requires_renewal' => isset($_POST['requires_renewal']) ? 1 : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];

            // Validation
            if (empty($data['name'])) {
                throw new \Exception('Certification name is required');
            }

            if (empty($data['agency_id'])) {
                throw new \Exception('Certification agency is required');
            }

            $certificationId = Database::insert('certifications', $data);

            $_SESSION['flash_success'] = 'Certification created successfully';
            redirect("/certifications/{$certificationId}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect('/certifications/create');
        }
    }

    /**
     * Display specific certification details
     */
    public function show(int $id)
    {
        if (!hasPermission('certifications.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        $certification = Database::fetchOne(
            "SELECT c.*, ca.name as agency_name, ca.code as agency_code,
                    ca.website as agency_website, ca.logo_url as agency_logo
             FROM certifications c
             LEFT JOIN certification_agencies ca ON c.agency_id = ca.id
             WHERE c.id = ?",
            [$id]
        );

        if (!$certification) {
            $_SESSION['flash_error'] = 'Certification not found';
            redirect('/certifications');
        }

        // Get students who have this certification
        $students = Database::fetchAll(
            "SELECT cc.*,
                    CONCAT(cust.first_name, ' ', cust.last_name) as student_name,
                    cust.email as student_email,
                    cust.phone as student_phone
             FROM customer_certifications cc
             INNER JOIN customers cust ON cc.customer_id = cust.id
             WHERE cc.certification_id = ?
             ORDER BY cc.issue_date DESC
             LIMIT 50",
            [$id]
        );

        // Get prerequisite certifications
        $prerequisites = [];
        if (!empty($certification['prerequisites'])) {
            $prereqIds = explode(',', $certification['prerequisites']);
            if (!empty($prereqIds)) {
                $placeholders = implode(',', array_fill(0, count($prereqIds), '?'));
                $prerequisites = Database::fetchAll(
                    "SELECT id, name, code FROM certifications WHERE id IN ($placeholders)",
                    $prereqIds
                );
            }
        }

        require __DIR__ . '/../../Views/certifications/show.php';
    }

    /**
     * Show form to edit certification
     */
    public function edit(int $id)
    {
        if (!hasPermission('certifications.edit')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/certifications');
        }

        $certification = Database::fetchOne("SELECT * FROM certifications WHERE id = ?", [$id]);

        if (!$certification) {
            $_SESSION['flash_error'] = 'Certification not found';
            redirect('/certifications');
        }

        // Get active certification agencies
        $agencies = Database::fetchAll(
            "SELECT id, name, code FROM certification_agencies WHERE is_active = 1 ORDER BY name"
        );

        require __DIR__ . '/../../Views/certifications/edit.php';
    }

    /**
     * Update certification
     */
    public function update(int $id)
    {
        if (!hasPermission('certifications.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $certification = Database::fetchOne("SELECT id FROM certifications WHERE id = ?", [$id]);

            if (!$certification) {
                throw new \Exception('Certification not found');
            }

            $data = [
                'agency_id' => (int)($_POST['agency_id'] ?? 0),
                'name' => sanitizeInput($_POST['name'] ?? ''),
                'code' => sanitizeInput($_POST['code'] ?? ''),
                'level' => sanitizeInput($_POST['level'] ?? ''),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'prerequisites' => sanitizeInput($_POST['prerequisites'] ?? ''),
                'minimum_age' => (int)($_POST['minimum_age'] ?? 0),
                'course_duration_days' => (int)($_POST['course_duration_days'] ?? 0),
                'max_depth_meters' => (float)($_POST['max_depth_meters'] ?? 0),
                'price' => (float)($_POST['price'] ?? 0),
                'certification_fee' => (float)($_POST['certification_fee'] ?? 0),
                'materials_cost' => (float)($_POST['materials_cost'] ?? 0),
                'expiration_months' => !empty($_POST['expiration_months']) ? (int)$_POST['expiration_months'] : null,
                'requires_renewal' => isset($_POST['requires_renewal']) ? 1 : 0,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];

            // Validation
            if (empty($data['name'])) {
                throw new \Exception('Certification name is required');
            }

            Database::update('certifications', $data, ['id' => $id]);

            $_SESSION['flash_success'] = 'Certification updated successfully';
            redirect("/certifications/{$id}");
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect("/certifications/{$id}/edit");
        }
    }

    /**
     * Delete certification
     */
    public function delete(int $id)
    {
        if (!hasPermission('certifications.delete')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            // Check if certification has students
            $hasStudents = Database::fetchOne(
                "SELECT COUNT(*) as count FROM customer_certifications WHERE certification_id = ?",
                [$id]
            )['count'] ?? 0;

            if ($hasStudents > 0) {
                throw new \Exception('Cannot delete certification that has students certified. Please deactivate instead.');
            }

            Database::delete('certifications', ['id' => $id]);

            $_SESSION['flash_success'] = 'Certification deleted successfully';
            redirect('/certifications');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect("/certifications/{$id}");
        }
    }

    /**
     * Display list of certification agencies
     */
    public function agencies()
    {
        if (!hasPermission('certifications.view')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/');
        }

        $agencies = Database::fetchAll(
            "SELECT ca.*,
                    COUNT(DISTINCT c.id) as certification_count,
                    COUNT(DISTINCT cc.id) as students_certified
             FROM certification_agencies ca
             LEFT JOIN certifications c ON ca.id = c.agency_id
             LEFT JOIN customer_certifications cc ON c.id = cc.certification_id
             GROUP BY ca.id
             ORDER BY ca.name"
        );

        require __DIR__ . '/../../Views/certifications/agencies/index.php';
    }

    /**
     * Show form to create new agency
     */
    public function createAgency()
    {
        if (!hasPermission('certifications.create')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/certifications/agencies');
        }

        require __DIR__ . '/../../Views/certifications/agencies/create.php';
    }

    /**
     * Store new agency
     */
    public function storeAgency()
    {
        if (!hasPermission('certifications.create')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $data = [
                'name' => sanitizeInput($_POST['name'] ?? ''),
                'code' => sanitizeInput($_POST['code'] ?? ''),
                'website' => sanitizeInput($_POST['website'] ?? ''),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'logo_url' => sanitizeInput($_POST['logo_url'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];

            if (empty($data['name'])) {
                throw new \Exception('Agency name is required');
            }

            $agencyId = Database::insert('certification_agencies', $data);

            $_SESSION['flash_success'] = 'Certification agency created successfully';
            redirect('/certifications/agencies');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect('/certifications/agencies/create');
        }
    }

    /**
     * Show form to edit agency
     */
    public function editAgency(int $id)
    {
        if (!hasPermission('certifications.edit')) {
            $_SESSION['flash_error'] = 'Access denied';
            redirect('/certifications/agencies');
        }

        $agency = Database::fetchOne("SELECT * FROM certification_agencies WHERE id = ?", [$id]);

        if (!$agency) {
            $_SESSION['flash_error'] = 'Agency not found';
            redirect('/certifications/agencies');
        }

        require __DIR__ . '/../../Views/certifications/agencies/edit.php';
    }

    /**
     * Update agency
     */
    public function updateAgency(int $id)
    {
        if (!hasPermission('certifications.edit')) {
            jsonResponse(['error' => 'Access denied'], 403);
        }

        try {
            $agency = Database::fetchOne("SELECT id FROM certification_agencies WHERE id = ?", [$id]);

            if (!$agency) {
                throw new \Exception('Agency not found');
            }

            $data = [
                'name' => sanitizeInput($_POST['name'] ?? ''),
                'code' => sanitizeInput($_POST['code'] ?? ''),
                'website' => sanitizeInput($_POST['website'] ?? ''),
                'description' => sanitizeInput($_POST['description'] ?? ''),
                'logo_url' => sanitizeInput($_POST['logo_url'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];

            if (empty($data['name'])) {
                throw new \Exception('Agency name is required');
            }

            Database::update('certification_agencies', $data, ['id' => $id]);

            $_SESSION['flash_success'] = 'Agency updated successfully';
            redirect('/certifications/agencies');
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect("/certifications/agencies/{$id}/edit");
        }
    }
}
