-- ============================================================
-- VMS Go Vista - Complete Database Schema
-- Database: vmsgovista
-- Run this file once to create all tables
-- ============================================================

CREATE DATABASE IF NOT EXISTS `vmsgovista` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `vmsgovista`;

-- ============================================================
-- USERS TABLE (admin accounts)
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(120) NOT NULL,
  `email`      VARCHAR(180) NOT NULL,
  `password`   VARCHAR(255) NOT NULL,
  `role`       ENUM('admin','editor') NOT NULL DEFAULT 'admin',
  `avatar`     VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PACKAGES TABLE (main package record)
-- ============================================================
CREATE TABLE IF NOT EXISTS `packages` (
  `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`             VARCHAR(255) NOT NULL,
  `slug`              VARCHAR(255) NOT NULL,
  `short_title`       VARCHAR(180) DEFAULT NULL,
  `short_desc`        TEXT DEFAULT NULL,
  `overview`          LONGTEXT DEFAULT NULL,
  `category`          VARCHAR(100) DEFAULT NULL,
  `tour_type`         VARCHAR(120) DEFAULT NULL,
  `destination`       VARCHAR(120) DEFAULT NULL,
  `country`           VARCHAR(120) DEFAULT NULL,
  `city`              VARCHAR(120) DEFAULT NULL,
  `days`              TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `nights`            TINYINT UNSIGNED NOT NULL DEFAULT 0,
  -- pricing
  `price_original`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `price_discounted`  DECIMAL(10,2) DEFAULT NULL,
  `currency`          VARCHAR(10) NOT NULL DEFAULT 'USD',
  `price_label`       VARCHAR(60) DEFAULT 'From',
  `discount_pct`      TINYINT UNSIGNED DEFAULT NULL,
  `price_per_adult`   DECIMAL(10,2) DEFAULT NULL,
  `price_per_child`   DECIMAL(10,2) DEFAULT NULL,
  `price_notes`       TEXT DEFAULT NULL,
  -- tour details (feature list on details page)
  `transportation`    VARCHAR(255) DEFAULT NULL,
  `accommodation`     VARCHAR(255) DEFAULT NULL,
  `max_altitude`      VARCHAR(80) DEFAULT NULL,
  `departure_from`    VARCHAR(120) DEFAULT NULL,
  `best_season`       VARCHAR(80) DEFAULT NULL,
  `meals`             VARCHAR(120) DEFAULT NULL,
  `language`          VARCHAR(120) DEFAULT NULL,
  `fitness_level`     VARCHAR(80) DEFAULT NULL,
  `group_size_min`    TINYINT UNSIGNED DEFAULT NULL,
  `group_size_max`    TINYINT UNSIGNED DEFAULT NULL,
  `min_age`           TINYINT UNSIGNED DEFAULT NULL,
  `max_age`           TINYINT UNSIGNED DEFAULT NULL,
  -- images
  `main_image`        VARCHAR(255) DEFAULT NULL,
  `thumbnail_image`   VARCHAR(255) DEFAULT NULL,
  `breadcrumb_image`  VARCHAR(255) DEFAULT NULL,
  -- ratings
  `rating`            DECIMAL(3,1) NOT NULL DEFAULT 0.0,
  `review_count`      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  -- map
  `map_embed`         TEXT DEFAULT NULL,
  -- status flags
  `status`            ENUM('published','draft','archived') NOT NULL DEFAULT 'draft',
  `is_featured`       TINYINT(1) NOT NULL DEFAULT 0,
  `is_popular`        TINYINT(1) NOT NULL DEFAULT 0,
  `is_recommended`    TINYINT(1) NOT NULL DEFAULT 0,
  `show_on_homepage`  TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order`        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  -- booking CTAs
  `booking_cta_text`  VARCHAR(80) DEFAULT 'Check Availability',
  `booking_cta_url`   VARCHAR(255) DEFAULT '#',
  `enquiry_cta_text`  VARCHAR(80) DEFAULT 'Send Enquiry',
  `whatsapp_number`   VARCHAR(30) DEFAULT NULL,
  -- timestamps
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_packages_slug` (`slug`),
  KEY `idx_packages_status`        (`status`),
  KEY `idx_packages_featured`      (`is_featured`),
  KEY `idx_packages_popular`       (`is_popular`),
  KEY `idx_packages_homepage`      (`show_on_homepage`),
  KEY `idx_packages_destination`   (`destination`),
  KEY `idx_packages_sort`          (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PACKAGE GALLERY IMAGES
-- ============================================================
CREATE TABLE IF NOT EXISTS `package_images` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id`  INT UNSIGNED NOT NULL,
  `image_path`  VARCHAR(255) NOT NULL,
  `alt_text`    VARCHAR(255) DEFAULT NULL,
  `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pkg_images_package` (`package_id`),
  CONSTRAINT `fk_pkg_images_package` FOREIGN KEY (`package_id`)
    REFERENCES `packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PACKAGE ITINERARY DAYS
-- ============================================================
CREATE TABLE IF NOT EXISTS `package_itinerary` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id`    INT UNSIGNED NOT NULL,
  `day_number`    TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `title`         VARCHAR(255) NOT NULL,
  `description`   TEXT DEFAULT NULL,
  `activities`    TEXT DEFAULT NULL,
  `meals`         VARCHAR(120) DEFAULT NULL,
  `accommodation` VARCHAR(255) DEFAULT NULL,
  `sort_order`    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_itin_package` (`package_id`),
  CONSTRAINT `fk_itin_package` FOREIGN KEY (`package_id`)
    REFERENCES `packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PACKAGE INCLUSIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS `package_inclusions` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id`  INT UNSIGNED NOT NULL,
  `item`        VARCHAR(255) NOT NULL,
  `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_incl_package` (`package_id`),
  CONSTRAINT `fk_incl_package` FOREIGN KEY (`package_id`)
    REFERENCES `packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PACKAGE EXCLUSIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS `package_exclusions` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id`  INT UNSIGNED NOT NULL,
  `item`        VARCHAR(255) NOT NULL,
  `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_excl_package` (`package_id`),
  CONSTRAINT `fk_excl_package` FOREIGN KEY (`package_id`)
    REFERENCES `packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PACKAGE HIGHLIGHTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `package_highlights` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id`  INT UNSIGNED NOT NULL,
  `item`        VARCHAR(255) NOT NULL,
  `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_high_package` (`package_id`),
  CONSTRAINT `fk_high_package` FOREIGN KEY (`package_id`)
    REFERENCES `packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PACKAGE FAQs
-- ============================================================
CREATE TABLE IF NOT EXISTS `package_faqs` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id`  INT UNSIGNED NOT NULL,
  `question`    VARCHAR(400) NOT NULL,
  `answer`      TEXT NOT NULL,
  `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_faqs_package` (`package_id`),
  CONSTRAINT `fk_faqs_package` FOREIGN KEY (`package_id`)
    REFERENCES `packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- PACKAGE IMPORTANT INFO (cancellation, visa, notes etc.)
-- ============================================================
CREATE TABLE IF NOT EXISTS `package_info` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id`  INT UNSIGNED NOT NULL,
  `info_type`   VARCHAR(80) NOT NULL COMMENT 'e.g. cancellation_policy, visa_info, important_notes, terms',
  `title`       VARCHAR(200) DEFAULT NULL,
  `content`     TEXT NOT NULL,
  `sort_order`  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_info_package` (`package_id`),
  CONSTRAINT `fk_info_package` FOREIGN KEY (`package_id`)
    REFERENCES `packages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ENQUIRIES (contact form on tour details)
-- ============================================================
CREATE TABLE IF NOT EXISTS `enquiries` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `package_id`    INT UNSIGNED DEFAULT NULL,
  `package_title` VARCHAR(255) DEFAULT NULL,
  `first_name`    VARCHAR(80) NOT NULL,
  `last_name`     VARCHAR(80) NOT NULL,
  `email`         VARCHAR(180) NOT NULL,
  `country`       VARCHAR(80) DEFAULT NULL,
  `phone`         VARCHAR(30) DEFAULT NULL,
  `adults`        TINYINT UNSIGNED DEFAULT NULL,
  `children`      TINYINT UNSIGNED DEFAULT NULL,
  `message`       TEXT DEFAULT NULL,
  `status`        ENUM('new','read','replied') NOT NULL DEFAULT 'new',
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_enq_package` (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- SETTINGS (key/value store for SMTP + notification config)
-- ============================================================
CREATE TABLE IF NOT EXISTS `settings` (
  `setting_key`   VARCHAR(100) NOT NULL,
  `setting_value` TEXT DEFAULT NULL,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- CONTACTS (website contact form submissions)
-- ============================================================
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

-- ============================================================
-- DEFAULT ADMIN USER
-- Password: admin123  (bcrypt hash)
-- CHANGE THIS PASSWORD IMMEDIATELY AFTER SETUP
-- ============================================================
INSERT IGNORE INTO `users` (`name`, `email`, `password`, `role`)
VALUES (
  'Admin',
  'admin@vmsgovista.com',
  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
  'admin'
);
