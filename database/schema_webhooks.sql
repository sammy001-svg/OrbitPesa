-- OrbitPesa Webhooks & Notifications Schema
-- Run AFTER schema.sql and schema_admin.sql

USE orbitpesa;

-- =============================================
-- WEBHOOKS
-- =============================================
CREATE TABLE IF NOT EXISTS webhooks (
    id         CHAR(36)     NOT NULL PRIMARY KEY,
    user_id    CHAR(36)     NOT NULL,
    label      VARCHAR(120) NOT NULL DEFAULT 'My Endpoint',
    url        VARCHAR(512) NOT NULL,
    events     JSON         NOT NULL,
    secret     VARCHAR(64)  NOT NULL,
    is_active  TINYINT(1)   NOT NULL DEFAULT 1,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- WEBHOOK DELIVERIES (delivery log)
-- =============================================
CREATE TABLE IF NOT EXISTS webhook_deliveries (
    id              CHAR(36)     NOT NULL PRIMARY KEY,
    webhook_id      CHAR(36)     NOT NULL,
    event           VARCHAR(60)  NOT NULL,
    transaction_ref VARCHAR(60)  NULL,
    payload         JSON         NOT NULL,
    response_status SMALLINT     NULL,
    response_body   TEXT         NULL,
    status          ENUM('success','failed','pending') NOT NULL DEFAULT 'pending',
    attempts        TINYINT      NOT NULL DEFAULT 0,
    created_at      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_webhook (webhook_id),
    FOREIGN KEY (webhook_id) REFERENCES webhooks(id) ON DELETE CASCADE
) ENGINE=InnoDB;
