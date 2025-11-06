# Fix Installation Permission Error

## Problem

The installation wizard fails with:
```
file_put_contents(/var/www/html/nautilus/.env): Failed to open stream: Permission denied
```

## Root Cause

The installer needs to create/update the `.env` file, but either:
1. The file doesn't exist and the directory isn't writable
2. The file exists but isn't writable by the `apache` user
3. SELinux is blocking the write operation

## Quick Fix

Run this command as root (you'll need to run it manually since sudo requires a password):

```bash
sudo bash /tmp/fix-installation-permissions.sh
```

## Manual Fix (If Script Doesn't Work)

Run these commands manually:

### Step 1: Set Ownership
```bash
sudo chown -R apache:apache /var/www/html/nautilus
```

### Step 2: Set Base Permissions
```bash
# Directories
sudo find /var/www/html/nautilus -type d -exec chmod 755 {} \;

# Files
sudo find /var/www/html/nautilus -type f -exec chmod 644 {} \;
```

### Step 3: Make Writable Directories
```bash
sudo chmod -R 775 /var/www/html/nautilus/storage
sudo chmod -R 775 /var/www/html/nautilus/public/uploads
```

### Step 4: Fix .env Permissions
```bash
# If .env exists
sudo chmod 664 /var/www/html/nautilus/.env
sudo chown apache:apache /var/www/html/nautilus/.env

# If .env doesn't exist, make the directory writable
sudo chmod 775 /var/www/html/nautilus
```

### Step 5: Fix SELinux (If Enabled)

Check if SELinux is the problem:
```bash
sudo getenforce
```

If it shows "Enforcing", run:

```bash
# Allow Apache to write to storage and .env
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/storage(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/public/uploads(/.*)?"
sudo semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/html/nautilus/.env"

# Apply the contexts
sudo restorecon -Rv /var/www/html/nautilus/storage
sudo restorecon -Rv /var/www/html/nautilus/public/uploads
sudo restorecon -v /var/www/html/nautilus/.env
```

Or temporarily disable SELinux to test:
```bash
sudo setenforce 0
# Try installation
# Then re-enable: sudo setenforce 1
```

## Verify Permissions

After fixing, verify:

```bash
ls -la /var/www/html/nautilus/ | grep -E '\.env|storage'
```

Should show:
```
drwxrwxr-x  apache apache  storage/
-rw-rw-r--  apache apache  .env
```

## Alternative: Delete .env and Let Installer Create It

If the .env file exists with wrong permissions:

```bash
# Backup existing
sudo cp /var/www/html/nautilus/.env /tmp/.env.backup

# Delete it
sudo rm /var/www/html/nautilus/.env

# Make directory writable
sudo chmod 775 /var/www/html/nautilus

# Try installation again
```

The installer will create a new .env file with correct permissions.

## After Fixing

1. Navigate to: https://nautilus.local/install
2. The installation should now proceed without permission errors
3. After successful installation, secure the .env file:
   ```bash
   sudo chmod 640 /var/www/html/nautilus/.env
   ```

## Troubleshooting

### Check Apache User
```bash
ps aux | grep -E 'apache|httpd' | grep -v grep | head -3
```
Should show processes running as `apache` user.

### Check SELinux Denials
```bash
sudo ausearch -m avc -ts recent
```

### Check Directory Tree Permissions
```bash
namei -l /var/www/html/nautilus/.env
```

This shows permissions for each directory in the path.

## Prevention for Future

After installation completes, set secure permissions:

```bash
# Secure .env
sudo chmod 640 /var/www/html/nautilus/.env
sudo chown apache:apache /var/www/html/nautilus/.env

# Keep storage writable
sudo chmod -R 775 /var/www/html/nautilus/storage
sudo chown -R apache:apache /var/www/html/nautilus/storage

# Keep uploads writable
sudo chmod -R 775 /var/www/html/nautilus/public/uploads
sudo chown -R apache:apache /var/www/html/nautilus/public/uploads
```

---

**Quick Command to Run:**

```bash
sudo bash /tmp/fix-installation-permissions.sh
```

Then retry the installation at: https://nautilus.local/install
