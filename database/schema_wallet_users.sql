-- OrbitPesa Wallet Users & Transactions Schema
-- Run BEFORE schema_pockets.sql, schema_referrals.sql, and schema_wallet_notifications.sql

USE orbitpesa;

-- =============================================
-- WALLET USERS
-- =============================================
CREATE TABLE IF NOT EXISTS wallet_users (
    id              CHAR(36)        NOT NULL PRIMARY KEY,
    wallet_id       VARCHAR(20)     NOT NULL UNIQUE,
    full_name       VARCHAR(150)    NOT NULL,
    email           VARCHAR(150)    NOT NULL UNIQUE,
    phone           VARCHAR(20)     NOT NULL UNIQUE,
    national_id     VARCHAR(50)     DEFAULT NULL,
    password        VARCHAR(255)    NOT NULL,
    pin_hash        VARCHAR(255)    NOT NULL,
    balance         DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
    status          ENUM('active','suspended') NOT NULL DEFAULT 'active',
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- WALLET TRANSACTIONS
-- =============================================
CREATE TABLE IF NOT EXISTS wallet_transactions (
    id                CHAR(36)        NOT NULL PRIMARY KEY,
    reference         VARCHAR(60)     NOT NULL UNIQUE,
    wallet_user_id    CHAR(36)        NOT NULL,
    type              VARCHAR(50)     NOT NULL,
    amount            DECIMAL(15,2)   NOT NULL,
    fee               DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
    balance_before    DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
    balance_after     DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
    counterparty      VARCHAR(255)    DEFAULT NULL,
    counterparty_name VARCHAR(255)    DEFAULT NULL,
    description       VARCHAR(255)    DEFAULT NULL,
    status            ENUM('pending','completed','failed','cancelled') NOT NULL DEFAULT 'completed',
    metadata          JSON            DEFAULT NULL,
    created_at        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_wt_user FOREIGN KEY (wallet_user_id) REFERENCES wallet_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
