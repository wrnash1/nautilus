<!-- Step 3: Application Configuration -->
<div class="step-content">
    <h2>Application Configuration</h2>
    <p>Configure your application settings and company information.</p>

    <?php
    // Get saved values from session
    $appConfig = $_SESSION['app_config'] ?? [
        'app_name' => 'Nautilus Dive Shop',
        'app_url' => 'http://' . $_SERVER['HTTP_HOST'],
        'company_name' => '',
        'company_email' => '',
        'company_phone' => '',
        'timezone' => 'America/New_York',
        'currency' => 'USD',
        'locale' => 'en_US'
    ];

    // Save posted values
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['back'])) {
        $_SESSION['app_config'] = [
            'app_name' => $_POST['app_name'] ?? $appConfig['app_name'],
            'app_url' => $_POST['app_url'] ?? $appConfig['app_url'],
            'company_name' => $_POST['company_name'] ?? $appConfig['company_name'],
            'company_email' => $_POST['company_email'] ?? $appConfig['company_email'],
            'company_phone' => $_POST['company_phone'] ?? $appConfig['company_phone'],
            'timezone' => $_POST['timezone'] ?? $appConfig['timezone'],
            'currency' => $_POST['currency'] ?? $appConfig['currency'],
            'locale' => $_POST['locale'] ?? $appConfig['locale']
        ];
        $appConfig = $_SESSION['app_config'];
    }

    $timezones = [
        'America/New_York' => 'Eastern Time (US & Canada)',
        'America/Chicago' => 'Central Time (US & Canada)',
        'America/Denver' => 'Mountain Time (US & Canada)',
        'America/Los_Angeles' => 'Pacific Time (US & Canada)',
        'America/Anchorage' => 'Alaska',
        'Pacific/Honolulu' => 'Hawaii',
        'UTC' => 'UTC',
        'Europe/London' => 'London',
        'Europe/Paris' => 'Paris',
        'Asia/Tokyo' => 'Tokyo',
        'Australia/Sydney' => 'Sydney'
    ];

    $currencies = [
        'USD' => 'US Dollar ($)',
        'EUR' => 'Euro (€)',
        'GBP' => 'British Pound (£)',
        'CAD' => 'Canadian Dollar ($)',
        'AUD' => 'Australian Dollar ($)',
        'JPY' => 'Japanese Yen (¥)',
        'CNY' => 'Chinese Yuan (¥)',
        'INR' => 'Indian Rupee (₹)'
    ];

    $locales = [
        'en_US' => 'English (United States)',
        'en_GB' => 'English (United Kingdom)',
        'es_ES' => 'Spanish (Spain)',
        'fr_FR' => 'French (France)',
        'de_DE' => 'German (Germany)',
        'it_IT' => 'Italian (Italy)',
        'pt_BR' => 'Portuguese (Brazil)',
        'ja_JP' => 'Japanese (Japan)',
        'zh_CN' => 'Chinese (Simplified)'
    ];
    ?>

    <form method="POST">
        <div class="config-section">
            <h3>Application Settings</h3>

            <div class="form-group">
                <label for="app_name">Application Name</label>
                <input type="text" id="app_name" name="app_name" class="form-control"
                       value="<?= htmlspecialchars($appConfig['app_name']) ?>" required>
                <small class="form-text">The name that will appear throughout the application</small>
            </div>

            <div class="form-group">
                <label for="app_url">Application URL</label>
                <input type="url" id="app_url" name="app_url" class="form-control"
                       value="<?= htmlspecialchars($appConfig['app_url']) ?>" required>
                <small class="form-text">The full URL where the application will be accessible (e.g., https://example.com)</small>
            </div>
        </div>

        <div class="config-section">
            <h3>Company Information</h3>

            <div class="form-group">
                <label for="company_name">Company Name</label>
                <input type="text" id="company_name" name="company_name" class="form-control"
                       value="<?= htmlspecialchars($appConfig['company_name']) ?>" required>
                <small class="form-text">Your business or organization name</small>
            </div>

            <div class="form-group">
                <label for="company_email">Company Email</label>
                <input type="email" id="company_email" name="company_email" class="form-control"
                       value="<?= htmlspecialchars($appConfig['company_email']) ?>" required>
                <small class="form-text">Main contact email for your business</small>
            </div>

            <div class="form-group">
                <label for="company_phone">Company Phone</label>
                <input type="tel" id="company_phone" name="company_phone" class="form-control"
                       value="<?= htmlspecialchars($appConfig['company_phone']) ?>">
                <small class="form-text">Main contact phone number (optional)</small>
            </div>
        </div>

        <div class="config-section">
            <h3>Regional Settings</h3>

            <div class="form-row">
                <div class="form-group">
                    <label for="timezone">Timezone</label>
                    <select id="timezone" name="timezone" class="form-control" required>
                        <?php foreach ($timezones as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>"
                                    <?= $appConfig['timezone'] === $value ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="currency">Currency</label>
                    <select id="currency" name="currency" class="form-control" required>
                        <?php foreach ($currencies as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>"
                                    <?= $appConfig['currency'] === $value ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="locale">Language/Locale</label>
                    <select id="locale" name="locale" class="form-control" required>
                        <?php foreach ($locales as $value => $label): ?>
                            <option value="<?= htmlspecialchars($value) ?>"
                                    <?= $appConfig['locale'] === $value ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="step-actions">
            <button type="submit" name="back" class="btn btn-secondary">Back</button>
            <button type="submit" name="next" class="btn btn-primary">
                Continue to Admin Account
            </button>
        </div>
    </form>
</div>

<style>
.config-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 1px solid #e0e0e0;
}

.config-section:last-of-type {
    border-bottom: none;
}

.config-section h3 {
    margin-bottom: 20px;
    color: #0066cc;
    font-size: 1.2em;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
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

select.form-control {
    cursor: pointer;
}
</style>
