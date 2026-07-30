-- Migration: Criação da tabela de reservas de salas (Totem)
-- Data: 2026-07-30

CREATE TABLE IF NOT EXISTS `room_reservations` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_id` INT UNSIGNED NOT NULL,
    `reservation_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `customer_name` VARCHAR(150) NOT NULL,
    `customer_phone` VARCHAR(30) DEFAULT NULL,
    `customer_email` VARCHAR(150) DEFAULT NULL,
    `status` ENUM('reserved', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'reserved',
    `source` VARCHAR(20) NOT NULL DEFAULT 'totem',
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_reservations_room` (`room_id`),
    INDEX `idx_reservations_date` (`reservation_date`),
    INDEX `idx_reservations_status` (`status`),
    CONSTRAINT `fk_reservations_room` FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
