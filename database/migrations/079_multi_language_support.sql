-- ================================================
-- Nautilus - Multi-Language Support System
-- Migration: 079_multi_language_support.sql
-- Description: Comprehensive internationalization (i18n) system
-- ================================================

-- Supported Languages
CREATE TABLE IF NOT EXISTS `languages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `language_code` VARCHAR(10) NOT NULL COMMENT 'ISO 639-1 code (e.g., en, es, fr)',
    `language_name` VARCHAR(100) NOT NULL COMMENT 'English name',
    `native_name` VARCHAR(100) NOT NULL COMMENT 'Native name (e.g., Español)',
    `locale_code` VARCHAR(20) NOT NULL COMMENT 'Full locale (e.g., en_US, es_MX)',

    -- Regional Settings
    `country_code` VARCHAR(2) NULL COMMENT 'ISO 3166-1 alpha-2',
    `direction` ENUM('ltr', 'rtl') DEFAULT 'ltr',

    -- Display
    `flag_emoji` VARCHAR(10) NULL,
    `flag_icon_path` VARCHAR(255) NULL,

    -- Status
    `is_active` BOOLEAN DEFAULT TRUE,
    `is_default` BOOLEAN DEFAULT FALSE,
    `is_rtl` BOOLEAN DEFAULT FALSE,

    -- Completion Status
    `translation_progress_percent` DECIMAL(5,2) DEFAULT 0,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `unique_language_code` (`language_code`),
    UNIQUE KEY `unique_locale` (`locale_code`),
    INDEX `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed common languages
INSERT INTO `languages` (`language_code`, `language_name`, `native_name`, `locale_code`, `country_code`, `flag_emoji`, `is_active`, `is_default`) VALUES
('en', 'English', 'English', 'en_US', 'US', '🇺🇸', TRUE, TRUE),
('es', 'Spanish', 'Español', 'es_ES', 'ES', '🇪🇸', TRUE, FALSE),
('fr', 'French', 'Français', 'fr_FR', 'FR', '🇫🇷', TRUE, FALSE),
('de', 'German', 'Deutsch', 'de_DE', 'DE', '🇩🇪', TRUE, FALSE),
('it', 'Italian', 'Italiano', 'it_IT', 'IT', '🇮🇹', TRUE, FALSE),
('pt', 'Portuguese', 'Português', 'pt_BR', 'BR', '🇧🇷', TRUE, FALSE),
('ja', 'Japanese', '日本語', 'ja_JP', 'JP', '🇯🇵', TRUE, FALSE),
('zh', 'Chinese (Simplified)', '简体中文', 'zh_CN', 'CN', '🇨🇳', TRUE, FALSE),
('ko', 'Korean', '한국어', 'ko_KR', 'KR', '🇰🇷', TRUE, FALSE),
('ar', 'Arabic', 'العربية', 'ar_SA', 'SA', '🇸🇦', TRUE, FALSE),
('ru', 'Russian', 'Русский', 'ru_RU', 'RU', '🇷🇺', TRUE, FALSE),
('th', 'Thai', 'ไทย', 'th_TH', 'TH', '🇹🇭', TRUE, FALSE),
('id', 'Indonesian', 'Bahasa Indonesia', 'id_ID', 'ID', '🇮🇩', TRUE, FALSE),
('nl', 'Dutch', 'Nederlands', 'nl_NL', 'NL', '🇳🇱', TRUE, FALSE),
('sv', 'Swedish', 'Svenska', 'sv_SE', 'SE', '🇸🇪', TRUE, FALSE);

-- Translation Strings
CREATE TABLE IF NOT EXISTS `translations` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Translation Key
    `translation_key` VARCHAR(255) NOT NULL COMMENT 'Unique key (e.g., menu.dashboard, button.save)',
    `language_code` VARCHAR(10) NOT NULL,

    -- Translation Content
    `translated_text` TEXT NOT NULL,
    `context` VARCHAR(500) NULL COMMENT 'Context/usage note for translators',

    -- Category
    `category` VARCHAR(100) NULL COMMENT 'UI, email, report, etc.',
    `module` VARCHAR(100) NULL COMMENT 'pos, crm, inventory, etc.',

    -- Status
    `is_verified` BOOLEAN DEFAULT FALSE COMMENT 'Reviewed by native speaker',
    `needs_review` BOOLEAN DEFAULT FALSE,

    -- Metadata
    `translated_by` VARCHAR(100) NULL,
    `verified_by` VARCHAR(100) NULL,
    `verified_at` TIMESTAMP NULL,

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`language_code`) REFERENCES `languages`(`language_code`) ON DELETE CASCADE,

    UNIQUE KEY `unique_translation` (`translation_key`, `language_code`),
    INDEX `idx_language` (`language_code`),
    INDEX `idx_category` (`category`),
    INDEX `idx_module` (`module`),
    INDEX `idx_needs_review` (`needs_review`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Language Preferences
CREATE TABLE IF NOT EXISTS `user_language_preferences` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `user_id` INT UNSIGNED NOT NULL,
    `language_code` VARCHAR(10) NOT NULL,

    -- Regional Preferences
    `date_format` VARCHAR(50) DEFAULT 'MM/DD/YYYY',
    `time_format` VARCHAR(50) DEFAULT '12h',
    `timezone` VARCHAR(100) DEFAULT 'UTC',
    `currency` VARCHAR(3) DEFAULT 'USD',
    `number_format` VARCHAR(50) DEFAULT 'en_US' COMMENT 'Locale for number formatting',

    -- Units
    `measurement_system` ENUM('metric', 'imperial') DEFAULT 'imperial',
    `temperature_unit` ENUM('fahrenheit', 'celsius') DEFAULT 'fahrenheit',
    `pressure_unit` ENUM('psi', 'bar') DEFAULT 'psi',
    `depth_unit` ENUM('feet', 'meters') DEFAULT 'feet',

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`language_code`) REFERENCES `languages`(`language_code`) ON DELETE CASCADE,

    UNIQUE KEY `unique_user_language` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Customer Language Preferences
CREATE TABLE IF NOT EXISTS `customer_language_preferences` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    `customer_id` INT UNSIGNED NOT NULL,
    `language_code` VARCHAR(10) NOT NULL,

    -- Communication Preferences
    `email_language` VARCHAR(10) NULL COMMENT 'Preferred language for emails',
    `sms_language` VARCHAR(10) NULL,

    -- Regional
    `timezone` VARCHAR(100) DEFAULT 'UTC',
    `currency` VARCHAR(3) DEFAULT 'USD',

    -- Units (for dive computers, logs, etc.)
    `measurement_system` ENUM('metric', 'imperial') DEFAULT 'imperial',
    `temperature_unit` ENUM('fahrenheit', 'celsius') DEFAULT 'fahrenheit',
    `pressure_unit` ENUM('psi', 'bar') DEFAULT 'psi',
    `depth_unit` ENUM('feet', 'meters') DEFAULT 'feet',

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`language_code`) REFERENCES `languages`(`language_code`) ON DELETE CASCADE,

    UNIQUE KEY `unique_customer_language` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Translatable Content (for dynamic content like courses, products)
CREATE TABLE IF NOT EXISTS `translatable_content` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Source Content
    `entity_type` VARCHAR(100) NOT NULL COMMENT 'product, course, trip, etc.',
    `entity_id` INT UNSIGNED NOT NULL,
    `field_name` VARCHAR(100) NOT NULL COMMENT 'name, description, etc.',

    -- Translation
    `language_code` VARCHAR(10) NOT NULL,
    `translated_content` TEXT NOT NULL,

    -- Status
    `is_auto_translated` BOOLEAN DEFAULT FALSE,
    `translation_service` VARCHAR(50) NULL COMMENT 'google, deepl, manual',

    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (`language_code`) REFERENCES `languages`(`language_code`) ON DELETE CASCADE,

    UNIQUE KEY `unique_entity_translation` (`entity_type`, `entity_id`, `field_name`, `language_code`),
    INDEX `idx_entity` (`entity_type`, `entity_id`),
    INDEX `idx_language` (`language_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed common UI translations for English and Spanish
INSERT INTO `translations` (`translation_key`, `language_code`, `translated_text`, `category`, `module`) VALUES
-- English
('menu.dashboard', 'en', 'Dashboard', 'UI', 'navigation'),
('menu.customers', 'en', 'Customers', 'UI', 'navigation'),
('menu.products', 'en', 'Products', 'UI', 'navigation'),
('menu.courses', 'en', 'Courses', 'UI', 'navigation'),
('menu.trips', 'en', 'Trips', 'UI', 'navigation'),
('menu.rentals', 'en', 'Rentals', 'UI', 'navigation'),
('menu.pos', 'en', 'Point of Sale', 'UI', 'navigation'),
('button.save', 'en', 'Save', 'UI', 'common'),
('button.cancel', 'en', 'Cancel', 'UI', 'common'),
('button.delete', 'en', 'Delete', 'UI', 'common'),
('button.edit', 'en', 'Edit', 'UI', 'common'),
('button.add', 'en', 'Add', 'UI', 'common'),
('button.search', 'en', 'Search', 'UI', 'common'),

-- Spanish
('menu.dashboard', 'es', 'Panel de Control', 'UI', 'navigation'),
('menu.customers', 'es', 'Clientes', 'UI', 'navigation'),
('menu.products', 'es', 'Productos', 'UI', 'navigation'),
('menu.courses', 'es', 'Cursos', 'UI', 'navigation'),
('menu.trips', 'es', 'Viajes', 'UI', 'navigation'),
('menu.rentals', 'es', 'Alquileres', 'UI', 'navigation'),
('menu.pos', 'es', 'Punto de Venta', 'UI', 'navigation'),
('button.save', 'es', 'Guardar', 'UI', 'common'),
('button.cancel', 'es', 'Cancelar', 'UI', 'common'),
('button.delete', 'es', 'Eliminar', 'UI', 'common'),
('button.edit', 'es', 'Editar', 'UI', 'common'),
('button.add', 'es', 'Agregar', 'UI', 'common'),
('button.search', 'es', 'Buscar', 'UI', 'common');
