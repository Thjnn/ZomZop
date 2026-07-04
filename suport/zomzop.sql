-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jul 04, 2026 at 07:37 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `zomzop`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_chat_sessions`
--

CREATE TABLE `ai_chat_sessions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `session_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `messages` json DEFAULT NULL COMMENT 'Sliding window tối đa 15 tin',
  `context` json DEFAULT NULL,
  `message_count` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `shift_id` bigint UNSIGNED NOT NULL,
  `check_in` timestamp NULL DEFAULT NULL,
  `check_out` timestamp NULL DEFAULT NULL,
  `method` enum('face','manual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'face',
  `face_confidence` decimal(5,2) DEFAULT NULL COMMENT 'Phần trăm độ chính xác nhận diện',
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `user_id`, `branch_id`, `shift_id`, `check_in`, `check_out`, `method`, `face_confidence`, `note`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 3, '2026-05-15 09:57:00', '2026-05-15 15:14:00', 'face', 0.88, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(2, 2, 1, 3, '2026-05-16 10:10:00', '2026-05-16 15:12:00', 'face', 0.94, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(3, 2, 1, 3, '2026-05-18 10:07:00', '2026-05-18 15:11:00', 'face', 0.95, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(4, 2, 1, 2, '2026-05-19 04:29:00', '2026-05-19 10:12:00', 'face', 0.92, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(5, 2, 1, 3, '2026-05-20 10:08:00', '2026-05-20 15:14:00', 'face', 0.92, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(6, 2, 1, 3, '2026-05-21 10:03:00', '2026-05-21 14:51:00', 'face', 0.95, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(7, 2, 1, 1, '2026-05-22 00:05:00', '2026-05-22 04:22:00', 'face', 0.98, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(8, 2, 1, 3, '2026-05-23 09:57:00', '2026-05-23 15:03:00', 'manual', NULL, 'Nhận diện thất bại, xác nhận thủ công', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(9, 2, 1, 2, '2026-05-25 04:30:00', '2026-05-25 10:01:00', 'face', 0.91, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(10, 2, 1, 1, '2026-05-26 00:05:00', '2026-05-26 04:50:00', 'manual', NULL, 'Nhận diện thất bại, xác nhận thủ công', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(11, 2, 1, 2, '2026-05-27 04:41:00', '2026-05-27 10:09:00', 'face', 0.94, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(12, 2, 1, 3, '2026-05-28 10:01:00', '2026-05-28 14:51:00', 'face', 0.93, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(13, 2, 1, 2, '2026-05-29 04:34:00', '2026-05-29 10:04:00', 'face', 0.97, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(14, 2, 1, 3, '2026-05-30 10:13:00', '2026-05-30 15:02:00', 'face', 0.94, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(15, 2, 1, 3, '2026-06-01 09:58:00', '2026-06-01 15:15:00', 'face', 0.85, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(16, 2, 1, 2, '2026-06-02 04:30:00', '2026-06-02 10:01:00', 'face', 0.88, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(17, 2, 1, 1, '2026-06-03 00:10:00', '2026-06-03 04:48:00', 'manual', NULL, 'Nhận diện thất bại, xác nhận thủ công', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(18, 2, 1, 1, '2026-06-03 23:55:00', '2026-06-04 04:31:00', 'face', 0.94, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(19, 2, 1, 3, '2026-06-05 10:11:00', '2026-06-05 15:20:00', 'face', 0.88, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(20, 2, 1, 1, '2026-06-06 00:12:00', '2026-06-06 04:33:00', 'face', 0.85, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(21, 2, 1, 3, '2026-06-08 10:12:00', '2026-06-08 15:10:00', 'face', 0.93, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(22, 2, 1, 3, '2026-06-09 10:06:00', '2026-06-09 15:12:00', 'face', 0.97, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(23, 2, 1, 1, '2026-06-09 23:59:00', '2026-06-10 04:32:00', 'face', 0.93, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(24, 2, 1, 3, '2026-06-11 10:08:00', '2026-06-11 14:57:00', 'face', 0.93, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(25, 2, 1, 3, '2026-06-12 10:06:00', '2026-06-12 14:50:00', 'face', 0.93, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(26, 2, 1, 1, '2026-06-12 23:59:00', '2026-06-13 04:35:00', 'face', 0.86, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(27, 4, 1, 2, '2026-05-15 04:28:00', '2026-05-15 10:04:00', 'face', 0.96, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(28, 4, 1, 2, '2026-05-16 04:27:00', '2026-05-16 10:20:00', 'face', 0.98, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(29, 4, 1, 3, '2026-05-18 10:03:00', '2026-05-18 15:16:00', 'face', 0.86, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(30, 4, 1, 3, '2026-05-19 09:59:00', '2026-05-19 15:02:00', 'face', 0.88, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(31, 4, 1, 1, '2026-05-20 00:09:00', '2026-05-20 04:20:00', 'face', 0.94, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(32, 4, 1, 2, '2026-05-21 04:33:00', '2026-05-21 10:16:00', 'face', 0.96, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(33, 4, 1, 3, '2026-05-22 10:10:00', '2026-05-22 15:12:00', 'face', 0.85, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(34, 4, 1, 2, '2026-05-23 04:36:00', '2026-05-23 10:18:00', 'face', 0.90, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(35, 4, 1, 1, '2026-05-25 00:06:00', '2026-05-25 04:41:00', 'face', 0.85, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(36, 4, 1, 2, '2026-05-26 04:35:00', '2026-05-26 09:55:00', 'face', 0.97, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(37, 4, 1, 2, '2026-05-27 04:42:00', '2026-05-27 10:20:00', 'face', 0.87, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(38, 4, 1, 2, '2026-05-28 04:43:00', '2026-05-28 09:59:00', 'face', 0.93, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(39, 4, 1, 3, '2026-05-29 09:58:00', '2026-05-29 15:20:00', 'face', 0.91, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(40, 4, 1, 3, '2026-05-30 09:55:00', '2026-05-30 15:19:00', 'face', 0.89, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(41, 4, 1, 2, '2026-06-01 04:29:00', '2026-06-01 10:08:00', 'face', 0.85, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(42, 4, 1, 3, '2026-06-02 10:12:00', '2026-06-02 14:56:00', 'face', 0.89, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(43, 4, 1, 1, '2026-06-02 23:59:00', '2026-06-03 04:34:00', 'manual', NULL, 'Nhận diện thất bại, xác nhận thủ công', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(44, 4, 1, 1, '2026-06-03 23:57:00', '2026-06-04 04:22:00', 'face', 0.95, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(45, 4, 1, 2, '2026-06-05 04:35:00', '2026-06-05 10:14:00', 'manual', NULL, 'Nhận diện thất bại, xác nhận thủ công', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(46, 4, 1, 3, '2026-06-06 10:12:00', '2026-06-06 14:50:00', 'face', 0.86, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(47, 4, 1, 3, '2026-06-08 10:10:00', '2026-06-08 14:50:00', 'face', 0.97, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(48, 4, 1, 3, '2026-06-09 10:12:00', '2026-06-09 15:20:00', 'face', 0.95, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(49, 4, 1, 1, '2026-06-10 00:04:00', '2026-06-10 04:39:00', 'face', 0.96, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(50, 4, 1, 3, '2026-06-11 10:09:00', '2026-06-11 14:52:00', 'face', 0.87, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(51, 4, 1, 2, '2026-06-12 04:40:00', '2026-06-12 10:14:00', 'face', 0.92, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(52, 4, 1, 2, '2026-06-13 04:25:00', '2026-06-13 09:52:00', 'face', 0.99, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(53, 5, 1, 3, '2026-05-15 09:59:00', '2026-05-15 15:02:00', 'face', 0.89, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(54, 5, 1, 3, '2026-05-16 10:00:00', '2026-05-16 14:52:00', 'manual', NULL, 'Nhận diện thất bại, xác nhận thủ công', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(55, 5, 1, 1, '2026-05-18 00:15:00', '2026-05-18 04:36:00', 'face', 0.97, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(56, 5, 1, 3, '2026-05-19 10:01:00', '2026-05-19 15:18:00', 'face', 0.95, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(57, 5, 1, 3, '2026-05-20 10:14:00', '2026-05-20 14:55:00', 'face', 0.93, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(58, 5, 1, 3, '2026-05-21 10:04:00', '2026-05-21 14:50:00', 'face', 0.85, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(59, 5, 1, 3, '2026-05-22 10:04:00', '2026-05-22 14:51:00', 'face', 0.92, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(60, 5, 1, 1, '2026-05-23 00:03:00', '2026-05-23 04:33:00', 'manual', NULL, 'Nhận diện thất bại, xác nhận thủ công', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(61, 5, 1, 3, '2026-05-25 09:57:00', '2026-05-25 15:00:00', 'face', 0.99, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(62, 5, 1, 1, '2026-05-26 00:03:00', '2026-05-26 04:29:00', 'face', 0.98, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(63, 5, 1, 1, '2026-05-27 00:06:00', '2026-05-27 04:45:00', 'face', 0.92, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(64, 5, 1, 1, '2026-05-28 00:13:00', '2026-05-28 04:22:00', 'face', 0.86, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(65, 5, 1, 3, '2026-05-29 10:12:00', '2026-05-29 14:53:00', 'face', 0.88, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(66, 5, 1, 2, '2026-05-30 04:26:00', '2026-05-30 10:06:00', 'face', 0.88, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(67, 5, 1, 2, '2026-06-01 04:33:00', '2026-06-01 10:19:00', 'manual', NULL, 'Nhận diện thất bại, xác nhận thủ công', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(68, 5, 1, 3, '2026-06-02 10:11:00', '2026-06-02 15:02:00', 'face', 0.98, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(69, 5, 1, 2, '2026-06-03 04:41:00', '2026-06-03 10:14:00', 'face', 0.93, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(70, 5, 1, 1, '2026-06-04 00:05:00', '2026-06-04 04:27:00', 'face', 0.87, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(71, 5, 1, 2, '2026-06-05 04:42:00', '2026-06-05 10:19:00', 'face', 0.90, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(72, 5, 1, 3, '2026-06-06 09:59:00', '2026-06-06 14:52:00', 'face', 0.98, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(73, 5, 1, 3, '2026-06-08 09:57:00', '2026-06-08 15:05:00', 'manual', NULL, 'Nhận diện thất bại, xác nhận thủ công', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(74, 5, 1, 1, '2026-06-08 23:57:00', '2026-06-09 04:44:00', 'face', 0.93, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(75, 5, 1, 3, '2026-06-10 09:57:00', '2026-06-10 15:20:00', 'face', 0.92, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(76, 5, 1, 2, '2026-06-11 04:31:00', '2026-06-11 10:14:00', 'face', 0.89, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(77, 5, 1, 1, '2026-06-12 00:07:00', '2026-06-12 04:20:00', 'manual', NULL, 'Nhận diện thất bại, xác nhận thủ công', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(78, 5, 1, 1, '2026-06-13 00:03:00', '2026-06-13 04:47:00', 'face', 0.93, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint UNSIGNED NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `started_at` timestamp NULL DEFAULT NULL,
  `ended_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `image`, `link`, `sort_order`, `is_active`, `started_at`, `ended_at`, `created_at`, `updated_at`) VALUES
(1, 'Ưu đãi mùa hè — Giảm 20% tất cả Pizza', 'banner-pizza.jpg', '/category/pizza', 1, 1, '2026-06-13 00:21:57', '2026-07-13 00:21:57', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(2, 'Burger mới ra mắt — Double Smash chỉ 65k', 'banner-burger.jpg', '/category/burger', 2, 1, '2026-06-13 00:21:57', '2026-08-13 00:21:57', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(3, 'Combo Gia Đình — Tiết kiệm hơn gọi lẻ', 'banner-combo.jpg', '/category/combo', 3, 1, '2026-06-13 00:21:57', '2026-08-13 00:21:57', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(4, 'Nhập WELCOME10 — Giảm 10% đơn đầu tiên', 'banner-coupon.jpg', '/menu', 4, 1, '2026-06-13 00:21:57', '2026-09-13 00:21:57', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(5, 'Gà Chiên Giòn — Giòn tan từng miếng', 'banner-ga-chien.jpg', '/category/ga-chien', 5, 1, '2026-06-13 00:21:57', '2026-07-13 00:21:57', '2026-06-13 00:21:57', '2026-06-13 00:21:57');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `open_time` time NOT NULL DEFAULT '08:00:00',
  `close_time` time NOT NULL DEFAULT '22:00:00',
  `slot_minutes` int UNSIGNED NOT NULL DEFAULT '15',
  `max_orders_per_slot` int UNSIGNED NOT NULL DEFAULT '10',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `address`, `phone`, `lat`, `lng`, `open_time`, `close_time`, `slot_minutes`, `max_orders_per_slot`, `is_active`, `image`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'ZomZop - Mỹ Tho 1', 'Số 1, Đường Tết Mậu Thân, P. Đạo Thạnh, Đồng Tháp', '0326313224', 10.3545220, 106.3590420, '07:00:00', '22:00:00', 15, 10, 1, 'anh-chi-nhanh-1.jpg', '2026-06-13 00:21:55', '2026-06-13 00:21:55', NULL),
(2, 'ZomZop - Bến Tre', 'Số 10, Đường Phan Văn Trị, Xã Giồng Trôm, Vĩnh Long', '0877790085', 10.1515736, 106.5118544, '07:30:00', '21:30:00', 15, 10, 1, 'anh-chi-nhanh-2.jpg', '2026-06-13 00:21:55', '2026-06-13 00:21:55', NULL),
(3, 'ZomZop - Mỹ Tho 2', 'Số 1, Đường Ấp Bắc, P. Đạo Thạnh, Đồng Tháp', '0326313224', 10.3618262, 106.3612578, '07:00:00', '22:00:00', 15, 10, 1, 'anh-chi-nhanh-3.jpg', '2026-06-13 00:21:55', '2026-06-13 00:21:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `branch_menu_items`
--

CREATE TABLE `branch_menu_items` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `menu_item_id` bigint UNSIGNED NOT NULL,
  `price` decimal(12,0) DEFAULT NULL COMMENT 'Override giá theo chi nhánh, null = dùng base_price',
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `stock_qty` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_menu_items`
--

INSERT INTO `branch_menu_items` (`id`, `branch_id`, `menu_item_id`, `price`, `is_available`, `stock_qty`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 1, 31, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(2, 1, 2, NULL, 1, 32, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(3, 1, 3, NULL, 1, 82, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(4, 1, 4, NULL, 1, 26, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(5, 1, 5, NULL, 1, 97, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(6, 1, 6, NULL, 1, 43, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(7, 1, 7, NULL, 1, 93, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(8, 1, 8, NULL, 1, 46, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(9, 1, 9, NULL, 0, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(10, 1, 10, NULL, 1, 80, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(11, 1, 11, NULL, 1, 32, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(12, 1, 12, NULL, 1, 80, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(13, 1, 13, NULL, 1, 50, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(14, 1, 14, NULL, 1, 30, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(15, 1, 15, NULL, 1, 43, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(16, 1, 16, NULL, 1, 45, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(17, 1, 17, NULL, 1, 32, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(18, 1, 18, NULL, 1, 43, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(19, 1, 19, NULL, 1, 86, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(20, 1, 20, NULL, 1, 66, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(21, 1, 21, NULL, 1, 51, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(22, 1, 22, NULL, 1, 27, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(23, 1, 23, NULL, 1, 97, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(24, 1, 24, NULL, 1, 96, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(25, 1, 25, NULL, 1, 40, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(26, 1, 26, NULL, 1, 77, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(27, 1, 27, NULL, 1, 43, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(28, 1, 28, NULL, 1, 69, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(29, 1, 29, NULL, 1, 25, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(30, 1, 30, NULL, 1, 22, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(31, 1, 31, NULL, 1, 77, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(32, 1, 32, NULL, 1, 74, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(33, 1, 33, NULL, 1, 100, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(34, 1, 34, NULL, 1, 27, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(35, 1, 35, NULL, 1, 65, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(36, 1, 36, NULL, 1, 54, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(37, 1, 37, NULL, 1, 53, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(38, 1, 38, NULL, 1, 48, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(39, 1, 39, NULL, 1, 35, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(40, 2, 1, 42000, 1, 60, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(41, 2, 2, 62000, 1, 93, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(42, 2, 3, NULL, 1, 56, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(43, 2, 4, NULL, 1, 85, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(44, 2, 5, NULL, 1, 43, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(45, 2, 6, NULL, 1, 51, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(46, 2, 7, 85000, 1, 69, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(47, 2, 8, 95000, 1, 100, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(48, 2, 9, 105000, 1, 100, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(49, 2, 10, 90000, 1, 59, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(50, 2, 11, NULL, 1, 35, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(51, 2, 12, NULL, 1, 40, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(52, 2, 13, NULL, 1, 28, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(53, 2, 14, NULL, 1, 99, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(54, 2, 15, NULL, 1, 97, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(55, 2, 16, NULL, 1, 85, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(56, 2, 17, NULL, 1, 97, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(57, 2, 18, NULL, 1, 43, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(58, 2, 19, NULL, 1, 95, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(59, 2, 20, NULL, 1, 42, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(60, 2, 21, NULL, 1, 99, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(61, 2, 22, NULL, 1, 99, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(62, 2, 23, NULL, 1, 47, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(63, 2, 24, NULL, 1, 67, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(64, 2, 25, NULL, 1, 20, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(65, 2, 26, NULL, 1, 67, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(66, 2, 27, NULL, 1, 76, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(67, 2, 28, NULL, 1, 36, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(68, 2, 29, NULL, 1, 77, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(69, 2, 30, NULL, 1, 33, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(70, 2, 31, NULL, 1, 49, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(71, 2, 32, NULL, 1, 89, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(72, 2, 33, NULL, 1, 25, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(73, 2, 34, NULL, 1, 34, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(74, 2, 35, NULL, 1, 56, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(75, 2, 36, NULL, 1, 22, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(76, 2, 37, NULL, 1, 50, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(77, 2, 38, NULL, 1, 75, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(78, 2, 39, NULL, 0, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(79, 3, 1, NULL, 1, 70, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(80, 3, 2, NULL, 1, 94, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(81, 3, 3, NULL, 1, 72, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(82, 3, 4, NULL, 1, 83, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(83, 3, 5, NULL, 1, 91, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(84, 3, 6, NULL, 1, 20, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(85, 3, 7, NULL, 1, 20, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(86, 3, 8, NULL, 1, 73, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(87, 3, 9, NULL, 1, 73, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(88, 3, 10, NULL, 1, 21, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(89, 3, 11, NULL, 1, 63, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(90, 3, 12, NULL, 1, 51, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(91, 3, 13, NULL, 1, 38, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(92, 3, 14, NULL, 1, 58, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(93, 3, 15, NULL, 1, 23, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(94, 3, 16, NULL, 1, 28, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(95, 3, 17, NULL, 1, 93, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(96, 3, 18, NULL, 1, 56, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(97, 3, 19, NULL, 1, 61, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(98, 3, 20, NULL, 1, 76, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(99, 3, 21, NULL, 1, 94, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(100, 3, 22, NULL, 1, 97, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(101, 3, 23, NULL, 1, 38, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(102, 3, 24, NULL, 1, 77, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(103, 3, 25, NULL, 1, 52, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(104, 3, 26, NULL, 1, 62, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(105, 3, 27, NULL, 1, 75, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(106, 3, 28, NULL, 1, 94, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(107, 3, 29, NULL, 1, 76, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(108, 3, 30, NULL, 1, 80, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(109, 3, 31, NULL, 1, 70, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(110, 3, 32, NULL, 1, 23, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(111, 3, 33, NULL, 1, 70, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(112, 3, 34, NULL, 1, 40, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(113, 3, 35, NULL, 1, 93, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(114, 3, 36, NULL, 1, 28, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(115, 3, 37, NULL, 1, 29, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(116, 3, 38, NULL, 1, 99, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(117, 3, 39, NULL, 1, 26, '2026-06-13 00:21:56', '2026-06-13 00:21:56');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Burger', 'burger', 'burger.jpg', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(2, 'Pizza', 'pizza', 'pizza.jpg', 2, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(3, 'Mỳ Ý', 'my-y', 'my-y.png', 3, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(4, 'Sandwich', 'sandwich', 'sandwich.jpg', 4, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(5, 'Gà Chiên', 'ga-chien', 'ga-chien.jpg', 5, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(6, 'Sides', 'sides', 'sides.jpg', 6, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(7, 'Đồ uống', 'do-uong', 'do-uong.jpg', 7, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(8, 'Combo', 'combo', 'combo.jpg', 8, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('percent','fixed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(12,0) NOT NULL,
  `min_order_value` decimal(12,0) NOT NULL DEFAULT '0',
  `max_uses` int UNSIGNED NOT NULL DEFAULT '0' COMMENT '0 = không giới hạn',
  `used_count` int UNSIGNED NOT NULL DEFAULT '0',
  `max_uses_per_user` int UNSIGNED NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `started_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `value`, `min_order_value`, `max_uses`, `used_count`, `max_uses_per_user`, `is_active`, `started_at`, `expired_at`, `created_at`, `updated_at`) VALUES
(1, 'WELCOME10', 'percent', 10, 50000, 100, 0, 1, 1, '2026-06-13 00:21:56', '2026-09-13 00:21:56', '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(2, 'ZOMZOP20', 'percent', 20, 100000, 50, 0, 1, 1, '2026-06-13 00:21:56', '2026-07-13 00:21:56', '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(3, 'GIAM30K', 'fixed', 30000, 150000, 200, 0, 1, 1, '2026-06-13 00:21:56', '2026-08-13 00:21:56', '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(4, 'FREESHIP', 'fixed', 15000, 80000, 300, 0, 2, 1, '2026-06-13 00:21:56', '2026-06-27 00:21:56', '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(5, 'SUMMER50', 'percent', 50, 200000, 30, 0, 1, 1, '2026-06-13 00:21:56', '2026-06-20 00:21:56', '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(6, 'EXPIRED', 'percent', 15, 50000, 100, 100, 1, 0, '2026-04-13 00:21:56', '2026-05-13 00:21:56', '2026-06-13 00:21:56', '2026-06-13 00:21:56');

-- --------------------------------------------------------

--
-- Table structure for table `coupon_notifications`
--

CREATE TABLE `coupon_notifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `coupon_id` bigint UNSIGNED NOT NULL,
  `channel` enum('zalo','sms','email') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'zalo',
  `status` enum('pending','sent','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupon_notifications`
--

INSERT INTO `coupon_notifications` (`id`, `user_id`, `coupon_id`, `channel`, `status`, `sent_at`, `created_at`, `updated_at`) VALUES
(1, 3, 1, 'zalo', 'sent', '2026-06-07 00:21:57', NULL, NULL),
(2, 3, 2, 'zalo', 'sent', '2026-06-11 00:21:57', NULL, NULL),
(3, 3, 3, 'zalo', 'sent', '2026-06-12 00:21:57', NULL, NULL),
(4, 3, 4, 'zalo', 'failed', NULL, NULL, NULL),
(5, 3, 5, 'zalo', 'sent', '2026-06-07 00:21:57', NULL, NULL),
(6, 3, 6, 'zalo', 'sent', '2026-06-06 00:21:57', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usages`
--

CREATE TABLE `coupon_usages` (
  `id` bigint UNSIGNED NOT NULL,
  `coupon_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupon_usages`
--

INSERT INTO `coupon_usages` (`id`, `coupon_id`, `user_id`, `order_id`, `used_at`) VALUES
(1, 4, 3, 1, '2026-06-13 00:21:57'),
(2, 6, 3, 2, '2026-06-13 00:21:57'),
(3, 3, 3, 6, '2026-06-13 00:21:57'),
(4, 2, 3, 7, '2026-06-13 00:21:57'),
(5, 1, 3, 8, '2026-06-13 00:21:57');

-- --------------------------------------------------------

--
-- Table structure for table `daily_sales_summary`
--

CREATE TABLE `daily_sales_summary` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `menu_item_id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `day_of_week` tinyint UNSIGNED NOT NULL COMMENT '0=Sun, 1=Mon, ..., 6=Sat',
  `total_qty` int UNSIGNED NOT NULL DEFAULT '0',
  `total_revenue` decimal(15,0) NOT NULL DEFAULT '0',
  `order_type_breakdown` json DEFAULT NULL COMMENT '{"takeaway": 10, "delivery": 5}',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `daily_sales_summary`
--

INSERT INTO `daily_sales_summary` (`id`, `branch_id`, `menu_item_id`, `date`, `day_of_week`, `total_qty`, `total_revenue`, `order_type_breakdown`, `created_at`, `updated_at`) VALUES
(1, 1, 38, '2026-06-13', 6, 1, 149000, '\"{\\\"takeaway\\\":0,\\\"delivery\\\":1}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(2, 1, 2, '2026-06-13', 6, 2, 130000, '\"{\\\"takeaway\\\":0,\\\"delivery\\\":2}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(3, 1, 24, '2026-06-13', 6, 2, 38000, '\"{\\\"takeaway\\\":0,\\\"delivery\\\":2}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(4, 1, 32, '2026-06-13', 6, 2, 38000, '\"{\\\"takeaway\\\":0,\\\"delivery\\\":2}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(5, 2, 9, '2026-06-13', 6, 2, 218000, '\"{\\\"takeaway\\\":2,\\\"delivery\\\":0}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(6, 2, 16, '2026-06-13', 6, 2, 110000, '\"{\\\"takeaway\\\":2,\\\"delivery\\\":0}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(7, 2, 2, '2026-06-13', 6, 3, 195000, '\"{\\\"takeaway\\\":2,\\\"delivery\\\":1}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(8, 2, 31, '2026-06-13', 6, 1, 15000, '\"{\\\"takeaway\\\":1,\\\"delivery\\\":0}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(9, 2, 29, '2026-06-13', 6, 1, 49000, '\"{\\\"takeaway\\\":1,\\\"delivery\\\":0}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(10, 3, 25, '2026-06-13', 6, 2, 50000, '\"{\\\"takeaway\\\":0,\\\"delivery\\\":2}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(11, 3, 14, '2026-06-13', 6, 4, 196000, '\"{\\\"takeaway\\\":3,\\\"delivery\\\":1}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(12, 3, 18, '2026-06-13', 6, 2, 158000, '\"{\\\"takeaway\\\":2,\\\"delivery\\\":0}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(13, 3, 2, '2026-06-13', 6, 2, 130000, '\"{\\\"takeaway\\\":2,\\\"delivery\\\":0}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(14, 3, 11, '2026-06-13', 6, 1, 75000, '\"{\\\"takeaway\\\":1,\\\"delivery\\\":0}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(15, 3, 16, '2026-06-13', 6, 2, 110000, '\"{\\\"takeaway\\\":0,\\\"delivery\\\":2}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(16, 3, 20, '2026-06-13', 6, 1, 85000, '\"{\\\"takeaway\\\":0,\\\"delivery\\\":1}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(17, 3, 22, '2026-06-13', 6, 2, 110000, '\"{\\\"takeaway\\\":2,\\\"delivery\\\":0}\"', '2026-06-13 00:21:57', '2026-06-13 00:21:57');

-- --------------------------------------------------------

--
-- Table structure for table `face_descriptors`
--

CREATE TABLE `face_descriptors` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `descriptor` json NOT NULL COMMENT 'Float array 128 chiều từ face-api.js — không lưu ảnh gốc',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `menu_item_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `user_id`, `menu_item_id`, `created_at`, `updated_at`) VALUES
(1, 1, 6, '2026-06-13 00:21:57', '2026-06-13 00:21:57');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

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
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `base_price` decimal(12,0) NOT NULL,
  `discount_percent` tinyint UNSIGNED NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `tags` json DEFAULT NULL,
  `prep_time_minutes` int UNSIGNED NOT NULL DEFAULT '10',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `category_id`, `name`, `slug`, `description`, `base_price`, `discount_percent`, `image`, `is_available`, `tags`, `prep_time_minutes`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'Classic Burger', 'classic-burger', 'Burger bò truyền thống với rau xà lách, cà chua, phô mai và sốt đặc trưng.', 45000, 100, 'classic-burger.jpg', 1, '\"[\\\"b\\\\u00f2\\\",\\\"c\\\\u1ed5 \\\\u0111i\\\\u1ec3n\\\",\\\"bestseller\\\"]\"', 8, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(2, 1, 'Double Smash', 'double-smash', 'Hai lớp thịt bò smash mỏng giòn, phô mai tan chảy, dưa chuột muối.', 65000, 0, 'double-smash.jpg', 1, '\"[\\\"b\\\\u00f2\\\",\\\"double\\\",\\\"ph\\\\u00f4 mai\\\"]\"', 10, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(3, 1, 'Crispy Chicken', 'crispy-chicken', 'Gà phi lê chiên giòn, xà lách bắp cải, sốt mayo.', 55000, 0, 'crispy-chicken.jpg', 1, '\"[\\\"g\\\\u00e0\\\",\\\"gi\\\\u00f2n\\\"]\"', 9, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(4, 1, 'Spicy Chicken', 'spicy-chicken', 'Gà phi lê cay, jalapeño, sốt sriracha, rau diếp.', 59000, 0, 'spicy-chicken.jpg', 1, '\"[\\\"g\\\\u00e0\\\",\\\"cay\\\",\\\"spicy\\\"]\"', 9, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(5, 1, 'Mushroom Swiss', 'mushroom-swiss', 'Thịt bò, nấm xào bơ, phô mai Swiss tan chảy.', 62000, 0, 'buger-nam-1.jpg', 1, '\"[\\\"b\\\\u00f2\\\",\\\"n\\\\u1ea5m\\\",\\\"ph\\\\u00f4 mai\\\"]\"', 10, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(6, 1, 'BBQ Bacon', 'bbq-bacon', 'Thịt bò, bacon giòn, sốt BBQ đậm đà, hành caramel.', 69000, 0, 'bbq-bacon.jpg', 1, '\"[\\\"b\\\\u00f2\\\",\\\"bacon\\\",\\\"bbq\\\",\\\"bestseller\\\"]\"', 11, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(7, 2, 'Pizza Phô Mai', 'pizza-pho-mai', 'Đế mỏng giòn, sốt cà chua, 4 loại phô mai tan chảy.', 89000, 0, 'pizza-pho-mai.jpg', 1, '\"[\\\"ph\\\\u00f4 mai\\\",\\\"chay\\\",\\\"bestseller\\\"]\"', 15, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(8, 2, 'Pizza BBQ Gà', 'pizza-bbq-ga', 'Đế dày mềm, sốt BBQ, gà nướng, ớt chuông, hành tây.', 99000, 0, 'pizza-bbq-ga.jpg', 1, '\"[\\\"g\\\\u00e0\\\",\\\"bbq\\\"]\"', 15, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(9, 2, 'Pizza Hải Sản', 'pizza-hai-san', 'Tôm, mực, nghêu, sốt kem tỏi, phô mai mozzarella.', 109000, 0, 'pizza-hai-san.jpg', 1, '\"[\\\"h\\\\u1ea3i s\\\\u1ea3n\\\",\\\"t\\\\u00f4m\\\",\\\"m\\\\u1ef1c\\\"]\"', 18, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(10, 2, 'Pizza Cay Kiểu Ý', 'pizza-cay-kieu-y', 'Xúc xích cay pepperoni, ớt, sốt cà chua đậm.', 95000, 0, 'pizza-cay-kieu-y.jpg', 1, '\"[\\\"cay\\\",\\\"pepperoni\\\",\\\"spicy\\\"]\"', 15, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(11, 3, 'Spaghetti Bò Bằm', 'spaghetti-bo-bam', 'Spaghetti với sốt bolognese thịt bò bằm đậm đà, rắc phô mai parmesan.', 75000, 0, 'spaghetti-bo-bam.jpg', 1, '\"[\\\"b\\\\u00f2\\\",\\\"s\\\\u1ed1t \\\\u0111\\\\u1ecf\\\",\\\"pasta\\\"]\"', 12, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(12, 3, 'Spaghetti Kem Gà', 'spaghetti-kem-ga', 'Spaghetti carbonara, gà xào kem, bacon, trứng.', 79000, 0, 'spaghetti-kem-ga.jpg', 1, '\"[\\\"g\\\\u00e0\\\",\\\"kem\\\",\\\"carbonara\\\"]\"', 12, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(13, 3, 'Penne Cà Chua', 'penne-ca-chua', 'Penne sốt marinara cà chua tươi, rau basil, chay hoàn toàn.', 69000, 0, 'penne-ca-chua.jpg', 1, '\"[\\\"chay\\\",\\\"c\\\\u00e0 chua\\\",\\\"pasta\\\"]\"', 10, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(14, 4, 'Sandwich Gà Nướng', 'sandwich-ga-nuong', 'Bánh mì sandwich, gà nướng thảo mộc, rau xà lách, cà chua.', 49000, 0, 'sandwich-ga-nuong.jpg', 1, '\"[\\\"g\\\\u00e0\\\",\\\"n\\\\u01b0\\\\u1edbng\\\",\\\"l\\\\u00e0nh m\\\\u1ea1nh\\\"]\"', 8, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(15, 4, 'Sandwich Trứng Phô Mai', 'sandwich-trung-pho-mai', 'Trứng ốp la, phô mai cheddar, bánh mì nướng giòn.', 45000, 0, 'sandwich-trung-pho-mai.jpg', 1, '\"[\\\"tr\\\\u1ee9ng\\\",\\\"ph\\\\u00f4 mai\\\",\\\"bu\\\\u1ed5i s\\\\u00e1ng\\\"]\"', 7, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(16, 4, 'Sandwich BLT Bacon', 'sandwich-blt-bacon', 'Bacon giòn, xà lách, cà chua, mayo, bánh mì trắng.', 55000, 0, 'sandwich-blt-bacon.jpg', 1, '\"[\\\"bacon\\\",\\\"blt\\\"]\"', 8, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(17, 5, 'Gà Chiên Giòn (2 miếng)', 'ga-chien-gion-2', 'Gà chiên giòn lớp vỏ đặc trưng, 2 miếng.', 45000, 0, 'ga-chien-gion.jpg', 1, '\"[\\\"g\\\\u00e0\\\",\\\"gi\\\\u00f2n\\\",\\\"chi\\\\u00ean\\\"]\"', 10, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(18, 5, 'Gà Chiên Giòn (4 miếng)', 'ga-chien-gion-4', 'Gà chiên giòn lớp vỏ đặc trưng, 4 miếng.', 79000, 0, 'ga-chien-gion.jpg', 1, '\"[\\\"g\\\\u00e0\\\",\\\"gi\\\\u00f2n\\\",\\\"chi\\\\u00ean\\\",\\\"ph\\\\u1ea7n l\\\\u1edbn\\\"]\"', 10, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(19, 5, 'Gà Chiên Cay (2 miếng)', 'ga-chien-cay-2', 'Gà chiên cay xé lưỡi, 2 miếng.', 49000, 0, 'ga-chien-cay.jpg', 1, '\"[\\\"g\\\\u00e0\\\",\\\"cay\\\",\\\"chi\\\\u00ean\\\",\\\"spicy\\\"]\"', 10, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(20, 5, 'Gà Chiên Cay (4 miếng)', 'ga-chien-cay-4', 'Gà chiên cay xé lưỡi, 4 miếng.', 85000, 0, 'ga-chien-cay.jpg', 1, '\"[\\\"g\\\\u00e0\\\",\\\"cay\\\",\\\"chi\\\\u00ean\\\",\\\"spicy\\\",\\\"ph\\\\u1ea7n l\\\\u1edbn\\\"]\"', 10, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(21, 5, 'Cánh Gà Mắm Tỏi', 'canh-ga-mam-toi', 'Cánh gà chiên giòn sốt mắm tỏi đặc trưng Việt Nam.', 59000, 0, 'canh-ga-mam-toi.jpg', 1, '\"[\\\"g\\\\u00e0\\\",\\\"m\\\\u1eafm t\\\\u1ecfi\\\",\\\"bestseller\\\"]\"', 12, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(22, 5, 'Đùi Gà Chiên Giòn', 'dui-ga-chien-gion', 'Đùi gà nguyên chiếc, vỏ giòn, thịt mềm thơm.', 55000, 0, 'dui-ga-chien-gion.jpg', 1, '\"[\\\"g\\\\u00e0\\\",\\\"\\\\u0111\\\\u00f9i\\\",\\\"gi\\\\u00f2n\\\"]\"', 12, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(23, 5, 'Gà Popcorn', 'ga-popcorn', 'Miếng gà nhỏ chiên giòn cỡ một miếng, ăn vặt tuyệt vời.', 39000, 0, 'ga-popcorn.jpg', 1, '\"[\\\"g\\\\u00e0\\\",\\\"snack\\\",\\\"\\\\u0103n v\\\\u1eb7t\\\"]\"', 8, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(24, 6, 'Khoai Tây Chiên (Nhỏ)', 'khoai-tay-nho', 'Khoai tây cắt sợi chiên vàng giòn, size nhỏ.', 19000, 0, 'khoai-tay-chien.jpg', 1, '\"[\\\"khoai\\\",\\\"chi\\\\u00ean\\\",\\\"sides\\\"]\"', 5, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(25, 6, 'Khoai Tây Chiên (Vừa)', 'khoai-tay-vua', 'Khoai tây cắt sợi chiên vàng giòn, size vừa.', 25000, 0, 'khoai-tay-chien.jpg', 1, '\"[\\\"khoai\\\",\\\"chi\\\\u00ean\\\",\\\"sides\\\"]\"', 5, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(26, 6, 'Khoai Tây Chiên (Lớn)', 'khoai-tay-lon', 'Khoai tây cắt sợi chiên vàng giòn, size lớn.', 32000, 0, 'khoai-tay-chien.jpg', 1, '\"[\\\"khoai\\\",\\\"chi\\\\u00ean\\\",\\\"sides\\\",\\\"ph\\\\u1ea7n l\\\\u1edbn\\\"]\"', 5, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(27, 6, 'Onion Rings', 'onion-rings', 'Vòng hành tây tẩm bột chiên giòn, chấm sốt.', 29000, 0, 'onion-rings.jpg', 1, '\"[\\\"h\\\\u00e0nh\\\",\\\"chi\\\\u00ean\\\",\\\"sides\\\"]\"', 6, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(28, 6, 'Nuggets Gà (6 miếng)', 'nuggets-6', 'Miếng gà nugget chiên giòn, 6 miếng, kèm sốt chấm.', 29000, 0, 'nuggets.jpg', 1, '\"[\\\"g\\\\u00e0\\\",\\\"nuggets\\\",\\\"sides\\\"]\"', 7, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(29, 6, 'Nuggets Gà (12 miếng)', 'nuggets-12', 'Miếng gà nugget chiên giòn, 12 miếng, kèm sốt chấm.', 49000, 0, 'nuggets.jpg', 1, '\"[\\\"g\\\\u00e0\\\",\\\"nuggets\\\",\\\"sides\\\",\\\"ph\\\\u1ea7n l\\\\u1edbn\\\"]\"', 7, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(30, 6, 'Coleslaw', 'coleslaw', 'Salad bắp cải, cà rốt trộn sốt kem nhẹ.', 15000, 0, 'coleslaw.jpg', 1, '\"[\\\"salad\\\",\\\"chay\\\",\\\"sides\\\",\\\"l\\\\u00e0nh m\\\\u1ea1nh\\\"]\"', 3, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(31, 7, 'Nước Ngọt', 'nuoc-ngot', 'Coca-Cola / Pepsi / Sprite — ghi chú loại khi đặt.', 15000, 0, 'nuoc-ngot.jpg', 1, '\"[\\\"n\\\\u01b0\\\\u1edbc\\\",\\\"c\\\\u00f3 ga\\\",\\\"ng\\\\u1ecdt\\\"]\"', 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(32, 7, 'Trà Chanh', 'tra-chanh', 'Trà xanh pha chanh tươi, đá lạnh.', 19000, 0, 'tra-chanh.jpg', 0, '\"[\\\"tr\\\\u00e0\\\",\\\"chanh\\\",\\\"m\\\\u00e1t\\\"]\"', 2, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(33, 7, 'Sữa Lắc Vani', 'sua-lac-vani', 'Sữa lắc hương vani đậm creamy.', 35000, 0, 'sua-lac.jpg', 0, '\"[\\\"s\\\\u1eefa l\\\\u1eafc\\\",\\\"ng\\\\u1ecdt\\\",\\\"vani\\\"]\"', 3, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(34, 7, 'Sữa Lắc Dâu', 'sua-lac-dau', 'Sữa lắc hương dâu tây tươi.', 35000, 0, 'sua-lac.jpg', 0, '\"[\\\"s\\\\u1eefa l\\\\u1eafc\\\",\\\"ng\\\\u1ecdt\\\",\\\"d\\\\u00e2u\\\"]\"', 3, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(35, 7, 'Sữa Lắc Chocolate', 'sua-lac-chocolate', 'Sữa lắc chocolate đậm vị.', 35000, 0, 'sua-lac.jpg', 0, '\"[\\\"s\\\\u1eefa l\\\\u1eafc\\\",\\\"ng\\\\u1ecdt\\\",\\\"chocolate\\\"]\"', 3, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(36, 7, 'Nước Suối', 'nuoc-suoi', 'Nước suối đóng chai 500ml.', 10000, 0, 'nuoc-suoi.jpg', 1, '\"[\\\"n\\\\u01b0\\\\u1edbc\\\",\\\"kh\\\\u00f4ng \\\\u0111\\\\u01b0\\\\u1eddng\\\"]\"', 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(37, 8, 'Combo Cơ Bản', 'combo-co-ban', '1 Classic Burger + Khoai tây chiên nhỏ + Nước ngọt. Tiết kiệm hơn gọi lẻ.', 69000, 0, 'combo-co-ban.jpg', 1, '\"[\\\"combo\\\",\\\"ti\\\\u1ebft ki\\\\u1ec7m\\\",\\\"1 ng\\\\u01b0\\\\u1eddi\\\"]\"', 12, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(38, 8, 'Combo Đôi', 'combo-doi', '2 Burger + Khoai tây chiên lớn + 2 Nước ngọt. Dành cho 2 người.', 149000, 0, 'combo-doi.jpg', 1, '\"[\\\"combo\\\",\\\"ti\\\\u1ebft ki\\\\u1ec7m\\\",\\\"2 ng\\\\u01b0\\\\u1eddi\\\"]\"', 15, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL),
(39, 8, 'Combo Gia Đình', 'combo-gia-dinh', '4 Burger + 2 Khoai tây chiên lớn + 4 Nước ngọt. Dành cho cả nhà.', 279000, 0, 'combo-gia-dinh.jpg', 1, '\"[\\\"combo\\\",\\\"ti\\\\u1ebft ki\\\\u1ec7m\\\",\\\"gia \\\\u0111\\\\u00ecnh\\\",\\\"4 ng\\\\u01b0\\\\u1eddi\\\"]\"', 20, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `menu_item_images`
--

CREATE TABLE `menu_item_images` (
  `id` bigint UNSIGNED NOT NULL,
  `menu_item_id` bigint UNSIGNED NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `menu_item_images`
--

INSERT INTO `menu_item_images` (`id`, `menu_item_id`, `image`, `alt_text`, `sort_order`, `is_primary`, `created_at`, `updated_at`) VALUES
(1, 6, 'buger-bacon-1.png', 'BBQ Bacon Burger góc trước', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(2, 6, 'buger-bacon-2.png', 'BBQ Bacon Burger góc nghiêng', 2, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(3, 6, 'buger-co-dien-3.png', 'BBQ Bacon Burger cắt đôi', 3, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(4, 3, 'buger-ga-crispy-1.jpg', 'Crispy Chicken Burger', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(5, 3, 'buger-ga-crispy-2.jpg', 'Crispy Chicken Burger nhìn gần', 2, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(6, 3, 'buger-ga-crispy-3.png', 'Crispy Chicken Burger cắt đôi', 3, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(7, 4, 'buger-ga-spicy-1.jpeg', 'Spicy Chicken Burger', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(8, 4, 'buger-ga-spicy-2.jpg', 'Spicy Chicken Burger góc 2', 2, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(9, 4, 'buger-ga-spicy-3.png', 'Spicy Chicken Burger cắt đôi', 3, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(10, 1, 'buger-co-dien-1.jpg', 'Classic Burger', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(11, 1, 'buger-nam-2.jpg', 'Classic Burger góc nghiêng', 2, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(12, 1, 'buger-nam-3.webp', 'Classic Burger cắt đôi', 3, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(13, 7, 'cheese-pizza-3.jpg', 'Pizza Phô Mai', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(14, 7, 'pizza-phomai-1.jpg', 'Pizza Phô Mai góc trên', 2, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(15, 7, 'pizza-phomai-2.jpg', 'Pizza Phô Mai miếng cắt', 3, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(16, 8, 'pizza-bbq-ga-1.png', 'Pizza BBQ Gà', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(17, 8, 'pizza-bbq-ga-2.png', 'Pizza BBQ Gà góc nghiêng', 2, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(18, 10, 'pizza-cay-kieuY-1.jpg', 'Pizza Cay Kiểu Ý', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(19, 10, 'pizza-cay-kieuY-2.webp', 'Pizza Cay Kiểu Ý góc 2', 2, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(20, 9, 'pizza-haisan-1.jpg', 'Pizza Hải Sản', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(21, 9, 'pizza-haisan-2.jpg', 'Pizza Hải Sản góc trên', 2, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(22, 9, 'pizza-haisan-3.jpg', 'Pizza Hải Sản miếng cắt', 3, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(23, 11, 'mi-bo-bam-1.png', 'Spaghetti Bò Bằm', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(24, 11, 'mi-bo-bam-2.webp', 'Spaghetti Bò Bằm góc gần', 2, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(25, 12, 'mi-kem-ga-1.png', 'Spaghetti Kem Gà', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(26, 12, 'mi-kem-ga-2.png', 'Spaghetti Kem Gà góc nghiêng', 2, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(27, 12, 'mi-kem-ga-3.png', 'Spaghetti Kem Gà đặc cận', 3, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(28, 13, 'penne-cachua-1.jpg', 'Penne Cà Chua', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(29, 13, 'penne-cachua-2.jpg', 'Penne Cà Chua góc nghiêng', 2, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(30, 13, 'penne-cachua-3.jpg', 'Penne Cà Chua cận sốt', 3, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(31, 16, 'sandwick-blt-1.jpg', 'Sandwich BLT Bacon', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(32, 16, 'sandwick-blt-2.jpg', 'Sandwich BLT Bacon góc 2', 2, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(33, 16, 'sandwick-blt-3.jpg', 'Sandwich BLT Bacon cắt đôi', 3, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(34, 14, 'sandwick-ga-2.webp', 'Sandwich Gà Nướng', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(35, 14, 'sandwick-ga-3.jpg', 'Sandwich Gà Nướng góc 2', 2, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(36, 15, 'sandwick-trung-phomai-1.jpg', 'Sandwich Trứng Phô Mai', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(37, 15, 'sandwick-trung-phomai-2.jpg', 'Sandwich Trứng Phô Mai góc 2', 2, 0, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(38, 17, 'ga-chien.webp', 'Gà Chiên Giòn 2 miếng', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(39, 18, 'ga-chien-2.webp', 'Gà Chiên Giòn 4 miếng', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(40, 19, 'ga-chien.webp', 'Gà Chiên Cay 2 miếng', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(41, 20, 'ga-chien-2.webp', 'Gà Chiên Cay 4 miếng', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(42, 30, 'coleslaw.jpg', 'Coleslaw salad', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(43, 28, 'nuggets.jpg', 'Nuggets Gà 6 miếng', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(44, 29, 'nuggets.jpg', 'Nuggets Gà 12 miếng', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(45, 37, 'combo-1.jpg', 'Combo Cơ Bản', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(46, 38, 'combo-2.jpg', 'Combo Đôi', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(47, 31, 'nuoc-ngot.jpg', 'Nước Ngọt', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(48, 36, 'nuoc-suoi.jpg', 'Nước Suối', 1, 1, '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(49, 39, 'combo-3.jpg', 'Combo Gia Đình', 1, 1, '2026-06-14 06:32:43', '2026-06-14 06:32:43');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2026_05_26_061458_create_branches_table', 1),
(2, '2026_05_26_075637_create_users_table', 1),
(3, '2026_05_26_075900_create_system_tables', 1),
(4, '2026_05_26_083133_create_categories_table', 1),
(5, '2026_05_26_083235_create_menu_items_table', 1),
(6, '2026_05_26_083314_create_branch_menu_items_table', 1),
(7, '2026_05_26_083338_create_coupons_table', 1),
(8, '2026_05_26_083421_create_orders_table', 1),
(9, '2026_05_26_083440_create_order_items_table', 1),
(10, '2026_05_26_083504_create_order_histories_table', 1),
(11, '2026_05_26_083525_create_reviews_table', 1),
(12, '2026_05_26_083542_create_banners_table', 1),
(13, '2026_05_26_083640_create_support_tickets_table', 1),
(14, '2026_05_26_083703_create_support_messages_table', 1),
(15, '2026_05_26_083728_create_settings_table', 1),
(16, '2026_05_26_083745_create_ai_chat_sessions_table', 1),
(17, '2026_05_26_083809_create_sales_predictions_table', 1),
(18, '2026_05_26_083830_create_daily_sales_summary_table', 1),
(19, '2026_05_26_083850_create_shifts_table', 1),
(20, '2026_05_26_083907_create_face_descriptors_table', 1),
(21, '2026_05_26_083928_create_attendances_table', 1),
(22, '2026_05_26_083945_create_salary_and_payroll_tables', 1),
(23, '2026_05_26_172743_add_zalo_fields_to_users_table', 1),
(24, '2026_05_26_173006_create_coupon_notifications_table', 1),
(25, '2026_05_26_173036_create_coupon_usages_table', 1),
(26, '2026_05_27_050122_create_menu_item_images_table', 1),
(27, '2026_05_27_061657_create_favorites_table', 1),
(28, '2026_06_14_000001_add_discount_percent_to_menu_items_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `order_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `kitchen_by` bigint UNSIGNED DEFAULT NULL,
  `type` enum('takeaway','delivery') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','confirmed','cooking','ready','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `subtotal` decimal(12,0) NOT NULL DEFAULT '0',
  `discount` decimal(12,0) NOT NULL DEFAULT '0',
  `total` decimal(12,0) NOT NULL DEFAULT '0',
  `payment_method` enum('cash','momo','vnpay') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash',
  `payment_status` enum('unpaid','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `delivery_address` text COLLATE utf8mb4_unicode_ci,
  `estimated_time` timestamp NULL DEFAULT NULL,
  `pickup_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `coupon_id` bigint UNSIGNED DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `user_id`, `branch_id`, `kitchen_by`, `type`, `status`, `subtotal`, `discount`, `total`, `payment_method`, `payment_status`, `delivery_address`, `estimated_time`, `pickup_code`, `coupon_id`, `note`, `created_at`, `updated_at`) VALUES
(1, 'ORD-6A2D0515427C5', 3, 3, NULL, 'delivery', 'completed', 25000, 15000, 10000, 'cash', 'paid', '123 Đường Nguyễn Huệ, Q.1, TP.HCM', NULL, NULL, 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(2, 'ORD-6A2D051543ACC', 3, 2, NULL, 'takeaway', 'completed', 164000, 15, 163985, 'cash', 'paid', NULL, NULL, '543ADE', 6, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(3, 'ORD-6A2D051544D0B', 3, 3, NULL, 'takeaway', 'completed', 128000, 0, 128000, 'momo', 'paid', NULL, NULL, '544D1C', NULL, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(4, 'ORD-6A2D051545DED', 3, 2, NULL, 'delivery', 'completed', 65000, 0, 65000, 'cash', 'paid', '123 Đường Nguyễn Huệ, Q.1, TP.HCM', NULL, NULL, NULL, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(5, 'ORD-6A2D0515467A2', 3, 2, NULL, 'takeaway', 'completed', 80000, 0, 80000, 'vnpay', 'paid', NULL, NULL, '5467B0', NULL, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(6, 'ORD-6A2D05154798E', 3, 3, NULL, 'takeaway', 'completed', 65000, 30000, 35000, 'momo', 'paid', NULL, NULL, '5479A1', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(7, 'ORD-6A2D051548576', 3, 3, NULL, 'takeaway', 'completed', 75000, 20, 74980, 'momo', 'paid', NULL, NULL, '548587', 2, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(8, 'ORD-6A2D0515490F6', 3, 2, NULL, 'takeaway', 'completed', 49000, 10, 48990, 'momo', 'paid', NULL, NULL, '549109', 1, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(9, 'ORD-6A2D051549C6B', 3, 3, NULL, 'delivery', 'completed', 189000, 0, 189000, 'momo', 'paid', '123 Đường Nguyễn Huệ, Q.1, TP.HCM', NULL, NULL, NULL, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(10, 'ORD-6A2D05154B6A7', 3, 3, NULL, 'takeaway', 'completed', 104000, 0, 104000, 'cash', 'paid', NULL, NULL, '54B6BD', NULL, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(11, 'ORD-6A2D05154CB2C', 3, 1, NULL, 'delivery', 'completed', 149000, 20, 148980, 'vnpay', 'paid', '123 Đường Nguyễn Huệ, Q.1, TP.HCM', NULL, NULL, 2, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(12, 'ORD-6A2D05154D971', 3, 1, NULL, 'delivery', 'completed', 103000, 0, 103000, 'momo', 'paid', '123 Đường Nguyễn Huệ, Q.1, TP.HCM', NULL, NULL, NULL, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(13, 'ORD-6A2D05154F286', 3, 1, NULL, 'delivery', 'cancelled', 15000, 20, 14980, 'cash', 'unpaid', '123 Đường Nguyễn Huệ, Q.1, TP.HCM', NULL, NULL, 2, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(14, 'ORD-6A2D05154FF96', 3, 2, NULL, 'takeaway', 'cancelled', 29000, 20, 28980, 'vnpay', 'unpaid', NULL, NULL, '54FFAB', 2, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(15, 'ORD-6A2D051550E63', 3, 3, NULL, 'delivery', 'cancelled', 199000, 10, 198990, 'momo', 'unpaid', '123 Đường Nguyễn Huệ, Q.1, TP.HCM', NULL, NULL, 1, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(16, 'ORD-6A2D0515527C8', 3, 1, NULL, 'takeaway', 'cancelled', 94000, 0, 94000, 'vnpay', 'unpaid', NULL, NULL, '5527DE', NULL, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(17, 'ORD-6A2D051553ABE', 3, 3, NULL, 'takeaway', 'cancelled', 223000, 0, 223000, 'vnpay', 'unpaid', NULL, NULL, '553AD4', NULL, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(18, 'ORD-6A2D05155534D', 3, 2, NULL, 'takeaway', 'ready', 176000, 15000, 161000, 'momo', 'unpaid', NULL, NULL, '555360', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(19, 'ORD-6A2D051557276', 3, 2, NULL, 'delivery', 'ready', 164000, 0, 164000, 'cash', 'unpaid', '123 Đường Nguyễn Huệ, Q.1, TP.HCM', NULL, NULL, NULL, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(20, 'ORD-6A2D051558933', 3, 2, NULL, 'takeaway', 'ready', 25000, 0, 25000, 'momo', 'unpaid', NULL, NULL, '55894A', NULL, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(21, 'ORD-6A2D0515597BC', 3, 3, NULL, 'delivery', 'cooking', 104000, 15000, 89000, 'vnpay', 'unpaid', '123 Đường Nguyễn Huệ, Q.1, TP.HCM', NULL, NULL, 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(22, 'ORD-6A2D05155B00B', 3, 1, NULL, 'takeaway', 'cooking', 123000, 0, 123000, 'cash', 'unpaid', NULL, NULL, '55B023', NULL, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(23, 'ORD-6A2D05155D4D3', 3, 3, NULL, 'delivery', 'cooking', 149000, 10, 148990, 'vnpay', 'unpaid', '123 Đường Nguyễn Huệ, Q.1, TP.HCM', NULL, NULL, 1, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(24, 'ORD-6A2D05155F994', 3, 1, NULL, 'delivery', 'confirmed', 109000, 30000, 79000, 'vnpay', 'unpaid', '123 Đường Nguyễn Huệ, Q.1, TP.HCM', NULL, NULL, 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(25, 'ORD-6A2D0515609C9', 3, 2, NULL, 'delivery', 'confirmed', 114000, 0, 114000, 'vnpay', 'unpaid', '123 Đường Nguyễn Huệ, Q.1, TP.HCM', NULL, NULL, NULL, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(26, 'ORD-6A2D05156222D', 3, 1, NULL, 'takeaway', 'confirmed', 35000, 50, 34950, 'momo', 'unpaid', NULL, NULL, '56223E', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(27, 'ORD-6A2D051563150', 3, 2, NULL, 'delivery', 'pending', 29000, 29000, 0, 'vnpay', 'unpaid', '123 Đường Nguyễn Huệ, Q.1, TP.HCM', NULL, NULL, 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(28, 'ORD-6A2D051563EF0', 3, 2, NULL, 'delivery', 'pending', 356000, 30000, 326000, 'momo', 'unpaid', '123 Đường Nguyễn Huệ, Q.1, TP.HCM', NULL, NULL, 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(29, 'ORD-6A2D051565A77', 3, 1, NULL, 'takeaway', 'pending', 124000, 0, 124000, 'momo', 'unpaid', NULL, NULL, '565A8A', NULL, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(30, 'ORD-6A2D05156704A', 3, 1, NULL, 'delivery', 'pending', 124000, 0, 124000, 'vnpay', 'unpaid', '123 Đường Nguyễn Huệ, Q.1, TP.HCM', NULL, NULL, NULL, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57');

-- --------------------------------------------------------

--
-- Table structure for table `order_histories`
--

CREATE TABLE `order_histories` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `from_status` enum('pending','confirmed','cooking','ready','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` enum('pending','confirmed','cooking','ready','completed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` bigint UNSIGNED NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_histories`
--

INSERT INTO `order_histories` (`id`, `order_id`, `from_status`, `to_status`, `changed_by`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(2, 1, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(3, 1, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(4, 1, 'cooking', 'ready', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(5, 1, 'ready', 'completed', 2, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(6, 2, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(7, 2, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(8, 2, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(9, 2, 'cooking', 'ready', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(10, 2, 'ready', 'completed', 2, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(11, 3, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(12, 3, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(13, 3, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(14, 3, 'cooking', 'ready', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(15, 3, 'ready', 'completed', 2, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(16, 4, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(17, 4, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(18, 4, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(19, 4, 'cooking', 'ready', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(20, 4, 'ready', 'completed', 2, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(21, 5, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(22, 5, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(23, 5, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(24, 5, 'cooking', 'ready', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(25, 5, 'ready', 'completed', 2, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(26, 6, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(27, 6, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(28, 6, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(29, 6, 'cooking', 'ready', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(30, 6, 'ready', 'completed', 2, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(31, 7, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(32, 7, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(33, 7, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(34, 7, 'cooking', 'ready', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(35, 7, 'ready', 'completed', 2, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(36, 8, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(37, 8, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(38, 8, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(39, 8, 'cooking', 'ready', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(40, 8, 'ready', 'completed', 2, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(41, 9, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(42, 9, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(43, 9, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(44, 9, 'cooking', 'ready', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(45, 9, 'ready', 'completed', 2, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(46, 10, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(47, 10, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(48, 10, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(49, 10, 'cooking', 'ready', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(50, 10, 'ready', 'completed', 2, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(51, 11, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(52, 11, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(53, 11, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(54, 11, 'cooking', 'ready', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(55, 11, 'ready', 'completed', 2, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(56, 12, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(57, 12, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(58, 12, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(59, 12, 'cooking', 'ready', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(60, 12, 'ready', 'completed', 2, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(61, 13, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(62, 13, 'pending', 'cancelled', 4, 'Khách hủy đơn', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(63, 14, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(64, 14, 'pending', 'cancelled', 4, 'Khách hủy đơn', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(65, 15, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(66, 15, 'pending', 'cancelled', 4, 'Khách hủy đơn', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(67, 16, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(68, 16, 'pending', 'cancelled', 4, 'Khách hủy đơn', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(69, 17, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(70, 17, 'pending', 'cancelled', 4, 'Khách hủy đơn', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(71, 18, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(72, 18, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(73, 18, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(74, 18, 'cooking', 'ready', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(75, 19, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(76, 19, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(77, 19, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(78, 19, 'cooking', 'ready', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(79, 20, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(80, 20, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(81, 20, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(82, 20, 'cooking', 'ready', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(83, 21, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(84, 21, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(85, 21, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(86, 22, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(87, 22, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(88, 22, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(89, 23, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(90, 23, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(91, 23, 'confirmed', 'cooking', 5, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(92, 24, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(93, 24, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(94, 25, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(95, 25, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(96, 26, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(97, 26, 'pending', 'confirmed', 4, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(98, 27, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(99, 28, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(100, 29, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(101, 30, NULL, 'pending', 3, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `menu_item_id` bigint UNSIGNED NOT NULL,
  `name_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price_snapshot` decimal(12,0) NOT NULL,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `subtotal` decimal(12,0) NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `name_snapshot`, `price_snapshot`, `quantity`, `subtotal`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, 25, 'Khoai Tây Chiên (Vừa)', 25000, 2, 50000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(2, 2, 9, 'Pizza Hải Sản', 109000, 2, 218000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(3, 2, 16, 'Sandwich BLT Bacon', 55000, 2, 110000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(4, 3, 14, 'Sandwich Gà Nướng', 49000, 1, 49000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(5, 3, 18, 'Gà Chiên Giòn (4 miếng)', 79000, 2, 158000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(6, 4, 2, 'Double Smash', 65000, 1, 65000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(7, 5, 2, 'Double Smash', 65000, 2, 130000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(8, 5, 31, 'Nước Ngọt', 15000, 1, 15000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(9, 6, 2, 'Double Smash', 65000, 2, 130000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(10, 7, 11, 'Spaghetti Bò Bằm', 75000, 1, 75000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(11, 8, 29, 'Nuggets Gà (12 miếng)', 49000, 1, 49000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(12, 9, 14, 'Sandwich Gà Nướng', 49000, 1, 49000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(13, 9, 16, 'Sandwich BLT Bacon', 55000, 2, 110000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(14, 9, 20, 'Gà Chiên Cay (4 miếng)', 85000, 1, 85000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(15, 10, 14, 'Sandwich Gà Nướng', 49000, 2, 98000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(16, 10, 22, 'Đùi Gà Chiên Giòn', 55000, 2, 110000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(17, 11, 38, 'Combo Đôi', 149000, 1, 149000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(18, 12, 2, 'Double Smash', 65000, 2, 130000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(19, 12, 24, 'Khoai Tây Chiên (Nhỏ)', 19000, 2, 38000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(20, 12, 32, 'Trà Chanh', 19000, 2, 38000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(21, 13, 31, 'Nước Ngọt', 15000, 2, 30000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(22, 14, 27, 'Onion Rings', 29000, 2, 58000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(23, 15, 11, 'Spaghetti Bò Bằm', 75000, 2, 150000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(24, 15, 12, 'Spaghetti Kem Gà', 79000, 1, 79000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(25, 15, 15, 'Sandwich Trứng Phô Mai', 45000, 1, 45000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(26, 16, 2, 'Double Smash', 65000, 2, 130000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(27, 16, 27, 'Onion Rings', 29000, 1, 29000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(28, 17, 11, 'Spaghetti Bò Bằm', 75000, 1, 75000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(29, 17, 12, 'Spaghetti Kem Gà', 79000, 2, 158000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(30, 17, 37, 'Combo Cơ Bản', 69000, 1, 69000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(31, 18, 7, 'Pizza Phô Mai', 89000, 1, 89000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(32, 18, 22, 'Đùi Gà Chiên Giòn', 55000, 1, 55000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(33, 18, 26, 'Khoai Tây Chiên (Lớn)', 32000, 1, 32000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(34, 19, 9, 'Pizza Hải Sản', 109000, 1, 109000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(35, 19, 16, 'Sandwich BLT Bacon', 55000, 1, 55000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(36, 20, 25, 'Khoai Tây Chiên (Vừa)', 25000, 1, 25000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(37, 21, 6, 'BBQ Bacon', 69000, 1, 69000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(38, 21, 34, 'Sữa Lắc Dâu', 35000, 1, 35000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(39, 22, 4, 'Spicy Chicken', 59000, 1, 59000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(40, 22, 28, 'Nuggets Gà (6 miếng)', 29000, 1, 29000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(41, 22, 33, 'Sữa Lắc Vani', 35000, 2, 70000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(42, 23, 13, 'Penne Cà Chua', 69000, 2, 138000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(43, 23, 22, 'Đùi Gà Chiên Giòn', 55000, 2, 110000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(44, 23, 25, 'Khoai Tây Chiên (Vừa)', 25000, 1, 25000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(45, 24, 9, 'Pizza Hải Sản', 109000, 1, 109000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(46, 25, 10, 'Pizza Cay Kiểu Ý', 95000, 1, 95000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(47, 25, 24, 'Khoai Tây Chiên (Nhỏ)', 19000, 2, 38000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(48, 26, 33, 'Sữa Lắc Vani', 35000, 2, 70000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(49, 27, 27, 'Onion Rings', 29000, 2, 58000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(50, 28, 5, 'Mushroom Swiss', 62000, 1, 62000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(51, 28, 30, 'Coleslaw', 15000, 1, 15000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(52, 28, 39, 'Combo Gia Đình', 279000, 1, 279000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(53, 29, 3, 'Crispy Chicken', 55000, 1, 55000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(54, 29, 37, 'Combo Cơ Bản', 69000, 2, 138000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(55, 30, 16, 'Sandwich BLT Bacon', 55000, 2, 110000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(56, 30, 37, 'Combo Cơ Bản', 69000, 1, 69000, NULL, '2026-06-13 00:21:57', '2026-06-13 00:21:57');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payrolls`
--

CREATE TABLE `payrolls` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `month` tinyint UNSIGNED NOT NULL,
  `year` smallint UNSIGNED NOT NULL,
  `total_hours` decimal(8,2) NOT NULL DEFAULT '0.00',
  `total_days` int UNSIGNED NOT NULL DEFAULT '0',
  `base_salary` decimal(12,0) NOT NULL DEFAULT '0',
  `bonus` decimal(12,0) NOT NULL DEFAULT '0',
  `deduction` decimal(12,0) NOT NULL DEFAULT '0',
  `total` decimal(12,0) NOT NULL DEFAULT '0',
  `status` enum('draft','confirmed','paid') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payrolls`
--

INSERT INTO `payrolls` (`id`, `user_id`, `branch_id`, `month`, `year`, `total_hours`, `total_days`, `base_salary`, `bonus`, `deduction`, `total`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 6, 2026, 58.30, 12, 1457500, 0, 0, 1457500, 'draft', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(2, 4, 1, 6, 2026, 59.78, 12, 12000000, 0, 0, 12000000, 'draft', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(3, 5, 1, 6, 2026, 61.00, 12, 1342000, 0, 0, 1342000, 'draft', '2026-06-13 00:21:57', '2026-06-13 00:21:57');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `rating` tinyint UNSIGNED NOT NULL,
  `delivery_rating` tinyint UNSIGNED DEFAULT NULL COMMENT 'Null nếu takeaway',
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `order_id`, `user_id`, `branch_id`, `rating`, `delivery_rating`, `comment`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 3, 3, 4, 'Sẽ giới thiệu cho bạn bè, quá ngon!', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(2, 2, 3, 2, 5, NULL, 'Đóng gói cẩn thận, đồ ăn còn nóng khi nhận.', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(3, 3, 3, 3, 4, NULL, 'Đồ ăn ngon, giao hàng nhanh, rất hài lòng!', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(4, 5, 3, 2, 3, NULL, 'Combo gia đình rất đáng tiền, đủ ăn cho cả nhà.', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(5, 8, 3, 2, 4, NULL, 'Giao hơi trễ nhưng đồ ăn vẫn ngon.', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(6, 9, 3, 3, 4, 3, 'Vị hơi mặn một chút nhưng nhìn chung ổn.', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(7, 10, 3, 3, 4, NULL, 'Combo gia đình rất đáng tiền, đủ ăn cho cả nhà.', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(8, 11, 3, 1, 4, 4, 'Đồ ăn ngon, giao hàng nhanh, rất hài lòng!', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(9, 12, 3, 1, 5, 5, 'Vị hơi mặn một chút nhưng nhìn chung ổn.', '2026-06-13 00:21:57', '2026-06-13 00:21:57');

-- --------------------------------------------------------

--
-- Table structure for table `salary_configs`
--

CREATE TABLE `salary_configs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` enum('hourly','fixed') COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` decimal(12,0) NOT NULL COMMENT 'Lương/giờ hoặc lương cố định/tháng',
  `effective_from` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `salary_configs`
--

INSERT INTO `salary_configs` (`id`, `user_id`, `type`, `rate`, `effective_from`, `created_at`, `updated_at`) VALUES
(1, 2, 'hourly', 25000, '2026-06-01', '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(2, 4, 'fixed', 12000000, '2026-06-01', '2026-06-13 00:21:56', '2026-06-13 00:21:56'),
(3, 5, 'hourly', 22000, '2026-06-01', '2026-06-13 00:21:56', '2026-06-13 00:21:56');

-- --------------------------------------------------------

--
-- Table structure for table `sales_predictions`
--

CREATE TABLE `sales_predictions` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `menu_item_id` bigint UNSIGNED NOT NULL,
  `predicted_date` date NOT NULL,
  `predicted_qty` int UNSIGNED NOT NULL,
  `actual_qty` int UNSIGNED DEFAULT NULL,
  `confidence` decimal(5,2) DEFAULT NULL COMMENT 'Phần trăm độ tin cậy',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales_predictions`
--

INSERT INTO `sales_predictions` (`id`, `branch_id`, `menu_item_id`, `predicted_date`, `predicted_qty`, `actual_qty`, `confidence`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '2026-06-14', 2, NULL, 0.80, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(2, 1, 1, '2026-06-15', 3, NULL, 0.73, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(3, 1, 1, '2026-06-16', 2, NULL, 0.78, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(4, 1, 1, '2026-06-17', 1, NULL, 0.80, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(5, 1, 1, '2026-06-18', 1, NULL, 0.93, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(6, 1, 1, '2026-06-19', 1, NULL, 0.78, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(7, 1, 1, '2026-06-20', 4, NULL, 0.93, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(8, 1, 2, '2026-06-14', 5, NULL, 0.93, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(9, 1, 2, '2026-06-15', 2, NULL, 0.88, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(10, 1, 2, '2026-06-16', 2, NULL, 0.75, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(11, 1, 2, '2026-06-17', 2, NULL, 0.87, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(12, 1, 2, '2026-06-18', 3, NULL, 0.75, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(13, 1, 2, '2026-06-19', 4, NULL, 0.87, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(14, 1, 2, '2026-06-20', 2, NULL, 0.89, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(15, 1, 3, '2026-06-14', 9, NULL, 0.92, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(16, 1, 3, '2026-06-15', 10, NULL, 0.93, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(17, 1, 3, '2026-06-16', 9, NULL, 0.93, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(18, 1, 3, '2026-06-17', 8, NULL, 0.94, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(19, 1, 3, '2026-06-18', 8, NULL, 0.70, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(20, 1, 3, '2026-06-19', 8, NULL, 0.87, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(21, 1, 3, '2026-06-20', 9, NULL, 0.71, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(22, 1, 4, '2026-06-14', 10, NULL, 0.80, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(23, 1, 4, '2026-06-15', 9, NULL, 0.94, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(24, 1, 4, '2026-06-16', 9, NULL, 0.86, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(25, 1, 4, '2026-06-17', 7, NULL, 0.92, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(26, 1, 4, '2026-06-18', 7, NULL, 0.87, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(27, 1, 4, '2026-06-19', 9, NULL, 0.86, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(28, 1, 4, '2026-06-20', 9, NULL, 0.83, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(29, 1, 5, '2026-06-14', 13, NULL, 0.93, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(30, 1, 5, '2026-06-15', 9, NULL, 0.86, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(31, 1, 5, '2026-06-16', 10, NULL, 0.91, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(32, 1, 5, '2026-06-17', 8, NULL, 0.70, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(33, 1, 5, '2026-06-18', 10, NULL, 0.85, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(34, 1, 5, '2026-06-19', 10, NULL, 0.75, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(35, 1, 5, '2026-06-20', 13, NULL, 0.71, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(36, 1, 6, '2026-06-14', 7, NULL, 0.78, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(37, 1, 6, '2026-06-15', 5, NULL, 0.84, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(38, 1, 6, '2026-06-16', 6, NULL, 0.95, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(39, 1, 6, '2026-06-17', 6, NULL, 0.72, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(40, 1, 6, '2026-06-18', 3, NULL, 0.73, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(41, 1, 6, '2026-06-19', 4, NULL, 0.75, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(42, 1, 6, '2026-06-20', 6, NULL, 0.75, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(43, 1, 7, '2026-06-14', 11, NULL, 0.95, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(44, 1, 7, '2026-06-15', 10, NULL, 0.90, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(45, 1, 7, '2026-06-16', 11, NULL, 0.95, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(46, 1, 7, '2026-06-17', 8, NULL, 0.77, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(47, 1, 7, '2026-06-18', 10, NULL, 0.73, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(48, 1, 7, '2026-06-19', 10, NULL, 0.80, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(49, 1, 7, '2026-06-20', 13, NULL, 0.78, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(50, 1, 8, '2026-06-14', 2, NULL, 0.90, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(51, 1, 8, '2026-06-15', 4, NULL, 0.75, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(52, 1, 8, '2026-06-16', 2, NULL, 0.79, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(53, 1, 8, '2026-06-17', 4, NULL, 0.91, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(54, 1, 8, '2026-06-18', 1, NULL, 0.74, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(55, 1, 8, '2026-06-19', 2, NULL, 0.82, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(56, 1, 8, '2026-06-20', 2, NULL, 0.87, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(57, 1, 9, '2026-06-14', 14, NULL, 0.85, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(58, 1, 9, '2026-06-15', 11, NULL, 0.72, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(59, 1, 9, '2026-06-16', 9, NULL, 0.84, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(60, 1, 9, '2026-06-17', 10, NULL, 0.86, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(61, 1, 9, '2026-06-18', 11, NULL, 0.94, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(62, 1, 9, '2026-06-19', 10, NULL, 0.73, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(63, 1, 9, '2026-06-20', 11, NULL, 0.75, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(64, 1, 10, '2026-06-14', 7, NULL, 0.88, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(65, 1, 10, '2026-06-15', 3, NULL, 0.85, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(66, 1, 10, '2026-06-16', 4, NULL, 0.92, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(67, 1, 10, '2026-06-17', 4, NULL, 0.85, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(68, 1, 10, '2026-06-18', 5, NULL, 0.74, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(69, 1, 10, '2026-06-19', 4, NULL, 0.76, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(70, 1, 10, '2026-06-20', 7, NULL, 0.74, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(71, 1, 11, '2026-06-14', 10, NULL, 0.95, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(72, 1, 11, '2026-06-15', 5, NULL, 0.81, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(73, 1, 11, '2026-06-16', 5, NULL, 0.88, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(74, 1, 11, '2026-06-17', 6, NULL, 0.79, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(75, 1, 11, '2026-06-18', 5, NULL, 0.73, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(76, 1, 11, '2026-06-19', 8, NULL, 0.87, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(77, 1, 11, '2026-06-20', 8, NULL, 0.87, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(78, 1, 12, '2026-06-14', 3, NULL, 0.88, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(79, 1, 12, '2026-06-15', 4, NULL, 0.74, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(80, 1, 12, '2026-06-16', 4, NULL, 0.91, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(81, 1, 12, '2026-06-17', 3, NULL, 0.70, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(82, 1, 12, '2026-06-18', 2, NULL, 0.77, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(83, 1, 12, '2026-06-19', 4, NULL, 0.90, '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(84, 1, 12, '2026-06-20', 3, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(85, 1, 13, '2026-06-14', 5, NULL, 0.77, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(86, 1, 13, '2026-06-15', 5, NULL, 0.83, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(87, 1, 13, '2026-06-16', 5, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(88, 1, 13, '2026-06-17', 5, NULL, 0.76, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(89, 1, 13, '2026-06-18', 3, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(90, 1, 13, '2026-06-19', 4, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(91, 1, 13, '2026-06-20', 3, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(92, 1, 14, '2026-06-14', 10, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(93, 1, 14, '2026-06-15', 9, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(94, 1, 14, '2026-06-16', 8, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(95, 1, 14, '2026-06-17', 7, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(96, 1, 14, '2026-06-18', 7, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(97, 1, 14, '2026-06-19', 10, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(98, 1, 14, '2026-06-20', 9, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(99, 1, 15, '2026-06-14', 2, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(100, 1, 15, '2026-06-15', 1, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(101, 1, 15, '2026-06-16', 2, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(102, 1, 15, '2026-06-17', 1, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(103, 1, 15, '2026-06-18', 2, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(104, 1, 15, '2026-06-19', 1, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(105, 1, 15, '2026-06-20', 4, NULL, 0.78, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(106, 1, 16, '2026-06-14', 9, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(107, 1, 16, '2026-06-15', 7, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(108, 1, 16, '2026-06-16', 5, NULL, 0.88, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(109, 1, 16, '2026-06-17', 6, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(110, 1, 16, '2026-06-18', 6, NULL, 0.77, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(111, 1, 16, '2026-06-19', 5, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(112, 1, 16, '2026-06-20', 8, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(113, 1, 17, '2026-06-14', 8, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(114, 1, 17, '2026-06-15', 6, NULL, 0.83, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(115, 1, 17, '2026-06-16', 6, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(116, 1, 17, '2026-06-17', 6, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(117, 1, 17, '2026-06-18', 4, NULL, 0.73, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(118, 1, 17, '2026-06-19', 6, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(119, 1, 17, '2026-06-20', 8, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(120, 1, 18, '2026-06-14', 12, NULL, 0.73, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(121, 1, 18, '2026-06-15', 9, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(122, 1, 18, '2026-06-16', 10, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(123, 1, 18, '2026-06-17', 8, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(124, 1, 18, '2026-06-18', 7, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(125, 1, 18, '2026-06-19', 10, NULL, 0.88, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(126, 1, 18, '2026-06-20', 9, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(127, 1, 19, '2026-06-14', 12, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(128, 1, 19, '2026-06-15', 11, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(129, 1, 19, '2026-06-16', 10, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(130, 1, 19, '2026-06-17', 10, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(131, 1, 19, '2026-06-18', 11, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(132, 1, 19, '2026-06-19', 8, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(133, 1, 19, '2026-06-20', 13, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(134, 1, 20, '2026-06-14', 11, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(135, 1, 20, '2026-06-15', 9, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(136, 1, 20, '2026-06-16', 10, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(137, 1, 20, '2026-06-17', 7, NULL, 0.83, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(138, 1, 20, '2026-06-18', 9, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(139, 1, 20, '2026-06-19', 7, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(140, 1, 20, '2026-06-20', 12, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(141, 1, 21, '2026-06-14', 11, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(142, 1, 21, '2026-06-15', 6, NULL, 0.73, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(143, 1, 21, '2026-06-16', 9, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(144, 1, 21, '2026-06-17', 9, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(145, 1, 21, '2026-06-18', 8, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(146, 1, 21, '2026-06-19', 6, NULL, 0.76, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(147, 1, 21, '2026-06-20', 11, NULL, 0.73, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(148, 1, 22, '2026-06-14', 12, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(149, 1, 22, '2026-06-15', 10, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(150, 1, 22, '2026-06-16', 7, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(151, 1, 22, '2026-06-17', 9, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(152, 1, 22, '2026-06-18', 9, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(153, 1, 22, '2026-06-19', 9, NULL, 0.88, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(154, 1, 22, '2026-06-20', 11, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(155, 1, 23, '2026-06-14', 4, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(156, 1, 23, '2026-06-15', 2, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(157, 1, 23, '2026-06-16', 2, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(158, 1, 23, '2026-06-17', 4, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(159, 1, 23, '2026-06-18', 3, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(160, 1, 23, '2026-06-19', 5, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(161, 1, 23, '2026-06-20', 3, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(162, 1, 24, '2026-06-14', 4, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(163, 1, 24, '2026-06-15', 4, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(164, 1, 24, '2026-06-16', 4, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(165, 1, 24, '2026-06-17', 2, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(166, 1, 24, '2026-06-18', 4, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(167, 1, 24, '2026-06-19', 3, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(168, 1, 24, '2026-06-20', 3, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(169, 1, 25, '2026-06-14', 12, NULL, 0.77, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(170, 1, 25, '2026-06-15', 10, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(171, 1, 25, '2026-06-16', 9, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(172, 1, 25, '2026-06-17', 9, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(173, 1, 25, '2026-06-18', 12, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(174, 1, 25, '2026-06-19', 11, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(175, 1, 25, '2026-06-20', 15, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(176, 1, 26, '2026-06-14', 10, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(177, 1, 26, '2026-06-15', 9, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(178, 1, 26, '2026-06-16', 9, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(179, 1, 26, '2026-06-17', 9, NULL, 0.73, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(180, 1, 26, '2026-06-18', 7, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(181, 1, 26, '2026-06-19', 7, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(182, 1, 26, '2026-06-20', 9, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(183, 1, 27, '2026-06-14', 11, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(184, 1, 27, '2026-06-15', 10, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(185, 1, 27, '2026-06-16', 8, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(186, 1, 27, '2026-06-17', 8, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(187, 1, 27, '2026-06-18', 8, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(188, 1, 27, '2026-06-19', 7, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(189, 1, 27, '2026-06-20', 11, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(190, 1, 28, '2026-06-14', 9, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(191, 1, 28, '2026-06-15', 9, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(192, 1, 28, '2026-06-16', 9, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(193, 1, 28, '2026-06-17', 6, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(194, 1, 28, '2026-06-18', 9, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(195, 1, 28, '2026-06-19', 8, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(196, 1, 28, '2026-06-20', 8, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(197, 1, 29, '2026-06-14', 5, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(198, 1, 29, '2026-06-15', 3, NULL, 0.77, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(199, 1, 29, '2026-06-16', 6, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(200, 1, 29, '2026-06-17', 3, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(201, 1, 29, '2026-06-18', 4, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(202, 1, 29, '2026-06-19', 6, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(203, 1, 29, '2026-06-20', 7, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(204, 1, 30, '2026-06-14', 14, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(205, 1, 30, '2026-06-15', 11, NULL, 0.76, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(206, 1, 30, '2026-06-16', 11, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(207, 1, 30, '2026-06-17', 10, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(208, 1, 30, '2026-06-18', 10, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(209, 1, 30, '2026-06-19', 10, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(210, 1, 30, '2026-06-20', 14, NULL, 0.88, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(211, 1, 31, '2026-06-14', 4, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(212, 1, 31, '2026-06-15', 4, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(213, 1, 31, '2026-06-16', 6, NULL, 0.76, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(214, 1, 31, '2026-06-17', 3, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(215, 1, 31, '2026-06-18', 3, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(216, 1, 31, '2026-06-19', 3, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(217, 1, 31, '2026-06-20', 6, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(218, 1, 32, '2026-06-14', 2, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(219, 1, 32, '2026-06-15', 3, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(220, 1, 32, '2026-06-16', 2, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(221, 1, 32, '2026-06-17', 3, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(222, 1, 32, '2026-06-18', 2, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(223, 1, 32, '2026-06-19', 2, NULL, 0.88, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(224, 1, 32, '2026-06-20', 4, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(225, 1, 33, '2026-06-14', 3, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(226, 1, 33, '2026-06-15', 2, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(227, 1, 33, '2026-06-16', 2, NULL, 0.78, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(228, 1, 33, '2026-06-17', 2, NULL, 0.77, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(229, 1, 33, '2026-06-18', 3, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(230, 1, 33, '2026-06-19', 5, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(231, 1, 33, '2026-06-20', 6, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(232, 1, 34, '2026-06-14', 12, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(233, 1, 34, '2026-06-15', 9, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(234, 1, 34, '2026-06-16', 9, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(235, 1, 34, '2026-06-17', 10, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(236, 1, 34, '2026-06-18', 12, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(237, 1, 34, '2026-06-19', 10, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(238, 1, 34, '2026-06-20', 15, NULL, 0.83, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(239, 1, 35, '2026-06-14', 5, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(240, 1, 35, '2026-06-15', 5, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(241, 1, 35, '2026-06-16', 5, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(242, 1, 35, '2026-06-17', 2, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(243, 1, 35, '2026-06-18', 2, NULL, 0.83, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(244, 1, 35, '2026-06-19', 3, NULL, 0.77, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(245, 1, 35, '2026-06-20', 6, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(246, 1, 36, '2026-06-14', 4, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(247, 1, 36, '2026-06-15', 4, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(248, 1, 36, '2026-06-16', 5, NULL, 0.83, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(249, 1, 36, '2026-06-17', 4, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(250, 1, 36, '2026-06-18', 6, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(251, 1, 36, '2026-06-19', 3, NULL, 0.76, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(252, 1, 36, '2026-06-20', 7, NULL, 0.88, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(253, 1, 37, '2026-06-14', 12, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(254, 1, 37, '2026-06-15', 10, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(255, 1, 37, '2026-06-16', 10, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(256, 1, 37, '2026-06-17', 11, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(257, 1, 37, '2026-06-18', 8, NULL, 0.78, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(258, 1, 37, '2026-06-19', 11, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(259, 1, 37, '2026-06-20', 12, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(260, 1, 38, '2026-06-14', 3, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(261, 1, 38, '2026-06-15', 3, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(262, 1, 38, '2026-06-16', 1, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(263, 1, 38, '2026-06-17', 1, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(264, 1, 38, '2026-06-18', 1, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(265, 1, 38, '2026-06-19', 3, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(266, 1, 38, '2026-06-20', 1, NULL, 0.73, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(267, 1, 39, '2026-06-14', 9, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(268, 1, 39, '2026-06-15', 6, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(269, 1, 39, '2026-06-16', 4, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(270, 1, 39, '2026-06-17', 4, NULL, 0.88, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(271, 1, 39, '2026-06-18', 5, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(272, 1, 39, '2026-06-19', 7, NULL, 0.76, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(273, 1, 39, '2026-06-20', 8, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(274, 2, 1, '2026-06-14', 14, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(275, 2, 1, '2026-06-15', 11, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(276, 2, 1, '2026-06-16', 11, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(277, 2, 1, '2026-06-17', 9, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(278, 2, 1, '2026-06-18', 11, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(279, 2, 1, '2026-06-19', 9, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(280, 2, 1, '2026-06-20', 14, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(281, 2, 2, '2026-06-14', 3, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(282, 2, 2, '2026-06-15', 3, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(283, 2, 2, '2026-06-16', 5, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(284, 2, 2, '2026-06-17', 5, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(285, 2, 2, '2026-06-18', 2, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(286, 2, 2, '2026-06-19', 4, NULL, 0.73, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(287, 2, 2, '2026-06-20', 3, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(288, 2, 3, '2026-06-14', 2, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(289, 2, 3, '2026-06-15', 3, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(290, 2, 3, '2026-06-16', 1, NULL, 0.78, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(291, 2, 3, '2026-06-17', 4, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(292, 2, 3, '2026-06-18', 3, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(293, 2, 3, '2026-06-19', 2, NULL, 0.88, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(294, 2, 3, '2026-06-20', 2, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(295, 2, 4, '2026-06-14', 7, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(296, 2, 4, '2026-06-15', 3, NULL, 0.88, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(297, 2, 4, '2026-06-16', 6, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(298, 2, 4, '2026-06-17', 3, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(299, 2, 4, '2026-06-18', 6, NULL, 0.78, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(300, 2, 4, '2026-06-19', 3, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(301, 2, 4, '2026-06-20', 6, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(302, 2, 5, '2026-06-14', 14, NULL, 0.76, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(303, 2, 5, '2026-06-15', 9, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(304, 2, 5, '2026-06-16', 8, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(305, 2, 5, '2026-06-17', 8, NULL, 0.76, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(306, 2, 5, '2026-06-18', 8, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(307, 2, 5, '2026-06-19', 9, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(308, 2, 5, '2026-06-20', 14, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(309, 2, 6, '2026-06-14', 11, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(310, 2, 6, '2026-06-15', 6, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(311, 2, 6, '2026-06-16', 9, NULL, 0.83, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(312, 2, 6, '2026-06-17', 6, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(313, 2, 6, '2026-06-18', 9, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(314, 2, 6, '2026-06-19', 6, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(315, 2, 6, '2026-06-20', 8, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(316, 2, 7, '2026-06-14', 4, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(317, 2, 7, '2026-06-15', 4, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(318, 2, 7, '2026-06-16', 6, NULL, 0.77, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(319, 2, 7, '2026-06-17', 5, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(320, 2, 7, '2026-06-18', 4, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(321, 2, 7, '2026-06-19', 4, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(322, 2, 7, '2026-06-20', 7, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(323, 2, 8, '2026-06-14', 5, NULL, 0.77, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(324, 2, 8, '2026-06-15', 1, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(325, 2, 8, '2026-06-16', 4, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(326, 2, 8, '2026-06-17', 1, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(327, 2, 8, '2026-06-18', 4, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(328, 2, 8, '2026-06-19', 3, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(329, 2, 8, '2026-06-20', 4, NULL, 0.73, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(330, 2, 9, '2026-06-14', 3, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(331, 2, 9, '2026-06-15', 4, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(332, 2, 9, '2026-06-16', 2, NULL, 0.73, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(333, 2, 9, '2026-06-17', 4, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(334, 2, 9, '2026-06-18', 1, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(335, 2, 9, '2026-06-19', 1, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(336, 2, 9, '2026-06-20', 2, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(337, 2, 10, '2026-06-14', 11, NULL, 0.78, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(338, 2, 10, '2026-06-15', 9, NULL, 0.77, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(339, 2, 10, '2026-06-16', 7, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(340, 2, 10, '2026-06-17', 7, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(341, 2, 10, '2026-06-18', 7, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(342, 2, 10, '2026-06-19', 10, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(343, 2, 10, '2026-06-20', 10, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(344, 2, 11, '2026-06-14', 9, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(345, 2, 11, '2026-06-15', 8, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(346, 2, 11, '2026-06-16', 9, NULL, 0.73, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(347, 2, 11, '2026-06-17', 8, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(348, 2, 11, '2026-06-18', 7, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(349, 2, 11, '2026-06-19', 7, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(350, 2, 11, '2026-06-20', 12, NULL, 0.76, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(351, 2, 12, '2026-06-14', 8, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(352, 2, 12, '2026-06-15', 6, NULL, 0.77, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(353, 2, 12, '2026-06-16', 9, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(354, 2, 12, '2026-06-17', 6, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(355, 2, 12, '2026-06-18', 9, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(356, 2, 12, '2026-06-19', 7, NULL, 0.76, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(357, 2, 12, '2026-06-20', 9, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(358, 2, 13, '2026-06-14', 9, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(359, 2, 13, '2026-06-15', 7, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(360, 2, 13, '2026-06-16', 9, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(361, 2, 13, '2026-06-17', 7, NULL, 0.76, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(362, 2, 13, '2026-06-18', 9, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(363, 2, 13, '2026-06-19', 8, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(364, 2, 13, '2026-06-20', 9, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(365, 2, 14, '2026-06-14', 3, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(366, 2, 14, '2026-06-15', 1, NULL, 0.88, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(367, 2, 14, '2026-06-16', 4, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(368, 2, 14, '2026-06-17', 3, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(369, 2, 14, '2026-06-18', 3, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(370, 2, 14, '2026-06-19', 4, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(371, 2, 14, '2026-06-20', 4, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(372, 2, 15, '2026-06-14', 9, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(373, 2, 15, '2026-06-15', 7, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(374, 2, 15, '2026-06-16', 4, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(375, 2, 15, '2026-06-17', 5, NULL, 0.83, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(376, 2, 15, '2026-06-18', 5, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(377, 2, 15, '2026-06-19', 5, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(378, 2, 15, '2026-06-20', 9, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(379, 2, 16, '2026-06-14', 4, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(380, 2, 16, '2026-06-15', 4, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(381, 2, 16, '2026-06-16', 2, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(382, 2, 16, '2026-06-17', 3, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(383, 2, 16, '2026-06-18', 1, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(384, 2, 16, '2026-06-19', 3, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(385, 2, 16, '2026-06-20', 5, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(386, 2, 17, '2026-06-14', 11, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(387, 2, 17, '2026-06-15', 10, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(388, 2, 17, '2026-06-16', 10, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(389, 2, 17, '2026-06-17', 8, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(390, 2, 17, '2026-06-18', 7, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(391, 2, 17, '2026-06-19', 10, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(392, 2, 17, '2026-06-20', 12, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(393, 2, 18, '2026-06-14', 14, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(394, 2, 18, '2026-06-15', 12, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(395, 2, 18, '2026-06-16', 10, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(396, 2, 18, '2026-06-17', 10, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(397, 2, 18, '2026-06-18', 9, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(398, 2, 18, '2026-06-19', 10, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(399, 2, 18, '2026-06-20', 12, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(400, 2, 19, '2026-06-14', 11, NULL, 0.88, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(401, 2, 19, '2026-06-15', 9, NULL, 0.88, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(402, 2, 19, '2026-06-16', 10, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(403, 2, 19, '2026-06-17', 10, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(404, 2, 19, '2026-06-18', 11, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(405, 2, 19, '2026-06-19', 10, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(406, 2, 19, '2026-06-20', 13, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(407, 2, 20, '2026-06-14', 12, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(408, 2, 20, '2026-06-15', 11, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(409, 2, 20, '2026-06-16', 12, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(410, 2, 20, '2026-06-17', 12, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(411, 2, 20, '2026-06-18', 12, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(412, 2, 20, '2026-06-19', 10, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(413, 2, 20, '2026-06-20', 15, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(414, 2, 21, '2026-06-14', 6, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(415, 2, 21, '2026-06-15', 4, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(416, 2, 21, '2026-06-16', 6, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(417, 2, 21, '2026-06-17', 4, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(418, 2, 21, '2026-06-18', 6, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(419, 2, 21, '2026-06-19', 3, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(420, 2, 21, '2026-06-20', 5, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(421, 2, 22, '2026-06-14', 8, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(422, 2, 22, '2026-06-15', 8, NULL, 0.78, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(423, 2, 22, '2026-06-16', 8, NULL, 0.73, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(424, 2, 22, '2026-06-17', 5, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(425, 2, 22, '2026-06-18', 8, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(426, 2, 22, '2026-06-19', 5, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(427, 2, 22, '2026-06-20', 9, NULL, 0.77, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(428, 2, 23, '2026-06-14', 8, NULL, 0.88, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(429, 2, 23, '2026-06-15', 6, NULL, 0.83, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(430, 2, 23, '2026-06-16', 8, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(431, 2, 23, '2026-06-17', 7, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(432, 2, 23, '2026-06-18', 8, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(433, 2, 23, '2026-06-19', 7, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(434, 2, 23, '2026-06-20', 9, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(435, 2, 24, '2026-06-14', 5, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(436, 2, 24, '2026-06-15', 2, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(437, 2, 24, '2026-06-16', 3, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(438, 2, 24, '2026-06-17', 2, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(439, 2, 24, '2026-06-18', 2, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(440, 2, 24, '2026-06-19', 5, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(441, 2, 24, '2026-06-20', 3, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(442, 2, 25, '2026-06-14', 10, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(443, 2, 25, '2026-06-15', 8, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(444, 2, 25, '2026-06-16', 8, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(445, 2, 25, '2026-06-17', 8, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(446, 2, 25, '2026-06-18', 7, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(447, 2, 25, '2026-06-19', 7, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(448, 2, 25, '2026-06-20', 10, NULL, 0.77, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(449, 2, 26, '2026-06-14', 11, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(450, 2, 26, '2026-06-15', 6, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(451, 2, 26, '2026-06-16', 7, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(452, 2, 26, '2026-06-17', 8, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(453, 2, 26, '2026-06-18', 8, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(454, 2, 26, '2026-06-19', 7, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(455, 2, 26, '2026-06-20', 11, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(456, 2, 27, '2026-06-14', 5, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(457, 2, 27, '2026-06-15', 6, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(458, 2, 27, '2026-06-16', 5, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(459, 2, 27, '2026-06-17', 3, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(460, 2, 27, '2026-06-18', 3, NULL, 0.83, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(461, 2, 27, '2026-06-19', 6, NULL, 0.83, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(462, 2, 27, '2026-06-20', 5, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(463, 2, 28, '2026-06-14', 9, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(464, 2, 28, '2026-06-15', 8, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(465, 2, 28, '2026-06-16', 7, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(466, 2, 28, '2026-06-17', 9, NULL, 0.83, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(467, 2, 28, '2026-06-18', 7, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(468, 2, 28, '2026-06-19', 7, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(469, 2, 28, '2026-06-20', 9, NULL, 0.77, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(470, 2, 29, '2026-06-14', 2, NULL, 0.77, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(471, 2, 29, '2026-06-15', 2, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(472, 2, 29, '2026-06-16', 1, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(473, 2, 29, '2026-06-17', 2, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(474, 2, 29, '2026-06-18', 1, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(475, 2, 29, '2026-06-19', 2, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(476, 2, 29, '2026-06-20', 2, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(477, 2, 30, '2026-06-14', 4, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(478, 2, 30, '2026-06-15', 3, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(479, 2, 30, '2026-06-16', 3, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(480, 2, 30, '2026-06-17', 5, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(481, 2, 30, '2026-06-18', 4, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(482, 2, 30, '2026-06-19', 2, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(483, 2, 30, '2026-06-20', 5, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(484, 2, 31, '2026-06-14', 2, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(485, 2, 31, '2026-06-15', 1, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(486, 2, 31, '2026-06-16', 1, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(487, 2, 31, '2026-06-17', 2, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(488, 2, 31, '2026-06-18', 2, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(489, 2, 31, '2026-06-19', 1, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(490, 2, 31, '2026-06-20', 2, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(491, 2, 32, '2026-06-14', 10, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(492, 2, 32, '2026-06-15', 7, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(493, 2, 32, '2026-06-16', 7, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(494, 2, 32, '2026-06-17', 10, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(495, 2, 32, '2026-06-18', 10, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(496, 2, 32, '2026-06-19', 7, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(497, 2, 32, '2026-06-20', 12, NULL, 0.78, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(498, 2, 33, '2026-06-14', 10, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(499, 2, 33, '2026-06-15', 8, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(500, 2, 33, '2026-06-16', 7, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(501, 2, 33, '2026-06-17', 8, NULL, 0.73, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(502, 2, 33, '2026-06-18', 8, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(503, 2, 33, '2026-06-19', 8, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(504, 2, 33, '2026-06-20', 11, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(505, 2, 34, '2026-06-14', 5, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(506, 2, 34, '2026-06-15', 4, NULL, 0.73, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(507, 2, 34, '2026-06-16', 3, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(508, 2, 34, '2026-06-17', 3, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(509, 2, 34, '2026-06-18', 4, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(510, 2, 34, '2026-06-19', 5, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(511, 2, 34, '2026-06-20', 4, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(512, 2, 35, '2026-06-14', 7, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(513, 2, 35, '2026-06-15', 5, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(514, 2, 35, '2026-06-16', 6, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(515, 2, 35, '2026-06-17', 7, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(516, 2, 35, '2026-06-18', 8, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(517, 2, 35, '2026-06-19', 5, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(518, 2, 35, '2026-06-20', 9, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(519, 2, 36, '2026-06-14', 7, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(520, 2, 36, '2026-06-15', 6, NULL, 0.73, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(521, 2, 36, '2026-06-16', 8, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(522, 2, 36, '2026-06-17', 7, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(523, 2, 36, '2026-06-18', 7, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(524, 2, 36, '2026-06-19', 8, NULL, 0.73, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(525, 2, 36, '2026-06-20', 8, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(526, 2, 37, '2026-06-14', 11, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(527, 2, 37, '2026-06-15', 6, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(528, 2, 37, '2026-06-16', 6, NULL, 0.77, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(529, 2, 37, '2026-06-17', 7, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(530, 2, 37, '2026-06-18', 6, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(531, 2, 37, '2026-06-19', 6, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(532, 2, 37, '2026-06-20', 11, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(533, 2, 38, '2026-06-14', 9, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(534, 2, 38, '2026-06-15', 5, NULL, 0.86, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(535, 2, 38, '2026-06-16', 7, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(536, 2, 38, '2026-06-17', 5, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(537, 2, 38, '2026-06-18', 5, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(538, 2, 38, '2026-06-19', 5, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(539, 2, 38, '2026-06-20', 10, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(540, 2, 39, '2026-06-14', 9, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(541, 2, 39, '2026-06-15', 9, NULL, 0.83, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(542, 2, 39, '2026-06-16', 7, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(543, 2, 39, '2026-06-17', 9, NULL, 0.78, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(544, 2, 39, '2026-06-18', 8, NULL, 0.79, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(545, 2, 39, '2026-06-19', 7, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(546, 2, 39, '2026-06-20', 11, NULL, 0.88, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(547, 3, 1, '2026-06-14', 8, NULL, 0.95, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(548, 3, 1, '2026-06-15', 5, NULL, 0.78, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(549, 3, 1, '2026-06-16', 6, NULL, 0.75, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(550, 3, 1, '2026-06-17', 7, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(551, 3, 1, '2026-06-18', 6, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(552, 3, 1, '2026-06-19', 5, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(553, 3, 1, '2026-06-20', 7, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(554, 3, 2, '2026-06-14', 4, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(555, 3, 2, '2026-06-15', 2, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(556, 3, 2, '2026-06-16', 2, NULL, 0.87, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(557, 3, 2, '2026-06-17', 1, NULL, 0.76, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(558, 3, 2, '2026-06-18', 1, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(559, 3, 2, '2026-06-19', 1, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(560, 3, 2, '2026-06-20', 5, NULL, 0.76, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(561, 3, 3, '2026-06-14', 5, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(562, 3, 3, '2026-06-15', 2, NULL, 0.76, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(563, 3, 3, '2026-06-16', 3, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(564, 3, 3, '2026-06-17', 1, NULL, 0.91, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(565, 3, 3, '2026-06-18', 3, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(566, 3, 3, '2026-06-19', 1, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(567, 3, 3, '2026-06-20', 3, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(568, 3, 4, '2026-06-14', 9, NULL, 0.73, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(569, 3, 4, '2026-06-15', 9, NULL, 0.92, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(570, 3, 4, '2026-06-16', 8, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(571, 3, 4, '2026-06-17', 10, NULL, 0.76, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(572, 3, 4, '2026-06-18', 8, NULL, 0.83, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(573, 3, 4, '2026-06-19', 7, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(574, 3, 4, '2026-06-20', 10, NULL, 0.72, '2026-06-13 00:21:58', '2026-06-13 00:21:58');
INSERT INTO `sales_predictions` (`id`, `branch_id`, `menu_item_id`, `predicted_date`, `predicted_qty`, `actual_qty`, `confidence`, `created_at`, `updated_at`) VALUES
(575, 3, 5, '2026-06-14', 7, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(576, 3, 5, '2026-06-15', 7, NULL, 0.71, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(577, 3, 5, '2026-06-16', 8, NULL, 0.70, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(578, 3, 5, '2026-06-17', 8, NULL, 0.90, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(579, 3, 5, '2026-06-18', 5, NULL, 0.93, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(580, 3, 5, '2026-06-19', 5, NULL, 0.81, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(581, 3, 5, '2026-06-20', 7, NULL, 0.80, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(582, 3, 6, '2026-06-14', 9, NULL, 0.76, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(583, 3, 6, '2026-06-15', 5, NULL, 0.74, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(584, 3, 6, '2026-06-16', 5, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(585, 3, 6, '2026-06-17', 4, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(586, 3, 6, '2026-06-18', 6, NULL, 0.84, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(587, 3, 6, '2026-06-19', 5, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(588, 3, 6, '2026-06-20', 6, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(589, 3, 7, '2026-06-14', 7, NULL, 0.82, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(590, 3, 7, '2026-06-15', 3, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(591, 3, 7, '2026-06-16', 6, NULL, 0.85, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(592, 3, 7, '2026-06-17', 4, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(593, 3, 7, '2026-06-18', 3, NULL, 0.89, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(594, 3, 7, '2026-06-19', 5, NULL, 0.94, '2026-06-13 00:21:58', '2026-06-13 00:21:58'),
(595, 3, 7, '2026-06-20', 7, NULL, 0.88, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(596, 3, 8, '2026-06-14', 4, NULL, 0.82, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(597, 3, 8, '2026-06-15', 3, NULL, 0.78, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(598, 3, 8, '2026-06-16', 2, NULL, 0.77, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(599, 3, 8, '2026-06-17', 3, NULL, 0.90, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(600, 3, 8, '2026-06-18', 2, NULL, 0.79, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(601, 3, 8, '2026-06-19', 2, NULL, 0.82, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(602, 3, 8, '2026-06-20', 5, NULL, 0.83, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(603, 3, 9, '2026-06-14', 9, NULL, 0.80, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(604, 3, 9, '2026-06-15', 8, NULL, 0.73, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(605, 3, 9, '2026-06-16', 6, NULL, 0.88, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(606, 3, 9, '2026-06-17', 9, NULL, 0.81, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(607, 3, 9, '2026-06-18', 6, NULL, 0.73, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(608, 3, 9, '2026-06-19', 6, NULL, 0.94, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(609, 3, 9, '2026-06-20', 11, NULL, 0.80, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(610, 3, 10, '2026-06-14', 14, NULL, 0.93, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(611, 3, 10, '2026-06-15', 9, NULL, 0.75, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(612, 3, 10, '2026-06-16', 11, NULL, 0.94, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(613, 3, 10, '2026-06-17', 9, NULL, 0.75, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(614, 3, 10, '2026-06-18', 8, NULL, 0.71, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(615, 3, 10, '2026-06-19', 10, NULL, 0.95, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(616, 3, 10, '2026-06-20', 14, NULL, 0.83, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(617, 3, 11, '2026-06-14', 3, NULL, 0.71, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(618, 3, 11, '2026-06-15', 2, NULL, 0.92, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(619, 3, 11, '2026-06-16', 2, NULL, 0.87, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(620, 3, 11, '2026-06-17', 1, NULL, 0.88, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(621, 3, 11, '2026-06-18', 2, NULL, 0.80, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(622, 3, 11, '2026-06-19', 3, NULL, 0.77, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(623, 3, 11, '2026-06-20', 3, NULL, 0.76, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(624, 3, 12, '2026-06-14', 10, NULL, 0.77, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(625, 3, 12, '2026-06-15', 7, NULL, 0.82, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(626, 3, 12, '2026-06-16', 7, NULL, 0.92, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(627, 3, 12, '2026-06-17', 8, NULL, 0.71, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(628, 3, 12, '2026-06-18', 8, NULL, 0.76, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(629, 3, 12, '2026-06-19', 10, NULL, 0.88, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(630, 3, 12, '2026-06-20', 12, NULL, 0.74, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(631, 3, 13, '2026-06-14', 7, NULL, 0.79, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(632, 3, 13, '2026-06-15', 8, NULL, 0.76, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(633, 3, 13, '2026-06-16', 5, NULL, 0.72, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(634, 3, 13, '2026-06-17', 8, NULL, 0.93, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(635, 3, 13, '2026-06-18', 5, NULL, 0.74, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(636, 3, 13, '2026-06-19', 6, NULL, 0.93, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(637, 3, 13, '2026-06-20', 7, NULL, 0.93, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(638, 3, 14, '2026-06-14', 6, NULL, 0.81, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(639, 3, 14, '2026-06-15', 4, NULL, 0.75, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(640, 3, 14, '2026-06-16', 5, NULL, 0.91, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(641, 3, 14, '2026-06-17', 4, NULL, 0.80, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(642, 3, 14, '2026-06-18', 3, NULL, 0.87, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(643, 3, 14, '2026-06-19', 6, NULL, 0.83, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(644, 3, 14, '2026-06-20', 7, NULL, 0.72, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(645, 3, 15, '2026-06-14', 6, NULL, 0.89, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(646, 3, 15, '2026-06-15', 3, NULL, 0.89, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(647, 3, 15, '2026-06-16', 4, NULL, 0.91, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(648, 3, 15, '2026-06-17', 5, NULL, 0.87, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(649, 3, 15, '2026-06-18', 4, NULL, 0.84, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(650, 3, 15, '2026-06-19', 3, NULL, 0.89, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(651, 3, 15, '2026-06-20', 3, NULL, 0.85, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(652, 3, 16, '2026-06-14', 2, NULL, 0.87, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(653, 3, 16, '2026-06-15', 3, NULL, 0.71, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(654, 3, 16, '2026-06-16', 4, NULL, 0.80, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(655, 3, 16, '2026-06-17', 3, NULL, 0.71, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(656, 3, 16, '2026-06-18', 2, NULL, 0.94, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(657, 3, 16, '2026-06-19', 4, NULL, 0.92, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(658, 3, 16, '2026-06-20', 3, NULL, 0.75, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(659, 3, 17, '2026-06-14', 9, NULL, 0.76, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(660, 3, 17, '2026-06-15', 4, NULL, 0.76, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(661, 3, 17, '2026-06-16', 7, NULL, 0.90, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(662, 3, 17, '2026-06-17', 5, NULL, 0.84, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(663, 3, 17, '2026-06-18', 7, NULL, 0.75, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(664, 3, 17, '2026-06-19', 5, NULL, 0.77, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(665, 3, 17, '2026-06-20', 8, NULL, 0.83, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(666, 3, 18, '2026-06-14', 4, NULL, 0.82, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(667, 3, 18, '2026-06-15', 2, NULL, 0.79, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(668, 3, 18, '2026-06-16', 1, NULL, 0.75, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(669, 3, 18, '2026-06-17', 3, NULL, 0.70, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(670, 3, 18, '2026-06-18', 2, NULL, 0.89, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(671, 3, 18, '2026-06-19', 2, NULL, 0.70, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(672, 3, 18, '2026-06-20', 2, NULL, 0.89, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(673, 3, 19, '2026-06-14', 10, NULL, 0.78, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(674, 3, 19, '2026-06-15', 7, NULL, 0.85, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(675, 3, 19, '2026-06-16', 9, NULL, 0.90, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(676, 3, 19, '2026-06-17', 9, NULL, 0.88, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(677, 3, 19, '2026-06-18', 7, NULL, 0.87, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(678, 3, 19, '2026-06-19', 9, NULL, 0.76, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(679, 3, 19, '2026-06-20', 11, NULL, 0.94, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(680, 3, 20, '2026-06-14', 1, NULL, 0.84, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(681, 3, 20, '2026-06-15', 1, NULL, 0.84, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(682, 3, 20, '2026-06-16', 1, NULL, 0.78, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(683, 3, 20, '2026-06-17', 3, NULL, 0.90, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(684, 3, 20, '2026-06-18', 3, NULL, 0.85, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(685, 3, 20, '2026-06-19', 3, NULL, 0.78, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(686, 3, 20, '2026-06-20', 1, NULL, 0.95, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(687, 3, 21, '2026-06-14', 11, NULL, 0.83, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(688, 3, 21, '2026-06-15', 10, NULL, 0.92, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(689, 3, 21, '2026-06-16', 9, NULL, 0.71, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(690, 3, 21, '2026-06-17', 9, NULL, 0.83, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(691, 3, 21, '2026-06-18', 8, NULL, 0.73, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(692, 3, 21, '2026-06-19', 8, NULL, 0.75, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(693, 3, 21, '2026-06-20', 10, NULL, 0.80, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(694, 3, 22, '2026-06-14', 3, NULL, 0.85, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(695, 3, 22, '2026-06-15', 1, NULL, 0.73, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(696, 3, 22, '2026-06-16', 3, NULL, 0.82, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(697, 3, 22, '2026-06-17', 4, NULL, 0.72, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(698, 3, 22, '2026-06-18', 4, NULL, 0.75, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(699, 3, 22, '2026-06-19', 3, NULL, 0.95, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(700, 3, 22, '2026-06-20', 4, NULL, 0.90, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(701, 3, 23, '2026-06-14', 4, NULL, 0.93, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(702, 3, 23, '2026-06-15', 3, NULL, 0.88, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(703, 3, 23, '2026-06-16', 5, NULL, 0.95, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(704, 3, 23, '2026-06-17', 4, NULL, 0.95, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(705, 3, 23, '2026-06-18', 4, NULL, 0.89, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(706, 3, 23, '2026-06-19', 3, NULL, 0.80, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(707, 3, 23, '2026-06-20', 3, NULL, 0.71, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(708, 3, 24, '2026-06-14', 9, NULL, 0.70, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(709, 3, 24, '2026-06-15', 5, NULL, 0.73, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(710, 3, 24, '2026-06-16', 7, NULL, 0.70, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(711, 3, 24, '2026-06-17', 6, NULL, 0.70, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(712, 3, 24, '2026-06-18', 5, NULL, 0.91, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(713, 3, 24, '2026-06-19', 8, NULL, 0.72, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(714, 3, 24, '2026-06-20', 7, NULL, 0.91, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(715, 3, 25, '2026-06-14', 2, NULL, 0.80, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(716, 3, 25, '2026-06-15', 4, NULL, 0.95, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(717, 3, 25, '2026-06-16', 2, NULL, 0.83, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(718, 3, 25, '2026-06-17', 4, NULL, 0.70, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(719, 3, 25, '2026-06-18', 3, NULL, 0.95, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(720, 3, 25, '2026-06-19', 3, NULL, 0.87, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(721, 3, 25, '2026-06-20', 5, NULL, 0.81, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(722, 3, 26, '2026-06-14', 5, NULL, 0.86, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(723, 3, 26, '2026-06-15', 2, NULL, 0.95, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(724, 3, 26, '2026-06-16', 4, NULL, 0.70, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(725, 3, 26, '2026-06-17', 4, NULL, 0.88, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(726, 3, 26, '2026-06-18', 5, NULL, 0.86, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(727, 3, 26, '2026-06-19', 5, NULL, 0.84, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(728, 3, 26, '2026-06-20', 6, NULL, 0.71, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(729, 3, 27, '2026-06-14', 11, NULL, 0.78, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(730, 3, 27, '2026-06-15', 10, NULL, 0.70, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(731, 3, 27, '2026-06-16', 11, NULL, 0.89, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(732, 3, 27, '2026-06-17', 8, NULL, 0.78, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(733, 3, 27, '2026-06-18', 11, NULL, 0.84, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(734, 3, 27, '2026-06-19', 8, NULL, 0.79, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(735, 3, 27, '2026-06-20', 11, NULL, 0.94, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(736, 3, 28, '2026-06-14', 3, NULL, 0.94, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(737, 3, 28, '2026-06-15', 3, NULL, 0.77, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(738, 3, 28, '2026-06-16', 1, NULL, 0.90, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(739, 3, 28, '2026-06-17', 3, NULL, 0.94, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(740, 3, 28, '2026-06-18', 3, NULL, 0.93, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(741, 3, 28, '2026-06-19', 3, NULL, 0.79, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(742, 3, 28, '2026-06-20', 4, NULL, 0.74, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(743, 3, 29, '2026-06-14', 8, NULL, 0.80, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(744, 3, 29, '2026-06-15', 4, NULL, 0.70, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(745, 3, 29, '2026-06-16', 6, NULL, 0.82, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(746, 3, 29, '2026-06-17', 4, NULL, 0.74, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(747, 3, 29, '2026-06-18', 5, NULL, 0.84, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(748, 3, 29, '2026-06-19', 4, NULL, 0.87, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(749, 3, 29, '2026-06-20', 7, NULL, 0.72, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(750, 3, 30, '2026-06-14', 12, NULL, 0.83, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(751, 3, 30, '2026-06-15', 10, NULL, 0.76, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(752, 3, 30, '2026-06-16', 8, NULL, 0.83, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(753, 3, 30, '2026-06-17', 9, NULL, 0.76, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(754, 3, 30, '2026-06-18', 9, NULL, 0.81, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(755, 3, 30, '2026-06-19', 8, NULL, 0.94, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(756, 3, 30, '2026-06-20', 13, NULL, 0.85, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(757, 3, 31, '2026-06-14', 6, NULL, 0.92, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(758, 3, 31, '2026-06-15', 6, NULL, 0.84, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(759, 3, 31, '2026-06-16', 6, NULL, 0.80, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(760, 3, 31, '2026-06-17', 5, NULL, 0.81, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(761, 3, 31, '2026-06-18', 3, NULL, 0.89, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(762, 3, 31, '2026-06-19', 6, NULL, 0.74, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(763, 3, 31, '2026-06-20', 7, NULL, 0.79, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(764, 3, 32, '2026-06-14', 8, NULL, 0.73, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(765, 3, 32, '2026-06-15', 9, NULL, 0.78, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(766, 3, 32, '2026-06-16', 6, NULL, 0.77, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(767, 3, 32, '2026-06-17', 8, NULL, 0.91, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(768, 3, 32, '2026-06-18', 6, NULL, 0.80, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(769, 3, 32, '2026-06-19', 9, NULL, 0.83, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(770, 3, 32, '2026-06-20', 11, NULL, 0.76, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(771, 3, 33, '2026-06-14', 9, NULL, 0.90, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(772, 3, 33, '2026-06-15', 8, NULL, 0.82, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(773, 3, 33, '2026-06-16', 5, NULL, 0.87, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(774, 3, 33, '2026-06-17', 5, NULL, 0.71, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(775, 3, 33, '2026-06-18', 6, NULL, 0.70, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(776, 3, 33, '2026-06-19', 8, NULL, 0.84, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(777, 3, 33, '2026-06-20', 8, NULL, 0.86, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(778, 3, 34, '2026-06-14', 4, NULL, 0.75, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(779, 3, 34, '2026-06-15', 4, NULL, 0.72, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(780, 3, 34, '2026-06-16', 2, NULL, 0.86, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(781, 3, 34, '2026-06-17', 2, NULL, 0.82, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(782, 3, 34, '2026-06-18', 3, NULL, 0.76, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(783, 3, 34, '2026-06-19', 1, NULL, 0.82, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(784, 3, 34, '2026-06-20', 5, NULL, 0.91, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(785, 3, 35, '2026-06-14', 3, NULL, 0.91, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(786, 3, 35, '2026-06-15', 3, NULL, 0.82, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(787, 3, 35, '2026-06-16', 4, NULL, 0.74, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(788, 3, 35, '2026-06-17', 4, NULL, 0.77, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(789, 3, 35, '2026-06-18', 4, NULL, 0.80, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(790, 3, 35, '2026-06-19', 2, NULL, 0.83, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(791, 3, 35, '2026-06-20', 6, NULL, 0.77, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(792, 3, 36, '2026-06-14', 5, NULL, 0.94, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(793, 3, 36, '2026-06-15', 5, NULL, 0.75, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(794, 3, 36, '2026-06-16', 3, NULL, 0.79, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(795, 3, 36, '2026-06-17', 3, NULL, 0.95, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(796, 3, 36, '2026-06-18', 6, NULL, 0.81, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(797, 3, 36, '2026-06-19', 5, NULL, 0.83, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(798, 3, 36, '2026-06-20', 5, NULL, 0.71, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(799, 3, 37, '2026-06-14', 11, NULL, 0.77, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(800, 3, 37, '2026-06-15', 7, NULL, 0.94, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(801, 3, 37, '2026-06-16', 8, NULL, 0.86, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(802, 3, 37, '2026-06-17', 6, NULL, 0.95, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(803, 3, 37, '2026-06-18', 7, NULL, 0.88, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(804, 3, 37, '2026-06-19', 8, NULL, 0.87, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(805, 3, 37, '2026-06-20', 8, NULL, 0.70, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(806, 3, 38, '2026-06-14', 5, NULL, 0.89, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(807, 3, 38, '2026-06-15', 3, NULL, 0.90, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(808, 3, 38, '2026-06-16', 3, NULL, 0.85, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(809, 3, 38, '2026-06-17', 5, NULL, 0.78, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(810, 3, 38, '2026-06-18', 4, NULL, 0.82, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(811, 3, 38, '2026-06-19', 3, NULL, 0.88, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(812, 3, 38, '2026-06-20', 6, NULL, 0.74, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(813, 3, 39, '2026-06-14', 8, NULL, 0.82, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(814, 3, 39, '2026-06-15', 4, NULL, 0.87, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(815, 3, 39, '2026-06-16', 7, NULL, 0.79, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(816, 3, 39, '2026-06-17', 7, NULL, 0.89, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(817, 3, 39, '2026-06-18', 6, NULL, 0.78, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(818, 3, 39, '2026-06-19', 6, NULL, 0.89, '2026-06-13 00:21:59', '2026-06-13 00:21:59'),
(819, 3, 39, '2026-06-20', 9, NULL, 0.75, '2026-06-13 00:21:59', '2026-06-13 00:21:59');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('YODqWvTbv0nLYT365aUkKbtcy3aFS9xizPGT4O9m', 6, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiIyV3NYSWs2eFdNWjJEU2VVUDEzTERCNENlRVdKdlU4dk9FNThPRW1JIiwiX2ZsYXNoIjp7Im5ldyI6W10sIm9sZCI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvem9tem9wLnRlc3RcL2Zhdm9yaXRlcyIsInJvdXRlIjoiZmF2b3JpdGVzLmluZGV4In0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjo2fQ==', 1783193625);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `group` enum('general','navbar','footer','cms','seo','payment') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `group`, `created_at`, `updated_at`) VALUES
(1, 'brand_name', 'ZomZop', 'general', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(2, 'brand_slogan', 'Ăn ngon mỗi ngày!', 'general', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(3, 'hotline', '1900 1234', 'general', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(4, 'email', 'contact@zomzop.vn', 'general', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(5, 'logo', 'logo.png', 'general', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(6, 'favicon', 'favicon.ico', 'general', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(7, 'theme_color', '#E53935', 'general', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(8, 'navbar_show_search', '1', 'navbar', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(9, 'navbar_show_cart', '1', 'navbar', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(10, 'navbar_show_wishlist', '1', 'navbar', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(11, 'footer_about', 'ZomZop — Chuỗi cửa hàng đồ ăn nhanh hàng đầu Việt Nam.', 'footer', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(12, 'footer_facebook', 'https://facebook.com/zomzop', 'footer', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(13, 'footer_zalo', 'https://zalo.me/zomzop', 'footer', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(14, 'seo_title', 'ZomZop — Đặt đồ ăn nhanh online', 'seo', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(15, 'seo_description', 'Đặt burger, pizza, gà chiên tươi ngon, giao nhanh tận nơi.', 'seo', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(16, 'seo_keywords', 'đặt đồ ăn, burger, pizza, gà chiên, fast food', 'seo', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(17, 'payment_cash', '1', 'payment', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(18, 'payment_momo', '1', 'payment', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(19, 'payment_vnpay', '1', 'payment', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(20, 'cms_about', 'ZomZop được thành lập năm 2024, với sứ mệnh mang đến những bữa ăn nhanh chất lượng cao cho người Việt.', 'cms', '2026-06-13 00:21:57', '2026-06-13 00:21:57'),
(21, 'cms_policy', 'Chúng tôi cam kết giao hàng trong vòng 30 phút hoặc hoàn tiền.', 'cms', '2026-06-13 00:21:57', '2026-06-13 00:21:57');

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` bigint UNSIGNED NOT NULL,
  `branch_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Ca sáng / Ca chiều / Ca tối',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `branch_id`, `name`, `start_time`, `end_time`, `created_at`, `updated_at`) VALUES
(1, 1, 'Ca Sáng', '07:00:00', '11:30:00', '2026-06-13 00:21:55', '2026-06-13 00:21:55'),
(2, 1, 'Ca Trưa', '11:30:00', '17:00:00', '2026-06-13 00:21:55', '2026-06-13 00:21:55'),
(3, 1, 'Ca Tối', '17:00:00', '22:00:00', '2026-06-13 00:21:55', '2026-06-13 00:21:55'),
(4, 2, 'Ca Sáng', '07:00:00', '11:30:00', '2026-06-13 00:21:55', '2026-06-13 00:21:55'),
(5, 2, 'Ca Trưa', '11:30:00', '17:00:00', '2026-06-13 00:21:55', '2026-06-13 00:21:55'),
(6, 2, 'Ca Tối', '17:00:00', '22:00:00', '2026-06-13 00:21:55', '2026-06-13 00:21:55'),
(7, 3, 'Ca Sáng', '07:00:00', '11:30:00', '2026-06-13 00:21:55', '2026-06-13 00:21:55'),
(8, 3, 'Ca Trưa', '11:30:00', '17:00:00', '2026-06-13 00:21:55', '2026-06-13 00:21:55'),
(9, 3, 'Ca Tối', '17:00:00', '22:00:00', '2026-06-13 00:21:55', '2026-06-13 00:21:55');

-- --------------------------------------------------------

--
-- Table structure for table `support_messages`
--

CREATE TABLE `support_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `sender_id` bigint UNSIGNED NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('open','in_progress','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('customer','kitchen','staff','manager','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'customer',
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `zalo_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zalo_opted_in` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `phone`, `role`, `branch_id`, `address`, `avatar`, `is_active`, `remember_token`, `created_at`, `updated_at`, `deleted_at`, `zalo_id`, `zalo_opted_in`) VALUES
(1, 'Admin ZomZop', 'admin@zomzop.com', NULL, '$2y$12$3gEGRqtzUwVETEKzlQgZt.EEd1PDE0qm5Pp8O6IkoH8aNklLcUs7C', NULL, 'admin', NULL, NULL, NULL, 1, NULL, '2026-06-13 00:21:55', '2026-06-13 00:21:55', NULL, NULL, 0),
(2, 'Nhân viên A', 'staff@zomzop.com', NULL, '$2y$12$WF9NAPljpz.AcUP.C.qNJuYBjCSClKhl9z4VXPZ2sBLrG3L2jqLiu', NULL, 'staff', 1, NULL, NULL, 1, NULL, '2026-06-13 00:21:55', '2026-06-13 00:21:55', NULL, NULL, 0),
(3, 'Khách hàng mẫu', 'customer@zomzop.com', NULL, '$2y$12$rhTohBsdO32lv.LYhhLsDu13YAna4I75iNRZ/H3522At6eASGbq62', '0901234567', 'customer', NULL, NULL, NULL, 1, NULL, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL, NULL, 0),
(4, 'Quản lý Nhà hàng', 'manager@zomzop.com', NULL, '$2y$12$xlpXE99a3V8hTTAx4genA..33SkpZzQP8.rQu/8NGEERwiJN3gsrm', NULL, 'manager', 1, NULL, NULL, 1, NULL, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL, NULL, 0),
(5, 'Đầu bếp chính', 'kitchen@zomzop.com', NULL, '$2y$12$QeVcHw2yIppQ3lTX3ySoKuq6YAz48EqWSlonBZgJcyw5RuQGyG2J2', NULL, 'kitchen', 1, NULL, NULL, 1, NULL, '2026-06-13 00:21:56', '2026-06-13 00:21:56', NULL, NULL, 0),
(6, 'Cao Phúc Thịnh', 'caothinhlop94@gmail.com', NULL, '$2y$12$KEdGHBofAWl/s6atq6Ua2eB5.kYIfFWKxaBldiDB.udRmevqi8Nu.', '0326313224', 'customer', NULL, NULL, NULL, 1, NULL, '2026-06-13 17:30:17', '2026-06-13 17:30:17', NULL, NULL, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_chat_sessions`
--
ALTER TABLE `ai_chat_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ai_chat_sessions_session_token_unique` (`session_token`),
  ADD KEY `ai_chat_sessions_user_id_foreign` (`user_id`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendances_user_id_foreign` (`user_id`),
  ADD KEY `attendances_branch_id_foreign` (`branch_id`),
  ADD KEY `attendances_shift_id_foreign` (`shift_id`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branch_menu_items`
--
ALTER TABLE `branch_menu_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `branch_menu_items_branch_id_menu_item_id_unique` (`branch_id`,`menu_item_id`),
  ADD KEY `branch_menu_items_menu_item_id_foreign` (`menu_item_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`);

--
-- Indexes for table `coupon_notifications`
--
ALTER TABLE `coupon_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coupon_notifications_user_id_foreign` (`user_id`),
  ADD KEY `coupon_notifications_coupon_id_foreign` (`coupon_id`);

--
-- Indexes for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupon_usages_coupon_id_user_id_unique` (`coupon_id`,`user_id`),
  ADD KEY `coupon_usages_user_id_foreign` (`user_id`),
  ADD KEY `coupon_usages_order_id_foreign` (`order_id`);

--
-- Indexes for table `daily_sales_summary`
--
ALTER TABLE `daily_sales_summary`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `daily_sales_summary_branch_id_menu_item_id_date_unique` (`branch_id`,`menu_item_id`,`date`),
  ADD KEY `daily_sales_summary_menu_item_id_foreign` (`menu_item_id`);

--
-- Indexes for table `face_descriptors`
--
ALTER TABLE `face_descriptors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `face_descriptors_user_id_foreign` (`user_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `favorites_user_id_menu_item_id_unique` (`user_id`,`menu_item_id`),
  ADD KEY `favorites_menu_item_id_foreign` (`menu_item_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `menu_items_slug_unique` (`slug`),
  ADD KEY `menu_items_category_id_foreign` (`category_id`);

--
-- Indexes for table `menu_item_images`
--
ALTER TABLE `menu_item_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_item_images_menu_item_id_foreign` (`menu_item_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_code_unique` (`order_code`),
  ADD KEY `orders_user_id_foreign` (`user_id`),
  ADD KEY `orders_branch_id_foreign` (`branch_id`),
  ADD KEY `orders_kitchen_by_foreign` (`kitchen_by`),
  ADD KEY `orders_coupon_id_foreign` (`coupon_id`);

--
-- Indexes for table `order_histories`
--
ALTER TABLE `order_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_histories_order_id_foreign` (`order_id`),
  ADD KEY `order_histories_changed_by_foreign` (`changed_by`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_menu_item_id_foreign` (`menu_item_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payrolls_user_id_branch_id_month_year_unique` (`user_id`,`branch_id`,`month`,`year`),
  ADD KEY `payrolls_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reviews_order_id_unique` (`order_id`),
  ADD KEY `reviews_user_id_foreign` (`user_id`),
  ADD KEY `reviews_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `salary_configs`
--
ALTER TABLE `salary_configs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `salary_configs_user_id_foreign` (`user_id`);

--
-- Indexes for table `sales_predictions`
--
ALTER TABLE `sales_predictions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sales_predictions_branch_id_menu_item_id_predicted_date_unique` (`branch_id`,`menu_item_id`,`predicted_date`),
  ADD KEY `sales_predictions_menu_item_id_foreign` (`menu_item_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `shifts_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `support_messages_ticket_id_foreign` (`ticket_id`),
  ADD KEY `support_messages_sender_id_foreign` (`sender_id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `support_tickets_user_id_foreign` (`user_id`),
  ADD KEY `support_tickets_order_id_foreign` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_branch_id_foreign` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_chat_sessions`
--
ALTER TABLE `ai_chat_sessions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `branch_menu_items`
--
ALTER TABLE `branch_menu_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `coupon_notifications`
--
ALTER TABLE `coupon_notifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `daily_sales_summary`
--
ALTER TABLE `daily_sales_summary`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `face_descriptors`
--
ALTER TABLE `face_descriptors`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `menu_item_images`
--
ALTER TABLE `menu_item_images`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `order_histories`
--
ALTER TABLE `order_histories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `payrolls`
--
ALTER TABLE `payrolls`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `salary_configs`
--
ALTER TABLE `salary_configs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sales_predictions`
--
ALTER TABLE `sales_predictions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=820;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `support_messages`
--
ALTER TABLE `support_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_chat_sessions`
--
ALTER TABLE `ai_chat_sessions`
  ADD CONSTRAINT `ai_chat_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `attendances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `branch_menu_items`
--
ALTER TABLE `branch_menu_items`
  ADD CONSTRAINT `branch_menu_items_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `branch_menu_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coupon_notifications`
--
ALTER TABLE `coupon_notifications`
  ADD CONSTRAINT `coupon_notifications_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`),
  ADD CONSTRAINT `coupon_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  ADD CONSTRAINT `coupon_usages_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`),
  ADD CONSTRAINT `coupon_usages_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `coupon_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `daily_sales_summary`
--
ALTER TABLE `daily_sales_summary`
  ADD CONSTRAINT `daily_sales_summary_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `daily_sales_summary_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `face_descriptors`
--
ALTER TABLE `face_descriptors`
  ADD CONSTRAINT `face_descriptors_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD CONSTRAINT `menu_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `menu_item_images`
--
ALTER TABLE `menu_item_images`
  ADD CONSTRAINT `menu_item_images_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_kitchen_by_foreign` FOREIGN KEY (`kitchen_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_histories`
--
ALTER TABLE `order_histories`
  ADD CONSTRAINT `order_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_histories_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payrolls`
--
ALTER TABLE `payrolls`
  ADD CONSTRAINT `payrolls_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payrolls_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `salary_configs`
--
ALTER TABLE `salary_configs`
  ADD CONSTRAINT `salary_configs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales_predictions`
--
ALTER TABLE `sales_predictions`
  ADD CONSTRAINT `sales_predictions_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_predictions_menu_item_id_foreign` FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shifts`
--
ALTER TABLE `shifts`
  ADD CONSTRAINT `shifts_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD CONSTRAINT `support_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_messages_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `support_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
