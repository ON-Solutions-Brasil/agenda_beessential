-- Migration: Duração padrão de reserva do Totem
-- Data: 2026-07-30

INSERT INTO `settings` (`key_name`, `value`, `type`, `group_name`, `label`, `description`) VALUES
('totem_default_duration', '30', 'number', 'totem', 'Tempo Padrão de Reserva (min)', 'Duração pré-selecionada ao iniciar uma nova reserva');
