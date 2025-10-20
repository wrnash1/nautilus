-- ================================================
-- Nautilus V6 - Performance Optimization
-- Migration: 017_performance_indexes.sql
-- Description: Add indexes for query optimization
-- Note: Most indexes already exist in table creation
-- ================================================

-- ==================== CUSTOMERS TABLE ====================
-- Skip - indexes already exist in table creation (002)

-- ==================== PRODUCTS TABLE ====================
-- Skip - indexes already exist in table creation (003)

-- ==================== TRANSACTIONS TABLE ====================
-- Skip - indexes already exist in table creation (004)

-- ==================== RENTAL RESERVATIONS TABLE ====================
-- Skip - indexes already exist in table creation (006)

-- ==================== COURSE ENROLLMENTS TABLE ====================
-- Skip - indexes already exist in table creation (007)

-- ==================== TRIP BOOKINGS TABLE ====================
-- Skip - indexes already exist in table creation (007)

-- ==================== INVENTORY TABLE ====================
-- Skip - indexes already exist in table creation (003)

-- ==================== AUDIT LOGS TABLE ====================
-- Skip - indexes already exist in table creation (001)

-- ==================== CERTIFICATIONS TABLE ====================
-- Skip - indexes already exist in table creation (014)

-- ==================== WORK ORDERS TABLE ====================
-- Skip - indexes already exist in table creation (008)

-- ==================== ORDERS TABLE ====================
-- Skip - indexes already exist in table creation (009)

-- ==================== STAFF TABLE ====================
-- Skip - indexes already exist in table creation (012)

-- ==================== STAFF SCHEDULES TABLE ====================
-- Skip - indexes already exist in table creation (012)

-- ==================== COMMISSIONS TABLE ====================
-- Skip - indexes already exist in table creation (012)

-- ==================== EMAIL CAMPAIGNS TABLE ====================
-- Skip - indexes already exist in table creation (014)

-- ==================== LOYALTY TRANSACTIONS TABLE ====================
-- Skip - indexes already exist in table creation (011)

-- ==================== COUPONS TABLE ====================
-- Skip - indexes already exist in table creation (011)

-- ==================== SERVICE REMINDERS TABLE ====================
-- Skip - indexes already exist in table creation (014)

-- ==================== ANALYZE TABLES FOR OPTIMIZATION ====================
-- Run ANALYZE on all major tables to update statistics

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
-- All necessary indexes already exist in table creation
-- ANALYZE statements update optimizer statistics
-- ================================================
