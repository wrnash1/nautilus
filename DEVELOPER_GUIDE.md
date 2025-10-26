# Nautilus V6 - Complete Developer Guide

This comprehensive guide will help you understand the Nautilus V6 codebase and add new features effectively.

## Table of Contents

1. [Quick Start](#quick-start)
2. [Architecture Overview](#architecture-overview)
3. [Core Framework](#core-framework)
4. [Adding New Features](#adding-new-features)
5. [Database Operations](#database-operations)
6. [Security Best Practices](#security-best-practices)
7. [Common Patterns](#common-patterns)
8. [Testing](#testing)
9. [Deployment](#deployment)
10. [Troubleshooting](#troubleshooting)

---

## Quick Start

### System Requirements

- **PHP:** 8.2 or higher
- **Database:** MySQL 8.0+ or MariaDB 10.6+
- **Web Server:** Apache 2.4+ with mod_rewrite
- **Composer:** Latest version
- **Extensions:** mysqli, pdo, json, curl, mbstring, openssl, gd

### Installation

1. **Clone the repository:**
   ```bash
   git clone <repository-url> nautilus-v6
   cd nautilus-v6
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Configure environment:**
   ```bash
   cp .env.example .env
   # Edit .env with your database credentials
   ```

4. **Set permissions:**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 public/uploads/
   ```

5. **Run installation wizard:**
   Visit `http://localhost/nautilus/public/install` in your browser

### Project Structure

```
nautilus-v6/
├── app/
│   ├── Controllers/           # HTTP request handlers
│   │   ├── Admin/            # Admin functionality
│   │   ├── Auth/             # Authentication
│   │   ├── CRM/              # Customer management
│   │   ├── POS/              # Point of sale
│   │   ├── Inventory/        # Product management
│   │   ├── Rentals/          # Equipment rentals
│   │   ├── Courses/          # Training courses
│   │   ├── Trips/            # Dive trips
│   │   ├── Reports/          # Analytics
│   │   └── Staff/            # Staff management
│   ├── Core/                 # Framework classes
│   │   ├── Database.php      # Database connection
│   │   ├── Router.php        # URL routing
│   │   ├── Auth.php          # Authentication
│   │   ├── Controller.php    # Base controller
│   │   └── ...
│   ├── Models/               # Data models
│   │   ├── Customer.php
│   │   ├── Product.php
│   │   ├── User.php
│   │   └── ...
│   ├── Services/             # Business logic
│   ├── Middleware/           # Request middleware
│   ├── Views/                # Template files
│   ├── Languages/            # i18n translations
│   └── helpers.php           # Global helper functions
├── database/
│   └── migrations/           # SQL schema files
├── public/
│   ├── index.php             # Application entry point
│   ├── assets/               # CSS, JS, images
│   └── uploads/              # User uploads
├── routes/
│   └── web.php               # Route definitions
├── storage/                  # Runtime files
│   ├── logs/
│   ├── cache/
│   └── sessions/
├── tests/                    # PHPUnit tests
├── .env                      # Environment configuration
└── composer.json             # PHP dependencies
```

---

## Architecture Overview

### MVC Pattern

Nautilus V6 follows the Model-View-Controller pattern:

```
User Request → Router → Middleware → Controller → Service → Model → Database
                                         ↓
                                      View ← Render
```

### Request Lifecycle

1. **Entry Point:** All requests hit `public/index.php`
2. **Router:** Matches URL to controller/method
3. **Middleware:** Executes authentication, CSRF protection, etc.
4. **Controller:** Handles request, calls services
5. **Service:** Contains business logic
6. **Model:** Interacts with database
7. **View:** Renders HTML response

### Two-App Architecture

Nautilus V6 can be deployed as two separate applications sharing one database:

- **nautilus-storefront** (External): Public e-commerce site
- **nautilus-store** (Internal): Staff management system

See [ARCHITECTURE.md](ARCHITECTURE.md) for detailed architecture diagrams.

---

## Core Framework

### Database Class

Located at `app/Core/Database.php`

**Usage:**

```php
use App\Core\Database;

// Get database instance
$db = Database::getInstance();

// Execute query
$result = $db->query("SELECT * FROM products WHERE id = ?", [$id]);

// Fetch one row
$product = $db->fetchOne("SELECT * FROM products WHERE id = ?", [$id]);

// Fetch all rows
$products = $db->fetchAll("SELECT * FROM products WHERE active = ?", [1]);

// Insert and get ID
$db->query("INSERT INTO products (name, price) VALUES (?, ?)", [$name, $price]);
$lastId = $db->lastInsertId();

// Transactions
$db->beginTransaction();
try {
    $db->query("UPDATE products SET stock = stock - 1 WHERE id = ?", [$id]);
    $db->query("INSERT INTO transaction_items (...) VALUES (...)");
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}
```

### Router Class

Located at `app/Core/Router.php`

**Defining Routes:**

Routes are defined in `routes/web.php`:

```php
use App\Core\Router;

$router = new Router();

// Basic routes
$router->get('/', 'HomeController@index');
$router->post('/contact', 'HomeController@contact');

// Route parameters
$router->get('/products/{id}', 'ProductController@show');

// Route groups with middleware
$router->group(['prefix' => '/store', 'middleware' => 'auth'], function($router) {
    $router->get('/dashboard', 'DashboardController@index');
    $router->get('/customers', 'CRM\CustomerController@index');
    $router->post('/customers', 'CRM\CustomerController@store');
});

// Multiple middleware
$router->get('/admin', 'AdminController@index', ['auth', 'role:admin']);
```

**Route Parameters:**

Parameters are extracted from the URL and passed to the controller method:

```php
// Route definition
$router->get('/products/{id}/edit', 'ProductController@edit');

// Controller method
public function edit($id) {
    $product = Product::find($id);
    // ...
}
```

### Auth Class

Located at `app/Core/Auth.php`

**Usage:**

```php
use App\Core\Auth;

// Check if user is logged in
if (Auth::check()) {
    // User is authenticated
}

// Get current user
$user = Auth::user();
echo $user->name;
echo $user->email;

// Login
Auth::login($user);

// Logout
Auth::logout();

// Check permissions
if (Auth::hasPermission('manage_inventory')) {
    // User has permission
}

// Check role
if (Auth::hasRole('manager')) {
    // User has role
}
```

### Controller Base Class

Located at `app/Core/Controller.php`

**Creating Controllers:**

```php
namespace App\Controllers\Inventory;

use App\Core\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();

        return $this->view('products.index', [
            'products' => $products,
            'title' => 'Products'
        ]);
    }

    public function store()
    {
        // Validate input
        $this->validate($_POST, [
            'name' => 'required|min:3',
            'price' => 'required|numeric',
            'sku' => 'required|unique:products'
        ]);

        // Create product
        $product = Product::create($_POST);

        // Redirect with flash message
        return $this->redirect('/store/products')
            ->with('success', 'Product created successfully!');
    }

    public function json($data)
    {
        return $this->json($data);
    }
}
```

**Available Methods:**

- `view($view, $data)` - Render a view
- `redirect($url)` - Redirect to URL
- `json($data)` - Return JSON response
- `validate($data, $rules)` - Validate input
- `with($key, $value)` - Flash message

---

## Adding New Features

### Step-by-Step Process

Let's add a complete feature: **Equipment Maintenance Tracking**

#### Step 1: Create Database Migration

Create `database/migrations/018_create_maintenance_tables.sql`:

```sql
-- Equipment Maintenance Tracking
CREATE TABLE IF NOT EXISTS maintenance_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    schedule_type ENUM('daily', 'weekly', 'monthly', 'yearly', 'hours_based') NOT NULL,
    interval_value INT DEFAULT NULL COMMENT 'For hours_based schedules',
    last_maintenance_date DATETIME DEFAULT NULL,
    next_maintenance_date DATETIME DEFAULT NULL,
    assigned_to INT DEFAULT NULL COMMENT 'User ID',
    notes TEXT,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES rental_equipment(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_equipment_maintenance (equipment_id),
    INDEX idx_next_maintenance (next_maintenance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS maintenance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    equipment_id INT NOT NULL,
    performed_by INT NOT NULL COMMENT 'User ID',
    performed_at DATETIME NOT NULL,
    maintenance_type VARCHAR(100) NOT NULL,
    description TEXT,
    parts_replaced TEXT,
    hours_spent DECIMAL(5,2) DEFAULT 0,
    cost DECIMAL(10,2) DEFAULT 0,
    status ENUM('completed', 'incomplete', 'needs_attention') DEFAULT 'completed',
    next_action_required TEXT,
    attachments JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_id) REFERENCES maintenance_schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES rental_equipment(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_equipment_logs (equipment_id),
    INDEX idx_performed_at (performed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add maintenance hours to rental equipment
ALTER TABLE rental_equipment
ADD COLUMN usage_hours DECIMAL(10,2) DEFAULT 0 COMMENT 'Total usage hours for maintenance tracking',
ADD COLUMN last_hours_update DATETIME DEFAULT NULL;
```

#### Step 2: Create Model

Create `app/Models/MaintenanceSchedule.php`:

```php
<?php

namespace App\Models;

use App\Core\Database;

class MaintenanceSchedule
{
    private static $db;

    public static function init()
    {
        self::$db = Database::getInstance();
    }

    /**
     * Get all maintenance schedules
     */
    public static function all()
    {
        $query = "SELECT ms.*, re.name as equipment_name, re.serial_number,
                         u.name as assigned_to_name
                  FROM maintenance_schedules ms
                  LEFT JOIN rental_equipment re ON ms.equipment_id = re.id
                  LEFT JOIN users u ON ms.assigned_to = u.id
                  WHERE ms.active = 1
                  ORDER BY ms.next_maintenance_date ASC";

        return self::$db->fetchAll($query);
    }

    /**
     * Find schedule by ID
     */
    public static function find($id)
    {
        $query = "SELECT ms.*, re.name as equipment_name
                  FROM maintenance_schedules ms
                  LEFT JOIN rental_equipment re ON ms.equipment_id = re.id
                  WHERE ms.id = ?";

        return self::$db->fetchOne($query, [$id]);
    }

    /**
     * Get schedules due for maintenance
     */
    public static function getDue($days = 7)
    {
        $query = "SELECT ms.*, re.name as equipment_name, re.serial_number
                  FROM maintenance_schedules ms
                  JOIN rental_equipment re ON ms.equipment_id = re.id
                  WHERE ms.active = 1
                  AND ms.next_maintenance_date <= DATE_ADD(NOW(), INTERVAL ? DAY)
                  ORDER BY ms.next_maintenance_date ASC";

        return self::$db->fetchAll($query, [$days]);
    }

    /**
     * Create new schedule
     */
    public static function create($data)
    {
        $query = "INSERT INTO maintenance_schedules
                  (equipment_id, schedule_type, interval_value, next_maintenance_date,
                   assigned_to, notes, active)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";

        self::$db->query($query, [
            $data['equipment_id'],
            $data['schedule_type'],
            $data['interval_value'] ?? null,
            $data['next_maintenance_date'],
            $data['assigned_to'] ?? null,
            $data['notes'] ?? null,
            $data['active'] ?? 1
        ]);

        return self::$db->lastInsertId();
    }

    /**
     * Update schedule
     */
    public static function update($id, $data)
    {
        $query = "UPDATE maintenance_schedules
                  SET equipment_id = ?, schedule_type = ?, interval_value = ?,
                      next_maintenance_date = ?, assigned_to = ?, notes = ?, active = ?
                  WHERE id = ?";

        return self::$db->query($query, [
            $data['equipment_id'],
            $data['schedule_type'],
            $data['interval_value'] ?? null,
            $data['next_maintenance_date'],
            $data['assigned_to'] ?? null,
            $data['notes'] ?? null,
            $data['active'] ?? 1,
            $id
        ]);
    }

    /**
     * Delete schedule
     */
    public static function delete($id)
    {
        $query = "DELETE FROM maintenance_schedules WHERE id = ?";
        return self::$db->query($query, [$id]);
    }
}

MaintenanceSchedule::init();
```

Create `app/Models/MaintenanceLog.php`:

```php
<?php

namespace App\Models;

use App\Core\Database;

class MaintenanceLog
{
    private static $db;

    public static function init()
    {
        self::$db = Database::getInstance();
    }

    /**
     * Get all logs for equipment
     */
    public static function getByEquipment($equipmentId)
    {
        $query = "SELECT ml.*, u.name as performed_by_name
                  FROM maintenance_logs ml
                  LEFT JOIN users u ON ml.performed_by = u.id
                  WHERE ml.equipment_id = ?
                  ORDER BY ml.performed_at DESC";

        return self::$db->fetchAll($query, [$equipmentId]);
    }

    /**
     * Create maintenance log
     */
    public static function create($data)
    {
        $query = "INSERT INTO maintenance_logs
                  (schedule_id, equipment_id, performed_by, performed_at,
                   maintenance_type, description, parts_replaced, hours_spent,
                   cost, status, next_action_required)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        self::$db->query($query, [
            $data['schedule_id'],
            $data['equipment_id'],
            $data['performed_by'],
            $data['performed_at'],
            $data['maintenance_type'],
            $data['description'] ?? null,
            $data['parts_replaced'] ?? null,
            $data['hours_spent'] ?? 0,
            $data['cost'] ?? 0,
            $data['status'] ?? 'completed',
            $data['next_action_required'] ?? null
        ]);

        $logId = self::$db->lastInsertId();

        // Update schedule's last maintenance date
        $updateQuery = "UPDATE maintenance_schedules
                        SET last_maintenance_date = ?
                        WHERE id = ?";
        self::$db->query($updateQuery, [$data['performed_at'], $data['schedule_id']]);

        return $logId;
    }

    /**
     * Get maintenance cost summary
     */
    public static function getCostSummary($startDate, $endDate)
    {
        $query = "SELECT
                    SUM(cost) as total_cost,
                    SUM(hours_spent) as total_hours,
                    COUNT(*) as total_maintenance,
                    equipment_id,
                    re.name as equipment_name
                  FROM maintenance_logs ml
                  LEFT JOIN rental_equipment re ON ml.equipment_id = re.id
                  WHERE ml.performed_at BETWEEN ? AND ?
                  GROUP BY ml.equipment_id
                  ORDER BY total_cost DESC";

        return self::$db->fetchAll($query, [$startDate, $endDate]);
    }
}

MaintenanceLog::init();
```

#### Step 3: Create Service Layer

Create `app/Services/Maintenance/MaintenanceService.php`:

```php
<?php

namespace App\Services\Maintenance;

use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceLog;
use App\Core\Database;

class MaintenanceService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Schedule maintenance for equipment
     */
    public function scheduleMaintenance($data)
    {
        // Validate data
        $this->validateScheduleData($data);

        // Calculate next maintenance date
        $nextDate = $this->calculateNextMaintenanceDate(
            $data['schedule_type'],
            $data['interval_value'] ?? null
        );

        $data['next_maintenance_date'] = $nextDate;

        // Create schedule
        return MaintenanceSchedule::create($data);
    }

    /**
     * Log maintenance performed
     */
    public function logMaintenance($scheduleId, $data)
    {
        $this->db->beginTransaction();

        try {
            // Get schedule
            $schedule = MaintenanceSchedule::find($scheduleId);
            if (!$schedule) {
                throw new \Exception('Maintenance schedule not found');
            }

            $data['schedule_id'] = $scheduleId;
            $data['equipment_id'] = $schedule->equipment_id;

            // Create log
            $logId = MaintenanceLog::create($data);

            // Calculate and update next maintenance date
            $nextDate = $this->calculateNextMaintenanceDate(
                $schedule->schedule_type,
                $schedule->interval_value,
                $data['performed_at']
            );

            MaintenanceSchedule::update($scheduleId, [
                'equipment_id' => $schedule->equipment_id,
                'schedule_type' => $schedule->schedule_type,
                'interval_value' => $schedule->interval_value,
                'next_maintenance_date' => $nextDate,
                'assigned_to' => $schedule->assigned_to,
                'notes' => $schedule->notes,
                'active' => $schedule->active
            ]);

            $this->db->commit();

            return $logId;

        } catch (\Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Get upcoming maintenance
     */
    public function getUpcomingMaintenance($days = 7)
    {
        return MaintenanceSchedule::getDue($days);
    }

    /**
     * Get overdue maintenance
     */
    public function getOverdueMaintenance()
    {
        $query = "SELECT ms.*, re.name as equipment_name, re.serial_number
                  FROM maintenance_schedules ms
                  JOIN rental_equipment re ON ms.equipment_id = re.id
                  WHERE ms.active = 1
                  AND ms.next_maintenance_date < NOW()
                  ORDER BY ms.next_maintenance_date ASC";

        return $this->db->fetchAll($query);
    }

    /**
     * Calculate next maintenance date
     */
    private function calculateNextMaintenanceDate($scheduleType, $intervalValue = null, $fromDate = null)
    {
        $baseDate = $fromDate ?: date('Y-m-d H:i:s');

        switch ($scheduleType) {
            case 'daily':
                return date('Y-m-d H:i:s', strtotime($baseDate . ' +1 day'));
            case 'weekly':
                return date('Y-m-d H:i:s', strtotime($baseDate . ' +1 week'));
            case 'monthly':
                return date('Y-m-d H:i:s', strtotime($baseDate . ' +1 month'));
            case 'yearly':
                return date('Y-m-d H:i:s', strtotime($baseDate . ' +1 year'));
            case 'hours_based':
                // For hours-based, we don't calculate a date
                return null;
            default:
                return date('Y-m-d H:i:s', strtotime($baseDate . ' +1 month'));
        }
    }

    /**
     * Validate schedule data
     */
    private function validateScheduleData($data)
    {
        if (empty($data['equipment_id'])) {
            throw new \Exception('Equipment ID is required');
        }

        if (empty($data['schedule_type'])) {
            throw new \Exception('Schedule type is required');
        }

        $validTypes = ['daily', 'weekly', 'monthly', 'yearly', 'hours_based'];
        if (!in_array($data['schedule_type'], $validTypes)) {
            throw new \Exception('Invalid schedule type');
        }

        if ($data['schedule_type'] === 'hours_based' && empty($data['interval_value'])) {
            throw new \Exception('Interval value required for hours-based schedules');
        }
    }
}
```

#### Step 4: Create Controller

Create `app/Controllers/Maintenance/MaintenanceController.php`:

```php
<?php

namespace App\Controllers\Maintenance;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\MaintenanceSchedule;
use App\Models\MaintenanceLog;
use App\Services\Maintenance\MaintenanceService;

class MaintenanceController extends Controller
{
    private $maintenanceService;

    public function __construct()
    {
        $this->maintenanceService = new MaintenanceService();
    }

    /**
     * Display maintenance dashboard
     */
    public function index()
    {
        // Get upcoming and overdue maintenance
        $upcoming = $this->maintenanceService->getUpcomingMaintenance(7);
        $overdue = $this->maintenanceService->getOverdueMaintenance();

        return $this->view('maintenance.index', [
            'title' => 'Equipment Maintenance',
            'upcoming' => $upcoming,
            'overdue' => $overdue
        ]);
    }

    /**
     * Display all schedules
     */
    public function schedules()
    {
        $schedules = MaintenanceSchedule::all();

        return $this->view('maintenance.schedules', [
            'title' => 'Maintenance Schedules',
            'schedules' => $schedules
        ]);
    }

    /**
     * Show create schedule form
     */
    public function createSchedule()
    {
        // Get equipment list
        $db = \App\Core\Database::getInstance();
        $equipment = $db->fetchAll("SELECT id, name, serial_number FROM rental_equipment WHERE active = 1");
        $users = $db->fetchAll("SELECT id, name FROM users WHERE active = 1");

        return $this->view('maintenance.create_schedule', [
            'title' => 'Create Maintenance Schedule',
            'equipment' => $equipment,
            'users' => $users
        ]);
    }

    /**
     * Store new schedule
     */
    public function storeSchedule()
    {
        try {
            $scheduleId = $this->maintenanceService->scheduleMaintenance($_POST);

            // Log activity
            logActivity('maintenance_schedule_created', 'Created maintenance schedule ID: ' . $scheduleId);

            return $this->redirect('/store/maintenance/schedules')
                ->with('success', 'Maintenance schedule created successfully!');

        } catch (\Exception $e) {
            return $this->redirect('/store/maintenance/schedules/create')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show log maintenance form
     */
    public function logForm($scheduleId)
    {
        $schedule = MaintenanceSchedule::find($scheduleId);

        if (!$schedule) {
            return $this->redirect('/store/maintenance')
                ->with('error', 'Schedule not found');
        }

        return $this->view('maintenance.log_form', [
            'title' => 'Log Maintenance',
            'schedule' => $schedule
        ]);
    }

    /**
     * Store maintenance log
     */
    public function storeLog($scheduleId)
    {
        try {
            $_POST['performed_by'] = Auth::user()->id;
            $_POST['performed_at'] = date('Y-m-d H:i:s');

            $logId = $this->maintenanceService->logMaintenance($scheduleId, $_POST);

            // Log activity
            logActivity('maintenance_logged', 'Logged maintenance for schedule ID: ' . $scheduleId);

            return $this->redirect('/store/maintenance')
                ->with('success', 'Maintenance logged successfully!');

        } catch (\Exception $e) {
            return $this->redirect('/store/maintenance/log/' . $scheduleId)
                ->with('error', $e->getMessage());
        }
    }

    /**
     * View equipment maintenance history
     */
    public function history($equipmentId)
    {
        $logs = MaintenanceLog::getByEquipment($equipmentId);

        $db = \App\Core\Database::getInstance();
        $equipment = $db->fetchOne("SELECT * FROM rental_equipment WHERE id = ?", [$equipmentId]);

        return $this->view('maintenance.history', [
            'title' => 'Maintenance History',
            'equipment' => $equipment,
            'logs' => $logs
        ]);
    }

    /**
     * Maintenance cost report
     */
    public function costReport()
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');

        $summary = MaintenanceLog::getCostSummary($startDate, $endDate);

        return $this->view('maintenance.cost_report', [
            'title' => 'Maintenance Cost Report',
            'summary' => $summary,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
    }
}
```

#### Step 5: Add Routes

Add to `routes/web.php`:

```php
// Maintenance routes (protected)
$router->group(['prefix' => '/store/maintenance', 'middleware' => 'auth'], function($router) {
    // Dashboard
    $router->get('', 'Maintenance\MaintenanceController@index');

    // Schedules
    $router->get('/schedules', 'Maintenance\MaintenanceController@schedules');
    $router->get('/schedules/create', 'Maintenance\MaintenanceController@createSchedule');
    $router->post('/schedules', 'Maintenance\MaintenanceController@storeSchedule');

    // Logging
    $router->get('/log/{id}', 'Maintenance\MaintenanceController@logForm');
    $router->post('/log/{id}', 'Maintenance\MaintenanceController@storeLog');

    // History
    $router->get('/history/{id}', 'Maintenance\MaintenanceController@history');

    // Reports
    $router->get('/reports/costs', 'Maintenance\MaintenanceController@costReport');
});
```

#### Step 6: Create Views

Create `app/Views/maintenance/index.php`:

```php
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><?= $title ?></h1>
        <a href="/store/maintenance/schedules/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Schedule
        </a>
    </div>

    <?php if (!empty($overdue)): ?>
    <div class="alert alert-danger">
        <h4><i class="fas fa-exclamation-triangle"></i> Overdue Maintenance</h4>
        <p>You have <?= count($overdue) ?> equipment items with overdue maintenance!</p>
    </div>

    <div class="card mb-4">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">Overdue Maintenance</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Equipment</th>
                        <th>Serial Number</th>
                        <th>Schedule Type</th>
                        <th>Due Date</th>
                        <th>Days Overdue</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($overdue as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item->equipment_name) ?></td>
                        <td><?= htmlspecialchars($item->serial_number) ?></td>
                        <td><span class="badge bg-secondary"><?= ucfirst($item->schedule_type) ?></span></td>
                        <td><?= date('M d, Y', strtotime($item->next_maintenance_date)) ?></td>
                        <td>
                            <?php
                            $daysOverdue = floor((time() - strtotime($item->next_maintenance_date)) / 86400);
                            echo '<span class="badge bg-danger">' . $daysOverdue . ' days</span>';
                            ?>
                        </td>
                        <td>
                            <a href="/store/maintenance/log/<?= $item->id ?>" class="btn btn-sm btn-warning">
                                <i class="fas fa-wrench"></i> Log Maintenance
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($upcoming)): ?>
    <div class="card">
        <div class="card-header bg-warning">
            <h5 class="mb-0">Upcoming Maintenance (Next 7 Days)</h5>
        </div>
        <div class="card-body">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Equipment</th>
                        <th>Serial Number</th>
                        <th>Schedule Type</th>
                        <th>Due Date</th>
                        <th>Assigned To</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($upcoming as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item->equipment_name) ?></td>
                        <td><?= htmlspecialchars($item->serial_number) ?></td>
                        <td><span class="badge bg-info"><?= ucfirst($item->schedule_type) ?></span></td>
                        <td><?= date('M d, Y', strtotime($item->next_maintenance_date)) ?></td>
                        <td><?= htmlspecialchars($item->assigned_to_name ?: 'Unassigned') ?></td>
                        <td>
                            <a href="/store/maintenance/log/<?= $item->id ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-wrench"></i> Log Maintenance
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i> No upcoming maintenance scheduled in the next 7 days!
    </div>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
```

Create `app/Views/maintenance/create_schedule.php`:

```php
<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="container">
    <h1><?= $title ?></h1>

    <div class="card">
        <div class="card-body">
            <form action="/store/maintenance/schedules" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="mb-3">
                    <label for="equipment_id" class="form-label">Equipment *</label>
                    <select name="equipment_id" id="equipment_id" class="form-select" required>
                        <option value="">Select Equipment</option>
                        <?php foreach ($equipment as $item): ?>
                        <option value="<?= $item->id ?>">
                            <?= htmlspecialchars($item->name) ?> (SN: <?= htmlspecialchars($item->serial_number) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="schedule_type" class="form-label">Schedule Type *</label>
                    <select name="schedule_type" id="schedule_type" class="form-select" required>
                        <option value="daily">Daily</option>
                        <option value="weekly">Weekly</option>
                        <option value="monthly" selected>Monthly</option>
                        <option value="yearly">Yearly</option>
                        <option value="hours_based">Hours Based</option>
                    </select>
                </div>

                <div class="mb-3" id="interval_field" style="display: none;">
                    <label for="interval_value" class="form-label">Hours Between Maintenance</label>
                    <input type="number" name="interval_value" id="interval_value" class="form-control" min="1">
                    <small class="text-muted">For hours-based schedules only</small>
                </div>

                <div class="mb-3">
                    <label for="assigned_to" class="form-label">Assign To</label>
                    <select name="assigned_to" id="assigned_to" class="form-select">
                        <option value="">Unassigned</option>
                        <?php foreach ($users as $user): ?>
                        <option value="<?= $user->id ?>"><?= htmlspecialchars($user->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea name="notes" id="notes" class="form-control" rows="4"></textarea>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="/store/maintenance/schedules" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Schedule
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('schedule_type').addEventListener('change', function() {
    const intervalField = document.getElementById('interval_field');
    if (this.value === 'hours_based') {
        intervalField.style.display = 'block';
        document.getElementById('interval_value').required = true;
    } else {
        intervalField.style.display = 'none';
        document.getElementById('interval_value').required = false;
    }
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
```

#### Step 7: Run Migration

```bash
# Import the migration
mysql -u root -p nautilus < database/migrations/018_create_maintenance_tables.sql
```

#### Step 8: Test the Feature

1. Visit `/store/maintenance` - View maintenance dashboard
2. Click "New Schedule" - Create a maintenance schedule
3. View upcoming/overdue maintenance
4. Log maintenance when performed
5. View maintenance history for equipment
6. Run cost reports

---

## Database Operations

### Using the Database Class

**Basic Queries:**

```php
use App\Core\Database;

$db = Database::getInstance();

// SELECT with parameters
$products = $db->fetchAll(
    "SELECT * FROM products WHERE category_id = ? AND active = ?",
    [$categoryId, 1]
);

// INSERT
$db->query(
    "INSERT INTO products (name, sku, price) VALUES (?, ?, ?)",
    [$name, $sku, $price]
);
$newId = $db->lastInsertId();

// UPDATE
$db->query(
    "UPDATE products SET price = ? WHERE id = ?",
    [$newPrice, $productId]
);

// DELETE
$db->query(
    "DELETE FROM products WHERE id = ?",
    [$productId]
);
```

**Transactions:**

```php
$db->beginTransaction();

try {
    // Multiple operations
    $db->query("UPDATE inventory SET stock = stock - ? WHERE product_id = ?", [1, $productId]);
    $db->query("INSERT INTO transactions (...) VALUES (...)", [...]);
    $db->query("INSERT INTO transaction_items (...) VALUES (...)", [...]);

    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}
```

**Complex Queries with JOINs:**

```php
$results = $db->fetchAll("
    SELECT
        p.id,
        p.name,
        p.price,
        c.name as category_name,
        v.name as vendor_name,
        i.quantity as stock
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN vendors v ON p.vendor_id = v.id
    LEFT JOIN inventory_stock i ON p.id = i.product_id
    WHERE p.active = ?
    ORDER BY p.name ASC
", [1]);
```

### Creating Models

**Model Template:**

```php
<?php

namespace App\Models;

use App\Core\Database;

class YourModel
{
    private static $db;

    public static function init()
    {
        self::$db = Database::getInstance();
    }

    // CRUD Operations

    public static function all()
    {
        return self::$db->fetchAll("SELECT * FROM your_table ORDER BY created_at DESC");
    }

    public static function find($id)
    {
        return self::$db->fetchOne("SELECT * FROM your_table WHERE id = ?", [$id]);
    }

    public static function create($data)
    {
        $query = "INSERT INTO your_table (field1, field2, field3) VALUES (?, ?, ?)";
        self::$db->query($query, [$data['field1'], $data['field2'], $data['field3']]);
        return self::$db->lastInsertId();
    }

    public static function update($id, $data)
    {
        $query = "UPDATE your_table SET field1 = ?, field2 = ?, field3 = ? WHERE id = ?";
        return self::$db->query($query, [$data['field1'], $data['field2'], $data['field3'], $id]);
    }

    public static function delete($id)
    {
        return self::$db->query("DELETE FROM your_table WHERE id = ?", [$id]);
    }

    // Custom methods

    public static function search($keyword)
    {
        $keyword = '%' . $keyword . '%';
        return self::$db->fetchAll("SELECT * FROM your_table WHERE field1 LIKE ? OR field2 LIKE ?", [$keyword, $keyword]);
    }
}

YourModel::init();
```

---

## Security Best Practices

### Input Validation

**Always validate user input:**

```php
// In controller
public function store()
{
    // Validate required fields
    if (empty($_POST['name'])) {
        return $this->redirect()->with('error', 'Name is required');
    }

    // Validate data types
    if (!is_numeric($_POST['price'])) {
        return $this->redirect()->with('error', 'Price must be numeric');
    }

    // Validate email
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        return $this->redirect()->with('error', 'Invalid email');
    }

    // Sanitize strings
    $name = htmlspecialchars($_POST['name']);
    $description = htmlspecialchars($_POST['description']);

    // Continue with safe data
}
```

### SQL Injection Prevention

**Always use prepared statements:**

```php
// GOOD - Prepared statements
$db->query("SELECT * FROM users WHERE email = ?", [$email]);

// BAD - String concatenation (vulnerable to SQL injection)
$db->query("SELECT * FROM users WHERE email = '$email'");
```

### XSS Prevention

**Always escape output:**

```php
<!-- In views -->
<p><?= htmlspecialchars($user->name) ?></p>
<input type="text" value="<?= htmlspecialchars($product->name) ?>">

<!-- For JSON -->
<?= json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
```

### CSRF Protection

**Include CSRF token in forms:**

```php
<!-- In form -->
<form method="POST" action="/store/products">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <!-- form fields -->
</form>
```

**Verify in controller:**

```php
public function store()
{
    // CSRF middleware should handle this automatically
    // But you can manually check:
    if (!verifyCsrfToken($_POST['csrf_token'])) {
        return $this->redirect()->with('error', 'Invalid request');
    }
}
```

### Authentication

**Protect routes with middleware:**

```php
// In routes/web.php
$router->group(['middleware' => 'auth'], function($router) {
    $router->get('/admin', 'AdminController@index');
});
```

**Check permissions in controller:**

```php
public function delete($id)
{
    if (!Auth::hasPermission('delete_products')) {
        return $this->redirect()->with('error', 'Access denied');
    }

    // Continue with delete
}
```

### Password Handling

```php
// Hash password (registration)
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Verify password (login)
if (password_verify($inputPassword, $user->password)) {
    // Password correct
    Auth::login($user);
}
```

---

## Common Patterns

### Flash Messages

**Setting messages:**

```php
// In controller
return $this->redirect('/store/products')
    ->with('success', 'Product created successfully!');

return $this->redirect('/store/products')
    ->with('error', 'An error occurred');

return $this->redirect('/store/products')
    ->with('warning', 'Low stock warning');
```

**Displaying messages:**

```php
<!-- In view -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($_SESSION['success']) ?>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>
```

### Pagination

```php
// In controller
public function index()
{
    $page = $_GET['page'] ?? 1;
    $perPage = 20;
    $offset = ($page - 1) * $perPage;

    $db = Database::getInstance();

    // Get total count
    $total = $db->fetchOne("SELECT COUNT(*) as count FROM products")->count;

    // Get paginated results
    $products = $db->fetchAll(
        "SELECT * FROM products LIMIT ? OFFSET ?",
        [$perPage, $offset]
    );

    return $this->view('products.index', [
        'products' => $products,
        'page' => $page,
        'perPage' => $perPage,
        'total' => $total,
        'totalPages' => ceil($total / $perPage)
    ]);
}
```

**Pagination UI:**

```php
<!-- In view -->
<?php if ($totalPages > 1): ?>
<nav>
    <ul class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
```

### Search Functionality

```php
// In controller
public function search()
{
    $query = $_GET['q'] ?? '';

    if (empty($query)) {
        return $this->json(['results' => []]);
    }

    $db = Database::getInstance();
    $keyword = '%' . $query . '%';

    $results = $db->fetchAll(
        "SELECT id, name, sku, price FROM products
         WHERE name LIKE ? OR sku LIKE ? OR description LIKE ?
         LIMIT 10",
        [$keyword, $keyword, $keyword]
    );

    return $this->json(['results' => $results]);
}
```

### File Uploads

```php
public function uploadImage()
{
    if (!isset($_FILES['image'])) {
        return $this->json(['error' => 'No file uploaded'], 400);
    }

    $file = $_FILES['image'];

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return $this->json(['error' => 'Invalid file type'], 400);
    }

    // Validate file size (5MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        return $this->json(['error' => 'File too large'], 400);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $uploadPath = __DIR__ . '/../../public/uploads/products/' . $filename;

    // Move file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return $this->json(['error' => 'Upload failed'], 500);
    }

    return $this->json([
        'success' => true,
        'filename' => $filename,
        'url' => '/uploads/products/' . $filename
    ]);
}
```

### Activity Logging

```php
// Use the helper function
logActivity('product_created', 'Created product: ' . $product->name);
logActivity('customer_updated', 'Updated customer ID: ' . $customerId);
logActivity('transaction_completed', 'Completed transaction ID: ' . $transactionId);

// With additional data
logActivity('inventory_adjusted', 'Adjusted stock for product ID: ' . $productId, [
    'product_id' => $productId,
    'old_quantity' => $oldQty,
    'new_quantity' => $newQty,
    'reason' => $reason
]);
```

---

## Testing

### Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run specific test file
vendor/bin/phpunit tests/Models/ProductTest.php

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/
```

### Writing Tests

**Test Example:**

```php
<?php

namespace Tests\Models;

use PHPUnit\Framework\TestCase;
use App\Models\Product;
use App\Core\Database;

class ProductTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        $this->db = Database::getInstance();

        // Create test product
        $this->db->query("INSERT INTO products (name, sku, price) VALUES (?, ?, ?)",
            ['Test Product', 'TEST-SKU', 19.99]
        );
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->db->query("DELETE FROM products WHERE sku = ?", ['TEST-SKU']);
    }

    public function testFindProduct()
    {
        $product = Product::findBySku('TEST-SKU');

        $this->assertNotNull($product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals(19.99, $product->price);
    }

    public function testUpdateProduct()
    {
        $product = Product::findBySku('TEST-SKU');

        Product::update($product->id, ['price' => 24.99]);

        $updated = Product::find($product->id);
        $this->assertEquals(24.99, $updated->price);
    }
}
```

---

## Deployment

### Production Checklist

1. **Environment Configuration:**
   ```ini
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Database:**
   - Run all migrations
   - Set up automated backups
   - Configure proper user permissions

3. **File Permissions:**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 public/uploads/
   chmod 644 .env
   ```

4. **Apache Configuration:**
   ```apache
   <VirtualHost *:443>
       ServerName yourdomain.com
       DocumentRoot /var/www/html/nautilus/public

       <Directory /var/www/html/nautilus/public>
           AllowOverride All
           Require all granted
       </Directory>

       SSLEngine on
       SSLCertificateFile /path/to/cert.pem
       SSLCertificateKeyFile /path/to/key.pem
   </VirtualHost>
   ```

5. **PHP Configuration:**
   - Set appropriate memory_limit
   - Configure upload_max_filesize
   - Set post_max_size
   - Disable display_errors

6. **Security:**
   - Enable HTTPS
   - Configure firewall
   - Set up fail2ban
   - Regular security updates

---

## Troubleshooting

### Common Issues

**Database Connection Errors:**
```php
// Check .env configuration
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=nautilus
DB_USERNAME=your_username
DB_PASSWORD=your_password

// Test connection
php -r "new PDO('mysql:host=localhost;dbname=nautilus', 'user', 'pass');"
```

**Permission Errors:**
```bash
# Fix storage permissions
chmod -R 755 storage/
chown -R www-data:www-data storage/

# Fix uploads permissions
chmod -R 755 public/uploads/
chown -R www-data:www-data public/uploads/
```

**Route Not Found:**
```apache
# Ensure mod_rewrite is enabled
sudo a2enmod rewrite
sudo systemctl restart apache2

# Check .htaccess in public/
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?/$1 [L]
</IfModule>
```

**Session Issues:**
```php
// Check session configuration
session.save_path = /var/lib/php/sessions
session.gc_maxlifetime = 1440

// Ensure directory exists and is writable
mkdir -p /var/lib/php/sessions
chmod 755 /var/lib/php/sessions
```

---

## Additional Resources

- **Architecture:** See [ARCHITECTURE.md](ARCHITECTURE.md)
- **API Documentation:** See [API.md](API.md) (if exists)
- **Database Schema:** See [database/migrations/](database/migrations/)
- **Code Examples:** See existing controllers in [app/Controllers/](app/Controllers/)

---

## Support

For questions or issues:
1. Check this documentation
2. Review existing code examples
3. Check error logs in `storage/logs/`
4. Review the codebase for similar implementations

---

**Happy coding!** This guide should help you understand and extend the Nautilus V6 application effectively.
