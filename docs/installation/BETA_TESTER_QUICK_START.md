# 🌊 Nautilus - Beta Tester Quick Start Guide

Thank you for beta testing Nautilus! This guide will get you up and running in **10 minutes**.

---

## 📋 What You're Testing

**Nautilus** is a free, open-source dive shop management system with:
- ✅ Point of Sale (POS)
- ✅ Inventory Management with AI-powered features
- ✅ Customer Management (CRM)
- ✅ Certifications tracking (PADI, SSI, NAUI)
- ✅ Course scheduling & enrollment
- ✅ Equipment rentals & maintenance
- ✅ E-commerce storefront
- ✅ **NEW: AI product scanning at POS**
- ✅ **NEW: AI inventory counting with barcode scanning**
- ✅ **NEW: AI auto-fills missing product data**

---

## 🚀 Installation (3 Steps)

### **Option 1: Shared Hosting (Easiest)**

If you have **cPanel, Plesk, or any web hosting**:

1. **Upload Files**
   - Download/extract the Nautilus folder
   - Upload the entire `nautilus` folder to your web server
   - Via cPanel File Manager, FTP, or SFTP

2. **Visit Installer**
   ```
   http://yourwebsite.com/nautilus/public/install.php
   ```

3. **Follow 5-Step Wizard**
   - Step 1: Requirements check ✅
   - Step 2: Database connection
   - Step 3: Run migrations (107 migrations - takes 2-3 minutes)
   - Step 4: Create admin account
   - Step 5: Done! 🎉

**Total Time: 5-10 minutes**

---

### **Option 2: Local Testing (Windows/Mac/Linux)**

#### Windows:
1. Install [XAMPP](https://www.apachefriends.org/) (free)
2. Copy `nautilus` folder to `C:\xampp\htdocs\`
3. Start Apache & MySQL in XAMPP Control Panel
4. Visit `http://localhost/nautilus/public/install.php`

#### Mac:
1. Install [MAMP](https://www.mamp.info/) (free)
2. Copy `nautilus` folder to `/Applications/MAMP/htdocs/`
3. Start MAMP servers
4. Visit `http://localhost/nautilus/public/install.php`

#### Linux:
```bash
# Ubuntu/Debian
sudo apt install apache2 mariadb-server php php-mysql php-curl php-gd php-mbstring
sudo systemctl start apache2 mariadb

# Copy files
sudo cp -r nautilus /var/www/html/

# Visit installer
http://localhost/nautilus/public/install.php
```

---

## 🎯 What to Test

### **Priority 1: Core Functionality** ⭐

1. **Installation**
   - Does the web installer work smoothly?
   - Any errors during migration?
   - Can you log in with admin account?

2. **Products & Inventory**
   - Add a few products (mask, fins, regulator, tank)
   - Try the **AI Enrichment** feature (auto-fills data)
   - Upload product images
   - Add shipping info (weight, dimensions)

3. **Point of Sale (POS)**
   - Go to POS
   - Add products to cart
   - Process a sale
   - Print receipt

4. **Inventory Count** (NEW!)
   - Go to Inventory → Inventory Counts
   - Create new count
   - Scan products (barcode or photo)
   - Complete count

5. **Customers**
   - Add a customer
   - View customer details
   - Track certifications

### **Priority 2: AI Features** 🤖

6. **AI Product Enrichment**
   - Create product with minimal info (just name & SKU)
   - Click "AI Enrich" button
   - Does AI suggest category, attributes, shipping class?

7. **AI POS Scanning**
   - At POS, take photo of product
   - Does AI identify it?
   - Does it add to cart automatically?

8. **AI Inventory Scanning**
   - During inventory count
   - Scan barcode or take product photo
   - Does it recognize and count?

### **Priority 3: Other Features**

9. **Certifications Module** (NEW!)
   - Add certification agency (PADI, SSI)
   - Create certification (Open Water, Advanced)
   - Assign to customer

10. **E-commerce Storefront**
    - Visit public storefront
    - Browse products
    - Add to cart
    - Test checkout (use test mode)

---

## 🐛 What to Report

### **Critical Issues** (Report Immediately):
- ❌ Installation fails
- ❌ Cannot log in
- ❌ Database errors
- ❌ POS doesn't work
- ❌ White screen/fatal errors

### **Important Issues**:
- ⚠️ Feature doesn't work as expected
- ⚠️ Confusing UI/UX
- ⚠️ Missing functionality
- ⚠️ Performance problems

### **Nice to Have**:
- 💡 Feature requests
- 💡 UI improvements
- 💡 Documentation unclear
- 💡 Suggestions

---

## 📝 How to Report Issues

**GitHub Issues** (Preferred):
```
https://github.com/yourusername/nautilus/issues
```

**Or Send Email With**:
1. **What you were doing** (steps to reproduce)
2. **What happened** (actual result)
3. **What you expected** (expected result)
4. **Screenshots** (if applicable)
5. **Your environment**:
   - Operating system (Windows/Mac/Linux)
   - PHP version (shown in installer)
   - Browser (Chrome/Firefox/Safari/Edge)

---

## 💾 Sample Data (Optional)

Want realistic test data?

After installation, run this in your browser:
```
http://yourwebsite.com/nautilus/seed-sample-data.php
```

This adds:
- ✅ 50+ dive products (masks, fins, regulators, tanks, BCDs)
- ✅ 20 customers with certifications
- ✅ Sample transactions
- ✅ Certification agencies (PADI, SSI, NAUI)
- ✅ Course schedules

---

## 🆘 Common Issues & Solutions

### **"Database connection failed"**
- Check database credentials in installer
- Ensure MySQL/MariaDB is running
- Database user has full permissions

### **"500 Internal Server Error"**
- Check file permissions (755 for folders, 644 for files)
- Enable error display in `.env` file: `APP_DEBUG=true`
- Check Apache error log

### **"Migration failed"**
- Check database user has CREATE/ALTER permissions
- Try running installer again (migrations are idempotent)
- Check MySQL version (need 5.7+ or MariaDB 10.2+)

### **"AI features not working"**
- Ensure php-ml is installed: `composer install`
- Check PHP extensions: GD, JSON, MBString required
- Check PHP version (need 8.2+)

### **"Cannot upload images"**
- Check folder permissions on `public/uploads/`
- Increase PHP upload limits in `php.ini`:
  ```ini
  upload_max_filesize = 20M
  post_max_size = 20M
  ```

---

## 📊 Test Scenarios

### **Scenario 1: New Product Entry**
1. Go to Products → Add Product
2. Enter minimal info:
   - Name: "ScubaPro MK25 EVO Regulator"
   - SKU: "SP-MK25-EVO"
   - Price: $599.00
3. Click **"AI Enrich"**
4. **Expected**: AI suggests:
   - Category: Regulators
   - Brand: ScubaPro
   - Shipping class: Standard
   - Meta description generated

### **Scenario 2: Inventory Count**
1. Go to Inventory → Inventory Counts → New Count
2. Select "Partial Count" for "Retail Floor"
3. Use barcode scanner or phone camera
4. Scan 10 products
5. Complete count
6. **Expected**: Inventory updated, differences shown

### **Scenario 3: POS with AI Scan**
1. Go to POS
2. Customer brings unknown product
3. Click camera icon
4. Take photo of product
5. **Expected**: Product identified, added to cart with price

### **Scenario 4: Customer Certification**
1. Add customer: "John Diver"
2. Go to Certifications → Add Certification
3. Create "PADI Open Water Diver"
4. Assign to John with cert number & date
5. **Expected**: Shows on customer profile

---

## 🎓 Resources

- **Full Documentation**: See `README.md`
- **Inventory Features**: See `INVENTORY_ENHANCEMENT.md`
- **Contributing**: See `CONTRIBUTING.md`
- **License**: MIT (100% free and open source!)

---

## ✅ Testing Checklist

Copy this to track your progress:

```
[ ] Installation completed successfully
[ ] Logged in as admin
[ ] Added products
[ ] Tested AI enrichment
[ ] Uploaded product images
[ ] POS transaction completed
[ ] Inventory count performed
[ ] Barcode scanning tested
[ ] AI image scanning tested
[ ] Customer added
[ ] Certification created & assigned
[ ] E-commerce storefront browsed
[ ] Checkout process tested
[ ] Reported any issues found
```

---

## 🙏 Thank You!

Your feedback is invaluable! This open-source project helps dive shops worldwide.

**Questions?** 
- GitHub Discussions
- GitHub Issues
- Email: [your-email]

**Enjoy testing!** 🌊🤿

---

**Made with ❤️ by divers, for divers**

*Nautilus is MIT licensed - 100% free and open source!*
