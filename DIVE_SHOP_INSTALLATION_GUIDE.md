# 🌊 Nautilus Installation Guide for Dive Shops

**Quick & Easy Installation - No Technical Knowledge Required!**

---

## 📦 What You're Getting

Nautilus is a **complete dive shop management system** that includes:

- ✅ **Point of Sale (POS)** - Sell products, process payments
- ✅ **Inventory Management** - Track stock, manage vendors
- ✅ **Course Management** - Schedule classes, track students (PADI compliant)
- ✅ **Equipment Rentals** - Manage rental equipment
- ✅ **Customer Portal** - Let customers view orders, courses, certifications
- ✅ **Online Store** - Sell products online 24/7
- ✅ **Staff Management** - Track employees, commissions
- ✅ **Reports & Analytics** - Business insights and forecasting
- ✅ **Multi-Currency** - Accept payments in multiple currencies
- ✅ **AI Features** - Smart inventory forecasting, product recommendations
- ✅ **And 140+ more features!**

---

## 🚀 Installation Steps (15 Minutes)

### Step 1: Get Web Hosting

You need a web hosting account with:
- PHP 8.1 or higher
- MySQL 8.0 or higher
- At least 1GB storage

**Recommended Hosts:**
- **SiteGround** (starts at $2.99/mo) - Best for beginners
- **Bluehost** ($2.95/mo) - Easy WordPress-style interface
- **A2 Hosting** ($2.99/mo) - Very fast

### Step 2: Upload Files

1. Download Nautilus (you received a ZIP file)
2. Extract the ZIP file on your computer
3. Upload **all files** to your hosting account:
   - Using FileZilla (free FTP program)
   - Or your hosting's file manager
   - Upload to `/public_html` or `/www` folder

### Step 3: Create Database

1. Log into your hosting control panel (cPanel)
2. Find "MySQL Databases"
3. Create a new database:
   - Database name: `nautilus`
   - Create a user with a strong password
   - Give the user "All Privileges" on the database
4. **Write down these details:**
   - Database name
   - Database username
   - Database password
   - Database host (usually `localhost`)

### Step 4: Run Installer

1. Open your web browser
2. Go to: `https://yourwebsite.com/install.php`
3. Follow the 4-step wizard:

**Step 1: Requirements Check**
- System will check if your server is compatible
- All checks should show ✓ Pass
- Click "Continue to Database Setup"

**Step 2: Database Configuration**
- Enter the database details from Step 3
- Click "Test Connection & Setup Database"
- Wait for migrations to complete (2-3 minutes)
- Click "Continue to Admin Setup"

**Step 3: Create Admin Account**
- Enter your company name (e.g., "Coral Reef Divers")
- Choose a subdomain (e.g., "coralreef")
- Enter your name and email
- Create a strong password
- Click "Complete Installation"

**Step 4: Done!**
- Installation complete! 🎉
- Click "Go to Login Page"

### Step 5: Login & Configure

1. Login with your admin email and password
2. Go to **Settings** to configure:
   - Company logo
   - Brand colors
   - Contact information
   - Tax settings
   - Payment methods

---

## 🎨 Customizing Your Storefront

### Upload Your Logo
1. Go to **Admin → Storefront Settings**
2. Click "Upload Logo"
3. Choose your logo file (PNG or JPG)
4. Click "Save"

### Set Your Colors
1. Go to **Admin → Storefront Settings**
2. Click the color pickers:
   - **Primary Color** - Main brand color
   - **Secondary Color** - Accent color
   - **Accent Color** - Buttons and highlights
3. See preview in real-time
4. Click "Save Settings"

### Add Products
1. Go to **Inventory → Products**
2. Click "Add New Product"
3. Fill in details:
   - Product name
   - Price
   - Description
   - Upload photo
   - Set stock quantity
   - Mark as "Web Visible" for online store
4. Click "Save"

### Set Up Courses
1. Go to **Courses → Add Course**
2. Enter course details:
   - Course name (e.g., "Open Water Diver")
   - Price
   - Duration
   - Description
   - Maximum students
3. Click "Save"

---

## 💳 Payment Setup

### Stripe (Credit Cards)
1. Create account at stripe.com
2. Get your API keys
3. In Nautilus: **Settings → Payment Gateways**
4. Enter Stripe keys
5. Click "Enable Stripe"

### PayPal
1. Create PayPal Business account
2. Get API credentials
3. In Nautilus: **Settings → Payment Gateways**
4. Enter PayPal credentials
5. Click "Enable PayPal"

### Square
1. Create Square account
2. Get Access Token
3. In Nautilus: **Settings → Payment Gateways**
4. Enter Square token
5. Click "Enable Square"

---

## 👥 Adding Staff

1. Go to **Staff → Add Employee**
2. Enter their information:
   - Name
   - Email
   - Create temporary password
   - Assign role (Manager, Instructor, Sales, etc.)
3. Click "Save"
4. Staff member receives email with login instructions

---

## 📱 Customer Features

### Customer Registration
- Customers can register at: `yourdomain.com/register`
- They create account with email/password
- Immediately access customer portal

### Customer Portal
Customers can:
- View order history
- Download invoices
- See course enrollments
- Track certifications
- Update profile
- Access at: `yourdomain.com/portal/dashboard`

---

## 🛍️ Online Store

Your online store is automatically available at:
- **Homepage:** `yourdomain.com/`
- **Shop:** `yourdomain.com/shop`
- **Courses:** `yourdomain.com/courses`

Customers can:
- Browse products
- Search and filter
- Add to cart
- Checkout and pay
- Track orders
- Register for courses

---

## 📊 Reports & Analytics

View business insights:
1. Go to **Reports**
2. Available reports:
   - **Sales Reports** - Daily, weekly, monthly sales
   - **Product Performance** - Top sellers, inventory
   - **Customer Reports** - New customers, retention
   - **Course Reports** - Enrollments, completion rates
   - **Financial Reports** - Revenue, profit margins

---

## 🆘 Common Issues & Solutions

### "Database Connection Failed"
**Solution:** Check your database credentials in Settings → Database

### "Permission Denied" Errors
**Solution:** Set folder permissions:
- `storage/` folder: 755
- `public/uploads/` folder: 755

### Forgot Admin Password
**Solution:** Use "Forgot Password" link on login page

### Can't Upload Images
**Solution:** Check `public/uploads/` folder is writable (755 permissions)

### Website is Slow
**Solution:**
1. Enable caching in Settings
2. Optimize images before uploading
3. Consider upgrading hosting

---

## 🔒 Security Best Practices

1. **Strong Passwords**
   - Use 12+ characters
   - Mix letters, numbers, symbols
   - Change every 90 days

2. **Keep Software Updated**
   - Check for Nautilus updates monthly
   - Update when available

3. **Regular Backups**
   - Enable automatic daily backups
   - Store backups off-site
   - Test restore process monthly

4. **SSL Certificate**
   - Get free SSL from Let's Encrypt
   - Force HTTPS for all connections
   - Hosting provider can help install

5. **Access Control**
   - Only give access to staff who need it
   - Use appropriate roles (don't make everyone admin)
   - Remove ex-employees immediately

---

## 📞 Support & Help

### Documentation
- **Full Documentation:** See `/docs` folder
- **Video Tutorials:** Coming soon
- **FAQ:** See `/docs/FAQ.md`

### Getting Help
- **Email Support:** support@nautilus.com
- **Community Forum:** community.nautilus.com
- **GitHub Issues:** github.com/nautilus/issues

### Training
- **Admin Training:** 2-hour session available
- **Staff Training:** 1-hour session available
- **Custom Training:** Available on request

---

## 🎯 Quick Start Checklist

After installation, complete these tasks:

- [ ] Upload company logo
- [ ] Set brand colors
- [ ] Add company information
- [ ] Configure payment methods
- [ ] Add 5-10 products
- [ ] Set up 2-3 courses
- [ ] Add staff members
- [ ] Test checkout process
- [ ] Test customer registration
- [ ] Review all settings
- [ ] Create first backup
- [ ] Train staff
- [ ] Launch! 🚀

---

## 💡 Tips for Success

1. **Start Simple**
   - Add a few products first
   - Test everything
   - Then add more

2. **Get Staff Input**
   - Involve staff in setup
   - Get their feedback
   - Train them thoroughly

3. **Promote Your Online Store**
   - Add link to social media
   - Email customers about it
   - Offer online-only promotions

4. **Use Reports**
   - Check reports weekly
   - Identify trends
   - Make data-driven decisions

5. **Customer Service**
   - Respond to inquiries quickly
   - Be helpful and friendly
   - Build relationships

---

## 🌟 Advanced Features

### Multi-Currency
Enable selling in multiple currencies:
1. **Settings → Currencies**
2. Enable desired currencies
3. Exchange rates update automatically
4. Customers see prices in their currency

### AI Features
Already enabled:
- **Inventory Forecasting** - Predicts what to reorder
- **Product Recommendations** - Suggests products to customers
- **Chatbot** - Answers common questions

### White-Label
Make it fully your brand:
1. **Admin → Storefront Settings**
2. Upload logo and favicon
3. Set all brand colors
4. Add custom CSS if desired
5. Configure custom domain

### Subscription Plans
Available in Enterprise plan:
- Recurring billing for memberships
- Usage-based pricing
- Automatic renewals
- Customer self-service

---

## 📈 Growing Your Business

### Marketing Features
- **Email Campaigns** - Send newsletters
- **Loyalty Program** - Reward repeat customers
- **Referral Program** - Customers refer friends
- **Discount Codes** - Create special offers
- **Gift Cards** - Sell gift certificates

### Expansion Options
- **Multiple Locations** - Manage several stores
- **Franchise Support** - Perfect for franchises
- **API Access** - Connect to other systems
- **Custom Development** - We can customize for you

---

## 🎊 You're Ready to Go!

Congratulations! Your Nautilus system is set up and ready to transform your dive shop business.

**Need help?** Contact support anytime.

**Want training?** Schedule a session with our team.

**Ready to launch?** Go make waves! 🌊

---

**Nautilus v3.0** - Enterprise Dive Shop Management System
*Built with ❤️ for the diving community*
