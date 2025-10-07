-- Import-ready SQL for `tugaskominfo` (no CREATE DATABASE/USE)
-- Select your target database first in Adminer/phpMyAdmin, then run this script.

-- Session & safety settings
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';
SET NAMES utf8mb4;

-- Drop existing tables (order matters when FKs exist)
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `login_attempts`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `users`;

-- Create base tables (parent first to satisfy FKs)
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('superadmin','admin','operator','validator') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activity_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `login_attempts` (
  `attempt_id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `success` tinyint(1) DEFAULT NULL,
  `attempted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`attempt_id`),
  KEY `email` (`email`),
  KEY `attempted_at` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `settings` (
  `setting_id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`setting_id`),
  UNIQUE KEY `setting_key_unique` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data
INSERT INTO `users` (`user_id`, `email`, `password_hash`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(3, 'admin@example.com', '$2y$10$ryqVi8ELoa93Rhh7EnT76OfNPVBfTuX5xlu71TYgq2RJHYiqjxFx6', 'superadmin', 1, '2025-10-06 19:21:05', '2025-10-06 19:21:05'),
(4, 'diskominfo@gmail.com', '$2y$10$SQ5BXr5ARdcGGuTmgDq/Xu9N9aB0VuXoxKRuYVUwW3DGyB4t57Dqq', 'operator', 1, '2025-10-06 19:57:44', '2025-10-06 23:06:37'),
(5, 'tanahbumbu@gmail.com', '$2y$10$VuIw8PcjST0CwnhT6kM/Su5UXGjg2lg8i4wF2LFp.5UuoCCbRjWSi', 'validator', 1, '2025-10-06 21:52:10', '2025-10-06 23:10:25'),
(6, 'admin@gmail.com', '$2y$10$8ldpsTmUta51VK0N8J.QT.f0MnG5kPZotP1Bj8Poac/fvxwdU7KKW', 'admin', 1, '2025-10-06 23:04:25', '2025-10-06 23:04:25');

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `description`, `ip_address`, `created_at`) VALUES
(1, 3, 'login', 'User logged in successfully', '::1', '2025-10-06 19:29:07'),
(2, 3, 'create_user', 'Created user: diskominfo@gmail.com (ID: 4)', '::1', '2025-10-06 19:57:44'),
(3, 3, 'logout', 'User logged out', '::1', '2025-10-06 20:26:10'),
(4, 3, 'login', 'User logged in successfully', '::1', '2025-10-06 20:27:56'),
(5, 3, 'logout', 'User logged out', '::1', '2025-10-06 20:29:11'),
(6, 3, 'login', 'User logged in successfully', '::1', '2025-10-06 20:29:29'),
(7, 3, 'logout', 'User logged out', '::1', '2025-10-06 20:32:17'),
(8, 3, 'login', 'User logged in successfully', '::1', '2025-10-06 20:32:30'),
(9, 3, 'update_settings', 'Memperbarui pengaturan logo login', '::1', '2025-10-06 20:38:39'),
(10, 3, 'update_settings', 'Memperbarui pengaturan logo login', '::1', '2025-10-06 20:38:47'),
(11, 3, 'logout', 'User logged out', '::1', '2025-10-06 20:38:54'),
(12, 3, 'login', 'User logged in successfully', '::1', '2025-10-06 20:40:46'),
(13, 3, 'update_settings', 'Memperbarui pengaturan logo login', '::1', '2025-10-06 20:40:51'),
(14, 3, 'update_settings', 'Memperbarui pengaturan logo login', '::1', '2025-10-06 20:40:54'),
(15, 3, 'update_settings', 'Memperbarui pengaturan logo login', '::1', '2025-10-06 20:41:00'),
(16, 3, 'logout', 'User logged out', '::1', '2025-10-06 20:41:12'),
(17, 3, 'login', 'User logged in successfully', '::1', '2025-10-06 20:42:00'),
(18, 3, 'update_settings', 'Memperbarui pengaturan logo login', '::1', '2025-10-06 20:42:06'),
(19, 3, 'update_settings', 'Memperbarui pengaturan logo login', '::1', '2025-10-06 20:42:10'),
(20, 3, 'update_settings', 'Memperbarui pengaturan logo login', '::1', '2025-10-06 20:42:13'),
(21, 3, 'update_settings', 'Memperbarui pengaturan logo login', '::1', '2025-10-06 20:42:21'),
(22, 3, 'update_settings', 'Memperbarui pengaturan logo login', '::1', '2025-10-06 20:43:56'),
(23, 3, 'logout', 'User logged out', '::1', '2025-10-06 20:49:28'),
(24, 3, 'login', 'User logged in successfully', '::1', '2025-10-06 21:49:58'),
(25, 3, 'create_user', 'Membuat pengguna: tanahbumbu@gmail.com (ID: 5)', '::1', '2025-10-06 21:52:10'),
(26, 3, 'update_user', 'Memperbarui pengguna: diskominfo@gmail.com (ID: 4)', '::1', '2025-10-06 21:52:55'),
(27, 3, 'update_settings', 'Memperbarui pengaturan logo login', '::1', '2025-10-06 21:59:16'),
(28, 3, 'update_settings', 'Memperbarui pengaturan logo login', '::1', '2025-10-06 22:04:05'),
(29, 3, 'logout', 'User logged out', '::1', '2025-10-06 22:04:09'),
(30, 3, 'login', 'User logged in successfully', '::1', '2025-10-06 22:25:06'),
(31, 3, 'create_user', 'Membuat pengguna: admin@gmail.com (ID: 6)', '::1', '2025-10-06 23:04:25'),
(32, 3, 'update_user', 'Memperbarui pengguna: diskominfo@gmail.com (ID: 4)', '::1', '2025-10-06 23:05:21'),
(33, 3, 'update_user', 'Memperbarui pengguna: diskominfo@gmail.com (ID: 4)', '::1', '2025-10-06 23:05:28'),
(34, 3, 'logout', 'User logged out', '::1', '2025-10-06 23:05:36'),
(35, 3, 'login', 'User logged in successfully', '::1', '2025-10-06 23:06:13'),
(36, 3, 'update_user', 'Memperbarui pengguna: diskominfo@gmail.com (ID: 4)', '::1', '2025-10-06 23:06:37'),
(37, 3, 'logout', 'User logged out', '::1', '2025-10-06 23:06:46'),
(38, 4, 'login', 'User logged in successfully', '::1', '2025-10-06 23:07:11'),
(39, 4, 'update_user', 'Memperbarui pengguna: tanahbumbu@gmail.com (ID: 5)', '::1', '2025-10-06 23:07:58'),
(40, 4, 'logout', 'User logged out', '::1', '2025-10-06 23:08:02'),
(41, 5, 'login', 'User logged in successfully', '::1', '2025-10-06 23:08:21'),
(42, 5, 'logout', 'User logged out', '::1', '2025-10-06 23:08:39'),
(43, 6, 'login', 'User logged in successfully', '::1', '2025-10-06 23:08:48'),
(44, 6, 'update_user', 'Memperbarui pengguna: tanahbumbu@gmail.com (ID: 5)', '::1', '2025-10-06 23:10:17'),
(45, 6, 'update_user', 'Memperbarui pengguna: tanahbumbu@gmail.com (ID: 5)', '::1', '2025-10-06 23:10:25');

INSERT INTO `login_attempts` (`attempt_id`, `email`, `ip_address`, `success`, `attempted_at`) VALUES
(9, 'admin@example.com', '::1', 0, '2025-10-06 19:29:02'),
(10, 'admin@example.com', '::1', 1, '2025-10-06 19:29:07'),
(11, 'admin@example.com', '::1', 1, '2025-10-06 20:27:56'),
(12, 'admin@example.com', '::1', 1, '2025-10-06 20:29:29'),
(13, 'admin@example.com', '::1', 1, '2025-10-06 20:32:30'),
(14, 'admin@example.com', '::1', 1, '2025-10-06 20:40:46'),
(15, 'admin@example.com', '::1', 1, '2025-10-06 20:42:00'),
(16, 'admin@example.com', '::1', 1, '2025-10-06 21:49:58'),
(17, 'admin@example.com', '::1', 1, '2025-10-06 22:25:06'),
(18, 'diskominfo@gmail.com', '::1', 0, '2025-10-06 23:05:44'),
(19, 'diskominfo@gmail.com', '::1', 0, '2025-10-06 23:05:53'),
(20, 'admin@example.com', '::1', 0, '2025-10-06 23:06:05'),
(21, 'admin@example.com', '::1', 1, '2025-10-06 23:06:13'),
(22, 'diskominfo@gmail.com', '::1', 0, '2025-10-06 23:06:56'),
(23, 'diskominfo@gmail.com', '::1', 1, '2025-10-06 23:07:11'),
(24, 'tanahbumbu@gmail.com', '::1', 1, '2025-10-06 23:08:21'),
(25, 'admin@gmail.com', '::1', 1, '2025-10-06 23:08:48');

INSERT INTO `settings` (`setting_id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'login_logo', 'logo_1759759445.png', '2025-10-06 22:04:05');

-- Restore FK checks
SET foreign_key_checks = 1;

-- 2025-10-06 15:39:28
