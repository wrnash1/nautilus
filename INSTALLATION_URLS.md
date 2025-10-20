# Installation URLs for Nautilus

## Your Configuration

Based on your `.env` file:

```env
APP_NAME="Nautilus"
APP_URL=http://Pangolin.local
DB_DATABASE=nautilus
```

---

## Installation URL

### Start the Installation:
```
http://Pangolin.local/install
```

---

## After Installation URLs

### Admin Panel:
```
http://Pangolin.local/login
```

### Main Application:
```
http://Pangolin.local/
```

### Online Store (Customer-Facing):
```
http://Pangolin.local/shop
```

### Customer Portal:
```
http://Pangolin.local/account
```

### API Documentation:
```
http://Pangolin.local/api/docs
```

---

## Key Application Sections

After logging in, access these through the sidebar menu:

### Core Operations:
- **Dashboard**: `http://Pangolin.local/`
- **Point of Sale**: `http://Pangolin.local/pos`
- **Customers**: `http://Pangolin.local/customers`
- **Products**: `http://Pangolin.local/products`

### Staff Management:
- **Employees**: `http://Pangolin.local/staff`
- **Add Employee/Instructor**: `http://Pangolin.local/staff/create`
- **Schedules**: `http://Pangolin.local/staff/schedules`
- **Time Clock**: `http://Pangolin.local/staff/timeclock`

### Services:
- **Rentals**: `http://Pangolin.local/rentals`
- **Air Fills**: `http://Pangolin.local/air-fills`
- **Work Orders**: `http://Pangolin.local/workorders`
- **Courses**: `http://Pangolin.local/courses`
- **Dive Trips**: `http://Pangolin.local/trips`

### Waivers:
- **View Signed Waivers**: `http://Pangolin.local/waivers`
- **Sign Waiver** (customers receive link via email): `http://Pangolin.local/waivers/sign/{token}`

### Marketing:
- **Email Campaigns**: `http://Pangolin.local/marketing/campaigns`
- **Loyalty Program**: `http://Pangolin.local/marketing/loyalty`
- **Coupons**: `http://Pangolin.local/marketing/coupons`

### Reports:
- **Sales Report**: `http://Pangolin.local/reports/sales`
- **Customer Report**: `http://Pangolin.local/reports/customers`
- **Inventory Report**: `http://Pangolin.local/reports/inventory`

### Advanced Features:
- **Dive Sites**: `http://Pangolin.local/dive-sites`
- **Serial Numbers**: `http://Pangolin.local/serial-numbers`
- **Vendor Import**: `http://Pangolin.local/vendor-catalog/import`

### Integrations:
- **Wave Accounting**: `http://Pangolin.local/integrations/wave`
- **QuickBooks**: `http://Pangolin.local/integrations/quickbooks`
- **Google Workspace**: `http://Pangolin.local/integrations/google-workspace`

### Admin:
- **API Tokens**: `http://Pangolin.local/api/tokens`
- **Settings**: `http://Pangolin.local/admin/settings`
- **Users**: `http://Pangolin.local/admin/users`
- **Roles**: `http://Pangolin.local/admin/roles`

---

## Database Configuration

Already set in your `.env`:
```
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nautilus
DB_USERNAME=root
DB_PASSWORD=Frogman09!
```

✅ Database "nautilus" created and ready
✅ All configuration points to "nautilus" (not "nautilus-v6")

---

## Installation Steps

### 1. Navigate to Installer
```
http://Pangolin.local/install
```

### 2. The installer will:
- ✅ Verify database connection to "nautilus"
- ✅ Run all migration files
- ✅ Create 80+ tables
- ✅ Pre-load:
  - 10 certification agencies
  - 60+ diving certifications
  - 4 waiver templates
  - 12 famous dive sites
  - Default settings
- ✅ Create admin user account

### 3. Login
```
http://Pangolin.local/login
```

Use the admin credentials you created during installation.

### 4. Start Testing
Follow the checklist in `REINSTALL_READY.md`

---

## Waiver Email Links

When the system sends waiver emails to customers, they'll receive links like:
```
http://Pangolin.local/waivers/sign/abc123token456
```

**Important**: For production, update `APP_URL` in `.env` to your public domain:
```env
APP_URL=https://yourdomain.com
```

---

## API Access

If you want to use the API:

### 1. Create API Token:
```
http://Pangolin.local/api/tokens
```

### 2. View API Docs:
```
http://Pangolin.local/api/docs
```

### 3. Make API Calls:
```bash
curl -X GET "http://Pangolin.local/api/v1/customers" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

---

## Mobile/Remote Access

Since your server is at `Pangolin.local`, to access from other devices on your network:

1. Find your server's IP address:
   ```bash
   ip addr show
   ```

2. Access from other devices:
   ```
   http://YOUR_IP_ADDRESS/
   ```

   For example:
   ```
   http://192.168.1.100/
   ```

---

## Production Deployment

When you're ready to go live:

1. Update `.env`:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   ```

2. Configure SSL certificate (Let's Encrypt recommended)

3. Update waiver email links will automatically use the production URL

4. Configure SMTP for email:
   ```env
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-app-password
   MAIL_FROM_ADDRESS=noreply@yourdomain.com
   MAIL_FROM_NAME=Your Dive Shop
   ```

---

## Everything is "Nautilus" now!

✅ Application name: **Nautilus**
✅ Database name: **nautilus**
✅ URLs: All reference your domain (Pangolin.local)
✅ No more "v6" references in configuration

Ready to install at: **http://Pangolin.local/install**
