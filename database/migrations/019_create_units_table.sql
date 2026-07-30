-- Migration: Unidades (localizações) do Totem
-- Data: 2026-07-30

CREATE TABLE IF NOT EXISTS `units` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `location` VARCHAR(255) DEFAULT NULL,
    `pin` VARCHAR(4) NOT NULL,
    `open_time` TIME NOT NULL DEFAULT '08:00:00',
    `close_time` TIME NOT NULL DEFAULT '18:00:00',
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_units_active` (`active`),
    INDEX `idx_units_pin` (`pin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Unidade padrão herdando as configurações globais atuais do totem
INSERT INTO `units` (`id`, `name`, `location`, `pin`, `open_time`, `close_time`, `active`, `sort_order`)
SELECT 1, 'Unidade Principal', 'Matriz',
    COALESCE((SELECT `value` FROM `settings` WHERE `key_name` = 'totem_pin' LIMIT 1), '1234'),
    CONCAT(COALESCE((SELECT `value` FROM `settings` WHERE `key_name` = 'totem_open_time' LIMIT 1), '08:00'), ':00'),
    CONCAT(COALESCE((SELECT `value` FROM `settings` WHERE `key_name` = 'totem_close_time' LIMIT 1), '18:00'), ':00'),
    1, 1
WHERE NOT EXISTS (SELECT 1 FROM `units` WHERE `id` = 1);

-- Vincula as salas existentes à unidade padrão
ALTER TABLE `rooms`
    ADD COLUMN `unit_id` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD INDEX `idx_rooms_unit` (`unit_id`);

UPDATE `rooms` SET `unit_id` = 1 WHERE `unit_id` IS NULL OR `unit_id` = 0;

ALTER TABLE `rooms`
    ADD CONSTRAINT `fk_rooms_unit` FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON DELETE CASCADE;
