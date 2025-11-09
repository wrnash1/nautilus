<!-- Step 4: Admin Account Setup -->
<div class="step-content">
    <h2>Create Administrator Account</h2>
    <p>Create the main administrator account for managing the application.</p>

    <?php
    $errors = [];

    // Get saved values from session
    $adminConfig = $_SESSION['admin_config'] ?? [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'username' => 'admin',
        'password' => '',
        'password_confirm' => ''
    ];

    // Validate form if submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['next']) && !isset($_POST['back'])) {
        $adminConfig = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? ''
        ];

        // Validation
        if (empty($adminConfig['first_name'])) {
            $errors[] = 'First name is required';
        }
        if (empty($adminConfig['last_name'])) {
            $errors[] = 'Last name is required';
        }
        if (empty($adminConfig['email']) || !filter_var($adminConfig['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email address is required';
        }
        if (empty($adminConfig['username']) || strlen($adminConfig['username']) < 3) {
            $errors[] = 'Username must be at least 3 characters';
        }
        if (empty($adminConfig['password']) || strlen($adminConfig['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        if ($adminConfig['password'] !== $adminConfig['password_confirm']) {
            $errors[] = 'Passwords do not match';
        }

        // Check password strength
        if (!empty($adminConfig['password'])) {
            $hasUpper = preg_match('/[A-Z]/', $adminConfig['password']);
            $hasLower = preg_match('/[a-z]/', $adminConfig['password']);
            $hasNumber = preg_match('/[0-9]/', $adminConfig['password']);

            if (!$hasUpper || !$hasLower || !$hasNumber) {
                $errors[] = 'Password must contain uppercase, lowercase, and numbers';
            }
        }

        if (empty($errors)) {
            $_SESSION['admin_config'] = $adminConfig;
        }
    }
    ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <strong>Please correct the following errors:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" id="adminForm">
        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First Name <span class="required">*</span></label>
                <input type="text" id="first_name" name="first_name" class="form-control"
                       value="<?= htmlspecialchars($adminConfig['first_name']) ?>" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name <span class="required">*</span></label>
                <input type="text" id="last_name" name="last_name" class="form-control"
                       value="<?= htmlspecialchars($adminConfig['last_name']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email Address <span class="required">*</span></label>
            <input type="email" id="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($adminConfig['email']) ?>" required autocomplete="off">
            <small class="form-text">This will be used for login and notifications</small>
        </div>

        <div class="form-group">
            <label for="username">Username <span class="required">*</span></label>
            <input type="text" id="username" name="username" class="form-control"
                   value="<?= htmlspecialchars($adminConfig['username']) ?>" required autocomplete="off">
            <small class="form-text">Minimum 3 characters, no spaces</small>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" class="form-control"
                       required autocomplete="new-password">
                <small class="form-text">Minimum 8 characters</small>
            </div>

            <div class="form-group">
                <label for="password_confirm">Confirm Password <span class="required">*</span></label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                       required autocomplete="new-password">
            </div>
        </div>

        <div class="password-requirements">
            <h4>Password Requirements:</h4>
            <ul>
                <li id="req-length">At least 8 characters</li>
                <li id="req-upper">Contains uppercase letter (A-Z)</li>
                <li id="req-lower">Contains lowercase letter (a-z)</li>
                <li id="req-number">Contains number (0-9)</li>
                <li id="req-match">Passwords match</li>
            </ul>
        </div>

        <div class="step-actions">
            <button type="submit" name="back" class="btn btn-secondary">Back</button>
            <button type="submit" name="next" class="btn btn-primary">
                Continue to Installation
            </button>
        </div>
    </form>
</div>

<style>
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.required {
    color: #dc3545;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: #0066cc;
    box-shadow: 0 0 0 3px rgba(0, 102, 204, 0.1);
}

.form-text {
    display: block;
    margin-top: 5px;
    font-size: 0.875em;
    color: #6c757d;
}

.password-requirements {
    background-color: #f8f9fa;
    padding: 15px 20px;
    border-radius: 5px;
    margin: 20px 0;
}

.password-requirements h4 {
    margin: 0 0 10px 0;
    font-size: 0.95em;
    color: #333;
}

.password-requirements ul {
    margin: 0;
    padding-left: 20px;
}

.password-requirements li {
    margin: 5px 0;
    color: #6c757d;
}

.password-requirements li.valid {
    color: #28a745;
}

.password-requirements li.invalid {
    color: #dc3545;
}

.alert {
    padding: 15px 20px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.alert-error {
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alert-error ul {
    margin: 10px 0 0 20px;
    padding: 0;
}

.alert-error li {
    margin: 5px 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const passwordConfirm = document.getElementById('password_confirm');

    function validatePassword() {
        const value = password.value;

        // Length check
        const lengthValid = value.length >= 8;
        document.getElementById('req-length').className = lengthValid ? 'valid' : 'invalid';

        // Uppercase check
        const upperValid = /[A-Z]/.test(value);
        document.getElementById('req-upper').className = upperValid ? 'valid' : 'invalid';

        // Lowercase check
        const lowerValid = /[a-z]/.test(value);
        document.getElementById('req-lower').className = lowerValid ? 'valid' : 'invalid';

        // Number check
        const numberValid = /[0-9]/.test(value);
        document.getElementById('req-number').className = numberValid ? 'valid' : 'invalid';

        // Match check
        checkPasswordMatch();
    }

    function checkPasswordMatch() {
        const match = password.value === passwordConfirm.value && password.value !== '';
        document.getElementById('req-match').className = match ? 'valid' : 'invalid';
    }

    password.addEventListener('input', validatePassword);
    passwordConfirm.addEventListener('input', checkPasswordMatch);
});
</script>
