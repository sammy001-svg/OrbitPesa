-- In-app notifications for consumer wallet users

CREATE TABLE IF NOT EXISTS wallet_notifications (
    id              CHAR(36)        NOT NULL PRIMARY KEY,
    wallet_user_id  CHAR(36)        NOT NULL,
    type            VARCHAR(50)     NOT NULL DEFAULT 'system',
    title           VARCHAR(150)    NOT NULL,
    body            TEXT            NOT NULL,
    url             VARCHAR(255)    DEFAULT NULL,
    is_read         TINYINT(1)      NOT NULL DEFAULT 0,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_wn_user_read (wallet_user_id, is_read, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
