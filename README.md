# 🌊 Nautilus - Dive Shop Management System

**Complete open-source management system for dive shops**

---

## 🎯 What is Nautilus?

Nautilus is a complete business management system designed specifically for dive shops. It handles everything from point-of-sale to course scheduling, inventory management, and customer relationships.

### Built for Dive Shops, By Divers

- 💰 **Point of Sale (POS)** - Process sales and rentals  
- 📚 **Course Management** - Schedule PADI, SSI, NAUI certifications
- 👥 **Customer Management (CRM)** - Track customers, certs, history
- 📦 **Inventory** - Products, equipment, stock tracking
- 🏊 **Dive Trips** - Plan and manage dive trips
- 🛒 **E-commerce** - Online booking and sales
- 📊 **Reports & Analytics** - Business insights
- 💳 **Payment Processing** - Stripe, Square integration

---

## 🚀 Installation

You can install Nautilus locally for testing or on a server for production.

### Option 1: Production Deployment (VPS/Dedicated)
**Best for live business operations**

1. **Server Setup:** Ubuntu 22.04/24.04 recommended.
2. **Web Server:** Nginx or Apache with SSL.
3. **Environment:** 
   - Copy `.env.example` to `.env`: `cp .env.example .env`
   - Update database credentials and `APP_URL`.
   - Set `APP_ENV=production` and `APP_DEBUG=false`.
4. **Permissions:** 
   ```bash
   chown -R www-data:www-data storage public/uploads
   chmod -R 775 storage public/uploads
   ```
5. **Install Dependencies:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
6. **Initialize:** Access `http://your-domain.com/install.php` to initialize the database and admin user.
7. **CRON:** Set up the system cron for scheduled tasks:
   ```bash
   * * * * * php /path/to/nautilus/artisan schedule:run >> /dev/null 2>&1
   ```
8. **Queue:** Use Supervisor to run `php artisan queue:work` (if using queues).

### Option 2: Local Testing (Docker / XAMPP)

**Docker:**
1. Ensure Docker and Docker Compose are installed.
2. Run `./start-dev.sh`.
3. Access `http://localhost:8080/install.php`.

**XAMPP/MAMP:**
1. Clone into `htdocs`.
2. Create a database named `nautilus`.
3. Access `http://localhost/nautilus/public/install.php`.

---

## 💻 System Requirements

**Minimum:**
- PHP 8.0+, MySQL 5.7+ or MariaDB 10.3+
- 256MB RAM, 500MB disk space
- Extensions: PDO, OpenSSL, Mbstring, Tokenizer, XML, Ctype, JSON

**Recommended:**
- PHP 8.2+, MySQL 8.0+ or MariaDB 10.6+
- 512MB RAM, 1GB disk space, SSL certificate
- Redis (optional, for caching/queues)

---

## ✨ Key Features

- **Point of Sale (POS)** - Fast checkout, multiple payments
- **Course Management** - Schedule courses, track certifications
- **CRM** - Customer database, purchase history
- **Inventory** - Stock tracking, barcode support
- **Equipment Rentals** - Checkout, maintenance tracking
- **E-commerce** - Customer-facing website
- **Dive Trips** - Trip planning and bookings
- **Payments** - Stripe, Square, BTCPay
- **Reports** - Sales, inventory, analytics
- **Security** - Role-based access, audit logs

---

## 🌍 Open Source

- ✅ **Free to use** - No monthly fees
- ✅ **Own your data** - Full control
- ✅ **Customize** - Modify as needed
- ✅ **Community-driven** - Contribute features
- ✅ **Multi-Tenant Ready** - Support multiple shop locations

---

## 🆘 Support

- **Documentation:** See `docs/` directory (Coming Soon)
- **Issues:** Report bugs on GitHub
- **Discussions:** Ask questions on GitHub

---

## 📊 Statistics

- 200+ database tables
- 120+ migrations
- Production-ready code

---

**Made with ❤️ for the diving community** 🌊
