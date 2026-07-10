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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorites`
--

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `listings`
--

LOCK TABLES `listings` WRITE;
/*!40000 ALTER TABLE `listings` DISABLE KEYS */;
INSERT INTO `listings` VALUES (3,1,22,'дизельный генератор на 5000 кВт','сдам на длительный срок, доставка и пуско-наладка за доп. плату',60000.00,NULL,NULL,'active',1,'2026-06-30 22:36:17','2026-06-30 23:46:33'),(4,1,8,'складское помещение на 10 000м2','6 подъездных ворот, охрана 24/7',3000000.00,NULL,NULL,'active',1,'2026-06-30 22:40:34','2026-06-30 23:46:36'),(5,1,7,'свободного назначения 35 м2','под ногтевой сервис, салон массажа или парикмахерскую, есть все оборудование',75000.00,NULL,NULL,'active',1,'2026-07-01 01:11:47','2026-07-01 01:20:19'),(6,1,25,'бригада плиточников','укладка плитки, керамогранита, мраморные лестницы и фасады под ключ. цена 1 за м2',5000.00,NULL,NULL,'active',1,'2026-07-01 01:13:19','2026-07-01 01:20:20'),(7,1,4,'загородный дом для большой компании','сдается коттедж, до 15 человек, есть вся мебель и посуда',35000.00,NULL,NULL,'active',1,'2026-07-01 01:14:34','2026-07-01 01:20:22'),(8,1,3,'2-х комнатная квартира в центре города','сдам 2-х комнатную квартиру в центре города, транспортная развязка, рядом магазины, школа и дет. сад. есть паркинг за доп. плату',60000.00,NULL,NULL,'active',1,'2026-07-01 01:15:59','2026-07-01 01:20:23'),(9,1,27,'услуги переезда','поможем вам с переездом, всегда опрятные грузчики, в удобное для вас время',2000.00,NULL,NULL,'active',1,'2026-07-01 01:17:17','2026-07-01 01:20:25'),(10,1,26,'клиринг за копейки','услуги клиринга, уберем, помоем, постираем, погладим!',7500.00,NULL,NULL,'active',1,'2026-07-01 01:18:20','2026-07-01 01:20:27'),(11,1,20,'спецтехника для обустройства участков','эксковаторы, самосвалы, манипуляторы, есть  все',0.00,NULL,NULL,'active',1,'2026-07-01 01:20:03','2026-07-01 01:20:29');
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `media`
--

LOCK TABLES `media` WRITE;
/*!40000 ALTER TABLE `media` DISABLE KEYS */;
INSERT INTO `media` VALUES (3,'App\\Models\\Listing',3,'88cb0369-88c6-4c43-90d5-310099c2d018','images','5ofnRzrGy9aCQWhTqTP7XYGXf2lJyPGTefxHnU4kgx5e4V7hPZ_Am_uroFpBy1-x3c--Pvz6mBc1vqIT7iJLlbGTi945FaFtkaoEP4JUXfbqukrKttiA4Nwgk-jSFnzHOUqCQ8DBWxu0yV1zP1UQkr5mcS9Bc22z8LzyJDD9UDs','5ofnRzrGy9aCQWhTqTP7XYGXf2lJyPGTefxHnU4kgx5e4V7hPZ_Am_uroFpBy1-x3c--Pvz6mBc1vqIT7iJLlbGTi945FaFtkaoEP4JUXfbqukrKttiA4Nwgk-jSFnzHOUqCQ8DBWxu0yV1zP1UQkr5mcS9Bc22z8LzyJDD9UDs.jpg','image/jpeg','public','public',92365,'[]','[]','{\"thumb\": true}','[]',1,'2026-06-30 22:36:17','2026-07-01 01:09:04'),(4,'App\\Models\\Listing',4,'dbafc0ba-7e5a-41c8-8fe0-27f6dffaa594','images','ChatGPT_Image_25_мар._2026_г.__20_35_44','ChatGPT_Image_25_мар._2026_г.__20_35_44.png','image/png','public','public',1618508,'[]','[]','{\"thumb\": true}','[]',1,'2026-06-30 22:40:34','2026-07-01 01:08:41'),(5,'App\\Models\\Listing',5,'bce6e710-ba4e-40c8-b6f3-7fd43bd70e8e','images','Без названия','Без-названия.jpg','image/jpeg','public','public',9371,'[]','[]','{\"thumb\": true}','[]',1,'2026-07-01 01:11:47','2026-07-01 01:11:47'),(6,'App\\Models\\Listing',6,'350d9b63-c66b-4ef3-a55f-2cc211760e54','images','IutH29QrXNt6xPPiNy_zwfjnRWM1mSqxOY1HmAjLMR8wXnAkeCis_9gg3GS33hhtjsr5WvsHu9_1WSvzogLQQOHDkkDz1b_DQeDHJEI-Y7WtR_mnt6JlK531myq9vzYaqKEymRU02ZwM7cR4LpMC0p6x3BE0o7eI2CvPecXryXocRqA3hMu3wB9YptDBmGGS','IutH29QrXNt6xPPiNy_zwfjnRWM1mSqxOY1HmAjLMR8wXnAkeCis_9gg3GS33hhtjsr5WvsHu9_1WSvzogLQQOHDkkDz1b_DQeDHJEI-Y7WtR_mnt6JlK531myq9vzYaqKEymRU02ZwM7cR4LpMC0p6x3BE0o7eI2CvPecXryXocRqA3hMu3wB9YptDBmGGS.jpg','image/jpeg','public','public',100939,'[]','[]','{\"thumb\": true}','[]',1,'2026-07-01 01:13:19','2026-07-01 01:13:19'),(7,'App\\Models\\Listing',7,'56e6418f-2c4c-428a-a8e3-a3c444f7ce2e','images','N11K9MUmSbiNavA1l9Y_1BoDaLECs1rxSzY6MZSmg79Llw0Z7t7Nb_fb3UfswSCoUKSYQIA6jFMloVaEfwuf85bCnQus1TzYuGSApDqqAKE5ZewP0wjKrGTFov9domWooqLyp-qKYM8DyDqlW93887sc5u9DG2VPyqu-OKqecvk','N11K9MUmSbiNavA1l9Y_1BoDaLECs1rxSzY6MZSmg79Llw0Z7t7Nb_fb3UfswSCoUKSYQIA6jFMloVaEfwuf85bCnQus1TzYuGSApDqqAKE5ZewP0wjKrGTFov9domWooqLyp-qKYM8DyDqlW93887sc5u9DG2VPyqu-OKqecvk.jpg','image/jpeg','public','public',25246,'[]','[]','{\"thumb\": true}','[]',1,'2026-07-01 01:14:34','2026-07-01 01:14:34'),(8,'App\\Models\\Listing',8,'3a6be958-1fa7-4e17-9ab5-1f24beae4ff4','images','756281448063005','756281448063005.jpg','image/jpeg','public','public',1935767,'[]','[]','{\"thumb\": true}','[]',1,'2026-07-01 01:15:59','2026-07-01 01:15:59'),(9,'App\\Models\\Listing',8,'7ea1d273-dc80-4ea0-bb53-e18f1b191169','images','unnamed (1)','unnamed-(1).jpg','image/jpeg','public','public',217824,'[]','[]','{\"thumb\": true}','[]',2,'2026-07-01 01:15:59','2026-07-01 01:15:59'),(10,'App\\Models\\Listing',8,'6c7a642f-3ee8-4242-a655-f782da975ed8','images','unnamed','unnamed.jpg','image/jpeg','public','public',273767,'[]','[]','{\"thumb\": true}','[]',3,'2026-07-01 01:15:59','2026-07-01 01:15:59'),(11,'App\\Models\\Listing',9,'c9ff9bfd-09e8-425a-a05a-10aad2464a55','images','VCTNK7wR_kV6jBEEyZhyjzjOJMY30c-5rTeJiIiGiYniHxSc1HUGmEZB0PgsPOwt-pFrVOnKMl9ghs540iCww2cRlzN0nqRarsp0O9w1sbtIEAetlYCaNyQmkXwzrSrXF6Ti_yHjTkR4ahAq9EUkcW-CS1m7N7kanhOWJ1x78-k','VCTNK7wR_kV6jBEEyZhyjzjOJMY30c-5rTeJiIiGiYniHxSc1HUGmEZB0PgsPOwt-pFrVOnKMl9ghs540iCww2cRlzN0nqRarsp0O9w1sbtIEAetlYCaNyQmkXwzrSrXF6Ti_yHjTkR4ahAq9EUkcW-CS1m7N7kanhOWJ1x78-k.jpg','image/jpeg','public','public',136597,'[]','[]','{\"thumb\": true}','[]',1,'2026-07-01 01:17:17','2026-07-01 01:17:17'),(12,'App\\Models\\Listing',10,'8bcead2f-1c85-4f0c-a216-cfef7d262013','images','qp1qcgE7WwTbhmRDFlhPCGnjnJ5YfWJcn2D8rmI6LXjqP7ZLK056lohg7zNo5UM0IeTk6b1QU4BCIGzEYCyjwdgxmpcTJLM5s9qZIT6KkI5GJriaqJBgd-qWuwK70IO3QGh9bl0ilrp6C7j6ojKN9HixGlMK1xzmxwAgZb3yDL8','qp1qcgE7WwTbhmRDFlhPCGnjnJ5YfWJcn2D8rmI6LXjqP7ZLK056lohg7zNo5UM0IeTk6b1QU4BCIGzEYCyjwdgxmpcTJLM5s9qZIT6KkI5GJriaqJBgd-qWuwK70IO3QGh9bl0ilrp6C7j6ojKN9HixGlMK1xzmxwAgZb3yDL8.jpg','image/jpeg','public','public',47903,'[]','[]','{\"thumb\": true}','[]',1,'2026-07-01 01:18:20','2026-07-01 01:18:20'),(13,'App\\Models\\Listing',10,'b321e7f1-c94e-4397-bff9-f724dfe8e236','images','service','service.jpg','image/jpeg','public','public',80775,'[]','[]','{\"thumb\": true}','[]',2,'2026-07-01 01:18:20','2026-07-01 01:18:20'),(14,'App\\Models\\Listing',11,'88d31aa7-9079-48e8-8d90-efc8bdc53cf9','images','TU567fL8cAuRHUO8jm5wmOBHtP787K1JSceMBncgfkF80tAdUgPt0c_yrm3D2kQIxCW8B2jvDMfVqWExaHn7SFD5a3qeUdHb99_B7KvI89dvdGdokj7a5HWJubSLsO-S694jW3u8XJp3y9BNH_dPuFcQgtHyNSZmRl-ZFyrB7hc','TU567fL8cAuRHUO8jm5wmOBHtP787K1JSceMBncgfkF80tAdUgPt0c_yrm3D2kQIxCW8B2jvDMfVqWExaHn7SFD5a3qeUdHb99_B7KvI89dvdGdokj7a5HWJubSLsO-S694jW3u8XJp3y9BNH_dPuFcQgtHyNSZmRl-ZFyrB7hc.jpg','image/jpeg','public','public',60559,'[]','[]','{\"thumb\": true}','[]',1,'2026-07-01 01:20:03','2026-07-01 01:20:03');
/*!40000 ALTER TABLE `media` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2026_06_29_000001_create_users_table',1),(2,'2026_06_29_000002_create_password_reset_tokens_table',1),(3,'2026_06_29_000003_create_sessions_table',1),(4,'2026_06_29_000004_create_cache_table',1),(5,'2026_06_29_000005_create_jobs_table',1),(6,'2026_06_29_000006_create_categories_table',1),(7,'2026_06_29_000007_create_listings_table',1),(8,'2026_06_29_000008_create_bookings_table',1),(9,'2026_06_29_000009_create_reviews_table',1),(10,'2026_06_29_000010_create_favorites_table',1),(11,'2026_06_30_051948_create_media_table',1),(12,'2026_06_30_072030_add_is_active_to_reviews_table',1),(13,'2026_07_06_050619_add_location_to_listings_table',2),(14,'2026_07_06_081627_add_price_type_to_listings_table',2),(15,'2026_07_08_052236_add_role_to_users_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin','admin@baano.local','user','+79000000000','2026-06-30 22:11:04','2026-06-30 22:11:04',NULL,NULL,1,NULL,0.00,0,NULL,0,0,'$2y$12$iveF2vuTVxF5GyOQnQrRMOtomv9Nlih91QWv8g1gspVaBHLrBZvQu',NULL,'2026-06-30 22:11:04','2026-06-30 22:11:04'),(2,'admin','admin@baano.ru','user',NULL,'2026-06-30 23:03:20',NULL,NULL,NULL,1,NULL,0.00,0,NULL,0,0,'$2y$12$xHRSVL0ESyLsH3mQNGSrg.bTb2Lb5eV3Uz59bUQ0eeuchg3edVsu6',NULL,'2026-06-30 22:56:32','2026-06-30 23:03:24'),(3,'Олег','ole2776@icloud.com','user','+79068088844',NULL,NULL,'2315','2026-07-04 08:25:56',0,NULL,0.00,0,NULL,0,0,'$2y$12$7Jv/u0kxYS2gsvGdDKeQueqKIxal2FWxDsFgt8uekaClC3WHFwgti',NULL,'2026-07-04 08:15:56','2026-07-04 08:15:56');
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

-- Dump completed on 2026-07-09 14:43:07
