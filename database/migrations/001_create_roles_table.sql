-- Migration: Criação da tabela de roles (papéis de usuário)
-- Data: 2026-07-30

CREATE TABLE IF NOT EXISTS `roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(50) NOT NULL,
    `slug` VARCHAR(50) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `is_superadmin` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_roles_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir roles padrão
INSERT INTO `roles` (`name`, `slug`, `description`, `is_superadmin`) VALUES
('Super Admin', 'superadmin', 'Acesso total ao sistema', 1),
('Administrador', 'admin', 'Acesso administrativo sem configurações de sistema', 0),
('Colaborador', 'colaborador', 'Pode agendar e visualizar reuniões', 0),
('Visualizador', 'visualizador', 'Apenas visualiza agendamentos', 0);
