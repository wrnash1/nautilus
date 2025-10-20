<?php

namespace App\Controllers\API;

use App\Core\Database;

class TokenController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Display API token management page
     */
    public function index()
    {
        $pageTitle = 'API Token Management';
        $activeMenu = 'api';

        // Get all active tokens for current user
        $tokens = $this->getAllTokens();

        $content = $this->renderView($tokens);

        require BASE_PATH . '/app/Views/layouts/app.php';
    }

    /**
     * Show create token form
     */
    public function create()
    {
        $pageTitle = 'Create API Token';
        $activeMenu = 'api';

        $content = $this->renderCreateForm();

        require BASE_PATH . '/app/Views/layouts/app.php';
    }

    /**
     * Store new API token
     */
    public function store()
    {
        try {
            $name = $_POST['name'] ?? '';
            $expiresIn = (int)($_POST['expires_in'] ?? 365);
            $scopes = $_POST['scopes'] ?? [];

            if (empty($name)) {
                $_SESSION['flash_error'] = 'Token name is required';
                header('Location: /api/tokens/create');
                exit;
            }

            // Generate a secure random token
            $token = bin2hex(random_bytes(32));
            $hashedToken = hash('sha256', $token);

            // Calculate expiration date
            $expiresAt = $expiresIn > 0 ? date('Y-m-d H:i:s', strtotime("+{$expiresIn} days")) : null;

            // Store token in database
            $sql = "INSERT INTO api_tokens (user_id, name, token, scopes, expires_at, created_at)
                    VALUES (?, ?, ?, ?, ?, NOW())";

            $this->db->query($sql, [
                $_SESSION['user_id'],
                $name,
                $hashedToken,
                json_encode($scopes),
                $expiresAt
            ]);

            // Store the plain token in session for one-time display
            $_SESSION['new_api_token'] = $token;
            $_SESSION['flash_success'] = 'API token created successfully! Make sure to copy it now - you won\'t be able to see it again.';

            header('Location: /api/tokens');
            exit;

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error creating token: ' . $e->getMessage();
            header('Location: /api/tokens/create');
            exit;
        }
    }

    /**
     * Revoke (disable) a token
     */
    public function revoke(int $id)
    {
        try {
            $sql = "UPDATE api_tokens SET revoked = 1, revoked_at = NOW()
                    WHERE id = ? AND user_id = ?";

            $this->db->query($sql, [$id, $_SESSION['user_id']]);

            $_SESSION['flash_success'] = 'Token revoked successfully';

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error revoking token: ' . $e->getMessage();
        }

        header('Location: /api/tokens');
        exit;
    }

    /**
     * Delete a token permanently
     */
    public function delete(int $id)
    {
        try {
            $sql = "DELETE FROM api_tokens WHERE id = ? AND user_id = ?";
            $this->db->query($sql, [$id, $_SESSION['user_id']]);

            $_SESSION['flash_success'] = 'Token deleted successfully';

        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Error deleting token: ' . $e->getMessage();
        }

        header('Location: /api/tokens');
        exit;
    }

    /**
     * Get all tokens for current user
     */
    private function getAllTokens(): array
    {
        $sql = "SELECT id, name, scopes, last_used_at, expires_at, revoked, created_at
                FROM api_tokens
                WHERE user_id = ?
                ORDER BY created_at DESC";

        return $this->db->query($sql, [$_SESSION['user_id']])->fetchAll();
    }

    /**
     * Render the main view
     */
    private function renderView(array $tokens): string
    {
        ob_start();

        // Get the newly created token from session if available
        $newToken = $_SESSION['new_api_token'] ?? null;
        unset($_SESSION['new_api_token']);
        ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-key"></i> API Token Management</h1>
                <a href="/api/tokens/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create New Token
                </a>
            </div>

            <?php if ($newToken): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <h5 class="alert-heading"><i class="bi bi-check-circle"></i> Token Created Successfully!</h5>
                <p>Make sure to copy your new API token now. You won't be able to see it again!</p>
                <div class="input-group mb-3">
                    <input type="text" class="form-control font-monospace" id="newToken" value="<?= htmlspecialchars($newToken) ?>" readonly>
                    <button class="btn btn-outline-secondary" type="button" onclick="copyToken()">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <p class="mb-0">
                                <i class="bi bi-info-circle"></i>
                                API tokens allow you to authenticate API requests. Keep your tokens secure and never share them.
                                <a href="/api/docs" class="ms-2">View API Documentation</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Your API Tokens</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($tokens)): ?>
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-key" style="font-size: 3rem;"></i>
                        <p class="mt-3">No API tokens yet. Create one to get started!</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Scopes</th>
                                    <th>Last Used</th>
                                    <th>Expires</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tokens as $token): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($token['name']) ?></strong>
                                        <br>
                                        <small class="text-muted">Created <?= date('M d, Y', strtotime($token['created_at'])) ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $scopes = json_decode($token['scopes'], true) ?: [];
                                        if (empty($scopes)) {
                                            echo '<span class="badge bg-secondary">Full Access</span>';
                                        } else {
                                            foreach ($scopes as $scope) {
                                                echo '<span class="badge bg-info me-1">' . htmlspecialchars($scope) . '</span>';
                                            }
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?= $token['last_used_at'] ? date('M d, Y H:i', strtotime($token['last_used_at'])) : '<span class="text-muted">Never</span>' ?>
                                    </td>
                                    <td>
                                        <?php if ($token['expires_at']): ?>
                                            <?php
                                            $expiresAt = strtotime($token['expires_at']);
                                            $isExpired = $expiresAt < time();
                                            ?>
                                            <span class="<?= $isExpired ? 'text-danger' : '' ?>">
                                                <?= date('M d, Y', $expiresAt) ?>
                                                <?= $isExpired ? '(Expired)' : '' ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($token['revoked']): ?>
                                            <span class="badge bg-danger">Revoked</span>
                                        <?php elseif ($token['expires_at'] && strtotime($token['expires_at']) < time()): ?>
                                            <span class="badge bg-warning">Expired</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$token['revoked']): ?>
                                        <form method="POST" action="/api/tokens/<?= $token['id'] ?>/revoke" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Revoke this token?')">
                                                <i class="bi bi-x-circle"></i> Revoke
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                        <form method="POST" action="/api/tokens/<?= $token['id'] ?>/delete" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this token permanently?')">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
        function copyToken() {
            const tokenInput = document.getElementById('newToken');
            tokenInput.select();
            document.execCommand('copy');

            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check"></i> Copied!';

            setTimeout(() => {
                btn.innerHTML = originalHTML;
            }, 2000);
        }
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * Render the create form
     */
    private function renderCreateForm(): string
    {
        ob_start();
        ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="bi bi-key"></i> Create New API Token</h1>
                <a href="/api/tokens" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Tokens
                </a>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Token Details</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="/api/tokens">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                                <div class="mb-3">
                                    <label class="form-label">Token Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required placeholder="e.g., Mobile App Token">
                                    <small class="form-text text-muted">A descriptive name to identify this token</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Expiration</label>
                                    <select name="expires_in" class="form-control">
                                        <option value="30">30 days</option>
                                        <option value="90">90 days</option>
                                        <option value="365" selected>1 year</option>
                                        <option value="730">2 years</option>
                                        <option value="0">Never</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Scopes (optional)</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="scopes[]" value="read" id="scopeRead">
                                        <label class="form-check-label" for="scopeRead">Read Access</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="scopes[]" value="write" id="scopeWrite">
                                        <label class="form-check-label" for="scopeWrite">Write Access</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="scopes[]" value="delete" id="scopeDelete">
                                        <label class="form-check-label" for="scopeDelete">Delete Access</label>
                                    </div>
                                    <small class="form-text text-muted">Leave unchecked for full access</small>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Create Token
                                </button>
                                <a href="/api/tokens" class="btn btn-secondary">Cancel</a>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Security Notice</h5>
                        </div>
                        <div class="card-body">
                            <ul class="small">
                                <li>Tokens grant access to your account</li>
                                <li>Keep tokens secure and private</li>
                                <li>You'll only see the token once after creation</li>
                                <li>Revoke tokens immediately if compromised</li>
                                <li>Use specific scopes when possible</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
