-- Newsletter Subscriptions Table
-- Manages email newsletter subscriptions

CREATE TABLE IF NOT EXISTS newsletter_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT UNSIGNED NOT NULL,

    -- Subscriber Information
    email VARCHAR(255) NOT NULL,
    name VARCHAR(255),

    -- Subscription Management
    confirm_token VARCHAR(255),
    is_active BOOLEAN DEFAULT 1,

    -- Timestamps
    subscribed_at TIMESTAMP NULL,
    confirmed_at TIMESTAMP NULL,
    unsubscribed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_email (tenant_id, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes for faster lookups
CREATE INDEX idx_newsletter_tenant ON newsletter_subscriptions(tenant_id);
CREATE INDEX idx_newsletter_email ON newsletter_subscriptions(email);
CREATE INDEX idx_newsletter_token ON newsletter_subscriptions(confirm_token);
CREATE INDEX idx_newsletter_active ON newsletter_subscriptions(is_active);
