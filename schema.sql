-- ============================================================
-- PBX Command Dashboard — MySQL Schema
-- Compatible with MySQL 5.7+ and MariaDB 10.3+
-- Import this file via cPanel phpMyAdmin or CLI:
--   mysql -u your_user -p your_db < schema.sql
-- ============================================================

SET NAMES utf8mb4;
SET time_zone = '+00:00';

-- Dashboard users (for login)
CREATE TABLE IF NOT EXISTS `dashboard_users` (
  `id`            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`          VARCHAR(120) NOT NULL,
  `email`         VARCHAR(255) NOT NULL UNIQUE,
  `role`          ENUM('admin','manager','employee') NOT NULL DEFAULT 'employee',
  `status`        ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `password_hash` VARCHAR(255) DEFAULT NULL,
  `last_login_at` DATETIME DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SIP Extensions
CREATE TABLE IF NOT EXISTS `extensions` (
  `id`         INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `number`     VARCHAR(20) NOT NULL UNIQUE,
  `name`       VARCHAR(120) NOT NULL,
  `type`       VARCHAR(50) NOT NULL DEFAULT 'customer_support',
  `status`     ENUM('registered','unregistered') NOT NULL DEFAULT 'unregistered',
  `notes`      TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Call Queues
CREATE TABLE IF NOT EXISTS `call_queues` (
  `id`            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`          VARCHAR(120) NOT NULL,
  `strategy`      VARCHAR(50) NOT NULL DEFAULT 'ringall',
  `max_wait_time` INT NOT NULL DEFAULT 60,
  `notes`         TEXT DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ring Groups
CREATE TABLE IF NOT EXISTS `ring_groups` (
  `id`               INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`             VARCHAR(120) NOT NULL,
  `extension_number` VARCHAR(20) DEFAULT NULL,
  `strategy`         VARCHAR(50) NOT NULL DEFAULT 'ringall',
  `ring_time`        INT NOT NULL DEFAULT 20,
  `notes`            TEXT DEFAULT NULL,
  `created_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- IVR Menus
CREATE TABLE IF NOT EXISTS `ivr_menus` (
  `id`                  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`                VARCHAR(120) NOT NULL,
  `description`         TEXT DEFAULT NULL,
  `timeout`             INT NOT NULL DEFAULT 5,
  `invalid_retry_count` INT NOT NULL DEFAULT 3,
  `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SLA Rules
CREATE TABLE IF NOT EXISTS `sla_rules` (
  `id`                  INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`                VARCHAR(120) NOT NULL,
  `target_answer_time`  INT NOT NULL DEFAULT 20,
  `target_abandon_rate` DECIMAL(5,2) NOT NULL DEFAULT 5.00,
  `threshold_warning`   INT NOT NULL DEFAULT 80,
  `threshold_critical`  INT NOT NULL DEFAULT 70,
  `is_active`           TINYINT(1) NOT NULL DEFAULT 1,
  `notes`               TEXT DEFAULT NULL,
  `created_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Time Conditions
CREATE TABLE IF NOT EXISTS `time_conditions` (
  `id`         INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(120) NOT NULL,
  `timezone`   VARCHAR(60) NOT NULL DEFAULT 'Africa/Lagos',
  `open_time`  TIME NOT NULL DEFAULT '08:00:00',
  `close_time` TIME NOT NULL DEFAULT '18:00:00',
  `open_days`  VARCHAR(60) NOT NULL DEFAULT 'Mon-Fri',
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  `notes`      TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- SIP Trunks
CREATE TABLE IF NOT EXISTS `sip_trunks` (
  `id`           INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`         VARCHAR(120) NOT NULL,
  `host`         VARCHAR(255) NOT NULL,
  `port`         INT NOT NULL DEFAULT 5060,
  `username`     VARCHAR(120) DEFAULT NULL,
  `password`     VARCHAR(255) DEFAULT NULL,
  `status`       ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `codecs`       VARCHAR(255) DEFAULT NULL,
  `max_channels` INT NOT NULL DEFAULT 30,
  `notes`        TEXT DEFAULT NULL,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PBX Agents (call center staff)
CREATE TABLE IF NOT EXISTS `pbx_users` (
  `id`         INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(120) NOT NULL,
  `extension`  VARCHAR(20) DEFAULT NULL,
  `email`      VARCHAR(255) DEFAULT NULL,
  `role`       VARCHAR(50) NOT NULL DEFAULT 'agent',
  `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
  `notes`      TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- FreePBX Settings
CREATE TABLE IF NOT EXISTS `freepbx_settings` (
  `id`           INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `url`          VARCHAR(255) DEFAULT NULL,
  `api_key`      VARCHAR(255) DEFAULT NULL,
  `api_secret`   VARCHAR(255) DEFAULT NULL,
  `ami_host`     VARCHAR(255) DEFAULT NULL,
  `ami_port`     INT NOT NULL DEFAULT 5038,
  `ami_username` VARCHAR(120) DEFAULT NULL,
  `ami_password` VARCHAR(255) DEFAULT NULL,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Call Logs
CREATE TABLE IF NOT EXISTS `call_logs` (
  `id`            INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `caller_number` VARCHAR(40) DEFAULT NULL,
  `caller_name`   VARCHAR(120) DEFAULT NULL,
  `destination`   VARCHAR(40) DEFAULT NULL,
  `status`        ENUM('answered','missed','voicemail','transferred','busy') NOT NULL DEFAULT 'missed',
  `duration`      INT NOT NULL DEFAULT 0,
  `ivr_path`      VARCHAR(255) DEFAULT NULL,
  `agent_name`    VARCHAR(120) DEFAULT NULL,
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_call_logs_created` (`created_at`),
  INDEX `idx_call_logs_status`  (`status`),
  INDEX `idx_call_logs_agent`   (`agent_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- System Settings (key-value store for all integration credentials)
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id`          INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_val` TEXT DEFAULT NULL,
  `grp`         VARCHAR(50) NOT NULL DEFAULT 'general',
  `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- WhatsApp conversations (one record per contact phone number)
CREATE TABLE IF NOT EXISTS `whatsapp_conversations` (
  `id`                INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `contact_number`    VARCHAR(40) NOT NULL UNIQUE,
  `contact_name`      VARCHAR(120) DEFAULT NULL,
  `assigned_agent`    VARCHAR(120) DEFAULT NULL,
  `status`            ENUM('open','pending','closed') NOT NULL DEFAULT 'open',
  `unread_count`      INT NOT NULL DEFAULT 0,
  `last_message_at`   DATETIME DEFAULT NULL,
  `last_message_body` TEXT DEFAULT NULL,
  `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_wa_conv_status` (`status`),
  INDEX `idx_wa_conv_last`   (`last_message_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- WhatsApp messages
CREATE TABLE IF NOT EXISTS `whatsapp_messages` (
  `id`              INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `conversation_id` INT NOT NULL,
  `wamid`           VARCHAR(255) DEFAULT NULL UNIQUE,
  `direction`       ENUM('inbound','outbound') NOT NULL DEFAULT 'inbound',
  `message_type`    ENUM('text','image','audio','video','document','template') NOT NULL DEFAULT 'text',
  `body`            TEXT DEFAULT NULL,
  `media_url`       TEXT DEFAULT NULL,
  `status`          ENUM('received','sent','delivered','read','failed') NOT NULL DEFAULT 'received',
  `sender_name`     VARCHAR(120) DEFAULT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_wa_msg_conv` (`conversation_id`),
  INDEX `idx_wa_msg_dir`  (`direction`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Default admin user (password: Admin123!) ─────────────────────────────
INSERT IGNORE INTO `dashboard_users` (`name`, `email`, `role`, `status`, `password_hash`)
VALUES ('Admin', 'admin@pbx.local', 'admin', 'active',
        '$2y$12$jdsvgpIX0kP8dYLfu3yrz.iW0KFAs0iKMR9A2dIWsAnbGdz3Vb5Cq');
