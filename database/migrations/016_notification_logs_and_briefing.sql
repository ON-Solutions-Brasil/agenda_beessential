-- Migration: Logs de envio (e-mail/webhook) e briefing da reserva
-- Data: 2026-07-30

CREATE TABLE IF NOT EXISTS `notification_logs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `reservation_id` INT UNSIGNED DEFAULT NULL,
    `channel` ENUM('email', 'webhook') NOT NULL,
    `recipient` VARCHAR(255) DEFAULT NULL,
    `subject` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('success', 'failed', 'skipped') NOT NULL DEFAULT 'success',
    `error` TEXT DEFAULT NULL,
    `payload` MEDIUMTEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_notif_reservation` (`reservation_id`),
    INDEX `idx_notif_channel` (`channel`),
    INDEX `idx_notif_status` (`status`),
    INDEX `idx_notif_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Briefing / interesse do visitante na reserva (para qualificação futura)
ALTER TABLE `room_reservations`
    ADD COLUMN `interest` VARCHAR(500) DEFAULT NULL AFTER `seller_name`;

-- Dias de trabalho: segunda a domingo por padrão (0=Dom ... 6=Sáb)
UPDATE `settings` SET `value` = '["0","1","2","3","4","5","6"]' WHERE `key_name` = 'work_days';
