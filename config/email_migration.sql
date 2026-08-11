-- ============================================================
-- VMS Go Vista — Email/SMTP + Contact Messages migration
-- Run this once on existing installs (tables auto-create anyway,
-- but this keeps your schema documented).
-- ============================================================

USE `vmsgovista`;

-- ------------------------------------------------------------
-- SETTINGS — key/value store for SMTP + notification config
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key`   VARCHAR(100) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- CONTACTS — website contact form submissions
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contacts` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(120) NOT NULL,
  `email`      VARCHAR(180) NOT NULL,
  `company`    VARCHAR(180) DEFAULT NULL,
  `website`    VARCHAR(180) DEFAULT NULL,
  `message`    TEXT NOT NULL,
  `status`     ENUM('new','read') NOT NULL DEFAULT 'new',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contacts_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
