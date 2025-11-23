<?php
/**
 * System Requirements Checker
 * Performs comprehensive checks for installation readiness
 */

header('Content-Type: application/json');

$checks = [];

// 1. PHP Version Check (>= 8.1)
$phpVersion = PHP_VERSION;
$checks['php_version'] = [
    'name' => 'PHP Version (>= 8.1)',
    'status' => version_compare($phpVersion, '8.1.0', '>='),
    'message' => "PHP {$phpVersion}",
    'critical' => true
];

// 2. Web Server Check
$webServer = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';
$checks['web_server'] = [
    'name' => 'Web Server',
    'status' => !empty($webServer),
    'message' => $webServer,
    'critical' => true
];

// 2.1 Dependencies Check
$autoloadFile = dirname(__DIR__, 2) . '/vendor/autoload.php';
$hasDependencies = file_exists($autoloadFile);
$checks['dependencies'] = [
    'name' => 'Dependencies Installed',
    'status' => $hasDependencies,
    'message' => $hasDependencies ? 'Installed ✓' : 'Missing vendor directory',
    'fix_command' => $hasDependencies ? '' : 'composer install',
    'help_text' => 'The application dependencies are missing. You need to run "composer install" in the root directory.',
    'critical' => true
];

// 3. MySQL/MariaDB Server Check
$mysqlRunning = false;
$mysqlMessage = 'Not accessible';
$mysqlFixCmd = '';
$mysqlHelpText = '';

// Try to connect via TCP socket first
$socketCheck = @fsockopen('127.0.0.1', 3306, $errno, $errstr, 1);
if ($socketCheck) {
    $mysqlRunning = true;
    $mysqlMessage = 'Accessible ✓';
    $mysqlHelpText = 'Database server is listening and ready';
    fclose($socketCheck);
} else {
    // Connection failed - could be SELinux blocking or service not running
    // Check if unix socket exists (means MariaDB is running)
    $unixSocket = '/var/lib/mysql/mysql.sock';
    if (file_exists($unixSocket)) {
        // Socket exists = MariaDB IS running, but SELinux blocking network check
        $mysqlRunning = true;
        $mysqlMessage = 'Running (SELinux blocks network check) ✓';
        $mysqlHelpText = 'Database server is running. SELinux prevents network connectivity check, but this is OK for installation.';
    } else {
        // No socket = MariaDB not running
        $mysqlMessage = 'Not running';
        $mysqlFixCmd = 'sudo systemctl start mariadb\nsudo systemctl enable mariadb';
        $mysqlHelpText = 'Database server is not running. Start MariaDB/MySQL to continue.';
    }
}

$checks['mysql_server'] = [
    'name' => 'MySQL/MariaDB Server',
    'status' => $mysqlRunning,
    'message' => $mysqlMessage,
    'fix_command' => $mysqlFixCmd,
    'help_text' => $mysqlHelpText,
    'critical' => true
];

// 4. Database Extension (PDO)
$checks['pdo'] = [
    'name' => 'PDO Extension',
    'status' => extension_loaded('pdo'),
    'message' => extension_loaded('pdo') ? 'Installed' : 'Not installed',
    'fix_command' => extension_loaded('pdo') ? '' : 'sudo dnf install php-pdo',
    'critical' => true
];

// 5. PDO MySQL Driver
$checks['pdo_mysql'] = [
    'name' => 'PDO MySQL Driver',
    'status' => extension_loaded('pdo_mysql'),
    'message' => extension_loaded('pdo_mysql') ? 'Installed' : 'Not installed',
    'fix_command' => extension_loaded('pdo_mysql') ? '' : 'sudo dnf install php-mysqlnd',
    'critical' => true
];

// 5. OpenSSL Extension
$checks['openssl'] = [
    'name' => 'OpenSSL Extension',
    'status' => extension_loaded('openssl'),
    'message' => extension_loaded('openssl') ? 'Installed' : 'Not installed',
    'critical' => true
];

// 6. MBString Extension
$checks['mbstring'] = [
    'name' => 'MBString Extension',
    'status' => extension_loaded('mbstring'),
    'message' => extension_loaded('mbstring') ? 'Installed' : 'Not installed',
    'critical' => true
];

// 7. JSON Extension
$checks['json'] = [
    'name' => 'JSON Extension',
    'status' => extension_loaded('json'),
    'message' => extension_loaded('json') ? 'Installed' : 'Not installed',
    'critical' => true
];

// 8. Curl Extension
$checks['curl'] = [
    'name' => 'Curl Extension',
    'status' => extension_loaded('curl'),
    'message' => extension_loaded('curl') ? 'Installed' : 'Not installed',
    'critical' => false
];

// 9. GD Extension (for image processing)
$checks['gd'] = [
    'name' => 'GD Extension',
    'status' => extension_loaded('gd'),
    'message' => extension_loaded('gd') ? 'Installed' : 'Not installed',
    'critical' => false
];

// 10. Zip Extension
$checks['zip'] = [
    'name' => 'Zip Extension',
    'status' => extension_loaded('zip'),
    'message' => extension_loaded('zip') ? 'Installed' : 'Not installed',
    'critical' => false
];

// 11. Storage Directory Writable
$storageDir = dirname(__DIR__, 2) . '/storage';

// Try to create storage subdirectories if they don't exist
$storageSubdirs = ['logs', 'cache', 'sessions', 'uploads'];
foreach ($storageSubdirs as $subdir) {
    $path = $storageDir . '/' . $subdir;
    if (!is_dir($path)) {
        @mkdir($path, 0775, true);
    }
}

$storageWritable = is_dir($storageDir) && is_writable($storageDir);
$storageMessage = '';
$storageFixCmd = '';

if ($storageWritable) {
    $storageMessage = 'Writable ✓';
} else {
    $storageMessage = 'Not writable';
    $storageFixCmd = 'sudo chmod -R 775 ' . realpath(dirname(__DIR__, 2)) . '/storage && sudo chown -R apache:apache ' . realpath(dirname(__DIR__, 2)) . '/storage';
    // Add SELinux fix
    $storageFixCmd .= "\n# If SELinux is enabled (Fedora/RHEL):\nsudo chcon -R -t httpd_sys_rw_content_t " . realpath(dirname(__DIR__, 2)) . '/storage';
}

$checks['storage_writable'] = [
    'name' => 'Storage Directory Writable',
    'status' => $storageWritable,
    'message' => $storageMessage,
    'fix_command' => $storageFixCmd,
    'critical' => true
];

// 12. Uploads Directory Writable
$uploadsDir = dirname(__DIR__) . '/uploads';

// Auto-fix: Try to create uploads directory
if (!is_dir($uploadsDir)) {
    $created = @mkdir($uploadsDir, 0775, true);
    if ($created) {
        @chmod($uploadsDir, 0775);
    }
}

// Try to create uploads subdirectories
$uploadSubdirs = ['feedback', 'products', 'customers', 'temp'];
foreach ($uploadSubdirs as $subdir) {
    $path = $uploadsDir . '/' . $subdir;
    if (!is_dir($path)) {
        @mkdir($path, 0775, true);
    }
}

$uploadsWritable = is_dir($uploadsDir) && is_writable($uploadsDir);
$uploadsMessage = '';
$uploadsFixCmd = '';

if ($uploadsWritable) {
    $uploadsMessage = 'Writable ✓';
} else {
    $uploadsMessage = 'Not writable';
    $uploadsFixCmd = 'sudo chmod -R 775 ' . realpath(dirname(__DIR__)) . '/uploads && sudo chown -R apache:apache ' . realpath(dirname(__DIR__)) . '/uploads';
    // Add SELinux fix
    $uploadsFixCmd .= "\n# If SELinux is enabled (Fedora/RHEL):\nsudo chcon -R -t httpd_sys_rw_content_t " . realpath(dirname(__DIR__)) . '/uploads';
}

$checks['uploads_writable'] = [
    'name' => 'Uploads Directory Writable',
    'status' => $uploadsWritable,
    'message' => $uploadsMessage,
    'fix_command' => $uploadsFixCmd,
    'critical' => true
];

// 13. .htaccess File Exists
$htaccessFile = dirname(__DIR__) . '/.htaccess';
$htaccessExists = file_exists($htaccessFile);
$htaccessMessage = '';
$htaccessFixCmd = '';

if ($htaccessExists) {
    $htaccessMessage = 'Present ✓';
} else {
    $htaccessMessage = 'Missing - Required for URL rewriting';
    $htaccessFixCmd = 'The .htaccess file should be in your public/ directory. Contact your system administrator or check the documentation.';
}

$checks['htaccess'] = [
    'name' => '.htaccess File',
    'status' => $htaccessExists,
    'message' => $htaccessMessage,
    'fix_command' => $htaccessFixCmd,
    'critical' => true
];

// 14. Apache mod_rewrite (if Apache)
$modRewriteEnabled = false;
$modRewriteMessage = '';
$modRewriteFixCmd = '';
$modRewriteHelpText = '';

if (stripos($webServer, 'apache') !== false || stripos($webServer, 'httpd') !== false) {
    // Actually verify mod_rewrite is loaded
    if (function_exists('apache_get_modules')) {
        // PHP-FPM or mod_php with apache_get_modules()
        $modRewriteEnabled = in_array('mod_rewrite', apache_get_modules());
    } else {
        // Fedora/RHEL: Use httpd -M to check loaded modules
        $httpdModules = shell_exec('httpd -M 2>/dev/null | grep -i rewrite');
        $modRewriteEnabled = !empty(trim($httpdModules));
    }

    if ($modRewriteEnabled) {
        $modRewriteMessage = 'Enabled ✓';
        $modRewriteHelpText = 'mod_rewrite is loaded and ready for clean URLs';
    } else {
        $modRewriteMessage = 'Not detected';
        $modRewriteHelpText = 'mod_rewrite is required for clean URLs';

        // Platform-specific fix commands
        if (file_exists('/etc/fedora-release') || file_exists('/etc/redhat-release')) {
            // Fedora/RHEL: mod_rewrite is usually compiled in, just verify config
            $modRewriteFixCmd = 'grep -i "LoadModule rewrite_module" /etc/httpd/conf.modules.d/*.conf\n# If not loaded, reinstall: sudo dnf reinstall httpd\nsudo systemctl restart httpd';
        } else {
            // Debian/Ubuntu
            $modRewriteFixCmd = 'sudo a2enmod rewrite\nsudo systemctl restart apache2';
        }
    }
} else {
    // Not Apache - Nginx or other
    $modRewriteEnabled = true; // Don't fail for non-Apache
    $modRewriteMessage = 'Not applicable (not Apache)';
    $modRewriteHelpText = 'URL rewriting configured differently on this web server';
}

$checks['mod_rewrite'] = [
    'name' => 'Apache mod_rewrite',
    'status' => $modRewriteEnabled,
    'message' => $modRewriteMessage,
    'fix_command' => $modRewriteFixCmd,
    'help_text' => $modRewriteHelpText,
    'critical' => false
];

// 15. SELinux Status (Fedora/RHEL specific) - IMPORTANT CHECK
$selinuxOk = true;
$selinuxFixCmd = '';
$selinuxHelpText = '';
$selinuxMessage = 'Not detected ✓';

// Check SELinux config file instead of getenforce (no shell commands needed)
if (file_exists('/etc/selinux/config')) {
    $selinuxConfig = @file_get_contents('/etc/selinux/config');
    if ($selinuxConfig && preg_match('/^SELINUX=(\w+)/m', $selinuxConfig, $matches)) {
        $configMode = $matches[1];

        if ($configMode === 'enforcing') {
            // SELinux is Enforcing - need to enable database connection boolean
            $selinuxOk = true;  // Not critical - installer will work, but app needs this
            $selinuxMessage = 'Enforcing (needs DB boolean) ⚠';
            $selinuxFixCmd = '# Allow Apache to connect to database:\nsudo setsebool -P httpd_can_network_connect_db 1';
            $selinuxHelpText = 'SELinux is Enforcing. To allow the application to connect to the database, enable the httpd_can_network_connect_db boolean. The installer can still run, but the application will need this.';
        } elseif ($configMode === 'permissive') {
            $selinuxOk = true;
            $selinuxMessage = 'Permissive ✓';
            $selinuxHelpText = 'SELinux is Permissive - allows all access but logs violations.';
        } elseif ($configMode === 'disabled') {
            $selinuxOk = true;
            $selinuxMessage = 'Disabled ✓';
            $selinuxHelpText = 'SELinux is disabled.';
        }
    } else {
        $selinuxMessage = 'Installed ✓';
        $selinuxHelpText = 'SELinux configuration detected';
    }
} else {
    $selinuxMessage = 'Not installed ✓';
    $selinuxHelpText = 'SELinux not detected on this system.';
}

$checks['selinux'] = [
    'name' => 'SELinux Status',
    'status' => $selinuxOk,
    'message' => $selinuxMessage,
    'fix_command' => $selinuxFixCmd,
    'help_text' => $selinuxHelpText,
    'critical' => false  // Warning only
];

// 16. Configuration File Writable
$envFile = dirname(__DIR__, 2) . '/.env';
$rootDir = dirname(__DIR__, 2);
$configWritable = false;
$configMessage = '';
$configFixCmd = '';

if (file_exists($envFile)) {
    $configWritable = is_writable($envFile);
    $configMessage = $configWritable ? 'Writable ✓' : 'Not writable';
    if (!$configWritable) {
         $configFixCmd = 'sudo chmod 664 ' . $envFile . ' && sudo chown apache:apache ' . $envFile;
         $configFixCmd .= "\n# SELinux:\nsudo chcon -t httpd_sys_rw_content_t " . $envFile;
    }
} else {
    $configWritable = is_writable($rootDir);
    $configMessage = $configWritable ? 'Writable (Directory) ✓' : 'Directory not writable';
    if (!$configWritable) {
        $configFixCmd = 'sudo chmod 775 ' . $rootDir . ' && sudo chown apache:apache ' . $rootDir;
        $configFixCmd .= "\n# SELinux:\nsudo chcon -t httpd_sys_rw_content_t " . $rootDir;
    }
}

$checks['config_writable'] = [
    'name' => 'Configuration Writable',
    'status' => $configWritable,
    'message' => $configMessage,
    'fix_command' => $configFixCmd,
    'critical' => true
];

// 17. Firewall Status
$firewallStatus = 'Unknown';
$firewallOk = true;
$firewallFixCmd = '';
$firewallHelpText = '';

// Since the user can access this page, the firewall is working for their connection
// Check if firewalld is running and show status for informational purposes
if (file_exists('/usr/bin/firewall-cmd')) {
    // Check if firewalld is running
    $firewallRunning = shell_exec('/usr/bin/firewall-cmd --state 2>/dev/null');

    if (trim($firewallRunning) === 'running') {
        // List open ports/services
        $httpOpen = shell_exec('/usr/bin/firewall-cmd --list-services 2>/dev/null');
        $portsOpen = shell_exec('/usr/bin/firewall-cmd --list-ports 2>/dev/null');

        // If user can access this installer, firewall is OK for their connection
        $firewallStatus = 'Running - You can access this page ✓';
        $firewallOk = true;
        $firewallHelpText = 'Firewall is active. Since you can access this installer, your connection is allowed. Services: ' . trim($httpOpen);

        // Show how to fully open if needed for external access
        if (strpos($httpOpen, 'http') === false && strpos($httpOpen, 'https') === false) {
            $firewallFixCmd = "# To allow external web access:\nsudo firewall-cmd --permanent --add-service=http\nsudo firewall-cmd --permanent --add-service=https\nsudo firewall-cmd --reload";
        }
    } else {
        $firewallStatus = 'Not running ✓';
        $firewallOk = true;
        $firewallHelpText = 'firewalld is not active';
    }
} else {
    // No firewall-cmd, assume OK
    $firewallStatus = 'No firewalld detected ✓';
    $firewallHelpText = 'firewalld not installed. If using iptables or another firewall, ensure ports 80/443 are accessible.';
}

$checks['firewall'] = [
    'name' => 'Firewall Status',
    'status' => $firewallOk,
    'message' => $firewallStatus,
    'fix_command' => $firewallFixCmd,
    'help_text' => $firewallHelpText,
    'critical' => false
];

// 17. PHP Memory Limit (informational only)
$memoryLimit = ini_get('memory_limit');
$memoryMessage = $memoryLimit;
$memoryHelpText = 'Current PHP memory limit. 128M is sufficient for most dive shop operations.';

$checks['memory_limit'] = [
    'name' => 'PHP Memory Limit',
    'status' => true,  // Always pass - just informational
    'message' => $memoryMessage,
    'fix_command' => '',
    'help_text' => $memoryHelpText,
    'critical' => false
];

// Helper function to convert memory limit to bytes
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    switch($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }
    return $val;
}

// Calculate overall status
$allPassed = true;
$criticalFailed = false;

foreach ($checks as $check) {
    if (!$check['status']) {
        $allPassed = false;
        if ($check['critical']) {
            $criticalFailed = true;
        }
    }
}

// Convert boolean status to string (success/error/warning) for frontend
$formattedChecks = [];
foreach ($checks as $key => $check) {
    $formatted = [
        'name' => $check['name'],
        'message' => $check['message'],
        'status' => $check['status']
            ? 'success'
            : ($check['critical'] ? 'error' : 'warning')
    ];

    // Add fix_command if it exists and check failed
    if (!$check['status'] && isset($check['fix_command']) && !empty($check['fix_command'])) {
        $formatted['fix_command'] = $check['fix_command'];
    }

    // Add help_text if it exists
    if (isset($check['help_text']) && !empty($check['help_text'])) {
        $formatted['help_text'] = $check['help_text'];
    }

    $formattedChecks[$key] = $formatted;
}

echo json_encode($formattedChecks);
