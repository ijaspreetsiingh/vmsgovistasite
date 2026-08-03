-- ============================================================
-- VMS Go Vista - CRM System Migration
-- Run this to add CRM features to the enquiries system
-- ============================================================

-- Extend enquiries status to full CRM pipeline
ALTER TABLE `enquiries` MODIFY COLUMN `status`
  ENUM('new','read','contacted','qualified','proposal_sent','negotiation','converted','lost')
  NOT NULL DEFAULT 'new';

-- Add CRM columns to enquiries table
ALTER TABLE `enquiries`
  ADD COLUMN IF NOT EXISTS `converted_at`    DATETIME DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `converted_value` DECIMAL(12,2) DEFAULT NULL AFTER `converted_at`,
  ADD COLUMN IF NOT EXISTS `assigned_to`     VARCHAR(120) DEFAULT NULL AFTER `converted_value`,
  ADD COLUMN IF NOT EXISTS `source`          VARCHAR(80) DEFAULT 'website' AFTER `assigned_to`,
  ADD COLUMN IF NOT EXISTS `tags`            VARCHAR(255) DEFAULT NULL AFTER `source`,
  ADD COLUMN IF NOT EXISTS `updated_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `tags`;

-- ============================================================
-- ENQUIRY NOTES (internal staff notes & timeline)
-- ============================================================
CREATE TABLE IF NOT EXISTS `enquiry_notes` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enquiry_id`  INT UNSIGNED NOT NULL,
  `note`        TEXT NOT NULL,
  `note_type`   ENUM('note','email','call','meeting','status_change','conversion') NOT NULL DEFAULT 'note',
  `created_by`  VARCHAR(120) DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_enquiry_notes_enq` (`enquiry_id`),
  KEY `idx_enquiry_notes_created` (`created_at`),
  CONSTRAINT `fk_enquiry_notes_enq` FOREIGN KEY (`enquiry_id`)
    REFERENCES `enquiries`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ENQUIRY STATUS HISTORY (automatic audit log)
-- ============================================================
CREATE TABLE IF NOT EXISTS `enquiry_status_history` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enquiry_id`  INT UNSIGNED NOT NULL,
  `old_status`  VARCHAR(30) DEFAULT NULL,
  `new_status`  VARCHAR(30) NOT NULL,
  `changed_by`  VARCHAR(120) DEFAULT NULL,
  `notes`       TEXT DEFAULT NULL,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_enq_history_enq` (`enquiry_id`),
  CONSTRAINT `fk_enq_history_enq` FOREIGN KEY (`enquiry_id`)
    REFERENCES `enquiries`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
