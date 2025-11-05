# Nautilus Production Readiness Checklist

## Pre-Deployment Checklist

### 🔒 Security

- [ ] **Change default admin password**
  - Current: `admin@nautilus.local` / `password`
  - Update at: Settings > User Management

- [ ] **Set production environment**
  - [ ] `.env` file: `APP_ENV=production`
  - [ ] `.env` file: `APP_DEBUG=false`
  - [ ] `.env` file: `APP_URL=https://yourdomain.com`

- [ ] **Generate secure keys**
  ```bash
  # Generate APP_KEY (64 hex characters)
  php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"

  # Generate JWT_SECRET (64 hex characters)
  php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
  ```

- [ ] **Database credentials**
  - [ ] Strong database password (16+ characters)
  - [ ] Unique database user (not root)
  - [ ] Database accessible only from localhost

- [ ] **Remove debug files**
  ```bash
  rm -f public/debug-*.php
  rm -f public/test*.php
  ```

- [ ] **File permissions**
  ```bash
  sudo chown -R apache:apache /var/www/html/nautilus
  sudo find /var/www/html/nautilus -type d -exec chmod 755 {} \;
  sudo find /var/www/html/nautilus -type f -exec chmod 644 {} \;
  sudo chmod -R 775 /var/www/html/nautilus/storage
  sudo chmod -R 775 /var/www/html/nautilus/public/uploads
  ```

- [ ] **SSL Certificate**
  - [ ] Valid SSL certificate installed
  - [ ] Force HTTPS redirect enabled
  - [ ] Auto-renewal configured (Let's Encrypt)

- [ ] **Hide sensitive files**
  - [ ] `.env` not accessible via web
  - [ ] `.git` directory not accessible
  - [ ] `composer.json` not accessible

### ⚙️ Configuration

- [ ] **Email Settings** (Settings > Email)
  - [ ] SMTP server configured
  - [ ] Test email sent successfully
  - [ ] From address set to your domain

- [ ] **Tax Configuration** (Settings > Tax)
  - [ ] Tax rates configured for your region
  - [ ] Default tax rate set

- [ ] **Payment Processors** (Settings > Payment)
  - [ ] Stripe API keys (live, not test)
  - [ ] Square API keys (live, not test)
  - [ ] Bitcoin wallet address (if using)

- [ ] **Store Information** (Settings > General)
  - [ ] Store name
  - [ ] Store logo uploaded
  - [ ] Store address
  - [ ] Contact information
  - [ ] Business hours

- [ ] **Rental Settings** (Settings > Rentals)
  - [ ] Rental pricing configured
  - [ ] Damage fees set
  - [ ] Late return penalties

- [ ] **Air Fill Settings** (Settings > Air Fills)
  - [ ] Air fill pricing
  - [ ] Nitrox pricing
  - [ ] Tank inspection requirements

### 🔌 Integrations

- [ ] **PADI API** (Settings > Integrations)
  - [ ] API credentials configured
  - [ ] Test connection successful

- [ ] **Wave Accounting** (if using)
  - [ ] OAuth connected
  - [ ] Test sync successful

- [ ] **QuickBooks** (if using)
  - [ ] OAuth connected
  - [ ] Test sync successful

- [ ] **Google Workspace** (if using)
  - [ ] OAuth connected
  - [ ] Calendar sync tested

- [ ] **Twilio SMS** (if using)
  - [ ] Account SID configured
  - [ ] Auth token configured
  - [ ] Test SMS sent

### 📊 Database

- [ ] **Migrations run successfully**
  ```bash
  php database/migrate.php
  ```

- [ ] **Initial data seeded**
  ```bash
  php database/seed.php
  ```

- [ ] **Database backup configured**
  - [ ] Automated daily backups
  - [ ] Backup retention policy (30 days)
  - [ ] Backup tested and verified

- [ ] **Database optimized**
  - [ ] Indexes verified
  - [ ] Query performance tested
  - [ ] Connection pool configured

### 🌐 Web Server

- [ ] **Apache Configuration**
  - [ ] Virtual host configured
  - [ ] DocumentRoot points to `public/`
  - [ ] `.htaccess` working (mod_rewrite enabled)
  - [ ] Error logs configured

- [ ] **PHP Configuration**
  - [ ] PHP 8.4+ installed
  - [ ] All required extensions installed
  - [ ] OpCache enabled
  - [ ] Memory limit: 256M minimum
  - [ ] Upload max filesize: 20M minimum
  - [ ] Post max size: 25M minimum

- [ ] **SELinux (if applicable)**
  - [ ] Storage directory contexts set
  - [ ] Upload directory contexts set
  - [ ] Network connections allowed (if needed)

- [ ] **Firewall**
  - [ ] Port 80 (HTTP) open
  - [ ] Port 443 (HTTPS) open
  - [ ] Unnecessary ports closed

### 🎨 Storefront

- [ ] **Theme configured**
  - [ ] Active theme selected
  - [ ] Colors match brand
  - [ ] Logo uploaded and displaying

- [ ] **Homepage**
  - [ ] Hero section configured
  - [ ] Featured products added
  - [ ] Featured categories added
  - [ ] Call-to-action buttons working

- [ ] **Navigation**
  - [ ] Menu items configured
  - [ ] All links tested
  - [ ] Footer information updated

### 💳 Point of Sale

- [ ] **POS tested**
  - [ ] Products load correctly
  - [ ] Customer search working
  - [ ] Add to cart functional
  - [ ] Checkout process tested
  - [ ] Receipt printing tested
  - [ ] Cash drawer integration tested

- [ ] **Payment methods enabled**
  - [ ] Cash
  - [ ] Credit/Debit card
  - [ ] Check
  - [ ] Bitcoin (if offered)

### 📦 Inventory

- [ ] **Products imported**
  - [ ] All products added
  - [ ] Categories configured
  - [ ] Product images uploaded
  - [ ] Pricing verified
  - [ ] Stock quantities set

- [ ] **Vendors configured**
  - [ ] All vendors added
  - [ ] Contact information complete
  - [ ] Vendor catalog links

- [ ] **Serial number tracking** (if applicable)
  - [ ] Serial numbers imported
  - [ ] Barcode labels printed
  - [ ] Equipment tagged

### 👥 Users & Permissions

- [ ] **User accounts created**
  - [ ] Admin account (YOU)
  - [ ] Manager accounts
  - [ ] Employee accounts
  - [ ] Instructor accounts
  - [ ] Cashier accounts

- [ ] **Roles configured**
  - [ ] Admin permissions verified
  - [ ] Manager permissions verified
  - [ ] Employee permissions verified
  - [ ] Instructor permissions verified
  - [ ] Cashier permissions verified

- [ ] **Test user access**
  - [ ] Each role tested
  - [ ] Permissions working correctly
  - [ ] No unauthorized access possible

### 📧 Notifications

- [ ] **Email templates customized**
  - [ ] Welcome email
  - [ ] Password reset
  - [ ] Order confirmation
  - [ ] Appointment reminder
  - [ ] Course enrollment confirmation

- [ ] **Test all email notifications**
  - [ ] Emails sending
  - [ ] Emails not going to spam
  - [ ] Formatting correct
  - [ ] Links working

### 🎓 Courses (if offering)

- [ ] **Course catalog populated**
  - [ ] All courses added
  - [ ] Pricing configured
  - [ ] Prerequisites set
  - [ ] Materials listed

- [ ] **Instructors configured**
  - [ ] All instructors added
  - [ ] Certifications verified
  - [ ] Availability set

- [ ] **Enrollment tested**
  - [ ] Student can enroll
  - [ ] Payment processed
  - [ ] Confirmation sent

### ✈️ Trips (if offering)

- [ ] **Trip catalog populated**
  - [ ] All trips added
  - [ ] Dates scheduled
  - [ ] Pricing configured
  - [ ] Availability limits set

- [ ] **Booking tested**
  - [ ] Customer can book
  - [ ] Payment processed
  - [ ] Confirmation sent
  - [ ] Travel packets generated

### 📈 Analytics & Reports

- [ ] **Dashboard verified**
  - [ ] Metrics displaying correctly
  - [ ] Charts rendering
  - [ ] Real-time data updating

- [ ] **Reports tested**
  - [ ] Sales reports
  - [ ] Inventory reports
  - [ ] Customer reports
  - [ ] Staff reports

### 🔍 Testing

- [ ] **Functional testing**
  - [ ] Create customer
  - [ ] Create product
  - [ ] Complete POS sale
  - [ ] Process refund
  - [ ] Open/close cash drawer
  - [ ] Generate report

- [ ] **Browser testing**
  - [ ] Chrome/Edge
  - [ ] Firefox
  - [ ] Safari
  - [ ] Mobile browsers

- [ ] **Mobile responsiveness**
  - [ ] Phone portrait
  - [ ] Phone landscape
  - [ ] Tablet

- [ ] **Performance testing**
  - [ ] Page load times < 3 seconds
  - [ ] Database queries optimized
  - [ ] Large dataset handling

### 📋 Documentation

- [ ] **Staff training**
  - [ ] POS training completed
  - [ ] Inventory management training
  - [ ] Customer management training
  - [ ] Reporting training

- [ ] **User manuals**
  - [ ] Admin manual
  - [ ] Cashier manual
  - [ ] Manager manual

- [ ] **Backup procedures documented**
  - [ ] Database backup process
  - [ ] File backup process
  - [ ] Restore process tested

### 🚀 Go-Live

- [ ] **Final verification**
  - [ ] All checklist items completed
  - [ ] Production data imported
  - [ ] DNS pointed to server
  - [ ] SSL certificate verified
  - [ ] All integrations working

- [ ] **Monitoring setup**
  - [ ] Error logging enabled
  - [ ] Uptime monitoring configured
  - [ ] Alert notifications set up

- [ ] **Launch plan**
  - [ ] Maintenance window scheduled
  - [ ] Staff notified
  - [ ] Customers notified (if applicable)
  - [ ] Rollback plan prepared

## Post-Launch Checklist

### First 24 Hours

- [ ] Monitor error logs
- [ ] Check all critical functionality
- [ ] Verify payment processing
- [ ] Test customer-facing features
- [ ] Verify email notifications

### First Week

- [ ] Daily database backups running
- [ ] No critical errors in logs
- [ ] Performance metrics within targets
- [ ] User feedback collected
- [ ] Staff comfortable with system

### First Month

- [ ] Review security logs
- [ ] Update software/dependencies
- [ ] Optimize based on usage patterns
- [ ] Collect and review analytics
- [ ] Plan feature enhancements

## Support Contacts

**Technical Support:**
- GitHub Issues: https://github.com/yourusername/nautilus/issues
- Email: support@yourdomain.com

**Hosting Support:**
- Your hosting provider contact info

**Payment Processor Support:**
- Stripe: https://support.stripe.com
- Square: https://squareup.com/help

**Integration Support:**
- PADI: your PADI rep contact
- Wave: https://support.waveapps.com

---

**Remember:** Always test in a staging environment before deploying to production!

**Version:** 2.0 Alpha
**Last Updated:** November 5, 2025
