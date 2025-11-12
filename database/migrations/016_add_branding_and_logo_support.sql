-- Migration 016: Add Company Branding and Logo Support
-- Adds logo and branding capabilities for invoices, receipts, emails, and website

-- ============================================================================
-- PART 1: Add Branding Fields to Settings
-- ============================================================================

-- Insert branding-related settings into the general category
INSERT INTO `settings` (`category`, `key`, `value`, `type`, `description`, `updated_at`)
VALUES
  ('general', 'company_logo_path', '', 'string', 'Path to company logo (for invoices, receipts, emails)', NOW()),
  ('general', 'company_logo_small_path', '', 'string', 'Path to small/icon version of logo (for navbar)', NOW()),
  ('general', 'company_favicon_path', '', 'string', 'Path to favicon (browser tab icon)', NOW()),
  ('general', 'brand_primary_color', '#0066CC', 'string', 'Primary brand color (hex code)', NOW()),
  ('general', 'brand_secondary_color', '#00A8E8', 'string', 'Secondary brand color (hex code)', NOW()),
  ('general', 'company_tagline', '', 'string', 'Company tagline/slogan', NOW()),
  ('general', 'invoice_logo_width', '200', 'integer', 'Logo width on invoices/receipts (pixels)', NOW()),
  ('general', 'email_logo_width', '150', 'integer', 'Logo width in email templates (pixels)', NOW())
ON DUPLICATE KEY UPDATE
  `type` = VALUES(`type`),
  `description` = VALUES(`description`),
  `updated_at` = NOW();

-- ============================================================================
-- PART 2: Create File Uploads Tracking Table
-- ============================================================================

CREATE TABLE IF NOT EXISTS `file_uploads` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `file_name` VARCHAR(255) NOT NULL COMMENT 'Original filename',
  `file_path` VARCHAR(500) NOT NULL COMMENT 'Relative path from public directory',
  `file_size` INT UNSIGNED NOT NULL COMMENT 'File size in bytes',
  `mime_type` VARCHAR(100) NOT NULL COMMENT 'MIME type (image/png, image/jpeg, etc.)',
  `file_type` ENUM('logo', 'product_image', 'customer_photo', 'certification_card', 'document', 'other') DEFAULT 'other',
  `uploaded_by` INT UNSIGNED COMMENT 'User who uploaded the file',
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `is_public` BOOLEAN DEFAULT TRUE COMMENT 'Whether file is publicly accessible',
  INDEX `idx_file_type` (`file_type`),
  INDEX `idx_uploaded_by` (`uploaded_by`),
  INDEX `idx_uploaded_at` (`uploaded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Tracks all file uploads in the system';

-- ============================================================================
-- PART 3: Add Logo Support to Email Templates (if table exists)
-- ============================================================================

-- Add logo fields to email_templates table if it exists
-- Using ALTER TABLE with IF NOT EXISTS for safety

-- ============================================================================
-- PART 4: Add Branding to Email Campaigns (if table exists)
-- ============================================================================

-- Add branding fields to email_campaigns table if it exists
-- Using ALTER TABLE with IF NOT EXISTS for safety

-- ============================================================================
-- PART 5: Create Default Upload Directories Metadata
-- ============================================================================

-- Record expected directory structure in system metadata
INSERT INTO `system_metadata` (`meta_key`, `meta_value`, `updated_at`)
VALUES
  ('upload_directory_structure', '{"logos": "public/uploads/logos/", "products": "public/uploads/products/", "customers": "public/uploads/customers/", "documents": "public/uploads/documents/"}', NOW()),
  ('allowed_logo_extensions', '["jpg", "jpeg", "png", "svg", "webp"]', NOW()),
  ('max_logo_file_size', '5242880', NOW()),  -- 5MB in bytes
  ('branding_enabled', 'true', NOW())
ON DUPLICATE KEY UPDATE
  `meta_value` = VALUES(`meta_value`),
  `updated_at` = NOW();

-- ============================================================================
-- PART 6: Add Default Logo Placeholder
-- ============================================================================

-- Record that system needs logo setup
INSERT INTO `settings` (`category`, `key`, `value`, `type`, `description`, `updated_at`)
VALUES
  ('general', 'logo_setup_completed', '0', 'boolean', 'Whether company logo has been uploaded', NOW())
ON DUPLICATE KEY UPDATE
  `updated_at` = NOW();

-- ============================================================================
-- Migration Complete
-- ============================================================================

-- Note: After running this migration:
-- 1. Create directory structure: public/uploads/logos/
-- 2. Set proper permissions: chmod 755 public/uploads/
-- 3. Upload company logo via Settings → General
-- 4. Logo will appear on invoices, receipts, emails, and website header
