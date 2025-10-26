# Nautilus Enterprise Dive Shop Management System

**Version**: 2.0
**Architecture**: Dual Application (Customer + Staff)
**Framework**: Custom PHP 8.2+ MVC
**Database**: MySQL 8.0+ / MariaDB 10.6+

---

## What is Nautilus?

Nautilus is a comprehensive, enterprise-grade web application designed specifically for scuba diving businesses. It combines a public-facing e-commerce storefront with a powerful internal management system for running all aspects of a dive shop.

### Built for Dive Shop Professionals

Developed by an expert programmer with deep knowledge of the scuba diving industry, Nautilus handles:

- **Retail Operations**: Point of Sale, inventory management, e-commerce
- **Equipment Rentals**: Gear tracking, reservations, condition monitoring
- **Training Programs**: PADI/SSI course management, certification tracking
- **Dive Trips**: Trip planning, bookings, capacity management
- **Customer Management**: CRM, loyalty programs, communication tracking
- **Staff Operations**: Scheduling, time tracking, commission calculations
- **Business Intelligence**: Comprehensive reporting and analytics

---

## Architecture Overview

Nautilus is split into **two independent applications** that share a common database:

```
┌─────────────────────────────────────────────────────────────┐
│                    SHARED DATABASE LAYER                     │
│              MySQL 8.0+ (50+ tables, ACID compliant)        │
└─────────────────────────────────────────────────────────────┘
         ▲                                          ▲
         │                                          │
┌────────┴─────────────┐                ┌──────────┴────────────┐
│   CUSTOMER APP       │                │   STAFF APP           │
│   (nautilus-customer)│                │   (nautilus-staff)    │
├──────────────────────┤                ├───────────────────────┤
│ • Public Storefront  │                │ • Point of Sale       │
│ • E-commerce         │                │ • CRM                 │
│ • Product Catalog    │                │ • Inventory Mgmt      │
│ • Customer Portal    │                │ • Equipment Rentals   │
│ • Shopping Cart      │                │ • Training Courses    │
│                      │                │ • Dive Trips          │
│ Routes: /*, /shop/*  │                │ • Reports             │
│ Auth: Optional       │                │ • Administration      │
│                      │                │                       │
│                      │                │ Routes: /store/*      │
│                      │                │ Auth: REQUIRED+RBAC   │
└──────────────────────┘                └───────────────────────┘
```

**Why Two Applications?**

- **Security**: Separate authentication, different access controls
- **Performance**: Each app can scale independently
- **Maintenance**: Update public site without affecting operations
- **User Experience**: Optimized UX for customers vs. staff
- **Deployment**: Deploy to different servers if needed

---

## Quick Start

### Option 1: Quick Local Setup (15 minutes)

```bash
# 1. Split the application
cd /home/wrnash1/development/nautilus
./scripts/split-enterprise-apps.sh

# 2. Install dependencies
cd ../nautilus-customer && composer install
cd ../nautilus-staff && composer install

# 3. Create database
mysql -u root -p -e "CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 4. Configure (edit both .env files)
cd ../nautilus-customer && cp .env.example .env
cd ../nautilus-staff && cp .env.example .env

# 5. Run migrations
cd ../nautilus-customer && php scripts/migrate.php

# 6. Seed demo data (optional)
php scripts/seed-demo-data.php

# 7. Test locally
cd ../nautilus-customer/public && php -S localhost:8000 &
cd ../../nautilus-staff/public && php -S localhost:8001
```

Access:
- **Customer**: http://localhost:8000
- **Staff**: http://localhost:8001/store/login

### Option 2: Production Deployment

See **[QUICK_START_GUIDE.md](QUICK_START_GUIDE.md)** for step-by-step deployment.

---

## Documentation

### Essential Guides

| Document | Purpose | Audience |
|----------|---------|----------|
| **[QUICK_START_GUIDE.md](QUICK_START_GUIDE.md)** | Get running in 15 minutes | New users |
| **[docs/ENTERPRISE_DEPLOYMENT_GUIDE.md](docs/ENTERPRISE_DEPLOYMENT_GUIDE.md)** | Complete deployment instructions | DevOps/Admins |
| **[docs/DEVELOPER_GUIDE.md](docs/DEVELOPER_GUIDE.md)** | Development & customization | Developers |
| **[ARCHITECTURE.md](ARCHITECTURE.md)** | System architecture | Technical |
| **[APPLICATION_SPLIT_GUIDE.md](APPLICATION_SPLIT_GUIDE.md)** | Understanding the split | Technical |

### Additional Resources

- **Installation**: See `/docs/ENTERPRISE_DEPLOYMENT_GUIDE.md`
- **Development**: See `/docs/DEVELOPER_GUIDE.md`
- **Archived Docs**: See `/docs/archive/` (historical reference)

---

## Features

### Customer Application (Public)

**E-commerce Storefront**
- Product browsing with categories and search
- Shopping cart with session persistence
- Secure checkout with multiple payment options
- Responsive design (mobile-friendly)
- SEO-optimized product pages

**Customer Portal**
- Account registration and login
- Order history and tracking
- Profile management
- Saved addresses
- Loyalty points tracking

**Content Management**
- Dynamic homepage builder
- Blog system
- Custom pages
- Contact forms

### Staff Application (Internal)

**Core Operations**
- **Point of Sale**: Fast checkout, split payments, refunds
- **CRM**: 360° customer view, communication tracking
- **Inventory**: Stock management, purchase orders, vendors
- **Reports**: Sales, inventory, customer analytics (CSV export)

**Specialized Operations**
- **Equipment Rentals**: Reservations, checkout/return, condition tracking
- **Training Courses**: PADI/SSI courses, enrollment, certification
- **Dive Trips**: Trip planning, bookings, capacity management
- **Air Fills**: Tank tracking, fill records
- **Work Orders**: Equipment service and repair tracking

**Marketing & Engagement**
- **Loyalty Programs**: Points, tiers, rewards
- **Coupons**: Discount codes, usage tracking
- **Email Campaigns**: Newsletter and promotions
- **Referral Programs**: Customer referral tracking

**Administration**
- **User Management**: Staff accounts, role-based access
- **Settings**: Store configuration, tax rates, integrations
- **Storefront Config**: Theme designer, homepage builder
- **Audit Logs**: Complete activity tracking

**Integrations Ready**
- Wave Apps (Accounting)
- QuickBooks (Accounting)
- Google Workspace (Calendar, Drive)
- Stripe/Square (Payments)
- Twilio (SMS)
- PADI API (Certifications)
- UPS/FedEx (Shipping)

---

## Technology Stack

### Backend
- **Framework**: Custom PHP MVC (PSR-compatible)
- **PHP Version**: 8.2+
- **Database**: MySQL 8.0+ / MariaDB 10.6+
- **Web Server**: Apache 2.4+ with mod_rewrite
- **Dependencies**: Managed via Composer

### Core Components
- **Router**: Custom regex-based routing
- **Database**: PDO with prepared statements
- **Authentication**: Session-based with optional 2FA
- **Authorization**: Role-Based Access Control (RBAC)
- **Security**: CSRF protection, XSS prevention, SQL injection protection
- **Caching**: File-based caching system
- **Logging**: Comprehensive error and activity logging

### Frontend
- **HTML5 & CSS3**: Semantic markup
- **JavaScript**: Vanilla JS (no heavy frameworks)
- **Responsive**: Mobile-first design
- **Icons**: Font Awesome (optional)

---

## Database Schema

**17 comprehensive migrations creating 50+ tables:**

### Core Tables
- Authentication (users, roles, permissions, sessions)
- Customers (customers, addresses, tags, notes)
- Products (products, categories, vendors, inventory)
- Transactions (POS sales, payments, refunds)

### Operations Tables
- Rentals (equipment, reservations, condition checks)
- Courses (courses, schedules, enrollments, attendance)
- Trips (trips, schedules, bookings)
- Work Orders (service requests, repairs)
- Air Fills (cylinder tracking, fill records)

### E-commerce Tables
- Orders (orders, order items, shipments)
- Shopping Carts (carts, cart items)

### Marketing Tables
- Loyalty (programs, points, rewards)
- Coupons (coupons, usage tracking)
- Campaigns (email/SMS campaigns, recipients)
- Referrals (referral programs, tracking)

### Content Tables
- CMS (pages, blog posts, media library)
- Storefront (theme config, homepage sections, navigation)

### Administrative Tables
- Staff (employees, schedules, time clock, commissions)
- Settings (system configuration)
- Audit Logs (activity tracking)
- Error Logs (error tracking)

---

## Security Features

### Authentication
- Separate auth systems for customers and staff
- Bcrypt password hashing (never plain text)
- Session-based authentication
- Optional Two-Factor Authentication (2FA)
- Password reset with secure tokens

### Authorization
- Role-Based Access Control (RBAC)
- Permission-based feature access
- Route-level middleware protection
- View-level permission checks

### Data Protection
- All database queries use prepared statements
- CSRF token validation on state-changing requests
- XSS prevention via output escaping
- Input validation and sanitization
- SQL injection prevention
- Secure file upload handling

### Monitoring
- Complete audit logging
- Failed login attempt tracking
- Security event logging
- Error logging with stack traces

---

## Scripts & Automation

### Application Management

```bash
# Split monolithic app into customer + staff apps
./scripts/split-enterprise-apps.sh

# Deploy to production
sudo ./scripts/deploy-to-production.sh

# Run database migrations
php scripts/migrate.php

# Seed demo data (development only)
php scripts/seed-demo-data.php

# Backup database and files
./scripts/backup.sh
```

### Automated Backups

Set up daily automated backups:

```bash
# Edit crontab
sudo crontab -e

# Add line (runs daily at 2 AM):
0 2 * * * /home/wrnash1/development/nautilus/scripts/backup.sh >> /var/log/nautilus-backup.log 2>&1
```

---

## Development

### Prerequisites
- PHP 8.2+
- MySQL 8.0+ / MariaDB 10.6+
- Composer
- Apache 2.4+ with mod_rewrite

### Local Development Setup

```bash
# Clone repository
git clone https://github.com/yourusername/nautilus.git
cd nautilus

# Install dependencies
composer install

# Configure environment
cp .env.example .env
nano .env

# Create database
mysql -u root -p -e "CREATE DATABASE nautilus_dev;"

# Run migrations
php scripts/migrate.php

# Start dev server
cd public
php -S localhost:8000
```

### Adding Features

See **[docs/DEVELOPER_GUIDE.md](docs/DEVELOPER_GUIDE.md)** for:
- Architecture patterns
- Code examples
- Step-by-step module creation
- Best practices
- Testing guidelines

---

## Project Statistics

- **Total Lines of Code**: 50,000+
- **Controllers**: 53
- **Services**: 47
- **Views**: 200+
- **Models**: 5 core models
- **Middleware**: 8
- **Database Tables**: 50+
- **Routes**: 200+
- **Migrations**: 17
- **Development Time**: 6+ months equivalent

---

## System Requirements

### Minimum Requirements
- **Server**: Linux (Ubuntu 20.04+ / Fedora 35+)
- **PHP**: 8.2 or higher
- **Database**: MySQL 8.0+ or MariaDB 10.6+
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 2GB RAM
- **Storage**: 10GB free space

### Recommended Requirements
- **Memory**: 4GB+ RAM
- **Storage**: 50GB+ SSD
- **CPU**: 2+ cores
- **SSL**: Let's Encrypt certificate

### PHP Extensions Required
```
mysqli, pdo, pdo_mysql, json, curl, mbstring,
openssl, gd, xml, zip, bcmath, intl
```

---

## Deployment Options

### Option 1: Single Server (Recommended for small/medium shops)
- Both apps on one server
- Customer app as default DocumentRoot
- Staff app accessible via `/store` path

### Option 2: Separate Subdomains
- Customer: `yourdomain.com`
- Staff: `staff.yourdomain.com`

### Option 3: Separate Servers (Enterprise)
- Customer app on public-facing server
- Staff app on internal/VPN-only server
- Shared database server

See **[docs/ENTERPRISE_DEPLOYMENT_GUIDE.md](docs/ENTERPRISE_DEPLOYMENT_GUIDE.md)** for detailed instructions.

---

## Support & Contribution

### Getting Help
- **Documentation**: See `/docs/` directory
- **Issues**: Report bugs via GitHub Issues
- **Email**: support@yourdomain.com

### Contributing
Contributions welcome! Please:
1. Fork the repository
2. Create a feature branch
3. Follow code standards (see DEVELOPER_GUIDE.md)
4. Write tests
5. Submit a pull request

---

## License

Proprietary - All Rights Reserved

This software is licensed for use by authorized customers only.

---

## Roadmap

### Version 2.1 (Planned)
- [ ] RESTful API with JWT authentication
- [ ] Mobile app integration
- [ ] Advanced analytics dashboard
- [ ] Booking system enhancements
- [ ] Multi-location support

### Version 3.0 (Future)
- [ ] Microservices architecture
- [ ] React/Vue.js frontend
- [ ] Real-time notifications
- [ ] AI-powered recommendations
- [ ] Multi-language support

---

## Credits

**Developed by**: Expert PHP Developer specializing in dive shop operations

**Built with expertise in**:
- Scuba diving industry operations
- Retail and e-commerce systems
- Training and certification management
- Equipment rental operations
- Enterprise software architecture

---

## Getting Started

Choose your path:

**For Beginners**: Start with [QUICK_START_GUIDE.md](QUICK_START_GUIDE.md)

**For DevOps/Admins**: Read [docs/ENTERPRISE_DEPLOYMENT_GUIDE.md](docs/ENTERPRISE_DEPLOYMENT_GUIDE.md)

**For Developers**: See [docs/DEVELOPER_GUIDE.md](docs/DEVELOPER_GUIDE.md)

---

**Nautilus - Professional Dive Shop Management**
*Making wave in dive shop software* 🤿

---

**Version**: 2.0
**Last Updated**: 2025-10-26
