-- Migration: Auditoria de alterações nas reservas (logs de ações)
-- Data: 2026-07-30

CREATE TABLE IF NOT EXISTS `reservation_audit` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reservation_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(30) NOT NULL DEFAULT 'updated',
    `field` VARCHAR(60) DEFAULT NULL,
    `old_value` VARCHAR(255) DEFAULT NULL,
    `new_value` VARCHAR(255) DEFAULT NULL,
    `actor` VARCHAR(120) DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_audit_reservation` (`reservation_id`),
    INDEX `idx_audit_action` (`action`),
    INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
