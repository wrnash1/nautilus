# Fix: Installation Redirect Issue

## Problem
When database is empty/deleted, accessing the site should go to `/install.php` but instead goes to dashboard or shows errors.

## Root Cause
The application tries to connect to database immediately in HomeController and other controllers before checking if installation is complete.

## Solution

We need to add installation checking BEFORE the router runs.

### Fix in `public/index.php`

Add this AFTER line 60 (after error handler) and BEFORE line 62 (load routes):

```php
// Check if application is installed
$installedFile = __DIR__ . '/../.installed';
$installScript = __DIR__ . '/install.php';

// If not installed and not accessing install.php, redirect to installer
if (!file_exists($installedFile) && basename($_SERVER['SCRIPT_FILENAME']) !== 'install.php') {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Allow install routes
    if (strpos($requestUri, '/install') !== 0 && $requestUri !== '/install.php') {
        // Redirect to standalone installer
        if (file_exists($installScript)) {
            header('Location: /install.php');
            exit;
        }
    }
}
```

This will:
1. Check if `.installed` file exists
2. If not, and user is not already on install.php, redirect them
3. Allow the installer to run properly

### Alternative: Use standalone install.php

The `install.php` in the root directory is standalone and doesn't load the full application. It should work independently.

**To use it:**
1. Access: `https://nautilus.local/install.php` (NOT `/install`)
2. This bypasses the application router entirely
3. Runs the 4-step wizard
4. Creates `.installed` file when done

## Quick Test

```bash
# Remove .installed file
rm -f /var/www/html/nautilus/.installed

# Access the site - should redirect to installer
curl -I https://nautilus.local

# Or access installer directly
curl -I https://nautilus.local/install.php
```

## Why This Happened

The application has TWO install systems:
1. `/install.php` - Standalone installer (root directory)
2. `/install` route → InstallController - Integrated installer

When database is missing, the integrated one fails because it tries to load the application first.

**Recommendation:** Use `/install.php` (standalone) for fresh installations.
