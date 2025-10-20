-- ================================================
-- Nautilus V6 - Performance Optimization
-- Migration: 017_performance_indexes.sql
-- Description: Add indexes for query optimization
-- ================================================

-- ==================== CUSTOMERS TABLE ====================
ALTER TABLE customers
    ADD INDEX idx_email (email),
    ADD INDEX idx_phone (phone),
    ADD INDEX idx_status (status),
    ADD INDEX idx_customer_type (customer_type),
    ADD INDEX idx_created_at (created_at),
    ADD INDEX idx_search (first_name, last_name, email);

-- ==================== PRODUCTS TABLE ====================
ALTER TABLE products
    ADD INDEX idx_sku (sku),
    ADD INDEX idx_category (category_id),
    ADD INDEX idx_is_active (is_active),
    ADD INDEX idx_stock (stock_quantity),
    ADD INDEX idx_price (price),
    ADD INDEX idx_search (name, sku);

-- ==================== TRANSACTIONS TABLE ====================
ALTER TABLE transactions
    ADD INDEX idx_customer (customer_id),
    ADD INDEX idx_user (user_id),
    ADD INDEX idx_status (status),
    ADD INDEX idx_transaction_date (transaction_date),
    ADD INDEX idx_total (total_amount),
    ADD INDEX idx_payment_method (payment_method);

-- ==================== TRANSACTION ITEMS TABLE ====================
ALTER TABLE transaction_items
    ADD INDEX idx_transaction (transaction_id),
    ADD INDEX idx_product (product_id);

-- ==================== RENTAL RESERVATIONS TABLE ====================
ALTER TABLE rental_reservations
    ADD INDEX idx_customer (customer_id),
    ADD INDEX idx_status (status),
    ADD INDEX idx_dates (start_date, end_date),
    ADD INDEX idx_equipment (equipment_id);

-- ==================== COURSE ENROLLMENTS TABLE ====================
ALTER TABLE course_enrollments
    ADD INDEX idx_customer (customer_id),
    ADD INDEX idx_schedule (schedule_id),
    ADD INDEX idx_status (status),
    ADD INDEX idx_enrollment_date (enrollment_date);

-- ==================== TRIP BOOKINGS TABLE ====================
ALTER TABLE trip_bookings
    ADD INDEX idx_customer (customer_id),
    ADD INDEX idx_schedule (schedule_id),
    ADD INDEX idx_status (status),
    ADD INDEX idx_booking_date (booking_date);

-- ==================== INVENTORY TABLE ====================
ALTER TABLE inventory_movements
    ADD INDEX idx_product (product_id),
    ADD INDEX idx_type (movement_type),
    ADD INDEX idx_date (movement_date),
    ADD INDEX idx_user (user_id);

-- ==================== AUDIT LOGS TABLE ====================
ALTER TABLE audit_logs
    ADD INDEX idx_user (user_id),
    ADD INDEX idx_action (action),
    ADD INDEX idx_table (table_name),
    ADD INDEX idx_created (created_at),
    ADD INDEX idx_composite (user_id, action, created_at);

-- ==================== CERTIFICATIONS TABLE ====================
ALTER TABLE customer_certifications
    ADD INDEX idx_customer (customer_id),
    ADD INDEX idx_agency (certification_agency_id),
    ADD INDEX idx_issue_date (issue_date),
    ADD INDEX idx_expiry_date (expiry_date),
    ADD INDEX idx_verification (verification_status);

-- ==================== WORK ORDERS TABLE ====================
ALTER TABLE work_orders
    ADD INDEX idx_customer (customer_id),
    ADD INDEX idx_status (status),
    ADD INDEX idx_priority (priority),
    ADD INDEX idx_created_date (created_date),
    ADD INDEX idx_due_date (due_date),
    ADD INDEX idx_assigned (assigned_to);

-- ==================== ORDERS TABLE ====================
ALTER TABLE orders
    ADD INDEX idx_customer_id (customer_id),
    ADD INDEX idx_status (status),
    ADD INDEX idx_order_date (order_date),
    ADD INDEX idx_total (total_amount);

-- ==================== STAFF TABLE ====================
ALTER TABLE staff
    ADD INDEX idx_user_id (user_id),
    ADD INDEX idx_status (status),
    ADD INDEX idx_hire_date (hire_date);

-- ==================== STAFF SCHEDULES TABLE ====================
ALTER TABLE staff_schedules
    ADD INDEX idx_staff (staff_id),
    ADD INDEX idx_date (schedule_date),
    ADD INDEX idx_shift (shift_type);

-- ==================== COMMISSIONS TABLE ====================
ALTER TABLE commissions
    ADD INDEX idx_staff (staff_id),
    ADD INDEX idx_transaction (transaction_id),
    ADD INDEX idx_period (period_start, period_end),
    ADD INDEX idx_status (status);

-- ==================== EMAIL CAMPAIGNS TABLE ====================
ALTER TABLE email_campaigns
    ADD INDEX idx_status (status),
    ADD INDEX idx_scheduled (scheduled_send_date),
    ADD INDEX idx_created (created_at);

-- ==================== LOYALTY TRANSACTIONS TABLE ====================
ALTER TABLE loyalty_transactions
    ADD INDEX idx_customer (customer_id),
    ADD INDEX idx_type (transaction_type),
    ADD INDEX idx_date (transaction_date);

-- ==================== COUPONS TABLE ====================
ALTER TABLE coupons
    ADD INDEX idx_code (code),
    ADD INDEX idx_active (is_active),
    ADD INDEX idx_validity (valid_from, valid_until);

-- ==================== SERVICE REMINDERS TABLE ====================
ALTER TABLE service_reminders
    ADD INDEX idx_customer (customer_id),
    ADD INDEX idx_status (status),
    ADD INDEX idx_scheduled (scheduled_send_date),
    ADD INDEX idx_type (reminder_type);

-- ==================== COMPOSITE INDEXES FOR COMMON QUERIES ====================

-- Sales reports by date range
ALTER TABLE transactions
    ADD INDEX idx_sales_report (transaction_date, status, total_amount);

-- Customer activity
ALTER TABLE customers
    ADD INDEX idx_customer_activity (id, status, created_at);

-- Product inventory management
ALTER TABLE products
    ADD INDEX idx_inventory_mgmt (category_id, is_active, stock_quantity);

-- Active reservations
ALTER TABLE rental_reservations
    ADD INDEX idx_active_reservations (status, start_date, end_date);

-- Upcoming courses
ALTER TABLE course_schedules
    ADD INDEX idx_upcoming_courses (start_date, status);

-- Upcoming trips
ALTER TABLE trip_schedules
    ADD INDEX idx_upcoming_trips (departure_date, status);

-- ==================== FULL TEXT SEARCH INDEXES ====================

-- Product search
ALTER TABLE products
    ADD FULLTEXT INDEX ft_product_search (name, description);

-- Customer search
ALTER TABLE customers
    ADD FULLTEXT INDEX ft_customer_search (first_name, last_name, email, company);

-- Course search
ALTER TABLE courses
    ADD FULLTEXT INDEX ft_course_search (name, description);

-- ==================== ANALYZE TABLES FOR OPTIMIZATION ====================

ANALYZE TABLE customers;
ANALYZE TABLE products;
ANALYZE TABLE transactions;
ANALYZE TABLE transaction_items;
ANALYZE TABLE rental_reservations;
ANALYZE TABLE course_enrollments;
ANALYZE TABLE trip_bookings;
ANALYZE TABLE work_orders;
ANALYZE TABLE orders;
ANALYZE TABLE staff;

-- ================================================
-- Performance optimization complete
-- These indexes will significantly improve query performance
-- Remember to monitor slow query log and adjust as needed
-- ================================================
