# Nautilus V6 - Architecture Overview

## Application Structure

```
┌─────────────────────────────────────────────────────────────────────┐
│                                                                      │
│                         NAUTILUS V6 SYSTEM                          │
│                                                                      │
│  ┌────────────────────────┐         ┌─────────────────────────┐   │
│  │   STOREFRONT (External) │         │   STORE (Internal)      │   │
│  │   Customer Facing       │         │   Staff Management      │   │
│  └────────────────────────┘         └─────────────────────────┘   │
│           │                                      │                   │
│           │                                      │                   │
│           ▼                                      ▼                   │
│  ┌────────────────────────┐         ┌─────────────────────────┐   │
│  │  Public Routes          │         │  Protected Routes       │   │
│  │  ----------------       │         │  -----------------      │   │
│  │  / (homepage)          │         │  /store (dashboard)     │   │
│  │  /shop (products)      │         │  /store/pos             │   │
│  │  /contact              │         │  /store/customers       │   │
│  │  /account              │         │  /store/inventory       │   │
│  │                         │         │  /store/reports         │   │
│  │  Auth: Optional         │         │  Auth: Required ✓       │   │
│  └────────────────────────┘         │  Roles: ✓✓✓             │   │
│           │                          └─────────────────────────┘   │
│           │                                      │                   │
│           └──────────────┬───────────────────────┘                  │
│                          │                                           │
│                          ▼                                           │
│              ┌─────────────────────────┐                            │
│              │   SHARED DATABASE        │                            │
│              │   MySQL/MariaDB          │                            │
│              │                          │                            │
│              │  Tables:                 │                            │
│              │  - customers             │                            │
│              │  - products              │                            │
│              │  - orders                │                            │
│              │  - users (staff)         │                            │
│              │  - transactions          │                            │
│              │  - rentals               │                            │
│              │  - courses               │                            │
│              │  - trips                 │                            │
│              │  ... and 25+ more        │                            │
│              └─────────────────────────┘                            │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## Directory Structure

```
/var/www/html/
│
├── nautilus-storefront/              ← EXTERNAL APP
│   ├── app/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php
│   │   │   ├── Shop/
│   │   │   ├── Customer/
│   │   │   └── Install/
│   │   ├── Core/                     (Database, Router, Auth...)
│   │   ├── Models/                   (Customer, Product, Order...)
│   │   ├── Services/                 (Business logic)
│   │   ├── Middleware/               (CSRF, Security...)
│   │   └── Views/
│   │       ├── storefront/           ← Customer-facing layouts
│   │       ├── shop/
│   │       └── customer/
│   ├── public/
│   │   ├── index.php                 ← Entry point
│   │   └── assets/
│   ├── routes/
│   │   └── web.php                   ← Public routes
│   ├── storage/
│   ├── .env
│   └── composer.json
│
└── nautilus-store/                   ← INTERNAL APP
    ├── app/
    │   ├── Controllers/
    │   │   ├── Admin/
    │   │   ├── Auth/
    │   │   ├── POS/
    │   │   ├── CRM/
    │   │   ├── Inventory/
    │   │   ├── Rentals/
    │   │   ├── Courses/
    │   │   ├── Trips/
    │   │   ├── Reports/
    │   │   └── Staff/
    │   ├── Core/                     (Same as Storefront)
    │   ├── Models/                   (Same as Storefront)
    │   ├── Services/                 (Same as Storefront)
    │   ├── Middleware/               (Same + RoleMiddleware)
    │   └── Views/
    │       ├── dashboard/            ← Staff-facing layouts
    │       ├── pos/
    │       ├── customers/
    │       ├── products/
    │       ├── reports/
    │       └── staff/
    ├── public/
    │   ├── index.php                 ← Entry point
    │   └── assets/
    ├── routes/
    │   └── web.php                   ← Protected routes (/store/*)
    ├── storage/
    ├── .env
    └── composer.json
```

---

## Request Flow

### External Request (Customer)

```
1. Customer visits: https://yourdomain.com/shop
                                │
                                ▼
2. Apache DocumentRoot: /var/www/html/nautilus-storefront/public/
                                │
                                ▼
3. index.php loads Router
                                │
                                ▼
4. Router matches: /shop → Shop\ShopController@index
                                │
                                ▼
5. Controller queries database
                                │
                                ▼
6. Render view: app/Views/shop/index.php
                                │
                                ▼
7. Return HTML to customer
```

### Internal Request (Staff)

```
1. Staff visits: https://yourdomain.com/store/customers
                                │
                                ▼
2. Apache Alias: /store → /var/www/html/nautilus-store/public/
                                │
                                ▼
3. index.php loads Router
                                │
                                ▼
4. AuthMiddleware checks login
   ├─ Not logged in → redirect to /store/login
   └─ Logged in ✓
                                │
                                ▼
5. Router matches: /store/customers → CRM\CustomerController@index
                                │
                                ▼
6. RoleMiddleware checks permissions (future)
   ├─ No permission → 403 Forbidden
   └─ Has permission ✓
                                │
                                ▼
7. Controller queries database
                                │
                                ▼
8. Render role-specific view: app/Views/customers/index.php
                                │
                                ▼
9. Return HTML to staff member
```

---

## Role-Based Access (Internal App)

```
┌─────────────────────────────────────────────────────────────┐
│                    STAFF MEMBER LOGS IN                      │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
              ┌─────────────────────────┐
              │  Auth::user()->role     │
              └─────────────────────────┘
                            │
        ┌───────────────────┼───────────────────┐
        │                   │                   │
        ▼                   ▼                   ▼
┌──────────────┐    ┌──────────────┐    ┌──────────────┐
│   MANAGER    │    │ SALES STAFF  │    │  INSTRUCTOR  │
│   (admin)    │    │   (sales)    │    │   (teacher)  │
└──────────────┘    └──────────────┘    └──────────────┘
        │                   │                   │
        ▼                   ▼                   ▼
  Full Access         Limited Access      Course Access
        │                   │                   │
        ├─ Dashboard        ├─ POS              ├─ Courses
        ├─ POS              ├─ Customers        ├─ Enrollments
        ├─ CRM              ├─ Basic Reports    ├─ Attendance
        ├─ Inventory        └─ (No Settings)    └─ Student Records
        ├─ Rentals
        ├─ Courses
        ├─ Trips
        ├─ Reports
        ├─ Staff Mgmt
        ├─ Storefront Config
        └─ Settings
```

### Role Implementation

**In Controller:**
```php
public function index()
{
    $user = Auth::user();
    $role = $user->role;

    // Different dashboards for different roles
    switch ($role) {
        case 'manager':
            return $this->renderManagerView();
        case 'sales':
            return $this->renderSalesView();
        case 'instructor':
            return $this->renderInstructorView();
        default:
            return $this->renderBasicView();
    }
}
```

**In Middleware (future):**
```php
class RoleMiddleware
{
    public function handle($request)
    {
        $user = Auth::user();
        $route = $request->getRoute();

        // Check if user's role has access to this route
        if (!$this->hasAccess($user->role, $route)) {
            return redirect('/store/dashboard')
                ->with('error', 'Access denied');
        }
    }
}
```

**In Views:**
```php
<?php if (Auth::user()->hasRole('manager')): ?>
    <li><a href="/store/staff">Staff Management</a></li>
    <li><a href="/store/storefront">Storefront Config</a></li>
<?php endif; ?>

<?php if (Auth::user()->can('view_reports')): ?>
    <li><a href="/store/reports">Reports</a></li>
<?php endif; ?>
```

---

## Database Schema (Shared)

Both applications use the same database with these key tables:

**Core Tables:**
- `users` - Staff accounts (for Store app)
- `customers` - Customer accounts (for Storefront app)
- `roles` - Role definitions
- `permissions` - Permission definitions

**Product Tables:**
- `products`
- `categories`
- `vendors`
- `product_images`

**Sales Tables:**
- `transactions`
- `transaction_items`
- `orders`
- `order_items`

**Operations Tables:**
- `rental_equipment`
- `rental_reservations`
- `air_fills`
- `courses`
- `course_schedules`
- `course_enrollments`
- `trips`
- `trip_schedules`
- `trip_bookings`
- `work_orders`

**Storefront Tables:**
- `theme_config`
- `homepage_sections`
- `storefront_settings`
- `navigation_menus`
- `theme_assets`

---

## Security Model

### External App (Storefront)
- ✓ Rate limiting on forms
- ✓ CSRF protection
- ✓ Input sanitization
- ✓ SQL injection prevention (prepared statements)
- ✓ XSS prevention (output escaping)
- ⚠ No authentication required (public)
- ⚠ Customer portal requires login

### Internal App (Store)
- ✓ All pages require authentication
- ✓ Session-based auth with secure cookies
- ✓ Password hashing (bcrypt)
- ✓ Role-based access control
- ✓ CSRF protection
- ✓ Input sanitization
- ✓ Activity logging
- ✓ Failed login tracking
- ✓ Optional 2FA
- ✓ IP whitelisting (optional)

---

## Deployment Architecture

```
                    ┌─────────────┐
                    │   INTERNET  │
                    └──────┬──────┘
                           │
                           ▼
                    ┌─────────────┐
                    │   Firewall  │
                    │   (443/80)  │
                    └──────┬──────┘
                           │
                           ▼
                    ┌─────────────┐
                    │  Apache     │
                    │  Web Server │
                    └──────┬──────┘
                           │
              ┌────────────┴─────────────┐
              │                          │
              ▼                          ▼
    ┌──────────────────┐      ┌─────────────────┐
    │   Storefront     │      │   Store         │
    │   /public/       │      │   /store/       │
    │                  │      │                 │
    │   Public Access  │      │   Auth Required │
    └────────┬─────────┘      └────────┬────────┘
             │                         │
             └────────┬────────────────┘
                      │
                      ▼
             ┌────────────────┐
             │  MySQL/MariaDB │
             │   Database     │
             │                │
             │   Port: 3306   │
             │   (localhost)  │
             └────────────────┘
```

---

## Future Architecture Options

### Option 1: Microservices

```
┌──────────┐     ┌──────────┐     ┌──────────┐
│Storefront│────▶│Product   │     │Customer  │
│  App     │     │Service   │     │Service   │
└──────────┘     └──────────┘     └──────────┘
                       │                │
                       ▼                ▼
┌──────────┐     ┌──────────┐     ┌──────────┐
│  Store   │────▶│Inventory │     │Order     │
│  App     │     │Service   │     │Service   │
└──────────┘     └──────────┘     └──────────┘
```

### Option 2: API Gateway

```
┌──────────┐                 ┌──────────┐
│Storefront│────┐            │  Store   │
│  (React) │    │            │  (React) │
└──────────┘    │            └──────────┘
                ▼                   │
           ┌─────────┐              │
           │   API   │◀─────────────┘
           │ Gateway │
           └─────────┘
                │
    ┌───────────┼───────────┐
    ▼           ▼           ▼
┌────────┐ ┌────────┐ ┌────────┐
│Products│ │Customers│ │Orders │
│Service │ │Service  │ │Service│
└────────┘ └────────┘ └────────┘
```

---

## Summary

✅ **Two completely separate applications**
✅ **Share only the database**
✅ **Independent routing and deployment**
✅ **Role-based access in Store app**
✅ **Clean separation of concerns**
✅ **Easy to scale independently**

---

For implementation details, see [APPLICATION_SPLIT_GUIDE.md](APPLICATION_SPLIT_GUIDE.md)
