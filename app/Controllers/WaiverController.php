<?php

namespace App\Controllers;

use App\Services\Waiver\WaiverService;

class WaiverController
{
    private WaiverService $waiverService;

    public function __construct()
    {
        $this->waiverService = new WaiverService();
    }

    /**
     * Display waiver signing page (public - accessed via email link)
     */
    public function sign(string $token)
    {
        $waiverData = $this->waiverService->getWaiverByToken($token);

        if (!$waiverData) {
            $pageTitle = 'Invalid Waiver Link';
            $error = 'This waiver link is invalid or has expired.';
            require BASE_PATH . '/app/Views/waivers/error.php';
            return;
        }

        $pageTitle = $waiverData['title'];
        require BASE_PATH . '/app/Views/waivers/sign.php';
    }

    /**
     * Process waiver signature submission
     */
    public function submitSignature(string $token)
    {
        $waiverData = $this->waiverService->getWaiverByToken($token);

        if (!$waiverData) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid or expired waiver link']);
            return;
        }

        try {
            $signatureData = $_POST['signature_data'] ?? '';

            if (empty($signatureData)) {
                throw new \Exception('Signature is required');
            }

            // Prepare waiver data
            $data = [
                'waiver_template_id' => $waiverData['waiver_template_id'],
                'customer_id' => $waiverData['customer_id'],
                'reference_type' => $waiverData['reference_type'],
                'reference_id' => $waiverData['reference_id'],
                'signature_data' => $signatureData,
                'customer_name' => $_POST['customer_name'] ?? $waiverData['first_name'] . ' ' . $waiverData['last_name'],
                'customer_email' => $_POST['customer_email'] ?? $waiverData['customer_email'],
                'customer_phone' => $_POST['customer_phone'] ?? '',
                'customer_dob' => $_POST['customer_dob'] ?? null,
                'queue_token' => $token
            ];

            // Emergency contact (if required)
            if ($waiverData['requires_emergency_contact']) {
                $data['emergency_contact_name'] = $_POST['emergency_contact_name'] ?? '';
                $data['emergency_contact_phone'] = $_POST['emergency_contact_phone'] ?? '';
                $data['emergency_contact_relationship'] = $_POST['emergency_contact_relationship'] ?? '';
            }

            // Medical info (if required)
            if ($waiverData['requires_medical_info']) {
                $data['has_medical_conditions'] = isset($_POST['has_medical_conditions']) ? 1 : 0;
                $data['medical_conditions'] = $_POST['medical_conditions'] ?? '';
                $data['medications'] = $_POST['medications'] ?? '';
                $data['allergies'] = $_POST['allergies'] ?? '';
            }

            // Set valid_until based on service type
            $validityDays = [
                'rental' => 365,   // 1 year
                'air_fill' => 180, // 6 months
                'training' => null, // Permanent
                'trip' => 30,      // 30 days
                'repair' => null   // One-time
            ];

            $days = $validityDays[$waiverData['reference_type']] ?? 365;
            if ($days) {
                $data['valid_until'] = date('Y-m-d', strtotime("+{$days} days"));
            }

            // Save signed waiver
            $waiverId = $this->waiverService->saveSignedWaiver($data);

            echo json_encode([
                'success' => true,
                'message' => 'Waiver signed successfully! A confirmation has been sent to your email.',
                'waiver_id' => $waiverId
            ]);

        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Admin: View all waivers
     */
    public function index()
    {
        $pageTitle = 'Waivers';
        $activeMenu = 'waivers';

        // Get filters
        $search = $_GET['search'] ?? '';
        $status = $_GET['status'] ?? '';
        $type = $_GET['type'] ?? '';

        // Build query
        $sql = "SELECT sw.*, c.first_name, c.last_name, c.email, wt.title, wt.type
                FROM signed_waivers sw
                JOIN customers c ON sw.customer_id = c.id
                JOIN waiver_templates wt ON sw.waiver_template_id = wt.id
                WHERE 1=1";

        $params = [];

        if ($search) {
            $sql .= " AND (c.first_name LIKE ? OR c.last_name LIKE ? OR c.email LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }

        if ($status) {
            $sql .= " AND sw.status = ?";
            $params[] = $status;
        }

        if ($type) {
            $sql .= " AND wt.type = ?";
            $params[] = $type;
        }

        $sql .= " ORDER BY sw.signed_at DESC LIMIT 100";

        $waivers = $this->waiverService->db->query($sql, $params)->fetchAll();

        $content = $this->renderIndex($waivers);
        require BASE_PATH . '/app/Views/layouts/app.php';
    }

    /**
     * Admin: View single waiver
     */
    public function show(int $id)
    {
        $pageTitle = 'Waiver Details';
        $activeMenu = 'waivers';

        $sql = "SELECT sw.*, c.first_name, c.last_name, c.email, c.phone,
                wt.title, wt.type, wt.content, wt.legal_text
                FROM signed_waivers sw
                JOIN customers c ON sw.customer_id = c.id
                JOIN waiver_templates wt ON sw.waiver_template_id = wt.id
                WHERE sw.id = ?";

        $waiver = $this->waiverService->db->query($sql, [$id])->fetch();

        if (!$waiver) {
            $_SESSION['flash_error'] = 'Waiver not found';
            header('Location: /waivers');
            exit;
        }

        $content = $this->renderShow($waiver);
        require BASE_PATH . '/app/Views/layouts/app.php';
    }

    /**
     * Admin: Download waiver PDF
     */
    public function downloadPDF(int $id)
    {
        $sql = "SELECT pdf_path, customer_name FROM signed_waivers WHERE id = ?";
        $waiver = $this->waiverService->db->query($sql, [$id])->fetch();

        if (!$waiver || !$waiver['pdf_path'] || !file_exists($waiver['pdf_path'])) {
            $_SESSION['flash_error'] = 'PDF not found';
            header('Location: /waivers');
            exit;
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="waiver-' . $id . '.pdf"');
        readfile($waiver['pdf_path']);
        exit;
    }

    /**
     * Render index view
     */
    private function renderIndex(array $waivers): string
    {
        ob_start();
        ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-file-earmark-text"></i> Signed Waivers</h2>
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="/waivers" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="search" placeholder="Search customer name or email" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="rental" <?= ($_GET['type'] ?? '') === 'rental' ? 'selected' : '' ?>>Rental</option>
                            <option value="repair" <?= ($_GET['type'] ?? '') === 'repair' ? 'selected' : '' ?>>Repair</option>
                            <option value="air_fill" <?= ($_GET['type'] ?? '') === 'air_fill' ? 'selected' : '' ?>>Air Fill</option>
                            <option value="training" <?= ($_GET['type'] ?? '') === 'training' ? 'selected' : '' ?>>Training</option>
                            <option value="trip" <?= ($_GET['type'] ?? '') === 'trip' ? 'selected' : '' ?>>Trip</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="signed" <?= ($_GET['status'] ?? '') === 'signed' ? 'selected' : '' ?>>Signed</option>
                            <option value="expired" <?= ($_GET['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Expired</option>
                            <option value="voided" <?= ($_GET['status'] ?? '') === 'voided' ? 'selected' : '' ?>>Voided</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Search</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Waiver Type</th>
                                <th>Signed Date</th>
                                <th>Valid Until</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($waivers)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">No waivers found</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($waivers as $waiver): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($waiver['first_name'] . ' ' . $waiver['last_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($waiver['email']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= ucfirst(str_replace('_', ' ', $waiver['type'])) ?></span><br>
                                    <small><?= htmlspecialchars($waiver['title']) ?></small>
                                </td>
                                <td><?= date('M j, Y g:i A', strtotime($waiver['signed_at'])) ?></td>
                                <td>
                                    <?php if ($waiver['valid_until']): ?>
                                        <?= date('M j, Y', strtotime($waiver['valid_until'])) ?>
                                        <?php if (strtotime($waiver['valid_until']) < time()): ?>
                                            <span class="badge bg-warning">Expired</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No expiration</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'signed' => 'success',
                                        'expired' => 'warning',
                                        'voided' => 'danger'
                                    ];
                                    $color = $statusColors[$waiver['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= ucfirst($waiver['status']) ?></span>
                                </td>
                                <td>
                                    <a href="/waivers/<?= $waiver['id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <?php if ($waiver['pdf_path']): ?>
                                    <a href="/waivers/<?= $waiver['id'] ?>/pdf" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-download"></i> PDF
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render show view
     */
    private function renderShow(array $waiver): string
    {
        ob_start();
        ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="bi bi-file-earmark-text"></i> Waiver Details</h2>
            <div>
                <?php if ($waiver['pdf_path']): ?>
                <a href="/waivers/<?= $waiver['id'] ?>/pdf" class="btn btn-primary">
                    <i class="bi bi-download"></i> Download PDF
                </a>
                <?php endif; ?>
                <a href="/waivers" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5><?= htmlspecialchars($waiver['title']) ?></h5>
                    </div>
                    <div class="card-body">
                        <h6>Agreement Content:</h6>
                        <div class="border p-3 mb-3" style="white-space: pre-wrap;"><?= htmlspecialchars($waiver['content']) ?></div>

                        <h6>Legal Terms:</h6>
                        <div class="border p-3" style="white-space: pre-wrap;"><?= htmlspecialchars($waiver['legal_text']) ?></div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h5>Signature</h5>
                    </div>
                    <div class="card-body">
                        <img src="<?= htmlspecialchars($waiver['signature_data']) ?>" alt="Signature" class="img-fluid border" style="max-width: 400px;">
                        <p class="mt-2 text-muted small">
                            Signed from IP: <?= htmlspecialchars($waiver['signature_ip']) ?><br>
                            User Agent: <?= htmlspecialchars($waiver['signature_user_agent']) ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?= htmlspecialchars($waiver['customer_name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($waiver['customer_email']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($waiver['customer_phone']) ?></p>
                        <?php if ($waiver['customer_dob']): ?>
                        <p><strong>DOB:</strong> <?= date('M j, Y', strtotime($waiver['customer_dob'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($waiver['emergency_contact_name']): ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>Emergency Contact</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Name:</strong> <?= htmlspecialchars($waiver['emergency_contact_name']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($waiver['emergency_contact_phone']) ?></p>
                        <p><strong>Relationship:</strong> <?= htmlspecialchars($waiver['emergency_contact_relationship']) ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($waiver['has_medical_conditions']): ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h5>Medical Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($waiver['medical_conditions']): ?>
                        <p><strong>Conditions:</strong><br><?= nl2br(htmlspecialchars($waiver['medical_conditions'])) ?></p>
                        <?php endif; ?>
                        <?php if ($waiver['medications']): ?>
                        <p><strong>Medications:</strong><br><?= nl2br(htmlspecialchars($waiver['medications'])) ?></p>
                        <?php endif; ?>
                        <?php if ($waiver['allergies']): ?>
                        <p><strong>Allergies:</strong><br><?= nl2br(htmlspecialchars($waiver['allergies'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h5>Waiver Status</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Type:</strong> <span class="badge bg-info"><?= ucfirst(str_replace('_', ' ', $waiver['type'])) ?></span></p>
                        <p><strong>Status:</strong> <span class="badge bg-success"><?= ucfirst($waiver['status']) ?></span></p>
                        <p><strong>Signed:</strong> <?= date('M j, Y g:i A', strtotime($waiver['signed_at'])) ?></p>
                        <?php if ($waiver['valid_until']): ?>
                        <p><strong>Valid Until:</strong> <?= date('M j, Y', strtotime($waiver['valid_until'])) ?></p>
                        <?php endif; ?>
                        <?php if ($waiver['email_sent']): ?>
                        <p><strong>Confirmation Sent:</strong> <?= date('M j, Y g:i A', strtotime($waiver['email_sent_at'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
