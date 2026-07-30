-- Migration: Criação da tabela de salas de demonstração
-- Data: 2026-07-30

CREATE TABLE IF NOT EXISTS `rooms` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(150) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `icon` VARCHAR(50) NOT NULL DEFAULT 'bi-easel',
    `capacity` INT UNSIGNED DEFAULT NULL,
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `show_in_totem` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_rooms_active` (`active`),
    INDEX `idx_rooms_totem` (`show_in_totem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- Salas cadastradas (Ativas aparecem no Totem; Inativas ficam ocultas)
-- Ícones: Bootstrap Icons (https://icons.getbootstrap.com)
-- ─────────────────────────────────────────────────────────────
INSERT INTO `rooms` (`name`, `description`, `icon`, `capacity`, `active`, `show_in_totem`, `sort_order`) VALUES
-- ── Ativas ──────────────────────────────────────────────────
('CJ - Showroom',                'Showroom principal para apresentação de produtos.',        'bi-shop-window',   8,    1, 1, 1),
('STS - Reunião Interna',        'Sala reservada para reuniões internas da equipe.',         'bi-people',        6,    1, 1, 2),
('STS - Reunião Showroom',       'Reuniões com clientes no ambiente do showroom.',           'bi-people-fill',   6,    1, 1, 3),
('STS - Select',                 'Ambiente premium para atendimento selecionado.',           'bi-stars',         4,    1, 1, 4),
('STS - Showroom (Apresentação)','Espaço dedicado a apresentações e demonstrações.',         'bi-easel',         10,   1, 1, 5),

-- ── Inativas ────────────────────────────────────────────────
('Escritório do Arquiteto',      'Atendimento no escritório do arquiteto.',                  'bi-rulers',        NULL, 0, 0, 6),
('Escritório do Cliente',        'Atendimento no escritório do cliente.',                    'bi-briefcase',     NULL, 0, 0, 7),
('Obra',                         'Visita técnica em obra.',                                  'bi-cone-striped',  NULL, 0, 0, 8),
('On line',                      'Atendimento remoto por videochamada.',                     'bi-camera-video',  NULL, 0, 0, 9),
('STS - Cinema/Loft',            'Ambiente cinema/loft para experiências imersivas.',        'bi-film',          12,   0, 0, 10),
('STS - Gourmet',                'Espaço gourmet para demonstrações de cozinha.',            'bi-egg-fried',     8,    0, 0, 11),
('STS - Living',                 'Ambiente living para composição de estar.',                'bi-house-heart',   6,    0, 0, 12),
('Sara Golds parceira visita',   'Visita da parceira Sara Golds.',                           'bi-person-badge',  NULL, 0, 0, 13),
('Showroom CJ',                  'Showroom CJ para exposição de produtos.',                  'bi-shop',          8,    0, 0, 14),
('Showroom Gourmet - Azevedo Sodré',      'Showroom gourmet na unidade Azevedo Sodré.',      'bi-cup-hot',       8,    0, 0, 15),
('Showroom Loft - Azevedo Sodré',         'Showroom loft na unidade Azevedo Sodré.',         'bi-building',      10,   0, 0, 16),
('Showroom Reunião - Azevedo Sodré',      'Sala de reunião na unidade Azevedo Sodré.',       'bi-people',        6,    0, 0, 17),
('Showroom Sala Invisível - Azevedo Sodré','Ambiente "sala invisível" na unidade Azevedo Sodré.','bi-eye-slash',  4,    0, 0, 18),
('Showroom Todos - Azevedo Sodré',        'Ambiente integrado na unidade Azevedo Sodré.',    'bi-grid-3x3-gap',  15,   0, 0, 19),
('visita a parceiro',            'Visita a parceiro externo.',                               'bi-geo-alt',       NULL, 0, 0, 20);
