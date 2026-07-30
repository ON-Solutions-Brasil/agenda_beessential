-- Migration: Suporte a imagens (logo/salas) e notificações (SMTP + Webhook)
-- Data: 2026-07-30

-- Imagem PNG por sala (substitui o ícone quando presente)
ALTER TABLE `rooms`
    ADD COLUMN `image_path` VARCHAR(255) DEFAULT NULL AFTER `icon`;

-- Configurações adicionais do Totem
INSERT INTO `settings` (`key_name`, `value`, `type`, `group_name`, `label`, `description`) VALUES
-- Identidade visual
('totem_logo', '', 'text', 'totem', 'Logo do Totem (PNG)', 'Logo exibido no topo do totem'),

-- SMTP
('smtp_enabled', '0', 'boolean', 'notificacoes', 'Ativar Envio de E-mail (SMTP)', 'Habilita o envio de e-mails de confirmação'),
('smtp_host', '', 'text', 'notificacoes', 'Servidor SMTP', 'Endereço do servidor SMTP'),
('smtp_port', '587', 'number', 'notificacoes', 'Porta SMTP', 'Porta do servidor (587 = TLS, 465 = SSL)'),
('smtp_encryption', 'tls', 'text', 'notificacoes', 'Criptografia', 'tls, ssl ou vazio'),
('smtp_username', '', 'text', 'notificacoes', 'Usuário SMTP', 'Login de autenticação SMTP'),
('smtp_password', '', 'text', 'notificacoes', 'Senha SMTP', 'Senha de autenticação SMTP'),
('smtp_from_email', '', 'email', 'notificacoes', 'E-mail Remetente', 'Endereço que aparece como remetente'),
('smtp_from_name', 'Agenda Beessential', 'text', 'notificacoes', 'Nome Remetente', 'Nome exibido como remetente'),

-- Webhook
('webhook_enabled', '0', 'boolean', 'notificacoes', 'Ativar Webhook', 'Dispara um webhook a cada reserva'),
('webhook_url', '', 'url', 'notificacoes', 'URL do Webhook', 'Endpoint que receberá o POST JSON da reserva');
