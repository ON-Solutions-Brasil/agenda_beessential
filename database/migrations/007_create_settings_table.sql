-- Migration: Criação da tabela de configurações do sistema
-- Data: 2026-07-30

CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key_name` VARCHAR(100) NOT NULL,
    `value` TEXT DEFAULT NULL,
    `type` ENUM('text', 'number', 'boolean', 'json', 'email', 'url') NOT NULL DEFAULT 'text',
    `group_name` VARCHAR(50) NOT NULL DEFAULT 'geral',
    `label` VARCHAR(150) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_settings_key` (`key_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir configurações padrão
INSERT INTO `settings` (`key_name`, `value`, `type`, `group_name`, `label`, `description`) VALUES
-- Geral
('site_name', 'Agenda Beessential', 'text', 'geral', 'Nome do Sistema', 'Nome exibido no topo do sistema'),
('site_description', 'Sistema de agendamento de reuniões', 'text', 'geral', 'Descrição', 'Descrição breve do sistema'),
('timezone', 'America/Sao_Paulo', 'text', 'geral', 'Fuso Horário', 'Fuso horário padrão do sistema'),
('date_format', 'd/m/Y', 'text', 'geral', 'Formato de Data', 'Formato de exibição de datas'),
('time_format', 'H:i', 'text', 'geral', 'Formato de Hora', 'Formato de exibição de horários'),

-- Reuniões
('meeting_default_duration', '60', 'number', 'reunioes', 'Duração Padrão (min)', 'Duração padrão de uma reunião em minutos'),
('meeting_min_duration', '15', 'number', 'reunioes', 'Duração Mínima (min)', 'Duração mínima permitida para uma reunião'),
('meeting_max_duration', '480', 'number', 'reunioes', 'Duração Máxima (min)', 'Duração máxima permitida para uma reunião'),
('meeting_advance_days', '90', 'number', 'reunioes', 'Antecedência Máxima (dias)', 'Dias máximos de antecedência para agendamento'),
('meeting_auto_generate_meet', '1', 'boolean', 'reunioes', 'Gerar Link Meet Automaticamente', 'Gera link do Google Meet ao criar reunião'),

-- Horário de trabalho
('work_start_time', '08:00', 'text', 'horarios', 'Início do Expediente', 'Horário de início do expediente'),
('work_end_time', '18:00', 'text', 'horarios', 'Fim do Expediente', 'Horário de fim do expediente'),
('lunch_start_time', '12:00', 'text', 'horarios', 'Início do Almoço', 'Horário de início do intervalo'),
('lunch_end_time', '13:00', 'text', 'horarios', 'Fim do Almoço', 'Horário de fim do intervalo'),
('work_days', '["1","2","3","4","5"]', 'json', 'horarios', 'Dias de Trabalho', 'Dias da semana que permitem agendamento (0=Dom, 6=Sáb)'),

-- Google
('google_client_id', '', 'text', 'google', 'Google Client ID', 'ID do cliente OAuth do Google'),
('google_client_secret', '', 'text', 'google', 'Google Client Secret', 'Secret do cliente OAuth do Google'),
('google_api_key', '', 'text', 'google', 'Google API Key', 'Chave de API do Google');
