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

## ✨ Complete Feature List

### 💼 Point of Sale (POS)
- Fast checkout with product search
- Multiple payment methods (cash, card, gift cards)
- Barcode scanning support
- Customer lookup and history
- Transaction queue management
- Discount and promotion handling
- Receipt printing and email

### 📚 Course Management
- **Course Scheduling** - Create and manage course schedules
- **Instructor Portal** - Dashboard for instructors to manage their classes
- **Student Enrollment** - Enroll students and track progress
- **Student Transfers** - Move students between classes
- **PADI Compliance** - Skills checkoff aligned with PADI standards
- **Progress Tracking** - Knowledge, confined water, and open water tracking
- **Certification Tracking** - Record and verify certifications

### 👨‍🏫 Instructor Portal
- Personal dashboard with today's classes
- Student management across all assigned courses
- Progress tracking and skill checkoffs
- Email communications (welcome, reminders, thank you)
- SMS messaging for opted-in students
- View pending paperwork and recent completions

### 📧 Communications System
- **Email Templates** - Welcome, reminder, progress, thank you emails
- **SMS Integration** - Twilio-powered SMS messaging
- **Customer Opt-in** - SMS preference management
- **Bulk Messaging** - Send to multiple customers
- **Campaign Tracking** - Monitor communication effectiveness

### 👥 Customer Management (CRM)
- Customer profiles with full history
- Multiple phones, emails, addresses
- Certification tracking and verification
- Equipment ownership tracking
- Customer tags and segmentation
- Purchase and rental history
- Document storage

### 📦 Inventory Management
- Product catalog with categories
- Stock tracking and adjustments
- Low stock alerts
- Reorder point management
- Barcode/SKU support
- Product images
- Vendor management
- Purchase orders

### 🤿 Equipment Rentals
- Equipment checkout and return
- Maintenance scheduling
- Service history tracking
- Availability calendar
- Rental agreements

### 🏝️ Dive Trips
- Trip planning and scheduling
- Customer bookings
- Participant management
- Emergency contacts
- Trip waivers

### 💳 Payment Processing
- **Stripe** - Credit/debit cards
- **Square** - In-store payments
- **BTCPay** - Bitcoin/crypto (optional)
- Gift card support
- Layaway/payment plans
- Refund processing

### 📊 Reports & Analytics
- Sales reports (daily, weekly, monthly)
- Inventory reports
- Customer analytics
- Course analytics
- Trip analytics
- Financial dashboards
- Custom report builder
- Export to CSV/PDF

### 🔐 Security & Administration
- Role-based access control
- Audit logging
- User management
- Backup and restore
- System settings
- Multi-location support

### 📄 Document Management
- Customer document storage
- PADI form templates
- Medical forms
- Liability waivers
- Digital signatures

### 🎁 Loyalty Program
- Points earning and redemption
- Tier-based rewards
- Member benefits
- Transaction history

### 🤝 Integrations
- PADI eLearning (sync)
- QuickBooks (accounting)
- Mailchimp (marketing)
- Twilio (SMS)
- Firebase (push notifications)

---

## 🚀 Installation

### Option 1: Production Deployment (VPS/Dedicated)

1. **Server Setup:** Ubuntu 22.04/24.04 recommended
2. **Web Server:** Nginx or Apache with SSL
3. **Environment:** 
   ```bash
   cp .env.example .env
   # Update database credentials and APP_URL
   # Set APP_ENV=production and APP_DEBUG=false
   ```
4. **Permissions:** 
   ```bash
   chown -R www-data:www-data storage public/uploads
   chmod -R 775 storage public/uploads
   ```
5. **Install Dependencies:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
6. **Initialize:** Access `http://your-domain.com/install.php`
7. **CRON:** 
   ```bash
   * * * * * php /path/to/nautilus/artisan schedule:run >> /dev/null 2>&1
   ```

### Option 2: Local Testing (Docker)

```bash
./start-dev.sh
# Access http://localhost:8080/install.php
```

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

## 🗺️ Roadmap

### Coming Soon
- [ ] Mobile app for instructors
- [ ] Customer self-service portal enhancements
- [ ] Advanced dive log integration
- [ ] Equipment QR code tracking
- [ ] Multi-language support
- [ ] Advanced marketing automation

### Under Consideration
- [ ] AI-powered inventory forecasting
- [ ] Dive computer data integration
- [ ] Live weather/conditions integration
- [ ] Online booking widget for websites

---

## 🌍 Open Source

- ✅ **Free to use** - No monthly fees
- ✅ **Own your data** - Full control
- ✅ **Customize** - Modify as needed
- ✅ **Community-driven** - Contribute features
- ✅ **Multi-Tenant Ready** - Support multiple shop locations

---

## 🆘 Support

- **Documentation:** See `docs/` directory
- **Issues:** Report bugs on GitHub
- **Discussions:** Ask questions on GitHub

---

## 📊 Statistics

- 200+ database tables
- 250+ migrations
- 125+ controllers
- 128+ services
- Production-ready code

---

**Made with ❤️ for the diving community** 🌊
