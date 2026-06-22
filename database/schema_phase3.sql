-- OrbitPesa Phase 3 Schema Migrations
-- Run AFTER all previous schema files
-- Order: schema.sql → schema_admin.sql → schema_webhooks.sql → schema_wallet_users.sql
--        → schema_pockets.sql → schema_referrals.sql → schema_wallet_notifications.sql → (this file)

USE orbitpesa;

-- =============================================
-- FIX: Add mpesa_c2b to transactions channel ENUM
-- (C2B simulation was inserting an invalid ENUM value)
-- =============================================
ALTER TABLE transactions
  MODIFY COLUMN channel ENUM('mpesa','mpesa_c2b','card','wallet','bank','payment_link') NOT NULL;

-- =============================================
-- WALLET KYC DOCUMENTS
-- =============================================
CREATE TABLE IF NOT EXISTS wallet_kyc_documents (
    id              CHAR(36)        NOT NULL PRIMARY KEY,
    wallet_user_id  CHAR(36)        NOT NULL,
    doc_type        ENUM('national_id_front','national_id_back','passport','selfie') NOT NULL,
    file_path       VARCHAR(500)    NOT NULL,
    status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    admin_notes     TEXT            DEFAULT NULL,
    reviewed_by     CHAR(36)        DEFAULT NULL COMMENT 'admin id',
    reviewed_at     DATETIME        DEFAULT NULL,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_wkyc_user FOREIGN KEY (wallet_user_id) REFERENCES wallet_users(id) ON DELETE CASCADE,
    INDEX idx_wkyc_user   (wallet_user_id),
    INDEX idx_wkyc_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add kyc_status column to wallet_users if not already present
ALTER TABLE wallet_users
  ADD COLUMN IF NOT EXISTS kyc_status ENUM('unverified','pending','approved','rejected')
      NOT NULL DEFAULT 'unverified'
      AFTER status;

-- =============================================
-- MERCHANT DISPUTES (if not already created by schema_admin.sql)
-- Add missing columns to disputes table
-- =============================================
ALTER TABLE disputes
  ADD COLUMN IF NOT EXISTS transaction_amount DECIMAL(15,2) DEFAULT NULL AFTER transaction_ref,
  ADD COLUMN IF NOT EXISTS category VARCHAR(60) DEFAULT NULL AFTER reason;
