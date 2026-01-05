-- Enable Bitcoin Payment by Default
-- Migration 098: Bitcoin settings

INSERT INTO system_settings (setting_key, setting_value, setting_type, description)
VALUES 
    ('bitcoin_enabled', '1', 'boolean', 'Enable Bitcoin payment option in POS'),
    ('bitcoin_wallet_address', '', 'string', 'Bitcoin wallet address for receiving payments'),
    ('bitcoin_confirmations_required', '1', 'integer', 'Number of confirmations required before payment is confirmed')
ON DUPLICATE KEY UPDATE 
    setting_value = VALUES(setting_value),
    description = VALUES(description);

-- Screen saver and auto-logout settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, description)
VALUES 
    ('auto_logout_enabled', '1', 'boolean', 'Enable auto-logout after inactivity'),
    ('auto_logout_minutes', '15', 'integer', 'Minutes of inactivity before auto-logout'),
    ('screensaver_enabled', '1', 'boolean', 'Enable screensaver mode on idle'),
    ('screensaver_minutes', '10', 'integer', 'Minutes before screensaver activates'),
    ('pin_unlock_enabled', '0', 'boolean', 'Allow PIN unlock instead of full login')
ON DUPLICATE KEY UPDATE 
    description = VALUES(description);
