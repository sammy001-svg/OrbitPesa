-- OrbitPesa Admin Schema Extension
-- Run AFTER schema.sql

USE orbitpesa;

-- =============================================
-- ADMINS
-- =============================================
CREATE TABLE IF NOT EXISTS admins (
    id         CHAR(36)     NOT NULL PRIMARY KEY,
    name       VARCHAR(120) NOT NULL,
    email      VARCHAR(150) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('super_admin','admin','support') NOT NULL DEFAULT 'admin',
    is_active  TINYINT(1)   NOT NULL DEFAULT 1,
    last_login DATETIME     NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB;

-- =============================================
-- KYC DOCUMENTS
-- =============================================
CREATE TABLE IF NOT EXISTS kyc_documents (
    id           CHAR(36)     NOT NULL PRIMARY KEY,
    user_id      CHAR(36)     NOT NULL,
    doc_type     ENUM('national_id','passport','business_reg','bank_statement','utility_bill') NOT NULL,
    file_path    VARCHAR(500) NOT NULL,
    status       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    reviewed_by  CHAR(36)     NULL COMMENT 'admin id',
    review_notes TEXT         NULL,
    reviewed_at  DATETIME     NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user   (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =============================================
-- FEE CONFIGURATION
-- =============================================
CREATE TABLE IF NOT EXISTS fee_config (
    id           CHAR(36)      NOT NULL PRIMARY KEY,
    channel      VARCHAR(50)   NOT NULL UNIQUE,
    fee_type     ENUM('flat','percentage','combined') NOT NULL DEFAULT 'combined',
    flat_fee     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    percentage   DECIMAL(5,4)  NOT NULL DEFAULT 0.0000,
    min_fee      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    max_fee      DECIMAL(10,2) NULL,
    is_active    TINYINT(1)    NOT NULL DEFAULT 1,
    updated_at   DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO fee_config (id, channel, fee_type, flat_fee, percentage, min_fee, max_fee) VALUES
(UUID(), 'mpesa',        'combined',   5.00, 0.0150, 5.00,  500.00),
(UUID(), 'card',         'percentage', 0.00, 0.0290, 30.00, NULL),
(UUID(), 'wallet',       'flat',       0.00, 0.0000, 0.00,  0.00),
(UUID(), 'payment_link', 'combined',   5.00, 0.0150, 5.00,  500.00),
(UUID(), 'bank',         'flat',      30.00, 0.0000, 30.00, 30.00);

-- =============================================
-- SYSTEM SETTINGS
-- =============================================
CREATE TABLE IF NOT EXISTS system_settings (
    `key`       VARCHAR(100) NOT NULL PRIMARY KEY,
    `value`     TEXT         NULL,
    `type`      ENUM('string','boolean','integer','json') NOT NULL DEFAULT 'string',
    description VARCHAR(255) NULL,
    updated_at  DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO system_settings (`key`, `value`, `type`, description) VALUES
('maintenance_mode',       '0',              'boolean', 'Put site in maintenance mode'),
('new_registrations',      '1',              'boolean', 'Allow new merchant registrations'),
('kyc_required_for_live',  '1',              'boolean', 'Require KYC before enabling live payments'),
('max_daily_withdrawal',   '1000000',        'integer', 'Maximum daily withdrawal per merchant (KES)'),
('min_withdrawal_amount',  '100',            'integer', 'Minimum withdrawal amount (KES)'),
('support_email',          'support@orbitpesa.com', 'string', 'Customer support email'),
('company_name',           'OrbitPesa Ltd',  'string',  'Legal company name'),
('mpesa_sandbox',          '1',              'boolean', 'Use M-Pesa sandbox environment'),
('settlement_days',        '1',             'integer', 'Days to settle to bank (0=instant)');

-- =============================================
-- ADMIN ACTIVITY LOG
-- =============================================
CREATE TABLE IF NOT EXISTS admin_logs (
    id          CHAR(36)     NOT NULL PRIMARY KEY,
    admin_id    CHAR(36)     NOT NULL,
    action      VARCHAR(100) NOT NULL,
    description TEXT         NULL,
    target_type VARCHAR(50)  NULL,
    target_id   VARCHAR(50)  NULL,
    ip_address  VARCHAR(45)  NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_admin  (admin_id),
    INDEX idx_target (target_type, target_id)
) ENGINE=InnoDB;

-- =============================================
-- DISPUTES / CHARGEBACKS
-- =============================================
CREATE TABLE IF NOT EXISTS disputes (
    id             CHAR(36)     NOT NULL PRIMARY KEY,
    transaction_ref VARCHAR(60) NOT NULL,
    user_id        CHAR(36)     NOT NULL,
    reason         TEXT         NOT NULL,
    status         ENUM('open','under_review','resolved','closed') NOT NULL DEFAULT 'open',
    resolution     TEXT         NULL,
    resolved_by    CHAR(36)     NULL,
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =============================================
-- SEED: Super Admin
-- =============================================
INSERT IGNORE INTO admins (id, name, email, password, role) VALUES
('AAAAAAAA-0000-0000-0000-000000000001', 'Super Admin', 'admin@orbitpesa.com',
 '$2y$12$Tx3eqks2iiSH/H8NpKtVke87vvsgZEtwh2deLVSIktI8wR8RYY1Tu', 'super_admin');
-- Password: password
