-- Migration: Fix Promotional Banners Schema
-- Description: Renames link_text/url to button_text/url and updates banner_type enum to match code expectations.

ALTER TABLE promotional_banners
    CHANGE COLUMN link_text button_text VARCHAR(100) NULL,
    CHANGE COLUMN link_url button_url VARCHAR(255) NULL,
    MODIFY COLUMN banner_type ENUM('info', 'warning', 'success', 'danger', 'promotion', 'top_bar', 'hero', 'sidebar', 'popup', 'footer') DEFAULT 'info';
