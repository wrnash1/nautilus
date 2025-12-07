# Nautilus Utility Scripts

This directory contains command-line utilities for system administration and maintenance.

## Available Scripts

### create-admin-cli.php
**Purpose:** Create admin user accounts from command line

**Usage:**
```bash
php bin/create-admin-cli.php
```

**When to use:**
- Creating additional admin accounts
- Recovering from lost admin password
- Automated deployment scripts

---

### seed-roles.php
**Purpose:** Seed or re-seed user roles and permissions

**Usage:**
```bash
php bin/seed-roles.php
```

**When to use:**
- Initial setup if roles weren't seeded
- After database reset
- Updating role permissions

---

### seed-roles-simple.php
**Purpose:** Alternative simplified role seeder

**Usage:**
```bash
php bin/seed-roles-simple.php
```

**When to use:**
- If the main seed-roles.php fails
- Minimal role setup

---

### test-installation.php
**Purpose:** Test database connection and verify installation

**Usage:**
```bash
php bin/test-installation.php
```

**When to use:**
- Verifying installation is complete
- Troubleshooting database connection issues
- Testing migrations ran successfully

---

### install.sh
**Purpose:** Command-line installer (alternative to web installer)

**Usage:**
```bash
bash bin/install.sh
```

**When to use:**
- Automated deployments
- Server environments without web access
- CI/CD pipelines

**Note:** Most users should use the web installer at `/install` instead

---

## Production Recommendation

For production environments, use the web-based installer at `/install` which provides:
- Visual progress tracking
- Error handling with helpful messages
- Database configuration wizard
- Automatic migration running
- Admin account creation

These scripts are provided for advanced users and system administrators who prefer command-line tools.
