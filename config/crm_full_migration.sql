-- ============================================================
-- VMS Go Vista - Complete CRM System Migration
-- Run: mysql -u root vmsgovista < config/crm_full_migration.sql
-- ============================================================

-- 1. New enquiry statuses (already done if ran before)
ALTER TABLE enquiries MODIFY COLUMN status
  ENUM('new','read','contacted','qualified','proposal_sent','negotiation','converted','lost')
  NOT NULL DEFAULT 'new';

-- 2. Enquiry CRM columns (safe to re-run)
ALTER TABLE enquiries
  ADD COLUMN IF NOT EXISTS `converted_at` DATETIME DEFAULT NULL AFTER `status`,
  ADD COLUMN IF NOT EXISTS `converted_value` DECIMAL(12,2) DEFAULT NULL AFTER `converted_at`,
  ADD COLUMN IF NOT EXISTS `assigned_to` VARCHAR(120) DEFAULT NULL AFTER `converted_value`,
  ADD COLUMN IF NOT EXISTS `source` VARCHAR(80) DEFAULT 'website' AFTER `assigned_to`,
  ADD COLUMN IF NOT EXISTS `tags` VARCHAR(255) DEFAULT NULL AFTER `source`,
  ADD COLUMN IF NOT EXISTS `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `tags`;

-- 3. Companies (for invoice header / billing)
CREATE TABLE IF NOT EXISTS `companies` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(255) NOT NULL,
  `address`    TEXT DEFAULT NULL,
  `phone`      VARCHAR(50) DEFAULT NULL,
  `email`      VARCHAR(180) DEFAULT NULL,
  `website`    VARCHAR(255) DEFAULT NULL,
  `tax_id`     VARCHAR(100) DEFAULT NULL,
  `logo`       VARCHAR(255) DEFAULT NULL,
  `currency`   VARCHAR(10) NOT NULL DEFAULT 'USD',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default company
INSERT IGNORE INTO `companies` (`id`, `name`, `address`, `phone`, `email`, `website`, `tax_id`, `currency`) VALUES
(1, 'VMS Go Vista', '123 Travel Street, Tourism District, East Java, Indonesia', '+62 812 3456 7890', 'hello@vmsgovista.com', 'https://vmsgovista.com', 'VMS-2024-ID', 'USD');

-- 4. Leads (converted from enquiries or direct)
CREATE TABLE IF NOT EXISTS `leads` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enquiry_id`       INT UNSIGNED DEFAULT NULL,
  `company_name`     VARCHAR(255) DEFAULT NULL,
  `first_name`       VARCHAR(80) NOT NULL,
  `last_name`        VARCHAR(80) NOT NULL,
  `email`            VARCHAR(180) NOT NULL,
  `phone`            VARCHAR(30) DEFAULT NULL,
  `country`          VARCHAR(80) DEFAULT NULL,
  `source`           VARCHAR(80) DEFAULT 'website',
  `status`           ENUM('new','contacted','qualified','proposal','negotiation','won','lost') NOT NULL DEFAULT 'new',
  `package_interest` TEXT DEFAULT NULL,
  `budget_min`       DECIMAL(12,2) DEFAULT NULL,
  `budget_max`       DECIMAL(12,2) DEFAULT NULL,
  `travel_date`      DATE DEFAULT NULL,
  `pax_adults`       TINYINT UNSIGNED DEFAULT NULL,
  `pax_children`     TINYINT UNSIGNED DEFAULT NULL,
  `assigned_to`      VARCHAR(120) DEFAULT NULL,
  `notes`            TEXT DEFAULT NULL,
  `converted_value`  DECIMAL(12,2) DEFAULT NULL,
  `converted_at`     DATETIME DEFAULT NULL,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_leads_enquiry` (`enquiry_id`),
  KEY `idx_leads_status` (`status`),
  KEY `idx_leads_email` (`email`),
  CONSTRAINT `fk_leads_enquiry` FOREIGN KEY (`enquiry_id`) REFERENCES `enquiries`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Lead notes
CREATE TABLE IF NOT EXISTS `lead_notes` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lead_id`    INT UNSIGNED NOT NULL,
  `note`       TEXT NOT NULL,
  `note_type`  ENUM('note','email','call','meeting','status_change') NOT NULL DEFAULT 'note',
  `created_by` VARCHAR(120) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lead_notes_lead` (`lead_id`),
  CONSTRAINT `fk_lead_notes_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Lead status history
CREATE TABLE IF NOT EXISTS `lead_status_history` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lead_id`    INT UNSIGNED NOT NULL,
  `old_status` VARCHAR(30) DEFAULT NULL,
  `new_status` VARCHAR(30) NOT NULL,
  `changed_by` VARCHAR(120) DEFAULT NULL,
  `notes`      TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lead_history_lead` (`lead_id`),
  CONSTRAINT `fk_lead_history_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Invoices (billing)
CREATE TABLE IF NOT EXISTS `invoices` (
  `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_number`   VARCHAR(50) NOT NULL,
  `lead_id`          INT UNSIGNED DEFAULT NULL,
  `company_id`       INT UNSIGNED NOT NULL DEFAULT 1,
  `customer_name`    VARCHAR(255) NOT NULL,
  `customer_email`   VARCHAR(180) DEFAULT NULL,
  `customer_phone`   VARCHAR(30) DEFAULT NULL,
  `customer_address` TEXT DEFAULT NULL,
  `invoice_date`     DATE NOT NULL,
  `due_date`         DATE DEFAULT NULL,
  `subtotal`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `tax_rate`         DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `tax_amount`       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `discount`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total`            DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `currency`         VARCHAR(10) NOT NULL DEFAULT 'USD',
  `status`           ENUM('draft','sent','paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
  `notes`            TEXT DEFAULT NULL,
  `created_by`       VARCHAR(120) DEFAULT NULL,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_invoice_number` (`invoice_number`),
  KEY `idx_invoices_lead` (`lead_id`),
  KEY `idx_invoices_company` (`company_id`),
  KEY `idx_invoices_status` (`status`),
  CONSTRAINT `fk_invoices_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_invoices_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Invoice line items
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `invoice_id`  INT UNSIGNED NOT NULL,
  `description` VARCHAR(500) NOT NULL,
  `quantity`    DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  `unit_price`  DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total`       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_inv_items_invoice` (`invoice_id`),
  CONSTRAINT `fk_inv_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Enquiry notes & status history (already created in previous migration, safe to re-run)
CREATE TABLE IF NOT EXISTS `enquiry_notes` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enquiry_id` INT UNSIGNED NOT NULL,
  `note`       TEXT NOT NULL,
  `note_type`  ENUM('note','email','call','meeting','status_change','conversion') NOT NULL DEFAULT 'note',
  `created_by` VARCHAR(120) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_enquiry_notes_enq` (`enquiry_id`),
  CONSTRAINT `fk_enquiry_notes_enq` FOREIGN KEY (`enquiry_id`) REFERENCES `enquiries`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `enquiry_status_history` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `enquiry_id` INT UNSIGNED NOT NULL,
  `old_status` VARCHAR(30) DEFAULT NULL,
  `new_status` VARCHAR(30) NOT NULL,
  `changed_by` VARCHAR(120) DEFAULT NULL,
  `notes`      TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_enq_history_enq` (`enquiry_id`),
  CONSTRAINT `fk_enq_history_enq` FOREIGN KEY (`enquiry_id`) REFERENCES `enquiries`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
