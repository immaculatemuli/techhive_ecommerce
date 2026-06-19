-- TechHive DB Migration: Bonus Auth Features
-- Run this file once in phpMyAdmin or mysql CLI:
--   mysql -u root techhive_db < update_db.sql

USE techhive_db;

-- ── Extend users table ──────────────────────────────────────────
ALTER TABLE users
    ADD COLUMN IF NOT EXISTS remember_token  VARCHAR(64)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS email_verified  TINYINT(1)   NOT NULL DEFAULT 1,
    ADD COLUMN IF NOT EXISTS two_fa_secret   VARCHAR(32)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS two_fa_enabled  TINYINT(1)   NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS profile_image   VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS phone           VARCHAR(20)  DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS bio             TEXT         DEFAULT NULL;

-- Existing users are already "verified" (set to 1 above via DEFAULT)
-- New registrations will be set to 0 until they click the email link.

-- ── Password reset tokens ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS password_resets (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(100) NOT NULL,
    token      VARCHAR(64)  NOT NULL,
    expires_at DATETIME     NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_token (token)
);

-- ── Email verification tokens ───────────────────────────────────
CREATE TABLE IF NOT EXISTS email_verifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    token      VARCHAR(64)  NOT NULL,
    expires_at DATETIME     NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token)
);
