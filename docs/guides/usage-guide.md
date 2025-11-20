# Nautilus Simple Usage Guide

**Easy-to-follow examples for common dive shop operations**

This guide shows you how to use Nautilus for everyday tasks with simple, copy-paste code examples.

---

## Table of Contents

1. [Customer Management](#customer-management)
2. [Course Bookings](#course-bookings)
3. [Equipment Rentals](#equipment-rentals)
4. [Layaway Plans](#layaway-plans)
5. [Diving Clubs](#diving-clubs)
6. [Inventory Management](#inventory-management)
7. [Travel Bookings](#travel-bookings)
8. [Reports & Analytics](#reports--analytics)
9. [Point of Sale](#point-of-sale)
10. [Communication](#communication)

---

## Customer Management

### Example 1: Add a New Customer

```php
// Simple customer registration
$stmt = $db->prepare("
    INSERT INTO customers (
        tenant_id, first_name, last_name, email, phone,
        date_of_birth, certification_agency, certification_level
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    1,                          // Your tenant ID
    'John',                     // First name
    'Doe',                      // Last name
    'john.doe@email.com',       // Email
    '555-123-4567',             // Phone
    '1985-06-15',               // Birth date
    'PADI',                     // Certification agency
    'Open Water Diver'          // Certification level
]);

$customerId = $db->lastInsertId();
echo "Customer created! ID: $customerId\n";
```

### Example 2: Update Customer Certification

```php
// Customer completed Advanced Open Water
$stmt = $db->prepare("
    INSERT INTO certifications (
        tenant_id, customer_id, certification_type, certification_number,
        certification_date, certifying_agency
    ) VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    1,
    $customerId,
    'Advanced Open Water Diver',
    'AOW123456',
    date('Y-m-d'),
    'PADI'
]);

echo "Certification added!\n";
```

---

## Course Bookings

### Example 3: Book a Customer for Open Water Course

```php
// Step 1: Find the course
$stmt = $db->prepare("
    SELECT id, course_name, price
    FROM courses
    WHERE tenant_id = ? AND course_name LIKE '%Open Water%'
    LIMIT 1
");
$stmt->execute([1]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

// Step 2: Create booking
$stmt = $db->prepare("
    INSERT INTO bookings (
        tenant_id, customer_id, course_id,
        booking_date, start_date, total_amount, status
    ) VALUES (?, ?, ?, ?, ?, ?, 'confirmed')
");

$stmt->execute([
    1,
    $customerId,
    $course['id'],
    date('Y-m-d'),                          // Booking date (today)
    date('Y-m-d', strtotime('+7 days')),    // Course starts in 7 days
    $course['price']
]);

$bookingId = $db->lastInsertId();
echo "Booking created! ID: $bookingId\n";
```

### Example 4: Record Payment for Booking

```php
// Customer pays for their course
$stmt = $db->prepare("
    INSERT INTO payments (
        tenant_id, booking_id, customer_id,
        amount, payment_method, status
    ) VALUES (?, ?, ?, ?, ?, 'completed')
");

$stmt->execute([
    1,
    $bookingId,
    $customerId,
    $course['price'],
    'credit_card'  // or 'cash', 'check', etc.
]);

echo "Payment recorded!\n";
```

---

## Equipment Rentals

### Example 5: Rent Equipment to a Customer

```php
// Rent BCD and regulator for weekend dive
$stmt = $db->prepare("
    INSERT INTO equipment_rentals (
        tenant_id, customer_id, equipment_id,
        rental_start, rental_end, daily_rate, total_cost, status
    ) VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
");

// BCD rental
$stmt->execute([
    1,
    $customerId,
    15,  // Equipment ID for BCD
    date('Y-m-d'),                          // Today
    date('Y-m-d', strtotime('+2 days')),    // Return in 2 days
    25.00,  // $25/day
    50.00,  // 2 days × $25
]);

echo "Equipment rented!\n";
```

### Example 6: Return Rental Equipment

```php
// Customer returns the equipment
$rentalId = $db->lastInsertId();

$stmt = $db->prepare("
    UPDATE equipment_rentals
    SET status = 'returned',
        actual_return_date = CURDATE()
    WHERE id = ?
");

$stmt->execute([$rentalId]);

echo "Equipment returned!\n";
```

---

## Layaway Plans

### Example 7: Start a Layaway for Equipment Purchase

```php
use App\Services\Financial\LayawayService;

$layaway = new LayawayService($db);

// Customer wants to buy $800 of equipment on layaway
$result = $layaway->createLayawayAgreement([
    'tenant_id' => 1,
    'customer_id' => $customerId,
    'layaway_plan_id' => 1,  // 90-day plan
    'total_amount' => 800.00,
    'items' => [
        ['product_id' => 5, 'quantity' => 1, 'price' => 450.00],  // Regulator
        ['product_id' => 8, 'quantity' => 1, 'price' => 350.00]   // Dive computer
    ],
    'created_by' => 1,
    'reserve_products' => true
]);

echo "Layaway created!\n";
echo "Agreement #: {$result['agreement_number']}\n";
echo "Down payment: \${$result['down_payment']}\n";
echo "Monthly payment: \${$result['payment_amount']}\n";
```

### Example 8: Record Layaway Payment

```php
// Customer makes their monthly payment
$agreementId = $result['agreement_id'];

// First record the payment
$stmt = $db->prepare("
    INSERT INTO payments (
        tenant_id, customer_id, amount, payment_method, status
    ) VALUES (?, ?, ?, ?, 'completed')
");
$stmt->execute([1, $customerId, 266.67, 'credit_card']);
$paymentId = $db->lastInsertId();

// Then update the layaway agreement
$result = $layaway->recordPayment($agreementId, 266.67, $paymentId);

echo "Payment recorded!\n";
echo "Balance remaining: \${$result['balance_remaining']}\n";
if ($result['completed']) {
    echo "✅ Layaway complete! Customer can pick up equipment.\n";
}
```

---

## Diving Clubs

### Example 9: Create a Diving Club

```php
use App\Services\Club\DivingClubService;

$clubService = new DivingClubService($db);

$result = $clubService->createClub([
    'tenant_id' => 1,
    'club_name' => 'Reef Explorers',
    'club_type' => 'social',
    'description' => 'Monthly reef dives and social events',
    'meeting_schedule' => 'First Saturday monthly at 10am',
    'annual_dues' => 50.00,
    'discount_percentage' => 10.00  // 10% member discount
]);

echo "Club created! Code: {$result['club_code']}\n";
```

### Example 10: Add Member to Club

```php
// Add customer as club member
$clubId = $result['club_id'];

$result = $clubService->addMember($clubId, $customerId, 1, [
    'dues_paid' => true
]);

echo "Member added! Number: {$result['member_number']}\n";
```

### Example 11: Create Club Event

```php
// Schedule a club dive trip
$result = $clubService->createEvent([
    'tenant_id' => 1,
    'club_id' => $clubId,
    'event_name' => 'Wreck Dive - SS Spiegel Grove',
    'event_type' => 'dive_trip',
    'description' => 'Advanced wreck dive at the Spiegel Grove',
    'event_date' => date('Y-m-d', strtotime('+30 days')),
    'start_time' => '07:00:00',
    'max_participants' => 12,
    'member_cost' => 65.00,
    'non_member_cost' => 85.00
]);

echo "Event created! ID: {$result['event_id']}\n";
```

### Example 12: Register for Club Event

```php
// Member registers for the dive trip
$eventId = $result['event_id'];

$result = $clubService->registerForEvent($eventId, $customerId, 1);

echo "Registered! Status: {$result['registration_status']}\n";
echo "Amount due: \${$result['amount_due']}\n";
```

---

## Inventory Management

### Example 13: Check Stock Levels

```php
// Check how many dive computers in stock
$stmt = $db->prepare("
    SELECT
        p.product_name,
        l.location_name,
        s.quantity_on_hand,
        s.quantity_available
    FROM inventory_stock_levels s
    JOIN product_master p ON s.product_id = p.id
    JOIN inventory_locations l ON s.location_id = l.id
    WHERE s.tenant_id = ?
      AND p.product_name LIKE '%Computer%'
");

$stmt->execute([1]);
$stock = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($stock as $item) {
    echo "{$item['product_name']} at {$item['location_name']}: ";
    echo "{$item['quantity_available']} available\n";
}
```

### Example 14: Record Equipment Sale

```php
// Sell a dive computer from main store
$stmt = $db->prepare("
    INSERT INTO inventory_movements (
        tenant_id, product_id, location_id,
        movement_type, quantity, reference_type, reference_id
    ) VALUES (?, ?, ?, 'sale', -1, 'sale', ?)
");

$stmt->execute([1, 8, 1, $saleId]);

// Update stock level
$stmt = $db->prepare("
    UPDATE inventory_stock_levels
    SET quantity_on_hand = quantity_on_hand - 1,
        quantity_available = quantity_available - 1
    WHERE product_id = ? AND location_id = ?
");

$stmt->execute([8, 1]);

echo "Sale recorded and inventory updated!\n";
```

---

## Travel Bookings

### Example 15: Search Travel Packages

```php
use App\Services\Travel\TravelBookingService;

$travel = new TravelBookingService($db);

// Search for packages to Cozumel
$result = $travel->searchPackages([
    'tenant_id' => 1,
    'destination_id' => 5,  // Cozumel
    'max_price' => 2000,
    'sort_by' => 'price_low'
]);

echo "Found {$result['count']} packages:\n";
foreach ($result['packages'] as $package) {
    echo "- {$package['package_name']}: \${$package['price_per_person']}\n";
}
```

### Example 16: Book a Travel Package

```php
// Customer books 7-day Cozumel trip
$result = $travel->createBooking([
    'tenant_id' => 1,
    'customer_id' => $customerId,
    'package_id' => 12,  // Cozumel package
    'departure_date' => '2025-03-15',
    'return_date' => '2025-03-22',
    'number_of_travelers' => 2,
    'primary_traveler' => [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'passport_number' => 'ABC123456',
        'date_of_birth' => '1985-06-15'
    ],
    'additional_travelers' => [
        [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'passport_number' => 'DEF789012',
            'date_of_birth' => '1987-08-20'
        ]
    ],
    'booked_by' => 1
]);

echo "Travel booking created!\n";
echo "Reference: {$result['booking_reference']}\n";
echo "Total: \${$result['total_price']}\n";
echo "Deposit due: \${$result['deposit_amount']}\n";
echo "Balance due by: {$result['balance_due_date']}\n";
```

---

## Reports & Analytics

### Example 17: Generate Daily Sales Report

```php
use App\Services\Analytics\BusinessIntelligenceService;

$bi = new BusinessIntelligenceService($db);

// Generate today's sales report
$result = $bi->generateReport(1, 1, [  // Template ID 1 = Daily Sales Summary
    'date_range' => 'today',
    'user_id' => 1
]);

echo "Daily Sales Report\n";
echo "==================\n";
echo "Total Sales: \${$result['data'][0]['total_revenue']}\n";
echo "Transactions: {$result['data'][0]['transaction_count']}\n";
echo "Generated in: {$result['execution_time_ms']}ms\n";
```

### Example 18: View Dashboard

```php
// Load executive dashboard
$dashboard = $bi->getDashboard(1, 1);  // Dashboard ID 1 = Executive Overview

echo "Executive Dashboard\n";
echo "===================\n";

foreach ($dashboard['widgets'] as $widget) {
    echo "{$widget['widget_name']}: ";
    if ($widget['widget_type'] === 'kpi') {
        echo "\${$widget['data']['value']} ";
        echo "({$widget['data']['trend']} {$widget['data']['change_percentage']}%)\n";
    }
}
```

### Example 19: Get Customer Analytics

```php
use App\Services\Analytics\CustomerAnalyticsService;

$analytics = new CustomerAnalyticsService($db);

// Get high-value customers
$vips = $analytics->getHighValueCustomers(1, 10);

echo "Top 10 Customers by Lifetime Value:\n";
foreach ($vips['customers'] as $i => $customer) {
    echo ($i + 1) . ". {$customer['first_name']} {$customer['last_name']} ";
    echo "(\${$customer['lifetime_value']} - {$customer['total_bookings']} bookings)\n";
}
```

---

## Point of Sale

### Example 20: Process a POS Transaction

```php
// Ring up a sale at the register
$stmt = $db->prepare("
    INSERT INTO pos_transactions (
        tenant_id, terminal_id, staff_id, transaction_type,
        subtotal, tax_amount, total_amount, payment_method, status
    ) VALUES (?, ?, ?, 'sale', ?, ?, ?, ?, 'completed')
");

$subtotal = 125.00;
$tax = $subtotal * 0.08;  // 8% tax
$total = $subtotal + $tax;

$stmt->execute([
    1,
    1,  // Terminal ID (Main Register)
    1,  // Staff member ID
    $subtotal,
    $tax,
    $total,
    'credit_card'
]);

$transactionId = $db->lastInsertId();
echo "Sale complete! Transaction #$transactionId - Total: \$$total\n";
```

---

## Communication

### Example 21: Send Booking Reminder

```php
// Send SMS reminder to customer
$stmt = $db->prepare("
    INSERT INTO messages (
        tenant_id, conversation_id, channel_type, direction,
        message_type, content, recipient_phone, status
    ) VALUES (?, ?, 'sms', 'outbound', 'text', ?, ?, 'sent')
");

$message = "Reminder: Your Open Water course starts tomorrow at 8am. See you there!";

$stmt->execute([
    1,
    null,  // Create new conversation
    $message,
    '555-123-4567'
]);

echo "Reminder sent!\n";
```

---

## Complete Workflow Example

### Example 22: Full Customer Journey

```php
// Complete workflow: New customer books course, pays, gets certified

// 1. Create customer
$stmt = $db->prepare("
    INSERT INTO customers (tenant_id, first_name, last_name, email, phone)
    VALUES (1, 'Sarah', 'Smith', 'sarah@email.com', '555-987-6543')
");
$stmt->execute();
$customerId = $db->lastInsertId();
echo "1. Customer created: Sarah Smith\n";

// 2. Book Open Water course
$stmt = $db->prepare("
    INSERT INTO bookings (tenant_id, customer_id, course_id, booking_date, total_amount, status)
    VALUES (1, ?, 1, CURDATE(), 499.00, 'confirmed')
");
$stmt->execute([$customerId]);
$bookingId = $db->lastInsertId();
echo "2. Booked Open Water course\n";

// 3. Process payment
$stmt = $db->prepare("
    INSERT INTO payments (tenant_id, booking_id, customer_id, amount, payment_method, status)
    VALUES (1, ?, ?, 499.00, 'credit_card', 'completed')
");
$stmt->execute([$bookingId, $customerId]);
echo "3. Payment processed: \$499.00\n";

// 4. Rent equipment
$stmt = $db->prepare("
    INSERT INTO equipment_rentals (tenant_id, customer_id, equipment_id, rental_start, rental_end, total_cost, status)
    VALUES (1, ?, 10, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 4 DAY), 100.00, 'active')
");
$stmt->execute([$customerId]);
echo "4. Equipment rented for course\n";

// 5. Issue certification (after course completion)
$stmt = $db->prepare("
    INSERT INTO certifications (tenant_id, customer_id, certification_type, certification_number, certification_date, certifying_agency)
    VALUES (1, ?, 'Open Water Diver', 'OW123456', CURDATE(), 'PADI')
");
$stmt->execute([$customerId]);
echo "5. ✅ Certification issued: Open Water Diver\n";

// 6. Add to diving club
$clubService = new DivingClubService($db);
$clubService->addMember(1, $customerId, 1);
echo "6. Added to Ocean Explorers club\n";

echo "\n✅ Complete! Sarah is now a certified diver and club member!\n";
```

---

## Quick Reference

### Most Common Queries

```php
// Find a customer by email
$customer = $db->prepare("SELECT * FROM customers WHERE email = ?");
$customer->execute(['john@email.com']);

// Get customer's bookings
$bookings = $db->prepare("
    SELECT * FROM bookings
    WHERE customer_id = ?
    ORDER BY booking_date DESC
");
$bookings->execute([$customerId]);

// Check equipment availability
$available = $db->prepare("
    SELECT * FROM equipment_inventory
    WHERE status = 'available'
      AND equipment_type = ?
");
$available->execute(['BCD']);

// Get today's schedule
$schedule = $db->query("
    SELECT * FROM bookings
    WHERE start_date = CURDATE()
    ORDER BY start_time
");

// Monthly revenue
$revenue = $db->query("
    SELECT SUM(total_amount) as revenue
    FROM bookings
    WHERE MONTH(booking_date) = MONTH(CURDATE())
      AND status = 'completed'
");
```

---

## Tips for Success

### Best Practices

1. **Always use tenant_id**: Every query should filter by tenant_id = 1 (or your tenant ID)
2. **Validate data**: Check if customer/booking/product exists before operations
3. **Use transactions**: For multi-step operations (booking + payment), use database transactions
4. **Handle errors**: Wrap database operations in try/catch blocks
5. **Log activities**: Record who did what and when for audit trails

### Example with Error Handling

```php
try {
    $db->beginTransaction();

    // Create booking
    $stmt = $db->prepare("INSERT INTO bookings (...) VALUES (...)");
    $stmt->execute([...]);
    $bookingId = $db->lastInsertId();

    // Record payment
    $stmt = $db->prepare("INSERT INTO payments (...) VALUES (...)");
    $stmt->execute([...]);

    $db->commit();
    echo "Success!\n";

} catch (Exception $e) {
    $db->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
```

---

## Need Help?

- **Full Documentation**: See [COMPLETE_SYSTEM_DOCUMENTATION.md](COMPLETE_SYSTEM_DOCUMENTATION.md)
- **Quick Start**: See [QUICK_START_GUIDE.md](QUICK_START_GUIDE.md)
- **Business Intelligence**: See [BUSINESS_INTELLIGENCE_GUIDE.md](BUSINESS_INTELLIGENCE_GUIDE.md)

---

**Version**: 1.0
**Last Updated**: January 2025
**Perfect for**: Dive shop staff, developers, system administrators
