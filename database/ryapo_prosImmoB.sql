
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `abonnement_historiques`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abonnement_historiques` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `agence_id` varchar(255) NOT NULL,
  `ancien_abonnement_id` bigint(20) unsigned DEFAULT NULL,
  `nouvel_abonnement_id` bigint(20) unsigned DEFAULT NULL,
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
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `abonnement_historiques_agence_id_foreign` (`agence_id`),
  KEY `abonnement_historiques_ancien_abonnement_id_foreign` (`ancien_abonnement_id`),
  KEY `abonnement_historiques_nouvel_abonnement_id_foreign` (`nouvel_abonnement_id`),
  CONSTRAINT `abonnement_historiques_ancien_abonnement_id_foreign` FOREIGN KEY (`ancien_abonnement_id`) REFERENCES `abonnements` (`abonnement_id`) ON DELETE SET NULL,
  CONSTRAINT `abonnement_historiques_nouvel_abonnement_id_foreign` FOREIGN KEY (`nouvel_abonnement_id`) REFERENCES `abonnements` (`abonnement_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `abonnements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `abonnements` (
  `abonnement_id` bigint(20) unsigned NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'plan',
  `agence_id` varchar(255) DEFAULT NULL,
  `ancien_abonnement_id` bigint(20) unsigned DEFAULT NULL,
  `nouvel_abonnement_id` bigint(20) unsigned DEFAULT NULL,
  `ancienne_date_debut` date DEFAULT NULL,
  `ancienne_date_fin` date DEFAULT NULL,
  `nouvelle_date_debut` date DEFAULT NULL,
  `nouvelle_date_fin` date DEFAULT NULL,
  `duree_mois` int(10) unsigned DEFAULT NULL,
  `montant_ht` decimal(12,2) NOT NULL DEFAULT 0.00,
  `action` varchar(255) DEFAULT NULL,
  `action_par` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`abonnement_id`),
  KEY `idx_agence_id` (`agence_id`),
  KEY `idx_ancien_abonnement` (`ancien_abonnement_id`),
  KEY `idx_nouvel_abonnement` (`nouvel_abonnement_id`),
  KEY `idx_code_abonnement` (`code_abonnement`),
  KEY `idx_statut` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `acheteurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acheteurs` (
  `id_acheteur` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `name` varchar(150) NOT NULL,
  `telephone1` varchar(30) NOT NULL,
  `telephone2` varchar(30) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `type_piece_id` int(11) NOT NULL,
  `numero_piece` varchar(50) DEFAULT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_acheteur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `remember_token` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_admin`),
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `agences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`agence_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `batiment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  PRIMARY KEY (`batiment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cache` (
  `key` varchar(250) NOT NULL,
  `value` text DEFAULT NULL,
  `expiration` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `caisse_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caisse_sessions` (
  `caisse_session_id` char(36) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `opened_by` varchar(150) DEFAULT NULL,
  `closed_by` varchar(150) DEFAULT NULL,
  `solde_ouverture` decimal(15,2) NOT NULL,
  `solde_theorique` decimal(15,2) DEFAULT NULL,
  `solde_fermeture` decimal(15,2) DEFAULT NULL,
  `ecart` decimal(15,2) DEFAULT NULL,
  `observation_ouverture` text DEFAULT NULL,
  `observation_fermeture` text DEFAULT NULL,
  `opened_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`caisse_session_id`),
  KEY `caisse_sessions_agence_id_closed_at_index` (`agence_id`,`closed_at`),
  KEY `caisse_sessions_agence_id_index` (`agence_id`),
  KEY `caisse_sessions_opened_at_index` (`opened_at`),
  KEY `caisse_sessions_closed_at_index` (`closed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `caisses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `caisses` (
  `caisse_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `agence_id` bigint(20) unsigned NOT NULL,
  `libelle` varchar(100) NOT NULL,
  `solde` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`caisse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `configuration_tarif_durees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuration_tarif_durees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tarif_id` bigint(20) unsigned NOT NULL,
  `nombre_mois` int(11) NOT NULL,
  `prix_reduit` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tarif_durees_tarif_id_foreign` (`tarif_id`),
  CONSTRAINT `tarif_durees_tarif_id_foreign` FOREIGN KEY (`tarif_id`) REFERENCES `configuration_tarifs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `configuration_tarif_modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuration_tarif_modules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tarif_id` bigint(20) unsigned NOT NULL,
  `label` varchar(255) NOT NULL,
  `prix_mensuel` decimal(10,2) NOT NULL DEFAULT 0.00,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `ordre` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tarif_modules_tarif_id_foreign` (`tarif_id`),
  CONSTRAINT `tarif_modules_tarif_id_foreign` FOREIGN KEY (`tarif_id`) REFERENCES `configuration_tarifs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `configuration_tarifs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuration_tarifs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plan_nom` varchar(255) NOT NULL DEFAULT 'Abonnement de base',
  `plan_prix_mensuel` decimal(10,2) NOT NULL DEFAULT 0.00,
  `delai_grace` int(11) NOT NULL DEFAULT 0,
  `cycle_facturation` enum('mensuel','annuel') NOT NULL DEFAULT 'mensuel',
  `plan_description` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `configurations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configurations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `mention_legale` longtext DEFAULT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `website_story` text DEFAULT NULL,
  `website_mission_title` varchar(255) DEFAULT NULL,
  `website_mission_text` text DEFAULT NULL,
  `website_commitments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`website_commitments`)),
  `website_faqs` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`website_faqs`)),
  `owner_android_url` varchar(255) DEFAULT NULL,
  `owner_ios_url` varchar(255) DEFAULT NULL,
  `tenant_android_url` varchar(255) DEFAULT NULL,
  `tenant_ios_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contact_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_messages` (
  `contact_message_id` char(36) NOT NULL,
  `request_type` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'new',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`contact_message_id`),
  KEY `contact_messages_status_created_at_index` (`status`,`created_at`),
  KEY `contact_messages_request_type_index` (`request_type`),
  KEY `contact_messages_email_index` (`email`),
  KEY `contact_messages_status_index` (`status`),
  KEY `contact_messages_processed_at_index` (`processed_at`),
  KEY `contact_messages_processed_by_index` (`processed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `contact_replies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_replies` (
  `contact_reply_id` char(36) NOT NULL,
  `contact_message_id` char(36) NOT NULL,
  `admin_id` varchar(150) DEFAULT NULL,
  `channel` varchar(30) NOT NULL DEFAULT 'email',
  `recipient` varchar(150) NOT NULL,
  `subject` varchar(180) NOT NULL,
  `message` text NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`contact_reply_id`),
  KEY `contact_replies_contact_message_id_index` (`contact_message_id`),
  KEY `contact_replies_admin_id_index` (`admin_id`),
  KEY `contact_replies_status_index` (`status`),
  KEY `contact_replies_sent_at_index` (`sent_at`),
  CONSTRAINT `contact_replies_contact_message_id_foreign` FOREIGN KEY (`contact_message_id`) REFERENCES `contact_messages` (`contact_message_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `equipement_proprietes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `equipement_proprietes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `fonction_maintenance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fonction_maintenance` (
  `fonction_maintenance_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `name` varchar(250) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`fonction_maintenance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `genres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `genres` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `abreviation` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `locataire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  PRIMARY KEY (`locataire_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `locataire_agence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `date_fin_bail` timestamp NULL DEFAULT NULL,
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
  `periodicite_paiement_id` bigint(20) unsigned DEFAULT NULL,
  `mode_paiement_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`locataire_agence_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `loyer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  PRIMARY KEY (`loyer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `maintenance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`maintenance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `maintenance_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `maintenance_categories` (
  `maintenance_category_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `maintenance_detail`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`maintenance_detail_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `maintenanciers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`maintenancier_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mode_paiements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mode_paiements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `module_actions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_actions` (
  `module_action_id` char(36) NOT NULL,
  `module_id` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_critical` tinyint(1) NOT NULL DEFAULT 0,
  `order_index` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`module_action_id`),
  UNIQUE KEY `module_actions_module_slug_unique` (`module_id`,`slug`),
  UNIQUE KEY `module_actions_id_module_unique` (`module_action_id`,`module_id`),
  KEY `module_actions_active_order_index` (`module_id`,`is_active`,`order_index`),
  CONSTRAINT `module_actions_module_id_foreign` FOREIGN KEY (`module_id`) REFERENCES `modules` (`module_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `modules` (
  `module_id` char(36) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `route` varchar(255) DEFAULT NULL,
  `parent_id` char(36) DEFAULT NULL,
  `order_index` int(10) unsigned NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `deleted_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`module_id`),
  UNIQUE KEY `modules_slug_unique` (`slug`),
  KEY `modules_parent_order_index` (`parent_id`,`order_index`),
  KEY `modules_active_index` (`is_active`),
  CONSTRAINT `modules_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `modules` (`module_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `mouvements_caisse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mouvements_caisse` (
  `mouvement_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `caisse_id` bigint(20) unsigned NOT NULL,
  `agence_id` bigint(20) unsigned NOT NULL,
  `transaction_agence_id` bigint(20) unsigned DEFAULT NULL,
  `loyer_id` bigint(20) unsigned DEFAULT NULL,
  `type` enum('entree','sortie') NOT NULL,
  `motif` varchar(191) NOT NULL,
  `montant` decimal(15,2) NOT NULL,
  `mode_paiement_id` bigint(20) unsigned DEFAULT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `commentaire` text DEFAULT NULL,
  `date_mouvement` date NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`mouvement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `parametrages_agence`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`parametrages_agence_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `periodicite_paiements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `periodicite_paiements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_actif` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `periodicite_paiements_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `porte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`porte_id`),
  UNIQUE KEY `porte_id` (`porte_id`),
  UNIQUE KEY `batiment_id` (`batiment_id`,`numero_porte`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `propietaire_lots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `propietaire_lots` (
  `propreietaire_lot_id` varchar(150) NOT NULL,
  `name` varchar(250) NOT NULL,
  `superficie` int(11) DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  `ville_id` int(11) DEFAULT NULL,
  `adresse` varchar(150) DEFAULT NULL,
  `is_for_sale` tinyint(1) NOT NULL DEFAULT 0,
  `sale_price` decimal(15,2) DEFAULT NULL,
  `num_lot` varchar(50) DEFAULT NULL,
  `num_ilot` varchar(50) DEFAULT NULL,
  `proprietaire_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `deleted_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`propreietaire_lot_id`),
  KEY `lots_for_sale_idx` (`is_for_sale`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `proprietaire_agences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `genre_representant_id` bigint(20) unsigned DEFAULT NULL,
  `adresse_representant` varchar(250) DEFAULT NULL,
  `tel1_representant` varchar(50) DEFAULT NULL,
  `tel2_representant` varchar(50) DEFAULT NULL,
  `email_representant` varchar(250) DEFAULT NULL,
  `type_pieces_representant_id` bigint(20) unsigned DEFAULT NULL,
  `numpiece_representant` varchar(255) DEFAULT NULL,
  `photo_representant` varchar(255) DEFAULT NULL,
  `created_by` varchar(150) NOT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `deleted_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`proprietaire_agence_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `proprietaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`proprietaire_id`),
  UNIQUE KEY `numpiece` (`numpiece`),
  UNIQUE KEY `tel1` (`tel1`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `propriete`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `sale_type` enum('none','whole','by_door') NOT NULL DEFAULT 'none',
  `sale_price` decimal(15,2) DEFAULT NULL,
  `is_actif` tinyint(1) DEFAULT 1,
  `prossimites` text DEFAULT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `deleted_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`propriete_id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `properties_sale_type_idx` (`sale_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `propriete_proximites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `propriete_proximites` (
  `propriete_proximite_id` char(36) NOT NULL,
  `propriete_id` char(36) NOT NULL,
  `proximite_id` bigint(20) unsigned NOT NULL,
  `distance` decimal(10,2) DEFAULT NULL,
  `unite` varchar(5) DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `updated_by` varchar(255) DEFAULT NULL,
  `deleted_by` varchar(255) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`propriete_proximite_id`),
  KEY `propriete_proximites_propriete_id_index` (`propriete_id`),
  KEY `propriete_proximites_proximite_id_index` (`proximite_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `prossimite_proprietes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prossimite_proprietes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reversement_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reversement_details` (
  `id_reversement_detail` varchar(255) NOT NULL,
  `reversement_id` varchar(255) NOT NULL,
  `locataire_id` varchar(150) NOT NULL,
  `porte_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `proprietaire_id` varchar(150) NOT NULL,
  `lot_id` varchar(150) NOT NULL,
  `propriete_id` varchar(150) NOT NULL,
  `batiment_id` varchar(150) NOT NULL,
  `montant_loyer` int(11) NOT NULL,
  `arrieres_init` int(11) NOT NULL DEFAULT 0,
  `montant_attendu` int(11) NOT NULL,
  `loyer_paye` int(11) NOT NULL DEFAULT 0,
  `arriere_paye` int(11) NOT NULL DEFAULT 0,
  `total_paye` int(11) NOT NULL DEFAULT 0,
  `impayes` int(11) NOT NULL DEFAULT 0,
  `date_paiement` date DEFAULT NULL,
  `caution_payee` int(11) DEFAULT 0,
  `mois_payer` text DEFAULT NULL,
  `caution_sodeci` int(11) DEFAULT 0,
  `date_entree` date DEFAULT NULL,
  `nouvelle_caution` int(11) DEFAULT 0,
  `montant_paye` int(11) DEFAULT 0,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_reversement_detail`),
  KEY `idx_reversement_details_reversement` (`reversement_id`),
  KEY `idx_reversement_details_locataire` (`locataire_id`),
  KEY `idx_reversement_details_porte` (`porte_id`),
  KEY `idx_reversement_details_propriete` (`propriete_id`),
  KEY `idx_reversement_details_proprietaire` (`proprietaire_id`),
  KEY `idx_reversement_details_agence` (`agence_id`),
  KEY `idx_reversement_details_proprietaire_lot` (`lot_id`),
  KEY `idx_reversement_details_batiment` (`batiment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `reversements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reversements` (
  `id_reversement` varchar(150) NOT NULL,
  `lot_id` varchar(150) NOT NULL,
  `proprietaire_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `periode_debut` date NOT NULL,
  `periode_fin` date NOT NULL,
  `total_attendu` int(11) NOT NULL DEFAULT 0,
  `total_encaisse` int(11) NOT NULL DEFAULT 0,
  `total_restant` int(11) NOT NULL DEFAULT 0,
  `total_loyer_paye` int(11) NOT NULL DEFAULT 0,
  `total_arriere_paye` int(11) NOT NULL DEFAULT 0,
  `taux_commission` decimal(5,2) NOT NULL DEFAULT 10.00,
  `montant_commission` int(11) NOT NULL DEFAULT 0,
  `montant_apres_commission` int(11) NOT NULL DEFAULT 0,
  `nouvelle_caution` int(11) NOT NULL DEFAULT 0,
  `depenses_effectuees` int(11) NOT NULL DEFAULT 0,
  `net_a_reverser` int(11) NOT NULL DEFAULT 0,
  `statut` varchar(20) NOT NULL DEFAULT 'en_attente' CHECK (`statut` in ('en_attente','reverse','annule')),
  `date_reversement` date DEFAULT NULL,
  `mode_paiement` varchar(50) DEFAULT NULL,
  `reference_paiement` varchar(100) DEFAULT NULL,
  `numero_cheque` varchar(50) DEFAULT NULL,
  `observation` text DEFAULT NULL,
  `signe_par` varchar(100) DEFAULT NULL,
  `date_signature` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `deleted_by` varchar(150) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_reversement`),
  KEY `idx_reversements_cour` (`lot_id`),
  KEY `idx_reversements_proprietaire` (`proprietaire_id`),
  KEY `idx_reversements_agence` (`agence_id`),
  KEY `idx_reversements_periode` (`periode_debut`,`periode_fin`),
  KEY `idx_reversements_statut` (`statut`),
  KEY `idx_reversements_date_reversement` (`date_reversement`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_permissions` (
  `role_permission_id` char(36) NOT NULL,
  `role_id` varchar(150) NOT NULL,
  `module_id` char(36) NOT NULL,
  `module_action_id` char(36) NOT NULL,
  `is_allowed` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`role_permission_id`),
  UNIQUE KEY `role_permissions_role_module_action_unique` (`role_id`,`module_id`,`module_action_id`),
  KEY `role_permissions_allowed_index` (`role_id`,`is_allowed`),
  KEY `role_permissions_module_id_index` (`module_id`),
  KEY `role_permissions_module_action_id_index` (`module_action_id`),
  KEY `role_permissions_module_action_module_foreign` (`module_action_id`,`module_id`),
  CONSTRAINT `role_permissions_module_action_module_foreign` FOREIGN KEY (`module_action_id`, `module_id`) REFERENCES `module_actions` (`module_action_id`, `module_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `role_id` varchar(150) NOT NULL,
  `name` varchar(250) NOT NULL,
  `slug` varchar(150) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `agence_id` varchar(150) DEFAULT NULL,
  `base_role_id` varchar(150) DEFAULT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `support_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_attachments` (
  `support_attachment_id` char(36) NOT NULL,
  `support_ticket_id` char(36) NOT NULL,
  `support_message_id` char(36) DEFAULT NULL,
  `nom_original` varchar(255) NOT NULL,
  `chemin` varchar(255) NOT NULL,
  `mime_type` varchar(120) NOT NULL,
  `taille` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`support_attachment_id`),
  KEY `support_attachments_support_ticket_id_index` (`support_ticket_id`),
  KEY `support_attachments_support_message_id_index` (`support_message_id`),
  CONSTRAINT `support_attachments_support_message_id_foreign` FOREIGN KEY (`support_message_id`) REFERENCES `support_messages` (`support_message_id`) ON DELETE SET NULL,
  CONSTRAINT `support_attachments_support_ticket_id_foreign` FOREIGN KEY (`support_ticket_id`) REFERENCES `support_tickets` (`support_ticket_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `support_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_messages` (
  `support_message_id` char(36) NOT NULL,
  `support_ticket_id` char(36) NOT NULL,
  `auteur_id` varchar(150) DEFAULT NULL,
  `auteur_type` enum('agence','support') NOT NULL DEFAULT 'agence',
  `contenu` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`support_message_id`),
  KEY `support_messages_support_ticket_id_index` (`support_ticket_id`),
  CONSTRAINT `support_messages_support_ticket_id_foreign` FOREIGN KEY (`support_ticket_id`) REFERENCES `support_tickets` (`support_ticket_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `support_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `support_tickets` (
  `support_ticket_id` char(36) NOT NULL,
  `reference` varchar(30) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `demandeur_id` varchar(150) DEFAULT NULL,
  `categorie` varchar(40) NOT NULL,
  `sujet` varchar(160) NOT NULL,
  `description` text NOT NULL,
  `statut` enum('open','pending','resolved','closed') NOT NULL DEFAULT 'open',
  `priorite` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `resolved_at` timestamp NULL DEFAULT NULL,
  `agence_read_at` timestamp NULL DEFAULT NULL,
  `admin_read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`support_ticket_id`),
  UNIQUE KEY `support_tickets_reference_unique` (`reference`),
  KEY `support_tickets_agence_id_index` (`agence_id`),
  KEY `support_tickets_demandeur_id_index` (`demandeur_id`),
  KEY `support_tickets_statut_index` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tarif_porte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `mt_frais_dossier` decimal(12,2) DEFAULT NULL,
  UNIQUE KEY `tarif_id` (`tarif_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `test` (
  `a` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transaction_agences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `reference` varchar(150) DEFAULT NULL,
  `is_first` tinyint(4) NOT NULL DEFAULT 0,
  `type_transaction` enum('loyer','maintenance','depense','vente') NOT NULL DEFAULT 'loyer',
  `mode_paiement_id` int(11) DEFAULT NULL,
  `is_reversement` tinyint(4) NOT NULL DEFAULT 0,
  `date_reversement` timestamp NULL DEFAULT NULL,
  `reversement_by` varchar(150) DEFAULT NULL,
  `date_transaction` datetime NOT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  PRIMARY KEY (`transaction_agence_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `transactions` (
  `transaction_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reference` varchar(255) NOT NULL,
  `agence_id` varchar(255) NOT NULL,
  `abonnement_id` bigint(20) unsigned DEFAULT NULL,
  `abonnement_historique_id` bigint(20) unsigned DEFAULT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`transaction_id`),
  UNIQUE KEY `transactions_reference_unique` (`reference`),
  KEY `transactions_agence_id_foreign` (`agence_id`),
  KEY `transactions_abonnement_id_foreign` (`abonnement_id`),
  KEY `transactions_abonnement_historique_id_foreign` (`abonnement_historique_id`),
  KEY `transactions_agence_id_statut_index` (`agence_id`,`statut`),
  KEY `transactions_statut_created_at_index` (`statut`,`created_at`),
  KEY `transactions_reference_index` (`reference`),
  CONSTRAINT `transactions_abonnement_historique_id_foreign` FOREIGN KEY (`abonnement_historique_id`) REFERENCES `abonnement_historiques` (`id`) ON DELETE SET NULL,
  CONSTRAINT `transactions_abonnement_id_foreign` FOREIGN KEY (`abonnement_id`) REFERENCES `abonnements` (`abonnement_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_maintenances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type_maintenances` (
  `type_maintenance_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `name` varchar(150) NOT NULL,
  `categorie` varchar(150) DEFAULT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`type_maintenance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_pieces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type_pieces` (
  `type_pieces_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp(),
  PRIMARY KEY (`type_pieces_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_porte`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type_porte` (
  `type_porte_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `libelle` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`type_porte_id`),
  UNIQUE KEY `type_porte_id` (`type_porte_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `type_proprietes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `type_proprietes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agence_id` varchar(150) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` varchar(250) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_users`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ventes_biens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ventes_biens` (
  `id_vente` varchar(150) NOT NULL,
  `reference` varchar(30) NOT NULL,
  `propriete_id` varchar(150) NOT NULL,
  `batiment_id` varchar(150) NOT NULL,
  `lot_id` varchar(150) NOT NULL,
  `porte_id` varchar(150) NOT NULL,
  `proprietaire_id` varchar(150) NOT NULL,
  `agence_id` varchar(150) NOT NULL,
  `acheteur_vente_id` varchar(150) DEFAULT NULL,
  `date_accord` date NOT NULL,
  `prix_vente` decimal(14,2) NOT NULL,
  `commission` decimal(14,2) DEFAULT NULL,
  `montant_proprietaire` decimal(14,2) DEFAULT NULL,
  `type_paiement` enum('complet','tranches','mensuel','personnalise') NOT NULL,
  `acompte_mensuel` decimal(14,2) DEFAULT 0.00,
  `nombre_mensualites` int(11) DEFAULT 0,
  `date_premiere_mensualite` date DEFAULT NULL,
  `statut` enum('en_cours','partiel','termine','annule') NOT NULL DEFAULT 'en_cours',
  `created_by` varchar(150) DEFAULT NULL,
  `updated_by` varchar(150) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_vente`),
  UNIQUE KEY `reference` (`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `villes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `villes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `region_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=126 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
