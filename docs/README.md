# 🌊 Nautilus - Dive Shop Management System

**Version**: 2.0  
**Status**: Production Ready

Complete management system for dive shops featuring POS, inventory, customer management, course scheduling, certifications, equipment rentals, and e-commerce.

---

## ⚡ Quick Start

### Installation (Universal - All Linux Distributions)

```bash
cd /home/wrnash1/Developer/nautilus
sudo bash scripts/universal-install.sh
```

Then open browser: **https://nautilus.local/install/**

### Login Credentials (After Installation)
- **Email**: admin@nautilus.local
- **Password**: admin123
- ⚠️ **Change password immediately after first login!**

---

## 📋 System Requirements

- **Server**: Apache 2.4+ with mod_rewrite, mod_ssl
- **PHP**: 8.1+ (pdo_mysql, mbstring, json, curl, gd, zip)
- **Database**: MySQL 8.0+ or MariaDB 10.6+
- **OS**: RHEL/Fedora/CentOS, Debian/Ubuntu, Arch, openSUSE, Alpine
- **Storage**: 2GB RAM minimum, 10GB disk space

---

## 🚀 Features

### Core Modules
- **Point of Sale (POS)** - Professional checkout, multi-payment, receipt printing
- **Inventory Management** - Products, stock tracking, reorder alerts, barcode scanning
- **Customer Management (CRM)** - Profiles, certifications, purchase history, communications
- **Course Scheduling** - Dive courses, instructor assignment, student enrollment
- **Equipment Rentals** - Tanks, regulators, BCDs, wetsuits, tracking
- **Certifications** - PADI, SSI, NAUI, TDI, certification tracking
- **E-commerce Storefront** - Online catalog, shopping cart, customer portal
- **Work Orders** - Equipment service, repair tracking
- **Reporting & Analytics** - Sales reports, inventory reports, customer analytics

### Advanced Features
- **Multi-Tenant Support** - Multiple dive shops, one installation
- **Role-Based Access Control** - Granular permissions system
- **Two-Factor Authentication** - Enhanced security
- **SSO Integration** - Google, Microsoft, GitHub OAuth
- **Audit Logging** - Track all system changes
- **PWA Support** - Offline mode, mobile install
- **Internationalization** - Multi-language support
- **API** - RESTful API for integrations

---

## 📖 Quick Reference

### Common Tasks

#### Add a Product
1. Navigate to **Inventory → Products**
2. Click "Add Product"
3. Fill in SKU, name, price, cost, stock
4. Save

#### Process a Sale (POS)
1. Navigate to **POS**
2. Select customer (or walk-in)
3. Add items to cart
4. Select payment method
5. Complete transaction

#### Enroll Student in Course
1. **Courses → Schedules**
2. Select course schedule
3. Click "Enroll Student"
4. Select student, mark payment
5. Student appears on roster

#### Create Invoice
1. Navigate to **Sales → Invoices**
2. Click "Create Invoice"
3. Select customer
4. Add line items
5. Generate and print/email

---

## 🔧 Troubleshooting

### Login Issues
Check default credentials:
```bash
mysql -u root -p nautilus -e "SELECT email, username FROM users WHERE id=1;"
```

### Permission Errors
Fix file permissions:
```bash
sudo chown -R apache:apache /var/www/html/nautilus
sudo chmod -R 775 /var/www/html/nautilus/storage
```

### SELinux Issues  
If SELinux is blocking:
```bash
sudo ausearch -m avc -ts recent | grep denied
# Fix contexts
sudo restorecon -Rv /var/www/html/nautilus
```

### Database Connection Failed
1. Check `.env` file has correct credentials
2. Verify MySQL is running: `systemctl status mysql`
3. Test connection: `mysql -u username -p -h localhost`

---

## 🔐 Security

### Production Checklist
- [ ] Change default admin password
- [ ] Enable HTTPS (SSL certificate)
- [ ] Set `APP_DEBUG=false` in .env
- [ ] Enable SELinux enforcing mode
- [ ] Configure firewall (ports 80, 443 only)
- [ ] Set proper file permissions (755 dirs, 644 files)
- [ ] Secure `.env` file (`chmod 600`)
- [ ] Enable automated backups
- [ ] Review audit logs regularly

---

## 📁 Directory Structure

```
nautilus/
├── app/                # Application code
│   ├── Controllers/    # Request handlers
│   ├── Services/       # Business logic
│   ├── Models/         # Data models  
│   ├── Views/          # Templates
│   └── Core/           # Framework core
├── public/             # Web root
│   ├── assets/         # CSS, JS, images
│   └── uploads/        # User uploads
├── database/
│   └── migrations/     # SQL migrations (110+ files)
├── storage/
│   ├── logs/           # Application logs
│   └── backups/        # Database backups
├── scripts/            # Utility scripts
└── docs/               # Documentation
```

---

## 🆘 Support

### View Logs
```bash
# Application logs
sudo tail -f /var/www/html/nautilus/storage/logs/debug_auth.log

# Apache logs
sudo tail -f /var/log/httpd/nautilus_ssl_error.log

# Database logs
sudo tail -f /var/log/mysql/error.log
```

### Backup Database
```bash
mysqldump -u root -p nautilus > nautilus_backup_$(date +%Y%m%d).sql
```

### Restore Database
```bash
mysql -u root -p nautilus < nautilus_backup_20251127.sql
```

---

## 📚 Full Documentation

For complete documentation including:
- Development guidelines
- API reference
- Database schema
- Deployment procedures
- Security best practices

See: **[COMPLETE_GUIDE.md](COMPLETE_GUIDE.md)**

---

## 📞 Getting Help

1. Check this README for quick answers
2. Review [COMPLETE_GUIDE.md](COMPLETE_GUIDE.md) for details
3. Check log files for errors
4. Review code comments in source files

---

## 📜 License

Proprietary - All Rights Reserved

---

**Made with ❤️ for the diving community**
