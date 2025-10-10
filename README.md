# Nautilus v6.0 - Enterprise Dive Shop Management System

A comprehensive, enterprise-grade web application for managing all aspects of dive shop operations, including POS, CRM, inventory, rentals, courses, e-commerce, and more.

**Built for:** Bill Nash (@wrnash1)  
**Devin Run:** https://app.devin.ai/sessions/0a53533785e14a6f95aae83c5390ae8a

## 🌊 Overview

Nautilus v6.0 is a full-featured dive shop management system designed to handle B2C and B2B operations with enterprise-grade functionality. The system supports the complete lifecycle of dive shop operations from point-of-sale transactions to customer relationship management, equipment rentals, training courses, and online e-commerce.

## 🏗️ Architecture

- **Framework:** Custom PHP 8.2+ MVC Framework
- **Database:** MySQL 8.0+ / MariaDB 10.6+
- **Web Server:** Apache 2.4+ with mod_rewrite
- **Architecture Pattern:** MVC with Service Layer and Repository Pattern
- **Security:** Role-Based Access Control (RBAC), CSRF Protection, Session Management
- **Integration:** Google Workspace (Calendar, Gmail, Drive)

## 📋 Features by Phase

### Phase 1: Core Operations
- **POS System:** Complete point-of-sale with multiple payment methods
- **CRM:** B2C and B2B customer management with 360° customer view
- **Inventory Management:** Product catalog, stock tracking, purchase orders
- **Basic Reporting:** Sales, inventory, and customer reports

### Phase 2: Specialized Operations
- **Rental Management:** Equipment checkout/checkin, maintenance tracking
- **Training Courses:** Course scheduling, enrollment, certification management
- **Trip Bookings:** Dive trip management and group bookings
- **Work Orders:** Equipment service and repair tracking

### Phase 3: E-Commerce & Digital
- **Online Store:** Full e-commerce platform with shopping cart
- **Customer Portal:** Self-service account management
- **Content Management:** Blog, news, and page management
- **Email Marketing:** Campaign management and automation

### Phase 4: Advanced Features
- **Loyalty Programs:** Points, rewards, and tiered memberships
- **Gift Cards & Store Credit:** Digital gift cards and credit management
- **Cryptocurrency Payments:** Bitcoin and other crypto support
- **Layaway Programs:** Flexible payment plans

### Phase 5: Enterprise & Analytics
- **Staff Management:** Scheduling, time tracking, payroll integration
- **Advanced Reporting:** Business intelligence and analytics
- **Multi-location Support:** Franchise and chain management
- **API & Integrations:** RESTful API for third-party integrations

## 📁 Directory Structure

```
nautilus-v6/
├── app/
│   ├── Controllers/          # HTTP request handlers
│   │   ├── Admin/           # Admin dashboard
│   │   ├── Auth/            # Authentication
│   │   ├── CRM/             # Customer management
│   │   ├── Courses/         # Training courses
│   │   ├── Ecommerce/       # Online store
│   │   ├── Inventory/       # Product management
│   │   ├── POS/             # Point of sale
│   │   └── Rentals/         # Equipment rentals
│   ├── Core/                # Core framework classes
│   │   ├── Auth.php         # Authentication system
│   │   ├── Database.php     # Database connection
│   │   └── Router.php       # URL routing
│   ├── Middleware/          # HTTP middleware
│   │   ├── AuthMiddleware.php
│   │   └── CsrfMiddleware.php
│   ├── Models/              # Data models
│   │   ├── Customer.php
│   │   ├── Product.php
│   │   └── User.php
│   ├── Services/            # Business logic layer
│   │   ├── CRM/
│   │   ├── Courses/
│   │   ├── Ecommerce/
│   │   ├── Integration/
│   │   ├── Inventory/
│   │   ├── POS/
│   │   └── Rentals/
│   └── helpers.php          # Helper functions
├── database/
│   └── migrations/          # Database schema files (13 migrations)
├── docs/                    # Documentation
│   ├── API.md              # API documentation
│   └── DEPLOYMENT.md       # Deployment guide
├── public/                  # Web root
│   ├── index.php           # Application entry point
│   ├── .htaccess           # Apache configuration
│   └── uploads/            # User uploads
├── routes/
│   └── web.php             # Route definitions
├── storage/                 # Application storage
│   ├── backups/            # Database backups
│   ├── cache/              # Application cache
│   ├── logs/               # Log files
│   └── sessions/           # Session files
├── .env.example            # Environment configuration template
├── .gitignore              # Git ignore rules
├── composer.json           # PHP dependencies
├── LICENSE                 # License file
└── README.md               # This file
```

## 🗄️ Database Schema

The system includes 13 comprehensive database migrations covering:

1. **Users & Authentication** - RBAC, permissions, sessions, audit logs
2. **Customers** - B2C/B2B customers, addresses, tags, communications
3. **Products & Inventory** - Categories, products, variants, purchase orders
4. **POS Transactions** - Sales, payments, refunds, gift cards
5. **Certifications** - Agencies, certifications, customer certifications
6. **Rentals** - Equipment, reservations, checkout/checkin
7. **Courses & Trips** - Training courses, schedules, enrollments, dive trips
8. **Work Orders** - Service requests, repairs, maintenance
9. **E-Commerce** - Online orders, shopping carts, shipments
10. **Content Management** - Pages, blog posts, media library
11. **Marketing** - Campaigns, loyalty programs, promotions
12. **Staff Management** - Employee records, schedules, commissions
13. **Reporting & Analytics** - Custom reports, dashboards, KPIs

## 🚀 Installation

### Prerequisites

- PHP 8.2 or higher
- MySQL 8.0+ or MariaDB 10.6+
- Apache 2.4+ with mod_rewrite enabled
- Composer

### Quick Start

```bash
# Clone the repository
git clone https://github.com/wrnash1/nautilus-v6.git
cd nautilus-v6

# Install dependencies
composer install

# Configure environment
cp .env.example .env
nano .env  # Update database credentials and settings

# Create database
mysql -u root -p -e "CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Run migrations
for file in database/migrations/*.sql; do
    mysql -u root -p nautilus < "$file"
done

# Set permissions
chmod -R 755 storage public/uploads
```

### Apache Configuration

```apache
<VirtualHost *:80>
    ServerName nautilus.local
    DocumentRoot /path/to/nautilus-v6/public
    
    <Directory /path/to/nautilus-v6/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## ⚙️ Configuration

Edit `.env` file to configure:

- Database connection
- Application settings
- Google Workspace API credentials
- Payment gateway credentials
- Email configuration
- Encryption keys

## 🔐 Security Features

- **Authentication:** Session-based with optional 2FA
- **Authorization:** Role-Based Access Control (RBAC)
- **CSRF Protection:** Token-based CSRF protection on all forms
- **SQL Injection Prevention:** Prepared statements throughout
- **XSS Protection:** Input sanitization and output escaping
- **Audit Logging:** Complete audit trail of all actions
- **Password Security:** Bcrypt hashing with salt
- **Session Security:** Secure session management

## 📊 Key Modules

### Point of Sale (POS)
- Multi-tender transactions (cash, card, crypto, gift cards)
- Split payments and partial refunds
- Receipt printing and email
- Real-time inventory updates
- Tax calculation and reporting

### Customer Relationship Management (CRM)
- 360° customer view with full history
- B2C and B2B customer support
- Document management (certifications, waivers)
- Communication tracking (email, SMS, phone)
- Customer segmentation and tagging
- Loyalty points and rewards

### Inventory Management
- Product catalog with variants
- Stock tracking and alerts
- Purchase order management
- Vendor management
- Barcode/SKU support
- Multi-location inventory

### Rental Equipment
- Equipment catalog and availability
- Reservation system
- Checkout/checkin workflow
- Condition tracking and inspections
- Maintenance scheduling
- Damage/loss tracking

### Training Courses
- Course catalog and scheduling
- Student enrollment and waitlists
- Attendance tracking
- Certification issuance
- Instructor assignment
- Integration with certification agencies

## 🔌 API Documentation

RESTful API available at `/api/v1`. See [API Documentation](docs/API.md) for details.

Authentication required for all endpoints via JWT or session.

## 📝 Development

This is a project skeleton with placeholder implementations. To develop a module:

1. Implement business logic in `app/Services/`
2. Create data access methods in `app/Models/`
3. Add routes in `routes/web.php`
4. Implement controller actions in `app/Controllers/`
5. Apply appropriate middleware for authentication

## 🚢 Deployment

See [Deployment Guide](docs/DEPLOYMENT.md) for production deployment instructions.

## 📖 Documentation

- [API Documentation](docs/API.md) - RESTful API reference
- [Deployment Guide](docs/DEPLOYMENT.md) - Production deployment steps

## 🤝 Support & Contribution

This is a proprietary system developed for dive shop operations. For support or inquiries, contact Bill Nash.

## 📄 License

Copyright (c) 2025 Bill Nash. All rights reserved.

This software is proprietary and confidential. See [LICENSE](LICENSE) file for details.

## 🏆 Credits

**Developed by:** Devin AI  
**Project Owner:** Bill Nash (@wrnash1)  
**Session:** https://app.devin.ai/sessions/0a53533785e14a6f95aae83c5390ae8a

---

**Note:** This is a complete project skeleton covering all 5 phases of development. Individual modules contain placeholder implementations that should be completed during actual development phases.
