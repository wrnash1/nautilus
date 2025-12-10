<?php
/**
 * User Settings Page
 */
use App\Core\Translator;
$translator = Translator::getInstance();
?>

<div class="container-fluid py-4">
    <h1 class="mb-4">
        <i class="bi bi-gear"></i> <?= __('messages.settings') ?>
    </h1>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="row">
        <!-- Profile Settings -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-person"></i> <?= __('messages.profile') ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/settings/update">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <div class="mb-3">
                            <label for="first_name" class="form-label"><?= __('common.name') ?></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="first_name" name="first_name"
                                           value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
                                    <small class="text-muted">First Name</small>
                                </div>
                                <div class="col-md-6">
                                    <input type="text" class="form-control" id="last_name" name="last_name"
                                           value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
                                    <small class="text-muted">Last Name</small>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label"><?= __('auth.email') ?></label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone" class="form-label"><?= __('common.phone') ?></label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <label for="locale" class="form-label">
                                <i class="bi bi-globe"></i> Language / Idioma / Langue
                            </label>
                            <select class="form-select" id="locale" name="locale">
                                <?php foreach ($translator->getAvailableLocales() as $code => $name): ?>
                                    <option value="<?= $code ?>"
                                            <?= ($user['locale'] ?? 'en') === $code ? 'selected' : '' ?>>
                                        <?= $name ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">
                                Choose your preferred language for the application
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> <?= __('messages.save') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="col-lg-6 mb-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-shield-lock"></i> Security
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Change Password -->
                    <form method="POST" action="/settings/change-password">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password"
                                   name="current_password" required>
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password"
                                   name="new_password" required minlength="8">
                            <small class="text-muted">Minimum 8 characters</small>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password"
                                   name="confirm_password" required minlength="8">
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key"></i> Change Password
                        </button>
                    </form>

                    <hr class="my-4">

                    <!-- Two-Factor Authentication -->
                    <div>
                        <h6><i class="bi bi-phone"></i> Two-Factor Authentication</h6>
                        <p class="text-muted small">
                            Add an extra layer of security to your account
                        </p>

                        <?php if ($two_factor_enabled ?? false): ?>
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle"></i>
                                Two-factor authentication is <strong>enabled</strong>
                            </div>
                            <a href="/auth/2fa/disable" class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-x-circle"></i> Disable 2FA
                            </a>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                Two-factor authentication is <strong>disabled</strong>
                            </div>
                            <a href="/auth/2fa/setup" class="btn btn-success btn-sm">
                                <i class="bi bi-shield-check"></i> Enable 2FA
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Preferences -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-bell"></i> Notification Preferences
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/settings/update">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="email_notifications"
                                   name="email_notifications"
                                   <?= ($notification_prefs['email_notifications'] ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="email_notifications">
                                <i class="bi bi-envelope"></i> Email Notifications
                                <small class="d-block text-muted">
                                    Receive notifications via email
                                </small>
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="sms_notifications"
                                   name="sms_notifications"
                                   <?= ($notification_prefs['sms_notifications'] ?? false) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="sms_notifications">
                                <i class="bi bi-phone"></i> SMS Notifications
                                <small class="d-block text-muted">
                                    Receive notifications via SMS (text message)
                                </small>
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="in_app_notifications"
                                   name="in_app_notifications"
                                   <?= ($notification_prefs['in_app_notifications'] ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="in_app_notifications">
                                <i class="bi bi-app-indicator"></i> In-App Notifications
                                <small class="d-block text-muted">
                                    Show notifications within the application
                                </small>
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Save Preferences
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
