-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Anamakine: 127.0.0.1:3306
-- Üretim Zamanı: 02 Mar 2025, 14:49:10
-- Sunucu sürümü: 9.1.0
-- PHP Sürümü: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Veritabanı: `innomis`
--

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `authorities`
--

DROP TABLE IF EXISTS `authorities`;
CREATE TABLE IF NOT EXISTS `authorities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `student_number` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `authorities`
--

INSERT INTO `authorities` (`id`, `name`, `student_number`, `department`, `phone`, `email`, `role`, `password`, `image`, `created_at`) VALUES
(1, 'Oğuz Kaan Ekin', '22410051065', 'yönetim bilişim sistemleri', '05527063620', 'info.oguzkaan@gmail.com', 'admin', '$2y$10$k2vCC4EMzlNpntAkC.WiF.zDc2uajWyzwsTmbk8mBxZmJ0ALJHsF.', 'assets/img/authorities/20240925_221944.jpg', '2025-02-24 12:13:15'),
(3, 'Arda Özer', '234000000', 'yönetim bilişim sistemleri', '530 788 62 42', 'ardaozer@gmail.com', 'admin', '$2y$10$hrVq0gzCbu2gEJjFxldoRec4abAE0nN6TejMmvR2XgS6dqP6w0yrC', 'assets/img/authorities/WhatsApp Görsel 2024-11-21 saat 21.09.51_2d81f123.jpg', '2025-03-02 14:45:54');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `board_members`
--

DROP TABLE IF EXISTS `board_members`;
CREATE TABLE IF NOT EXISTS `board_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `student_number` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `position` enum('Başkan','Başkan Yardımcısı','Genel Direktör','Sosyal Medya Direktörü','İnsan Kaynakları ve Haberleşme Direktörü','Organizasyon ve Planlama Direktörü','Finans Direktörü','Genel Kurul Üyesi') NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `board_members`
--

INSERT INTO `board_members` (`id`, `name`, `student_number`, `department`, `phone`, `email`, `position`, `image`, `created_at`) VALUES
(1, 'Arda Özer', '23410051023', 'yönetim bilişim sistemleri', '5527063620', 'info.innomis@gmail.com', 'Başkan', 'assets/img/board/WhatsApp Görsel 2024-11-21 saat 21.09.51_2d81f123.jpg', '2025-02-24 12:53:13'),
(2, 'Deniz Arslan', '2342351005', 'yönetim bilişim sistemleri', '5527063620', 'readykaan@gmail.com', 'Finans Direktörü', 'assets/img/board/WhatsApp Görsel 2024-11-21 saat 21.18.44_600ec826.jpg', '2025-02-24 12:53:41'),
(3, 'furkan utkay demirdaşşak', '22410051065', 'yönetim bilişim sistemleri', '5527063620', 'deneme@gmail.com', 'Genel Direktör', 'assets/img/board/WhatsApp Görsel 2024-11-21 saat 21.08.26_84780332.jpg', '2025-02-24 12:54:16');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `device_stats`
--

DROP TABLE IF EXISTS `device_stats`;
CREATE TABLE IF NOT EXISTS `device_stats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `device_type` enum('desktop','mobile','tablet') DEFAULT NULL,
  `visitor_count` int DEFAULT '1',
  `visit_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `device_date` (`device_type`,`visit_date`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `device_stats`
--

INSERT INTO `device_stats` (`id`, `device_type`, `visitor_count`, `visit_date`) VALUES
(1, 'desktop', 13, '2025-02-24');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `location` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `events`
--

INSERT INTO `events` (`id`, `title`, `date`, `location`, `image`) VALUES
(28, 'tteetete', '2025-03-02 22:51:00', 'Taş Bina Kültür Merkezi', 'assets/img/gallery/IMG-20241206-WA0062.jpg'),
(22, 'Dijital Tasarım', '2025-01-05 19:09:00', 'Kapsul Teknoloji Platformu', 'assets/img/gallery/Ekran görüntüsü 2025-02-23 190939.png'),
(20, 'KAPSUL MIS CONNECT 25', '2025-02-16 19:04:00', 'Taş Bina Kültür Merkezi', 'assets/img/gallery/WhatsApp Görsel 2025-02-18 saat 13.42.04_ee6197c4.jpg'),
(21, 'Genel Kurul Toplantısı', '2025-02-24 19:07:00', 'Kapsul Teknoloji Platformu', 'assets/img/gallery/WhatsApp Görsel 2025-02-18 saat 13.45.09_a22b3447.jpg');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `finances`
--

DROP TABLE IF EXISTS `finances`;
CREATE TABLE IF NOT EXISTS `finances` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('income','expense') NOT NULL,
  `description` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  `category` varchar(100) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `receipt_no` varchar(100) DEFAULT NULL,
  `added_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `added_by` (`added_by`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `finances`
--

INSERT INTO `finances` (`id`, `type`, `description`, `amount`, `date`, `category`, `payment_method`, `receipt_no`, `added_by`) VALUES
(5, 'income', 'Armiya Sponsorluk Geliri', 1475.00, '2025-02-24 23:54:22', 'Etkinlik', 'Nakit', '12331231', 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `gallery`
--

DROP TABLE IF EXISTS `gallery`;
CREATE TABLE IF NOT EXISTS `gallery` (
  `id` int NOT NULL AUTO_INCREMENT,
  `image_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `upload_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `gallery`
--

INSERT INTO `gallery` (`id`, `image_name`, `category`, `uploaded_at`, `upload_date`, `title`, `description`) VALUES
(2, 'Ekran görüntüsü 2025-02-23 190939.png', 'etkinlik', '2025-02-23 17:54:58', '2025-02-24 19:55:40', '', NULL);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_notified` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `messages`
--

INSERT INTO `messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`, `is_notified`) VALUES
(3, 'latif can aykurt', 'latifcanaykurt@gmail.com', 'en büyük fener', 'fenerbahçe diker adamı', '2025-02-24 12:00:52', 1),
(4, 'latif can aykurt', 'latifcanaykurt@gmail.com', 'en büyük fener', 'fenerbahçe diker adamı', '2025-02-24 13:00:55', 1),
(5, 'Oğuz Kaan Ekin', 'oguz.931@hotmail.com', 'Konu: Dostware İçin MISCONNECT 2025 Etkinliğine Konuşmacı Daveti', 'dwqdqwd', '2025-02-24 19:08:06', 1),
(6, 'Oğuz Kaan Ekin', 'deneme@gmail.com', 'Konu: Dostware İçin MISCONNECT 2025 Etkinliğine Konuşmacı Daveti', 'ewqeqw', '2025-02-24 19:13:55', 1),
(7, 'Oğuz Kaan Ekin', 'deneme@gmail.com', 'Konu: Dostware İçin MISCONNECT 2025 Etkinliğine Konuşmacı Daveti', 'ewqeqw', '2025-02-24 19:17:39', 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `registrations`
--

DROP TABLE IF EXISTS `registrations`;
CREATE TABLE IF NOT EXISTS `registrations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullName` varchar(255) NOT NULL,
  `faculty` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `studentNumber` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `contactNumber` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `registrations`
--

INSERT INTO `registrations` (`id`, `fullName`, `faculty`, `class`, `studentNumber`, `department`, `address`, `contactNumber`, `email`, `created_at`) VALUES
(3, 'arda özer', 'ubf', '2', '21331321321', 'beyin cerrahi', 'mahmudiye mahallesi', '2133213213', 'info.innomis@gmail.com', '2025-02-23 17:54:20'),
(4, 'Havva İREM Çalışkan', 'ubf ybs', '4', '2145454128', 'ybs', 'konya aksaray', '05424515934', 'irem2@gmail.com', '2025-02-24 07:31:58');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `team_members`
--

DROP TABLE IF EXISTS `team_members`;
CREATE TABLE IF NOT EXISTS `team_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  `department` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
CREATE TABLE IF NOT EXISTS `user_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `session_start` datetime DEFAULT NULL,
  `session_end` datetime DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_start`, `session_end`, `duration`, `created_at`) VALUES
(10, 0, '2025-02-24 13:00:55', '2025-02-24 13:30:44', 1789, '2025-02-24 13:30:44'),
(9, 0, '2025-02-24 13:00:55', '2025-02-24 13:28:35', 1660, '2025-02-24 13:28:35'),
(8, 0, '2025-02-24 13:00:55', '2025-02-24 13:28:01', 1626, '2025-02-24 13:28:01'),
(7, 0, '2025-02-24 13:00:55', '2025-02-24 13:27:07', 1572, '2025-02-24 13:27:07'),
(6, 0, '2025-02-24 13:00:55', '2025-02-24 13:20:33', 1178, '2025-02-24 13:20:33'),
(11, 0, '2025-02-24 13:35:56', '2025-02-24 13:35:56', 0, '2025-02-24 13:35:56'),
(12, 0, '2025-02-24 13:35:56', '2025-02-24 13:37:41', 105, '2025-02-24 13:37:41'),
(13, 0, '2025-02-24 13:35:56', '2025-02-24 13:38:37', 161, '2025-02-24 13:38:37'),
(14, 0, '2025-02-24 13:35:56', '2025-02-24 13:38:47', 171, '2025-02-24 13:38:47'),
(15, 0, '2025-02-24 13:35:56', '2025-02-24 13:38:48', 172, '2025-02-24 13:38:48'),
(16, 0, '2025-02-24 13:35:56', '2025-02-24 13:38:48', 172, '2025-02-24 13:38:48'),
(17, 0, '2025-02-24 13:35:56', '2025-02-24 13:38:51', 175, '2025-02-24 13:38:51'),
(18, 0, '2025-02-24 13:35:56', '2025-02-24 13:38:56', 180, '2025-02-24 13:38:56'),
(19, 0, '2025-02-24 13:35:56', '2025-02-24 13:38:56', 180, '2025-02-24 13:38:56'),
(20, 0, '2025-02-24 13:35:56', '2025-02-24 13:39:00', 184, '2025-02-24 13:39:00'),
(21, 0, '2025-02-24 13:35:56', '2025-02-24 13:39:12', 196, '2025-02-24 13:39:12'),
(22, 0, '2025-02-24 13:35:56', '2025-02-24 13:39:12', 196, '2025-02-24 13:39:12'),
(23, 0, '2025-02-24 13:35:56', '2025-02-24 13:39:13', 197, '2025-02-24 13:39:13'),
(24, 0, '2025-02-24 13:35:56', '2025-02-24 13:39:20', 204, '2025-02-24 13:39:20'),
(25, 0, '2025-02-24 13:35:56', '2025-02-24 13:39:20', 204, '2025-02-24 13:39:20'),
(26, 0, '2025-02-24 13:35:56', '2025-02-24 13:40:02', 246, '2025-02-24 13:40:02');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `user_time_stats`
--

DROP TABLE IF EXISTS `user_time_stats`;
CREATE TABLE IF NOT EXISTS `user_time_stats` (
  `id` int NOT NULL AUTO_INCREMENT,
  `visit_date` date NOT NULL,
  `visit_time` time NOT NULL,
  `duration_minutes` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `user_time_stats`
--

INSERT INTO `user_time_stats` (`id`, `visit_date`, `visit_time`, `duration_minutes`, `created_at`) VALUES
(1, '2025-02-24', '19:38:28', 2, '2025-02-24 19:38:28'),
(2, '2025-02-24', '19:41:00', 3, '2025-02-24 19:41:00'),
(3, '2025-02-24', '19:51:44', 11, '2025-02-24 19:51:44'),
(4, '2025-02-24', '19:57:17', 6, '2025-02-24 19:57:17'),
(5, '2025-02-24', '19:59:17', 2, '2025-02-24 19:59:17'),
(6, '2025-02-24', '20:00:40', 1, '2025-02-24 20:00:40'),
(7, '2025-02-24', '20:04:31', 4, '2025-02-24 20:04:31'),
(8, '2025-02-24', '20:06:09', 2, '2025-02-24 20:06:09'),
(9, '2025-02-24', '20:07:33', 1, '2025-02-24 20:07:33'),
(10, '2025-02-24', '20:08:49', 1, '2025-02-24 20:08:49'),
(11, '2025-02-24', '20:13:36', 5, '2025-02-24 20:13:36'),
(12, '2025-02-24', '20:16:40', 2, '2025-02-24 20:16:40'),
(13, '2025-02-24', '20:18:03', 1, '2025-02-24 20:18:03'),
(14, '2025-02-24', '20:19:26', 1, '2025-02-24 20:19:26'),
(15, '2025-02-24', '20:20:30', 1, '2025-02-24 20:20:30'),
(16, '2025-02-24', '20:24:12', 4, '2025-02-24 20:24:12'),
(17, '2025-02-24', '20:25:26', 1, '2025-02-24 20:25:26'),
(18, '2025-02-24', '20:27:01', 2, '2025-02-24 20:27:01'),
(19, '2025-02-24', '20:29:43', 3, '2025-02-24 20:29:43'),
(20, '2025-02-24', '20:31:23', 2, '2025-02-24 20:31:23'),
(21, '2025-02-24', '20:32:26', 1, '2025-02-24 20:32:26'),
(22, '2025-02-24', '20:33:58', 2, '2025-02-24 20:33:58'),
(23, '2025-02-24', '20:37:15', 3, '2025-02-24 20:37:15'),
(24, '2025-02-24', '20:39:02', 2, '2025-02-24 20:39:02'),
(25, '2025-02-24', '20:43:07', 4, '2025-02-24 20:43:07'),
(26, '2025-02-24', '20:44:15', 1, '2025-02-24 20:44:15'),
(27, '2025-02-24', '20:45:36', 1, '2025-02-24 20:45:36'),
(28, '2025-02-24', '20:47:34', 2, '2025-02-24 20:47:34'),
(29, '2025-02-24', '20:50:24', 3, '2025-02-24 20:50:24'),
(30, '2025-02-24', '20:51:34', 1, '2025-02-24 20:51:34'),
(31, '2025-02-24', '21:02:36', 2, '2025-02-24 21:02:36'),
(32, '2025-02-24', '21:04:14', 2, '2025-02-24 21:04:14'),
(33, '2025-02-24', '21:08:44', 5, '2025-02-24 21:08:44'),
(34, '2025-02-24', '21:09:56', 1, '2025-02-24 21:09:56'),
(35, '2025-03-02', '14:49:02', 67, '2025-03-02 14:49:02');

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `visitors`
--

DROP TABLE IF EXISTS `visitors`;
CREATE TABLE IF NOT EXISTS `visitors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `visit_date` date DEFAULT NULL,
  `visitor_count` int DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Tablo döküm verisi `visitors`
--

INSERT INTO `visitors` (`id`, `visit_date`, `visitor_count`, `created_at`) VALUES
(1, '2025-02-24', 40, '2025-02-24 13:40:43');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
