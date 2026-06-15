-- Referral & Cashback system for consumer wallet users

ALTER TABLE wallet_users
  ADD COLUMN IF NOT EXISTS referral_code VARCHAR(10) DEFAULT NULL UNIQUE,
  ADD COLUMN IF NOT EXISTS referred_by   CHAR(36)    DEFAULT NULL;

CREATE TABLE IF NOT EXISTS wallet_referrals (
    id              CHAR(36)        NOT NULL PRIMARY KEY,
    referrer_id     CHAR(36)        NOT NULL COMMENT 'User who shared the code',
    referred_id     CHAR(36)        NOT NULL COMMENT 'User who registered with the code',
    status          ENUM('pending','completed') NOT NULL DEFAULT 'pending',
    referrer_bonus  DECIMAL(10,2)   NOT NULL DEFAULT 50.00,
    referred_bonus  DECIMAL(10,2)   NOT NULL DEFAULT 25.00,
    completed_at    DATETIME        DEFAULT NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_referred (referred_id),
    INDEX idx_referrer (referrer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
