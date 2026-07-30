-- Migration: Intervalo de segurança entre reservas (buffer)
-- Data: 2026-07-30

INSERT INTO `settings` (`key_name`, `value`, `type`, `group_name`, `label`, `description`) VALUES
('totem_buffer_minutes', '0', 'number', 'totem', 'Intervalo entre Reservas (min)', 'Tempo de folga após cada reserva para limpeza/deslocamento, evitando sobreposição de pessoas');
