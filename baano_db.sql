-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: baano_db
-- ------------------------------------------------------
-- Server version	8.0.46-0ubuntu0.22.04.3

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
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `listing_id` bigint unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `message` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bookings_user_id_foreign` (`user_id`),
  KEY `bookings_listing_id_foreign` (`listing_id`),
  CONSTRAINT `bookings_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Недвижимость','realty',NULL,'Аренда и продажа недвижимости','main','2026-06-30 22:11:04','2026-06-30 22:11:04'),(2,'Квартиры','apartments',1,'Аренда квартир','sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(3,'Посуточно и на длительный срок','apartments-long-term',2,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(4,'Дома и коттеджи','houses',2,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(5,'Для отдыха, мероприятий и проживания','vacation-rentals',2,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(6,'Коммерческая недвижимость','commercial',1,'Коммерческая недвижимость','sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(7,'Офисы','offices',6,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(8,'Склады','warehouses',6,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(9,'Торговые помещения','retail-spaces',6,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(10,'Гаражи и парковки','garages-parking',6,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(11,'Места для хранения и стоянки транспорта','storage-parking',6,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(12,'Банкетные залы','banquet-halls',6,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(13,'Конференц-залы','conference-halls',6,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(14,'Транспорт','transport',NULL,'Аренда транспорта','main','2026-06-30 22:11:04','2026-06-30 22:11:04'),(15,'Легковые автомобили','cars',14,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(16,'Мотоциклы','motorcycles',14,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(17,'Грузовой транспорт','trucks',14,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(18,'Спецтранспорт','special-vehicles',14,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(19,'Техника и оборудование','equipment',NULL,'Аренда техники и оборудования','main','2026-06-30 22:11:04','2026-06-30 22:11:04'),(20,'Строительная техника','construction-equipment',19,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(21,'Инструменты','tools',19,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(22,'Генераторы','generators',19,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(23,'Фото- и видеотехника','photo-video-equipment',19,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(24,'Услуги','services',NULL,'Различные услуги','main','2026-06-30 22:11:04','2026-06-30 22:11:04'),(25,'Ремонт и строительство','repair-construction',24,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(26,'Уборка помещений','cleaning',24,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(27,'Грузоперевозки','cargo-transport',24,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(28,'Красота и здоровье','beauty-health',24,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(29,'Обучение и репетиторство','education-tutoring',24,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04'),(30,'IT и дизайн','it-design',24,NULL,'sub','2026-06-30 22:11:04','2026-06-30 22:11:04');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `conversations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_one_id` bigint unsigned NOT NULL,
  `user_two_id` bigint unsigned NOT NULL,
  `last_message_id` bigint unsigned DEFAULT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conversations_user_one_id_user_two_id_unique` (`user_one_id`,`user_two_id`),
  KEY `conversations_user_two_id_foreign` (`user_two_id`),
  KEY `conversations_last_message_at_index` (`last_message_at`),
  KEY `conversations_last_message_id_foreign` (`last_message_id`),
  CONSTRAINT `conversations_last_message_id_foreign` FOREIGN KEY (`last_message_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL,
  CONSTRAINT `conversations_user_one_id_foreign` FOREIGN KEY (`user_one_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversations_user_two_id_foreign` FOREIGN KEY (`user_two_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `conversations`
--

LOCK TABLES `conversations` WRITE;
/*!40000 ALTER TABLE `conversations` DISABLE KEYS */;
INSERT INTO `conversations` VALUES (1,2,3,8,'2026-07-17 01:36:43','2026-07-17 01:27:06','2026-07-17 01:36:43'),(2,1,3,NULL,'2026-07-17 02:44:52','2026-07-17 02:24:30','2026-07-17 02:44:52');
/*!40000 ALTER TABLE `conversations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `favorites` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `favoritable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `favoritable_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `favorites_user_id_favoritable_type_favoritable_id_unique` (`user_id`,`favoritable_type`,`favoritable_id`),
  KEY `favorites_favoritable_type_favoritable_id_index` (`favoritable_type`,`favoritable_id`),
  CONSTRAINT `favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorites`
--

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
INSERT INTO `favorites` VALUES (1,2,'App\\Models\\Listing',11,'2026-07-16 05:33:09','2026-07-16 05:33:09'),(2,1,'App\\Models\\Listing',13,'2026-07-17 02:47:26','2026-07-17 02:47:26'),(3,12,'App\\Models\\Listing',11,'2026-07-18 08:38:38','2026-07-18 08:38:38'),(4,12,'App\\Models\\Listing',9,'2026-07-18 08:38:53','2026-07-18 08:38:53'),(5,12,'App\\Models\\Listing',8,'2026-07-18 08:38:55','2026-07-18 08:38:55');
/*!40000 ALTER TABLE `favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `listings`
--

DROP TABLE IF EXISTS `listings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `listings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `price_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `images` json DEFAULT NULL,
  `status` enum('draft','active','sold','inactive','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `listings_user_id_foreign` (`user_id`),
  KEY `listings_category_id_foreign` (`category_id`),
  CONSTRAINT `listings_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `listings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `listings`
--

LOCK TABLES `listings` WRITE;
/*!40000 ALTER TABLE `listings` DISABLE KEYS */;
INSERT INTO `listings` VALUES (3,1,22,'дизельный генератор на 5000 кВт','сдам на длительный срок, доставка и пуско-наладка за доп. плату',60000.00,'fixed',NULL,NULL,'active',1,'2026-06-30 22:36:17','2026-06-30 23:46:33'),(4,1,8,'складское помещение на 10 000м2','6 подъездных ворот, охрана 24/7',3000000.00,'fixed',NULL,NULL,'active',1,'2026-06-30 22:40:34','2026-06-30 23:46:36'),(5,1,7,'свободного назначения 35 м2','под ногтевой сервис, салон массажа или парикмахерскую, есть все оборудование',75000.00,'fixed',NULL,NULL,'active',1,'2026-07-01 01:11:47','2026-07-01 01:20:19'),(6,1,25,'бригада плиточников','укладка плитки, керамогранита, мраморные лестницы и фасады под ключ. цена 1 за м2',5000.00,'fixed',NULL,NULL,'active',1,'2026-07-01 01:13:19','2026-07-01 01:20:20'),(7,1,4,'загородный дом для большой компании','сдается коттедж, до 15 человек, есть вся мебель и посуда',35000.00,'fixed',NULL,NULL,'active',1,'2026-07-01 01:14:34','2026-07-01 01:20:22'),(8,1,3,'2-х комнатная квартира в центре города','сдам 2-х комнатную квартиру в центре города, транспортная развязка, рядом магазины, школа и дет. сад. есть паркинг за доп. плату',60000.00,'fixed',NULL,NULL,'active',1,'2026-07-01 01:15:59','2026-07-01 01:20:23'),(9,1,27,'услуги переезда','поможем вам с переездом, всегда опрятные грузчики, в удобное для вас время',2000.00,'fixed',NULL,NULL,'active',1,'2026-07-01 01:17:17','2026-07-01 01:20:25'),(10,1,26,'клиринг за копейки','услуги клиринга, уберем, помоем, постираем, погладим!',7500.00,'fixed',NULL,NULL,'active',1,'2026-07-01 01:18:20','2026-07-01 01:20:27'),(11,1,20,'спецтехника для обустройства участков','эксковаторы, самосвалы, манипуляторы, есть  все',0.00,'fixed',NULL,NULL,'active',1,'2026-07-01 01:20:03','2026-07-01 01:20:29'),(12,2,16,'мопеды в аренду','права не нужны',2000.00,'daily',NULL,NULL,'pending',1,'2026-07-16 06:19:41','2026-07-16 06:19:57'),(13,3,25,'Ремонт стиральных машин','Ремонтирую)',1000.00,'fixed',NULL,NULL,'pending',1,'2026-07-16 23:46:54','2026-07-17 01:09:38');
/*!40000 ALTER TABLE `listings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collection_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversions_disk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint unsigned NOT NULL,
  `manipulations` json NOT NULL,
  `custom_properties` json NOT NULL,
  `generated_conversions` json NOT NULL,
  `responsive_images` json NOT NULL,
  `order_column` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `media_order_column_index` (`order_column`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media`
--

LOCK TABLES `media` WRITE;
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
INSERT INTO `media` VALUES (15,'App\\Models\\Listing',12,'bcb7b9fb-ccc3-4c06-8058-11d65aaa779e','images','cau43nwNIjxcqifvPqJHduVr-cQNmxj3I_pzv1vik6NJtSuEX9Ci-48WpzDV6dZzVpCupSg7pYs9LlNiclmlnI3Fjsl9HTEApco6mNYyUyJp5-uZc24Xffx6QUO5TAFO47fT13wBYO1L4Mb3mIn4vWXeQB24q-J_Fiz-HcERALQ','cau43nwNIjxcqifvPqJHduVr-cQNmxj3I_pzv1vik6NJtSuEX9Ci-48WpzDV6dZzVpCupSg7pYs9LlNiclmlnI3Fjsl9HTEApco6mNYyUyJp5-uZc24Xffx6QUO5TAFO47fT13wBYO1L4Mb3mIn4vWXeQB24q-J_Fiz-HcERALQ.jpg','image/jpeg','public','public',68327,'[]','[]','{\"thumb\": true}','[]',1,'2026-07-16 06:19:41','2026-07-16 22:23:49'),(16,'App\\Models\\Listing',12,'e621ebf7-ad23-42ba-bca4-77c01110f10f','images','kak-arendovat-skuter-v-tailande','kak-arendovat-skuter-v-tailande.webp','image/webp','public','public',78014,'[]','[]','{\"thumb\": true}','[]',2,'2026-07-16 06:19:41','2026-07-16 22:23:49'),(17,'App\\Models\\Listing',3,'691cd2a0-1c28-403d-b307-fd7067f4c97e','images','5ofnRzrGy9aCQWhTqTP7XYGXf2lJyPGTefxHnU4kgx5e4V7hPZ_Am_uroFpBy1-x3c--Pvz6mBc1vqIT7iJLlbGTi945FaFtkaoEP4JUXfbqukrKttiA4Nwgk-jSFnzHOUqCQ8DBWxu0yV1zP1UQkr5mcS9Bc22z8LzyJDD9UDs','01KXNC8HJRJGYADDSHB1QP66JS.jpg','image/jpeg','public','public',92365,'[]','{\"custom_headers\": {\"ContentType\": \"image/jpeg\"}}','{\"thumb\": true}','[]',2,'2026-07-16 06:51:15','2026-07-16 22:23:49'),(18,'App\\Models\\Listing',3,'40bc0c8f-0bd1-4c25-be1a-ac7103fe461e','images','7Zt-GAMXLlzztY_WTjCDpCVKVlUqBsfuZSfBIvygTRJD0Dy91BmWXDtppCNiNWpUJPLFEnmi7JcTpziqh7eaSqEmRHaLD7hKq7dNkc_b2Z1I_i_AV2mQqxDRLvi0ygyPp6Rw5HzGbWfxDLzSFWrrg0RHIs09vo2enEbyZWZJHpM','01KXNC8HM2AMSXTJFM6010CTEV.jpg','image/jpeg','public','public',178924,'[]','{\"custom_headers\": {\"ContentType\": \"image/jpeg\"}}','{\"thumb\": true}','[]',3,'2026-07-16 06:51:15','2026-07-16 22:23:49'),(19,'App\\Models\\Listing',4,'e374e9db-dfa5-4d89-aa8c-e091daf17d4c','images','9','01KXNCB7CPJR2SC3QG99SPKB6C.jpg','image/jpeg','public','public',156481,'[]','{\"custom_headers\": {\"ContentType\": \"image/jpeg\"}}','{\"thumb\": true}','[]',2,'2026-07-16 06:52:43','2026-07-16 22:23:49'),(20,'App\\Models\\Listing',4,'6af456b8-bafb-4b6b-a860-a1c46229dd30','images','ChatGPT_Image_25_мар._2026_г.__20_35_44','01KXNCB7F0TA6DTS5458THE3NQ.png','image/png','public','public',1618508,'[]','{\"custom_headers\": {\"ContentType\": \"image/png\"}}','{\"thumb\": true}','[]',3,'2026-07-16 06:52:43','2026-07-16 22:23:49'),(22,'App\\Models\\Listing',5,'52ac71bb-ac86-46bd-9d86-a8afd2432d09','images','Без названия','01KXQ07C3EXGMWWBYNK65HSQE7.jpg','image/jpeg','public','public',9371,'[]','{\"custom_headers\": {\"ContentType\": \"image/jpeg\"}}','{\"thumb\": true}','[]',1,'2026-07-16 21:59:23','2026-07-16 22:23:49'),(24,'App\\Models\\Listing',6,'f7d2d7c4-5e25-440c-9e49-c88b0a5e2493','images','IutH29QrXNt6xPPiNy_zwfjnRWM1mSqxOY1HmAjLMR8wXnAkeCis_9gg3GS33hhtjsr5WvsHu9_1WSvzogLQQOHDkkDz1b_DQeDHJEI-Y7WtR_mnt6JlK531myq9vzYaqKEymRU02ZwM7cR4LpMC0p6x3BE0o7eI2CvPecXryXocRqA3hMu3wB9YptDBmGGS','01KXQ09123ME0AMJCE6NPEGJHM.jpg','image/jpeg','public','public',100939,'[]','{\"custom_headers\": {\"ContentType\": \"image/jpeg\"}}','{\"thumb\": true}','[]',1,'2026-07-16 22:00:17','2026-07-16 22:23:49'),(25,'App\\Models\\Listing',7,'9b54dd1f-9c82-4d3f-a2a3-b96455ac093a','images','unnamed-thumb','01KXQ0EW7HVN2AE1D9R1EDTYMR.jpg','image/jpeg','public','public',12451,'[]','{\"custom_headers\": {\"ContentType\": \"image/jpeg\"}}','{\"thumb\": true}','[]',1,'2026-07-16 22:03:29','2026-07-16 22:23:49'),(26,'App\\Models\\Listing',8,'8b76337b-c172-446e-bd46-6f5fbb94239e','images','3b1395e687fc3a32d9dd8c535e29d8527fbebe40','01KXQ2MMRV4PSZNSAM9TW83X0W.avif','image/avif','public','public',115792,'[]','{\"custom_headers\": {\"ContentType\": \"image/avif\"}}','{\"thumb\": true}','[]',1,'2026-07-16 22:41:35','2026-07-16 22:41:35'),(27,'App\\Models\\Listing',8,'c22ede33-3021-4ce9-9777-8fb6f7a77574','images','4b55621226408a0b30b18c50adc68f84983bf3a9','01KXQ2MMVMBHJNHDC7JBXFM16Q.avif','image/avif','public','public',58677,'[]','{\"custom_headers\": {\"ContentType\": \"image/avif\"}}','{\"thumb\": true}','[]',2,'2026-07-16 22:41:35','2026-07-16 22:41:35'),(28,'App\\Models\\Listing',8,'15b7050d-dbcd-4bd6-9c67-df97e5955fa0','images','4e36c6e1bbf63fff9947a10b856bde4ab38f7322','01KXQ2MMYDQ4ZH1ZPZ57XA0XXC.avif','image/avif','public','public',187022,'[]','{\"custom_headers\": {\"ContentType\": \"image/avif\"}}','{\"thumb\": true}','[]',3,'2026-07-16 22:41:35','2026-07-16 22:41:35'),(29,'App\\Models\\Listing',8,'2ed940ae-50c9-48f3-84e3-dfbfebca3bfd','images','41841a202dc760e48af79bdb5a30e9e74ed3df3e','01KXQ2MN1DAQ310JWA9R9PD27C.avif','image/avif','public','public',109493,'[]','{\"custom_headers\": {\"ContentType\": \"image/avif\"}}','{\"thumb\": true}','[]',4,'2026-07-16 22:41:35','2026-07-16 22:41:35'),(30,'App\\Models\\Listing',8,'d1d137d3-6015-45a1-89a8-0b1fe9a472c8','images','600070d9d140890565800c7d3ddb206679c1743d','01KXQ2MN4G209XB607A8BKDPN2.avif','image/avif','public','public',61937,'[]','{\"custom_headers\": {\"ContentType\": \"image/avif\"}}','{\"thumb\": true}','[]',5,'2026-07-16 22:41:35','2026-07-16 22:41:35'),(31,'App\\Models\\Listing',8,'07c85d62-f8e6-4123-af74-673c5ce00054','images','ba8ce7378b9967a1396b8e7e428868d3d26c0b8f','01KXQ2MN78XZYGH1XKR2D5FCMC.avif','image/avif','public','public',115126,'[]','{\"custom_headers\": {\"ContentType\": \"image/avif\"}}','{\"thumb\": true}','[]',6,'2026-07-16 22:41:35','2026-07-16 22:41:35'),(32,'App\\Models\\Listing',8,'96ad741d-a03b-481d-8259-eb9b23dbaeed','images','f2ca9e01d1e2416edf3109f520292a592be44174','01KXQ2MNACT7JN1MNNF3461PZS.avif','image/avif','public','public',78264,'[]','{\"custom_headers\": {\"ContentType\": \"image/avif\"}}','{\"thumb\": true}','[]',7,'2026-07-16 22:41:35','2026-07-16 22:41:36'),(33,'App\\Models\\Listing',9,'8afbcefe-c444-4d83-bf06-a9bf7dc5e4af','images','VCTNK7wR_kV6jBEEyZhyjzjOJMY30c-5rTeJiIiGiYniHxSc1HUGmEZB0PgsPOwt-pFrVOnKMl9ghs540iCww2cRlzN0nqRarsp0O9w1sbtIEAetlYCaNyQmkXwzrSrXF6Ti_yHjTkR4ahAq9EUkcW-CS1m7N7kanhOWJ1x78-k','01KXQ2N7ZB3RVJ4YB0Z43KFF3C.jpg','image/jpeg','public','public',136597,'[]','{\"custom_headers\": {\"ContentType\": \"image/jpeg\"}}','{\"thumb\": true}','[]',1,'2026-07-16 22:41:55','2026-07-16 22:41:55'),(34,'App\\Models\\Listing',10,'c3a783aa-5f7e-4c5b-b2eb-1246d8df6942','images','xnS8bLky8sL1m03j64ODJw5vVMBI5hnvI6QtgadDFeXGs5CS3bn9WQjEphzf8ZwaaWiUvmx6FDYSSq7s3fBSTmnZu8ky6e36-gGXSuVwCmEvb4smySnoQUWXViJ9gE97pxEtsSgtkPv47SzCQl3eLvYyFpLY0wYP8qWyX8YHn9g','01KXQ2NTZNSJQ78FNZ8HAK8T2R.jpg','image/jpeg','public','public',80775,'[]','{\"custom_headers\": {\"ContentType\": \"image/jpeg\"}}','{\"thumb\": true}','[]',1,'2026-07-16 22:42:14','2026-07-16 22:42:14'),(35,'App\\Models\\Listing',10,'cee5457d-e23d-4f60-9a95-d872021d2294','images','qp1qcgE7WwTbhmRDFlhPCGnjnJ5YfWJcn2D8rmI6LXjqP7ZLK056lohg7zNo5UM0IeTk6b1QU4BCIGzEYCyjwdgxmpcTJLM5s9qZIT6KkI5GJriaqJBgd-qWuwK70IO3QGh9bl0ilrp6C7j6ojKN9HixGlMK1xzmxwAgZb3yDL8','01KXQ2NV0ZHMVFNCNW6BVCSB6E.jpg','image/jpeg','public','public',47903,'[]','{\"custom_headers\": {\"ContentType\": \"image/jpeg\"}}','{\"thumb\": true}','[]',2,'2026-07-16 22:42:14','2026-07-16 22:42:14'),(36,'App\\Models\\Listing',11,'ce275247-6c77-47b8-ac71-3f0a7bb1bca6','images','TU567fL8cAuRHUO8jm5wmOBHtP787K1JSceMBncgfkF80tAdUgPt0c_yrm3D2kQIxCW8B2jvDMfVqWExaHn7SFD5a3qeUdHb99_B7KvI89dvdGdokj7a5HWJubSLsO-S694jW3u8XJp3y9BNH_dPuFcQgtHyNSZmRl-ZFyrB7hc','01KXQ2PJ8NSR310QYGD58AYF0K.jpg','image/jpeg','public','public',60559,'[]','{\"custom_headers\": {\"ContentType\": \"image/jpeg\"}}','{\"thumb\": true}','[]',1,'2026-07-16 22:42:38','2026-07-16 22:42:38'),(37,'App\\Models\\Listing',11,'c923317e-bda9-4a9b-9b1c-c19f98244736','images','ZqxRw9niR9NI5LoQcq53Kmj3tPoHy_n35pTWJXfYQoCZMRvuAH2jflxxgBB-5D0FTK7filVcUyLEV4XSTmBtKL_rrO_yItfbopIaD5ry4iLn0t3gyzDvW7AHnvA6Jwhc-FPLQW4lUSe4ghfe08kAlPBPLnHmpeUtciLSZa9Rx3g','01KXQ2PJ9K01CSVYYQR1HMQJN9.jpg','image/jpeg','public','public',80862,'[]','{\"custom_headers\": {\"ContentType\": \"image/jpeg\"}}','{\"thumb\": true}','[]',2,'2026-07-16 22:42:38','2026-07-16 22:42:38'),(39,'App\\Models\\Listing',13,'1691bf43-5997-421d-85bd-2814baa22453','images','IMG_2495','IMG_2495.webp','image/webp','public','public',81638,'[]','[]','{\"thumb\": true}','[]',1,'2026-07-17 01:51:17','2026-07-17 01:51:17');
/*!40000 ALTER TABLE `media` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint unsigned NOT NULL,
  `sender_id` bigint unsigned NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_conversation_id_created_at_index` (`conversation_id`,`created_at`),
  KEY `messages_sender_id_is_read_index` (`sender_id`,`is_read`),
  CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1,1,2,'собачка красивая..машина где?',1,'2026-07-17 01:27:26','2026-07-17 01:29:24'),(2,1,3,'Привет бро. Купи собачку)',1,'2026-07-17 01:29:47','2026-07-17 01:32:33'),(3,1,3,'Не удаляется фотка и новая не добавляется',1,'2026-07-17 01:31:04','2026-07-17 01:32:33'),(4,1,2,'j!',0,'2026-07-17 01:32:36','2026-07-17 01:32:36'),(5,1,2,'cjj,otybz hf,jnf.n',0,'2026-07-17 01:32:42','2026-07-17 01:32:42'),(6,1,2,'соообщения работают!',0,'2026-07-17 01:32:48','2026-07-17 01:32:48'),(7,1,2,'заебца',0,'2026-07-17 01:32:52','2026-07-17 01:32:52'),(8,1,2,'пробуй фотку менять',0,'2026-07-17 01:36:43','2026-07-17 01:36:43'),(9,2,1,'ты тут?',0,'2026-07-17 02:44:52','2026-07-17 02:44:52');
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_06_29_000001_create_users_table',1),(2,'2026_06_29_000002_create_password_reset_tokens_table',1),(3,'2026_06_29_000003_create_sessions_table',1),(4,'2026_06_29_000004_create_cache_table',1),(5,'2026_06_29_000005_create_jobs_table',1),(6,'2026_06_29_000006_create_categories_table',1),(7,'2026_06_29_000007_create_listings_table',1),(8,'2026_06_29_000008_create_bookings_table',1),(9,'2026_06_29_000009_create_reviews_table',1),(10,'2026_06_29_000010_create_favorites_table',1),(11,'2026_06_30_051948_create_media_table',1),(12,'2026_06_30_072030_add_is_active_to_reviews_table',1),(13,'2026_07_06_050619_add_location_to_listings_table',2),(14,'2026_07_06_081627_add_price_type_to_listings_table',2),(15,'2026_07_08_052236_add_role_to_users_table',2),(16,'2026_07_09_072558_create_conversations_table',3),(17,'2026_07_09_072604_create_messages_table',3),(18,'2026_07_09_093005_add_foreign_key_to_conversations_last_message',3),(19,'2020_10_04_115514_create_moonshine_roles_table',4),(20,'2020_10_05_173148_create_moonshine_tables',4),(21,'2026_07_15_111438_create_notifications_table',4),(22,'2026_07_16_000000_add_price_type_to_listings_table',5),(23,'2026_07_16_111843_add_price_type_to_listings_table',5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `moonshine_user_roles`
--

DROP TABLE IF EXISTS `moonshine_user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moonshine_user_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `moonshine_user_roles`
--

LOCK TABLES `moonshine_user_roles` WRITE;
/*!40000 ALTER TABLE `moonshine_user_roles` DISABLE KEYS */;
INSERT INTO `moonshine_user_roles` VALUES (1,'Admin','2026-07-15 06:14:46','2026-07-15 06:14:46');
/*!40000 ALTER TABLE `moonshine_user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `moonshine_users`
--

DROP TABLE IF EXISTS `moonshine_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `moonshine_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `moonshine_user_role_id` bigint unsigned NOT NULL DEFAULT '1',
  `email` varchar(190) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `moonshine_users_email_unique` (`email`),
  KEY `moonshine_users_moonshine_user_role_id_foreign` (`moonshine_user_role_id`),
  CONSTRAINT `moonshine_users_moonshine_user_role_id_foreign` FOREIGN KEY (`moonshine_user_role_id`) REFERENCES `moonshine_user_roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `moonshine_users`
--

LOCK TABLES `moonshine_users` WRITE;
/*!40000 ALTER TABLE `moonshine_users` DISABLE KEYS */;
INSERT INTO `moonshine_users` VALUES (1,1,'admin','$2y$12$xk1lmaAb.QBPbX19LEqk3OMWVQ.cuNtuM5IVPUUG2GfC51Duyj.Xy','admin',NULL,NULL,'2026-07-15 06:15:04','2026-07-15 06:15:04'),(2,1,'admin@baano.ru','$2y$12$TVyH2SVCkmpeTqndNj1vueH40qI9X2kqFbE/Sa5tMKto81HLL/wva','admin',NULL,NULL,'2026-07-15 06:15:44','2026-07-15 06:15:44');
/*!40000 ALTER TABLE `moonshine_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `listing_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `booking_id` bigint unsigned DEFAULT NULL,
  `rating` int NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_listing_id_foreign` (`listing_id`),
  KEY `reviews_user_id_foreign` (`user_id`),
  KEY `reviews_booking_id_foreign` (`booking_id`),
  CONSTRAINT `reviews_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL,
  CONSTRAINT `reviews_listing_id_foreign` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verification_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_verification_expires_at` timestamp NULL DEFAULT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `push_subscription` text COLLATE utf8mb4_unicode_ci,
  `balance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `is_subscribed` tinyint(1) NOT NULL DEFAULT '0',
  `subscription_expires_at` timestamp NULL DEFAULT NULL,
  `subscription_listings_limit` int NOT NULL DEFAULT '0',
  `subscription_listings_used` int NOT NULL DEFAULT '0',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_phone_unique` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'user','knaz2012@gmail.com','user','+79000000000','2026-06-30 22:11:04','2026-06-30 22:11:04',NULL,NULL,1,NULL,0.00,0,NULL,0,0,'$2y$12$ZMElfu2ZC2o/snr2JYkbm.v9pn0V46LlrMXi5snGMz5eS/cb5C/WK',NULL,'2026-06-30 22:11:04','2026-07-17 01:57:29'),(2,'Super Admin','admin@baano.ru','admin','666',NULL,'2026-07-15 05:58:15',NULL,NULL,1,NULL,0.00,0,NULL,0,0,'$2y$12$eU5e2IRvOUyu8VwVDgAeP.o19mGlGC6Z1yhN4JcMHiYc/os7D4oqC',NULL,'2026-06-30 22:56:32','2026-07-17 01:32:12'),(3,'Олег','ole2776@icloud.com','user','+79068088844','2026-07-16 23:45:01','2026-07-17 04:33:41','2315','2026-07-04 08:25:56',0,NULL,0.00,0,NULL,0,0,'$2y$12$7Jv/u0kxYS2gsvGdDKeQueqKIxal2FWxDsFgt8uekaClC3WHFwgti',NULL,'2026-07-04 08:15:56','2026-07-16 23:45:01'),(12,'Maxim','lokshin.maksim@gmail.com','user','+75645646545','2026-07-18 08:38:05',NULL,NULL,NULL,0,NULL,0.00,0,NULL,0,0,'$2y$12$ILpoNUNOyvJK0i0hj4ZNOeVbbG4xXV6DZGu.cuD5qhd0rYrW3VYUm',NULL,'2026-07-18 08:37:18','2026-07-18 08:38:05');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-19 16:55:36
