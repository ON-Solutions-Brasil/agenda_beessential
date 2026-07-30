-- Migration: Vincula vendedores a uma unidade
-- Data: 2026-07-30

ALTER TABLE `sellers`
    ADD COLUMN `unit_id` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD INDEX `idx_sellers_unit` (`unit_id`);

UPDATE `sellers` SET `unit_id` = 1 WHERE `unit_id` IS NULL OR `unit_id` = 0;

ALTER TABLE `sellers`
    ADD CONSTRAINT `fk_sellers_unit` FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON DELETE CASCADE;
