-- Migration: Vendedores e vínculo com reservas
-- Data: 2026-07-30

CREATE TABLE IF NOT EXISTS `sellers` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) DEFAULT NULL,
    `phone` VARCHAR(30) DEFAULT NULL,
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_sellers_active` (`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sellers` (`name`, `email`, `active`, `sort_order`) VALUES
('Vendedor Exemplo', 'vendedor@exemplo.com', 1, 1);

-- Vincula reservas ao vendedor responsável
ALTER TABLE `room_reservations`
    ADD COLUMN `seller_id` INT UNSIGNED DEFAULT NULL AFTER `customer_email`,
    ADD COLUMN `seller_name` VARCHAR(150) DEFAULT NULL AFTER `seller_id`,
    ADD INDEX `idx_reservations_seller` (`seller_id`),
    ADD CONSTRAINT `fk_reservations_seller` FOREIGN KEY (`seller_id`) REFERENCES `sellers`(`id`) ON DELETE SET NULL;
