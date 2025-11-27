# Nautilus - Quick Start Guide

## Installation in 3 Easy Steps

### Step 1: Upload Files

Upload all Nautilus files to your web server directory:
- **Linux**: `/var/www/html/nautilus`
- **cPanel**: `public_html/nautilus`

### Step 2: Run Setup

**SSH into your server** and run:

```bash
cd /var/www/html/nautilus
sudo bash setup.sh
```

Then **edit the .env file** with your database details:

```bash
nano .env
```

Update these lines:
```
DB_DATABASE=nautilus
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password
```

Save and exit (Ctrl+X, then Y, then Enter).

### Step 3: Install via Web Browser

Open your browser and go to:

```
https://your-domain.com/check-requirements.php
```

If all checks pass (green checkmarks), click **"Proceed to Installation"** or visit:

```
https://your-domain.com/simple-install.php
```

Fill in the form:
- Business Name
- Admin Email
- Admin Password

Click **"Install Nautilus"** and wait for completion.

---

## Done! 🎉

You can now log in at:
```
https://your-domain.com/store/login
```

---

## Troubleshooting

### Can't access the installer?

1. Check file permissions:
   ```bash
   sudo chown -R www-data:www-data /var/www/html/nautilus
   sudo chmod -R 775 /var/www/html/nautilus/storage
   ```

2. Check Apache error log:
   ```bash
   sudo tail -f /var/log/apache2/error.log
   ```

### "HTTP 500 Error"?

- Make sure Composer dependencies are installed:
  ```bash
  cd /var/www/html/nautilus
  composer install --no-dev
  ```

- Ensure `.env` file exists:
  ```bash
  cp .env.example .env
  ```

### Database connection failed?

- Verify MySQL is running:
  ```bash
  sudo systemctl status mysql
  ```

- Test database credentials:
  ```bash
  mysql -u your_user -p -h localhost
  ```

---

## Need Help?

See the full **[INSTALL.md](INSTALL.md)** for detailed instructions.
