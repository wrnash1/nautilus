# 🌊 Nautilus Complete Guide
**Developer & Administrator Documentation**

Complete technical documentation for developers, system administrators, and power users.

---

## 📑 Table of Contents
1. [Installation](#installation)
2. [Deployment](#deployment)
3. [Architecture](#architecture)
4. [Development](#development)
5. [API Reference](#api-reference)
6. [Database](#database)
7. [Security](#security)
8. [Multi-Tenant](#multi-tenant)

---

## 🚀 Installation

### Universal Installer (All Linux Distributions)

The universal installer automatically detects your OS and configures everything:

```bash
cd /home/wrnash1/Developer/nautilus
sudo bash scripts/universal-install.sh
```

**Supported OS:**
- RHEL / Fedora / CentOS / Rocky / AlmaLinux
- Debian / Ubuntu
- Arch Linux
- openSUSE
- Alpine Linux

**What It Does:**
1. Detects OS and package manager
2. Installs dependencies (Apache, PHP, MySQL/MariaDB)
3. Configures SELinux (RHEL-based)
4. Generates SSL certificate
5. Creates Apache virtual hosts (HTTP + HTTPS)
6. Sets file permissions
7. Enables and starts services

### Manual Installation

#### 1. Install Dependencies

**RHEL/Fedora:**
```bash
sudo dnf install httpd php php-mysqlnd php-mbstring php-json php-curl php-gd php-zip mariadb-server mod_ssl
```

**Debian/Ubuntu:**
```bash
sudo apt install apache2 php php-mysql php-mbstring php-json php-curl php-gd php-zip mariadb-server
```

#### 2. Deploy Files

```bash
sudo cp -r /home/wrnash1/Developer/nautilus /var/www/html/
sudo chown -R apache:apache /var/www/html/nautilus  # or www-data on Debian
```

#### 3. Database Setup

```bash
mysql -u root -p

CREATE DATABASE nautilus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nautilus'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON nautilus.* TO 'nautilus'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 4. Web Installer

Navigate to: `https://nautilus.local/install/`

The installer will:
- Run all 110+ database migrations
- Create default admin account
- Configure initial settings

---

## 🔧 Deployment

### Fresh Installation from Scratch

#### Complete Cleanup
```bash
# Stop Apache
sudo systemctl stop httpd

# Remove existing files
sudo rm -rf /var/www/html/nautilus

# Drop database
mysql -u root -p -e "DROP DATABASE IF EXISTS nautilus;"

# Enable SELinux enforcing
sudo setenforce 1
```

#### Run Universal Installer
```bash
cd /home/wrnash1/Developer/nautilus
sudo bash scripts/universal-install.sh
```

### SELinux Configuration

**Enable Enforcing Mode:**
```bash
sudo setenforce 1
sudo sed -i 's/^SELINUX=.*/SELINUX=enforcing/' /etc/selinux/config
```

**Set File Contexts:**
```bash
sudo chcon -R -t httpd_sys_content_t /var/www/html/nautilus
sudo chcon -R -t httpd_sys_rw_content_t /var/www/html/nautilus/storage
sudo chcon -t cert_t /etc/pki/tls/certs/nautilus-selfsigned.crt
sudo chcon -t cert_t /etc/pki/tls/private/nautilus-selfsigned.key
```

**Enable Booleans:**
```bash
sudo setsebool -P httpd_can_network_connect_db on
sudo setsebool -P httpd_can_network_connect on
sudo setsebool -P httpd_can_sendmail on
sudo setsebool -P httpd_unified on
```

### HTTPS / SSL Configuration

**Generate Self-Signed Certificate:**
```bash
sudo openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout /etc/pki/tls/private/nautilus-selfsigned.key \
  -out /etc/pki/tls/certs/nautilus-selfsigned.crt \
  -subj "/C=US/ST=State/L=City/O=Nautilus/CN=nautilus.local"

sudo chmod 600 /etc/pki/tls/private/nautilus-selfsigned.key
sudo chmod 644 /etc/pki/tls/certs/nautilus-selfsigned.crt
```

**Apache SSL Virtual Host:**
```apache
<VirtualHost *:443>
    ServerName nautilus.local
    DocumentRoot /var/www/html/nautilus/public

    SSLEngine on
    SSLCertificateFile /etc/pki/tls/certs/nautilus-selfsigned.crt
    SSLCertificateKeyFile /etc/pki/tls/private/nautilus-selfsigned.key
    
    # Security Settings
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite HIGH:!aNULL:!MD5
    
    # Security Headers
    Header always set Strict-Transport-Security "max-age=31536000"
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    
    <Directory /var/www/html/nautilus/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## 🏗️ Architecture

### MVC Framework

Nautilus uses a custom PHP MVC framework:

```
Request → Router → Middleware → Controller → Service → Model → Database
                                     ↓
                                   View → Response
```

### Directory Structure

```
app/
├── Controllers/       # Handle HTTP requests
├── Services/          # Business logic
├── Models/            # Data access
├── Core/              # Framework (Router, Auth, Database)
├── Middleware/        # Request filtering
└── Views/             # Templates

database/migrations/   # 110+ SQL migration files
public/                # Web root (index.php, assets)
routes/                # Route definitions
storage/               # Logs, cache, sessions
```

### Key Components

**Router** (`app/Core/Router.php`):
- Regex-based URL matching
- Named parameters
- Middleware support
- RESTful routing

**Database** (`app/Core/Database.php`):
- PDO singleton
- Prepared statements
- Transaction support

**Auth** (`app/Core/Auth.php` & `CustomerAuth.php`):
- Session-based authentication
- Role-based access control
- Password hashing (bcrypt)
- 2FA support

---

## 💻 Development

### Creating a New Module

#### 1. Database Migration

Create `database/migrations/XXX_create_feature_tables.sql`:

```sql
CREATE TABLE my_feature (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED DEFAULT 1,
    name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### 2. Create Service

`app/Services/MyFeature/MyFeatureService.php`:

```php
<?php
namespace App\Services\MyFeature;

use App\Core\Database;

class MyFeatureService {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM my_feature");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    public function create(array $data) {
        $stmt = $this->db->prepare(
            "INSERT INTO my_feature (name) VALUES (?)"
        );
        $stmt->execute([$data['name']]);
        return $this->db->lastInsertId();
    }
}
```

#### 3. Create Controller

`app/Controllers/MyFeature/MyFeatureController.php`:

```php
<?php
namespace App\Controllers\MyFeature;

use App\Core\Controller;
use App\Services\MyFeature\MyFeatureService;

class MyFeatureController extends Controller {
    private $service;
    
    public function __construct() {
        parent::__construct();
        $this->service = new MyFeatureService();
    }
    
    public function index() {
        $items = $this->service->getAll();
        return $this->view('myfeature/index', ['items' => $items]);
    }
    
    public function store() {
        $data = $this->validate($_POST, [
            'name' => 'required|min:3'
        ]);
        
        $this->service->create($data);
        return $this->redirect('/store/myfeature')
            ->with('success', 'Created successfully');
    }
}
```

#### 4. Add Routes

In `routes/web.php`:

```php
$router->get('/myfeature', 'MyFeature\MyFeatureController@index', ['AuthMiddleware']);
$router->post('/myfeature', 'MyFeature\MyFeatureController@store', ['AuthMiddleware', 'CsrfMiddleware']);
```

#### 5. Create View

`app/Views/myfeature/index.php`:

```php
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <h1>My Feature</h1>
    
    <?php foreach ($items as $item): ?>
        <div class="item">
            <?= htmlspecialchars($item['name']) ?>
        </div>
    <?php endforeach; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
```

---

## 📡 API Reference

### Authentication

**JWT Authentication:**

```php
// Generate token
POST /api/auth/login
{
    "email": "user@example.com",
    "password": "password"
}

// Response
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "expires_in": 3600
}

// Use token
GET /api/users
Headers: Authorization: Bearer {token}
```

### Common Endpoints

```http
# Products
GET    /api/products              # List all
GET    /api/products/{id}         # Get one
POST   /api/products              # Create
PUT    /api/products/{id}         # Update
DELETE /api/products/{id}         # Delete

# Customers
GET    /api/customers             # List all
POST   /api/customers             # Create
GET    /api/customers/{id}        # Get one

# Courses
GET    /api/courses               # List courses
GET    /api/courses/{id}/schedules # Get schedules
POST   /api/enrollments           # Enroll student

# Transactions
GET    /api/transactions          # List transactions
POST   /api/transactions          # Create sale
```

### Response Format

**Success:**
```json
{
    "success": true,
    "data": { ... },
    "message": "Operation successful"
}
```

**Error:**
```json
{
    "success": false,
    "error": "Error message",
    "code": "ERROR_CODE"
}
```

---

## 💾 Database

### Schema Overview

**Core Tables:**
- `tenants` - Multi-tenant support
- `users` - Staff users
- `roles` / `permissions` / `role_permissions` - RBAC
- `customers` - Customer records
- `products` / `categories` / `vendors` - Inventory
- `transactions` / `transaction_items` - Sales
- `courses` / `course_schedules` / `course_enrollments` - Training

**110+ Migration Files:**

All migrations in `database/migrations/` are installed automatically during setup.

### Common Queries

**Check Migration Status:**
```sql
SELECT COUNT(*) as installed_migrations 
FROM migrations;
```

**List All Tables:**
```sql
SHOW TABLES;
```

**User Permissions:**
```sql
SELECT u.email, r.name as role, p.name as permission
FROM users u
JOIN user_roles ur ON u.id = ur.user_id
JOIN roles r ON ur.role_id = r.id
JOIN role_permissions rp ON r.id = rp.role_id
JOIN permissions p ON rp.permission_id = p.id
WHERE u.id = 1;
```

**Sales Report:**
```sql
SELECT 
    DATE(created_at) as date,
    COUNT(*) as transaction_count,
    SUM(total_amount) as total_sales
FROM transactions
WHERE status = 'completed'
GROUP BY DATE(created_at)
ORDER BY date DESC;
```

### Backup & Restore

**Backup:**
```bash
mysqldump -u root -p nautilus > nautilus_$(date +%Y%m%d_%H%M%S).sql
```

**Restore:**
```bash
mysql -u root -p nautilus < nautilus_20251127_120000.sql
```

**Automated Daily Backup (Cron):**
```bash
# Add to crontab: crontab -e
0 2 * * * mysqldump -u backup_user -pPASSWORD nautilus > /backups/nautilus_$(date +\%Y\%m\%d).sql 2>&1 | logger -t nautilus-backup
```

---

## 🔐 Security

### Authentication

**Password Hashing:**
```php
// Hash password (bcrypt)
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Verify
$valid = password_verify($inputPassword, $storedHash);
```

**Two-Factor Authentication:**
1. Enable in user profile
2. Scan QR code with authenticator app
3. Enter 6-digit code on login

**Session Security:**
```php
// In bootstrap/session.php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);  // HTTPS only
ini_set('session.cookie_samesite', 'Strict');
```

### Input Validation

**Always sanitize inputs:**
```php
// Helper function
$clean = sanitizeInput($_POST['field']);

// In controller
$data = $this->validate($_POST, [
    'email' => 'required|email',
    'name' => 'required|min:3|max:100',
    'price' => 'required|numeric|min:0'
]);
```

**Output escaping:**
```php
// In views
<?= htmlspecialchars($userInput) ?>
<?= htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8') ?>
```

### CSRF Protection

**In forms:**
```php
<form method="POST">
    <?= csrf_field() ?>
    <!-- form fields -->
</form>
```

**Middleware validates automatically on POST/PUT/DELETE requests.**

### File Upload Security

```php
// Validate file type
$allowed = ['jpg', 'jpeg', 'png', 'pdf'];
$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowed)) {
    throw new Exception('Invalid file type');
}

// Generate safe filename
$safeName = uniqid() . '.' . $ext;
move_uploaded_file($_FILES['file']['tmp_name'], UPLOAD_PATH . $safeName);
```

### SQL Injection Prevention

**Always use prepared statements:**
```php
// CORRECT
$stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);

// NEVER DO THIS
$query = "SELECT * FROM users WHERE email = '$email'";  // VULNERABLE!
```

---

## 🏢 Multi-Tenant Architecture

### Tenant Isolation

Each record belongs to a tenant:

```sql
CREATE TABLE products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED DEFAULT 1,
    name VARCHAR(255),
    ...
    FOREIGN KEY (tenant_id) REFERENCES tenants(id)
);
```

### Tenant Scoping

**In queries:**
```php
$tenantId = $_SESSION['tenant_id'] ?? 1;

$stmt = $db->prepare("
    SELECT * FROM products 
    WHERE tenant_id = ? AND deleted_at IS NULL
");
$stmt->execute([$tenantId]);
```

**Auto-scoping in Service:**
```php
class ProductService {
    private function getTenantId() {
        return $_SESSION['tenant_id'] ?? 1;
    }
    
    public function getAll() {
        $stmt = $this->db->prepare(
            "SELECT * FROM products WHERE tenant_id = ?"
        );
        $stmt->execute([$this->getTenantId()]);
        return $stmt->fetchAll();
    }
}
```

### Creating New Tenant

```sql
INSERT INTO tenants (name, subdomain, status) 
VALUES ('New Dive Shop', 'newshop', 'active');

-- Get tenant_id
SET @tenant_id = LAST_INSERT_ID();

-- Create admin user for tenant
INSERT INTO users (tenant_id, email, password_hash, first_name, last_name)
VALUES (@tenant_id, 'admin@newshop.com', '$2y$...', 'Admin', 'User');
```

---

## 🧪 Testing

### Manual Testing

**Test POS Transaction:**
1. Login as admin
2. Navigate to POS
3. Select customer
4. Add products
5. Complete sale
6. Verify in database:
   ```sql
   SELECT * FROM transactions ORDER BY id DESC LIMIT 1;
   SELECT * FROM transaction_items WHERE transaction_id = LAST_INSERT_ID();
   ```

**Test Course Enrollment:**
1. Create course and schedule
2. In POS, add course to cart
3. Modal should appear for schedule selection
4. Complete sale
5. Check enrollment:
   ```sql
   SELECT * FROM course_enrollments ORDER BY id DESC LIMIT 1;
   ```

### Performance Testing

**Enable Query Logging:**
```php
// In Database class
$this->pdo->setAttribute(PDO::ATTR_STATEMENT_CLASS, ['LoggedStatement']);
```

**Check Slow Queries:**
```sql
-- Enable slow query log in MySQL
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1;  -- Log queries > 1 second

-- View slow queries
SELECT * FROM mysql.slow_log;
```

---

## 🚨 Troubleshooting

### Debug Mode

**Enable in `.env`:**
```env
APP_DEBUG=true
APP_ENVIRONMENT=development
```

**View Errors:**
```bash
tail -f storage/logs/debug_auth.log
tail -f storage/logs/debug_login.log
tail -f /var/log/httpd/error_log
```

### Common Issues

**500 Error:**
- Check Apache error log
- Verify file permissions
- Check database connection
- Enable debug mode

**404 Error:**
- Check `.htaccess` exists
- Verify mod_rewrite enabled
- Check route definitions

**Database Connection Failed:**
- Verify `.env` credentials
- Check MySQL is running
- Test connection manually

**SELinux Denials:**
```bash
# Check denials
ausearch -m avc -ts recent | grep denied

# Fix contexts
restorecon -Rv /var/www/html/nautilus
```

---

## 📚 Additional Resources

- **Main README**: Quick start and basic usage
- **Installation Guide**: Detailed setup instructions  
- **API Documentation**: Complete API reference
- **Security Assessment**: Security audit and recommendations

---

## 🔄 Updates

**Check for Updates:**
```bash
cd /home/wrnash1/Developer/nautilus
git pull origin main
```

**Apply Updates:**
```bash
# Backup first!
mysqldump -u root -p nautilus > backup_before_update.sql

# Sync files
sudo bash scripts/universal-install.sh

# Run new migrations
php database/migrate.php
```

---

**Last Updated**: November 27, 2025  
**Version**: 2.0  
**Status**: ✅ Production Ready
