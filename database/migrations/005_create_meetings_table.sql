-- Migration: Criação da tabela de reuniões
-- Data: 2026-07-30

CREATE TABLE IF NOT EXISTS `meetings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `organizer_id` INT UNSIGNED NOT NULL,
    `meeting_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `location` VARCHAR(255) DEFAULT NULL,
    `meet_link` VARCHAR(500) DEFAULT NULL,
    `status` ENUM('scheduled', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'scheduled',
    `color` VARCHAR(7) DEFAULT '#3788d8',
    `notes` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_meetings_date` (`meeting_date`),
    INDEX `idx_meetings_organizer` (`organizer_id`),
    INDEX `idx_meetings_status` (`status`),
    CONSTRAINT `fk_meetings_organizer` FOREIGN KEY (`organizer_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
