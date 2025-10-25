# Nautilus V6 - Quick Start Guide

## 🚀 Split Applications in 5 Steps

### 1️⃣ Run the Split Script
```bash
cd /home/wrnash1/Developer/nautilus-v6
./scripts/split-applications.sh
```

### 2️⃣ Install Dependencies
```bash
cd /home/wrnash1/Developer/nautilus-storefront
composer install

cd /home/wrnash1/Developer/nautilus-store
composer install
```

### 3️⃣ Configure Environment
Edit both `.env` files:
- `/home/wrnash1/Developer/nautilus-storefront/.env`
- `/home/wrnash1/Developer/nautilus-store/.env`

Ensure same database credentials in both!

### 4️⃣ Deploy to Web Server
```bash
# Copy Storefront
sudo rsync -av --delete --exclude='vendor/' \
  /home/wrnash1/Developer/nautilus-storefront/ \
  /var/www/html/nautilus-storefront/

# Copy Store
sudo rsync -av --delete --exclude='vendor/' \
  /home/wrnash1/Developer/nautilus-store/ \
  /var/www/html/nautilus-store/

# Install dependencies on server
cd /var/www/html/nautilus-storefront && composer install
cd /var/www/html/nautilus-store && composer install

# Set permissions
sudo chown -R www-data:www-data /var/www/html/nautilus-storefront
sudo chown -R www-data:www-data /var/www/html/nautilus-store
```

### 5️⃣ Configure Apache

Add to your VirtualHost:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com

    # External Storefront
    DocumentRoot /var/www/html/nautilus-storefront/public

    <Directory /var/www/html/nautilus-storefront/public>
        AllowOverride All
        Require all granted
    </Directory>

    # Internal Store at /store
    Alias /store /var/www/html/nautilus-store/public

    <Directory /var/www/html/nautilus-store/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

Then restart Apache:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

## 🌐 Test Your Applications

- **External Storefront:** http://yourdomain.com/
- **Internal Store:** http://yourdomain.com/store/

---

## 📚 Full Documentation

See [APPLICATION_SPLIT_GUIDE.md](APPLICATION_SPLIT_GUIDE.md) for complete details.

---

## ✅ Quick Checklist

- [ ] Split script completed successfully
- [ ] Composer dependencies installed
- [ ] .env files configured (same database!)
- [ ] Files copied to /var/www/html/
- [ ] Permissions set (www-data:www-data)
- [ ] Apache configured with Alias
- [ ] mod_rewrite enabled
- [ ] Apache restarted
- [ ] External site works (/)
- [ ] Internal site works (/store)
- [ ] Staff login tested
- [ ] Database migrations run

---

## 🔧 Troubleshooting

**404 errors?**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

**Permission errors?**
```bash
sudo chown -R www-data:www-data /var/www/html/nautilus-storefront
sudo chown -R www-data:www-data /var/www/html/nautilus-store
sudo chmod -R 755 /var/www/html/nautilus-storefront
sudo chmod -R 755 /var/www/html/nautilus-store
```

**Database connection failed?**
- Check both .env files have correct DB credentials
- Both apps must use the SAME database

---

## 📞 Need Help?

See full documentation in [APPLICATION_SPLIT_GUIDE.md](APPLICATION_SPLIT_GUIDE.md)
