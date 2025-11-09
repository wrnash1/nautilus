<!-- Step 6: Installation Complete -->
<div class="step-content">
    <div class="success-header">
        <div class="success-icon">✓</div>
        <h2>Installation Complete!</h2>
        <p>Nautilus has been successfully installed and is ready to use.</p>
    </div>

    <div class="installation-summary">
        <h3>Installation Summary</h3>

        <div class="summary-section">
            <h4>Application Details</h4>
            <table class="summary-table">
                <tr>
                    <td><strong>Application Name:</strong></td>
                    <td><?= htmlspecialchars($_SESSION['app_config']['app_name'] ?? 'Nautilus') ?></td>
                </tr>
                <tr>
                    <td><strong>Application URL:</strong></td>
                    <td><?= htmlspecialchars($_SESSION['app_config']['app_url'] ?? '') ?></td>
                </tr>
                <tr>
                    <td><strong>Company:</strong></td>
                    <td><?= htmlspecialchars($_SESSION['app_config']['company_name'] ?? '') ?></td>
                </tr>
                <tr>
                    <td><strong>Timezone:</strong></td>
                    <td><?= htmlspecialchars($_SESSION['app_config']['timezone'] ?? 'UTC') ?></td>
                </tr>
            </table>
        </div>

        <div class="summary-section">
            <h4>Administrator Account</h4>
            <table class="summary-table">
                <tr>
                    <td><strong>Name:</strong></td>
                    <td>
                        <?= htmlspecialchars($_SESSION['admin_config']['first_name'] ?? '') ?>
                        <?= htmlspecialchars($_SESSION['admin_config']['last_name'] ?? '') ?>
                    </td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td><?= htmlspecialchars($_SESSION['admin_config']['email'] ?? '') ?></td>
                </tr>
                <tr>
                    <td><strong>Username:</strong></td>
                    <td><?= htmlspecialchars($_SESSION['admin_config']['username'] ?? '') ?></td>
                </tr>
            </table>
        </div>

        <div class="summary-section">
            <h4>Database</h4>
            <table class="summary-table">
                <tr>
                    <td><strong>Host:</strong></td>
                    <td><?= htmlspecialchars($_SESSION['db_config']['host'] ?? '') ?></td>
                </tr>
                <tr>
                    <td><strong>Database:</strong></td>
                    <td><?= htmlspecialchars($_SESSION['db_config']['database'] ?? '') ?></td>
                </tr>
                <tr>
                    <td><strong>Tables Created:</strong></td>
                    <td>60+ tables</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="next-steps">
        <h3>Next Steps</h3>
        <ol>
            <li>
                <strong>Delete the installation directory</strong>
                <p>For security, remove the <code>/public/install</code> directory from your server.</p>
                <pre>rm -rf <?= dirname(__DIR__) ?></pre>
            </li>
            <li>
                <strong>Set up automated tasks (Cron Jobs)</strong>
                <p>Configure the following cron jobs for automated system maintenance:</p>
                <div class="cron-jobs">
                    <div class="cron-job">
                        <strong>Hourly:</strong> Automated Notifications
                        <pre>0 * * * * php <?= dirname(dirname(__DIR__)) ?>/app/Jobs/SendAutomatedNotificationsJob.php</pre>
                    </div>
                    <div class="cron-job">
                        <strong>Daily 1 AM:</strong> Calculate Analytics
                        <pre>0 1 * * * php <?= dirname(dirname(__DIR__)) ?>/app/Jobs/CalculateDailyAnalyticsJob.php</pre>
                    </div>
                    <div class="cron-job">
                        <strong>Daily 2 AM:</strong> Database Backup
                        <pre>0 2 * * * php <?= dirname(dirname(__DIR__)) ?>/app/Jobs/DatabaseBackupJob.php</pre>
                    </div>
                    <div class="cron-job">
                        <strong>Every 6 hours:</strong> Cache Warmup
                        <pre>0 */6 * * * php <?= dirname(dirname(__DIR__)) ?>/app/Jobs/CacheWarmupJob.php</pre>
                    </div>
                    <div class="cron-job">
                        <strong>Sunday 3 AM:</strong> Cleanup Old Data
                        <pre>0 3 * * 0 php <?= dirname(dirname(__DIR__)) ?>/app/Jobs/CleanupOldDataJob.php</pre>
                    </div>
                    <div class="cron-job">
                        <strong>Monday 9 AM:</strong> Send Scheduled Reports
                        <pre>0 9 * * 1 php <?= dirname(dirname(__DIR__)) ?>/app/Jobs/SendScheduledReportsJob.php</pre>
                    </div>
                </div>
            </li>
            <li>
                <strong>Configure Email Settings</strong>
                <p>Update your SMTP settings in the <code>.env</code> file for email notifications to work properly.</p>
            </li>
            <li>
                <strong>Review Security Settings</strong>
                <p>Ensure proper file permissions and configure SSL/HTTPS for production use.</p>
            </li>
            <li>
                <strong>Customize Your Application</strong>
                <p>Add your company logo, customize colors, and configure system settings.</p>
            </li>
        </ol>
    </div>

    <div class="quick-links">
        <h3>Quick Links</h3>
        <div class="links-grid">
            <a href="../index.php" class="quick-link">
                <span class="link-icon">🏠</span>
                <span class="link-text">Go to Dashboard</span>
            </a>
            <a href="../login.php" class="quick-link">
                <span class="link-icon">🔐</span>
                <span class="link-text">Login</span>
            </a>
            <a href="../../docs/INSTALLATION_GUIDE.md" class="quick-link">
                <span class="link-icon">📚</span>
                <span class="link-text">Documentation</span>
            </a>
            <a href="../../docs/ANALYTICS_DASHBOARD.md" class="quick-link">
                <span class="link-icon">📊</span>
                <span class="link-text">Analytics Guide</span>
            </a>
        </div>
    </div>

    <div class="support-info">
        <h3>Need Help?</h3>
        <p>If you encounter any issues or need assistance:</p>
        <ul>
            <li>Check the documentation in the <code>/docs</code> directory</li>
            <li>Review the troubleshooting guide in <code>INSTALLATION_GUIDE.md</code></li>
            <li>Check application logs in <code>/storage/logs</code></li>
        </ul>
    </div>
</div>

<div class="step-actions">
    <a href="../index.php" class="btn btn-primary btn-large">
        Go to Application Dashboard
    </a>
</div>

<style>
.success-header {
    text-align: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    margin-bottom: 40px;
}

.success-icon {
    font-size: 80px;
    margin-bottom: 20px;
    animation: scaleIn 0.5s ease-out;
}

@keyframes scaleIn {
    from {
        transform: scale(0);
    }
    to {
        transform: scale(1);
    }
}

.success-header h2 {
    margin: 0 0 10px 0;
    font-size: 2em;
}

.success-header p {
    margin: 0;
    font-size: 1.1em;
    opacity: 0.95;
}

.installation-summary {
    background-color: #f8f9fa;
    padding: 30px;
    border-radius: 8px;
    margin-bottom: 30px;
}

.installation-summary h3 {
    margin-top: 0;
    color: #333;
    border-bottom: 2px solid #0066cc;
    padding-bottom: 10px;
}

.summary-section {
    margin-bottom: 30px;
}

.summary-section:last-child {
    margin-bottom: 0;
}

.summary-section h4 {
    color: #0066cc;
    margin-bottom: 15px;
    font-size: 1.1em;
}

.summary-table {
    width: 100%;
    border-collapse: collapse;
}

.summary-table tr {
    border-bottom: 1px solid #e0e0e0;
}

.summary-table tr:last-child {
    border-bottom: none;
}

.summary-table td {
    padding: 10px 5px;
}

.summary-table td:first-child {
    width: 180px;
    color: #666;
}

.next-steps {
    margin-bottom: 30px;
}

.next-steps h3 {
    color: #333;
    border-bottom: 2px solid #0066cc;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.next-steps ol {
    counter-reset: steps;
    list-style: none;
    padding: 0;
}

.next-steps li {
    counter-increment: steps;
    margin-bottom: 25px;
    padding-left: 45px;
    position: relative;
}

.next-steps li::before {
    content: counter(steps);
    position: absolute;
    left: 0;
    top: 0;
    background-color: #0066cc;
    color: white;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
}

.next-steps li strong {
    display: block;
    margin-bottom: 5px;
    color: #333;
}

.next-steps li p {
    margin: 5px 0;
    color: #666;
}

.next-steps pre {
    background-color: #f4f4f4;
    border: 1px solid #ddd;
    border-left: 3px solid #0066cc;
    padding: 12px;
    margin: 10px 0;
    overflow-x: auto;
    border-radius: 4px;
    font-size: 0.9em;
}

.next-steps code {
    background-color: #f4f4f4;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.9em;
}

.cron-jobs {
    margin-top: 15px;
}

.cron-job {
    margin-bottom: 15px;
}

.cron-job strong {
    display: block;
    color: #555;
    margin-bottom: 5px;
}

.quick-links h3 {
    color: #333;
    border-bottom: 2px solid #0066cc;
    padding-bottom: 10px;
    margin-bottom: 20px;
}

.links-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}

.quick-link {
    display: flex;
    align-items: center;
    padding: 20px;
    background-color: #f8f9fa;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
}

.quick-link:hover {
    border-color: #0066cc;
    background-color: #e7f3ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.link-icon {
    font-size: 2em;
    margin-right: 15px;
}

.link-text {
    font-weight: 600;
}

.support-info {
    background-color: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
}

.support-info h3 {
    margin-top: 0;
    color: #856404;
}

.support-info ul {
    margin-bottom: 0;
}

.support-info li {
    margin: 8px 0;
}

.support-info code {
    background-color: #fff;
    padding: 2px 6px;
    border-radius: 3px;
    border: 1px solid #ffc107;
}

.btn-large {
    padding: 15px 40px;
    font-size: 1.1em;
}

.step-actions {
    text-align: center;
    margin-top: 30px;
}
</style>
