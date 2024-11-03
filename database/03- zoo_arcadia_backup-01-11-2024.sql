CREATE DATABASE  IF NOT EXISTS `zoo_arcadia` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `zoo_arcadia`;
-- MySQL dump 10.13  Distrib 8.0.20, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: zoo_arcadia
-- ------------------------------------------------------
-- Server version	8.0.20

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `animal`
--

DROP TABLE IF EXISTS `animal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `animal` (
  `id` int NOT NULL AUTO_INCREMENT,
  `prenom_animal` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `race_animal` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `etat_animal` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `habitat_id` int NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_6AAB231FAFFE2D26` (`habitat_id`),
  CONSTRAINT `FK_6AAB231FAFFE2D26` FOREIGN KEY (`habitat_id`) REFERENCES `habitat` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `animal`
--

LOCK TABLES `animal` WRITE;
/*!40000 ALTER TABLE `animal` DISABLE KEYS */;
INSERT INTO `animal` VALUES (1,'Leo','Panthera leo','En bonne santé','lion-2.jpg',1,'2024-10-19 14:01:18',NULL),(2,'Grace','Giraffa camelopardalis','En bonne santé','girafe.jpg',2,'2024-10-19 14:01:33',NULL),(3,'Dumbo','Loxodonta africana','En bonne santé','elephant.jpg',3,'2024-10-19 14:01:43',NULL),(4,'Rajah','Panthera tigris','En bonne santé','tigre.jpg',1,'2024-10-19 14:03:29',NULL),(5,'Coco','Macaca fascicularis','En bonne santé','singe-2.jpg',1,'2024-10-19 14:03:41',NULL),(6,'Perroquet Ara','Ara macao','En bonne santé','oiseaux-exotiques-2.jpg',2,'2024-10-19 14:03:50',NULL),(7,'Nile','Crocodylus niloticus','En bonne santé','crocodile-2.jpg',2,'2024-10-19 14:03:58',NULL),(8,'Shelly','Chelonia mydas','En bonne santé','tortue.jpg',3,'2024-10-19 14:04:05',NULL),(9,'Crocodile','Crocodylus niloticus','En bonne santé','crocodile.jpg',3,'2024-10-19 14:04:12',NULL),(10,'Lion5','Panthera leo5','Sain','lion5.jpg',1,'2024-10-26 15:28:24',NULL),(11,'Zèbre','Equus quagga','En bonne santé','zebre.jpg',1,'2024-10-26 21:49:40',NULL);
/*!40000 ALTER TABLE `animal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commentaire`
--

DROP TABLE IF EXISTS `commentaire`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commentaire` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contenu` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `animal_id` int NOT NULL,
  `auteur` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '(DC2Type:datetime_immutable)',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'En attente',
  PRIMARY KEY (`id`),
  KEY `IDX_67F068BC8E962C16` (`animal_id`),
  CONSTRAINT `FK_67F068BC8E962C16` FOREIGN KEY (`animal_id`) REFERENCES `animal` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commentaire`
--

LOCK TABLES `commentaire` WRITE;
/*!40000 ALTER TABLE `commentaire` DISABLE KEYS */;
INSERT INTO `commentaire` VALUES (1,'Le parc est magnifique, et les girafes sont impressionnantes à voir de près !',2,'Lucie Martin','2024-10-19 14:40:37','Approuvé'),(2,'J\'ai adoré la visite en petit train, les enfants étaient ravis de voir les lions.',1,'Paul Lefebvre','2024-10-19 14:40:46','En attente'),(3,'Les soigneurs sont très gentils et attentifs aux animaux. Les tigres étaient majestueux.',3,'Sophie Bernard','2024-10-19 14:40:54','Approuvé'),(4,'La jungle est un habitat vraiment incroyable, avec des animaux exotiques fascinants.',2,'Emilie Durand','2024-10-19 14:41:11','Approuvé'),(7,'C\'est un endroit magnifique !',1,'John Doe','2024-10-27 19:33:16','En attente');
/*!40000 ALTER TABLE `commentaire` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consultation`
--

DROP TABLE IF EXISTS `consultation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `consultation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `compteur` int NOT NULL,
  `id_animal` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consultation`
--

LOCK TABLES `consultation` WRITE;
/*!40000 ALTER TABLE `consultation` DISABLE KEYS */;
INSERT INTO `consultation` VALUES (1,5,'1'),(2,10,'2');
/*!40000 ALTER TABLE `consultation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `habitat`
--

DROP TABLE IF EXISTS `habitat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `habitat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_habitat` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_habitat` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `habitat`
--

LOCK TABLES `habitat` WRITE;
/*!40000 ALTER TABLE `habitat` DISABLE KEYS */;
INSERT INTO `habitat` VALUES (1,'Savane','Grande savane pour diverses espèces animales.','savane.jpg','2024-10-19 15:56:32',NULL),(2,'Jungle','Habitat de forêt tropicale, accueillant plusieurs espèces.','jungle.jpg','2024-10-19 15:56:32',NULL),(3,'Marais','Habitat de marais pour les animaux aquatiques.','marais.jpg','2024-10-19 15:56:32',NULL),(5,'test habitat','testttt22','Savane.jpg','2024-10-28 20:23:32','2024-10-30 10:12:46');
/*!40000 ALTER TABLE `habitat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `service`
--

DROP TABLE IF EXISTS `service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `service` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_service` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description_service` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `service`
--

LOCK TABLES `service` WRITE;
/*!40000 ALTER TABLE `service` DISABLE KEYS */;
INSERT INTO `service` VALUES (1,'Visite Guidée','Visite guidée complète avec des explications sur les animaux'),(2,'Restaurant','Un restaurant familial avec une vue magnifique sur les habitats des animaux.'),(3,'Visite Nocturne','Une visite exclusive de nuit pour découvrir la vie nocturne des animaux.');
/*!40000 ALTER TABLE `service` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  `api_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`),
  UNIQUE KEY `UNIQ_8D93D6497BA2F5EB` (`api_token`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (1,'lucie-lefevre@zooarcadia.fr','[\"ROLE_ADMIN\"]','$2y$13$VZF3prqPFS4KTtwRP/7oROSXzADab6rGRN2TmIXDuALuOU/07bgrK','2024-10-27 16:05:45','2024-11-01 11:28:16','0048ed77b00c504fdc96a647b8eb522a8c6d3b81','Lucie','Lefevre'),(6,'marie-dupont@zooarcadia.fr','[\"ROLE_ADMIN\"]','$2y$13$btmsPoLDBLBgJl3O8gpdwe6UTt7ZEv9nVDfRxhzCKOepmERcKMuYW','2024-10-25 15:33:54','2024-11-01 11:21:04','eb6e3bb398ed0835a9451b052d2c0f7930cf8b30','Marie','Dupont'),(7,'pierre-lefevre@zooarcadia.fr','[\"ROLE_EMPLOYE\"]','$2y$13$oYP4Oj5fEtO2z3Wf4Ak2leFe8j0nIC6h5in8MUUBZJ820Qr6WxvZy','2024-10-25 15:36:31','2024-11-01 11:21:41','4327f6fd6c351ef3bafe5efe5b67ee77fad6c283','Pierre','Lefevre'),(8,'sophie-martin@zooarcadia.fr','[\"ROLE_VETERINAIRE\"]','$2y$13$yMIEQRK.1Bi.a9BizYe/zOTzpyrUuCDOLekerYvOJQYrJut9.vfDq','2024-10-25 15:37:04','2024-11-01 11:21:57','ae30bba4658b73e60b80be63c7760a025a1f09a5','Sophie','Martin'),(10,'j-martin@zooarcadia.fr','[\"ROLE_EMPLOYE\"]','$2y$13$CdVUhmnAqrRs5BysSmAMfOApX/5sW0H9bTY9m4FEpdPF8e/WWx6oq','2024-11-01 11:37:33',NULL,'d0a1b13d074819224dd92f8f33e3d0ac09fcc37a','Martin','Jean'),(11,'c-robert@zooarcadia.fr','[\"ROLE_VETERINAIRE\"]','$2y$13$rzyr7xwHhRd7oOqXJ3Sod.Fycppc8yXsJejlQXQE1rWXax0eiIU86','2024-11-01 11:38:57',NULL,'94d948b81a5d021d1b16354720d54c89774f8527','Robert','Camille');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `veterinaire_rapport`
--

DROP TABLE IF EXISTS `veterinaire_rapport`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `veterinaire_rapport` (
  `id` int NOT NULL AUTO_INCREMENT,
  `etat_animal` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nourriture` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `grammage` int NOT NULL,
  `date_passage` datetime NOT NULL,
  `animal_id` int NOT NULL,
  `created_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` datetime DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`id`),
  KEY `IDX_C3B339768E962C16` (`animal_id`),
  CONSTRAINT `FK_C3B339768E962C16` FOREIGN KEY (`animal_id`) REFERENCES `animal` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `veterinaire_rapport`
--

LOCK TABLES `veterinaire_rapport` WRITE;
/*!40000 ALTER TABLE `veterinaire_rapport` DISABLE KEYS */;
INSERT INTO `veterinaire_rapport` VALUES (1,'En bonne santé','Viande',500,'2024-10-28 00:00:00',1,'2024-10-28 10:00:00','2024-10-28 10:00:00'),(2,'Malade','Poulet',540,'2024-10-28 14:51:09',2,'2024-10-28 10:00:57','2024-10-28 14:51:09'),(3,'Blessé à la patte','Viande crue',700,'2024-10-25 00:00:00',2,'2024-10-28 10:01:07','2024-10-28 10:01:07'),(4,'En convalescence','Fruits',150,'2024-11-05 00:00:00',3,'2024-10-28 10:01:17','2024-10-28 10:01:17'),(5,'Fatigué','Foin',300,'2024-10-24 00:00:00',3,'2024-10-28 10:01:24','2024-10-28 10:01:24'),(6,'En convalescence','Fruits',200,'2024-10-27 00:00:00',2,'2024-10-28 10:01:45','2024-10-28 10:01:45'),(7,'Malade','Viande de bœuf',800,'2024-10-28 12:52:19',1,'2024-10-28 10:01:59','2024-10-28 12:52:19'),(8,'En observation','Légumes verts',350,'2024-10-28 00:00:00',3,'2024-10-28 10:02:23','2024-10-28 10:02:23'),(9,'Affaibli','Légumes',200,'2024-11-10 00:00:00',1,'2024-10-28 10:02:32','2024-10-28 10:02:32'),(11,'Prend du poids','Poisson',350,'2024-11-08 00:00:00',2,'2024-10-28 10:02:48','2024-10-28 10:02:48'),(14,'En observation','Fruits',34,'2024-10-28 15:23:10',1,'2024-10-28 15:16:55','2024-10-28 15:23:10');
/*!40000 ALTER TABLE `veterinaire_rapport` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'zoo_arcadia'
--

--
-- Dumping routines for database 'zoo_arcadia'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-11-01 12:56:20
