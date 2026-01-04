-- ==========================================
-- Migration: Create Storefront Theme System
-- Description: Themable online store configuration and settings
-- ==========================================

-- Storefront Settings (General store configuration)
CREATE TABLE IF NOT EXISTS storefront_settings (
    id INTEGER PRIMARY KEY ,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('text', 'textarea', 'boolean', 'number', 'json', 'image', 'color') DEFAULT 'text',
    category ENUM('general', 'seo', 'features', 'checkout', 'shipping', 'social', 'integrations') DEFAULT 'general',
    description TEXT,
    is_public BOOLEAN DEFAULT TRUE COMMENT 'Whether this setting is visible to public',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_key (setting_key)
);

-- Theme Configuration (Visual theming)
CREATE TABLE IF NOT EXISTS theme_config (
    id INTEGER PRIMARY KEY ,
    theme_name VARCHAR(100) NOT NULL,
    is_active BOOLEAN DEFAULT FALSE,
    is_default BOOLEAN DEFAULT FALSE,

    -- Colors
    primary_color VARCHAR(7) DEFAULT '#0d6efd',
    secondary_color VARCHAR(7) DEFAULT '#6c757d',
    accent_color VARCHAR(7) DEFAULT '#0dcaf0',
    success_color VARCHAR(7) DEFAULT '#198754',
    danger_color VARCHAR(7) DEFAULT '#dc3545',
    warning_color VARCHAR(7) DEFAULT '#ffc107',
    info_color VARCHAR(7) DEFAULT '#0dcaf0',
    dark_color VARCHAR(7) DEFAULT '#212529',
    light_color VARCHAR(7) DEFAULT '#f8f9fa',

    -- Background colors
    body_bg_color VARCHAR(7) DEFAULT '#ffffff',
    header_bg_color VARCHAR(7) DEFAULT '#ffffff',
    footer_bg_color VARCHAR(7) DEFAULT '#212529',
    hero_bg_color VARCHAR(7) DEFAULT '#01012e',

    -- Text colors
    text_color VARCHAR(7) DEFAULT '#212529',
    heading_color VARCHAR(7) DEFAULT '#000000',
    link_color VARCHAR(7) DEFAULT '#0d6efd',
    link_hover_color VARCHAR(7) DEFAULT '#0a58ca',

    -- Typography
    font_family_primary VARCHAR(200) DEFAULT 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
    font_family_heading VARCHAR(200) DEFAULT 'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
    font_size_base VARCHAR(10) DEFAULT '16px',
    font_size_heading_1 VARCHAR(10) DEFAULT '2.5rem',
    font_size_heading_2 VARCHAR(10) DEFAULT '2rem',
    font_size_heading_3 VARCHAR(10) DEFAULT '1.75rem',
    line_height DECIMAL(3,2) DEFAULT 1.5,

    -- Layout
    container_max_width VARCHAR(10) DEFAULT '1200px',
    border_radius VARCHAR(10) DEFAULT '0.375rem',
    spacing_unit VARCHAR(10) DEFAULT '1rem',

    -- Header/Navigation
    header_style ENUM('transparent', 'solid', 'gradient', 'sticky') DEFAULT 'solid',
    nav_position ENUM('top', 'side', 'both') DEFAULT 'top',
    show_search_bar BOOLEAN DEFAULT TRUE,
    show_cart_icon BOOLEAN DEFAULT TRUE,
    show_account_icon BOOLEAN DEFAULT TRUE,

    -- Hero Section
    hero_style ENUM('image', 'video', 'gradient', 'split', 'carousel') DEFAULT 'image',
    hero_height VARCHAR(10) DEFAULT '500px',
    hero_overlay_opacity DECIMAL(3,2) DEFAULT 0.5,
    show_hero_cta BOOLEAN DEFAULT TRUE,
    hero_cta_text VARCHAR(100) DEFAULT 'Shop Now',
    hero_cta_url VARCHAR(255) DEFAULT '/shop',

    -- Product Display
    products_per_row INT DEFAULT 4,
    product_card_style ENUM('classic', 'modern', 'minimal', 'overlay') DEFAULT 'classic',
    show_product_ratings BOOLEAN DEFAULT TRUE,
    show_product_quick_view BOOLEAN DEFAULT TRUE,
    show_add_to_cart_button BOOLEAN DEFAULT TRUE,
    show_wishlist_button BOOLEAN DEFAULT TRUE,

    -- Footer
    footer_style ENUM('simple', 'detailed', 'mega') DEFAULT 'detailed',
    show_newsletter_signup BOOLEAN DEFAULT TRUE,
    show_social_links BOOLEAN DEFAULT TRUE,
    show_payment_icons BOOLEAN DEFAULT TRUE,

    -- Custom CSS/JS
    custom_css TEXT COMMENT 'Custom CSS code',
    custom_js TEXT COMMENT 'Custom JavaScript code',
    custom_head_html TEXT COMMENT 'Custom HTML in <head>',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED,

    INDEX idx_active (is_active),
    INDEX idx_default (is_default)
);

-- Homepage Sections (Drag-and-drop homepage builder)
CREATE TABLE IF NOT EXISTS homepage_sections (
    id INTEGER PRIMARY KEY ,
    theme_id BIGINT UNSIGNED,
    section_type ENUM('hero', 'featured_products', 'categories', 'featured_categories', 'testimonials', 'blog_posts', 'brands', 'newsletter', 'custom_html', 'courses', 'trips', 'video', 'image_banner', 'countdown_timer') NOT NULL,
    section_title VARCHAR(200),
    section_subtitle TEXT,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,

    -- Section-specific configuration (JSON)
    config JSON COMMENT 'Section-specific settings',

    -- Styling
    background_color VARCHAR(7),
    text_color VARCHAR(7),
    padding_top VARCHAR(10) DEFAULT '3rem',
    padding_bottom VARCHAR(10) DEFAULT '3rem',
    background_image VARCHAR(255),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_theme (theme_id),
    INDEX idx_order (display_order),
    INDEX idx_active (is_active),
    FOREIGN KEY (theme_id) REFERENCES theme_config(id) ON DELETE CASCADE
);

-- Navigation Menus (Customizable navigation)
CREATE TABLE IF NOT EXISTS navigation_menus (
    id INTEGER PRIMARY KEY ,
    menu_location ENUM('header', 'footer', 'sidebar', 'mobile') NOT NULL,
    display_order INT DEFAULT 0,
    parent_id INTEGER NULL COMMENT 'For nested menus',

    label VARCHAR(100) NOT NULL,
    url VARCHAR(255),
    link_type ENUM('page', 'category', 'custom', 'shop', 'courses', 'trips', 'rentals') DEFAULT 'custom',
    link_target ENUM('_self', '_blank') DEFAULT '_self',

    icon_class VARCHAR(50) COMMENT 'Bootstrap icon or custom icon class',
    is_active BOOLEAN DEFAULT TRUE,

    -- Permissions
    requires_auth BOOLEAN DEFAULT FALSE,
    visible_to ENUM('all', 'customers', 'guests') DEFAULT 'all',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_location (menu_location),
    INDEX idx_parent (parent_id),
    INDEX idx_order (display_order),
    INDEX idx_active (is_active),
    FOREIGN KEY (parent_id) REFERENCES navigation_menus(id) ON DELETE CASCADE
);

-- Banners and Promotions (Site-wide promotional banners)
CREATE TABLE IF NOT EXISTS promotional_banners (
    id INTEGER PRIMARY KEY ,
    title VARCHAR(200) NOT NULL,
    content TEXT,
    banner_type ENUM('top_bar', 'hero', 'sidebar', 'popup', 'footer') DEFAULT 'top_bar',

    background_color VARCHAR(7) DEFAULT '#0d6efd',
    text_color VARCHAR(7) DEFAULT '#ffffff',

    link_url VARCHAR(255),
    link_text VARCHAR(100),

    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,

    -- Scheduling
    start_date TIMESTAMP,
    end_date TIMESTAMP,

    -- Targeting
    show_on_pages JSON COMMENT 'Array of page types to show on',
    show_to_user_types ENUM('all', 'guests', 'customers') DEFAULT 'all',

    -- Analytics
    view_count INT DEFAULT 0,
    click_count INT DEFAULT 0,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_by BIGINT UNSIGNED,

    INDEX idx_active (is_active),
    INDEX idx_dates (start_date, end_date),
    INDEX idx_type (banner_type)
);

-- Theme Assets (Images, fonts, files associated with themes)
CREATE TABLE IF NOT EXISTS theme_assets (
    id INTEGER PRIMARY KEY ,
    theme_id BIGINT UNSIGNED,
    asset_type ENUM('logo', 'favicon', 'hero_image', 'background', 'icon', 'font', 'other') NOT NULL,
    asset_name VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INTEGER COMMENT 'File size in bytes',
    mime_type VARCHAR(50),
    alt_text VARCHAR(255),
    is_primary BOOLEAN DEFAULT FALSE COMMENT 'Primary asset of this type',

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    uploaded_by BIGINT UNSIGNED,

    INDEX idx_theme (theme_id),
    INDEX idx_type (asset_type)
);

-- Insert default theme configuration (Ascuba-inspired)
INSERT INTO theme_config (
    theme_name, is_active, is_default,
    primary_color, secondary_color, accent_color, dark_color,
    body_bg_color, header_bg_color, footer_bg_color, hero_bg_color,
    text_color, heading_color, link_color,
    font_family_primary, font_family_heading,
    hero_style, hero_height, products_per_row,
    show_product_ratings, show_newsletter_signup, show_social_links
) VALUES (
    'Default Dive Shop', TRUE, TRUE,
    '#0d6efd', '#6c757d', '#0dcaf0', '#01012e',
    '#ffffff', '#ffffff', '#212529', '#01012e',
    '#212529', '#000000', '#0d6efd',
    'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
    'system-ui, -apple-system, "Segoe UI", Roboto, sans-serif',
    'image', '500px', 4,
    TRUE, TRUE, TRUE
);

-- Insert default storefront settings
INSERT INTO storefront_settings (setting_key, setting_value, setting_type, category, description) VALUES
('store_name', 'Nautilus Dive Shop', 'text', 'general', 'Online store name'),
('store_tagline', 'Your Premier Diving Equipment and Training Center', 'text', 'general', 'Store tagline/slogan'),
('store_description', 'Professional scuba diving equipment, certification courses, and unforgettable dive trips.', 'textarea', 'general', 'Store description for SEO'),
('contact_email', 'info@example.com', 'text', 'general', 'Customer service email'),
('contact_phone', '(555) 123-4567', 'text', 'general', 'Customer service phone'),
('store_address', '123 Ocean Drive, Miami, FL 33139', 'textarea', 'general', 'Physical store address'),

('enable_reviews', 'true', 'boolean', 'features', 'Enable product reviews'),
('enable_wishlist', 'true', 'boolean', 'features', 'Enable wishlist functionality'),
('enable_guest_checkout', 'true', 'boolean', 'features', 'Allow checkout without registration'),
('enable_live_chat', 'false', 'boolean', 'features', 'Enable live chat support'),
('show_stock_quantity', 'true', 'boolean', 'features', 'Display stock quantities to customers'),
('low_stock_threshold', '5', 'number', 'features', 'Show low stock warning below this quantity'),

('require_account_for_checkout', 'false', 'boolean', 'checkout', 'Require customer account to checkout'),
('enable_coupons', 'true', 'boolean', 'checkout', 'Enable coupon codes'),
('enable_gift_cards', 'true', 'boolean', 'checkout', 'Enable gift card payments'),
('tax_calculation_enabled', 'true', 'boolean', 'checkout', 'Enable automatic tax calculation'),
('default_tax_rate', '7.5', 'number', 'checkout', 'Default tax rate percentage'),

('free_shipping_threshold', '100', 'number', 'shipping', 'Free shipping on orders over this amount'),
('enable_local_pickup', 'true', 'boolean', 'shipping', 'Enable local pickup option'),
('enable_shipping_calculator', 'true', 'boolean', 'shipping', 'Show shipping calculator on cart page'),

('seo_meta_title', 'Nautilus Dive Shop - Scuba Equipment & Training', 'text', 'seo', 'SEO meta title'),
('seo_meta_description', 'Professional scuba diving equipment, PADI certification courses, and guided dive trips. Shop online or visit our store.', 'textarea', 'seo', 'SEO meta description'),
('seo_keywords', 'scuba diving, dive equipment, PADI courses, dive trips, snorkeling', 'textarea', 'seo', 'SEO keywords'),

('facebook_url', 'https://facebook.com/yourdiveshop', 'text', 'social', 'Facebook page URL'),
('instagram_url', 'https://instagram.com/yourdiveshop', 'text', 'social', 'Instagram profile URL'),
('twitter_url', '', 'text', 'social', 'Twitter/X profile URL'),
('youtube_url', '', 'text', 'social', 'YouTube channel URL'),

('google_analytics_id', '', 'text', 'integrations', 'Google Analytics tracking ID'),
('facebook_pixel_id', '', 'text', 'integrations', 'Facebook Pixel ID'),
('google_tag_manager_id', '', 'text', 'integrations', 'Google Tag Manager ID');

-- Insert default navigation menu
INSERT INTO navigation_menus (menu_location, display_order, label, url, link_type, is_active) VALUES
('header', 1, 'Home', '/', 'custom', TRUE),
('header', 2, 'Shop', '/shop', 'shop', TRUE),
('header', 3, 'Courses', '/courses', 'courses', TRUE),
('header', 4, 'Dive Trips', '/trips', 'trips', TRUE),
('header', 5, 'Equipment Rentals', '/rentals', 'rentals', TRUE),
('header', 6, 'About Us', '/about', 'custom', TRUE),
('header', 7, 'Contact', '/contact', 'custom', TRUE);

INSERT INTO navigation_menus (menu_location, display_order, label, url, link_type, is_active) VALUES
('footer', 1, 'Privacy Policy', '/privacy', 'custom', TRUE),
('footer', 2, 'Terms of Service', '/terms', 'custom', TRUE),
('footer', 3, 'Shipping & Returns', '/shipping', 'custom', TRUE),
('footer', 4, 'FAQs', '/faq', 'custom', TRUE);

-- Insert default homepage sections for the active theme
INSERT INTO homepage_sections (theme_id, section_type, section_title, section_subtitle, display_order, is_active, config) VALUES
(1, 'hero', 'Dive Into Adventure', 'Discover the underwater world with professional equipment and expert training', 1, TRUE, '{"cta_primary_text": "Shop Equipment", "cta_primary_url": "/shop", "cta_secondary_text": "View Courses", "cta_secondary_url": "/courses"}'),
(1, 'featured_categories', 'Shop by Category', 'Find everything you need for your diving adventures', 2, TRUE, '{"limit": 6, "show_product_count": true}'),
(1, 'featured_products', 'Featured Equipment', 'Top-rated gear trusted by professional divers', 3, TRUE, '{"limit": 8, "filter": "featured"}'),
(1, 'courses', 'PADI Certification Courses', 'Start your diving journey with professional training', 4, TRUE, '{"limit": 6, "show_schedule": true}'),
(1, 'trips', 'Upcoming Dive Trips', 'Join us for unforgettable underwater experiences', 5, TRUE, '{"limit": 3, "show_availability": true}'),
(1, 'brands', 'Trusted Brands', 'We carry the best brands in the industry', 6, TRUE, '{"show_logos": true}'),
(1, 'newsletter', 'Stay Connected', 'Get exclusive offers and diving tips delivered to your inbox', 7, TRUE, '{"show_social_links": true}');
