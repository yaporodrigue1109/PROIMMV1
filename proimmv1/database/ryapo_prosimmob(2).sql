-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 09, 2026 at 09:42 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ryapo_prosimmob`
--

-- --------------------------------------------------------

--
-- Table structure for table `abonnements`
--

CREATE TABLE `abonnements` (
  `abonnement_id` bigint(20) UNSIGNED NOT NULL,
  `code_abonnement` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `prix_mensuel_ht` decimal(12,2) NOT NULL DEFAULT 0.00,
  `prix_annuel_ht` decimal(12,2) DEFAULT NULL,
  `nb_proprietes_max` int(11) DEFAULT NULL,
  `nb_locataires_max` int(11) DEFAULT NULL,
  `nb_utilisateurs_max` int(11) DEFAULT NULL,
  `module_comptabilite` tinyint(4) NOT NULL DEFAULT 0,
  `module_reporting` tinyint(4) NOT NULL DEFAULT 0,
  `module_api` tinyint(4) NOT NULL DEFAULT 0,
  `statut` enum('actif','inactif','archive') NOT NULL DEFAULT 'actif',
  `is_default` tinyint(4) NOT NULL DEFAULT 0,
  `ordre` int(11) NOT NULL DEFAULT 0,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `created_by` varchar(255) DEFAULT NULL,
  `updated_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `abonnement_historiques`
--

CREATE TABLE `abonnement_historiques` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `agence_id` varchar(255) NOT NULL,
  `ancien_abonnement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nouvel_abonnement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ancienne_date_debut` date DEFAULT NULL,
  `ancienne_date_fin` date DEFAULT NULL,
  `nouvelle_date_debut` date DEFAULT NULL,
  `nouvelle_date_fin` date DEFAULT NULL,
  `duree_mois` int(11) DEFAULT NULL,
  `montant_ht` decimal(12,2) NOT NULL DEFAULT 0.00,
  `action` enum('creation','renouvellement','changement','annulation') NOT NULL DEFAULT 'creation',
  `action_par` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `abonnement_historiques`
--

INSERT INTO `abonnement_historiques` (`id`, `agence_id`, `ancien_abonnement_id`, `nouvel_abonnement_id`, `ancienne_date_debut`, `ancienne_date_fin`, `nouvelle_date_debut`, `nouvelle_date_fin`, `duree_mois`, `montant_ht`, `action`, `action_par`, `notes`, `created_at`, `updated_at`) VALUES
(6, 'a299981a-d1c1-4690-8e96-d6ddb5df0874', NULL, NULL, NULL, NULL, '2026-05-11', '2027-05-11', 12, 1440000.00, 'creation', 'ADM-001', NULL, '2026-05-11 16:49:43', '2026-05-11 16:49:43');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id_admin` varchar(150) NOT NULL,
  `name` varchar(250) NOT NULL,
  `phone` varchar(150) DEFAULT NULL,
  `email` varchar(250) NOT NULL,
  `statut` tinyint(4) NOT NULL DEFAULT 1,
  `password` varchar(250) NOT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `deleted_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id_admin`, `name`, `phone`, `email`, `statut`, `password`, `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`, `remember_token`) VALUES
('11111111-1111-1111-1111-111111111111', 'Admin Principal', '0000000000', 'admin@pros-immobilier.test', 1, '$2y$12$.SrAPgD3SjbkOdyTR6C3IeBGjl3K5.WB7HNL7hXrt0744wddrEhsy', NULL, NULL, NULL, '2026-07-09 11:41:01', '2026-07-09 11:41:01', NULL, 'XTiIki4aJp'),
('ADM-001', 'Super Admin', '0700000000', 'admin@test.com', 1, '$2y$12$NK9bPu725StydDkvjqHXxOOM7ZnmUhFv67bbrmd33n1Ngi0M9Ssjm', NULL, NULL, NULL, '2026-04-22 00:14:32', '2026-07-06 11:37:31', NULL, 'VFx7PTjHZOptfmv8B44y2Z8f6g6Y0y9NQzNmWKmsi38synHw7MEXXCT0ghzU');

-- --------------------------------------------------------

--
-- Table structure for table `agences`
--

CREATE TABLE `agences` (
  `agence_id` varchar(150) NOT NULL,
  `name` varchar(250) NOT NULL,
  `code_agence` varchar(150) DEFAULT NULL,
  `adresse` varchar(150) NOT NULL,
  `tel1` varchar(50) NOT NULL,
  `tel2` varchar(50) DEFAULT NULL,
  `email1` varchar(250) NOT NULL,
  `email2` varchar(250) DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  `ville_id` int(11) DEFAULT NULL,
  `statut` enum('en_demo','active','desactive') NOT NULL DEFAULT 'en_demo',
  `is_principale` tinyint(4) NOT NULL DEFAULT 1,
  `parent_id` varchar(150) DEFAULT NULL,
  `responsable_id` varchar(150) DEFAULT NULL,
  `abonnement_id` varchar(150) DEFAULT NULL,
  `abonnement_start` datetime DEFAULT NULL,
  `abonnement_end` datetime DEFAULT NULL,
  `duree_mois` int(11) DEFAULT NULL,
  `rib` varchar(150) DEFAULT NULL,
  `agence_bancaire` varchar(150) DEFAULT NULL,
  `banque` varchar(150) DEFAULT NULL,
  `site_web` varchar(250) DEFAULT NULL,
  `bp` varchar(150) DEFAULT NULL,
  `regime_fiscal` varchar(50) DEFAULT NULL,
  `num_contribuable` varchar(150) DEFAULT NULL,
  `rccm` varchar(150) DEFAULT NULL,
  `sigle` varchar(150) DEFAULT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `deleted_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `agences`
--

INSERT INTO `agences` (`agence_id`, `name`, `code_agence`, `adresse`, `tel1`, `tel2`, `email1`, `email2`, `region_id`, `ville_id`, `statut`, `is_principale`, `parent_id`, `responsable_id`, `abonnement_id`, `abonnement_start`, `abonnement_end`, `duree_mois`, `rib`, `agence_bancaire`, `banque`, `site_web`, `bp`, `regime_fiscal`, `num_contribuable`, `rccm`, `sigle`, `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('22222222-2222-2222-2222-222222222222', 'Agence Demo', 'AG-DEMO-001', 'Abidjan', '0102030405', NULL, 'agence.demo@pros-immobilier.test', NULL, NULL, NULL, 'active', 1, NULL, '33333333-3333-3333-3333-333333333333', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '11111111-1111-1111-1111-111111111111', '11111111-1111-1111-1111-111111111111', NULL, '2026-07-09 11:41:01', '2026-07-09 11:41:01', NULL),
('2df2a9f8-5d56-4842-a683-8676eb1d017f', 'Agence de bingerville', 'AG-2026-\n0001', 'Bingerville', '0707902963', NULL, 'agence@bingerville.ci', NULL, 1, 1, 'active', 1, NULL, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'BP 123 Abidjan 01', 'SARL', NULL, NULL, NULL, NULL, 'ADM-001', NULL, '2026-04-27 00:01:07', '2026-06-09 08:47:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `batiment`
--

CREATE TABLE `batiment` (
  `batiment_id` varchar(150) NOT NULL,
  `propriete_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `nbre_etages` int(11) DEFAULT 0,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `batiment`
--

INSERT INTO `batiment` (`batiment_id`, `propriete_id`, `agence_id`, `name`, `description`, `nbre_etages`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
('42ddb365-a8c2-4551-b1f4-9819322c35ef', '3c4ab403-e08f-4193-a0f1-0341f02a3ebd', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'FRR', NULL, 0, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-07-07 13:19:20', '2026-07-07 13:19:20'),
('66898dee-57dd-48c4-9358-97770602368a', '2254a569-9bee-45dd-bfec-4d0d3212fa5b', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'Bâtiment 2', NULL, 0, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-05-26 14:06:33', '2026-05-26 14:06:33'),
('79a83de8-04bb-4a25-a83e-48014879b541', '1a85c585-565f-4946-aecb-8a95677ec9d0', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'FRRSSS', NULL, 0, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-07-07 14:04:11', '2026-07-07 14:04:11'),
('7d5ffc45-c4e9-47a6-930b-3a48a8918778', '7eb908bc-f886-498a-a6a5-5875baf25e32', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'ABATTA', NULL, 0, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-07-07 10:37:09', '2026-07-07 10:37:09'),
('7e0d3674-2d1c-4c17-9011-d358d41b8978', 'b8b380ae-b180-49b5-88da-032e73828401', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'BETEOP', 'RAS', 0, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-07-07 10:16:51', '2026-07-07 10:16:51'),
('8ae0d4b2-84e8-4d02-8097-359bf6b0648c', '535e3431-6166-4f51-aa37-36931cc03d3d', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'Espoir', NULL, 0, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-07-09 17:52:43', '2026-07-09 17:52:43'),
('8e489f28-784f-46d8-bb57-da2fc1b466a5', '52d5341f-031c-47e3-95b4-1ebb00560bf7', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'A', NULL, 1, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-07-07 08:35:02', '2026-07-07 08:35:02'),
('915eb859-b82f-48b9-aca8-efb7cabfc411', 'b8b380ae-b180-49b5-88da-032e73828401', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'ATEOP', 'RAS', 2, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-07-07 10:16:50', '2026-07-07 10:16:50'),
('dd839ae3-4ffa-4863-a20d-bf0147125f39', '2254a569-9bee-45dd-bfec-4d0d3212fa5b', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'Bâtiment 1', 'test', 0, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-05-22 20:24:53', '2026-05-22 20:24:53');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(250) NOT NULL,
  `value` text DEFAULT NULL,
  `expiration` bigint(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `caisses`
--

CREATE TABLE `caisses` (
  `caisse_id` bigint(20) UNSIGNED NOT NULL,
  `agence_id` bigint(20) UNSIGNED NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `solde` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `configurations`
--

CREATE TABLE `configurations` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `boite_postal` varchar(150) DEFAULT NULL,
  `contact1` varchar(50) DEFAULT NULL,
  `contact2` varchar(50) DEFAULT NULL,
  `contact3` varchar(50) DEFAULT NULL,
  `langue` enum('fr','en') DEFAULT 'fr',
  `adresse` varchar(250) DEFAULT NULL,
  `raison_social` varchar(250) DEFAULT NULL,
  `site_web` varchar(250) DEFAULT NULL,
  `politique_confidentialite` text DEFAULT NULL,
  `condition_generale` text DEFAULT NULL,
  `cgu` text DEFAULT NULL,
  `email1` varchar(50) DEFAULT NULL,
  `email2` varchar(50) DEFAULT NULL,
  `logo` varchar(150) DEFAULT NULL,
  `flavicon` varchar(150) DEFAULT NULL,
  `num_rccm` varchar(150) DEFAULT NULL,
  `capital` int(11) DEFAULT 0,
  `num_cnps` varchar(20) DEFAULT NULL,
  `num_cc` varchar(150) DEFAULT NULL,
  `facebook` varchar(150) DEFAULT NULL,
  `instagram` varchar(150) DEFAULT NULL,
  `linkedin` varchar(150) DEFAULT NULL,
  `google` varchar(250) DEFAULT NULL,
  `twitter` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `configurations`
--

INSERT INTO `configurations` (`id`, `name`, `boite_postal`, `contact1`, `contact2`, `contact3`, `langue`, `adresse`, `raison_social`, `site_web`, `politique_confidentialite`, `condition_generale`, `cgu`, `email1`, `email2`, `logo`, `flavicon`, `num_rccm`, `capital`, `num_cnps`, `num_cc`, `facebook`, `instagram`, `linkedin`, `google`, `twitter`, `created_at`, `updated_at`) VALUES
(1, 'PROSIMMOBILIER', NULL, '0707902962', '0142259037', NULL, 'fr', 'Bingerville', 'SARL', 'https://prosimmobilier.com', NULL, NULL, NULL, 'info@rodrigue-yapo.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-03 04:14:44', '2026-05-08 10:12:49');

-- --------------------------------------------------------

--
-- Table structure for table `configuration_tarifs`
--

CREATE TABLE `configuration_tarifs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `plan_nom` varchar(255) NOT NULL DEFAULT 'Abonnement de base',
  `plan_prix_mensuel` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delai_grace` int(11) NOT NULL DEFAULT 0,
  `cycle_facturation` enum('mensuel','annuel') NOT NULL DEFAULT 'mensuel',
  `plan_description` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `configuration_tarifs`
--

INSERT INTO `configuration_tarifs` (`id`, `plan_nom`, `plan_prix_mensuel`, `delai_grace`, `cycle_facturation`, `plan_description`, `created_at`, `updated_at`) VALUES
(1, 'Abonnement de base', 50000.00, 7, 'mensuel', 'Notre plan de base offre accès complet à la plateforme avec support standard.', '2026-05-03 05:36:11', '2026-05-03 06:10:55');

-- --------------------------------------------------------

--
-- Table structure for table `configuration_tarif_durees`
--

CREATE TABLE `configuration_tarif_durees` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tarif_id` bigint(20) UNSIGNED NOT NULL,
  `nombre_mois` int(11) NOT NULL,
  `prix_reduit` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `configuration_tarif_durees`
--

INSERT INTO `configuration_tarif_durees` (`id`, `tarif_id`, `nombre_mois`, `prix_reduit`, `created_at`, `updated_at`) VALUES
(43, 1, 1, NULL, '2026-05-03 10:05:59', '2026-05-03 10:05:59'),
(44, 1, 3, NULL, '2026-05-03 10:05:59', '2026-05-03 10:05:59'),
(45, 1, 6, NULL, '2026-05-03 10:05:59', '2026-05-03 10:05:59'),
(46, 1, 12, NULL, '2026-05-03 10:05:59', '2026-05-03 10:05:59'),
(47, 1, 24, NULL, '2026-05-03 10:05:59', '2026-05-03 10:05:59'),
(48, 1, 36, NULL, '2026-05-03 10:05:59', '2026-05-03 10:05:59');

-- --------------------------------------------------------

--
-- Table structure for table `configuration_tarif_modules`
--

CREATE TABLE `configuration_tarif_modules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tarif_id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(255) NOT NULL,
  `prix_mensuel` decimal(10,2) NOT NULL DEFAULT 0.00,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `ordre` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `configuration_tarif_modules`
--

INSERT INTO `configuration_tarif_modules` (`id`, `tarif_id`, `label`, `prix_mensuel`, `actif`, `ordre`, `created_at`, `updated_at`) VALUES
(1, 1, 'SMS (Illimité)', 25000.00, 1, 1, '2026-05-03 05:36:11', '2026-05-03 06:21:52'),
(2, 1, 'WhatsApp Business', 2000.00, 1, 2, '2026-05-03 05:36:11', '2026-05-03 06:21:52'),
(3, 1, 'Portail web', 2000.00, 1, 3, '2026-05-03 05:36:11', '2026-05-03 06:21:52'),
(4, 1, 'Statistiques avancées', 2000.00, 1, 4, '2026-05-03 05:36:11', '2026-05-03 06:21:52'),
(5, 1, 'Portail propriétaire', 1000.00, 1, 5, NULL, '2026-05-03 06:24:39'),
(6, 1, 'Portail locataire', 1000.00, 1, 6, NULL, '2026-05-03 06:24:39');

-- --------------------------------------------------------

--
-- Table structure for table `equipement_proprietes`
--

CREATE TABLE `equipement_proprietes` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `equipement_proprietes`
--

INSERT INTO `equipement_proprietes` (`id`, `name`, `agence_id`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Cable internet', '2df2a9f8-5d56-4842-a683-8676eb1d017f', NULL, '2024-12-28 23:11:07', '2024-12-28 23:11:07'),
(2, 'Cable TV', '2df2a9f8-5d56-4842-a683-8676eb1d017f', NULL, '2024-12-28 23:11:21', '2024-12-28 23:11:21'),
(3, 'Compteur CIE', '2df2a9f8-5d56-4842-a683-8676eb1d017f', NULL, '2024-12-28 23:12:07', '2024-12-28 23:12:07'),
(4, 'Compteur SODECI', '2df2a9f8-5d56-4842-a683-8676eb1d017f', NULL, '2024-12-28 23:12:27', '2024-12-28 23:12:27'),
(5, 'Lave-vaisselle', '2df2a9f8-5d56-4842-a683-8676eb1d017f', NULL, '2024-12-28 23:12:55', '2024-12-28 23:12:55'),
(6, 'Balcon', '2df2a9f8-5d56-4842-a683-8676eb1d017f', NULL, '2024-12-28 23:13:21', '2024-12-28 23:14:08'),
(7, 'Piscine', '2df2a9f8-5d56-4842-a683-8676eb1d017f', NULL, '2024-12-28 23:13:46', '2024-12-28 23:13:46'),
(8, 'Terrasse', '2df2a9f8-5d56-4842-a683-8676eb1d017f', NULL, '2024-12-28 23:14:17', '2024-12-28 23:14:17'),
(9, 'CLimatiseur', '2df2a9f8-5d56-4842-a683-8676eb1d017f', NULL, '2024-12-28 23:15:32', '2024-12-28 23:15:32');

-- --------------------------------------------------------

--
-- Table structure for table `fonction_maintenance`
--

CREATE TABLE `fonction_maintenance` (
  `fonction_maintenance_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `name` varchar(250) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fonction_maintenance`
--

INSERT INTO `fonction_maintenance` (`fonction_maintenance_id`, `agence_id`, `name`, `description`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
('22a04e92-f7cf-42c4-b0c2-b229076810cc', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'Electricien', 'Electricien', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-06-02 17:57:13', '2026-06-02 17:57:13'),
('7081858f-c728-4a7a-b551-96346a41c93b', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'Plombier 👨‍🔧', 'Plombier 👨‍🔧', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-06-02 17:56:11', '2026-06-02 17:56:11'),
('f9d1f692-bbc7-4ec3-87f3-98f83fc50fd3', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'Serrurier', 'Serrurier', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-06-02 17:57:32', '2026-06-02 17:57:32');

-- --------------------------------------------------------

--
-- Table structure for table `genres`
--

CREATE TABLE `genres` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `abreviation` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `genres`
--

INSERT INTO `genres` (`id`, `name`, `abreviation`, `created_at`, `updated_at`) VALUES
(1, 'Monsieur', 'M.', '2026-05-13 00:49:21', '0000-00-00 00:00:00'),
(2, 'Madame', 'Mme', '2026-05-13 00:49:21', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `locataire`
--

CREATE TABLE `locataire` (
  `locataire_id` varchar(150) NOT NULL,
  `name` varchar(250) NOT NULL,
  `code` varchar(45) NOT NULL,
  `tel1` varchar(50) NOT NULL,
  `tel2` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  `ville_id` int(11) DEFAULT NULL,
  `adresse` varchar(250) DEFAULT NULL,
  `nationalite` varchar(150) DEFAULT 'IVOIRIENNE',
  `type_piece_id` int(11) NOT NULL,
  `num_piece` varchar(150) NOT NULL,
  `date_expiration_piece` date DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `lieu_naissance` varchar(150) DEFAULT NULL,
  `genre_id` int(11) DEFAULT NULL,
  `photo` varchar(250) DEFAULT NULL,
  `image_pice` varchar(250) DEFAULT NULL,
  `profession` varchar(250) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locataire`
--

INSERT INTO `locataire` (`locataire_id`, `name`, `code`, `tel1`, `tel2`, `email`, `region_id`, `ville_id`, `adresse`, `nationalite`, `type_piece_id`, `num_piece`, `date_expiration_piece`, `date_naissance`, `lieu_naissance`, `genre_id`, `photo`, `image_pice`, `profession`, `password`, `created_at`, `updated_at`) VALUES
('a60b1ee7-a122-4250-9ecc-f87602b8f581', 'Jean', 'VV-52287', '+225236583658', NULL, NULL, NULL, NULL, NULL, NULL, 2, 'CI9452', '2026-08-08', NULL, NULL, 1, NULL, NULL, NULL, '$2y$12$VRO4FTYHr5nCx.atO5mw7udM9RixDmUV8vSmrkYwIVge2g9u6Eiy6', '2026-07-09 17:37:02', '2026-07-09 17:37:02');

-- --------------------------------------------------------

--
-- Table structure for table `locataire_agence`
--

CREATE TABLE `locataire_agence` (
  `locataire_agence_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `locataire_id` varchar(150) NOT NULL,
  `proprietaire_id` varchar(150) NOT NULL,
  `loyer_net` decimal(12,2) DEFAULT NULL,
  `caution` decimal(12,2) DEFAULT NULL,
  `avance` decimal(12,2) DEFAULT NULL,
  `agence` decimal(12,2) DEFAULT NULL,
  `caution_cie` decimal(12,2) DEFAULT NULL,
  `caution_sodeci` decimal(12,2) DEFAULT NULL,
  `frais_annexe` decimal(12,2) DEFAULT NULL,
  `propriete_id` varchar(150) NOT NULL,
  `batiment_id` varchar(150) NOT NULL,
  `lot_id` varchar(150) NOT NULL,
  `porte_id` varchar(150) NOT NULL,
  `nbre_personne` int(11) NOT NULL DEFAULT 1,
  `date_debut_bail` timestamp NULL DEFAULT NULL,
  `date_entree` date DEFAULT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `is_new` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'Ancien locataire=0 nouveau locataire =1',
  `civilite_representant_id` int(11) DEFAULT NULL,
  `name_representant` varchar(150) DEFAULT NULL,
  `adresse_representant` varchar(250) DEFAULT NULL,
  `contant_representant` varchar(50) DEFAULT NULL,
  `nbre_enfant` int(11) NOT NULL DEFAULT 0,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `pas_de_porte` decimal(12,2) DEFAULT NULL,
  `montant_global_garantie` decimal(12,2) DEFAULT NULL,
  `date_signature_bail` date DEFAULT NULL,
  `versements_depot_garantie` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`versements_depot_garantie`)),
  `periodicite_paiement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `mode_paiement_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locataire_agence`
--

INSERT INTO `locataire_agence` (`locataire_agence_id`, `agence_id`, `locataire_id`, `proprietaire_id`, `loyer_net`, `caution`, `avance`, `agence`, `caution_cie`, `caution_sodeci`, `frais_annexe`, `propriete_id`, `batiment_id`, `lot_id`, `porte_id`, `nbre_personne`, `date_debut_bail`, `date_entree`, `is_active`, `is_new`, `civilite_representant_id`, `name_representant`, `adresse_representant`, `contant_representant`, `nbre_enfant`, `created_by`, `updated_by`, `created_at`, `updated_at`, `pas_de_porte`, `montant_global_garantie`, `date_signature_bail`, `versements_depot_garantie`, `periodicite_paiement_id`, `mode_paiement_id`) VALUES
('56abbdd5-f3c7-4cb3-bea1-66abee92a543', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'a60b1ee7-a122-4250-9ecc-f87602b8f581', '0143080c-1088-45b2-a292-edf809feb8e3', 100000.00, 2.00, 2.00, 1.00, 30000.00, 30000.00, 0.00, '2254a569-9bee-45dd-bfec-4d0d3212fa5b', '66898dee-57dd-48c4-9358-97770602368a', '39b36a6d-af4a-4b83-8a03-6a0be94840d8', 'c2a89ff2-c3cb-4069-b0d4-b3c406282d5d', 1, '2026-07-09 00:00:00', '2026-07-09', 1, 1, NULL, NULL, NULL, NULL, 0, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-07-09 17:37:02', '2026-07-09 17:37:02', 0.00, 560000.00, '2026-07-09', '[{\"montant\":\"400000\",\"date_versement\":\"2026-07-09\",\"mode_paiement_id\":\"1\"}]', 3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `loyer`
--

CREATE TABLE `loyer` (
  `loyer_id` varchar(150) NOT NULL,
  `locataire_id` varchar(150) NOT NULL,
  `proprietaire_id` varchar(150) NOT NULL,
  `lot_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `propriete_id` varchar(150) NOT NULL,
  `batiment_id` varchar(150) NOT NULL,
  `porte_id` varchar(150) NOT NULL,
  `statut` enum('Paiement en cours','Paiement partiel','Paiement en retard','Paiement total') NOT NULL DEFAULT 'Paiement en cours',
  `montant_a_payer` int(11) NOT NULL DEFAULT 0,
  `montant_payer` int(11) NOT NULL DEFAULT 0,
  `montant_restant` int(11) NOT NULL DEFAULT 0,
  `montant_proprio` int(11) NOT NULL DEFAULT 0,
  `montant_agence` int(11) NOT NULL DEFAULT 0,
  `montant_global_proprio` int(11) NOT NULL DEFAULT 0,
  `montant_global_agence` int(11) NOT NULL DEFAULT 0,
  `arriere_precedent` int(11) NOT NULL DEFAULT 0,
  `montant_penalite` int(11) NOT NULL DEFAULT 0,
  `is_first` tinyint(4) NOT NULL DEFAULT 0,
  `mode_paiement_id` int(11) DEFAULT NULL,
  `date_paiement` timestamp NULL DEFAULT NULL,
  `mois_paiement` int(11) NOT NULL,
  `annee_paiement` int(11) NOT NULL,
  `date_limit_paiement` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `commentaire` text DEFAULT NULL,
  `creaeted_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loyer`
--

INSERT INTO `loyer` (`loyer_id`, `locataire_id`, `proprietaire_id`, `lot_id`, `agence_id`, `propriete_id`, `batiment_id`, `porte_id`, `statut`, `montant_a_payer`, `montant_payer`, `montant_restant`, `montant_proprio`, `montant_agence`, `montant_global_proprio`, `montant_global_agence`, `arriere_precedent`, `montant_penalite`, `is_first`, `mode_paiement_id`, `date_paiement`, `mois_paiement`, `annee_paiement`, `date_limit_paiement`, `commentaire`, `creaeted_by`, `updated_by`, `created_at`, `updated_at`) VALUES
('2971b114-c488-4ffa-ad91-742329f56456', 'a60b1ee7-a122-4250-9ecc-f87602b8f581', '0143080c-1088-45b2-a292-edf809feb8e3', '39b36a6d-af4a-4b83-8a03-6a0be94840d8', '2df2a9f8-5d56-4842-a683-8676eb1d017f', '2254a569-9bee-45dd-bfec-4d0d3212fa5b', '66898dee-57dd-48c4-9358-97770602368a', 'c2a89ff2-c3cb-4069-b0d4-b3c406282d5d', 'Paiement total', 100000, 100000, 0, 90000, 10000, 90000, 10000, 0, 0, 0, NULL, '2026-07-09 17:37:02', 8, 2026, '2026-08-10 23:59:59', NULL, 'system', NULL, '2026-07-09 17:37:02', '2026-07-09 17:37:02'),
('b3ea86e2-6d80-4a73-af89-f5ae342053d0', 'a60b1ee7-a122-4250-9ecc-f87602b8f581', '0143080c-1088-45b2-a292-edf809feb8e3', '39b36a6d-af4a-4b83-8a03-6a0be94840d8', '2df2a9f8-5d56-4842-a683-8676eb1d017f', '2254a569-9bee-45dd-bfec-4d0d3212fa5b', '66898dee-57dd-48c4-9358-97770602368a', 'c2a89ff2-c3cb-4069-b0d4-b3c406282d5d', 'Paiement total', 100000, 100000, 0, 90000, 10000, 90000, 10000, 0, 0, 1, NULL, '2026-07-09 17:37:02', 7, 2026, '2026-07-10 23:59:59', NULL, 'system', NULL, '2026-07-09 17:37:02', '2026-07-09 17:37:02');

-- --------------------------------------------------------

--
-- Table structure for table `maintenance`
--

CREATE TABLE `maintenance` (
  `maintenance_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `proprietaire_id` varchar(150) DEFAULT NULL,
  `lot_id` varchar(150) DEFAULT NULL,
  `propriete_id` varchar(150) DEFAULT NULL,
  `batiment_id` varchar(150) DEFAULT NULL,
  `porte_id` varchar(150) DEFAULT NULL,
  `titre` varchar(250) NOT NULL,
  `description` text DEFAULT NULL,
  `statut` enum('en attente','en cours','terminer','annuler') NOT NULL DEFAULT 'en attente',
  `montant_global` int(11) NOT NULL DEFAULT 0,
  `prise_en_charge_par` enum('proprietaire','locataire','agence') NOT NULL DEFAULT 'proprietaire',
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `deleted_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance`
--

INSERT INTO `maintenance` (`maintenance_id`, `agence_id`, `proprietaire_id`, `lot_id`, `propriete_id`, `batiment_id`, `porte_id`, `titre`, `description`, `statut`, `montant_global`, `prise_en_charge_par`, `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('361a4f7a-590f-4b67-8e90-3b9a2acd30b1', '2df2a9f8-5d56-4842-a683-8676eb1d017f', '0143080c-1088-45b2-a292-edf809feb8e3', '39b36a6d-af4a-4b83-8a03-6a0be94840d8', NULL, '66898dee-57dd-48c4-9358-97770602368a', 'c2a89ff2-c3cb-4069-b0d4-b3c406282d5d', 'maintenance complet', 'maintenance', 'en attente', 55000, 'proprietaire', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, '2026-06-09 00:11:47', '2026-06-09 00:11:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_detail`
--

CREATE TABLE `maintenance_detail` (
  `maintenance_detail_id` varchar(150) NOT NULL,
  `maintenance_id` varchar(150) NOT NULL,
  `maintenancier_id` varchar(150) NOT NULL,
  `type_intervention_id` varchar(150) NOT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `priorite` enum('basse','normale','haute') NOT NULL DEFAULT 'normale',
  `montant` int(11) NOT NULL DEFAULT 0,
  `note` text DEFAULT NULL,
  `statut` enum('en attente','en cours','terminer','annuler') NOT NULL DEFAULT 'en attente',
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `deleted_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenance_detail`
--

INSERT INTO `maintenance_detail` (`maintenance_detail_id`, `maintenance_id`, `maintenancier_id`, `type_intervention_id`, `date_debut`, `date_fin`, `priorite`, `montant`, `note`, `statut`, `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('16c2abbe-fa7d-44b5-a08f-dd67414507a8', '361a4f7a-590f-4b67-8e90-3b9a2acd30b1', '8eed3e36-5553-49f9-b96e-211eddb51355', '54a4124d-4bdb-4dd7-931f-851279eecea3', '2026-06-11', '2026-07-09', 'normale', 25000, 'maintenance 1', 'en attente', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, '2026-06-09 00:11:47', '2026-06-09 00:11:47', NULL),
('b289585a-e0f2-4a11-9f50-2ad653c6228d', '361a4f7a-590f-4b67-8e90-3b9a2acd30b1', '8eed3e36-5553-49f9-b96e-211eddb51355', '54a4124d-4bdb-4dd7-931f-851279eecea3', '2026-06-16', '2026-07-12', 'basse', 30000, 'maintenance 2', 'en attente', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, '2026-06-09 00:11:47', '2026-06-09 00:11:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `maintenanciers`
--

CREATE TABLE `maintenanciers` (
  `maintenancier_id` varchar(150) NOT NULL,
  `fonction_maintenance_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `name` varchar(250) NOT NULL,
  `tel1` varchar(50) NOT NULL,
  `tel2` varchar(50) DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  `statut` tinyint(4) NOT NULL DEFAULT 1,
  `adresse` varchar(250) DEFAULT NULL,
  `entreprise` varchar(250) DEFAULT NULL,
  `type_piece_id` int(11) DEFAULT NULL,
  `numero_piece` varchar(150) DEFAULT NULL,
  `date_validite_piece` date DEFAULT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `maintenanciers`
--

INSERT INTO `maintenanciers` (`maintenancier_id`, `fonction_maintenance_id`, `agence_id`, `name`, `tel1`, `tel2`, `email`, `statut`, `adresse`, `entreprise`, `type_piece_id`, `numero_piece`, `date_validite_piece`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
('0c69f92a-3bdf-499d-a893-e8fca36dcb59', 'f9d1f692-bbc7-4ec3-87f3-98f83fc50fd3', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'John Doe', '0707902909', '0142259009', 'admin@test.com', 1, 'sdf', NULL, 1, '708937637', '2026-07-04', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-06-08 13:12:41', '2026-06-08 13:12:41'),
('8eed3e36-5553-49f9-b96e-211eddb51355', 'f9d1f692-bbc7-4ec3-87f3-98f83fc50fd3', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'John Doe', '0707902909', '0142259009', 'admin@test.com', 1, 'sdf', NULL, 1, '708937637', '2026-07-04', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-06-08 13:13:31', '2026-06-08 13:13:31');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_07_06_120000_create_propriete_proximites_table', 1),
(2, '2026_07_06_000001_add_mt_frais_dossier_to_tarif_porte_table', 2),
(3, '2026_07_06_000002_change_tarif_porte_porte_id_to_uuid', 3),
(4, '2026_07_07_000001_change_tarif_porte_tarif_id_to_uuid', 4),
(5, '2026_07_07_000002_add_is_allocation_to_porte_table', 5),
(6, '2026_07_07_000002_add_mt_vente_to_tarif_porte_table', 6),
(7, '2026_07_08_000001_add_representant_identity_and_photo_to_proprietaire_agences_table', 7),
(8, '2026_07_08_000002_add_missing_representant_fields_to_proprietaire_agences_table', 8),
(9, '2026_07_08_000003_add_depot_garantie_fields_to_locataire_agence_table', 9),
(10, '2026_07_09_000001_create_periodicite_paiements_table', 9),
(11, '2026_07_09_000002_add_periodicite_paiement_id_to_locataire_agence_table', 10),
(12, '2026_07_09_000003_add_loyer_net_to_locataire_agence_table', 11),
(13, '2026_07_09_000004_add_contract_amount_fields_to_locataire_agence_table', 12),
(14, '2026_07_09_000005_add_mode_paiement_id_to_locataire_agence_table', 13);

-- --------------------------------------------------------

--
-- Table structure for table `mode_paiements`
--

CREATE TABLE `mode_paiements` (
  `id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `mode_paiements`
--

INSERT INTO `mode_paiements` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Espèces', '2025-01-05 18:09:50', '2025-01-05 18:09:50'),
(2, 'MOOV Money', '2025-01-05 18:10:09', '2025-08-27 22:42:05'),
(3, 'MTN Money', '2025-01-05 18:10:37', '2025-08-27 22:42:22'),
(4, 'Chèques', '2025-01-05 18:10:46', '2025-08-27 22:43:39'),
(5, 'ORANGE Money', '2025-01-05 18:11:08', '2025-08-27 22:42:50'),
(6, 'WAVE', '2025-08-27 22:43:07', '2025-08-27 22:43:07');

-- --------------------------------------------------------

--
-- Table structure for table `mouvements_caisse`
--

CREATE TABLE `mouvements_caisse` (
  `mouvement_id` bigint(20) UNSIGNED NOT NULL,
  `caisse_id` bigint(20) UNSIGNED NOT NULL,
  `agence_id` bigint(20) UNSIGNED NOT NULL,
  `transaction_agence_id` bigint(20) UNSIGNED DEFAULT NULL,
  `loyer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type` enum('entree','sortie') NOT NULL,
  `motif` varchar(191) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `mode_paiement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `date_mouvement` date NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parametrages_agence`
--

CREATE TABLE `parametrages_agence` (
  `parametrages_agence_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `devise` varchar(3) DEFAULT 'XOF',
  `langue` varchar(2) DEFAULT 'fr',
  `format_date` varchar(20) DEFAULT 'd/m/Y',
  `timezone` varchar(50) DEFAULT 'Africa/Abidjan',
  `sauvegarde_auto` tinyint(1) DEFAULT 1,
  `double_validation` tinyint(1) DEFAULT 1,
  `journal_activites` tinyint(1) DEFAULT 1,
  `multi_session` tinyint(1) DEFAULT 0,
  `periode_facturation` enum('journalier','mensuelle','trimestrielle','semestrielle','annuelle') DEFAULT 'mensuelle',
  `jour_emission` varchar(50) DEFAULT '1',
  `delai_paiement` int(11) DEFAULT 30,
  `penalite_retard` decimal(5,2) DEFAULT 1.50,
  `prefixe_facture` varchar(50) DEFAULT 'FAC-',
  `sequence_facture` int(11) DEFAULT 1,
  `commission` decimal(5,2) DEFAULT 10.00,
  `base_commission` enum('ht','ttc','brut') DEFAULT 'ttc',
  `tva` decimal(5,2) DEFAULT 18.00,
  `aib` decimal(5,2) DEFAULT 0.00 COMMENT 'AIB — Acompte sur Impôts sur les Bénéfices (spécifique à l''Afrique de l''Ouest, notamment Côte d''Ivoire, Sénégal, Mali).\r\nC''est une retenue fiscale prélevée à la source sur les loyers versés aux propriétaires. L''agence la collecte pour le compte de l''État et la reverse au fisc.\r\n\r\n\r\nExemple : Loyer = 100 000 FCFA, AIB = 15%\r\n→ L''agence retient 15 000 FCFA pour l''État\r\n→ Le propriétaire reçoit 85 000 FCFA (moins la commission)',
  `ras` decimal(5,2) DEFAULT 0.00 COMMENT 'RAS — Retenue À la Source (sur les honoraires / commissions).\r\nC''est la retenue fiscale appliquée sur la commission de l''agence elle-même. Certains propriétaires ou entreprises sont tenus de retenir un % sur les honoraires qu''ils versent aux prestataires.\r\nExemple : Commission agence = 10 000 FCFA, RAS = 5%\r\n→ Retenue de 500 FCFA sur la commission\r\n→ L''agence perçoit réellement 9 500 FCFA',
  `acompte_min` decimal(5,2) DEFAULT 30.00 COMMENT 'acompte_min — Montant minimum d''acompte accepté lors d''un paiement partiel.\r\nC''est le seuil en dessous duquel l''agence refuse un paiement partiel. Si un locataire ne peut pas payer la totalité du loyer, il doit au moins verser ce montant minimum.',
  `mode_reglement_id` int(11) DEFAULT 1,
  `logo` varchar(255) DEFAULT NULL,
  `logo_largeur` int(11) DEFAULT 200,
  `logo_position` enum('gauche','centre','droit') DEFAULT 'gauche',
  `logo_tutelle` varchar(255) DEFAULT NULL,
  `logo_partenaire` varchar(255) DEFAULT NULL,
  `cachet` varchar(255) DEFAULT NULL,
  `signature_dg` varchar(255) DEFAULT NULL,
  `dg_nom` varchar(255) DEFAULT NULL,
  `dg_titre` varchar(255) DEFAULT 'Directeur Général',
  `signature_sg` varchar(255) DEFAULT NULL,
  `sg_nom` varchar(255) DEFAULT NULL,
  `sg_titre` varchar(255) DEFAULT 'Secrétaire Général(e)',
  `signature_cpt` varchar(255) DEFAULT NULL,
  `cpt_nom` varchar(255) DEFAULT NULL,
  `cpt_titre` varchar(255) DEFAULT 'Responsable Comptable',
  `sig_dg_facture` tinyint(1) DEFAULT 1,
  `sig_double` tinyint(1) DEFAULT 1,
  `cachet_auto` tinyint(1) DEFAULT 0,
  `notif_rappel` tinyint(1) DEFAULT 1,
  `notif_retard` tinyint(1) DEFAULT 1,
  `notif_recu` tinyint(1) DEFAULT 0,
  `email_compta` varchar(255) DEFAULT NULL,
  `email_dg` varchar(255) DEFAULT NULL,
  `delai_rappel` int(11) DEFAULT 7,
  `seuil_dg` decimal(15,0) DEFAULT 1000000,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `parametrages_agence`
--

INSERT INTO `parametrages_agence` (`parametrages_agence_id`, `agence_id`, `devise`, `langue`, `format_date`, `timezone`, `sauvegarde_auto`, `double_validation`, `journal_activites`, `multi_session`, `periode_facturation`, `jour_emission`, `delai_paiement`, `penalite_retard`, `prefixe_facture`, `sequence_facture`, `commission`, `base_commission`, `tva`, `aib`, `ras`, `acompte_min`, `mode_reglement_id`, `logo`, `logo_largeur`, `logo_position`, `logo_tutelle`, `logo_partenaire`, `cachet`, `signature_dg`, `dg_nom`, `dg_titre`, `signature_sg`, `sg_nom`, `sg_titre`, `signature_cpt`, `cpt_nom`, `cpt_titre`, `sig_dg_facture`, `sig_double`, `cachet_auto`, `notif_rappel`, `notif_retard`, `notif_recu`, `email_compta`, `email_dg`, `delai_rappel`, `seuil_dg`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('93577165-0a54-483a-acf8-7d838da4f883', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'XOF', 'fr', 'd/m/Y', 'Africa/Abidjan', 0, 0, 0, 0, 'mensuelle', '1', 10, 1.60, 'FAC-', 1, 10.00, 'ht', 0.00, 0.00, 0.00, 30.00, 1, NULL, 200, 'gauche', NULL, NULL, NULL, NULL, NULL, 'Directeur Général', NULL, NULL, 'Secrétaire Général(e)', NULL, NULL, 'Responsable Comptable', 1, 1, 0, 1, 1, 0, NULL, NULL, 7, 1000000, '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '2026-06-01 01:50:59', '2026-06-09 12:57:31', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `periodicite_paiements`
--

CREATE TABLE `periodicite_paiements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `periodicite_paiements`
--

INSERT INTO `periodicite_paiements` (`id`, `name`, `description`, `is_actif`, `created_at`, `updated_at`) VALUES
(1, 'Journalier', 'Paiement effectué chaque jour', 1, '2026-07-09 11:41:44', '2026-07-09 11:41:44'),
(2, 'Hebdomadaire', 'Paiement effectué chaque semaine', 1, '2026-07-09 11:41:44', '2026-07-09 11:41:44'),
(3, 'Mensuel', 'Paiement effectué chaque mois', 1, '2026-07-09 11:41:44', '2026-07-09 11:41:44'),
(4, 'Bimestriel', 'Paiement effectué tous les deux mois', 1, '2026-07-09 11:41:44', '2026-07-09 11:41:44'),
(5, 'Trimestriel', 'Paiement effectué tous les trois mois', 1, '2026-07-09 11:41:44', '2026-07-09 11:41:44'),
(6, 'Annuel', 'Paiement effectué chaque année', 1, '2026-07-09 11:41:44', '2026-07-09 11:41:44');

-- --------------------------------------------------------

--
-- Table structure for table `porte`
--

CREATE TABLE `porte` (
  `porte_id` varchar(150) NOT NULL,
  `batiment_id` varchar(150) NOT NULL,
  `type_porte_id` int(11) NOT NULL,
  `agence_id` varchar(150) DEFAULT NULL,
  `numero_porte` varchar(20) NOT NULL,
  `superficie_m2` decimal(8,2) DEFAULT NULL,
  `etage` int(11) DEFAULT 0,
  `is_allocation` tinyint(1) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `is_occupe` tinyint(1) DEFAULT 0,
  `is_actif` tinyint(1) DEFAULT 1,
  `caution` int(11) NOT NULL DEFAULT 2,
  `avance` int(11) NOT NULL DEFAULT 2,
  `agence` int(11) NOT NULL DEFAULT 1,
  `mt_caution_cie` int(11) NOT NULL DEFAULT 0,
  `mt_caution_sodeci` int(11) NOT NULL DEFAULT 0,
  `mt_autre_frais` int(11) NOT NULL DEFAULT 0,
  `mt_loyer` int(11) NOT NULL DEFAULT 0,
  `equipements` text DEFAULT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `porte`
--

INSERT INTO `porte` (`porte_id`, `batiment_id`, `type_porte_id`, `agence_id`, `numero_porte`, `superficie_m2`, `etage`, `is_allocation`, `description`, `is_occupe`, `is_actif`, `caution`, `avance`, `agence`, `mt_caution_cie`, `mt_caution_sodeci`, `mt_autre_frais`, `mt_loyer`, `equipements`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
('201fafef-f4ad-4449-9acb-f7a96cffd591', '79a83de8-04bb-4a25-a83e-48014879b541', 6, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'AAA', 250.00, 0, 0, NULL, 0, 1, 2, 2, 1, 0, 0, 0, 0, '[\"8\",\"6\",\"3\"]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '2026-07-07 14:04:11', '2026-07-07 14:04:11'),
('20a42d39-bad9-4cc9-9076-12fb85e64a98', '8e489f28-784f-46d8-bb57-da2fc1b466a5', 5, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'A-1', 30.00, 0, 1, NULL, 0, 1, 2, 2, 1, 0, 0, 0, 0, '[\"6\"]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '2026-07-07 08:35:02', '2026-07-09 17:50:22'),
('42d35228-6d56-4915-8aba-0db4563194ce', '7d5ffc45-c4e9-47a6-930b-3a48a8918778', 2, '2df2a9f8-5d56-4842-a683-8676eb1d017f', '78', NULL, 0, 1, NULL, 0, 1, 2, 2, 1, 0, 0, 0, 0, '[\"7\"]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '2026-07-07 10:44:57', '2026-07-07 10:44:57'),
('6400a16c-8da4-4b05-b0cb-198856d2a39e', '7e0d3674-2d1c-4c17-9011-d358d41b8978', 5, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'CEMA', 200.00, 0, 1, NULL, 0, 1, 2, 2, 1, 0, 0, 0, 0, '[\"6\"]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '2026-07-07 10:16:51', '2026-07-07 10:16:51'),
('6a976b1f-853a-4c55-908c-4679107f73a7', '7d5ffc45-c4e9-47a6-930b-3a48a8918778', 2, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'AR1', 20.00, 0, 0, NULL, 0, 1, 2, 2, 1, 0, 0, 0, 0, '[\"1\"]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '2026-07-07 10:37:09', '2026-07-07 10:37:09'),
('6e928829-dd85-4947-b365-252c1ff84f7f', '915eb859-b82f-48b9-aca8-efb7cabfc411', 5, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'C', 20.00, 2, 1, NULL, 0, 1, 2, 2, 1, 0, 0, 0, 0, '[\"1\"]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '2026-07-07 10:16:50', '2026-07-07 10:16:51'),
('7316f432-c33c-45e3-9a53-f9f509f89f6c', '42ddb365-a8c2-4551-b1f4-9819322c35ef', 4, '2df2a9f8-5d56-4842-a683-8676eb1d017f', '7aa', 20.00, 0, 0, 'RAS', 0, 1, 2, 2, 1, 0, 0, 0, 0, '[\"6\",\"8\",\"2\",\"5\",\"4\",\"1\"]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '2026-07-07 13:19:20', '2026-07-07 13:39:42'),
('7a27644f-5d1e-41d1-b5bc-4a0285d81b7c', '915eb859-b82f-48b9-aca8-efb7cabfc411', 2, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'B', 50.00, 0, 1, NULL, 0, 1, 2, 2, 1, 0, 0, 0, 0, '[\"8\"]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '2026-07-07 10:16:50', '2026-07-07 10:16:50'),
('8f6d2b4d-597d-46aa-96e2-bccd82a59fe7', '915eb859-b82f-48b9-aca8-efb7cabfc411', 1, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'A', 80.00, 1, 1, 'RASS', 0, 1, 2, 2, 1, 0, 0, 0, 0, '[\"6\",\"2\"]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '2026-07-07 10:16:50', '2026-07-07 10:16:50'),
('adf56a6b-c6ab-4e25-abc4-1cdd508dea6c', 'dd839ae3-4ffa-4863-a20d-bf0147125f39', 2, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'A-1', 60.00, 0, 1, NULL, 1, 1, 2, 2, 1, 4000, 0, 0, 50000, '[\"1\",\"2\",\"9\",\"3\",\"4\",\"5\",\"7\"]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '2026-05-22 20:24:53', '2026-07-08 17:13:22'),
('bd407e98-3d95-4d76-a5de-286d1f7bceff', 'dd839ae3-4ffa-4863-a20d-bf0147125f39', 2, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'A-2', 60.00, 0, 1, NULL, 1, 1, 2, 2, 1, 4000, 0, 0, 50000, '[\"1\",\"9\",\"3\",\"4\",\"5\",\"7\"]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '2026-05-23 16:57:08', '2026-05-31 17:07:59'),
('c04ec857-ad77-4a2b-a34b-2a860495a7a9', '8ae0d4b2-84e8-4d02-8097-359bf6b0648c', 4, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'A-89', NULL, 0, 1, NULL, 0, 1, 2, 2, 1, 0, 0, 0, 0, NULL, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-07-09 17:52:43', '2026-07-09 17:52:43'),
('c2a89ff2-c3cb-4069-b0d4-b3c406282d5d', '66898dee-57dd-48c4-9358-97770602368a', 5, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'A-3', NULL, 0, 1, NULL, 1, 1, 2, 2, 1, 30000, 30000, 0, 100000, '[\"1\",\"9\",\"4\",\"8\"]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '2026-05-26 14:06:33', '2026-07-09 17:37:02');

-- --------------------------------------------------------

--
-- Table structure for table `propietaire_lots`
--

CREATE TABLE `propietaire_lots` (
  `propreietaire_lot_id` varchar(150) NOT NULL,
  `name` varchar(250) NOT NULL,
  `superficie` int(11) DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  `ville_id` int(11) DEFAULT NULL,
  `adresse` varchar(150) DEFAULT NULL,
  `num_lot` varchar(50) DEFAULT NULL,
  `num_ilot` varchar(50) DEFAULT NULL,
  `proprietaire_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `deleted_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `propietaire_lots`
--

INSERT INTO `propietaire_lots` (`propreietaire_lot_id`, `name`, `superficie`, `region_id`, `ville_id`, `adresse`, `num_lot`, `num_ilot`, `proprietaire_id`, `agence_id`, `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('18c965f2-6c95-4b4d-85f6-5a0563952498', 'LOATA', NULL, NULL, NULL, NULL, NULL, NULL, 'a73b8ad2-e4c6-4c9e-82e9-d3e823c2682f', '2df2a9f8-5d56-4842-a683-8676eb1d017f', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, '2026-07-09 17:51:54', '2026-07-09 17:51:54', NULL),
('39b36a6d-af4a-4b83-8a03-6a0be94840d8', 'Lot 1', 700, 1, 4, 'Cocody Rivera 4', '180', '34', '0143080c-1088-45b2-a292-edf809feb8e3', '2df2a9f8-5d56-4842-a683-8676eb1d017f', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-05-18 15:12:11', '2026-05-18 15:19:09', NULL),
('96c871a7-b88a-4c43-90b2-90647b8381ee', 'Adeba', 400, 1, 4, 'Cocody', '2', 'A785', '0143080c-1088-45b2-a292-edf809feb8e3', '2df2a9f8-5d56-4842-a683-8676eb1d017f', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, '2026-07-06 13:46:23', '2026-07-06 13:46:23', NULL),
('aff40968-9f34-4139-a4d5-4d9afb71ae88', 'hhh', NULL, NULL, NULL, NULL, NULL, NULL, '0143080c-1088-45b2-a292-edf809feb8e3', '2df2a9f8-5d56-4842-a683-8676eb1d017f', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, '2026-07-07 11:29:24', '2026-07-07 11:29:24', NULL),
('ebd3f311-03c3-428d-bc39-275719bf786c', 'aa', 400, 12, 48, 'aaa', '1', 'a2', '0143080c-1088-45b2-a292-edf809feb8e3', '2df2a9f8-5d56-4842-a683-8676eb1d017f', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, '2026-07-06 13:52:22', '2026-07-06 13:52:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `proprietaires`
--

CREATE TABLE `proprietaires` (
  `proprietaire_id` varchar(150) NOT NULL,
  `code` varchar(150) NOT NULL,
  `genre_id` int(11) DEFAULT NULL,
  `name` varchar(250) NOT NULL,
  `tel1` varchar(50) NOT NULL,
  `tel2` varchar(50) DEFAULT NULL,
  `type_pieces_id` int(11) NOT NULL,
  `type_proprietaire` enum('particulier','entreprise') NOT NULL DEFAULT 'particulier',
  `numpiece` varchar(250) NOT NULL,
  `date_expiration_piece` date DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  `profession` varchar(250) DEFAULT NULL,
  `nationalite` varchar(150) DEFAULT 'IVOIRIENNE',
  `date_naiss` date DEFAULT NULL,
  `lieu_naiss` varchar(250) DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  `ville_id` int(11) DEFAULT NULL,
  `adresse` varchar(250) DEFAULT NULL,
  `photo` varchar(250) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `proprietaires`
--

INSERT INTO `proprietaires` (`proprietaire_id`, `code`, `genre_id`, `name`, `tel1`, `tel2`, `type_pieces_id`, `type_proprietaire`, `numpiece`, `date_expiration_piece`, `email`, `profession`, `nationalite`, `date_naiss`, `lieu_naiss`, `region_id`, `ville_id`, `adresse`, `photo`, `created_at`, `updated_at`, `password`) VALUES
('0143080c-1088-45b2-a292-edf809feb8e3', 'JH-09486', 1, 'John Doe', '0707902960', '0142259037', 1, 'particulier', 'ci0987652332345', '2026-06-07', 'john.doe@pro.com', 'tailleur', 'IVOIRIENNE', '2026-05-01', 'Abidjan', 1, 1, 'abidjan adjame', NULL, '2026-05-17 00:55:28', '2026-05-17 02:31:32', NULL),
('0577b283-a5ed-488d-a0a9-24aa7c64edb1', 'KH-06717', 1, 'jjjj', '+2250747033011', NULL, 1, 'particulier', '00777', '2043-07-07', 'ooo@hhh.com', 'jnuhbhn', 'fgvhb', '1976-07-07', 'rtfbgy', 4, 22, 'ytuhn', 'https://dev.rodrigue-yapo.com/admin/assets/images/proprietaire/6a4d57adf03935.30309817.png', '2026-07-07 19:46:54', '2026-07-07 19:46:54', '$2y$12$Wl9Q3ikY80WkiAizBvNlC.IyhpxlQG24hWIhCJrlNl3HC/GY8qchi'),
('5bae10a1-390b-4912-b3ee-3ed03d047e45', 'VO-62185', 1, 'fdvdvdvd', '+2257896585698', NULL, 2, 'particulier', '841adad82', '2026-07-17', NULL, NULL, 'IVOIRIENNE', NULL, NULL, NULL, NULL, NULL, 'http://localhost:8000/admin/assets/images/proprietaire/6a4e2ce6ce88b7.18041252.png', '2026-07-08 10:56:39', '2026-07-08 10:56:39', '$2y$12$otbcrcAulMuxy7SwK503.uPmRZGoUreGAfyHwktUvytBI8n4IgsyK'),
('a73b8ad2-e4c6-4c9e-82e9-d3e823c2682f', 'DC-66164', 1, 'afada', '+2257242465247', NULL, 1, 'particulier', 'adad4adad5a5da5', '2026-07-31', NULL, NULL, 'IVOIRIENNE', NULL, NULL, NULL, NULL, NULL, 'http://localhost:8000/admin/assets/images/proprietaire/6a4e5f07741432.84542582.png', '2026-07-08 13:58:39', '2026-07-08 14:30:31', '$2y$12$qO3LDf84gClI9/JTRjBEveBQ2AR.IrjjqO1CoY6875JfWEJUnh5LO'),
('b87c3c28-6686-4458-9c12-c9a50c473387', 'IU-88671', 1, 'ggdfvdvdf', '+2257896527896', NULL, 1, 'particulier', '74egrege', '2026-07-31', NULL, NULL, 'IVOIRIENNE', NULL, NULL, 2, 17, NULL, 'https://dev.rodrigue-yapo.com/admin/assets/images/proprietaire/6a4e2b128b8a21.53742631.png', '2026-07-08 10:48:51', '2026-07-08 10:48:51', '$2y$12$gqdsXXLvGsQ/OZipWivJ/uZPvr8IV7cepelMCodtf1LwihhnmDFOG');

-- --------------------------------------------------------

--
-- Table structure for table `proprietaire_agences`
--

CREATE TABLE `proprietaire_agences` (
  `proprietaire_agence_id` varchar(150) NOT NULL,
  `proprietaire_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `is_active` tinyint(4) NOT NULL DEFAULT 1,
  `date_activation` timestamp NULL DEFAULT NULL,
  `date_desactivation` timestamp NULL DEFAULT NULL,
  `agent_activation_id` varchar(150) DEFAULT NULL,
  `agent_desactivation_id` varchar(150) DEFAULT NULL,
  `name_representant` varchar(250) DEFAULT NULL,
  `genre_representant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `adresse_representant` varchar(250) DEFAULT NULL,
  `tel1_representant` varchar(50) DEFAULT NULL,
  `tel2_representant` varchar(50) DEFAULT NULL,
  `email_representant` varchar(250) DEFAULT NULL,
  `type_pieces_representant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `numpiece_representant` varchar(255) DEFAULT NULL,
  `photo_representant` varchar(255) DEFAULT NULL,
  `created_by` varchar(150) NOT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `deleted_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `proprietaire_agences`
--

INSERT INTO `proprietaire_agences` (`proprietaire_agence_id`, `proprietaire_id`, `agence_id`, `is_active`, `date_activation`, `date_desactivation`, `agent_activation_id`, `agent_desactivation_id`, `name_representant`, `genre_representant_id`, `adresse_representant`, `tel1_representant`, `tel2_representant`, `email_representant`, `type_pieces_representant_id`, `numpiece_representant`, `photo_representant`, `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('0c75c23c-cc96-4679-b289-1a13dfe5de15', '5bae10a1-390b-4912-b3ee-3ed03d047e45', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 0, '2026-07-08 10:56:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '4fb6adb2-c847-44ac-83e7-4c64c033caef', '2026-07-08 10:56:39', '2026-07-08 13:54:33', '2026-07-08 13:54:33'),
('902f26a3-c9c2-4227-a932-0d177263293c', 'b87c3c28-6686-4458-9c12-c9a50c473387', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 1, '2026-07-08 10:48:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, '2026-07-08 10:48:51', '2026-07-08 10:48:51', NULL),
('b1b5da9c-1829-4abc-b7ef-cfebe5fd9b6d', '0143080c-1088-45b2-a292-edf809feb8e3', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 1, '2026-05-17 02:00:01', NULL, '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', 'koffi kouadio jean', NULL, 'abobo', '0100987658', NULL, 'representant.koffi@gmail.com', NULL, NULL, NULL, '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-05-17 00:55:28', '2026-05-17 02:00:01', NULL),
('dba28c8d-c365-407e-b373-05d7dbcde368', 'a73b8ad2-e4c6-4c9e-82e9-d3e823c2682f', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 1, '2026-07-08 15:34:47', NULL, '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', 'AEEE', 1, NULL, '+2254565458525', NULL, NULL, 1, 'CF7522929', 'http://localhost:8000/admin/assets/images/representant/6a4e58c4b8a764.99382751.jpg', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-07-08 13:58:39', '2026-07-08 15:34:47', NULL),
('e410e7d8-c933-48bf-9147-b7f519f8836f', '0577b283-a5ed-488d-a0a9-24aa7c64edb1', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 0, '2026-07-07 19:46:54', NULL, NULL, NULL, 'hhhbnj,k', NULL, 'tfvgbhgt', '+2257896541236', NULL, 'ggyy@hhh.hhhh', NULL, NULL, NULL, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '4fb6adb2-c847-44ac-83e7-4c64c033caef', '2026-07-07 19:46:54', '2026-07-08 15:34:53', '2026-07-08 15:34:53');

-- --------------------------------------------------------

--
-- Table structure for table `propriete`
--

CREATE TABLE `propriete` (
  `propriete_id` varchar(150) NOT NULL,
  `proprietaire_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) DEFAULT NULL,
  `lot_id` varchar(150) DEFAULT NULL,
  `type_propriete_id` int(11) DEFAULT NULL,
  `reference` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `adresse_complete` text DEFAULT NULL,
  `videos_url` text DEFAULT NULL,
  `is_allocation` tinyint(1) DEFAULT 0,
  `is_actif` tinyint(1) DEFAULT 1,
  `prossimites` text DEFAULT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `deleted_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `propriete`
--

INSERT INTO `propriete` (`propriete_id`, `proprietaire_id`, `agence_id`, `lot_id`, `type_propriete_id`, `reference`, `description`, `adresse_complete`, `videos_url`, `is_allocation`, `is_actif`, `prossimites`, `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('1a85c585-565f-4946-aecb-8a95677ec9d0', '0143080c-1088-45b2-a292-edf809feb8e3', '2df2a9f8-5d56-4842-a683-8676eb1d017f', '96c871a7-b88a-4c43-90b2-90647b8381ee', NULL, 'PROP-2026-0006', NULL, 'Cocody', NULL, 0, 1, '[6]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, '2026-07-07 14:04:11', '2026-07-07 14:04:11', NULL),
('2254a569-9bee-45dd-bfec-4d0d3212fa5b', '0143080c-1088-45b2-a292-edf809feb8e3', '2df2a9f8-5d56-4842-a683-8676eb1d017f', '39b36a6d-af4a-4b83-8a03-6a0be94840d8', 2, 'PROP-2026-0001', 'fghjkl', 'Cocody Rivera 4', NULL, 1, 1, '{\"6\":\"234\",\"5\":\"444\",\"1\":\"444\"}', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-05-22 20:24:53', '2026-05-23 16:38:16', NULL),
('3c4ab403-e08f-4193-a0f1-0341f02a3ebd', '0143080c-1088-45b2-a292-edf809feb8e3', '2df2a9f8-5d56-4842-a683-8676eb1d017f', '96c871a7-b88a-4c43-90b2-90647b8381ee', NULL, 'PROP-2026-0005', NULL, 'Cocody', NULL, 0, 1, '[6,5]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, '2026-07-07 13:19:20', '2026-07-07 14:01:52', NULL),
('52d5341f-031c-47e3-95b4-1ebb00560bf7', '0143080c-1088-45b2-a292-edf809feb8e3', '2df2a9f8-5d56-4842-a683-8676eb1d017f', '96c871a7-b88a-4c43-90b2-90647b8381ee', 3, 'PROP-2026-0002', NULL, 'Cocody', NULL, 1, 1, '[6]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, '2026-07-07 08:35:02', '2026-07-09 17:50:22', NULL),
('535e3431-6166-4f51-aa37-36931cc03d3d', 'a73b8ad2-e4c6-4c9e-82e9-d3e823c2682f', '2df2a9f8-5d56-4842-a683-8676eb1d017f', '18c965f2-6c95-4b4d-85f6-5a0563952498', NULL, 'PROP-2026-0007', NULL, NULL, NULL, 1, 1, '[6]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, '2026-07-09 17:52:43', '2026-07-09 17:52:43', NULL),
('7eb908bc-f886-498a-a6a5-5875baf25e32', '0143080c-1088-45b2-a292-edf809feb8e3', '2df2a9f8-5d56-4842-a683-8676eb1d017f', '96c871a7-b88a-4c43-90b2-90647b8381ee', NULL, 'PROP-2026-0004', 'Test', 'Cocody', NULL, 1, 1, '[6]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-07-07 10:37:09', '2026-07-07 12:50:29', NULL),
('b8b380ae-b180-49b5-88da-032e73828401', '0143080c-1088-45b2-a292-edf809feb8e3', '2df2a9f8-5d56-4842-a683-8676eb1d017f', '39b36a6d-af4a-4b83-8a03-6a0be94840d8', NULL, 'PROP-2026-0003', NULL, 'Cocody Rivera 4', NULL, 0, 1, '[]', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, '2026-07-07 10:16:50', '2026-07-07 10:16:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `propriete_proximites`
--

CREATE TABLE `propriete_proximites` (
  `propriete_proximite_id` char(36) NOT NULL,
  `propriete_id` char(36) NOT NULL,
  `proximite_id` bigint(20) UNSIGNED NOT NULL,
  `distance` decimal(10,2) DEFAULT NULL,
  `unite` varchar(5) DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `updated_by` varchar(255) DEFAULT NULL,
  `deleted_by` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `propriete_proximites`
--

INSERT INTO `propriete_proximites` (`propriete_proximite_id`, `propriete_id`, `proximite_id`, `distance`, `unite`, `created_by`, `updated_by`, `deleted_by`, `deleted_at`, `created_at`, `updated_at`) VALUES
('11be0e4b-0c59-4e30-8a35-70bd31c2d2c7', '52d5341f-031c-47e3-95b4-1ebb00560bf7', 6, 20.00, 'm', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, NULL, '2026-07-09 17:50:22', '2026-07-09 17:50:22'),
('257666f9-3303-453c-99de-0fb8d998e380', '3c4ab403-e08f-4193-a0f1-0341f02a3ebd', 6, 10.00, 'm', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, NULL, '2026-07-07 14:01:52', '2026-07-07 14:01:52'),
('6b062e6b-3410-4f9c-aab2-3670a9b3d061', '7eb908bc-f886-498a-a6a5-5875baf25e32', 6, 200.00, 'm', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, NULL, '2026-07-07 12:50:29', '2026-07-07 12:50:29'),
('6e29e176-2476-4875-9df7-b743288c06cf', '535e3431-6166-4f51-aa37-36931cc03d3d', 6, 20.00, 'm', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, NULL, '2026-07-09 17:52:43', '2026-07-09 17:52:43'),
('9d95ff03-cfb4-43e4-abb6-410a6590e6fa', '1a85c585-565f-4946-aecb-8a95677ec9d0', 6, 10.00, 'm', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, NULL, '2026-07-07 14:07:35', '2026-07-07 14:07:35'),
('acc42bdb-0516-4ec2-9800-f29712aa74c2', '3c4ab403-e08f-4193-a0f1-0341f02a3ebd', 5, 15.00, 'm', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, NULL, NULL, '2026-07-07 14:01:52', '2026-07-07 14:01:52');

-- --------------------------------------------------------

--
-- Table structure for table `prossimite_proprietes`
--

CREATE TABLE `prossimite_proprietes` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `prossimite_proprietes`
--

INSERT INTO `prossimite_proprietes` (`id`, `name`, `agence_id`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Eglise', '2df2a9f8-5d56-4842-a683-8676eb1d017f', NULL, '2024-12-28 23:32:11', '2024-12-28 23:32:11'),
(2, 'Mosquée', '2df2a9f8-5d56-4842-a683-8676eb1d017f', NULL, '2024-12-28 23:32:19', '2024-12-28 23:32:19'),
(3, 'Loin du goudron', '2df2a9f8-5d56-4842-a683-8676eb1d017f', NULL, '2024-12-28 23:32:35', '2024-12-30 22:20:15'),
(4, 'hopital', '2df2a9f8-5d56-4842-a683-8676eb1d017f', NULL, '2024-12-28 23:32:52', '2024-12-28 23:32:52'),
(5, 'Centre de santé', '2df2a9f8-5d56-4842-a683-8676eb1d017f', NULL, '2024-12-28 23:33:09', '2024-12-28 23:33:09'),
(6, 'Banque', '2df2a9f8-5d56-4842-a683-8676eb1d017f', NULL, '2024-12-28 23:33:22', '2024-12-28 23:33:22'),
(7, 'Proche du goudron', '2df2a9f8-5d56-4842-a683-8676eb1d017f', NULL, '2024-12-30 22:19:51', '2024-12-30 22:19:51'),
(8, 'test', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'test', '2026-05-21 14:40:11', '2026-05-21 14:40:11');

-- --------------------------------------------------------

--
-- Table structure for table `regions`
--

CREATE TABLE `regions` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `regions`
--

INSERT INTO `regions` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'District autonome d\'Abidjan', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(2, 'Région de l\'Agnéby-Tiassa', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(3, 'Région du Bafing', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(4, 'Région du Bagoué', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(35, 'Région de Bélier', '2026-03-11 16:11:09', '2026-03-11 16:11:09'),
(6, 'Région du Béré', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(7, 'Région de Bounkani', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(8, 'Région du Cavally', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(9, 'Région du Folon', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(10, 'Région du Gbêkê', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(11, 'Région du Gbôklé', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(12, 'Région du Gôh', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(13, 'Région du Gontougou', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(14, 'Région du Grands Ponts', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(15, 'Région du Guémon', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(16, 'Région du Haut-Sassandra', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(17, 'Région de l\'Iffou', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(18, 'Région de l\'Indénié-Djuablin', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(19, 'Région du Kabadougou', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(20, 'Région de La Mé', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(21, 'Région du Lôh-Djiboua', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(22, 'Région de la Marahoué', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(23, 'Région du Hambol', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(24, 'Région du Moronou', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(25, 'Région de la Nawa', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(26, 'Région du N\'Zi', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(27, 'Région du Poro', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(28, 'Région de San-Pédro', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(29, 'Région du Sud-Comoé', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(30, 'Région du Tchologo', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(31, 'Région du Tonkpi', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(32, 'Région du Worodougou', '2024-12-24 05:59:05', '2024-12-24 05:59:05'),
(33, 'District autonome de Yamoussoukro', '2024-12-24 05:59:05', '2024-12-24 05:59:05');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `agence_id` varchar(150) NOT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `name`, `description`, `agence_id`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin', '', NULL, NULL, '2026-05-11 15:00:03', '2026-05-11 15:01:43');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('TTN4kdVK3Waws4vPZest7WBGlcgKBugXCwNNTQxV', NULL, '127.0.0.1', 'Symfony', 'eyJfdG9rZW4iOiJ6Q2Z2Y0pEUzN2YnkzNHBvWWZpWTVTZ1VrTVM0TUt1MkRkVXp3OFVlIiwiX2ZsYXNoIjp7Im5ldyI6W10sIm9sZCI6W119fQ==', 1783617960),
('tVplamRjqG3z1XX0WtYd9u9wVYA8WYOhv7r8sEJb', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:152.0) Gecko/20100101 Firefox/152.0', 'eyJfdG9rZW4iOiJrcVBON3pGVklGY3VKZ20zb1psS2ZiREo0Q3gxRUs1ZVBpTmVLU0kxIiwiX2ZsYXNoIjp7Im5ldyI6W10sIm9sZCI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvbG9jYWxob3N0OjgwMDBcL2FnZW5jZVwvcHJvcHJpZXRlc1wvc2hvd1wvNTM1ZTM0MzEtNjE2Ni00ZjUxLWFhMzctMzY5MzFjYzAzZDNkIiwicm91dGUiOiJhZ2VuY2UucHJvcHJpZXRlcy5zaG93In0sImxvZ2luX3VzZXJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6IjRmYjZhZGIyLWM4NDctNDRhYy04M2U3LTRjNjRjMDMzY2FlZiJ9', 1783619919);

-- --------------------------------------------------------

--
-- Table structure for table `tarif_porte`
--

CREATE TABLE `tarif_porte` (
  `tarif_id` char(36) NOT NULL,
  `porte_id` char(36) NOT NULL,
  `mt_loyer` decimal(12,2) NOT NULL,
  `mt_vente` decimal(12,2) DEFAULT NULL,
  `mt_caution` decimal(12,2) DEFAULT 0.00,
  `mt_avance` decimal(12,2) DEFAULT 0.00,
  `mt_frais_agence` decimal(12,2) DEFAULT 0.00,
  `mt_caution_cie` decimal(12,2) DEFAULT 0.00,
  `mt_caution_sodeci` decimal(12,2) DEFAULT 0.00,
  `date_effet` date NOT NULL,
  `is_actif` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `mt_frais_dossier` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tarif_porte`
--

INSERT INTO `tarif_porte` (`tarif_id`, `porte_id`, `mt_loyer`, `mt_vente`, `mt_caution`, `mt_avance`, `mt_frais_agence`, `mt_caution_cie`, `mt_caution_sodeci`, `date_effet`, `is_actif`, `created_at`, `mt_frais_dossier`) VALUES
('1de4df37-a3a3-4015-95e8-2efa426069ec', '20a42d39-bad9-4cc9-9076-12fb85e64a98', 75000.00, NULL, 2.00, 2.00, 1.00, 0.00, 0.00, '2026-07-07', 0, '2026-07-07 08:35:02', 0.00),
('295f3137-4479-47f9-b526-194dec9fd0f0', '7316f432-c33c-45e3-9a53-f9f509f89f6c', 0.00, 36000000.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2026-07-07', 0, '2026-07-07 13:21:17', 0.00),
('2b535303-85f0-452d-9d38-aca29edc4d87', '7a27644f-5d1e-41d1-b5bc-4a0285d81b7c', 80000.00, NULL, 2.00, 2.00, 1.00, 0.00, 0.00, '2026-07-07', 1, '2026-07-07 10:16:50', 0.00),
('37fadb46-2cd0-4ba6-b279-fa770d210bf4', '20a42d39-bad9-4cc9-9076-12fb85e64a98', 75000.00, NULL, 2.00, 2.00, 1.00, 0.00, 0.00, '2026-07-09', 1, '2026-07-09 17:50:22', 0.00),
('4a5f8e06-7898-4404-aa06-9a9b15f94dd6', '6a976b1f-853a-4c55-908c-4679107f73a7', 25000.00, NULL, 2.00, 2.00, 1.00, 0.00, 0.00, '2026-07-07', 0, '2026-07-07 10:37:09', 0.00),
('57e0edeb-50ff-4979-9e9e-a8f680441d4e', '7316f432-c33c-45e3-9a53-f9f509f89f6c', 0.00, 36000000.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2026-07-07', 0, '2026-07-07 13:39:42', 0.00),
('583442d1-2256-4379-b493-b07f71a6af03', '7316f432-c33c-45e3-9a53-f9f509f89f6c', 0.00, 36000000.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2026-07-07', 0, '2026-07-07 13:19:55', 0.00),
('65b01728-ade0-4681-af50-340bd5c6031c', '8f6d2b4d-597d-46aa-96e2-bccd82a59fe7', 50000.00, NULL, 2.00, 2.00, 1.00, 25000.00, 0.00, '2026-07-07', 1, '2026-07-07 10:16:50', 0.00),
('6f38ee39-112f-4058-9b9b-21febd535663', '6a976b1f-853a-4c55-908c-4679107f73a7', 25000.00, NULL, 2.00, 2.00, 1.00, 0.00, 0.00, '2026-07-07', 1, '2026-07-07 12:50:29', 0.00),
('82b801ca-ab0f-4738-8f63-de8ef893606a', '42d35228-6d56-4915-8aba-0db4563194ce', 25000.00, NULL, 2.00, 2.00, 1.00, 0.00, 0.00, '2026-07-07', 1, '2026-07-07 12:50:29', 0.00),
('8a30ed2b-b9fa-4337-acb4-cab1f9c48d55', '6a976b1f-853a-4c55-908c-4679107f73a7', 25000.00, NULL, 2.00, 2.00, 1.00, 0.00, 0.00, '2026-07-07', 0, '2026-07-07 10:44:57', 0.00),
('a10aeb3f-04da-4e58-ab51-e17d0cc49a7a', '7316f432-c33c-45e3-9a53-f9f509f89f6c', 0.00, 36000000.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2026-07-07', 1, '2026-07-07 14:01:52', 0.00),
('bd63ed34-5868-4d1a-a221-ef229352e696', '42d35228-6d56-4915-8aba-0db4563194ce', 25000.00, NULL, 2.00, 2.00, 1.00, 0.00, 0.00, '2026-07-07', 0, '2026-07-07 10:44:57', 0.00),
('bf04138d-8dca-4cdd-83f4-cbb195268270', '7316f432-c33c-45e3-9a53-f9f509f89f6c', 0.00, 36000000.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2026-07-07', 0, '2026-07-07 13:19:20', 0.00),
('c290d0ec-b5aa-4aeb-8ecb-bd528277311c', '201fafef-f4ad-4449-9acb-f7a96cffd591', 0.00, 36900000.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2026-07-07', 0, '2026-07-07 14:04:11', 5000.00),
('df10b9f8-8f36-4fa1-b9a0-4cff7007c091', '6400a16c-8da4-4b05-b0cb-198856d2a39e', 120000.00, NULL, 2.00, 2.00, 1.00, 0.00, 0.00, '2026-07-07', 1, '2026-07-07 10:16:51', 0.00),
('df887030-e333-4d2e-87d0-b0f25acddb5b', '7316f432-c33c-45e3-9a53-f9f509f89f6c', 0.00, 36000000.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2026-07-07', 0, '2026-07-07 13:32:41', 0.00),
('e5400a92-bba4-4ea6-a351-a1da1029b5ec', '201fafef-f4ad-4449-9acb-f7a96cffd591', 0.00, 36900000.00, 0.00, 0.00, 0.00, 0.00, 0.00, '2026-07-07', 1, '2026-07-07 14:07:35', 5000.00),
('edb423f0-669d-4163-8071-3d16ef8f4f1f', '6e928829-dd85-4947-b365-252c1ff84f7f', 60000.00, NULL, 2.00, 2.00, 1.00, 0.00, 0.00, '2026-07-07', 1, '2026-07-07 10:16:51', 0.00),
('eea57f4c-cd99-4c6e-977a-e8e6093d940c', 'c04ec857-ad77-4a2b-a34b-2a860495a7a9', 75000.00, NULL, 2.00, 2.00, 1.00, 30000.00, 0.00, '2026-07-09', 1, '2026-07-09 17:52:43', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `test`
--

CREATE TABLE `test` (
  `a` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` bigint(20) UNSIGNED NOT NULL,
  `reference` varchar(255) NOT NULL,
  `agence_id` varchar(255) NOT NULL,
  `abonnement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `abonnement_historique_id` bigint(20) UNSIGNED DEFAULT NULL,
  `montant_base_ht` decimal(12,2) NOT NULL DEFAULT 0.00,
  `montant_options_ht` decimal(12,2) NOT NULL DEFAULT 0.00,
  `montant_total_ht` decimal(12,2) NOT NULL DEFAULT 0.00,
  `taux_tva` decimal(5,2) NOT NULL DEFAULT 0.00,
  `montant_tva` decimal(12,2) NOT NULL DEFAULT 0.00,
  `montant_ttc` decimal(12,2) NOT NULL DEFAULT 0.00,
  `duree_mois` int(11) NOT NULL DEFAULT 1,
  `periode_debut` date DEFAULT NULL,
  `periode_fin` date DEFAULT NULL,
  `options_souscrites` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`options_souscrites`)),
  `mode_paiement` enum('especes','virement','cheque','mobile_money','carte','autre') DEFAULT NULL,
  `statut` enum('en_attente','validee','echouee','remboursee','annulee') NOT NULL DEFAULT 'en_attente',
  `reference_paiement` varchar(255) DEFAULT NULL,
  `date_paiement` datetime DEFAULT NULL,
  `date_validation` datetime DEFAULT NULL,
  `type_operation` enum('souscription','renouvellement','upgrade','remboursement') NOT NULL DEFAULT 'souscription',
  `created_by` varchar(255) DEFAULT NULL,
  `updated_by` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `reference`, `agence_id`, `abonnement_id`, `abonnement_historique_id`, `montant_base_ht`, `montant_options_ht`, `montant_total_ht`, `taux_tva`, `montant_tva`, `montant_ttc`, `duree_mois`, `periode_debut`, `periode_fin`, `options_souscrites`, `mode_paiement`, `statut`, `reference_paiement`, `date_paiement`, `date_validation`, `type_operation`, `created_by`, `updated_by`, `notes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(6, 'TXN-2026-7858D8', 'a299981a-d1c1-4690-8e96-d6ddb5df0874', NULL, 6, 600000.00, 840000.00, 1440000.00, 0.00, 0.00, 1440000.00, 12, '2026-05-11', '2027-05-11', '[\"1\", \"2\", \"3\", \"4\", \"5\", \"6\"]', NULL, 'en_attente', NULL, NULL, NULL, 'souscription', 'ADM-001', NULL, NULL, '2026-05-11 16:49:43', '2026-05-11 16:49:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transaction_agences`
--

CREATE TABLE `transaction_agences` (
  `transaction_agence_id` varchar(150) NOT NULL,
  `locataire_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `proprietaire_id` varchar(150) NOT NULL,
  `propriete_id` varchar(150) NOT NULL,
  `batiment_id` varchar(150) NOT NULL,
  `porte_id` varchar(150) NOT NULL,
  `montant_global_verser` int(11) NOT NULL DEFAULT 0,
  `mois_payer` text DEFAULT NULL,
  `arriere_actuel` int(11) NOT NULL DEFAULT 0,
  `montant_arriere_payer` int(11) NOT NULL DEFAULT 0,
  `montant_arriere_actuel` int(11) NOT NULL DEFAULT 0,
  `montant_loyer_payer` int(11) NOT NULL DEFAULT 0,
  `montant_avance_payer` int(11) NOT NULL DEFAULT 0,
  `is_first` tinyint(4) NOT NULL DEFAULT 0,
  `mode_paiement_id` int(11) DEFAULT NULL,
  `is_reversement` tinyint(4) NOT NULL DEFAULT 0,
  `date_transaction` datetime NOT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaction_agences`
--

INSERT INTO `transaction_agences` (`transaction_agence_id`, `locataire_id`, `agence_id`, `proprietaire_id`, `propriete_id`, `batiment_id`, `porte_id`, `montant_global_verser`, `mois_payer`, `arriere_actuel`, `montant_arriere_payer`, `montant_arriere_actuel`, `montant_loyer_payer`, `montant_avance_payer`, `is_first`, `mode_paiement_id`, `is_reversement`, `date_transaction`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
('06d71cb4-df6f-4683-b222-2f3939a4fa6d', 'a60b1ee7-a122-4250-9ecc-f87602b8f581', '2df2a9f8-5d56-4842-a683-8676eb1d017f', '0143080c-1088-45b2-a292-edf809feb8e3', '2254a569-9bee-45dd-bfec-4d0d3212fa5b', '66898dee-57dd-48c4-9358-97770602368a', 'c2a89ff2-c3cb-4069-b0d4-b3c406282d5d', 560000, '[\"Juillet-2026\",\"Ao\\u00fbt-2026\"]', 0, 0, 0, 100000, 200000, 1, NULL, 0, '2026-07-09 17:37:02', 'system', NULL, '2026-07-09 17:37:02', '2026-07-09 17:37:02');

-- --------------------------------------------------------

--
-- Table structure for table `type_maintenances`
--

CREATE TABLE `type_maintenances` (
  `type_maintenance_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `name` varchar(150) NOT NULL,
  `categorie` varchar(150) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `type_maintenances`
--

INSERT INTO `type_maintenances` (`type_maintenance_id`, `agence_id`, `name`, `categorie`, `description`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
('54a4124d-4bdb-4dd7-931f-851279eecea3', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'Réparation 🧑‍🔧', NULL, 'Réparation Electricite🧑‍🔧', '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-06-02 21:21:45', '2026-06-02 21:21:45');

-- --------------------------------------------------------

--
-- Table structure for table `type_pieces`
--

CREATE TABLE `type_pieces` (
  `type_pieces_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `type_pieces`
--

INSERT INTO `type_pieces` (`type_pieces_id`, `name`, `created_at`, `deleted_at`) VALUES
(1, 'CNI', '2026-05-13 00:46:09', '0000-00-00 00:00:00'),
(2, 'Attestation d\'indentite', '2026-05-13 00:46:09', '0000-00-00 00:00:00'),
(3, 'Permis de conduire', '2026-05-13 00:46:09', '0000-00-00 00:00:00'),
(4, 'Carte consulaire', '2026-05-13 00:46:09', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `type_porte`
--

CREATE TABLE `type_porte` (
  `type_porte_id` bigint(20) UNSIGNED NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `type_porte`
--

INSERT INTO `type_porte` (`type_porte_id`, `libelle`, `description`, `created_at`) VALUES
(1, 'magasin', NULL, '2026-05-22 12:14:18'),
(2, 'studio', NULL, '2026-05-22 12:14:18'),
(3, 'Entrée coucher ', NULL, '2026-05-22 12:14:18'),
(4, 'Deux pièces', NULL, '2026-05-22 12:14:18'),
(5, 'Trois pièces ', NULL, '2026-05-22 12:14:18'),
(6, 'Quatre pièces', '', '2026-05-22 12:14:18'),
(7, 'Cinq pièces ', '', '2026-05-22 12:14:18'),
(8, 'Six pieces', '', '2026-05-22 12:14:18');

-- --------------------------------------------------------

--
-- Table structure for table `type_proprietes`
--

CREATE TABLE `type_proprietes` (
  `id` int(11) NOT NULL,
  `agence_id` varchar(150) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `type_proprietes`
--

INSERT INTO `type_proprietes` (`id`, `agence_id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'Villa', NULL, '2024-12-28 21:34:51', '2024-12-28 21:34:51'),
(2, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'Appartement', NULL, '2024-12-28 21:35:47', '2024-12-28 21:35:47'),
(3, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'Terrain', NULL, '2024-12-28 21:35:55', '2024-12-28 21:35:55'),
(4, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'Residence meublée', NULL, '2024-12-28 21:37:06', '2024-12-28 21:37:06'),
(5, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'Magasin', NULL, '2024-12-28 21:44:30', '2024-12-28 21:44:30'),
(8, '2df2a9f8-5d56-4842-a683-8676eb1d017f', 'studio', NULL, '2026-05-18 17:47:45', '2026-05-18 17:47:45');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_users` varchar(150) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `adresse` varchar(250) DEFAULT NULL,
  `agence_id` varchar(150) DEFAULT NULL,
  `is_responsable` tinyint(4) NOT NULL DEFAULT 0,
  `role_id` varchar(150) NOT NULL,
  `tel1` varchar(50) NOT NULL,
  `tel2` varchar(50) DEFAULT NULL,
  `statut` enum('actif','inactif','suspendu') NOT NULL DEFAULT 'actif' COMMENT 'actif => le personnel est en service\r\ninactif => le personnel est en congé ou deplacement (...) mais travaille tourjours\r\nsuspendu => il ne travaille plus dans l''agence',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `photo` varchar(250) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `deleted_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_users`, `name`, `email`, `adresse`, `agence_id`, `is_responsable`, `role_id`, `tel1`, `tel2`, `statut`, `email_verified_at`, `password`, `remember_token`, `created_by`, `photo`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('001f5761-f809-431b-acad-23e6961978c6', 'Koffi Jean', 'koffijean@gmail.com', 'Treichville', 'a299981a-d1c1-4690-8e96-d6ddb5df0874', 1, '1', '0707902999', '0709902970', 'actif', NULL, '$2y$12$2cXZ8QndnT7uYef9ljZY7OeNfW.OLv/JwO/DywO7IHM2otxtTnkKK', NULL, 'ADM-001', NULL, NULL, NULL, '2026-05-11 16:49:43', '2026-05-11 20:16:48', NULL),
('4fb6adb2-c847-44ac-83e7-4c64c033caed', 'Mon Agence', 'agence@test.com', NULL, '', 0, '', '', NULL, 'actif', NULL, '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, NULL, NULL, NULL, '2026-05-02 12:25:29', '2026-05-02 12:26:29', NULL),
('4fb6adb2-c847-44ac-83e7-4c64c033caef', 'Rodrigue', 'rodrigue.yapo@soumafe.ci', 'Abidjan', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 1, '1', '0707902962', NULL, 'actif', NULL, '$2y$12$AVLFXIQ7HXGshGXhPBpl2.nPQVq34bq.Tg1ciFz4cRST01H.kuSk2', NULL, NULL, NULL, NULL, NULL, '2026-04-26 23:59:04', '2026-05-11 15:01:19', NULL),
('6b97e65c-42eb-4695-a85d-0a2b1302d73e', 'Ouattara Junior', 'junior.ouattara@prosimmobilier.ci', 'Cocody angre', '2df2a9f8-5d56-4842-a683-8676eb1d017f', 0, '1', '0707902909', NULL, 'actif', NULL, '$2y$12$kAyHVpvE1Dx2bMdU.259.ewPp9q2Pd04kW0aM9KyFusApfRC3WQum', NULL, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '4fb6adb2-c847-44ac-83e7-4c64c033caef', NULL, '2026-05-16 22:35:08', '2026-05-16 23:00:07', NULL),
('ac9b87f2-33a8-4df2-a96e-575edff2b9c4', 'Jonas Kouadio', 'jonask@gmail.com', 'Abobo', 'f2b4c17f-1d74-47fc-975b-49f62e64f9b4', 1, '1', '0707902492', '0709902904', 'actif', NULL, '$2y$12$ILFtC5P8hKfdZEpZKENmWekdnoHsSE4iVIQ3yKZWfRxor8NJrDPAW', NULL, 'ADM-001', '/admin/assets/images/users_photo/6a0248c9ba42c4.27078193.png', NULL, NULL, '2026-05-11 21:23:21', '2026-05-11 21:48:20', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `villes`
--

CREATE TABLE `villes` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `region_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `villes`
--

INSERT INTO `villes` (`id`, `name`, `region_id`, `created_at`, `updated_at`) VALUES
(1, 'Abobo', 1, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(2, 'Anyama', 1, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(3, 'Attécoubé', 1, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(4, 'Cocody', 1, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(5, 'Koumassi', 1, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(6, 'Marcory', 1, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(7, 'Plateau', 1, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(8, 'Port-Bouët', 1, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(9, 'Bingerville', 1, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(10, 'Yopougon', 1, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(11, 'Songon', 1, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(12, 'Treichville', 1, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(13, 'Adjamé', 1, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(14, 'Dabou', 1, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(15, 'Agboville', 2, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(16, 'Sikensi', 2, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(17, 'Taabo', 2, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(18, 'Tiassalé', 2, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(19, 'Koro', 3, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(20, 'Ouaninou', 3, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(21, 'Touba', 3, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(22, 'Boundiali', 4, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(23, 'Kouto', 4, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(24, 'Tengréla', 4, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(25, 'Didiévi', 5, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(26, 'Djékanou', 5, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(27, 'Tiébissou', 5, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(28, 'Toumodi', 5, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(29, 'Dianra', 6, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(30, 'Kounahiri', 6, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(31, 'Mankono', 6, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(32, 'Bouna', 7, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(33, 'Doropo', 7, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(34, 'Nassian', 7, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(35, 'Téhini', 7, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(36, 'Bloléquin', 8, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(37, 'Guiglo', 8, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(38, 'Taï', 8, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(39, 'Toulepleu', 8, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(40, 'Kaniasso', 9, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(41, 'Minignan', 9, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(42, 'Béoumi', 10, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(43, 'Botro', 10, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(44, 'Bouaké', 10, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(45, 'Sakassou', 10, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(46, 'Fresco', 11, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(47, 'Sassandra', 11, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(48, 'Gagnoa', 12, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(49, 'Oumé', 12, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(50, 'Bondoukou', 13, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(51, 'Koun-Fao', 13, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(52, 'Sandégué', 13, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(53, 'Tanda', 13, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(54, 'Transua', 13, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(55, 'Dabou', 14, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(56, 'Grand-Lahou', 14, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(57, 'Jacqueville', 14, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(58, 'Bangolo', 15, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(59, 'Duékoué', 15, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(60, 'Facobly', 15, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(61, 'Kouibly', 15, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(62, 'Daloa', 16, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(63, 'Issia', 16, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(64, 'Vavoua', 16, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(65, 'Zoukougbeu', 16, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(66, 'Daoukro', 17, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(67, 'M’Bahiakro', 17, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(68, 'Ouellé', 17, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(69, 'Prikro', 17, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(70, 'Abengourou', 18, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(71, 'Agnibilékrou', 18, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(72, 'Bettié', 18, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(73, 'Gbéléban', 19, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(74, 'Madinani', 19, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(75, 'Odienné', 19, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(76, 'Samatiguila', 19, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(77, 'Séguélon', 19, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(78, 'Adzopé', 20, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(79, 'Akoupé', 20, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(80, 'Alépé', 20, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(81, 'Yakassé-Attobrou', 20, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(82, 'Divo', 21, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(83, 'Guitry', 21, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(84, 'Lakota', 21, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(85, 'Bonon', 22, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(86, 'Bouaflé', 22, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(87, 'Gohitafla', 22, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(88, 'Sinfra', 22, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(89, 'Zuénoula', 22, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(90, 'Dabakala', 23, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(91, 'Katiola', 23, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(92, 'Niakaramadougou', 23, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(93, 'Arrah', 24, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(94, 'Bongouanou', 24, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(95, 'M’Batto', 24, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(96, 'Buyo', 25, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(97, 'Guéyo', 25, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(98, 'Méagui', 25, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(99, 'Soubré', 25, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(100, 'Bocanda', 26, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(101, 'Dimbokro', 26, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(102, 'Kouassi-Kouassikro', 26, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(103, 'Dikodougou', 27, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(104, 'Korhogo', 27, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(105, 'M’Bengué', 27, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(106, 'Sinématiali', 27, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(107, 'San-Pédro', 28, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(108, 'Tabou', 28, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(109, 'Aboisso', 29, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(110, 'Adiaké', 29, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(111, 'Grand-Bassam', 29, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(112, 'Tiapoum', 29, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(113, 'Ferkessédougou', 30, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(114, 'Kong', 30, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(115, 'Ouangolodougou', 30, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(116, 'Biankouma', 31, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(117, 'Danané', 31, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(118, 'Man', 31, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(119, 'Sipilou', 31, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(120, 'Zouan-Hounien', 31, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(121, 'Kani', 32, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(122, 'Séguéla', 32, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(123, 'Attiégouakro', 33, '2024-12-24 07:05:30', '2024-12-24 07:05:30'),
(124, 'Yamoussoukro', 33, '2024-12-24 07:05:30', '2024-12-24 07:05:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `abonnements`
--
ALTER TABLE `abonnements`
  ADD PRIMARY KEY (`abonnement_id`),
  ADD UNIQUE KEY `abonnements_code_abonnement_unique` (`code_abonnement`);

--
-- Indexes for table `abonnement_historiques`
--
ALTER TABLE `abonnement_historiques`
  ADD PRIMARY KEY (`id`),
  ADD KEY `abonnement_historiques_agence_id_foreign` (`agence_id`),
  ADD KEY `abonnement_historiques_ancien_abonnement_id_foreign` (`ancien_abonnement_id`),
  ADD KEY `abonnement_historiques_nouvel_abonnement_id_foreign` (`nouvel_abonnement_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id_admin`),
  ADD KEY `email` (`email`);

--
-- Indexes for table `agences`
--
ALTER TABLE `agences`
  ADD PRIMARY KEY (`agence_id`);

--
-- Indexes for table `batiment`
--
ALTER TABLE `batiment`
  ADD PRIMARY KEY (`batiment_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `caisses`
--
ALTER TABLE `caisses`
  ADD PRIMARY KEY (`caisse_id`);

--
-- Indexes for table `configurations`
--
ALTER TABLE `configurations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `configuration_tarifs`
--
ALTER TABLE `configuration_tarifs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `configuration_tarif_durees`
--
ALTER TABLE `configuration_tarif_durees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tarif_durees_tarif_id_foreign` (`tarif_id`);

--
-- Indexes for table `configuration_tarif_modules`
--
ALTER TABLE `configuration_tarif_modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tarif_modules_tarif_id_foreign` (`tarif_id`);

--
-- Indexes for table `equipement_proprietes`
--
ALTER TABLE `equipement_proprietes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fonction_maintenance`
--
ALTER TABLE `fonction_maintenance`
  ADD PRIMARY KEY (`fonction_maintenance_id`);

--
-- Indexes for table `genres`
--
ALTER TABLE `genres`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `locataire`
--
ALTER TABLE `locataire`
  ADD PRIMARY KEY (`locataire_id`);

--
-- Indexes for table `locataire_agence`
--
ALTER TABLE `locataire_agence`
  ADD PRIMARY KEY (`locataire_agence_id`);

--
-- Indexes for table `loyer`
--
ALTER TABLE `loyer`
  ADD PRIMARY KEY (`loyer_id`);

--
-- Indexes for table `maintenance`
--
ALTER TABLE `maintenance`
  ADD PRIMARY KEY (`maintenance_id`);

--
-- Indexes for table `maintenance_detail`
--
ALTER TABLE `maintenance_detail`
  ADD PRIMARY KEY (`maintenance_detail_id`);

--
-- Indexes for table `maintenanciers`
--
ALTER TABLE `maintenanciers`
  ADD PRIMARY KEY (`maintenancier_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mode_paiements`
--
ALTER TABLE `mode_paiements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mouvements_caisse`
--
ALTER TABLE `mouvements_caisse`
  ADD PRIMARY KEY (`mouvement_id`);

--
-- Indexes for table `parametrages_agence`
--
ALTER TABLE `parametrages_agence`
  ADD PRIMARY KEY (`parametrages_agence_id`);

--
-- Indexes for table `periodicite_paiements`
--
ALTER TABLE `periodicite_paiements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `periodicite_paiements_name_unique` (`name`);

--
-- Indexes for table `porte`
--
ALTER TABLE `porte`
  ADD PRIMARY KEY (`porte_id`),
  ADD UNIQUE KEY `porte_id` (`porte_id`),
  ADD UNIQUE KEY `batiment_id` (`batiment_id`,`numero_porte`);

--
-- Indexes for table `propietaire_lots`
--
ALTER TABLE `propietaire_lots`
  ADD PRIMARY KEY (`propreietaire_lot_id`);

--
-- Indexes for table `proprietaires`
--
ALTER TABLE `proprietaires`
  ADD PRIMARY KEY (`proprietaire_id`),
  ADD UNIQUE KEY `numpiece` (`numpiece`),
  ADD UNIQUE KEY `tel1` (`tel1`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `proprietaire_agences`
--
ALTER TABLE `proprietaire_agences`
  ADD PRIMARY KEY (`proprietaire_agence_id`);

--
-- Indexes for table `propriete`
--
ALTER TABLE `propriete`
  ADD PRIMARY KEY (`propriete_id`),
  ADD UNIQUE KEY `reference` (`reference`);

--
-- Indexes for table `propriete_proximites`
--
ALTER TABLE `propriete_proximites`
  ADD PRIMARY KEY (`propriete_proximite_id`),
  ADD KEY `propriete_proximites_propriete_id_index` (`propriete_id`),
  ADD KEY `propriete_proximites_proximite_id_index` (`proximite_id`);

--
-- Indexes for table `prossimite_proprietes`
--
ALTER TABLE `prossimite_proprietes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `regions`
--
ALTER TABLE `regions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tarif_porte`
--
ALTER TABLE `tarif_porte`
  ADD UNIQUE KEY `tarif_id` (`tarif_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD UNIQUE KEY `transactions_reference_unique` (`reference`),
  ADD KEY `transactions_agence_id_foreign` (`agence_id`),
  ADD KEY `transactions_abonnement_id_foreign` (`abonnement_id`),
  ADD KEY `transactions_abonnement_historique_id_foreign` (`abonnement_historique_id`),
  ADD KEY `transactions_agence_id_statut_index` (`agence_id`,`statut`),
  ADD KEY `transactions_statut_created_at_index` (`statut`,`created_at`),
  ADD KEY `transactions_reference_index` (`reference`);

--
-- Indexes for table `transaction_agences`
--
ALTER TABLE `transaction_agences`
  ADD PRIMARY KEY (`transaction_agence_id`);

--
-- Indexes for table `type_maintenances`
--
ALTER TABLE `type_maintenances`
  ADD PRIMARY KEY (`type_maintenance_id`);

--
-- Indexes for table `type_pieces`
--
ALTER TABLE `type_pieces`
  ADD PRIMARY KEY (`type_pieces_id`);

--
-- Indexes for table `type_porte`
--
ALTER TABLE `type_porte`
  ADD PRIMARY KEY (`type_porte_id`),
  ADD UNIQUE KEY `type_porte_id` (`type_porte_id`);

--
-- Indexes for table `type_proprietes`
--
ALTER TABLE `type_proprietes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_users`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `villes`
--
ALTER TABLE `villes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `abonnements`
--
ALTER TABLE `abonnements`
  MODIFY `abonnement_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `abonnement_historiques`
--
ALTER TABLE `abonnement_historiques`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `caisses`
--
ALTER TABLE `caisses`
  MODIFY `caisse_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `configurations`
--
ALTER TABLE `configurations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `configuration_tarifs`
--
ALTER TABLE `configuration_tarifs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `configuration_tarif_durees`
--
ALTER TABLE `configuration_tarif_durees`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `configuration_tarif_modules`
--
ALTER TABLE `configuration_tarif_modules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `equipement_proprietes`
--
ALTER TABLE `equipement_proprietes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `genres`
--
ALTER TABLE `genres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `mode_paiements`
--
ALTER TABLE `mode_paiements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `mouvements_caisse`
--
ALTER TABLE `mouvements_caisse`
  MODIFY `mouvement_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `periodicite_paiements`
--
ALTER TABLE `periodicite_paiements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `prossimite_proprietes`
--
ALTER TABLE `prossimite_proprietes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `regions`
--
ALTER TABLE `regions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `type_pieces`
--
ALTER TABLE `type_pieces`
  MODIFY `type_pieces_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `type_porte`
--
ALTER TABLE `type_porte`
  MODIFY `type_porte_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `type_proprietes`
--
ALTER TABLE `type_proprietes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `villes`
--
ALTER TABLE `villes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `abonnement_historiques`
--
ALTER TABLE `abonnement_historiques`
  ADD CONSTRAINT `abonnement_historiques_ancien_abonnement_id_foreign` FOREIGN KEY (`ancien_abonnement_id`) REFERENCES `abonnements` (`abonnement_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `abonnement_historiques_nouvel_abonnement_id_foreign` FOREIGN KEY (`nouvel_abonnement_id`) REFERENCES `abonnements` (`abonnement_id`) ON DELETE SET NULL;

--
-- Constraints for table `configuration_tarif_durees`
--
ALTER TABLE `configuration_tarif_durees`
  ADD CONSTRAINT `tarif_durees_tarif_id_foreign` FOREIGN KEY (`tarif_id`) REFERENCES `configuration_tarifs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `configuration_tarif_modules`
--
ALTER TABLE `configuration_tarif_modules`
  ADD CONSTRAINT `tarif_modules_tarif_id_foreign` FOREIGN KEY (`tarif_id`) REFERENCES `configuration_tarifs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_abonnement_historique_id_foreign` FOREIGN KEY (`abonnement_historique_id`) REFERENCES `abonnement_historiques` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `transactions_abonnement_id_foreign` FOREIGN KEY (`abonnement_id`) REFERENCES `abonnements` (`abonnement_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
