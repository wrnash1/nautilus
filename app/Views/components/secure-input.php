<?php
/**
 * Secure Input Component
 * Renders a password input field with proper masking for sensitive values
 *
 * Usage:
 * <?php require __DIR__ . '/../../components/secure-input.php'; ?>
 * <?= renderSecureInput('stripe_secret_key', 'Stripe Secret Key', $settings['stripe_secret_key'] ?? '', 'sk_live_...') ?>
 *
 * @param string $name Field name (used for name attribute)
 * @param string $label Display label
 * @param string $currentValue Current value (will be masked if not empty)
 * @param string $placeholder Placeholder text
 * @param string $helpText Optional help text below the input
 * @param bool $required Whether the field is required
 * @return string Rendered HTML
 */
function renderSecureInput(
    string $name,
    string $label,
    string $currentValue = '',
    string $placeholder = '',
    string $helpText = '',
    bool $required = false
): string {
    // Mask current value if it exists (show only last 4 characters)
    $displayValue = '';
    $hasExistingValue = !empty($currentValue);

    if ($hasExistingValue) {
        $maskedLength = max(8, strlen($currentValue) - 4);
        $displayValue = str_repeat('•', $maskedLength) . substr($currentValue, -4);
    }

    $requiredAttr = $required ? 'required' : '';
    $requiredLabel = $required ? '<span class="text-danger">*</span>' : '';

    ob_start();
    ?>
    <div class="mb-3">
        <label for="<?= htmlspecialchars($name) ?>" class="form-label">
            <?= htmlspecialchars($label) ?> <?= $requiredLabel ?>
        </label>

        <div class="input-group">
            <input type="password"
                   name="settings[<?= htmlspecialchars($name) ?>]"
                   id="<?= htmlspecialchars($name) ?>"
                   class="form-control secure-input"
                   placeholder="<?= htmlspecialchars($placeholder) ?>"
                   autocomplete="off"
                   <?= $requiredAttr ?>
                   data-has-value="<?= $hasExistingValue ? 'true' : 'false' ?>">

            <button class="btn btn-outline-secondary toggle-password" type="button"
                    data-target="<?= htmlspecialchars($name) ?>"
                    title="Show/Hide value">
                <i class="bi bi-eye"></i>
            </button>
        </div>

        <?php if ($hasExistingValue): ?>
            <small class="text-muted d-block mt-1">
                <i class="bi bi-shield-check text-success"></i>
                Current value: <code><?= htmlspecialchars($displayValue) ?></code>
                <br>
                <span class="text-info">Leave blank to keep current value, or enter new value to update</span>
            </small>
        <?php endif; ?>

        <?php if ($helpText): ?>
            <small class="text-muted d-block mt-1">
                <?= htmlspecialchars($helpText) ?>
            </small>
        <?php endif; ?>
    </div>

    <script>
    // Toggle password visibility
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.querySelector('[data-target="<?= htmlspecialchars($name) ?>"]');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const input = document.getElementById('<?= htmlspecialchars($name) ?>');
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        }
    });
    </script>
    <?php
    return ob_get_clean();
}
?>
