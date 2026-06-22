-- OrbitPesa Payment Gateway Database Schema
-- MySQL 8.0+
-- Created: 2026-06-14

CREATE DATABASE IF NOT EXISTS orbitpesa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE orbitpesa;

-- =============================================
-- USERS (Merchants)
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id            CHAR(36)     NOT NULL PRIMARY KEY,
    business_name VARCHAR(150) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    phone         VARCHAR(20)  NOT NULL,
    password      VARCHAR(255) NOT NULL,
    account_type  ENUM('personal','business') NOT NULL DEFAULT 'business',
    status        ENUM('active','suspended','pending') NOT NULL DEFAULT 'active',
    kyc_status    ENUM('unverified','pending','verified') NOT NULL DEFAULT 'unverified',
    avatar        VARCHAR(255) NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB;

-- =============================================
-- WALLETS
-- =============================================
CREATE TABLE IF NOT EXISTS wallets (
    id         CHAR(36)       NOT NULL PRIMARY KEY,
    user_id    CHAR(36)       NOT NULL UNIQUE,
    balance    DECIMAL(18,2)  NOT NULL DEFAULT 0.00,
    currency   CHAR(3)        NOT NULL DEFAULT 'KES',
    created_at DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME       NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- WALLET LEDGER (Audit trail)
-- =============================================
CREATE TABLE IF NOT EXISTS wallet_ledger (
    id            CHAR(36)      NOT NULL PRIMARY KEY,
    user_id       CHAR(36)      NOT NULL,
    type          ENUM('credit','debit') NOT NULL,
    amount        DECIMAL(18,2) NOT NULL,
    balance_after DECIMAL(18,2) NOT NULL,
    description   VARCHAR(255)  NOT NULL,
    reference     VARCHAR(100)  NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_created (user_id, created_at)
) ENGINE=InnoDB;

-- =============================================
-- TRANSACTIONS
-- =============================================
CREATE TABLE IF NOT EXISTS transactions (
    id          CHAR(36)      NOT NULL PRIMARY KEY,
    user_id     CHAR(36)      NOT NULL,
    reference   VARCHAR(60)   NOT NULL UNIQUE,
    amount      DECIMAL(18,2) NOT NULL,
    fee         DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    net_amount  DECIMAL(18,2) GENERATED ALWAYS AS (amount - fee) STORED,
    currency    CHAR(3)       NOT NULL DEFAULT 'KES',
    channel     ENUM('mpesa','mpesa_c2b','card','wallet','bank','payment_link') NOT NULL,
    phone       VARCHAR(20)   NULL,
    card_last4  CHAR(4)       NULL,
    description TEXT          NULL,
    status      ENUM('pending','processing','completed','failed','reversed') NOT NULL DEFAULT 'pending',
    provider_ref VARCHAR(100) NULL COMMENT 'M-Pesa/Stripe reference',
    metadata    JSON          NULL,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_status (user_id, status),
    INDEX idx_reference (reference),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- =============================================
-- PAYMENT LINKS
-- =============================================
CREATE TABLE IF NOT EXISTS payment_links (
    id              CHAR(36)      NOT NULL PRIMARY KEY,
    user_id         CHAR(36)      NOT NULL,
    slug            VARCHAR(20)   NOT NULL UNIQUE,
    title           VARCHAR(150)  NOT NULL,
    description     TEXT          NULL,
    amount          DECIMAL(18,2) NULL COMMENT 'NULL = customer enters amount',
    currency        CHAR(3)       NOT NULL DEFAULT 'KES',
    is_fixed_amount TINYINT(1)    NOT NULL DEFAULT 1,
    max_uses        INT           NULL COMMENT 'NULL = unlimited',
    expires_at      DATETIME      NULL,
    status          ENUM('active','inactive','expired') NOT NULL DEFAULT 'active',
    created_at      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_slug (slug),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- =============================================
-- API KEYS
-- =============================================
CREATE TABLE IF NOT EXISTS api_keys (
    id           CHAR(36)     NOT NULL PRIMARY KEY,
    user_id      CHAR(36)     NOT NULL,
    label        VARCHAR(100) NOT NULL,
    key_value    VARCHAR(100) NOT NULL UNIQUE,
    environment  ENUM('test','live') NOT NULL DEFAULT 'test',
    is_active    TINYINT(1)   NOT NULL DEFAULT 1,
    last_used_at DATETIME     NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_key (key_value),
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- =============================================
-- WEBHOOKS
-- =============================================
CREATE TABLE IF NOT EXISTS webhooks (
    id         CHAR(36)     NOT NULL PRIMARY KEY,
    user_id    CHAR(36)     NOT NULL,
    url        VARCHAR(500) NOT NULL,
    events     JSON         NOT NULL COMMENT 'Array of subscribed events',
    secret     VARCHAR(100) NOT NULL,
    is_active  TINYINT(1)   NOT NULL DEFAULT 1,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- WEBHOOK LOGS
-- =============================================
CREATE TABLE IF NOT EXISTS webhook_logs (
    id            CHAR(36) NOT NULL PRIMARY KEY,
    webhook_id    CHAR(36) NOT NULL,
    event         VARCHAR(100) NOT NULL,
    payload       JSON         NOT NULL,
    response_code INT          NULL,
    response_body TEXT         NULL,
    delivered     TINYINT(1)   NOT NULL DEFAULT 0,
    attempts      INT          NOT NULL DEFAULT 0,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (webhook_id) REFERENCES webhooks(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- MPESA STK PUSH REQUESTS
-- =============================================
CREATE TABLE IF NOT EXISTS mpesa_requests (
    id               CHAR(36)     NOT NULL PRIMARY KEY,
    transaction_ref  VARCHAR(60)  NOT NULL,
    checkout_request_id VARCHAR(100) NULL,
    merchant_request_id VARCHAR(100) NULL,
    phone            VARCHAR(20)  NOT NULL,
    amount           DECIMAL(18,2) NOT NULL,
    status           ENUM('pending','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
    result_code      VARCHAR(10)  NULL,
    result_desc      TEXT         NULL,
    mpesa_receipt    VARCHAR(50)  NULL,
    created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME     NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_checkout (checkout_request_id),
    INDEX idx_txn_ref (transaction_ref)
) ENGINE=InnoDB;

-- =============================================
-- WITHDRAWALS
-- =============================================
CREATE TABLE IF NOT EXISTS withdrawals (
    id           CHAR(36)      NOT NULL PRIMARY KEY,
    user_id      CHAR(36)      NOT NULL,
    amount       DECIMAL(18,2) NOT NULL,
    fee          DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    channel      ENUM('mpesa','bank') NOT NULL,
    destination  VARCHAR(255)  NOT NULL,
    status       ENUM('pending','processing','completed','failed') NOT NULL DEFAULT 'pending',
    reference    VARCHAR(60)   NOT NULL UNIQUE,
    created_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME      NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id)
) ENGINE=InnoDB;

-- =============================================
-- NOTIFICATIONS (Merchant)
-- =============================================
CREATE TABLE IF NOT EXISTS notifications (
    id              CHAR(36)        NOT NULL PRIMARY KEY,
    user_id         CHAR(36)        NOT NULL,
    type            VARCHAR(50)     NOT NULL DEFAULT 'system',
    title           VARCHAR(150)    NOT NULL,
    body            TEXT            NOT NULL,
    url             VARCHAR(255)    DEFAULT NULL,
    is_read         TINYINT(1)      NOT NULL DEFAULT 0,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_n_user_read (user_id, is_read, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- MPESA ACCOUNTS (Applications)
-- =============================================
CREATE TABLE IF NOT EXISTS mpesa_accounts (
    id                  CHAR(36)        NOT NULL PRIMARY KEY,
    user_id             CHAR(36)        DEFAULT NULL,
    application_type    VARCHAR(50)     NOT NULL,
    business_name       VARCHAR(150)    NOT NULL,
    business_reg_no     VARCHAR(100)    DEFAULT NULL,
    contact_name        VARCHAR(150)    NOT NULL,
    contact_email       VARCHAR(150)    NOT NULL,
    contact_phone       VARCHAR(20)     NOT NULL,
    business_type       VARCHAR(50)     NOT NULL,
    monthly_volume      VARCHAR(50)     NOT NULL,
    description         TEXT            DEFAULT NULL,
    account_number      VARCHAR(50)     DEFAULT NULL,
    status              ENUM('pending','under_review','approved','rejected') NOT NULL DEFAULT 'pending',
    reviewed_by         CHAR(36)        DEFAULT NULL,
    reviewed_at         DATETIME        DEFAULT NULL,
    admin_notes         TEXT            DEFAULT NULL,
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_mpesa_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- SEED: Demo user
-- =============================================
INSERT IGNORE INTO users (id, business_name, email, phone, password, account_type, status, kyc_status) VALUES
('11111111-0000-0000-0000-000000000001', 'Demo Business', 'demo@orbitpesa.com', '0712000000',
 '$2y$12$Tx3eqks2iiSH/H8NpKtVke87vvsgZEtwh2deLVSIktI8wR8RYY1Tu', 'business', 'active', 'verified');
-- Password: password

INSERT IGNORE INTO wallets (id, user_id, balance, currency) VALUES
('22222222-0000-0000-0000-000000000001', '11111111-0000-0000-0000-000000000001', 24850.00, 'KES');

INSERT IGNORE INTO api_keys (id, user_id, label, key_value, environment, is_active) VALUES
('33333333-0000-0000-0000-000000000001', '11111111-0000-0000-0000-000000000001',
 'Test Key', 'op_test_demo1234567890abcdef12345678901234567890abcd', 'test', 1),
('33333333-0000-0000-0000-000000000002', '11111111-0000-0000-0000-000000000001',
 'Live Key', 'op_live_demo1234567890abcdef12345678901234567890abcd', 'live', 1);
