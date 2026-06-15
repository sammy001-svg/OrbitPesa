-- Savings Pockets for consumer wallet users

CREATE TABLE IF NOT EXISTS wallet_pockets (
    id              CHAR(36)        NOT NULL PRIMARY KEY,
    wallet_user_id  CHAR(36)        NOT NULL,
    name            VARCHAR(100)    NOT NULL,
    emoji           VARCHAR(10)     NOT NULL DEFAULT '*',
    target_amount   DECIMAL(15,2)   DEFAULT NULL,
    balance         DECIMAL(15,2)   NOT NULL DEFAULT 0.00,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_pocket_user FOREIGN KEY (wallet_user_id)
        REFERENCES wallet_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
