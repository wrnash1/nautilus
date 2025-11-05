# Apache Configuration for Nautilus

## Current Issue
The application isn't accessible because Apache's DocumentRoot needs to point to the `public` directory.

## Quick Fix Options

### Option 1: Access via Direct Path (Immediate - TEST THIS FIRST)
Try accessing: **http://localhost/nautilus/public/index.php**

If this works, then try: **http://localhost/nautilus/public/store/login**

### Option 2: Configure Apache Virtual Host (Recommended)

1. Copy the Apache config:
```bash
sudo cp /home/wrnash1/development/nautilus/apache-config/nautilus.conf /etc/httpd/conf.d/
```

2. Test Apache configuration:
```bash
sudo apachectl configtest
```

3. Restart Apache:
```bash
sudo systemctl restart httpd
```

4. Then access: **http://localhost/store/login**

### Option 3: Symlink Public Directory (Quick Alternative)

```bash
sudo rm -rf /var/www/html/nautilus
sudo ln -s /home/wrnash1/development/nautilus/public /var/www/html/nautilus
sudo systemctl restart httpd
```

Then access: **http://localhost/nautilus/store/login**

### Option 4: Modify Main Apache Config

Edit `/etc/httpd/conf/httpd.conf` and change DocumentRoot:

```apache
DocumentRoot "/var/www/html/nautilus/public"

<Directory "/var/www/html/nautilus/public">
    AllowOverride All
    Require all granted
</Directory>
```

Then restart Apache.

## Verify Apache Modules

Make sure mod_rewrite is enabled:
```bash
sudo httpd -M | grep rewrite
```

If not showing, enable it:
```bash
# Check if it's in the config
grep -r "LoadModule rewrite_module" /etc/httpd/
```

## Check Current DocumentRoot

```bash
grep -i "^DocumentRoot" /etc/httpd/conf/httpd.conf
```

## Testing Steps

1. **First test direct PHP access:**
   - http://localhost/nautilus/public/index.php

2. **Check if mod_rewrite works:**
   - http://localhost/nautilus/public/

3. **Test application routes:**
   - http://localhost/nautilus/public/store/login

4. **Check error logs if issues:**
```bash
sudo tail -f /var/log/httpd/error_log
```

## Current Structure

```
/var/www/html/
├── index.php
└── nautilus/              ← Apache sees this as base
    ├── app/
    ├── database/
    ├── public/            ← Application entry point
    │   ├── index.php     ← Needs to be the DocumentRoot
    │   └── .htaccess
    └── vendor/
```

## What Should Happen

When configured correctly:
- User visits: `http://localhost/store/login`
- Apache serves: `/var/www/html/nautilus/public/index.php`
- Router processes: `/store/login`
- Displays: Login page

---

## Recommended: Option 1 First

**Try this URL right now:**
```
http://localhost/nautilus/public/store/login
```

If it works, we just need to adjust the Apache config. If it doesn't work, check the error log.
