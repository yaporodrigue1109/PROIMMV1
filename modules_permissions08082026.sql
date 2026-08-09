-- Gestion des modules, actions et permissions par rôle
-- Compatible MariaDB/MySQL et importable depuis phpMyAdmin.
-- Faire une sauvegarde de la base avant l'exécution.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Le projet utilise des identifiants texte historiques (role-responsable, etc.)
-- et génère des UUID pour les nouveaux rôles. VARCHAR(150) accepte les deux.
ALTER TABLE `roles`
    MODIFY COLUMN `role_id` VARCHAR(150) NOT NULL,
    MODIFY COLUMN `agence_id` VARCHAR(150) NULL;

ALTER TABLE `roles`
    ADD COLUMN IF NOT EXISTS `slug` VARCHAR(150) NULL AFTER `name`,
    ADD COLUMN IF NOT EXISTS `is_active` TINYINT(1) NOT NULL DEFAULT 1 AFTER `description`,
    ADD COLUMN IF NOT EXISTS `base_role_id` VARCHAR(150) NULL AFTER `agence_id`;

UPDATE `roles`
SET `slug` = CASE
    WHEN `role_id` = 'role-responsable' THEN 'role-responsable'
    WHEN `role_id` = 'role-agent' THEN 'role-agent'
    WHEN `role_id` = 'role-comptable' THEN 'role-comptable'
    WHEN `role_id` = 'role-technicien' THEN 'role-technicien'
    WHEN LOWER(TRIM(`name`)) = 'super admin' THEN 'super-admin'
    WHEN LOWER(TRIM(`name`)) = 'administrateur' THEN 'admin'
    ELSE LOWER(REPLACE(TRIM(`name`), ' ', '-'))
END
WHERE `slug` IS NULL OR `slug` = '';

UPDATE `roles` SET `is_active` = 1 WHERE `is_active` IS NULL;

-- Garantit la présence des rôles historiques utilisés par les utilisateurs.
INSERT INTO `roles`
    (`role_id`, `name`, `slug`, `description`, `agence_id`, `is_active`, `created_by`, `updated_by`, `created_at`, `updated_at`)
VALUES
    ('role-responsable', 'Responsable', 'role-responsable', 'Responsable d’agence', NULL, 1, NULL, NULL, NOW(), NOW()),
    ('role-agent', 'Agent', 'role-agent', 'Agent immobilier', NULL, 1, NULL, NULL, NOW(), NOW()),
    ('role-comptable', 'Comptable', 'role-comptable', 'Comptable', NULL, 1, NULL, NULL, NOW(), NOW()),
    ('role-technicien', 'Technicien', 'role-technicien', 'Technicien de maintenance', NULL, 1, NULL, NULL, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `slug` = VALUES(`slug`),
    `is_active` = 1,
    `updated_at` = NOW();

CREATE TABLE IF NOT EXISTS `modules` (
    `module_id` CHAR(36) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL,
    `icon` VARCHAR(255) NULL,
    `route` VARCHAR(255) NULL,
    `parent_id` CHAR(36) NULL,
    `order_index` INT UNSIGNED NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_by` VARCHAR(150) NULL,
    `updated_by` VARCHAR(150) NULL,
    `deleted_by` VARCHAR(150) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`module_id`),
    UNIQUE KEY `modules_slug_unique` (`slug`),
    KEY `modules_parent_order_index` (`parent_id`, `order_index`),
    KEY `modules_active_index` (`is_active`),
    CONSTRAINT `modules_parent_id_foreign`
        FOREIGN KEY (`parent_id`) REFERENCES `modules` (`module_id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `module_actions` (
    `module_action_id` CHAR(36) NOT NULL,
    `module_id` CHAR(36) NOT NULL,
    `name` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `is_critical` TINYINT(1) NOT NULL DEFAULT 0,
    `order_index` INT UNSIGNED NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_by` VARCHAR(150) NULL,
    `updated_by` VARCHAR(150) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`module_action_id`),
    UNIQUE KEY `module_actions_module_slug_unique` (`module_id`, `slug`),
    UNIQUE KEY `module_actions_id_module_unique` (`module_action_id`, `module_id`),
    KEY `module_actions_active_order_index` (`module_id`, `is_active`, `order_index`),
    CONSTRAINT `module_actions_module_id_foreign`
        FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `module_actions`
    ADD COLUMN IF NOT EXISTS `is_critical` TINYINT(1) NOT NULL DEFAULT 0 AFTER `description`;

CREATE TABLE IF NOT EXISTS `role_permissions` (
    `role_permission_id` CHAR(36) NOT NULL,
    `role_id` VARCHAR(150) NOT NULL,
    `module_id` CHAR(36) NOT NULL,
    `module_action_id` CHAR(36) NOT NULL,
    `is_allowed` TINYINT(1) NOT NULL DEFAULT 1,
    `created_by` VARCHAR(150) NULL,
    `updated_by` VARCHAR(150) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`role_permission_id`),
    UNIQUE KEY `role_permissions_role_module_action_unique` (`role_id`, `module_id`, `module_action_id`),
    KEY `role_permissions_allowed_index` (`role_id`, `is_allowed`),
    KEY `role_permissions_module_id_index` (`module_id`),
    KEY `role_permissions_module_action_id_index` (`module_action_id`),
    CONSTRAINT `role_permissions_role_id_foreign`
        FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `role_permissions_module_action_module_foreign`
        FOREIGN KEY (`module_action_id`, `module_id`)
        REFERENCES `module_actions` (`module_action_id`, `module_id`)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Modules principaux et sous-modules utilisés par les permissions historiques.
INSERT INTO `modules`
    (`module_id`, `name`, `slug`, `icon`, `route`, `parent_id`, `order_index`, `is_active`, `created_at`, `updated_at`)
VALUES
    ('10000000-0000-4000-8000-000000000001', 'Tableau de bord', 'dashboard', 'admin/icones_module/dashboard.svg', 'agence.dashboard', NULL, 1, 1, NOW(), NOW()),
    ('10000000-0000-4000-8000-000000000002', 'Propriétés', 'proprietes', 'admin/icones_module/proprietes.svg', 'agence.proprietes.index', NULL, 2, 1, NOW(), NOW()),
    ('10000000-0000-4000-8000-000000000003', 'Propriétaires', 'proprietaires', 'admin/icones_module/proprietaires.svg', 'agence.proprietaire.index', NULL, 3, 1, NOW(), NOW()),
    ('10000000-0000-4000-8000-000000000004', 'Locataires', 'locataires', 'admin/icones_module/locataires.svg', 'agence.locataires.index', NULL, 4, 1, NOW(), NOW()),
    ('10000000-0000-4000-8000-000000000005', 'Personnel', 'personnel', 'admin/icones_module/personnel.svg', 'agence.personnel.index', NULL, 5, 1, NOW(), NOW()),
    ('10000000-0000-4000-8000-000000000006', 'Maintenance', 'maintenance', 'admin/icones_module/maintenance.svg', 'agence.maintenance.index', NULL, 6, 1, NOW(), NOW()),
    ('10000000-0000-4000-8000-000000000007', 'Caisse', 'caisse', 'admin/icones_module/caisse.svg', 'agence.caisse.index', NULL, 7, 1, NOW(), NOW()),
    ('10000000-0000-4000-8000-000000000008', 'Reversement', 'reversement', 'admin/icones_module/reversement.svg', 'agence.reversements.index', NULL, 8, 1, NOW(), NOW()),
    ('10000000-0000-4000-8000-000000000009', 'Statistiques', 'statistiques', 'admin/icones_module/statistiques.svg', 'agence.statistiques.index', NULL, 9, 1, NOW(), NOW()),
    ('10000000-0000-4000-8000-000000000010', 'Support', 'support', 'admin/icones_module/support.svg', 'agence.support.index', NULL, 10, 1, NOW(), NOW()),
    ('10000000-0000-4000-8000-000000000011', 'Paramétrage', 'parametrage', 'admin/icones_module/parametrage.svg', 'agence.parametrage.index', NULL, 11, 1, NOW(), NOW()),
    ('10000000-0000-4000-8000-000000000012', 'Contrats', 'contrats', NULL, NULL, '10000000-0000-4000-8000-000000000004', 1, 1, NOW(), NOW()),
    ('10000000-0000-4000-8000-000000000013', 'Rapports', 'rapports', NULL, NULL, '10000000-0000-4000-8000-000000000009', 1, 1, NOW(), NOW()),
    ('10000000-0000-4000-8000-000000000014', 'Loyers', 'loyer', NULL, 'agence.caisse.loyer', '10000000-0000-4000-8000-000000000007', 1, 1, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `icon` = VALUES(`icon`),
    `route` = VALUES(`route`),
    `parent_id` = VALUES(`parent_id`),
    `order_index` = VALUES(`order_index`),
    `updated_at` = NOW();

-- Catalogue des actions par module. La contrainte (module_id, slug) évite les doublons.
INSERT INTO `module_actions`
    (`module_action_id`, `module_id`, `name`, `slug`, `order_index`, `is_active`, `created_at`, `updated_at`)
SELECT UUID(), m.`module_id`, a.`name`, a.`slug`, a.`order_index`, 1, NOW(), NOW()
FROM `modules` m
INNER JOIN (
    SELECT 'dashboard' module_slug, 'Voir' name, 'view' slug, 1 order_index
    UNION ALL SELECT 'proprietes', 'Voir', 'view', 1
    UNION ALL SELECT 'proprietes', 'Créer', 'create', 2
    UNION ALL SELECT 'proprietes', 'Modifier', 'edit', 3
    UNION ALL SELECT 'proprietes', 'Supprimer', 'delete', 4
    UNION ALL SELECT 'proprietaires', 'Voir', 'view', 1
    UNION ALL SELECT 'proprietaires', 'Créer', 'create', 2
    UNION ALL SELECT 'proprietaires', 'Modifier', 'edit', 3
    UNION ALL SELECT 'proprietaires', 'Supprimer', 'delete', 4
    UNION ALL SELECT 'locataires', 'Voir', 'view', 1
    UNION ALL SELECT 'locataires', 'Créer', 'create', 2
    UNION ALL SELECT 'locataires', 'Modifier', 'edit', 3
    UNION ALL SELECT 'locataires', 'Supprimer', 'delete', 4
    UNION ALL SELECT 'personnel', 'Voir', 'view', 1
    UNION ALL SELECT 'personnel', 'Créer', 'create', 2
    UNION ALL SELECT 'personnel', 'Modifier', 'edit', 3
    UNION ALL SELECT 'personnel', 'Supprimer', 'delete', 4
    UNION ALL SELECT 'personnel', 'Gérer', 'manage', 5
    UNION ALL SELECT 'maintenance', 'Voir', 'view', 1
    UNION ALL SELECT 'maintenance', 'Créer', 'create', 2
    UNION ALL SELECT 'maintenance', 'Modifier', 'edit', 3
    UNION ALL SELECT 'maintenance', 'Supprimer', 'delete', 4
    UNION ALL SELECT 'maintenance', 'Valider', 'validate', 5
    UNION ALL SELECT 'maintenance', 'Annuler', 'cancel', 6
    UNION ALL SELECT 'caisse', 'Voir', 'view', 1
    UNION ALL SELECT 'caisse', 'Créer', 'create', 2
    UNION ALL SELECT 'caisse', 'Valider', 'validate', 3
    UNION ALL SELECT 'caisse', 'Annuler', 'cancel', 4
    UNION ALL SELECT 'reversement', 'Voir', 'view', 1
    UNION ALL SELECT 'reversement', 'Créer', 'create', 2
    UNION ALL SELECT 'reversement', 'Modifier', 'edit', 3
    UNION ALL SELECT 'reversement', 'Supprimer', 'delete', 4
    UNION ALL SELECT 'reversement', 'Valider', 'validate', 5
    UNION ALL SELECT 'reversement', 'Annuler', 'cancel', 6
    UNION ALL SELECT 'reversement', 'Exporter', 'export', 7
    UNION ALL SELECT 'statistiques', 'Voir', 'view', 1
    UNION ALL SELECT 'statistiques', 'Exporter', 'export', 2
    UNION ALL SELECT 'support', 'Voir', 'view', 1
    UNION ALL SELECT 'support', 'Créer', 'create', 2
    UNION ALL SELECT 'support', 'Modifier', 'edit', 3
    UNION ALL SELECT 'support', 'Clôturer', 'close', 4
    UNION ALL SELECT 'parametrage', 'Voir', 'view', 1
    UNION ALL SELECT 'parametrage', 'Modifier', 'edit', 2
    UNION ALL SELECT 'contrats', 'Voir', 'view', 1
    UNION ALL SELECT 'contrats', 'Créer', 'create', 2
    UNION ALL SELECT 'contrats', 'Modifier', 'edit', 3
    UNION ALL SELECT 'contrats', 'Supprimer', 'delete', 4
    UNION ALL SELECT 'rapports', 'Voir', 'view', 1
    UNION ALL SELECT 'rapports', 'Exporter', 'export', 2
    UNION ALL SELECT 'loyer', 'Voir', 'view', 1
    UNION ALL SELECT 'loyer', 'Créer', 'create', 2
    UNION ALL SELECT 'loyer', 'Modifier', 'edit', 3
    UNION ALL SELECT 'loyer', 'Valider', 'validate', 4
) a ON a.`module_slug` = m.`slug`
ON DUPLICATE KEY UPDATE
    `name` = VALUES(`name`),
    `order_index` = VALUES(`order_index`),
    `is_active` = 1,
    `updated_at` = NOW();

UPDATE `module_actions`
SET `is_critical` = 1
WHERE `slug` IN ('delete', 'validate', 'cancel', 'manage');

-- Super administrateurs et administrateurs : toutes les actions actives.
INSERT INTO `role_permissions`
    (`role_permission_id`, `role_id`, `module_id`, `module_action_id`, `is_allowed`, `created_at`, `updated_at`)
SELECT UUID(), r.`role_id`, m.`module_id`, a.`module_action_id`, 1, NOW(), NOW()
FROM `roles` r
CROSS JOIN `modules` m
INNER JOIN `module_actions` a ON a.`module_id` = m.`module_id`
WHERE r.`slug` IN ('admin', 'super-admin', 'super_admin')
  AND r.`is_active` = 1
  AND m.`is_active` = 1
  AND a.`is_active` = 1
ON DUPLICATE KEY UPDATE `is_allowed` = 1, `updated_at` = NOW();

-- Le tableau de bord reste accessible à tous les rôles standards actifs.
INSERT INTO `role_permissions`
    (`role_permission_id`, `role_id`, `module_id`, `module_action_id`, `is_allowed`, `created_at`, `updated_at`)
SELECT UUID(), r.`role_id`, m.`module_id`, a.`module_action_id`, 1, NOW(), NOW()
FROM `roles` r
INNER JOIN `modules` m ON m.`slug` = 'dashboard'
INNER JOIN `module_actions` a ON a.`module_id` = m.`module_id` AND a.`slug` = 'view'
WHERE r.`slug` IN (
    'role-responsable', 'responsable', 'role-agent', 'agent',
    'role-comptable', 'comptable', 'role-technicien', 'technicien'
)
  AND r.`is_active` = 1
ON DUPLICATE KEY UPDATE `is_allowed` = 1, `updated_at` = NOW();

-- Responsables : toutes les actions actives, y compris celles ajoutÃ©es au catalogue.
INSERT INTO `role_permissions`
    (`role_permission_id`, `role_id`, `module_id`, `module_action_id`, `is_allowed`, `created_at`, `updated_at`)
SELECT UUID(), r.`role_id`, m.`module_id`, a.`module_action_id`, 1, NOW(), NOW()
FROM `roles` r
CROSS JOIN `modules` m
INNER JOIN `module_actions` a ON a.`module_id` = m.`module_id`
WHERE r.`slug` IN ('role-responsable', 'responsable')
  AND r.`is_active` = 1
  AND m.`is_active` = 1
  AND a.`is_active` = 1
ON DUPLICATE KEY UPDATE `is_allowed` = 1, `updated_at` = NOW();

-- Agents : reprise exacte des permissions historiques de User.php.
INSERT INTO `role_permissions`
    (`role_permission_id`, `role_id`, `module_id`, `module_action_id`, `is_allowed`, `created_at`, `updated_at`)
SELECT UUID(), r.`role_id`, m.`module_id`, a.`module_action_id`, 1, NOW(), NOW()
FROM `roles` r
CROSS JOIN `modules` m
INNER JOIN `module_actions` a ON a.`module_id` = m.`module_id`
WHERE r.`slug` IN ('role-agent', 'agent')
  AND CONCAT(a.`slug`, '_', m.`slug`) IN (
      'view_proprietes', 'create_proprietes', 'edit_proprietes',
      'view_contrats', 'create_contrats',
      'view_locataires', 'create_locataires',
      'view_proprietaires', 'view_rapports', 'view_caisse', 'view_loyer'
  )
ON DUPLICATE KEY UPDATE `is_allowed` = 1, `updated_at` = NOW();

-- Comptables : reprise exacte des permissions historiques de User.php.
INSERT INTO `role_permissions`
    (`role_permission_id`, `role_id`, `module_id`, `module_action_id`, `is_allowed`, `created_at`, `updated_at`)
SELECT UUID(), r.`role_id`, m.`module_id`, a.`module_action_id`, 1, NOW(), NOW()
FROM `roles` r
CROSS JOIN `modules` m
INNER JOIN `module_actions` a ON a.`module_id` = m.`module_id`
WHERE r.`slug` IN ('role-comptable', 'comptable')
  AND CONCAT(a.`slug`, '_', m.`slug`) IN (
      'view_contrats', 'view_rapports', 'export_rapports',
      'view_caisse', 'view_loyer', 'view_reversement'
  )
ON DUPLICATE KEY UPDATE `is_allowed` = 1, `updated_at` = NOW();

SET FOREIGN_KEY_CHECKS = 1;

-- Contrôles rapides à consulter après l'import.
SELECT COUNT(*) AS nombre_modules FROM `modules`;
SELECT COUNT(*) AS nombre_actions FROM `module_actions`;
SELECT COUNT(*) AS nombre_permissions_roles FROM `role_permissions`;
