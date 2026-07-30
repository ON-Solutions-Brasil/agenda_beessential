-- Migration: Criação da tabela de permissões
-- Data: 2026-07-30

CREATE TABLE IF NOT EXISTS `permissions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `group_name` VARCHAR(50) NOT NULL DEFAULT 'geral',
    `description` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_permissions_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir permissões padrão
INSERT INTO `permissions` (`name`, `slug`, `group_name`, `description`) VALUES
-- Reuniões
('Ver reuniões', 'meetings.view', 'reunioes', 'Visualizar lista de reuniões'),
('Criar reuniões', 'meetings.create', 'reunioes', 'Criar novas reuniões'),
('Editar reuniões', 'meetings.edit', 'reunioes', 'Editar reuniões existentes'),
('Cancelar reuniões', 'meetings.cancel', 'reunioes', 'Cancelar reuniões'),
('Excluir reuniões', 'meetings.delete', 'reunioes', 'Excluir reuniões permanentemente'),

-- Calendário
('Ver calendário', 'calendar.view', 'calendario', 'Visualizar o calendário'),

-- Usuários
('Ver usuários', 'users.view', 'usuarios', 'Visualizar lista de usuários'),
('Criar usuários', 'users.create', 'usuarios', 'Criar novos usuários'),
('Editar usuários', 'users.edit', 'usuarios', 'Editar usuários existentes'),
('Excluir usuários', 'users.delete', 'usuarios', 'Excluir usuários'),

-- Configurações
('Ver configurações', 'settings.view', 'configuracoes', 'Visualizar configurações do sistema'),
('Editar configurações', 'settings.edit', 'configuracoes', 'Alterar configurações do sistema'),

-- Administração
('Gerenciar roles', 'admin.roles', 'admin', 'Gerenciar papéis de usuário'),
('Gerenciar permissões', 'admin.permissions', 'admin', 'Gerenciar permissões do sistema');
