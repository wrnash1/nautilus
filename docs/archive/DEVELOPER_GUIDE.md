# Nautilus Developer Guide

**Version**: 2.0
**Last Updated**: 2025-10-26
**For**: Enterprise Application (Customer + Staff)

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Development Environment Setup](#development-environment-setup)
3. [Project Structure](#project-structure)
4. [Core Concepts](#core-concepts)
5. [Adding Features](#adding-features)
6. [Database](#database)
7. [Testing](#testing)
8. [Code Standards](#code-standards)
9. [Deployment](#deployment)
10. [Troubleshooting](#troubleshooting)

---

## Architecture Overview

Nautilus uses a **custom PHP MVC framework** with two separate applications sharing a common database.

### Technology Stack

- **Backend**: PHP 8.2+ (Custom MVC Framework)
- **Database**: MySQL 8.0+ / MariaDB 10.6+
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Web Server**: Apache 2.4+ with mod_rewrite
- **Dependency Management**: Composer

### Application Separation

```
┌─────────────────────┐         ┌─────────────────────┐
│  Customer App       │         │  Staff App          │
│  (Public)           │         │  (Internal)         │
├─────────────────────┤         ├─────────────────────┤
│ • E-commerce        │         │ • POS               │
│ • Product Catalog   │         │ • CRM               │
│ • Customer Portal   │         │ • Inventory         │
│ • Shopping Cart     │         │ • Reports           │
│                     │         │ • Administration    │
└──────────┬──────────┘         └──────────┬──────────┘
           │                               │
           └───────────┬───────────────────┘
                       ▼
           ┌───────────────────────┐
           │   Shared Database     │
           │   (MySQL/MariaDB)     │
           └───────────────────────┘
```

### Design Patterns

1. **MVC (Model-View-Controller)**: Separation of concerns
2. **Service Layer**: Business logic encapsulation
3. **Repository Pattern**: Data access abstraction (partially implemented)
4. **Middleware Pattern**: Request filtering and authentication
5. **Singleton Pattern**: Database connection management

---

## Development Environment Setup

### Prerequisites

```bash
# Check PHP version
php -v  # Should be 8.2+

# Check required extensions
php -m | grep -E "pdo|mysqli|json|curl|mbstring|openssl|gd"

# Check Composer
composer --version
```

### Clone and Setup

```bash
# Clone repository
cd ~/development
git clone https://github.com/yourusername/nautilus.git
cd nautilus

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Edit configuration
nano .env
```

### Database Setup

```bash
# Create database
mysql -u root -p

CREATE DATABASE nautilus_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'nautilus'@'localhost' IDENTIFIED BY 'dev_password';
GRANT ALL PRIVILEGES ON nautilus_dev.* TO 'nautilus'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php scripts/migrate.php

# (Optional) Seed demo data
php scripts/seed-demo-data.php
```

### Local Development Server

```bash
# Option 1: PHP Built-in Server
cd public
php -S localhost:8000

# Access at: http://localhost:8000

# Option 2: Apache Virtual Host (Recommended)
# See ENTERPRISE_DEPLOYMENT_GUIDE.md for Apache configuration
```

---

## Project Structure

### Directory Layout

```
nautilus/
│
├── app/
│   ├── Controllers/          # HTTP request handlers
│   │   ├── Admin/           # Admin-specific controllers
│   │   ├── POS/             # Point of Sale
│   │   ├── CRM/             # Customer management
│   │   ├── Shop/            # E-commerce
│   │   └── ...
│   │
│   ├── Core/                # Framework core
│   │   ├── Router.php       # URL routing system
│   │   ├── Database.php     # Database connection (PDO)
│   │   ├── Auth.php         # Staff authentication
│   │   ├── CustomerAuth.php # Customer authentication
│   │   ├── Controller.php   # Base controller class
│   │   ├── Cache.php        # Caching system
│   │   ├── Logger.php       # Logging system
│   │   └── Encryption.php   # Encryption utilities
│   │
│   ├── Models/              # Data models
│   │   ├── User.php         # Staff users
│   │   ├── Customer.php     # Customers
│   │   ├── Product.php      # Products
│   │   └── ...
│   │
│   ├── Services/            # Business logic layer
│   │   ├── POS/
│   │   ├── CRM/
│   │   ├── Inventory/
│   │   └── ...
│   │
│   ├── Middleware/          # Request middleware
│   │   ├── AuthMiddleware.php
│   │   ├── CustomerAuthMiddleware.php
│   │   ├── CsrfMiddleware.php
│   │   └── ...
│   │
│   ├── Views/               # View templates (PHP)
│   │   ├── layouts/         # Master layouts
│   │   ├── dashboard/       # Staff dashboard
│   │   ├── shop/            # E-commerce views
│   │   └── ...
│   │
│   ├── Languages/           # Internationalization
│   │   ├── en/
│   │   ├── es/
│   │   └── fr/
│   │
│   └── helpers.php          # Global helper functions
│
├── database/
│   └── migrations/          # SQL migration files
│
├── public/                  # Web root (Document Root)
│   ├── index.php           # Application entry point
│   ├── .htaccess           # Apache rewrite rules
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── images/
│   └── uploads/            # User-uploaded files
│
├── routes/
│   ├── web.php             # Web routes
│   └── api.php             # API routes
│
├── storage/
│   ├── logs/               # Application logs
│   ├── cache/              # Cached data
│   ├── sessions/           # Session files
│   └── backups/            # Database backups
│
├── scripts/                # Utility scripts
│   ├── migrate.php         # Database migrations
│   └── seed-demo-data.php  # Demo data seeder
│
├── docs/                   # Documentation
│
├── .env.example            # Environment template
├── .gitignore
├── composer.json
└── README.md
```

---

## Core Concepts

### 1. Request Lifecycle

```
1. User Request → public/index.php
                     ↓
2. Load Autoloader, Environment, Helpers
                     ↓
3. Initialize Router (from routes/web.php)
                     ↓
4. Match Route → Apply Middleware
                     ↓
5. Execute Controller Method
                     ↓
6. Controller Uses Service/Model
                     ↓
7. Render View or Return JSON
                     ↓
8. Send Response to User
```

### 2. Routing System

Routes are defined in `routes/web.php`:

```php
use App\Core\Router;

$router = new Router();

// Basic route
$router->get('/products', 'Inventory\ProductController@index');

// Route with parameter
$router->get('/products/{id}', 'Inventory\ProductController@show');

// Route with middleware
$router->post('/products', 'Inventory\ProductController@store',
    ['AuthMiddleware', 'CsrfMiddleware']);

// Group example (applied middleware to multiple routes)
$router->group(['middleware' => ['AuthMiddleware']], function($router) {
    $router->get('/dashboard', 'Admin\DashboardController@index');
    $router->get('/settings', 'Admin\SettingsController@index');
});
```

**Router Features:**
- Regex pattern matching
- Named parameters (`{id}`, `{slug}`)
- Middleware stacking
- HTTP method routing (GET, POST, PUT, DELETE)

### 3. Controllers

Controllers handle HTTP requests and return responses.

**Location**: `app/Controllers/`

**Example**: `app/Controllers/Inventory/ProductController.php`

```php
<?php

namespace App\Controllers\Inventory;

use App\Core\Controller;
use App\Services\Inventory\ProductService;

class ProductController extends Controller
{
    private $productService;

    public function __construct()
    {
        parent::__construct();
        $this->productService = new ProductService();
    }

    /**
     * Display product list
     */
    public function index()
    {
        $products = $this->productService->getAllProducts();

        return $this->view('products/index', [
            'products' => $products
        ]);
    }

    /**
     * Show single product
     */
    public function show($id)
    {
        $product = $this->productService->getProductById($id);

        if (!$product) {
            return $this->notFound('Product not found');
        }

        return $this->view('products/show', [
            'product' => $product
        ]);
    }

    /**
     * Store new product
     */
    public function store()
    {
        // Validate input
        $data = $this->validate($_POST, [
            'name' => 'required|min:3',
            'price' => 'required|numeric',
            'sku' => 'required|unique:products'
        ]);

        // Create product
        $product = $this->productService->createProduct($data);

        // Redirect with success message
        return $this->redirect('/store/products')
            ->with('success', 'Product created successfully');
    }
}
```

**Controller Base Class Methods:**

```php
// Render view
$this->view('view.name', ['data' => $value]);

// JSON response
$this->json(['success' => true], 200);

// Redirect
$this->redirect('/path');

// Flash message
$this->redirect('/path')->with('success', 'Message');

// Validate input
$this->validate($data, $rules);

// 404 response
$this->notFound('Message');

// 403 response
$this->forbidden('Message');
```

### 4. Services (Business Logic)

Services contain business logic and should be used by controllers.

**Location**: `app/Services/`

**Example**: `app/Services/Inventory/ProductService.php`

```php
<?php

namespace App\Services\Inventory;

use App\Core\Database;

class ProductService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get all products
     */
    public function getAllProducts()
    {
        $stmt = $this->db->query(
            "SELECT * FROM products WHERE deleted_at IS NULL ORDER BY name"
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get product by ID
     */
    public function getProductById($id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM products WHERE id = ? AND deleted_at IS NULL"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Create new product
     */
    public function createProduct(array $data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO products (name, description, price, sku, stock_quantity, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())"
        );

        $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['price'],
            $data['sku'],
            $data['stock_quantity'] ?? 0
        ]);

        return $this->getProductById($this->db->lastInsertId());
    }

    /**
     * Update product
     */
    public function updateProduct($id, array $data)
    {
        $stmt = $this->db->prepare(
            "UPDATE products
             SET name = ?, description = ?, price = ?, stock_quantity = ?, updated_at = NOW()
             WHERE id = ?"
        );

        return $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['price'],
            $data['stock_quantity'],
            $id
        ]);
    }

    /**
     * Delete product (soft delete)
     */
    public function deleteProduct($id)
    {
        $stmt = $this->db->prepare(
            "UPDATE products SET deleted_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }
}
```

### 5. Models

Models represent database tables and provide data access methods.

**Location**: `app/Models/`

**Example**: `app/Models/Product.php`

```php
<?php

namespace App\Models;

use App\Core\Database;

class Product
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Find product by ID
     */
    public function find($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get all products
     */
    public function all()
    {
        $stmt = $this->db->query("SELECT * FROM products ORDER BY name");
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Search products
     */
    public function search($query)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM products
             WHERE name LIKE ? OR sku LIKE ? OR description LIKE ?
             ORDER BY name"
        );
        $searchTerm = "%{$query}%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
```

### 6. Views

Views are PHP templates that render HTML.

**Location**: `app/Views/`

**Example**: `app/Views/products/index.php`

```php
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <div class="page-header">
        <h1>Products</h1>
        <a href="/store/products/create" class="btn btn-primary">Add Product</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <table class="table">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Name</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= htmlspecialchars($product['sku']) ?></td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= formatCurrency($product['price']) ?></td>
                    <td><?= number_format($product['stock_quantity']) ?></td>
                    <td>
                        <a href="/store/products/<?= $product['id'] ?>">View</a>
                        <a href="/store/products/<?= $product['id'] ?>/edit">Edit</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
```

**Important**: Always use `htmlspecialchars()` for output to prevent XSS attacks.

### 7. Middleware

Middleware filters requests before they reach controllers.

**Location**: `app/Middleware/`

**Example**: `app/Middleware/AuthMiddleware.php`

```php
<?php

namespace App\Middleware;

use App\Core\Auth;

class AuthMiddleware
{
    public function handle()
    {
        if (!Auth::check()) {
            // Not authenticated - redirect to login
            header('Location: /store/login');
            exit;
        }

        // Authenticated - continue to controller
        return true;
    }
}
```

**Built-in Middleware:**
- `AuthMiddleware` - Staff authentication
- `CustomerAuthMiddleware` - Customer authentication
- `CsrfMiddleware` - CSRF token validation
- `ApiAuthMiddleware` - JWT authentication for API
- `RateLimitMiddleware` - Rate limiting
- `SecurityHeadersMiddleware` - Security headers

---

## Adding Features

### Adding a New Module (Step-by-Step)

Let's add a "Wetsuits" rental module:

#### Step 1: Create Database Migration

`database/migrations/018_create_wetsuits_tables.sql`

```sql
-- Wetsuit inventory
CREATE TABLE wetsuit_inventory (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    size VARCHAR(10) NOT NULL,
    gender ENUM('male', 'female', 'unisex') NOT NULL,
    thickness VARCHAR(10) NOT NULL,
    brand VARCHAR(100),
    condition_rating INT DEFAULT 5,
    rental_price_per_day DECIMAL(10,2) NOT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Wetsuit rentals
CREATE TABLE wetsuit_rentals (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    wetsuit_id INT UNSIGNED NOT NULL,
    rental_date DATE NOT NULL,
    return_date DATE,
    daily_rate DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2),
    status ENUM('active', 'returned', 'overdue') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (wetsuit_id) REFERENCES wetsuit_inventory(id)
);
```

#### Step 2: Create Service

`app/Services/Rentals/WetsuitService.php`

```php
<?php

namespace App\Services\Rentals;

use App\Core\Database;

class WetsuitService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAvailableWetsuits()
    {
        $stmt = $this->db->query(
            "SELECT * FROM wetsuit_inventory WHERE is_available = TRUE ORDER BY size"
        );
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function rentWetsuit($customerId, $wetsuitId, $rentalDate)
    {
        // Get wetsuit price
        $wetsuit = $this->getWetsuitById($wetsuitId);

        // Create rental record
        $stmt = $this->db->prepare(
            "INSERT INTO wetsuit_rentals (customer_id, wetsuit_id, rental_date, daily_rate, status)
             VALUES (?, ?, ?, ?, 'active')"
        );
        $stmt->execute([$customerId, $wetsuitId, $rentalDate, $wetsuit['rental_price_per_day']]);

        // Mark wetsuit as unavailable
        $this->markWetsuitUnavailable($wetsuitId);

        return $this->db->lastInsertId();
    }

    public function returnWetsuit($rentalId, $returnDate)
    {
        // Get rental info
        $rental = $this->getRentalById($rentalId);

        // Calculate total
        $days = (strtotime($returnDate) - strtotime($rental['rental_date'])) / 86400;
        $total = $days * $rental['daily_rate'];

        // Update rental
        $stmt = $this->db->prepare(
            "UPDATE wetsuit_rentals
             SET return_date = ?, total_amount = ?, status = 'returned'
             WHERE id = ?"
        );
        $stmt->execute([$returnDate, $total, $rentalId]);

        // Mark wetsuit as available
        $this->markWetsuitAvailable($rental['wetsuit_id']);

        return $total;
    }

    private function getWetsuitById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM wetsuit_inventory WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function getRentalById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM wetsuit_rentals WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    private function markWetsuitUnavailable($id)
    {
        $stmt = $this->db->prepare("UPDATE wetsuit_inventory SET is_available = FALSE WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function markWetsuitAvailable($id)
    {
        $stmt = $this->db->prepare("UPDATE wetsuit_inventory SET is_available = TRUE WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
```

#### Step 3: Create Controller

`app/Controllers/Rentals/WetsuitController.php`

```php
<?php

namespace App\Controllers\Rentals;

use App\Core\Controller;
use App\Services\Rentals\WetsuitService;

class WetsuitController extends Controller
{
    private $wetsuitService;

    public function __construct()
    {
        parent::__construct();
        $this->wetsuitService = new WetsuitService();
    }

    public function index()
    {
        $wetsuits = $this->wetsuitService->getAvailableWetsuits();

        return $this->view('rentals/wetsuits/index', [
            'wetsuits' => $wetsuits
        ]);
    }

    public function rent()
    {
        $data = $this->validate($_POST, [
            'customer_id' => 'required|numeric',
            'wetsuit_id' => 'required|numeric',
            'rental_date' => 'required|date'
        ]);

        $rentalId = $this->wetsuitService->rentWetsuit(
            $data['customer_id'],
            $data['wetsuit_id'],
            $data['rental_date']
        );

        return $this->redirect('/store/rentals/wetsuits')
            ->with('success', 'Wetsuit rented successfully');
    }

    public function returnWetsuit()
    {
        $data = $this->validate($_POST, [
            'rental_id' => 'required|numeric',
            'return_date' => 'required|date'
        ]);

        $total = $this->wetsuitService->returnWetsuit(
            $data['rental_id'],
            $data['return_date']
        );

        return $this->redirect('/store/rentals/wetsuits')
            ->with('success', "Wetsuit returned. Total: $" . number_format($total, 2));
    }
}
```

#### Step 4: Create Views

`app/Views/rentals/wetsuits/index.php`

```php
<?php include __DIR__ . '/../../layouts/header.php'; ?>

<div class="container">
    <h1>Wetsuit Rentals</h1>

    <div class="row">
        <div class="col-md-8">
            <h2>Available Wetsuits</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Size</th>
                        <th>Gender</th>
                        <th>Thickness</th>
                        <th>Brand</th>
                        <th>Price/Day</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($wetsuits as $wetsuit): ?>
                        <tr>
                            <td><?= htmlspecialchars($wetsuit['size']) ?></td>
                            <td><?= htmlspecialchars($wetsuit['gender']) ?></td>
                            <td><?= htmlspecialchars($wetsuit['thickness']) ?></td>
                            <td><?= htmlspecialchars($wetsuit['brand']) ?></td>
                            <td>$<?= number_format($wetsuit['rental_price_per_day'], 2) ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary"
                                        onclick="rentWetsuit(<?= $wetsuit['id'] ?>)">
                                    Rent
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/footer.php'; ?>
```

#### Step 5: Add Routes

`routes/web.php` (in Staff app)

```php
// Wetsuit Rentals
$router->get('/store/rentals/wetsuits', 'Rentals\WetsuitController@index', ['AuthMiddleware']);
$router->post('/store/rentals/wetsuits/rent', 'Rentals\WetsuitController@rent', ['AuthMiddleware', 'CsrfMiddleware']);
$router->post('/store/rentals/wetsuits/return', 'Rentals\WetsuitController@returnWetsuit', ['AuthMiddleware', 'CsrfMiddleware']);
```

#### Step 6: Run Migration

```bash
mysql -u root -p nautilus < database/migrations/018_create_wetsuits_tables.sql
```

#### Step 7: Test

```
https://yourdomain.com/store/rentals/wetsuits
```

---

## Database

### Database Connection

Singleton pattern via `App\Core\Database`:

```php
use App\Core\Database;

$db = Database::getInstance()->getConnection();
```

### Prepared Statements (Always!)

```php
// SELECT
$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(\PDO::FETCH_ASSOC);

// INSERT
$stmt = $db->prepare(
    "INSERT INTO products (name, price) VALUES (?, ?)"
);
$stmt->execute([$name, $price]);
$newId = $db->lastInsertId();

// UPDATE
$stmt = $db->prepare(
    "UPDATE products SET name = ?, price = ? WHERE id = ?"
);
$stmt->execute([$name, $price, $id]);

// DELETE (soft delete recommended)
$stmt = $db->prepare(
    "UPDATE products SET deleted_at = NOW() WHERE id = ?"
);
$stmt->execute([$id]);
```

### Migrations

Create SQL files in `database/migrations/`:

**Naming Convention**: `###_descriptive_name.sql`

Example: `019_add_loyalty_points_to_customers.sql`

```sql
-- Add loyalty points column
ALTER TABLE customers
ADD COLUMN loyalty_points INT DEFAULT 0 AFTER email;

-- Add index for faster lookups
CREATE INDEX idx_customers_loyalty_points ON customers(loyalty_points);
```

Run migrations:

```bash
php scripts/migrate.php
```

---

## Testing

### Manual Testing Checklist

For each new feature:

- [ ] Test with valid data
- [ ] Test with invalid data (validation)
- [ ] Test authentication/authorization
- [ ] Test CSRF protection
- [ ] Test edge cases (empty, null, special characters)
- [ ] Test error handling
- [ ] Test database constraints
- [ ] Test on different browsers
- [ ] Test mobile responsiveness

### PHPUnit Tests (Future)

```bash
# Run tests
./vendor/bin/phpunit

# Run specific test
./vendor/bin/phpunit tests/Unit/ProductServiceTest.php
```

---

## Code Standards

### PHP Standards

1. **PSR-12 Coding Standard** (follow loosely)
2. **Namespace everything** under `App\`
3. **Type hinting** where possible
4. **DocBlocks** for classes and methods
5. **Meaningful names** (descriptive, not abbreviated)

### Security Best Practices

1. **Always use prepared statements** (no string concatenation in SQL)
2. **Always escape output** with `htmlspecialchars()`
3. **Validate all input** before processing
4. **Use CSRF tokens** on all state-changing forms
5. **Hash passwords** with `password_hash()` (never store plain text)
6. **Use HTTPS** in production
7. **Sanitize file uploads** (check mime type, size, extension)
8. **Log security events** (failed logins, permission denied)

### Code Example (Good Practices)

```php
<?php

namespace App\Controllers\Inventory;

use App\Core\Controller;
use App\Services\Inventory\ProductService;

/**
 * Product Controller
 * Handles product management operations
 */
class ProductController extends Controller
{
    private ProductService $productService;

    public function __construct()
    {
        parent::__construct();
        $this->productService = new ProductService();
    }

    /**
     * Create new product
     *
     * @return mixed
     */
    public function store()
    {
        // Validate input
        $data = $this->validate($_POST, [
            'name' => 'required|min:3|max:255',
            'sku' => 'required|unique:products|max:50',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'numeric|min:0'
        ]);

        try {
            // Create product
            $product = $this->productService->createProduct($data);

            // Log action
            logActivity('product_created', "Product created: {$product['name']}", $product['id']);

            // Redirect with success
            return $this->redirect('/store/products')
                ->with('success', 'Product created successfully');

        } catch (\Exception $e) {
            // Log error
            error_log("Product creation failed: " . $e->getMessage());

            // Redirect with error
            return $this->redirect('/store/products/create')
                ->with('error', 'Failed to create product')
                ->withInput();
        }
    }
}
```

---

## Deployment

### Pre-Deployment Checklist

- [ ] All tests passing
- [ ] Code reviewed
- [ ] Database migrations tested
- [ ] .env configured for production
- [ ] APP_DEBUG=false
- [ ] Composer dependencies updated
- [ ] File permissions correct
- [ ] Backups configured
- [ ] SSL certificate valid
- [ ] Error logging configured

### Deployment Process

```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader

# 3. Run migrations
php scripts/migrate.php

# 4. Clear cache
rm -rf storage/cache/*

# 5. Set permissions
chmod -R 755 storage public/uploads

# 6. Restart services
sudo systemctl restart apache2
```

See `docs/ENTERPRISE_DEPLOYMENT_GUIDE.md` for full deployment instructions.

---

## Troubleshooting

### Common Issues

**Issue**: White screen / blank page

**Solution**: Check error log, enable APP_DEBUG temporarily

```bash
tail -f /var/log/apache2/error.log
tail -f storage/logs/app.log
```

---

**Issue**: Route not found

**Solution**: Check route definition, verify mod_rewrite enabled

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

---

**Issue**: Database connection failed

**Solution**: Verify .env credentials, check MySQL is running

```bash
mysql -u username -p database_name
sudo systemctl status mysql
```

---

**Issue**: Session not persisting

**Solution**: Check storage/sessions permissions

```bash
chmod -R 775 storage/sessions
chown -R www-data:www-data storage/sessions
```

---

## Helper Functions

Available globally (defined in `app/helpers.php`):

```php
// Environment
env('KEY', 'default')

// URLs
url('/path')
redirect('/path')

// Views
view('view.name', ['data' => $value])

// JSON
jsonResponse(['data' => $value], 200)

// Security
sanitizeInput($input)
generateCsrfToken()
verifyCsrfToken($token)

// Formatting
formatCurrency($amount)
formatDate($date)

// Logging
logActivity($action, $description, $entityId)

// Authentication
currentUser()
hasPermission($permission)

// Validation
validateEmail($email)
validatePhone($phone)
```

---

## Additional Resources

- **Deployment Guide**: `docs/ENTERPRISE_DEPLOYMENT_GUIDE.md`
- **API Documentation**: `docs/API_DOCUMENTATION.md`
- **Database Schema**: `database/migrations/`

---

**Developer Guide Version**: 2.0
**Last Updated**: 2025-10-26
