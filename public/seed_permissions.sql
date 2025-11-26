-- Ensure permissions exist
INSERT IGNORE INTO permissions (name, slug, description) VALUES 
('View POS', 'pos.view', 'Access to Point of Sale system'),
('Create POS Transaction', 'pos.create', 'Ability to process sales'),
('Access Cash Drawer', 'pos.access', 'Open/Close cash drawer'),
('Void Transaction', 'pos.void', 'Void completed transactions'),
('Refund Transaction', 'pos.refund', 'Process refunds');

-- Get Admin Role ID
SET @admin_role_id = (SELECT id FROM roles WHERE name IN ('Admin', 'Administrator') LIMIT 1);

-- Assign permissions
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT @admin_role_id, id FROM permissions WHERE slug IN ('pos.view', 'pos.create', 'pos.access', 'pos.void', 'pos.refund');
