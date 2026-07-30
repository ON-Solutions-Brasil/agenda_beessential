-- Migration: Itens de demonstração das salas (checklist / cardápio)
-- Data: 2026-07-30

CREATE TABLE IF NOT EXISTS `room_items` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `room_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `description` VARCHAR(255) DEFAULT NULL,
    `icon` VARCHAR(50) NOT NULL DEFAULT 'bi-check2-circle',
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_room_items_room` (`room_id`),
    INDEX `idx_room_items_active` (`active`),
    CONSTRAINT `fk_room_items_room` FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─────────────────────────────────────────────────────────────
-- Itens de demonstração (prévia para o visitante + checklist do vendedor)
-- room_id 1 = CJ - Showroom | 2 = STS - Reunião Interna
-- 3 = STS - Reunião Showroom | 4 = STS - Select | 5 = STS - Showroom (Apresentação)
-- ─────────────────────────────────────────────────────────────
INSERT INTO `room_items` (`room_id`, `name`, `description`, `icon`, `active`, `sort_order`) VALUES
-- CJ - Showroom
(1, 'Automação Residencial', 'Cenários de iluminação e controle inteligente da casa.', 'bi-house-gear',   1, 1),
(1, 'Áudio Multiroom',        'Som ambiente distribuído por vários cômodos.',           'bi-speaker',      1, 2),
(1, 'Home Theater',           'Experiência de cinema com áudio surround.',              'bi-film',         1, 3),
(1, 'Cortinas Motorizadas',   'Abertura e fechamento automatizado.',                    'bi-curtains',     1, 4),
(1, 'Controle por App',       'Comando de tudo pelo smartphone.',                       'bi-phone',        1, 5),

-- STS - Reunião Interna
(2, 'TV de Apresentação',     'Tela para reuniões e apresentações.',                    'bi-tv',           1, 1),
(2, 'Videoconferência',       'Sistema de chamada com câmera e microfone.',             'bi-camera-video', 1, 2),
(2, 'Lousa Digital',          'Quadro interativo para anotações.',                      'bi-easel2',       1, 3),

-- STS - Reunião Showroom
(3, 'Mesa de Reunião',        'Ambiente para atendimento de clientes.',                 'bi-table',        1, 1),
(3, 'Amostras de Produtos',   'Catálogo físico para demonstração.',                     'bi-box-seam',     1, 2),
(3, 'Painel Demonstrativo',   'Painel com cenários de automação.',                      'bi-columns-gap',  1, 3),

-- STS - Select
(4, 'Atendimento Premium',    'Espaço reservado e exclusivo.',                          'bi-stars',        1, 1),
(4, 'Degustação',             'Café e cortesias para o cliente.',                       'bi-cup-hot',      1, 2),
(4, 'Consultoria Técnica',    'Especialista para tirar dúvidas.',                       'bi-person-video3',1, 3),

-- STS - Showroom (Apresentação)
(5, 'Projeção em Tela Grande','Apresentação em alta resolução.',                        'bi-projector',    1, 1),
(5, 'Som Profissional',       'Sistema de áudio de alta fidelidade.',                   'bi-soundwave',    1, 2),
(5, 'Iluminação Cênica',      'Cenários de luz para ambientação.',                      'bi-lightbulb',    1, 3),
(5, 'Demonstração Interativa','Cliente controla os cenários ao vivo.',                  'bi-hand-index',   1, 4);
