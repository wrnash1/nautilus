-- =====================================================
-- Nautilus Dive Shop - Database Seeder
-- Run this after creating tables to populate initial data
-- =====================================================

-- =====================================================
-- SYSTEM SETTINGS
-- =====================================================

-- Business Information
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, created_at) VALUES
('business_name', 'Nautilus Dive Shop', 'text', 'Business name', NOW()),
('business_phone', '817-406-4080', 'text', 'Business phone number', NOW()),
('business_email', 'info@nautilus.local', 'text', 'Business email address', NOW()),
('business_address', '149 W Main Street', 'text', 'Business street address', NOW()),
('business_city', 'Azle', 'text', 'Business city', NOW()),
('business_state', 'TX', 'text', 'Business state', NOW()),
('business_zip', '76020', 'text', 'Business ZIP code', NOW()),
('business_country', 'US', 'text', 'Business country', NOW())
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW();

-- Statistics
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, created_at) VALUES
('stats_certified_divers', '5000', 'text', 'Number of certified divers', NOW()),
('stats_years_experience', '25', 'text', 'Years in business', NOW()),
('stats_dive_destinations', '100', 'text', 'Number of dive destinations offered', NOW()),
('stats_customer_rating', '4.9', 'text', 'Customer rating out of 5', NOW())
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW();

-- Certification Organization
INSERT INTO system_settings (setting_key, setting_value, setting_type, description, created_at) VALUES
('primary_certification_org', 'PADI', 'text', 'Primary certification organization (PADI, SSI, NAUI, etc.)', NOW()),
('certification_level', '5-Star Center', 'text', 'Certification level or designation', NOW()),
('secondary_certifications', 'SSI,NAUI', 'text', 'Comma-separated list of other certifications offered', NOW())
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW();

-- =====================================================
-- STOREFRONT SERVICE BOXES (What We Offer)
-- =====================================================

INSERT INTO storefront_service_boxes (title, description, icon, link, display_order, is_active, created_at) VALUES
('PADI Courses', 'Professional diving certification from beginner to instructor', 'bi bi-award', '/courses', 1, 1, NOW()),
('Equipment Shop', 'Top-quality diving gear and accessories', 'bi bi-shop', '/shop', 2, 1, NOW()),
('Dive Trips', 'Guided adventures to amazing dive sites worldwide', 'bi bi-geo-alt', '/trips', 3, 1, NOW()),
('Equipment Rental', 'Professional-grade rental equipment available', 'bi bi-tools', '/rentals', 4, 1, NOW()),
('DAN Courses', 'Divers Alert Network training for dive safety and emergency response', 'bi bi-heart-pulse', '/courses/dan', 5, 1, NOW()),
('Dive Insurance', 'Comprehensive dive insurance coverage for your peace of mind', 'bi bi-shield-check', '/insurance', 6, 1, NOW())
ON DUPLICATE KEY UPDATE 
    description = VALUES(description),
    icon = VALUES(icon),
    link = VALUES(link),
    display_order = VALUES(display_order),
    updated_at = NOW();

-- =====================================================
-- MEMBERSHIP TIERS
-- =====================================================

INSERT INTO membership_tiers (name, description, price, duration_months, benefits, discount_percentage, max_rentals_per_month, priority_booking, free_air_fills, display_order, is_active, created_at) VALUES
('Bronze Member', 'Perfect for occasional divers who want to save on equipment and air fills', 99.00, 12, '10% discount on equipment purchases
5 free air fills per month
Priority booking for courses', 10.00, 0, 1, 5, 1, 1, NOW()),

('Silver Member', 'Great value for regular divers with additional rental benefits', 199.00, 12, '15% discount on equipment purchases
10 free air fills per month
Priority booking for courses and trips
2 free equipment rentals per month', 15.00, 2, 1, 10, 2, 1, NOW()),

('Gold Member', 'Premium membership for dedicated divers with extensive benefits', 349.00, 12, '20% discount on equipment purchases
Unlimited air fills
Priority booking for all services
5 free equipment rentals per month
10% discount on dive trips', 20.00, 5, 1, 999, 3, 1, NOW()),

('Platinum Member', 'VIP membership with unlimited access and maximum savings', 599.00, 12, '25% discount on equipment purchases
Unlimited air fills
VIP priority booking
Unlimited equipment rentals
20% discount on dive trips
Free annual equipment inspection', 25.00, 999, 1, 999, 4, 1, NOW())
ON DUPLICATE KEY UPDATE 
    description = VALUES(description),
    price = VALUES(price),
    benefits = VALUES(benefits),
    updated_at = NOW();

-- =====================================================
-- STOREFRONT SETTINGS (Optional - for theme customization)
-- =====================================================

INSERT INTO storefront_settings (setting_key, setting_value, category, created_at) VALUES
('show_search_bar', '1', 'general', NOW()),
('show_account_icon', '1', 'general', NOW()),
('show_cart_icon', '1', 'general', NOW()),
('show_newsletter_signup', '1', 'general', NOW()),
('show_social_links', '1', 'general', NOW()),
('show_payment_icons', '1', 'general', NOW()),
('header_style', 'sticky', 'general', NOW()),
('footer_style', 'detailed', 'general', NOW())
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW();

-- =====================================================
-- COMPLETION MESSAGE
-- =====================================================

SELECT 'Database seeding completed successfully!' as message;
SELECT COUNT(*) as system_settings_count FROM system_settings;
SELECT COUNT(*) as service_boxes_count FROM storefront_service_boxes WHERE is_active = 1;
SELECT COUNT(*) as membership_tiers_count FROM membership_tiers WHERE is_active = 1;
