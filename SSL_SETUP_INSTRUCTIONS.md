# 🔒 SSL Setup for Nautilus

## Quick Setup (Automated)

Run this single command to set everything up with SSL:

```bash
cd /home/wrnash1/development/nautilus
sudo ./setup-apache-ssl.sh
```

This script will:
1. ✅ Install `mod_ssl` if not present
2. ✅ Generate a self-signed SSL certificate (if you don't have one)
3. ✅ Create Apache virtual host configuration
4. ✅ Add `nautilus.local` to `/etc/hosts`
5. ✅ Set correct file permissions
6. ✅ Configure firewall for HTTPS
7. ✅ Test and restart Apache

---

## After Running the Script

### Access Your Application

**Primary URL:**
```
https://nautilus.local/store/login
```

**Alternative:**
```
https://localhost/store/login
```

### Login Credentials
- **Email:** `admin@nautilus.local`
- **Password:** `password`

### ⚠️ Browser Warning

Since we're using a self-signed certificate for development, your browser will show a security warning:

1. **Chrome/Edge:** Click "Advanced" → "Proceed to nautilus.local (unsafe)"
2. **Firefox:** Click "Advanced" → "Accept the Risk and Continue"
3. **Safari:** Click "Show Details" → "visit this website"

This is NORMAL for development with self-signed certificates.

---

## Manual Setup (If Script Fails)

### 1. Install mod_ssl
```bash
sudo dnf install mod_ssl
```

### 2. Generate Self-Signed Certificate
```bash
sudo openssl req -new -newkey rsa:2048 -days 365 -nodes -x509 \
  -subj "/C=US/ST=State/L=City/O=DiveShop/CN=nautilus.local" \
  -keyout /etc/pki/tls/private/nautilus-selfsigned.key \
  -out /etc/pki/tls/certs/nautilus-selfsigned.crt

sudo chmod 600 /etc/pki/tls/private/nautilus-selfsigned.key
```

### 3. Copy Apache Configuration
```bash
sudo cp /home/wrnash1/development/nautilus/apache-config/nautilus.conf /etc/httpd/conf.d/
```

### 4. Add to /etc/hosts
```bash
echo "127.0.0.1   nautilus.local" | sudo tee -a /etc/hosts
```

### 5. Set Permissions
```bash
sudo chown -R apache:apache /var/www/html/nautilus
sudo chmod -R 755 /var/www/html/nautilus
sudo chmod -R 775 /var/www/html/nautilus/storage
sudo chmod -R 775 /var/www/html/nautilus/public/uploads
```

### 6. Configure Firewall
```bash
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### 7. Test and Restart Apache
```bash
sudo apachectl configtest
sudo systemctl restart httpd
```

---

## For Production: Use Real SSL Certificate

For production deployment, replace the self-signed certificate with a real one:

### Option 1: Let's Encrypt (Free, Automated)
```bash
sudo dnf install certbot python3-certbot-apache
sudo certbot --apache -d yourdomain.com
```

### Option 2: Commercial Certificate
1. Purchase SSL certificate from a CA (Digicert, Comodo, etc.)
2. Place certificate files in:
   - Certificate: `/etc/pki/tls/certs/yourdomain.crt`
   - Private Key: `/etc/pki/tls/private/yourdomain.key`
   - Chain: `/etc/pki/tls/certs/ca-chain.crt`
3. Update `/etc/httpd/conf.d/nautilus.conf`:
```apache
SSLCertificateFile /etc/pki/tls/certs/yourdomain.crt
SSLCertificateKeyFile /etc/pki/tls/private/yourdomain.key
SSLCertificateChainFile /etc/pki/tls/certs/ca-chain.crt
```

---

## Troubleshooting

### Check if SSL module is loaded
```bash
sudo httpd -M | grep ssl
```
Should show: `ssl_module (shared)`

### Check certificate files exist
```bash
ls -l /etc/pki/tls/certs/nautilus-selfsigned.crt
ls -l /etc/pki/tls/private/nautilus-selfsigned.key
```

### View Apache error logs
```bash
sudo tail -f /var/log/httpd/nautilus-ssl-error.log
```

### Test Apache configuration
```bash
sudo apachectl configtest
```

### Check if Apache is listening on port 443
```bash
sudo netstat -tlnp | grep :443
```
or
```bash
sudo ss -tlnp | grep :443
```

### Restart Apache
```bash
sudo systemctl restart httpd
sudo systemctl status httpd
```

### SELinux Issues (if you get permission denied)
```bash
# Allow Apache to use network
sudo setsebool -P httpd_can_network_connect 1

# Check SELinux denials
sudo ausearch -m avc -ts recent
```

---

## Certificate Information

After setup, your self-signed certificate will have:
- **Subject:** CN=nautilus.local
- **Issuer:** Self-signed
- **Valid for:** 365 days
- **Key size:** 2048-bit RSA

To view certificate details:
```bash
openssl x509 -in /etc/pki/tls/certs/nautilus-selfsigned.crt -text -noout
```

---

## Security Notes

### Development (Self-Signed Certificate)
- ✅ Encrypts traffic between browser and server
- ⚠️ Browser will show warning (expected)
- ⚠️ Not trusted by browsers by default
- ✅ Perfect for local development and testing

### Production (Real Certificate)
- ✅ Fully trusted by all browsers
- ✅ No security warnings
- ✅ Required for production use
- ✅ SEO benefits (Google prefers HTTPS)

---

## Next Steps After SSL Setup

1. **Sync the latest code:**
```bash
sudo /tmp/nautilus_sync.sh
```

2. **Access the application:**
```
https://nautilus.local/store/login
```

3. **Login and test:**
   - Email: `admin@nautilus.local`
   - Password: `password`

4. **Change default password** in Settings!

---

## Alternative: Disable HTTPS for Testing

If you want to test without SSL first:

1. Don't run the SSL setup script
2. Edit `.htaccess` to comment out HTTPS redirect
3. Access via: `http://localhost/nautilus/public/store/login`

But for a dive shop business, **HTTPS is strongly recommended** even in development!

