-- Migration: Configurações do Modo Totem
-- Data: 2026-07-30

INSERT INTO `settings` (`key_name`, `value`, `type`, `group_name`, `label`, `description`) VALUES
('totem_enabled', '0', 'boolean', 'totem', 'Ativar Modo Totem', 'Habilita o acesso ao modo totem na tela de login'),
('totem_pin', '1234', 'text', 'totem', 'PIN de Acesso (4 dígitos)', 'PIN numérico de 4 dígitos para acessar o totem'),
('totem_open_time', '08:00', 'text', 'totem', 'Início do Funcionamento', 'Horário de abertura para reservas no totem'),
('totem_close_time', '18:00', 'text', 'totem', 'Fim do Funcionamento', 'Horário de encerramento para reservas no totem'),
('totem_slot_minutes', '30', 'number', 'totem', 'Intervalo das Janelas (min)', 'Duração de cada janela de reserva em minutos'),
('totem_min_duration', '30', 'number', 'totem', 'Tempo Mínimo de Reserva (min)', 'Duração mínima permitida para uma reserva'),
('totem_max_duration', '120', 'number', 'totem', 'Tempo Máximo de Reserva (min)', 'Duração máxima permitida para uma reserva'),
('totem_advance_minutes', '0', 'number', 'totem', 'Antecedência Mínima (min)', 'Minutos de antecedência exigidos para novas reservas'),
('totem_require_email', '0', 'boolean', 'totem', 'Exigir E-mail', 'Torna o e-mail obrigatório no formulário de reserva'),
('totem_refresh_seconds', '15', 'number', 'totem', 'Atualização Automática (s)', 'Intervalo de atualização em tempo real da tela do totem');
