-- Seed default cash drawers for a typical dive shop setup

-- Check if cash drawers already exist
SET @drawer_count = (SELECT COUNT(*) FROM cash_drawers);

-- Only insert if no drawers exist
INSERT INTO cash_drawers (name, location, starting_float, current_balance, notes, is_active, created_at)
SELECT * FROM (
    SELECT
        'Main Register' as name,
        'Front Counter' as location,
        200.00 as starting_float,
        200.00 as current_balance,
        'Primary POS register at front counter' as notes,
        1 as is_active,
        NOW() as created_at
    UNION ALL
    SELECT
        'Pool Register',
        'Pool Deck',
        150.00,
        150.00,
        'Register for pool-side air fills and equipment rentals',
        1,
        NOW()
    UNION ALL
    SELECT
        'Boat Register',
        'Dive Boat',
        100.00,
        100.00,
        'Mobile register for boat trips and charters',
        1,
        NOW()
) AS tmp
WHERE @drawer_count = 0;

-- Insert default customer tags if they don't exist
INSERT INTO customer_tags (name, slug, color, icon, description, is_active, display_order, created_at)
SELECT * FROM (
    SELECT
        'VIP' as name,
        'vip' as slug,
        '#f39c12' as color,
        'bi-star-fill' as icon,
        'VIP Customer - Highest priority service' as description,
        1 as is_active,
        1 as display_order,
        NOW() as created_at
    WHERE NOT EXISTS (SELECT 1 FROM customer_tags WHERE slug = 'vip')

    UNION ALL
    SELECT
        'Wholesale',
        'wholesale',
        '#3498db',
        'bi-briefcase-fill',
        'Wholesale Customer - Bulk buyer with special pricing',
        1,
        2,
        NOW()
    WHERE NOT EXISTS (SELECT 1 FROM customer_tags WHERE slug = 'wholesale')

    UNION ALL
    SELECT
        'Instructor',
        'instructor',
        '#2ecc71',
        'bi-mortarboard-fill',
        'Certified Diving Instructor',
        1,
        3,
        NOW()
    WHERE NOT EXISTS (SELECT 1 FROM customer_tags WHERE slug = 'instructor')

    UNION ALL
    SELECT
        'New Customer',
        'new-customer',
        '#1abc9c',
        'bi-person-plus-fill',
        'New or first-time customer',
        1,
        4,
        NOW()
    WHERE NOT EXISTS (SELECT 1 FROM customer_tags WHERE slug = 'new-customer')

    UNION ALL
    SELECT
        'Inactive',
        'inactive',
        '#95a5a6',
        'bi-pause-circle-fill',
        'Inactive customer (no purchases in 6+ months)',
        1,
        5,
        NOW()
    WHERE NOT EXISTS (SELECT 1 FROM customer_tags WHERE slug = 'inactive')

    UNION ALL
    SELECT
        'Corporate',
        'corporate',
        '#34495e',
        'bi-building-fill',
        'Corporate or business account',
        1,
        6,
        NOW()
    WHERE NOT EXISTS (SELECT 1 FROM customer_tags WHERE slug = 'corporate')

    UNION ALL
    SELECT
        'Newsletter',
        'newsletter',
        '#9b59b6',
        'bi-envelope-fill',
        'Subscribed to newsletter and marketing emails',
        1,
        7,
        NOW()
    WHERE NOT EXISTS (SELECT 1 FROM customer_tags WHERE slug = 'newsletter')

    UNION ALL
    SELECT
        'Referral',
        'referral',
        '#e74c3c',
        'bi-share-fill',
        'Referred by another customer',
        1,
        8,
        NOW()
    WHERE NOT EXISTS (SELECT 1 FROM customer_tags WHERE slug = 'referral')

    UNION ALL
    SELECT
        'Certification Due',
        'certification-due',
        '#e67e22',
        'bi-exclamation-triangle-fill',
        'Customer has certifications expiring soon',
        1,
        9,
        NOW()
    WHERE NOT EXISTS (SELECT 1 FROM customer_tags WHERE slug = 'certification-due')

    UNION ALL
    SELECT
        'Equipment Rental',
        'equipment-rental',
        '#16a085',
        'bi-tools',
        'Frequent equipment rental customer',
        1,
        10,
        NOW()
    WHERE NOT EXISTS (SELECT 1 FROM customer_tags WHERE slug = 'equipment-rental')
) AS tags;

-- Display summary
SELECT
    (SELECT COUNT(*) FROM cash_drawers) as total_cash_drawers,
    (SELECT COUNT(*) FROM customer_tags) as total_customer_tags,
    (SELECT COUNT(*) FROM cash_drawer_sessions) as total_sessions,
    (SELECT COUNT(*) FROM certification_agencies) as total_agencies
