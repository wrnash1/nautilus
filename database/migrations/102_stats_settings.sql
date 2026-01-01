-- Add settings for Store Stats
-- Migration: 102_stats_settings.sql

INSERT IGNORE INTO `storefront_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`, `is_public`) VALUES
('founding_year', '2010', 'number', 'general', 'Year the business was established', TRUE),
('social_google_rating', '4.9', 'number', 'social', 'Google Reviews Rating', TRUE),
('social_google_review_count', '1250', 'number', 'social', 'Number of Google Reviews', TRUE);
