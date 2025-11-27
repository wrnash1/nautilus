-- Audit Trail System
-- Comprehensive audit logging for compliance and security

-- Main Audit Log Table
CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT  PRIMARY KEY,
    tenant_id INT,
    user_id INT,
    action VARCHAR(100) NOT NULL COMMENT 'Action performed (create, update, delete, login, etc.)',
    entity_type VARCHAR(100) NOT NULL COMMENT 'Type of entity affected (product, customer, user, etc.)',
    entity_id INT COMMENT 'ID of the affected entity',
    old_values JSON COMMENT 'Previous values before change',
    new_values JSON COMMENT 'New values after change',
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    additional_data JSON COMMENT 'Any additional context data',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at),
    INDEX idx_ip_address (ip_address)
);

-- Data Access Log (track who viewed sensitive data)
CREATE TABLE IF NOT EXISTS data_access_log (
    id BIGINT  PRIMARY KEY,
    tenant_id INT,
    user_id INT NOT NULL,
    resource_type VARCHAR(100) NOT NULL COMMENT 'Type of resource accessed',
    resource_id INT NOT NULL,
    access_type ENUM('view', 'export', 'print') DEFAULT 'view',
    ip_address VARCHAR(45),
    user_agent VARCHAR(500),
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_accessed_at (accessed_at)
);

-- Login History (detailed login tracking)
CREATE TABLE IF NOT EXISTS login_history (
    id BIGINT  PRIMARY KEY,
    tenant_id INT,
    user_id INT,
    username VARCHAR(255),
    login_status ENUM('success', 'failed', 'blocked') NOT NULL,
    failure_reason VARCHAR(255) COMMENT 'Reason for failed login',
    ip_address VARCHAR(45) NOT NULL,
    user_agent VARCHAR(500),
    location_data JSON COMMENT 'Geolocation data if available',
    session_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_user_id (user_id),
    INDEX idx_login_status (login_status),
    INDEX idx_ip_address (ip_address),
    INDEX idx_created_at (created_at)
);

-- System Events Log (system-level events)
CREATE TABLE IF NOT EXISTS system_events_log (
    id BIGINT  PRIMARY KEY,
    tenant_id INT,
    event_type VARCHAR(100) NOT NULL COMMENT 'backup, maintenance, error, etc.',
    event_level ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
    message TEXT NOT NULL,
    details JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_event_type (event_type),
    INDEX idx_event_level (event_level),
    INDEX idx_created_at (created_at)
);

-- Audit Report Templates
CREATE TABLE IF NOT EXISTS audit_report_templates (
    id INT  PRIMARY KEY,
    tenant_id INT,
    template_name VARCHAR(100) NOT NULL,
    description TEXT,
    report_type ENUM('security', 'data_access', 'user_activity', 'system', 'compliance', 'custom') DEFAULT 'custom',
    filters JSON NOT NULL COMMENT 'Predefined filters for the report',
    columns JSON COMMENT 'Which columns to include',
    is_scheduled BOOLEAN DEFAULT FALSE,
    schedule_frequency ENUM('daily', 'weekly', 'monthly') NULL,
    recipients JSON COMMENT 'Email addresses to send scheduled reports',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_report_type (report_type),
    INDEX idx_is_scheduled (is_scheduled)
);

-- Compliance Snapshots (point-in-time compliance reports)
CREATE TABLE IF NOT EXISTS compliance_snapshots (
    id INT  PRIMARY KEY,
    tenant_id INT,
    snapshot_date DATE NOT NULL,
    compliance_type VARCHAR(100) NOT NULL COMMENT 'GDPR, HIPAA, SOX, etc.',
    status ENUM('compliant', 'non_compliant', 'review_needed') DEFAULT 'review_needed',
    findings JSON COMMENT 'Compliance check results',
    recommendations TEXT,
    reviewed_by INT,
    reviewed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant_id (tenant_id),
    INDEX idx_snapshot_date (snapshot_date),
    INDEX idx_compliance_type (compliance_type),
    INDEX idx_status (status)
);

-- Create indexes for performance on existing tables if needed
-- These help with audit queries that join to other tables

-- Index on permission_audit_log if it exists (from previous migration)
CREATE INDEX IF NOT EXISTS idx_permission_audit_action ON permission_audit_log(action);
CREATE INDEX IF NOT EXISTS idx_permission_audit_created ON permission_audit_log(created_at);

-- Insert sample audit report templates
INSERT INTO audit_report_templates (tenant_id, template_name, description, report_type, filters, is_scheduled)
VALUES
(NULL, 'Security Events - Last 30 Days', 'All security-related events in the last month', 'security',
 '{"days": 30, "actions": ["user_login", "login_failed", "password_changed", "role_assigned"]}', FALSE),

(NULL, 'Failed Login Attempts', 'All failed login attempts', 'security',
 '{"action": "login_failed", "days": 7}', FALSE),

(NULL, 'Data Access Report', 'Who accessed what sensitive data', 'data_access',
 '{"days": 30, "resource_types": ["customer", "transaction"]}', FALSE),

(NULL, 'User Activity Summary', 'Summary of all user activities', 'user_activity',
 '{"days": 30}', FALSE),

(NULL, 'System Events', 'System-level events and errors', 'system',
 '{"days": 7, "event_levels": ["error", "critical"]}', FALSE);
