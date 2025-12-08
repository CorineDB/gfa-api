-- MySQL dump 10.13  Distrib 8.0.29, for Linux (x86_64)
--
-- Host: localhost    Database: papcdb
-- ------------------------------------------------------
-- Server version	8.0.29-0ubuntu0.20.04.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activite_users`
--

DROP TABLE IF EXISTS `activite_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activite_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `activiteId` bigint unsigned NOT NULL,
  `userId` bigint unsigned NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activite_users_activiteid_foreign` (`activiteId`),
  KEY `activite_users_userid_foreign` (`userId`),
  CONSTRAINT `activite_users_activiteid_foreign` FOREIGN KEY (`activiteId`) REFERENCES `activites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `activite_users_userid_foreign` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activite_users`
--

LOCK TABLES `activite_users` WRITE;
/*!40000 ALTER TABLE `activite_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `activite_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activites`
--

DROP TABLE IF EXISTS `activites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL,
  `poids` int NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pret` bigint NOT NULL,
  `budgetNational` bigint NOT NULL,
  `tepPrevu` int NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `composanteId` bigint unsigned NOT NULL,
  `userId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activites_composanteid_foreign` (`composanteId`),
  KEY `activites_userid_foreign` (`userId`),
  CONSTRAINT `activites_composanteid_foreign` FOREIGN KEY (`composanteId`) REFERENCES `composantes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `activites_userid_foreign` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activites`
--

LOCK TABLES `activites` WRITE;
/*!40000 ALTER TABLE `activites` DISABLE KEYS */;
/*!40000 ALTER TABLE `activites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `causer_type` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `ipAdresse` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userAgent` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (1,'Enrégistrement','BOCOGA Corine a crée un programme','App\\Models\\Programme',1,'App\\Models\\User',1,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:03:43','2022-07-22 14:03:43'),(2,'Modification','BOCOGA Corine a mis à jour un programme','App\\Models\\Programme',1,'App\\Models\\User',1,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:03:55','2022-07-22 14:03:55'),(3,'Enrégistrement','BOCOGA Corine a crée un user','App\\Models\\User',12,'App\\Models\\User',1,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:04:49','2022-07-22 14:04:49'),(4,'Enrégistrement','BOCOGA Corine a créé l\'unitée de gestion POOL PAPC.','App\\Models\\UniteeDeGestion',1,'App\\Models\\User',1,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:04:50','2022-07-22 14:04:50'),(5,'Modification','BOCOGA Corine a modifié le compte de unitée de gestion POOL PAPC.','App\\Models\\UniteeDeGestion',1,'App\\Models\\User',1,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:05:27','2022-07-22 14:05:27'),(6,'Enrégistrement','POOL PAPC  a crée un user','App\\Models\\User',13,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:24:53','2022-07-22 14:24:53'),(7,'Enrégistrement','POOL PAPC  a créé le compte du bailleur .','App\\Models\\Bailleur',1,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:24:54','2022-07-22 14:24:54'),(8,'Modification','POOL PAPC  a modifié le compte du bailleur .','App\\Models\\Bailleur',1,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:25:05','2022-07-22 14:25:05'),(9,'Enrégistrement','POOL PAPC  a crée un user','App\\Models\\User',14,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:32:19','2022-07-22 14:32:19'),(10,'Enrégistrement','POOL PAPC  a créé un mod .','App\\Models\\MOD',1,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:32:19','2022-07-22 14:32:19'),(11,'Enrégistrement','POOL PAPC  a crée un user','App\\Models\\User',15,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:33:05','2022-07-22 14:33:05'),(12,'Enrégistrement','POOL PAPC  a créé un compte unitee de gestion .','App\\Models\\MissionDeControle',1,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:33:05','2022-07-22 14:33:05'),(13,'Enrégistrement','POOL PAPC  a crée un user','App\\Models\\User',16,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:33:35','2022-07-22 14:33:35'),(14,'Enrégistrement','POOL PAPC  a créé un compte pour l\'entreprise .','App\\Models\\EntrepriseExecutant',1,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:33:35','2022-07-22 14:33:35'),(15,'Enrégistrement','POOL PAPC  a crée un user','App\\Models\\User',17,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:34:10','2022-07-22 14:34:10'),(16,'Enrégistrement','POOL PAPC  a créé un compte pour l\'entreprise .','App\\Models\\EntrepriseExecutant',2,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:34:10','2022-07-22 14:34:10'),(17,'Suppression','POOL PAPC  a supprimé un user','App\\Models\\User',9,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:34:20','2022-07-22 14:34:20'),(18,'Enrégistrement','POOL PAPC  a créé un compte AZURE.','App\\Models\\User',18,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:34:48','2022-07-22 14:34:48'),(19,'Enrégistrement','POOL PAPC  a crée un user','App\\Models\\User',19,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:35:32','2022-07-22 14:35:32'),(20,'Enrégistrement','POOL PAPC  a créé le compte de l\'ong AZURE group','App\\Models\\OngCom',1,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:35:33','2022-07-22 14:35:33'),(21,'Enrégistrement','POOL PAPC  a crée un user','App\\Models\\User',20,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:36:11','2022-07-22 14:36:11'),(22,'Enrégistrement','POOL PAPC  a créé le compte de l\'agence DIGI-COMM','App\\Models\\OngCom',2,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-22 14:36:11','2022-07-22 14:36:11'),(23,'Enrégistrement','POOL PAPC  a crée un user','App\\Models\\User',21,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-24 23:43:11','2022-07-24 23:43:11'),(24,'Enrégistrement','POOL PAPC  a créé un mod .','App\\Models\\MOD',2,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-24 23:43:12','2022-07-24 23:43:12'),(25,'Modification','POOL PAPC  a modifié le compte de l\'entreprise .','App\\Models\\EntrepriseExecutant',2,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-24 23:44:07','2022-07-24 23:44:07'),(26,'Connexion','POOL PAPC  vient de se déconnecter.','App\\Models\\User',12,'App\\Models\\User',12,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-24 23:51:08','2022-07-24 23:51:08'),(27,'Connexion','BOCOGA Corine s\'est connecté.','App\\Models\\User',1,'App\\Models\\User',1,'{}','127.0.0.1','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36','2022-07-24 23:51:32','2022-07-24 23:51:32'),(28,'Connexion','BOCOGA Corine s\'est connecté.','App\\Models\\User',1,'App\\Models\\User',1,'{}','127.0.0.1','PostmanRuntime/7.29.2','2022-07-24 23:52:20','2022-07-24 23:52:20');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `anos`
--

DROP TABLE IF EXISTS `anos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `anos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `dossier` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `auteurId` bigint unsigned NOT NULL,
  `bailleurId` bigint unsigned NOT NULL,
  `typeId` bigint unsigned NOT NULL,
  `destinataire` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dateDeSoumission` date NOT NULL,
  `dateDeReponse` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `anos_bailleurid_foreign` (`bailleurId`),
  KEY `anos_auteurid_foreign` (`auteurId`),
  KEY `anos_typeid_foreign` (`typeId`),
  CONSTRAINT `anos_auteurid_foreign` FOREIGN KEY (`auteurId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `anos_bailleurid_foreign` FOREIGN KEY (`bailleurId`) REFERENCES `bailleurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `anos_typeid_foreign` FOREIGN KEY (`typeId`) REFERENCES `anos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anos`
--

LOCK TABLES `anos` WRITE;
/*!40000 ALTER TABLE `anos` DISABLE KEYS */;
/*!40000 ALTER TABLE `anos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bailleur_entreprise_executants`
--

DROP TABLE IF EXISTS `bailleur_entreprise_executants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bailleur_entreprise_executants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bailleurId` bigint unsigned NOT NULL,
  `entrepriseExecutantId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bailleur_entreprise_executants_bailleurid_foreign` (`bailleurId`),
  KEY `bailleur_entreprise_executants_entrepriseexecutantid_foreign` (`entrepriseExecutantId`),
  CONSTRAINT `bailleur_entreprise_executants_bailleurid_foreign` FOREIGN KEY (`bailleurId`) REFERENCES `bailleurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bailleur_entreprise_executants_entrepriseexecutantid_foreign` FOREIGN KEY (`entrepriseExecutantId`) REFERENCES `entreprise_executants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bailleur_entreprise_executants`
--

LOCK TABLES `bailleur_entreprise_executants` WRITE;
/*!40000 ALTER TABLE `bailleur_entreprise_executants` DISABLE KEYS */;
/*!40000 ALTER TABLE `bailleur_entreprise_executants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bailleur_programmes`
--

DROP TABLE IF EXISTS `bailleur_programmes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bailleur_programmes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bailleurId` bigint unsigned NOT NULL,
  `programmeId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bailleur_programmes_bailleurid_foreign` (`bailleurId`),
  KEY `bailleur_programmes_programmeid_foreign` (`programmeId`),
  CONSTRAINT `bailleur_programmes_bailleurid_foreign` FOREIGN KEY (`bailleurId`) REFERENCES `bailleurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bailleur_programmes_programmeid_foreign` FOREIGN KEY (`programmeId`) REFERENCES `programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bailleur_programmes`
--

LOCK TABLES `bailleur_programmes` WRITE;
/*!40000 ALTER TABLE `bailleur_programmes` DISABLE KEYS */;
INSERT INTO `bailleur_programmes` VALUES (1,1,1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `bailleur_programmes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bailleur_sites`
--

DROP TABLE IF EXISTS `bailleur_sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bailleur_sites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bailleurId` bigint unsigned NOT NULL,
  `siteId` bigint unsigned NOT NULL,
  `programmeId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bailleur_sites_bailleurid_foreign` (`bailleurId`),
  KEY `bailleur_sites_siteid_foreign` (`siteId`),
  KEY `bailleur_sites_programmeid_foreign` (`programmeId`),
  CONSTRAINT `bailleur_sites_bailleurid_foreign` FOREIGN KEY (`bailleurId`) REFERENCES `bailleurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bailleur_sites_programmeid_foreign` FOREIGN KEY (`programmeId`) REFERENCES `programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bailleur_sites_siteid_foreign` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bailleur_sites`
--

LOCK TABLES `bailleur_sites` WRITE;
/*!40000 ALTER TABLE `bailleur_sites` DISABLE KEYS */;
/*!40000 ALTER TABLE `bailleur_sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bailleurs`
--

DROP TABLE IF EXISTS `bailleurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bailleurs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sigle` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pays` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `userId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bailleurs_sigle_unique` (`sigle`),
  KEY `bailleurs_userid_foreign` (`userId`),
  CONSTRAINT `bailleurs_userid_foreign` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bailleurs`
--

LOCK TABLES `bailleurs` WRITE;
/*!40000 ALTER TABLE `bailleurs` DISABLE KEYS */;
INSERT INTO `bailleurs` VALUES (1,'BM','Bénin',13,'2022-07-22 14:24:53','2022-07-22 14:25:05',NULL);
/*!40000 ALTER TABLE `bailleurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cadre_logique_indicateurs`
--

DROP TABLE IF EXISTS `cadre_logique_indicateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cadre_logique_indicateurs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sourceDeVerification` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `hypothese` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `indicatable_id` bigint unsigned NOT NULL,
  `indicatable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cadre_logique_indicateurs`
--

LOCK TABLES `cadre_logique_indicateurs` WRITE;
/*!40000 ALTER TABLE `cadre_logique_indicateurs` DISABLE KEYS */;
/*!40000 ALTER TABLE `cadre_logique_indicateurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_nom_unique` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Indicateurs de résultats','2022-07-22 13:22:11','2022-07-22 13:22:11',NULL),(2,'Indicateurs d\'effet','2022-07-22 13:22:11','2022-07-22 13:22:11',NULL);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `check_list_com`
--

DROP TABLE IF EXISTS `check_list_com`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `check_list_com` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uniteId` bigint unsigned NOT NULL,
  `ongComId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `check_list_com_uniteid_foreign` (`uniteId`),
  KEY `check_list_com_ongcomid_foreign` (`ongComId`),
  CONSTRAINT `check_list_com_ongcomid_foreign` FOREIGN KEY (`ongComId`) REFERENCES `ong_com` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `check_list_com_uniteid_foreign` FOREIGN KEY (`uniteId`) REFERENCES `unitees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `check_list_com`
--

LOCK TABLES `check_list_com` WRITE;
/*!40000 ALTER TABLE `check_list_com` DISABLE KEYS */;
/*!40000 ALTER TABLE `check_list_com` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `check_lists`
--

DROP TABLE IF EXISTS `check_lists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `check_lists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `eActiviteId` bigint unsigned NOT NULL,
  `uniteeId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `check_lists_eactiviteid_foreign` (`eActiviteId`),
  KEY `check_lists_uniteeid_foreign` (`uniteeId`),
  CONSTRAINT `check_lists_eactiviteid_foreign` FOREIGN KEY (`eActiviteId`) REFERENCES `e_activites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `check_lists_uniteeid_foreign` FOREIGN KEY (`uniteeId`) REFERENCES `unitees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `check_lists`
--

LOCK TABLES `check_lists` WRITE;
/*!40000 ALTER TABLE `check_lists` DISABLE KEYS */;
/*!40000 ALTER TABLE `check_lists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `codes`
--

DROP TABLE IF EXISTS `codes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `codes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bailleurId` bigint unsigned NOT NULL,
  `codePta` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `programmeId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `codes_bailleurid_foreign` (`bailleurId`),
  KEY `codes_programmeid_foreign` (`programmeId`),
  CONSTRAINT `codes_bailleurid_foreign` FOREIGN KEY (`bailleurId`) REFERENCES `bailleurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `codes_programmeid_foreign` FOREIGN KEY (`programmeId`) REFERENCES `programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `codes`
--

LOCK TABLES `codes` WRITE;
/*!40000 ALTER TABLE `codes` DISABLE KEYS */;
INSERT INTO `codes` VALUES (1,1,'1',1,'2022-07-22 14:24:54','2022-07-22 14:25:05',NULL);
/*!40000 ALTER TABLE `codes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `com_suivis`
--

DROP TABLE IF EXISTS `com_suivis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `com_suivis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `valeur` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mois` int NOT NULL,
  `annee` int NOT NULL,
  `responsable_enquete` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `checkListComId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `com_suivis_checklistcomid_foreign` (`checkListComId`),
  CONSTRAINT `com_suivis_checklistcomid_foreign` FOREIGN KEY (`checkListComId`) REFERENCES `check_list_com` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `com_suivis`
--

LOCK TABLES `com_suivis` WRITE;
/*!40000 ALTER TABLE `com_suivis` DISABLE KEYS */;
/*!40000 ALTER TABLE `com_suivis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commentaires`
--

DROP TABLE IF EXISTS `commentaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commentaires` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `contenu` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `commentable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `commentable_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commentaires`
--

LOCK TABLES `commentaires` WRITE;
/*!40000 ALTER TABLE `commentaires` DISABLE KEYS */;
/*!40000 ALTER TABLE `commentaires` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `composantes`
--

DROP TABLE IF EXISTS `composantes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `composantes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL,
  `poids` int NOT NULL,
  `pret` bigint NOT NULL,
  `budgetNational` bigint NOT NULL,
  `tepPrevu` int NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `projetId` bigint unsigned DEFAULT NULL,
  `composanteId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `composantes_projetid_foreign` (`projetId`),
  CONSTRAINT `composantes_projetid_foreign` FOREIGN KEY (`projetId`) REFERENCES `projets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `composantes`
--

LOCK TABLES `composantes` WRITE;
/*!40000 ALTER TABLE `composantes` DISABLE KEYS */;
/*!40000 ALTER TABLE `composantes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `decaissements`
--

DROP TABLE IF EXISTS `decaissements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `decaissements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `projetId` bigint unsigned NOT NULL,
  `montant` int NOT NULL,
  `decaissementable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `decaissementable_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `decaissements_decaissementable_type_decaissementable_id_index` (`decaissementable_type`,`decaissementable_id`),
  KEY `decaissements_projetid_foreign` (`projetId`),
  CONSTRAINT `decaissements_projetid_foreign` FOREIGN KEY (`projetId`) REFERENCES `projets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `decaissements`
--

LOCK TABLES `decaissements` WRITE;
/*!40000 ALTER TABLE `decaissements` DISABLE KEYS */;
/*!40000 ALTER TABLE `decaissements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `durees`
--

DROP TABLE IF EXISTS `durees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `durees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `debut` date NOT NULL,
  `fin` date NOT NULL,
  `dureeable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dureeable_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `durees`
--

LOCK TABLES `durees` WRITE;
/*!40000 ALTER TABLE `durees` DISABLE KEYS */;
/*!40000 ALTER TABLE `durees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `e_activite_mods`
--

DROP TABLE IF EXISTS `e_activite_mods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `e_activite_mods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `debut` date NOT NULL,
  `fin` date NOT NULL,
  `modId` bigint unsigned NOT NULL,
  `siteId` bigint unsigned NOT NULL,
  `bailleurId` bigint unsigned NOT NULL,
  `programmeId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `e_activite_mods_modid_foreign` (`modId`),
  KEY `e_activite_mods_siteid_foreign` (`siteId`),
  KEY `e_activite_mods_bailleurid_foreign` (`bailleurId`),
  KEY `e_activite_mods_programmeid_foreign` (`programmeId`),
  CONSTRAINT `e_activite_mods_bailleurid_foreign` FOREIGN KEY (`bailleurId`) REFERENCES `bailleurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `e_activite_mods_modid_foreign` FOREIGN KEY (`modId`) REFERENCES `mods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `e_activite_mods_programmeid_foreign` FOREIGN KEY (`programmeId`) REFERENCES `programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `e_activite_mods_siteid_foreign` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `e_activite_mods`
--

LOCK TABLES `e_activite_mods` WRITE;
/*!40000 ALTER TABLE `e_activite_mods` DISABLE KEYS */;
/*!40000 ALTER TABLE `e_activite_mods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `e_activites`
--

DROP TABLE IF EXISTS `e_activites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `e_activites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `debut` date NOT NULL,
  `fin` date NOT NULL,
  `programmeId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `e_activites_programmeid_foreign` (`programmeId`),
  CONSTRAINT `e_activites_programmeid_foreign` FOREIGN KEY (`programmeId`) REFERENCES `programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `e_activites`
--

LOCK TABLES `e_activites` WRITE;
/*!40000 ALTER TABLE `e_activites` DISABLE KEYS */;
/*!40000 ALTER TABLE `e_activites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `e_suivi_activite_mods`
--

DROP TABLE IF EXISTS `e_suivi_activite_mods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `e_suivi_activite_mods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `niveauDeMiseEnOeuvre` int NOT NULL,
  `eActiviteModId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `e_suivi_activite_mods_eactivitemodid_foreign` (`eActiviteModId`),
  CONSTRAINT `e_suivi_activite_mods_eactivitemodid_foreign` FOREIGN KEY (`eActiviteModId`) REFERENCES `e_activite_mods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `e_suivi_activite_mods`
--

LOCK TABLES `e_suivi_activite_mods` WRITE;
/*!40000 ALTER TABLE `e_suivi_activite_mods` DISABLE KEYS */;
/*!40000 ALTER TABLE `e_suivi_activite_mods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `e_suivies`
--

DROP TABLE IF EXISTS `e_suivies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `e_suivies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `valeur` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mois` int NOT NULL,
  `annee` int NOT NULL,
  `responsable_enquete` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `siteId` bigint unsigned NOT NULL,
  `checkListId` bigint unsigned NOT NULL,
  `missionDeControleId` bigint unsigned NOT NULL,
  `entrepriseExecutantId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `e_suivies_siteid_foreign` (`siteId`),
  KEY `e_suivies_missiondecontroleid_foreign` (`missionDeControleId`),
  KEY `e_suivies_entrepriseexecutantid_foreign` (`entrepriseExecutantId`),
  KEY `e_suivies_checklistid_foreign` (`checkListId`),
  CONSTRAINT `e_suivies_checklistid_foreign` FOREIGN KEY (`checkListId`) REFERENCES `check_lists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `e_suivies_entrepriseexecutantid_foreign` FOREIGN KEY (`entrepriseExecutantId`) REFERENCES `entreprise_executants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `e_suivies_missiondecontroleid_foreign` FOREIGN KEY (`missionDeControleId`) REFERENCES `mission_de_controles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `e_suivies_siteid_foreign` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `e_suivies`
--

LOCK TABLES `e_suivies` WRITE;
/*!40000 ALTER TABLE `e_suivies` DISABLE KEYS */;
/*!40000 ALTER TABLE `e_suivies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entreprise_executant_e_activites`
--

DROP TABLE IF EXISTS `entreprise_executant_e_activites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entreprise_executant_e_activites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `EActiviteId` bigint unsigned NOT NULL,
  `entrepriseExecutantId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entreprise_executant_e_activites_eactiviteid_foreign` (`EActiviteId`),
  KEY `entreprise_executant_e_activites_entrepriseexecutantid_foreign` (`entrepriseExecutantId`),
  CONSTRAINT `entreprise_executant_e_activites_eactiviteid_foreign` FOREIGN KEY (`EActiviteId`) REFERENCES `e_activites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `entreprise_executant_e_activites_entrepriseexecutantid_foreign` FOREIGN KEY (`entrepriseExecutantId`) REFERENCES `entreprise_executants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entreprise_executant_e_activites`
--

LOCK TABLES `entreprise_executant_e_activites` WRITE;
/*!40000 ALTER TABLE `entreprise_executant_e_activites` DISABLE KEYS */;
/*!40000 ALTER TABLE `entreprise_executant_e_activites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entreprise_executant_maitrise_oeuvres`
--

DROP TABLE IF EXISTS `entreprise_executant_maitrise_oeuvres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entreprise_executant_maitrise_oeuvres` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `maitriseOeuvreId` bigint unsigned NOT NULL,
  `entrepriseExecutantId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entreprise_executant_maitrise_oeuvres_maitriseoeuvreid_foreign` (`maitriseOeuvreId`),
  KEY `e_executant_id_foreign` (`entrepriseExecutantId`),
  CONSTRAINT `e_executant_id_foreign` FOREIGN KEY (`entrepriseExecutantId`) REFERENCES `entreprise_executants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `entreprise_executant_maitrise_oeuvres_maitriseoeuvreid_foreign` FOREIGN KEY (`maitriseOeuvreId`) REFERENCES `maitrise_oeuvres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entreprise_executant_maitrise_oeuvres`
--

LOCK TABLES `entreprise_executant_maitrise_oeuvres` WRITE;
/*!40000 ALTER TABLE `entreprise_executant_maitrise_oeuvres` DISABLE KEYS */;
/*!40000 ALTER TABLE `entreprise_executant_maitrise_oeuvres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entreprise_executant_mods`
--

DROP TABLE IF EXISTS `entreprise_executant_mods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entreprise_executant_mods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `modId` bigint unsigned NOT NULL,
  `programmeId` bigint unsigned NOT NULL,
  `entrepriseExecutantId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entreprise_executant_mods_modid_foreign` (`modId`),
  KEY `entreprise_executant_mods_entrepriseexecutantid_foreign` (`entrepriseExecutantId`),
  KEY `entreprise_executant_mods_programmeid_foreign` (`programmeId`),
  CONSTRAINT `entreprise_executant_mods_entrepriseexecutantid_foreign` FOREIGN KEY (`entrepriseExecutantId`) REFERENCES `entreprise_executants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `entreprise_executant_mods_modid_foreign` FOREIGN KEY (`modId`) REFERENCES `mods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `entreprise_executant_mods_programmeid_foreign` FOREIGN KEY (`programmeId`) REFERENCES `programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entreprise_executant_mods`
--

LOCK TABLES `entreprise_executant_mods` WRITE;
/*!40000 ALTER TABLE `entreprise_executant_mods` DISABLE KEYS */;
INSERT INTO `entreprise_executant_mods` VALUES (1,1,1,1,NULL,NULL,NULL),(2,1,1,2,NULL,NULL,NULL);
/*!40000 ALTER TABLE `entreprise_executant_mods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entreprise_executant_sites`
--

DROP TABLE IF EXISTS `entreprise_executant_sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entreprise_executant_sites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `entrepriseExecutantId` bigint unsigned NOT NULL,
  `siteId` bigint unsigned NOT NULL,
  `programmeId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entreprise_executant_sites_entrepriseexecutantid_foreign` (`entrepriseExecutantId`),
  KEY `entreprise_executant_sites_siteid_foreign` (`siteId`),
  KEY `entreprise_executant_sites_programmeid_foreign` (`programmeId`),
  CONSTRAINT `entreprise_executant_sites_entrepriseexecutantid_foreign` FOREIGN KEY (`entrepriseExecutantId`) REFERENCES `entreprise_executants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `entreprise_executant_sites_programmeid_foreign` FOREIGN KEY (`programmeId`) REFERENCES `programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `entreprise_executant_sites_siteid_foreign` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entreprise_executant_sites`
--

LOCK TABLES `entreprise_executant_sites` WRITE;
/*!40000 ALTER TABLE `entreprise_executant_sites` DISABLE KEYS */;
/*!40000 ALTER TABLE `entreprise_executant_sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `entreprise_executants`
--

DROP TABLE IF EXISTS `entreprise_executants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entreprise_executants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `userId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `entreprise_executants_userid_foreign` (`userId`),
  CONSTRAINT `entreprise_executants_userid_foreign` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `entreprise_executants`
--

LOCK TABLES `entreprise_executants` WRITE;
/*!40000 ALTER TABLE `entreprise_executants` DISABLE KEYS */;
INSERT INTO `entreprise_executants` VALUES (1,16,'2022-07-22 14:33:35','2022-07-22 14:33:35',NULL),(2,17,'2022-07-22 14:34:10','2022-07-22 14:34:10',NULL);
/*!40000 ALTER TABLE `entreprise_executants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fichiers`
--

DROP TABLE IF EXISTS `fichiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fichiers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chemin` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `source` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fichiertable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fichiertable_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fichiers`
--

LOCK TABLES `fichiers` WRITE;
/*!40000 ALTER TABLE `fichiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `fichiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gouvernements`
--

DROP TABLE IF EXISTS `gouvernements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gouvernements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `userId` bigint unsigned NOT NULL,
  `programmeId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gouvernements_userid_foreign` (`userId`),
  KEY `gouvernements_programmeid_foreign` (`programmeId`),
  CONSTRAINT `gouvernements_programmeid_foreign` FOREIGN KEY (`programmeId`) REFERENCES `programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `gouvernements_userid_foreign` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gouvernements`
--

LOCK TABLES `gouvernements` WRITE;
/*!40000 ALTER TABLE `gouvernements` DISABLE KEYS */;
/*!40000 ALTER TABLE `gouvernements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `indicateur_mods`
--

DROP TABLE IF EXISTS `indicateur_mods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `indicateur_mods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `anneeDeBase` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `valeurDeBase` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `frequence` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsable` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `definition` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modId` bigint unsigned NOT NULL,
  `uniteeMesureId` bigint unsigned NOT NULL,
  `categorieId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `indicateur_mods_modid_foreign` (`modId`),
  KEY `indicateur_mods_categorieid_foreign` (`categorieId`),
  CONSTRAINT `indicateur_mods_categorieid_foreign` FOREIGN KEY (`categorieId`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `indicateur_mods_modid_foreign` FOREIGN KEY (`modId`) REFERENCES `mods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `indicateur_mods`
--

LOCK TABLES `indicateur_mods` WRITE;
/*!40000 ALTER TABLE `indicateur_mods` DISABLE KEYS */;
/*!40000 ALTER TABLE `indicateur_mods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `indicateurs`
--

DROP TABLE IF EXISTS `indicateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `indicateurs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `anneeDeBase` int NOT NULL,
  `valeurDeBase` int NOT NULL,
  `bailleurId` bigint unsigned NOT NULL,
  `uniteeMesureId` bigint unsigned NOT NULL,
  `categorieId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `indicateurs_bailleurid_foreign` (`bailleurId`),
  KEY `indicateurs_categorieid_foreign` (`categorieId`),
  CONSTRAINT `indicateurs_bailleurid_foreign` FOREIGN KEY (`bailleurId`) REFERENCES `bailleurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `indicateurs_categorieid_foreign` FOREIGN KEY (`categorieId`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `indicateurs`
--

LOCK TABLES `indicateurs` WRITE;
/*!40000 ALTER TABLE `indicateurs` DISABLE KEYS */;
/*!40000 ALTER TABLE `indicateurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
INSERT INTO `jobs` VALUES (1,'default','{\"uuid\":\"6ef37d8a-14ec-4eaf-b8c7-6fe5d6fa4f15\",\"displayName\":\"App\\\\Jobs\\\\SendEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmailJob\",\"command\":\"O:21:\\\"App\\\\Jobs\\\\SendEmailJob\\\":14:{s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000user\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":4:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:12;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000type\\\";s:19:\\\"confirmation-compte\\\";s:29:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000mailer\\\";N;s:31:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000password\\\";s:8:\\\"743A32FA\\\";s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2022-07-22 15:05:35.035829\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}',0,NULL,1658502335,1658502290),(2,'default','{\"uuid\":\"3382b567-a70c-4fd2-9142-7cc9e9ab29b8\",\"displayName\":\"App\\\\Jobs\\\\SendEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmailJob\",\"command\":\"O:21:\\\"App\\\\Jobs\\\\SendEmailJob\\\":14:{s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000user\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":4:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:13;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000type\\\";s:19:\\\"confirmation-compte\\\";s:29:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000mailer\\\";N;s:31:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000password\\\";s:8:\\\"B68DB8C1\\\";s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2022-07-22 15:25:39.161168\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}',0,NULL,1658503539,1658503494),(3,'default','{\"uuid\":\"41ce92c1-5d7d-485f-b505-34899cb5b4d6\",\"displayName\":\"App\\\\Jobs\\\\SendEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmailJob\",\"command\":\"O:21:\\\"App\\\\Jobs\\\\SendEmailJob\\\":14:{s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000user\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":4:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:14;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000type\\\";s:19:\\\"confirmation-compte\\\";s:29:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000mailer\\\";N;s:31:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000password\\\";s:8:\\\"E340BEFB\\\";s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2022-07-22 15:33:04.238027\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}',0,NULL,1658503984,1658503939),(4,'default','{\"uuid\":\"0ee848c9-92d7-442d-b386-efa991c2479c\",\"displayName\":\"App\\\\Jobs\\\\SendEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmailJob\",\"command\":\"O:21:\\\"App\\\\Jobs\\\\SendEmailJob\\\":14:{s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000user\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":4:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:15;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000type\\\";s:19:\\\"confirmation-compte\\\";s:29:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000mailer\\\";N;s:31:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000password\\\";s:8:\\\"0EC8BBDF\\\";s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2022-07-22 15:33:50.247826\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}',0,NULL,1658504030,1658503985),(5,'default','{\"uuid\":\"37568380-3154-4644-9252-60fae2a8ba9b\",\"displayName\":\"App\\\\Jobs\\\\SendEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmailJob\",\"command\":\"O:21:\\\"App\\\\Jobs\\\\SendEmailJob\\\":14:{s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000user\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":4:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:16;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000type\\\";s:19:\\\"confirmation-compte\\\";s:29:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000mailer\\\";N;s:31:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000password\\\";s:8:\\\"FE2F01F1\\\";s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2022-07-22 15:34:20.719810\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}',0,NULL,1658504060,1658504015),(6,'default','{\"uuid\":\"fcc4d877-d4e3-4441-9d4a-fa8f022c97af\",\"displayName\":\"App\\\\Jobs\\\\SendEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmailJob\",\"command\":\"O:21:\\\"App\\\\Jobs\\\\SendEmailJob\\\":14:{s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000user\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":4:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:17;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000type\\\";s:19:\\\"confirmation-compte\\\";s:29:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000mailer\\\";N;s:31:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000password\\\";s:8:\\\"7FC45B05\\\";s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2022-07-22 15:34:55.865736\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}',0,NULL,1658504095,1658504050),(7,'default','{\"uuid\":\"6dbe36f8-54b2-4f06-a2d9-8ff14f07a963\",\"displayName\":\"App\\\\Jobs\\\\SendEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmailJob\",\"command\":\"O:21:\\\"App\\\\Jobs\\\\SendEmailJob\\\":14:{s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000user\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":4:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:18;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000type\\\";s:19:\\\"confirmation-compte\\\";s:29:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000mailer\\\";N;s:31:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000password\\\";s:8:\\\"3F0A4C20\\\";s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2022-07-22 15:35:33.525833\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}',0,NULL,1658504133,1658504088),(8,'default','{\"uuid\":\"77c5b0c3-a76f-48e2-9e5b-7e8571197dc0\",\"displayName\":\"App\\\\Jobs\\\\SendEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmailJob\",\"command\":\"O:21:\\\"App\\\\Jobs\\\\SendEmailJob\\\":14:{s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000user\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":4:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:19;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000type\\\";s:19:\\\"confirmation-compte\\\";s:29:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000mailer\\\";N;s:31:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000password\\\";s:8:\\\"75993377\\\";s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2022-07-22 15:36:17.882896\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}',0,NULL,1658504177,1658504132),(9,'default','{\"uuid\":\"99b26bff-5bc9-4d6c-bc66-905a08dbea75\",\"displayName\":\"App\\\\Jobs\\\\SendEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmailJob\",\"command\":\"O:21:\\\"App\\\\Jobs\\\\SendEmailJob\\\":14:{s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000user\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":4:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:20;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000type\\\";s:19:\\\"confirmation-compte\\\";s:29:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000mailer\\\";N;s:31:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000password\\\";s:8:\\\"832BBE92\\\";s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2022-07-22 15:36:56.686467\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}',0,NULL,1658504216,1658504171),(10,'default','{\"uuid\":\"f79e0e04-3d07-4e70-92cf-489ac8056353\",\"displayName\":\"App\\\\Jobs\\\\SendEmailJob\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"App\\\\Jobs\\\\SendEmailJob\",\"command\":\"O:21:\\\"App\\\\Jobs\\\\SendEmailJob\\\":14:{s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000user\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":4:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";i:21;s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";}s:27:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000type\\\";s:19:\\\"confirmation-compte\\\";s:29:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000mailer\\\";N;s:31:\\\"\\u0000App\\\\Jobs\\\\SendEmailJob\\u0000password\\\";s:8:\\\"EA5E4292\\\";s:3:\\\"job\\\";N;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;s:5:\\\"delay\\\";O:25:\\\"Illuminate\\\\Support\\\\Carbon\\\":3:{s:4:\\\"date\\\";s:26:\\\"2022-07-25 00:43:56.891700\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}}\"}}',0,NULL,1658709836,1658709792);
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `maitrise_oeuvres`
--

DROP TABLE IF EXISTS `maitrise_oeuvres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maitrise_oeuvres` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estimation` bigint NOT NULL,
  `engagement` bigint NOT NULL,
  `reference` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bailleurId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maitrise_oeuvres_bailleurid_foreign` (`bailleurId`),
  CONSTRAINT `maitrise_oeuvres_bailleurid_foreign` FOREIGN KEY (`bailleurId`) REFERENCES `bailleurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `maitrise_oeuvres`
--

LOCK TABLES `maitrise_oeuvres` WRITE;
/*!40000 ALTER TABLE `maitrise_oeuvres` DISABLE KEYS */;
/*!40000 ALTER TABLE `maitrise_oeuvres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_12_000000_create_users_table',1),(2,'2014_10_12_100000_create_password_resets_table',1),(3,'2019_08_19_000000_create_failed_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2022_05_09_124908_create_notifications_table',1),(6,'2022_05_10_140000_create_activity_log_table',1),(7,'2022_05_24_170642_create_activites_table',1),(8,'2022_05_24_170642_create_bailleurs_table',1),(9,'2022_05_24_170642_create_categories_table',1),(10,'2022_05_24_170642_create_check_list_com_table',1),(11,'2022_05_24_170642_create_check_lists_table',1),(12,'2022_05_24_170642_create_com_suivis_table',1),(13,'2022_05_24_170642_create_commentaires_table',1),(14,'2022_05_24_170642_create_composantes_table',1),(15,'2022_05_24_170642_create_e_activite_mods_table',1),(16,'2022_05_24_170642_create_e_activites_table',1),(17,'2022_05_24_170642_create_e_suivi_activite_mods_table',1),(18,'2022_05_24_170642_create_e_suivies_table',1),(19,'2022_05_24_170642_create_entreprise_executants_table',1),(20,'2022_05_24_170642_create_fichiers_table',1),(21,'2022_05_24_170642_create_indicateur_mods_table',1),(22,'2022_05_24_170642_create_indicateurs_table',1),(23,'2022_05_24_170642_create_maitrise_oeuvres_table',1),(24,'2022_05_24_170642_create_mission_de_controles_table',1),(25,'2022_05_24_170642_create_mods_table',1),(26,'2022_05_24_170642_create_nouvelle_proprietes_table',1),(27,'2022_05_24_170642_create_ong_com_table',1),(28,'2022_05_24_170642_create_passations_table',1),(29,'2022_05_24_170642_create_payes_table',1),(30,'2022_05_24_170642_create_permissions_table',1),(31,'2022_05_24_170642_create_plan_de_decaissements_table',1),(32,'2022_05_24_170642_create_programmes_table',1),(33,'2022_05_24_170642_create_projets_table',1),(34,'2022_05_24_170642_create_proprietes_table',1),(35,'2022_05_24_170642_create_roles_table',1),(36,'2022_05_24_170642_create_sinistres_table',1),(37,'2022_05_24_170642_create_sites_table',1),(38,'2022_05_24_170642_create_statuts_table',1),(39,'2022_05_24_170642_create_suivi_financier_mods_table',1),(40,'2022_05_24_170642_create_suivi_financiers_table',1),(41,'2022_05_24_170642_create_suivis_table',1),(42,'2022_05_24_170642_create_taches_table',1),(43,'2022_05_24_170642_create_unitee_de_gestions_table',1),(44,'2022_05_24_170642_create_unitees_table',1),(45,'2022_05_24_172637_create_structureables_table',1),(46,'2022_05_24_174141_create_role_permissions_table',1),(47,'2022_05_24_175947_create_unitee_de_gestion_users_table',1),(48,'2022_05_24_180719_create_mission_de_controle_users_table',1),(49,'2022_05_24_181043_create_bailleur_programmes_table',1),(50,'2022_05_24_181114_create_programme_users_table',1),(51,'2022_05_24_181310_create_mod_programmes_table',1),(52,'2022_05_24_181321_create_mission_de_controle_programmes_table',1),(53,'2022_05_24_181743_create_bailleur_entreprise_executants_table',1),(54,'2022_05_24_181802_create_entreprise_executant_mods_table',1),(55,'2022_05_24_182120_create_entreprise_executant_maitrise_oeuvres_table',1),(56,'2022_05_24_182316_create_entreprise_executant_e_activites_table',1),(57,'2022_05_24_182456_create_bailleur_sites_table',1),(58,'2022_05_24_182456_create_entreprise_executant_sites_table',1),(59,'2022_05_27_163353_create_role_users_table',1),(60,'2022_05_31_155526_decaissements',1),(61,'2022_05_34_170641_create_valeur_cible_d_indicateurs',1),(62,'2022_05_34_170642_create_suivi_indicateur_mods_table',1),(63,'2022_05_34_170642_create_suivi_indicateurs_table',1),(64,'2022_05_34_170652_create_foreign_keys',1),(65,'2022_06_08_151032_create_codes_table',1),(66,'2022_06_08_155812_create_durees_table',1),(67,'2022_06_09_091628_create_anos_table',1),(68,'2022_06_13_134521_create_type_anos_table',1),(69,'2022_06_20_134448_create_gouvernements_table',1),(70,'2022_06_20_135008_create_objectif_specifiques_table',1),(71,'2022_06_20_135236_create_resultats_table',1),(72,'2022_06_20_135527_create_cadre_logique_indicateurs_table',1),(73,'2022_06_20_181043_create_gouvernement_programmes_table',1),(74,'2022_07_13_181846_create_jobs_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mission_de_controle_programmes`
--

DROP TABLE IF EXISTS `mission_de_controle_programmes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mission_de_controle_programmes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `missionDeControleId` bigint unsigned NOT NULL,
  `programmeId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mission_de_controle_programmes_missiondecontroleid_foreign` (`missionDeControleId`),
  KEY `mission_de_controle_programmes_programmeid_foreign` (`programmeId`),
  CONSTRAINT `mission_de_controle_programmes_missiondecontroleid_foreign` FOREIGN KEY (`missionDeControleId`) REFERENCES `mission_de_controles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mission_de_controle_programmes_programmeid_foreign` FOREIGN KEY (`programmeId`) REFERENCES `programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mission_de_controle_programmes`
--

LOCK TABLES `mission_de_controle_programmes` WRITE;
/*!40000 ALTER TABLE `mission_de_controle_programmes` DISABLE KEYS */;
/*!40000 ALTER TABLE `mission_de_controle_programmes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mission_de_controle_users`
--

DROP TABLE IF EXISTS `mission_de_controle_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mission_de_controle_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `roleId` bigint unsigned NOT NULL,
  `userId` bigint unsigned NOT NULL,
  `missionDeControleId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mission_de_controle_users_userid_foreign` (`userId`),
  KEY `mission_de_controle_users_missiondecontroleid_foreign` (`missionDeControleId`),
  KEY `mission_de_controle_users_roleid_foreign` (`roleId`),
  CONSTRAINT `mission_de_controle_users_missiondecontroleid_foreign` FOREIGN KEY (`missionDeControleId`) REFERENCES `mission_de_controles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mission_de_controle_users_roleid_foreign` FOREIGN KEY (`roleId`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mission_de_controle_users_userid_foreign` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mission_de_controle_users`
--

LOCK TABLES `mission_de_controle_users` WRITE;
/*!40000 ALTER TABLE `mission_de_controle_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `mission_de_controle_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mission_de_controles`
--

DROP TABLE IF EXISTS `mission_de_controles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mission_de_controles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `programmeId` bigint unsigned NOT NULL,
  `userId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mission_de_controles`
--

LOCK TABLES `mission_de_controles` WRITE;
/*!40000 ALTER TABLE `mission_de_controles` DISABLE KEYS */;
INSERT INTO `mission_de_controles` VALUES (1,1,15,'2022-07-22 14:33:05','2022-07-22 14:33:05',NULL);
/*!40000 ALTER TABLE `mission_de_controles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mod_programmes`
--

DROP TABLE IF EXISTS `mod_programmes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mod_programmes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `modId` bigint unsigned NOT NULL,
  `programmeId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mod_programmes_modid_foreign` (`modId`),
  KEY `mod_programmes_programmeid_foreign` (`programmeId`),
  CONSTRAINT `mod_programmes_modid_foreign` FOREIGN KEY (`modId`) REFERENCES `mods` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mod_programmes_programmeid_foreign` FOREIGN KEY (`programmeId`) REFERENCES `programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mod_programmes`
--

LOCK TABLES `mod_programmes` WRITE;
/*!40000 ALTER TABLE `mod_programmes` DISABLE KEYS */;
INSERT INTO `mod_programmes` VALUES (1,1,1,NULL,NULL,NULL),(2,2,1,NULL,NULL,NULL);
/*!40000 ALTER TABLE `mod_programmes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mods`
--

DROP TABLE IF EXISTS `mods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `userId` bigint unsigned NOT NULL,
  `programmeId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mods_userid_foreign` (`userId`),
  KEY `mods_programmeid_foreign` (`programmeId`),
  CONSTRAINT `mods_programmeid_foreign` FOREIGN KEY (`programmeId`) REFERENCES `programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `mods_userid_foreign` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mods`
--

LOCK TABLES `mods` WRITE;
/*!40000 ALTER TABLE `mods` DISABLE KEYS */;
INSERT INTO `mods` VALUES (1,14,1,'2022-07-22 14:32:19','2022-07-22 14:32:19',NULL),(2,21,1,'2022-07-24 23:43:11','2022-07-24 23:43:11',NULL);
/*!40000 ALTER TABLE `mods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nouvelle_proprietes`
--

DROP TABLE IF EXISTS `nouvelle_proprietes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `nouvelle_proprietes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `longitude` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `proprieteId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `nouvelle_proprietes_proprieteid_foreign` (`proprieteId`),
  CONSTRAINT `nouvelle_proprietes_proprieteid_foreign` FOREIGN KEY (`proprieteId`) REFERENCES `proprietes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nouvelle_proprietes`
--

LOCK TABLES `nouvelle_proprietes` WRITE;
/*!40000 ALTER TABLE `nouvelle_proprietes` DISABLE KEYS */;
/*!40000 ALTER TABLE `nouvelle_proprietes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `objectif_specifiques`
--

DROP TABLE IF EXISTS `objectif_specifiques`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `objectif_specifiques` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `objectifable_id` bigint unsigned NOT NULL,
  `objectifable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `objectif_specifiques`
--

LOCK TABLES `objectif_specifiques` WRITE;
/*!40000 ALTER TABLE `objectif_specifiques` DISABLE KEYS */;
/*!40000 ALTER TABLE `objectif_specifiques` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ong_com`
--

DROP TABLE IF EXISTS `ong_com`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ong_com` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `userId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ong_com_userid_foreign` (`userId`),
  CONSTRAINT `ong_com_userid_foreign` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ong_com`
--

LOCK TABLES `ong_com` WRITE;
/*!40000 ALTER TABLE `ong_com` DISABLE KEYS */;
INSERT INTO `ong_com` VALUES (1,19,'2022-07-22 14:35:32','2022-07-22 14:35:32',NULL),(2,20,'2022-07-22 14:36:11','2022-07-22 14:36:11',NULL);
/*!40000 ALTER TABLE `ong_com` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `passations`
--

DROP TABLE IF EXISTS `passations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `passations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `montant` bigint NOT NULL,
  `dateDeSignature` date DEFAULT NULL,
  `dateDobtention` date DEFAULT NULL,
  `dateDeDemarrage` date DEFAULT NULL,
  `datePrevisionnel` date DEFAULT NULL,
  `dateDobtentionAvance` date DEFAULT NULL,
  `montantAvance` bigint NOT NULL,
  `ordreDeService` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsableSociologue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `estimation` bigint NOT NULL,
  `travaux` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `entrepriseExecutantId` bigint unsigned NOT NULL,
  `passationable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `siteId` bigint unsigned NOT NULL,
  `passationable_id` bigint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `passations_entrepriseexecutantid_foreign` (`entrepriseExecutantId`),
  KEY `passations_siteid_foreign` (`siteId`),
  CONSTRAINT `passations_entrepriseexecutantid_foreign` FOREIGN KEY (`entrepriseExecutantId`) REFERENCES `entreprise_executants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `passations_siteid_foreign` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `passations`
--

LOCK TABLES `passations` WRITE;
/*!40000 ALTER TABLE `passations` DISABLE KEYS */;
/*!40000 ALTER TABLE `passations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payes`
--

DROP TABLE IF EXISTS `payes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `montant` bigint NOT NULL,
  `proprieteId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payes_proprieteid_foreign` (`proprieteId`),
  CONSTRAINT `payes_proprieteid_foreign` FOREIGN KEY (`proprieteId`) REFERENCES `proprietes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payes`
--

LOCK TABLES `payes` WRITE;
/*!40000 ALTER TABLE `payes` DISABLE KEYS */;
/*!40000 ALTER TABLE `payes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` longtext COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (6,'App\\Models\\User',1,'ea9af7b0dad814ab','54912fb16a2929a7aa78c9ee9e7aee618067377475843e094c3426465dc78d48','[\"*\"]','2022-07-24 23:51:36','2022-07-24 23:51:31','2022-07-24 23:51:36'),(7,'App\\Models\\User',1,'1c66397c96318430','380d05539f45f6d5ea311aeba4c9e556feb5dcff865faf1f76e78b3e4005d4d4','[\"*\"]','2022-07-24 23:52:58','2022-07-24 23:52:20','2022-07-24 23:52:58');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plan_de_decaissements`
--

DROP TABLE IF EXISTS `plan_de_decaissements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plan_de_decaissements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trimestre` int NOT NULL,
  `annee` int NOT NULL,
  `pret` bigint NOT NULL,
  `budgetNational` bigint NOT NULL,
  `activiteId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plan_de_decaissements_activiteid_foreign` (`activiteId`),
  CONSTRAINT `plan_de_decaissements_activiteid_foreign` FOREIGN KEY (`activiteId`) REFERENCES `activites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plan_de_decaissements`
--

LOCK TABLES `plan_de_decaissements` WRITE;
/*!40000 ALTER TABLE `plan_de_decaissements` DISABLE KEYS */;
/*!40000 ALTER TABLE `plan_de_decaissements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programme_users`
--

DROP TABLE IF EXISTS `programme_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programme_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `userId` bigint unsigned NOT NULL,
  `programmeId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `programme_users_userid_foreign` (`userId`),
  KEY `programme_users_programmeid_foreign` (`programmeId`),
  CONSTRAINT `programme_users_programmeid_foreign` FOREIGN KEY (`programmeId`) REFERENCES `programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `programme_users_userid_foreign` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programme_users`
--

LOCK TABLES `programme_users` WRITE;
/*!40000 ALTER TABLE `programme_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `programme_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `programmes`
--

DROP TABLE IF EXISTS `programmes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `programmes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `budgetNational` bigint NOT NULL,
  `debut` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `fin` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `objectifGlobaux` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `programmes`
--

LOCK TABLES `programmes` WRITE;
/*!40000 ALTER TABLE `programmes` DISABLE KEYS */;
INSERT INTO `programmes` VALUES (1,'Programme d\'assainissement pluvial','1',12000000,'2022-07','2022-07','dfgdf','fgfg','2022-07-22 14:03:43','2022-07-22 14:03:55',NULL);
/*!40000 ALTER TABLE `programmes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projets`
--

DROP TABLE IF EXISTS `projets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `poids` int NOT NULL,
  `couleur` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `ville` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `objectifGlobaux` longtext COLLATE utf8mb4_unicode_ci,
  `pret` bigint NOT NULL,
  `budgetNational` bigint NOT NULL,
  `nombreEmploie` bigint DEFAULT NULL,
  `debut` date NOT NULL,
  `fin` date NOT NULL,
  `bailleurId` bigint unsigned NOT NULL,
  `programmeId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projets_bailleurid_foreign` (`bailleurId`),
  KEY `projets_programmeid_foreign` (`programmeId`),
  CONSTRAINT `projets_bailleurid_foreign` FOREIGN KEY (`bailleurId`) REFERENCES `bailleurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `projets_programmeid_foreign` FOREIGN KEY (`programmeId`) REFERENCES `programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projets`
--

LOCK TABLES `projets` WRITE;
/*!40000 ALTER TABLE `projets` DISABLE KEYS */;
/*!40000 ALTER TABLE `projets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `proprietes`
--

DROP TABLE IF EXISTS `proprietes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `proprietes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `longitude` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `montant` bigint NOT NULL,
  `annee` int NOT NULL,
  `sinistreId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proprietes_sinistreid_foreign` (`sinistreId`),
  CONSTRAINT `proprietes_sinistreid_foreign` FOREIGN KEY (`sinistreId`) REFERENCES `sinistres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `proprietes`
--

LOCK TABLES `proprietes` WRITE;
/*!40000 ALTER TABLE `proprietes` DISABLE KEYS */;
/*!40000 ALTER TABLE `proprietes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resultats`
--

DROP TABLE IF EXISTS `resultats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resultats` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `resultable_id` int NOT NULL,
  `resultable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resultats`
--

LOCK TABLES `resultats` WRITE;
/*!40000 ALTER TABLE `resultats` DISABLE KEYS */;
/*!40000 ALTER TABLE `resultats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `roleId` bigint unsigned NOT NULL,
  `permissionId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_permissions_roleid_foreign` (`roleId`),
  KEY `role_permissions_permissionid_foreign` (`permissionId`),
  CONSTRAINT `role_permissions_permissionid_foreign` FOREIGN KEY (`permissionId`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `role_permissions_roleid_foreign` FOREIGN KEY (`roleId`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_permissions`
--

LOCK TABLES `role_permissions` WRITE;
/*!40000 ALTER TABLE `role_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_users`
--

DROP TABLE IF EXISTS `role_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `roleId` bigint unsigned NOT NULL,
  `userId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_users_roleid_foreign` (`roleId`),
  KEY `role_users_userid_foreign` (`userId`),
  CONSTRAINT `role_users_roleid_foreign` FOREIGN KEY (`roleId`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `role_users_userid_foreign` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_users`
--

LOCK TABLES `role_users` WRITE;
/*!40000 ALTER TABLE `role_users` DISABLE KEYS */;
INSERT INTO `role_users` VALUES (1,1,1,NULL,NULL,NULL),(2,2,2,NULL,NULL,NULL),(3,3,3,NULL,NULL,NULL),(4,4,4,NULL,NULL,NULL),(5,5,5,NULL,NULL,NULL),(6,6,6,NULL,NULL,NULL),(7,7,7,NULL,NULL,NULL),(8,8,8,NULL,NULL,NULL),(9,9,9,NULL,NULL,NULL),(10,10,10,NULL,NULL,NULL),(11,11,11,NULL,NULL,NULL),(12,4,12,NULL,NULL,NULL),(13,2,13,NULL,NULL,NULL),(14,3,14,NULL,NULL,NULL),(15,5,15,NULL,NULL,NULL),(16,8,16,NULL,NULL,NULL),(17,8,17,NULL,NULL,NULL),(18,9,18,NULL,NULL,NULL),(19,6,19,NULL,NULL,NULL),(20,7,20,NULL,NULL,NULL),(21,3,21,NULL,NULL,NULL);
/*!40000 ALTER TABLE `role_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `roleable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `roleable_id` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrateur','administrateur','Administrateur',NULL,NULL,'2022-07-22 13:22:04','2022-07-22 13:22:04',NULL),(2,'Bailleur','bailleur','Bailleur',NULL,NULL,'2022-07-22 13:22:05','2022-07-22 13:22:05',NULL),(3,'MOD','mod','MOD',NULL,NULL,'2022-07-22 13:22:06','2022-07-22 13:22:06',NULL),(4,'Unitee de gestion','unitee-de-gestion','Unitee de gestion',NULL,NULL,'2022-07-22 13:22:06','2022-07-22 13:22:06',NULL),(5,'Mission de controle','mission-de-controle','Mission de controle',NULL,NULL,'2022-07-22 13:22:07','2022-07-22 13:22:07',NULL),(6,'ONG','ong','ONG',NULL,NULL,'2022-07-22 13:22:07','2022-07-22 13:22:07',NULL),(7,'AGENCE','agence','AGENCE',NULL,NULL,'2022-07-22 13:22:08','2022-07-22 13:22:08',NULL),(8,'Entreprise executant','entreprise-executant','Entreprise executant',NULL,NULL,'2022-07-22 13:22:08','2022-07-22 13:22:08',NULL),(9,'Entreprise et institution','institution','Entreprise et institution',NULL,NULL,'2022-07-22 13:22:10','2022-07-22 13:22:10',NULL),(10,'Comptable','comptable','Comptable',NULL,NULL,'2022-07-22 13:22:10','2022-07-22 13:22:10',NULL),(11,'Expert suivi évaluation','expert-suivi-evaluation','Expert suivi évaluation',NULL,NULL,'2022-07-22 13:22:10','2022-07-22 13:22:10',NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sinistres`
--

DROP TABLE IF EXISTS `sinistres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sinistres` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bailleurId` bigint unsigned NOT NULL,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenoms` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `annee` int unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sinistres_bailleurid_foreign` (`bailleurId`),
  CONSTRAINT `sinistres_bailleurid_foreign` FOREIGN KEY (`bailleurId`) REFERENCES `bailleurs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sinistres`
--

LOCK TABLES `sinistres` WRITE;
/*!40000 ALTER TABLE `sinistres` DISABLE KEYS */;
/*!40000 ALTER TABLE `sinistres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sites`
--

DROP TABLE IF EXISTS `sites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `longitude` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sites`
--

LOCK TABLES `sites` WRITE;
/*!40000 ALTER TABLE `sites` DISABLE KEYS */;
/*!40000 ALTER TABLE `sites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `statuts`
--

DROP TABLE IF EXISTS `statuts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `statuts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `etat` int NOT NULL,
  `statuttable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statuttable_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `statuts`
--

LOCK TABLES `statuts` WRITE;
/*!40000 ALTER TABLE `statuts` DISABLE KEYS */;
/*!40000 ALTER TABLE `statuts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suivi_financier_mods`
--

DROP TABLE IF EXISTS `suivi_financier_mods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suivi_financier_mods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trimestre` smallint NOT NULL,
  `annee` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `decaissement` bigint NOT NULL,
  `taux` int NOT NULL,
  `maitriseDoeuvreId` bigint unsigned NOT NULL,
  `siteId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `suivi_financier_mods_maitrisedoeuvreid_foreign` (`maitriseDoeuvreId`),
  KEY `suivi_financier_mods_siteid_foreign` (`siteId`),
  CONSTRAINT `suivi_financier_mods_maitrisedoeuvreid_foreign` FOREIGN KEY (`maitriseDoeuvreId`) REFERENCES `maitrise_oeuvres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `suivi_financier_mods_siteid_foreign` FOREIGN KEY (`siteId`) REFERENCES `sites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suivi_financier_mods`
--

LOCK TABLES `suivi_financier_mods` WRITE;
/*!40000 ALTER TABLE `suivi_financier_mods` DISABLE KEYS */;
/*!40000 ALTER TABLE `suivi_financier_mods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suivi_financiers`
--

DROP TABLE IF EXISTS `suivi_financiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suivi_financiers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `consommer` bigint NOT NULL,
  `trimestre` int NOT NULL,
  `suivi_financierable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `suivi_financierable_id` bigint unsigned NOT NULL,
  `activiteId` bigint unsigned NOT NULL,
  `annee` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `financierable` (`suivi_financierable_type`,`suivi_financierable_id`),
  KEY `suivi_financiers_activiteid_foreign` (`activiteId`),
  CONSTRAINT `suivi_financiers_activiteid_foreign` FOREIGN KEY (`activiteId`) REFERENCES `activites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suivi_financiers`
--

LOCK TABLES `suivi_financiers` WRITE;
/*!40000 ALTER TABLE `suivi_financiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `suivi_financiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suivi_indicateur_mods`
--

DROP TABLE IF EXISTS `suivi_indicateur_mods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suivi_indicateur_mods` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trimestre` int NOT NULL,
  `valeurRealise` json NOT NULL,
  `valeurCibleId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `suivi_indicateur_mods_valeurcibleid_foreign` (`valeurCibleId`),
  CONSTRAINT `suivi_indicateur_mods_valeurcibleid_foreign` FOREIGN KEY (`valeurCibleId`) REFERENCES `valeur_cible_d_indicateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suivi_indicateur_mods`
--

LOCK TABLES `suivi_indicateur_mods` WRITE;
/*!40000 ALTER TABLE `suivi_indicateur_mods` DISABLE KEYS */;
/*!40000 ALTER TABLE `suivi_indicateur_mods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suivi_indicateurs`
--

DROP TABLE IF EXISTS `suivi_indicateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suivi_indicateurs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `trimestre` int NOT NULL,
  `valeurRealise` json NOT NULL,
  `valeurCibleId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `suivi_indicateurs_valeurcibleid_foreign` (`valeurCibleId`),
  CONSTRAINT `suivi_indicateurs_valeurcibleid_foreign` FOREIGN KEY (`valeurCibleId`) REFERENCES `valeur_cible_d_indicateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suivi_indicateurs`
--

LOCK TABLES `suivi_indicateurs` WRITE;
/*!40000 ALTER TABLE `suivi_indicateurs` DISABLE KEYS */;
/*!40000 ALTER TABLE `suivi_indicateurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suivis`
--

DROP TABLE IF EXISTS `suivis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suivis` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `poidsActuel` int NOT NULL,
  `suivitable_id` int NOT NULL,
  `suivitable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suivis`
--

LOCK TABLES `suivis` WRITE;
/*!40000 ALTER TABLE `suivis` DISABLE KEYS */;
/*!40000 ALTER TABLE `suivis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taches`
--

DROP TABLE IF EXISTS `taches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `taches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int NOT NULL,
  `poids` int NOT NULL,
  `tepPrevu` int NOT NULL,
  `activiteId` bigint unsigned NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `taches_activiteid_foreign` (`activiteId`),
  CONSTRAINT `taches_activiteid_foreign` FOREIGN KEY (`activiteId`) REFERENCES `activites` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taches`
--

LOCK TABLES `taches` WRITE;
/*!40000 ALTER TABLE `taches` DISABLE KEYS */;
/*!40000 ALTER TABLE `taches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `type_anos`
--

DROP TABLE IF EXISTS `type_anos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `type_anos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `type_anos`
--

LOCK TABLES `type_anos` WRITE;
/*!40000 ALTER TABLE `type_anos` DISABLE KEYS */;
/*!40000 ALTER TABLE `type_anos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unitee_de_gestion_users`
--

DROP TABLE IF EXISTS `unitee_de_gestion_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unitee_de_gestion_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `roleId` bigint unsigned NOT NULL,
  `userId` bigint unsigned NOT NULL,
  `uniteDeGestionId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unitee_de_gestion_users_userid_foreign` (`userId`),
  KEY `unitee_de_gestion_users_unitedegestionid_foreign` (`uniteDeGestionId`),
  KEY `unitee_de_gestion_users_roleid_foreign` (`roleId`),
  CONSTRAINT `unitee_de_gestion_users_roleid_foreign` FOREIGN KEY (`roleId`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `unitee_de_gestion_users_unitedegestionid_foreign` FOREIGN KEY (`uniteDeGestionId`) REFERENCES `unitee_de_gestions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `unitee_de_gestion_users_userid_foreign` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unitee_de_gestion_users`
--

LOCK TABLES `unitee_de_gestion_users` WRITE;
/*!40000 ALTER TABLE `unitee_de_gestion_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `unitee_de_gestion_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unitee_de_gestions`
--

DROP TABLE IF EXISTS `unitee_de_gestions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unitee_de_gestions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `programmeId` bigint unsigned NOT NULL,
  `userId` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unitee_de_gestions_programmeid_foreign` (`programmeId`),
  KEY `unitee_de_gestions_userid_foreign` (`userId`),
  CONSTRAINT `unitee_de_gestions_programmeid_foreign` FOREIGN KEY (`programmeId`) REFERENCES `programmes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `unitee_de_gestions_userid_foreign` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unitee_de_gestions`
--

LOCK TABLES `unitee_de_gestions` WRITE;
/*!40000 ALTER TABLE `unitee_de_gestions` DISABLE KEYS */;
INSERT INTO `unitee_de_gestions` VALUES (1,'POOL PAPC',1,12,'2022-07-22 14:04:49','2022-07-22 14:04:49',NULL);
/*!40000 ALTER TABLE `unitee_de_gestions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unitees`
--

DROP TABLE IF EXISTS `unitees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unitees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unitees_nom_unique` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unitees`
--

LOCK TABLES `unitees` WRITE;
/*!40000 ALTER TABLE `unitees` DISABLE KEYS */;
INSERT INTO `unitees` VALUES (1,'Personne','2022-07-22 13:22:11','2022-07-22 13:22:11',NULL),(2,'Nombre','2022-07-22 13:22:11','2022-07-22 13:22:11',NULL),(3,'%','2022-07-22 13:22:11','2022-07-22 13:22:11',NULL),(4,'Km','2022-07-22 13:22:12','2022-07-22 13:22:12',NULL),(5,'Km2','2022-07-22 13:22:12','2022-07-22 13:22:12',NULL),(6,'ml','2022-07-22 13:22:12','2022-07-22 13:22:12',NULL),(7,'Millions de FCFA','2022-07-22 13:22:12','2022-07-22 13:22:12',NULL);
/*!40000 ALTER TABLE `unitees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `poste` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `emailVerifiedAt` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_contact_unique` (`contact`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'BOCOGA','Corine','corinebocog@gmail.com','$2y$10$8Olm43zN79XmqgIChA9Tr.lFRGKQiIcj361IIrwQFYzzRAW0OJhQi','62004867',NULL,'administrateur','2022-07-22 13:30:34',NULL,'2022-07-22 13:22:05','2022-07-22 14:19:16',NULL),(2,'CHRIS','Amour','chrisamour@gmail.com','$2y$10$J09VrJYCYGZne0t4SimFGu6cgYHZcXMTxU8b9HqFHCWtDhdfN.j5i','67214237',NULL,'bailleur',NULL,NULL,'2022-07-22 13:22:05','2022-07-22 13:22:05',NULL),(3,'GANDA','Luthe','gandaluthe@gmail.com','$2y$10$OhUgtFxEU/aX1ixugXoei.4L6jm.L20CiL7u0m9wonE74BKewtGYq','67001237',NULL,'mod',NULL,NULL,'2022-07-22 13:22:06','2022-07-22 13:22:06',NULL),(4,'OLOU','Yann','yannkelly@gmail.com','$2y$10$07N4sZTKeYNj8M1/Z.Geuu84rk6pw0D4tSJhVsrNhdWyq5SG/7NqO','67225437',NULL,'unitee-de-gestion',NULL,NULL,'2022-07-22 13:22:06','2022-07-22 13:22:06',NULL),(5,'GOJANDA','Jacob','gojanda.jacob@gmail.com','$2y$10$c2XL6PvmBX/liXePAmaH9.JvQ7a9euTjSeMBtzDMlIQ8by8qFrYIC','95612355',NULL,'mission-de-controle',NULL,NULL,'2022-07-22 13:22:07','2022-07-22 13:22:07',NULL),(6,'CAKPO','Firmin','cakpofirmin@gmail.com','$2y$10$pUlIgpxZUVc1W7t1vnuJ7OkEKorR6gWaMZVWPhbCuNlqVM.LDc4Cq','60204867',NULL,'ong',NULL,NULL,'2022-07-22 13:22:08','2022-07-22 13:22:08',NULL),(7,'DOMINGO','Isaac','gojandajacob@gmail.com','$2y$10$JE39l/mq5ZXtfJ6oFZ9CQu8DBDE1RnmELtPAfmmQ3FIatlySySLji','96512355',NULL,'agence',NULL,NULL,'2022-07-22 13:22:08','2022-07-22 13:22:08',NULL),(8,'LOTO','Fortune','lotofortune@gmail.com','$2y$10$bIf3tGWTeD2ldEd4EYtWJeLHXsxpMoE1R134jPtpy3iTnOrklb/tu','60701237',NULL,'entreprise-executant',NULL,NULL,'2022-07-22 13:22:09','2022-07-22 13:22:09',NULL),(9,'AFFO','Eric','ericaffo@gmail.com','$2y$10$dQiVQydfs9pfQGW9952TzO5kgD1Yhfmjr29b3CfendYGDDkYjy5kG','62725437',NULL,'institution',NULL,NULL,'2022-07-22 13:22:10','2022-07-22 14:34:20','2022-07-22 14:34:20'),(10,'AFFO','Eric','ericcaffo@gmail.com','$2y$10$VEOZf.tZECCp8MOcrHMNpe.ImCWYYAyvs3oYf7lC/dHdu8i./5GJe','62735437',NULL,'comptable',NULL,NULL,'2022-07-22 13:22:10','2022-07-22 13:22:10',NULL),(11,'LOTOs','Fortunes','lotoforstune@gmail.com','$2y$10$EahpkpRMYeoi.PX1lqHNz.m4uQ7H9Fj2sZdH9QXZyXdOUFEfNybBS','60771237',NULL,'expert-suivi-evaluation',NULL,NULL,'2022-07-22 13:22:11','2022-07-22 13:22:11',NULL),(12,'POOL PAPC',NULL,'poolpapc@gmail.com','$2y$10$DGhrp9qfxsxymVrWBezU8uyOkamex/JEvzEveFMwxUNpBVcnOWffi','98765400',NULL,'unitee-de-gestion','2022-07-22 14:24:15',NULL,'2022-07-22 14:04:49','2022-07-22 14:24:15',NULL),(13,'Banque mondiale',NULL,'bm@gmail.com','$2y$10$e7YJQ33kkJ9c3VDcJHS1c.1yaV1KR96LHbe.pkqZuh99HcB44NDOq','97650012',NULL,'bailleur',NULL,NULL,'2022-07-22 14:24:53','2022-07-22 14:24:53',NULL),(14,'AGETUR',NULL,'agetur@gmail.com','$2y$10$T8NfIXcPyuw2SjsvZJYeTeZ3FolC4hJDKWgS3Ashool5SWGbZPzmO','97234567',NULL,'mod',NULL,NULL,'2022-07-22 14:32:19','2022-07-22 14:32:19',NULL),(15,'Mission de controle',NULL,'mission-controle@gmail.com','$2y$10$Kn9P0llsdm67Qc80iLDGjO0Nb0KM6.8a1nZLD7rnh6kqF.tM6G1VG','97654312',NULL,'mission-de-controle',NULL,NULL,'2022-07-22 14:33:05','2022-07-22 14:33:05',NULL),(16,'SOGEA SATOM',NULL,'gff@d','$2y$10$cHqoJFCZTzOltJ6rGplnR.D46jsTOvJ4r8JBXHPmPrZDNBcQWdSly','98761245',NULL,'entreprise-executant',NULL,NULL,'2022-07-22 14:33:35','2022-07-22 14:33:35',NULL),(17,'HNRB',NULL,'hnrb@gmail.com','$2y$10$yXQfezk1XMVtCvBtY8FL9eogYE1WoeWdu3t6EZc.RuZW.78GHJGau','97541234',NULL,'entreprise-executant',NULL,NULL,'2022-07-22 14:34:10','2022-07-22 14:34:10',NULL),(18,'AZURE',NULL,'azure@gmail.com','$2y$10$7qtQiiYwhyvZ4TP8A/iZZehG0uowW9t5XmDUBaJbizbBiAY8JLoW.','97865432',NULL,'institution',NULL,NULL,'2022-07-22 14:34:48','2022-07-22 14:34:48',NULL),(19,'AZURE group',NULL,'cgf@xgfh.com','$2y$10$zScHvLpeP4CbKvhNmrqET.QykewQCNs6LXPuTbJ0EtFLmVj/RzVqK','98765412',NULL,'ong',NULL,NULL,'2022-07-22 14:35:32','2022-07-22 14:35:32',NULL),(20,'DIGI-COMM',NULL,'fgf@xfch.cghgf','$2y$10$oD/9XLlRCzoUDj8u6PZwFO6gmaMfTcGBdwwHIFg8vr9lm81ReQtBG','97651234',NULL,'agence',NULL,NULL,'2022-07-22 14:36:11','2022-07-22 14:36:11',NULL),(21,'MOD',NULL,'mod@gmail.com','$2y$10$gXOigifmU9hEHTfWQURDIup0GryD1Mbfcr1MjFrgl8VJtXBk7QpWK','94123456',NULL,'mod',NULL,NULL,'2022-07-24 23:43:11','2022-07-24 23:43:11',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `valeur_cible_d_indicateurs`
--

DROP TABLE IF EXISTS `valeur_cible_d_indicateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `valeur_cible_d_indicateurs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `annee` int NOT NULL,
  `valeurCible` json NOT NULL,
  `cibleable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cibleable_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `valeur_cible_d_indicateurs_cibleable_type_cibleable_id_index` (`cibleable_type`,`cibleable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `valeur_cible_d_indicateurs`
--

LOCK TABLES `valeur_cible_d_indicateurs` WRITE;
/*!40000 ALTER TABLE `valeur_cible_d_indicateurs` DISABLE KEYS */;
/*!40000 ALTER TABLE `valeur_cible_d_indicateurs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2022-08-01 10:53:34
