# 🎯 Nautilus First-Time Setup Guide
**What to do after installation is complete**

---

## 🎉 Congratulations!

You've successfully installed Nautilus. Now let's get your dive shop up and running!

---

## 🔒 SECURITY FIRST (Do This Now!)

### 1. Delete the Installer
**IMPORTANT:** Remove the installer file for security.

**Using cPanel:**
1. Go to **File Manager**
2. Navigate to `public` folder
3. Find `install.php`
4. Right-click → Delete

**Using FTP:**
1. Connect to your server
2. Go to `public` folder
3. Delete `install.php`

### 2. Disable Debug Mode
1. In cPanel File Manager, go to your Nautilus root folder
2. Find and edit `.env` file
3. Change this line:
   ```
   APP_DEBUG=true
   ```
   to:
   ```
   APP_DEBUG=false
   ```
4. Save the file

### 3. Verify .env File Permissions
The `.env` file contains sensitive information. Make sure it's protected:
- File permissions should be `600` (readable only by owner)
- Never commit `.env` to version control
- Keep a backup in a secure location

---

## 👤 First Login

1. Go to your website: `https://yourwebsite.com`
2. Click **Login**
3. Use the admin email and password you created during installation
4. You should see the Dashboard!

---

## ⚙️ Essential Configuration (30 minutes)

Work through these settings to personalize your dive shop:

### 1. Company Settings (10 minutes)
**Go to:** Settings → Company Settings

Configure:
- [ ] **Company Information**
  - Legal business name
  - Contact phone number
  - Email address
  - Physical address
  - Website URL

- [ ] **Business Hours**
  - Operating days and hours
  - Time zone (verify it's correct)

- [ ] **Logo & Branding**
  - Upload your company logo
  - Set brand colors
  - Upload favicon

- [ ] **Regional Settings**
  - Currency (USD, EUR, etc.)
  - Tax rates
  - Date/time format
  - Measurement units (metric/imperial)

### 2. User Roles & Staff (15 minutes)
**Go to:** Admin → Users → Staff Management

Add your team:
- [ ] **Instructors**
  - Add each instructor
  - Assign certification levels
  - Set instructor rates

- [ ] **Store Staff**
  - Add retail/rental staff
  - Set permissions
  - Assign roles (Manager, Cashier, etc.)

- [ ] **Divemasters**
  - Add divemasters
  - Set rates and availability

**Default Roles:**
- **Admin:** Full system access
- **Manager:** Store operations
- **Instructor:** Course management
- **Staff:** Basic operations
- **Customer:** Self-service portal

### 3. Courses & Certifications (15 minutes)
**Go to:** Training → Course Management

Setup your course offerings:
- [ ] **Add Courses**
  - Open Water Diver
  - Advanced Open Water
  - Rescue Diver
  - Specialties
  - For each course, set:
    - Course fee
    - Duration (days/hours)
    - Prerequisites
    - Materials needed
    - Max students per class

- [ ] **Certification Agencies**
  - Configure PADI (or SSI, NAUI, etc.)
  - Add certification levels
  - Set up tracking

### 4. Products & Inventory (Optional - Can do later)
**Go to:** Inventory → Products

Add your retail items:
- [ ] Masks, fins, snorkels
- [ ] Wetsuits and exposure protection
- [ ] BCDs and regulators
- [ ] Dive computers and gauges
- [ ] Accessories and gear
- Set prices, quantities, and suppliers

### 5. Rental Equipment (Optional - Can do later)
**Go to:** Rentals → Equipment Management

Setup rental inventory:
- [ ] Add rental items (BCDs, regulators, wetsuits, etc.)
- [ ] Set rental rates (hourly, daily, weekly)
- [ ] Assign serial numbers
- [ ] Set maintenance schedules

---

## 💳 Payment Gateway Setup (Important!)

You'll need this to accept payments online and in-store.

### Option 1: Stripe (Recommended)
**Most popular, easy to setup**

1. Create account at: https://stripe.com
2. Get your API keys from Stripe Dashboard
3. In Nautilus:
   - Go to: Settings → Payment Gateways → Stripe
   - Enter **Publishable Key**
   - Enter **Secret Key**
   - Enter **Webhook Secret** (for automatic payment updates)
   - Test with Stripe test mode first!

### Option 2: Square
**Great if you already use Square for POS**

1. Create account at: https://squareup.com
2. Get your API credentials
3. In Nautilus:
   - Go to: Settings → Payment Gateways → Square
   - Enter **Application ID**
   - Enter **Access Token**
   - Enter **Location ID**

### Testing Payments
- Both Stripe and Square have test modes
- Use test credit cards to verify everything works
- Only switch to live mode when ready for real customers

---

## 📧 Email Configuration (Recommended)

Setup email so Nautilus can send:
- Booking confirmations
- Course reminders
- Invoices and receipts
- Password resets

**Go to:** Settings → Email Settings

### Option 1: Gmail (Easiest for testing)
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-specific-password
MAIL_ENCRYPTION=tls
```

**Note:** You need to create an "App Password" in your Gmail settings.

### Option 2: Your Hosting's Mail Server
Ask your hosting provider for SMTP details.

### Option 3: SendGrid or Mailgun (Professional)
Better for high volume. Sign up and use their SMTP credentials.

**Test Your Email:**
- Go to: Settings → Email Settings → Test Email
- Send a test message to yourself
- Verify it arrives and looks correct

---

## 📱 Optional Integrations

These can be configured now or later:

### SMS Notifications (Twilio)
- Send booking reminders via SMS
- Sign up at: https://twilio.com
- Add credentials in: Settings → SMS Settings

### PADI Integration
- Sync certifications with PADI
- Contact PADI for API access
- Configure in: Settings → Integrations → PADI

### Google Calendar
- Sync dive trips and courses
- Configure OAuth in: Settings → Integrations → Google

---

## 🎨 Customize Your Storefront (Optional)

If you're using the customer-facing store:

**Go to:** Storefront → Settings

- [ ] **Homepage**
  - Upload hero images
  - Set welcome message
  - Configure carousel slides

- [ ] **Service Boxes**
  - Highlight courses
  - Feature dive trips
  - Promote equipment sales

- [ ] **Footer**
  - Add social media links
  - Configure contact information
  - Add business hours

---

## 📊 Test Your Setup

Before going live, test these key workflows:

### Test 1: New Customer Registration
- [ ] Go to your storefront
- [ ] Create a customer account
- [ ] Verify confirmation email arrives
- [ ] Log in as customer
- [ ] Check customer dashboard works

### Test 2: Course Booking
- [ ] As a customer, browse courses
- [ ] Add a course to cart
- [ ] Go through checkout process
- [ ] Use test payment method
- [ ] Verify booking appears in admin

### Test 3: Retail Sale
- [ ] Go to: POS → Point of Sale
- [ ] Add products to cart
- [ ] Complete a test sale
- [ ] Verify inventory decremented
- [ ] Check receipt generation

### Test 4: Equipment Rental
- [ ] Go to: Rentals → New Rental
- [ ] Select equipment
- [ ] Set rental period
- [ ] Complete checkout
- [ ] Verify equipment marked as rented

---

## 🎓 Next Steps

### Week 1: Basic Operations
- [ ] Train staff on POS system
- [ ] Add all current customers
- [ ] Import existing inventory
- [ ] Schedule upcoming courses

### Week 2: Advanced Features
- [ ] Setup automated email campaigns
- [ ] Configure membership plans
- [ ] Enable customer portal
- [ ] Setup dive trip management

### Week 3: Optimization
- [ ] Review reports and analytics
- [ ] Fine-tune pricing and settings
- [ ] Enable additional features
- [ ] Train staff on advanced features

---

## 📚 Learning Resources

### Built-in Help
- **Help Icon (?):** Available throughout the system
- **Tooltips:** Hover over fields for explanations
- **Documentation:** Click "Help" in the main menu

### Video Tutorials (If Available)
Check the Help section for:
- POS system walkthrough
- Course management
- Inventory management
- Customer management

---

## 🆘 Common Questions

### "Where do I add customers?"
**CRM → Customers → Add Customer**

### "How do I schedule a course?"
**Training → Courses → Schedule New Session**

### "How do I create an invoice?"
**Customers → Select Customer → Create Invoice**

### "Where are my reports?"
**Reports → Dashboard** (or specific report type)

### "How do I backup my data?"
**Settings → Backup & Restore → Create Backup**

---

## 🔐 Security Best Practices

- [ ] **Regular Backups**
  - Enable automatic daily backups
  - Download weekly backups to external storage
  - Test restore process

- [ ] **Strong Passwords**
  - Enforce for all staff accounts
  - Change default passwords
  - Use password manager

- [ ] **User Permissions**
  - Give staff minimum necessary access
  - Review permissions quarterly
  - Disable inactive accounts

- [ ] **Software Updates**
  - Check for Nautilus updates monthly
  - Keep PHP and MySQL updated
  - Monitor security notifications

---

## 📞 Getting Help

### Documentation
- Full user manual: `docs/` folder
- API documentation: `docs/api/`
- FAQ: `docs/FAQ.md`

### Support Channels
1. **GitHub Issues:** Report bugs or request features
2. **Community Forum:** Ask questions and share tips
3. **Your Hosting Provider:** For server issues

### Training Resources
- Admin panel includes contextual help
- Check GitHub wiki for how-to guides
- Video tutorials (if available)

---

## ✅ Setup Complete Checklist

Before you go live, make sure:

- [ ] Installer deleted (`install.php`)
- [ ] Debug mode disabled (`APP_DEBUG=false`)
- [ ] Company information complete
- [ ] Logo and branding uploaded
- [ ] Staff accounts created
- [ ] Courses and pricing configured
- [ ] Payment gateway tested
- [ ] Email sending tested
- [ ] Test customer account created
- [ ] Test transactions completed
- [ ] Backups configured
- [ ] SSL certificate active (HTTPS)

---

**Ready to start taking bookings? You're all set!** 🏊‍♂️

**Pro Tip:** Start with a soft launch. Process a few real transactions with patient customers before full public launch.
