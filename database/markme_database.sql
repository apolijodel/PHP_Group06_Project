-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 03, 2026 at 06:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.5.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `markme_db`
--
-- phpMyAdmin exports the tables only: it assumes you have already selected a
-- database in its UI. Run from a terminal the way README.md documents --
-- `mysql -u root < database/markme_database.sql` -- that assumption does not
-- hold, and the import stops at the first CREATE TABLE with
-- "ERROR 1046 (3D000): No database selected".
--
-- These three lines make the file self-contained so it imports the same way
-- from phpMyAdmin or from a terminal. The DROP means re-importing over an
-- existing markme_db replaces it instead of failing on "table already exists".
--
DROP DATABASE IF EXISTS `markme_db`;
CREATE DATABASE `markme_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `markme_db`;

-- --------------------------------------------------------

--
-- Table structure for table `account_locks`
--

CREATE TABLE `account_locks` (
  `lock_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `failed_attempts` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_attempt_at` timestamp NULL DEFAULT NULL,
  `locked_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `account_locks`
--

INSERT INTO `account_locks` (`lock_id`, `user_id`, `username`, `failed_attempts`, `last_attempt_at`, `locked_at`, `updated_at`) VALUES
(24, 6, 'staff', 1, '2026-10-03 16:26:46', NULL, '2026-10-03 16:26:46');

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `log_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `actor_role` enum('customer','staff','admin','guest') NOT NULL DEFAULT 'guest',
  `actor_name` varchar(100) NOT NULL,
  `action` varchar(50) NOT NULL,
  `module` varchar(40) DEFAULT NULL,
  `entity_type` varchar(30) DEFAULT NULL,
  `entity_id` int(10) UNSIGNED DEFAULT NULL,
  `summary` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`log_id`, `user_id`, `actor_role`, `actor_name`, `action`, `module`, `entity_type`, `entity_id`, `summary`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', NULL, NULL, '2026-09-25 09:40:05'),
(2, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', NULL, NULL, '2026-09-25 09:40:05'),
(3, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', NULL, NULL, '2026-09-25 09:40:06'),
(4, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', NULL, NULL, '2026-09-25 09:40:06'),
(5, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', NULL, NULL, '2026-09-25 09:40:08'),
(6, 2, 'customer', 'juana', 'auth.logout', 'Authentication', 'user', 2, 'Customer signed out', NULL, NULL, '2026-09-25 09:40:08'),
(7, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', NULL, NULL, '2026-09-25 09:40:35'),
(8, 2, 'customer', 'juana', 'auth.logout', 'Authentication', 'user', 2, 'Customer signed out', NULL, NULL, '2026-09-25 09:40:35'),
(9, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', NULL, NULL, '2026-09-25 09:40:36'),
(10, 2, 'customer', 'juana', 'auth.logout', 'Authentication', 'user', 2, 'Customer signed out', NULL, NULL, '2026-09-25 09:40:36'),
(11, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', NULL, NULL, '2026-09-25 09:40:37'),
(12, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', NULL, NULL, '2026-09-25 09:40:38'),
(13, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', NULL, NULL, '2026-09-25 09:41:00'),
(14, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', NULL, NULL, '2026-09-25 10:36:59'),
(15, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', NULL, NULL, '2026-09-25 10:42:05'),
(16, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', NULL, NULL, '2026-09-26 00:23:07'),
(17, 4, 'customer', 'Jodel', 'auth.login', 'Authentication', 'user', 4, 'Customer signed in', NULL, NULL, '2026-09-26 00:23:28'),
(18, 4, 'customer', 'Jodel', 'auth.logout', 'Authentication', 'user', 4, 'Customer signed out', NULL, NULL, '2026-09-26 00:24:33'),
(19, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', NULL, NULL, '2026-09-26 00:24:45'),
(20, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', NULL, NULL, '2026-09-26 00:25:03'),
(21, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', NULL, NULL, '2026-09-26 01:04:15'),
(22, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', NULL, NULL, '2026-09-26 01:58:43'),
(23, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', NULL, NULL, '2026-09-26 02:02:23'),
(24, 1, 'admin', 'admin', 'product.updated', 'Product Management', 'product', 2, 'Updated product “Name & Quote Bookmark”', NULL, NULL, '2026-09-27 13:57:33'),
(25, 1, 'admin', 'admin', 'inventory.restocked', 'Inventory', 'product', 1, 'Restocked “Photo Memory Bookmark” by 19 to 63', NULL, NULL, '2026-09-27 13:57:46'),
(26, 2, 'customer', 'juana', 'auth.failed', 'Authentication', 'user', 2, 'Failed sign-in (attempt 1 of 3)', '::1', 'curl/8.18.0', '2026-09-27 14:22:32'),
(27, 2, 'customer', 'juana', 'auth.failed', 'Authentication', 'user', 2, 'Failed sign-in (attempt 2 of 3)', '::1', 'curl/8.18.0', '2026-09-27 14:22:33'),
(28, 2, 'customer', 'juana', 'auth.failed', 'Authentication', 'user', 2, 'Failed sign-in (attempt 3 of 3)', '::1', 'curl/8.18.0', '2026-09-27 14:22:34'),
(29, 2, 'customer', 'juana', 'account.locked', 'Authentication', 'user', 2, 'Account locked after 3 failed sign-in attempts', '::1', 'curl/8.18.0', '2026-09-27 14:22:34'),
(30, 2, 'customer', 'juana', 'auth.blocked', 'Authentication', 'user', 2, 'Sign-in refused: account is locked', '::1', 'curl/8.18.0', '2026-09-27 14:22:35'),
(31, 2, 'customer', 'juana', 'auth.blocked', 'Authentication', 'user', 2, 'Sign-in refused: account is locked', '::1', 'curl/8.18.0', '2026-09-27 14:22:36'),
(32, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-27 14:25:05'),
(33, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-27 14:25:05'),
(34, 1, 'admin', 'admin', 'account.unlocked', 'Security', 'user', 2, 'Unlocked account juana', '::1', 'curl/8.18.0', '2026-09-27 14:25:20'),
(35, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 14:25:20'),
(36, NULL, 'customer', 'sectest1', 'account.registered', 'Account', 'user', 8, 'Created a customer account (awaiting email activation)', '::1', 'curl/8.18.0', '2026-09-27 14:27:16'),
(37, NULL, 'customer', 'sectest1', 'auth.blocked', 'Authentication', 'user', 8, 'Sign-in refused: account not activated', '::1', 'curl/8.18.0', '2026-09-27 14:27:30'),
(38, NULL, 'customer', 'sectest1', 'account.activated', 'Account', 'user', 8, 'Account activated by email link', '::1', 'curl/8.18.0', '2026-09-27 14:27:32'),
(39, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-27 14:33:00'),
(40, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-27 14:33:00'),
(41, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 14:33:01'),
(42, NULL, 'guest', 'Guest', 'auth.failed', 'Authentication', 'user', NULL, 'Failed sign-in for unknown account \"\' OR \'1\'=\'1\"', '::1', 'curl/8.18.0', '2026-09-27 14:33:21'),
(43, NULL, 'guest', 'Guest', 'auth.failed', 'Authentication', 'user', NULL, 'Failed sign-in for unknown account \"admin\'--\"', '::1', 'curl/8.18.0', '2026-09-27 14:33:22'),
(44, NULL, 'guest', 'Guest', 'auth.failed', 'Authentication', 'user', NULL, 'Failed sign-in for unknown account \"\'; DROP TABLE users;--\"', '::1', 'curl/8.18.0', '2026-09-27 14:33:23'),
(45, NULL, 'customer', 'sectest1', 'auth.login', 'Authentication', 'user', 8, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 14:33:50'),
(46, NULL, 'customer', 'sectest1', 'auth.login', 'Authentication', 'user', 8, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 14:34:15'),
(47, NULL, 'customer', 'sectest1', 'auth.login', 'Authentication', 'user', 8, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 14:34:38'),
(48, NULL, 'customer', 'sectest1', 'account.mfa_enabled', 'Security', 'user', 8, 'Enabled two-factor authentication', '::1', 'curl/8.18.0', '2026-09-27 14:34:40'),
(49, NULL, 'customer', 'sectest1', 'auth.mfa_challenge', 'Authentication', 'user', 8, 'Password accepted, awaiting authenticator code', '::1', 'curl/8.18.0', '2026-09-27 14:34:56'),
(50, NULL, 'customer', 'sectest1', 'auth.mfa_failed', 'Authentication', 'user', 8, 'Incorrect authenticator code (attempt 1 of 3)', '::1', 'curl/8.18.0', '2026-09-27 14:34:57'),
(51, NULL, 'customer', 'sectest1', 'auth.login', 'Authentication', 'user', 8, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 14:34:58'),
(52, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 14:35:27'),
(53, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 14:36:48'),
(54, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 14:37:08'),
(55, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 14:37:37'),
(56, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 14:38:45'),
(57, NULL, 'customer', 'sectest1', 'auth.login', 'Authentication', 'user', 8, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 14:39:44'),
(58, NULL, 'customer', 'sectest1', 'auth.login', 'Authentication', 'user', 8, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 14:39:55'),
(59, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-27 14:40:38'),
(60, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-27 14:40:39'),
(61, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 14:40:52'),
(62, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-27 15:06:24'),
(63, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 15:06:27'),
(64, NULL, 'guest', 'Guest', 'auth.failed', 'Authentication', 'user', NULL, 'Failed sign-in for unknown account \"rhod_duldulaolhian@plpasig.edu.ph\"', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-27 15:08:07'),
(65, NULL, 'guest', 'Guest', 'auth.failed', 'Authentication', 'user', NULL, 'Failed sign-in for unknown account \"dudulao_rhodlhian@plpasig.edu.ph\"', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-27 15:08:28'),
(66, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-27 15:08:47'),
(67, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-27 15:09:04'),
(68, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-27 15:23:15'),
(69, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 15:23:18'),
(70, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-27 15:33:48'),
(71, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-27 15:35:26'),
(72, 6, 'staff', 'staff', 'inventory.restocked', 'Inventory', 'product', 1, 'Restocked “Photo Memory Bookmark” by 3 to 66', '::1', 'curl/8.18.0', '2026-09-27 15:35:45'),
(73, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 15:36:54'),
(74, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-27 16:05:12'),
(75, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-27 16:05:12'),
(76, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-27 16:05:33'),
(77, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-27 16:07:47'),
(78, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-27 16:07:47'),
(79, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-27 16:26:33'),
(80, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.0.0 Safari/537.36', '2026-09-27 16:27:07'),
(81, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 12:40:13'),
(82, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-28 12:40:28'),
(83, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-28 12:40:29'),
(84, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 12:41:08'),
(85, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-28 12:41:09'),
(86, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-28 12:41:09'),
(87, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'curl/8.13.0', '2026-09-28 12:41:38'),
(88, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'curl/8.13.0', '2026-09-28 12:41:41'),
(89, 2, 'customer', 'juana', 'auth.logout', 'Authentication', 'user', 2, 'Customer signed out', '::1', 'curl/8.13.0', '2026-09-28 12:41:43'),
(90, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 12:42:13'),
(91, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-28 12:42:14'),
(92, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-28 12:42:15'),
(93, 1, 'admin', 'admin', 'category.created', 'Category Management', 'category', 4, 'Added category “ZZ Test Category”', '::1', 'curl/8.18.0', '2026-09-28 12:44:16'),
(94, 1, 'admin', 'admin', 'category.deleted', 'Category Management', 'category', 4, 'Deleted category “ZZ Test Category”', '::1', 'curl/8.18.0', '2026-09-28 12:44:17'),
(95, 1, 'admin', 'admin', 'order.status', 'Order Management', 'order', 2, 'Moved order #2 from Pending to Processing', '::1', 'curl/8.18.0', '2026-09-28 12:44:18'),
(96, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 12:46:21'),
(97, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 12:47:18'),
(98, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-28 12:47:19'),
(99, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-28 12:47:41'),
(100, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-28 13:04:25'),
(101, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-28 13:08:27'),
(102, 1, 'admin', 'admin', 'category.updated', 'Category Management', 'category', 1, 'Updated category “Classic Bookmarks”', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-28 13:10:58'),
(103, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-28 13:14:57'),
(104, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 13:28:13'),
(105, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-28 13:28:14'),
(106, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 13:33:51'),
(107, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-28 13:35:32'),
(108, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 13:36:30'),
(109, 1, 'admin', 'admin', 'category.created', 'Category Management', 'category', 5, 'Added category “ZZ Probe Category”', '::1', 'curl/8.18.0', '2026-09-28 13:36:50'),
(110, 1, 'admin', 'admin', 'category.deleted', 'Category Management', 'category', 5, 'Deleted category “ZZ Probe Category”', '::1', 'curl/8.18.0', '2026-09-28 13:38:18'),
(111, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 13:39:13'),
(112, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-28 13:39:13'),
(113, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-28 13:39:14'),
(114, 1, 'admin', 'admin', 'auth.failed', 'Authentication', 'user', 1, 'Failed sign-in (attempt 1 of 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-28 13:49:03'),
(115, 1, 'admin', 'admin', 'auth.failed', 'Authentication', 'user', 1, 'Failed sign-in (attempt 2 of 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-28 13:49:09'),
(116, 1, 'admin', 'admin', 'auth.failed', 'Authentication', 'user', 1, 'Failed sign-in (attempt 3 of 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-28 13:49:39'),
(117, 1, 'admin', 'admin', 'account.locked', 'Authentication', 'user', 1, 'Account locked after 3 failed sign-in attempts', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-28 13:49:39'),
(118, 1, 'admin', 'admin', 'auth.blocked', 'Authentication', 'user', 1, 'Sign-in refused: account is locked', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-28 13:49:47'),
(119, 1, 'admin', 'admin', 'auth.blocked', 'Authentication', 'user', 1, 'Sign-in refused: account is locked', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-28 13:50:02'),
(120, 2, 'customer', 'juana', 'auth.failed', 'Authentication', 'user', 2, 'Failed sign-in (attempt 1 of 3)', '::1', 'curl/8.18.0', '2026-09-28 13:53:34'),
(121, 2, 'customer', 'juana', 'auth.failed', 'Authentication', 'user', 2, 'Failed sign-in (attempt 2 of 3)', '::1', 'curl/8.18.0', '2026-09-28 13:53:55'),
(122, 2, 'customer', 'juana', 'auth.failed', 'Authentication', 'user', 2, 'Failed sign-in (attempt 3 of 3)', '::1', 'curl/8.18.0', '2026-09-28 13:55:14'),
(123, 2, 'customer', 'juana', 'account.locked', 'Authentication', 'user', 2, 'Account locked after 3 failed sign-in attempts', '::1', 'curl/8.18.0', '2026-09-28 13:55:15'),
(124, NULL, 'guest', 'CLI', 'account.unlocked', 'Security', 'user', 1, 'Unlocked admin from the command line', NULL, NULL, '2026-09-28 13:56:40'),
(125, NULL, 'guest', 'CLI', 'account.unlocked', 'Security', 'user', 2, 'Unlocked juana from the command line', NULL, NULL, '2026-09-28 13:56:40'),
(126, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 13:56:41'),
(127, 2, 'customer', 'juana', 'auth.failed', 'Authentication', 'user', 2, 'Failed sign-in (attempt 1 of 3)', '::1', 'curl/8.18.0', '2026-09-28 13:57:20'),
(128, 2, 'customer', 'juana', 'auth.failed', 'Authentication', 'user', 2, 'Failed sign-in (attempt 2 of 3)', '::1', 'curl/8.18.0', '2026-09-28 13:57:22'),
(129, 2, 'customer', 'juana', 'auth.failed', 'Authentication', 'user', 2, 'Failed sign-in (attempt 3 of 3)', '::1', 'curl/8.18.0', '2026-09-28 13:57:23'),
(130, 2, 'customer', 'juana', 'account.locked', 'Authentication', 'user', 2, 'Account locked after 3 failed sign-in attempts', '::1', 'curl/8.18.0', '2026-09-28 13:57:23'),
(131, 2, 'customer', 'juana', 'auth.blocked', 'Authentication', 'user', 2, 'Sign-in refused: account is locked', '::1', 'curl/8.18.0', '2026-09-28 13:57:23'),
(132, 2, 'customer', 'juana', 'auth.blocked', 'Authentication', 'user', 2, 'Sign-in refused: account is locked', '::1', 'curl/8.18.0', '2026-09-28 13:57:24'),
(133, 2, 'guest', 'juana', 'account.unlocked', 'Security', 'user', 2, 'Lockout expired after 15 minutes', '::1', 'curl/8.18.0', '2026-09-28 13:58:59'),
(134, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-28 13:59:00'),
(135, 2, 'customer', 'juana', 'auth.failed', 'Authentication', 'user', 2, 'Failed sign-in (attempt 1 of 3)', '::1', 'curl/8.18.0', '2026-09-28 13:59:01'),
(136, 2, 'customer', 'juana', 'auth.failed', 'Authentication', 'user', 2, 'Failed sign-in (attempt 2 of 3)', '::1', 'curl/8.18.0', '2026-09-28 13:59:02'),
(137, 2, 'customer', 'juana', 'auth.failed', 'Authentication', 'user', 2, 'Failed sign-in (attempt 3 of 3)', '::1', 'curl/8.18.0', '2026-09-28 13:59:03'),
(138, 2, 'customer', 'juana', 'account.locked', 'Authentication', 'user', 2, 'Account locked after 3 failed sign-in attempts', '::1', 'curl/8.18.0', '2026-09-28 13:59:03'),
(139, 2, 'customer', 'juana', 'auth.blocked', 'Authentication', 'user', 2, 'Sign-in refused: account is locked', '::1', 'curl/8.18.0', '2026-09-28 13:59:03'),
(140, NULL, 'guest', 'CLI', 'account.unlocked', 'Security', 'user', 2, 'Unlocked juana from the command line', NULL, NULL, '2026-09-28 13:59:04'),
(141, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 13:59:20'),
(142, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-28 13:59:21'),
(143, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-28 14:00:32'),
(144, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-28 14:00:33'),
(145, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-28 14:09:35'),
(146, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 14:13:36'),
(147, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 14:18:25'),
(148, 1, 'admin', 'admin', 'category.created', 'Category Management', 'category', 6, 'Added category “ZZ Temp”', '::1', 'curl/8.18.0', '2026-09-28 14:20:09'),
(149, 1, 'admin', 'admin', 'category.deleted', 'Category Management', 'category', 6, 'Deleted category “ZZ Temp”', '::1', 'curl/8.18.0', '2026-09-28 14:20:10'),
(150, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-28 14:21:02'),
(151, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-28 17:50:09'),
(152, 1, 'admin', 'admin', 'security.settings', 'Security', NULL, NULL, 'Changed security settings: max_login_attempts 3 to 5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-28 17:52:15'),
(153, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 17:59:27'),
(154, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 18:01:32'),
(155, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 18:02:15'),
(156, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 18:03:26'),
(157, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-28 18:03:49'),
(158, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-28 18:03:50'),
(159, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 18:08:24'),
(160, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-28 18:08:24'),
(161, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-28 18:08:25'),
(162, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-28 18:15:25'),
(163, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 18:16:11'),
(164, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 18:17:11'),
(165, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 18:17:12'),
(166, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 18:17:38'),
(167, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 18:18:14'),
(168, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 18:19:00'),
(169, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 18:19:37'),
(170, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-28 18:19:59'),
(171, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-28 18:19:59'),
(172, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-09-28 18:20:00'),
(173, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-28 18:21:25'),
(174, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:29:58'),
(175, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:30:12'),
(176, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:36:00'),
(177, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-28 18:39:06'),
(178, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:43:02'),
(179, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:43:07'),
(180, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:43:38'),
(181, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:44:11'),
(182, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:46:06'),
(183, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:49:45'),
(184, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:49:51'),
(185, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:50:35'),
(186, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:51:00'),
(187, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:51:18'),
(188, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:52:11'),
(189, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:52:35'),
(190, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:52:47'),
(191, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:54:28'),
(192, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:54:53'),
(193, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:55:28'),
(194, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:55:52'),
(195, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:56:58'),
(196, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:57:51'),
(197, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:58:32'),
(198, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:58:56'),
(199, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:58:58'),
(200, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:59:15'),
(201, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-28 18:59:19'),
(202, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 00:59:53'),
(203, 1, 'admin', 'admin', 'inventory.restocked', 'Inventory', 'product', 4, 'Restocked “Minimalist Line Art Bookmark” by 16 to 16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 01:01:27'),
(204, 1, 'admin', 'admin', 'security.settings', 'Security', NULL, NULL, 'Changed security settings: max_login_attempts 5 to 10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 01:03:16'),
(205, 1, 'admin', 'admin', 'security.settings', 'Security', NULL, NULL, 'Changed security settings: max_login_attempts 10 to 5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 01:03:30'),
(206, 1, 'admin', 'admin', 'category.created', 'Category Management', 'category', 7, 'Added category “Test Category”', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 01:08:39'),
(207, 1, 'admin', 'admin', 'product.created', 'Product Management', 'product', 6, 'Added product “Test” at ₱70.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 01:09:48'),
(208, 1, 'admin', 'admin', 'product.deleted', 'Product Management', 'product', 6, 'Deleted product “Test”', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 01:10:39'),
(209, 1, 'admin', 'admin', 'category.deleted', 'Category Management', 'category', 7, 'Deleted category “Test Category”', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 01:10:45'),
(210, 4, 'customer', 'Jodel', 'auth.login', 'Authentication', 'user', 4, 'Customer signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 05:08:02'),
(211, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 10:23:28'),
(212, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 15:34:42'),
(213, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-29 15:40:00'),
(214, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-29 15:40:30'),
(215, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-29 15:41:38'),
(216, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-29 15:47:24'),
(217, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 15:48:39'),
(218, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 15:49:02'),
(219, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 15:49:21'),
(220, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 15:49:52'),
(221, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 15:50:41'),
(222, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 15:51:13'),
(223, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 15:51:53'),
(224, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 15:52:23'),
(225, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 15:53:09'),
(226, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 15:54:52'),
(227, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 15:57:28'),
(228, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 15:58:20'),
(229, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 15:58:45'),
(230, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 15:58:57'),
(231, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 15:59:33'),
(232, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:00:46'),
(233, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:01:04'),
(234, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:01:07'),
(235, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:01:11'),
(236, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:01:23'),
(237, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:01:27'),
(238, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:01:30'),
(239, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:02:05'),
(240, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:02:46'),
(241, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:03:22'),
(242, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:03:37'),
(243, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:04:20'),
(244, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:04:52'),
(245, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:04:55'),
(246, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:05:34'),
(247, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:05:54'),
(248, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:06:26'),
(249, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-29 16:06:50'),
(250, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-29 16:07:31'),
(251, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 16:13:22'),
(252, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-29 16:15:27'),
(253, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:16:18'),
(254, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:16:49'),
(255, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:17:35'),
(256, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:17:52'),
(257, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:17:56'),
(258, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:18:23'),
(259, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:18:41'),
(260, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:18:49'),
(261, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:19:23'),
(262, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-29 16:19:31'),
(263, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:20:09'),
(264, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:20:27'),
(265, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:37:29'),
(266, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:38:00'),
(267, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:38:26');
INSERT INTO `activity_log` (`log_id`, `user_id`, `actor_role`, `actor_name`, `action`, `module`, `entity_type`, `entity_id`, `summary`, `ip_address`, `user_agent`, `created_at`) VALUES
(268, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:38:42'),
(269, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:38:45'),
(270, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:39:24'),
(271, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:39:41'),
(272, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:40:03'),
(273, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:40:34'),
(274, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:41:14'),
(275, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:41:46'),
(276, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:42:13'),
(277, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:42:26'),
(278, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:42:51'),
(279, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 16:50:59'),
(280, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:53:24'),
(281, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:53:28'),
(282, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:54:15'),
(283, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:54:49'),
(284, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:55:16'),
(285, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 16:56:28'),
(286, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 16:59:56'),
(287, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:00:30'),
(288, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:01:14'),
(289, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:01:33'),
(290, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:01:59'),
(291, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:02:21'),
(292, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 17:06:11'),
(293, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:07:36'),
(294, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:07:40'),
(295, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:08:14'),
(296, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:08:48'),
(297, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:09:12'),
(298, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 17:11:18'),
(299, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:14:38'),
(300, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:14:59'),
(301, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:15:00'),
(302, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:15:00'),
(303, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:15:00'),
(304, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:15:01'),
(305, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:15:01'),
(306, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:15:01'),
(307, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:15:01'),
(308, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:15:02'),
(309, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:15:19'),
(310, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:15:46'),
(311, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:16:35'),
(312, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:17:18'),
(313, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:17:45'),
(314, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:18:04'),
(315, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:18:05'),
(316, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:18:06'),
(317, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:18:06'),
(318, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:18:06'),
(319, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:18:07'),
(320, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:18:07'),
(321, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:18:08'),
(322, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:18:08'),
(323, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:18:08'),
(324, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:18:09'),
(325, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:18:09'),
(326, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:18:10'),
(327, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:18:10'),
(328, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:18:10'),
(329, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:18:22'),
(330, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:05'),
(331, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:10'),
(332, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:10'),
(333, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:31'),
(334, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:32'),
(335, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:35'),
(336, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:35'),
(337, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:38'),
(338, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:38'),
(339, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:41'),
(340, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:41'),
(341, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:44'),
(342, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:45'),
(343, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:48'),
(344, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:49'),
(345, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:52'),
(346, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:52'),
(347, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:54'),
(348, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:55'),
(349, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:56'),
(350, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:56'),
(351, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:56'),
(352, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:56'),
(353, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:56'),
(354, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:57'),
(355, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:57'),
(356, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:57'),
(357, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:57'),
(358, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:58'),
(359, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:58'),
(360, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:58'),
(361, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:59'),
(362, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:59'),
(363, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:59'),
(364, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:19:59'),
(365, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:00'),
(366, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:00'),
(367, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:00'),
(368, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:00'),
(369, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:01'),
(370, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:01'),
(371, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:02'),
(372, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:02'),
(373, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:03'),
(374, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:03'),
(375, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:03'),
(376, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:03'),
(377, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:03'),
(378, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:04'),
(379, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:04'),
(380, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:05'),
(381, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:05'),
(382, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:05'),
(383, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:06'),
(384, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:06'),
(385, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:06'),
(386, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:06'),
(387, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:07'),
(388, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:07'),
(389, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:07'),
(390, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:07'),
(391, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:07'),
(392, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:08'),
(393, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:08'),
(394, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:08'),
(395, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:09'),
(396, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:09'),
(397, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:09'),
(398, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:10'),
(399, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:10'),
(400, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:10'),
(401, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:10'),
(402, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:10'),
(403, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:11'),
(404, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:11'),
(405, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:11'),
(406, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:12'),
(407, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:12'),
(408, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:12'),
(409, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:13'),
(410, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:13'),
(411, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:14'),
(412, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:14'),
(413, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:14'),
(414, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:14'),
(415, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:15'),
(416, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:15'),
(417, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:15'),
(418, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:16'),
(419, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:16'),
(420, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:16'),
(421, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:17'),
(422, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:17'),
(423, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:18'),
(424, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:19'),
(425, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:20'),
(426, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:23'),
(427, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:23'),
(428, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:26'),
(429, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:26'),
(430, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:30'),
(431, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:31'),
(432, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:35'),
(433, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:36'),
(434, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:36'),
(435, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:36'),
(436, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:36'),
(437, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:37'),
(438, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:37'),
(439, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:37'),
(440, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:37'),
(441, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:38'),
(442, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:38'),
(443, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:38'),
(444, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:39'),
(445, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:39'),
(446, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:39'),
(447, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:40'),
(448, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:40'),
(449, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:40'),
(450, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:41'),
(451, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:41'),
(452, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:41'),
(453, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:42'),
(454, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:42'),
(455, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:42'),
(456, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:42'),
(457, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:43'),
(458, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:43'),
(459, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:43'),
(460, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:44'),
(461, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:44'),
(462, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:45'),
(463, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:45'),
(464, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:45'),
(465, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:45'),
(466, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:46'),
(467, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:46');
INSERT INTO `activity_log` (`log_id`, `user_id`, `actor_role`, `actor_name`, `action`, `module`, `entity_type`, `entity_id`, `summary`, `ip_address`, `user_agent`, `created_at`) VALUES
(468, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:46'),
(469, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:47'),
(470, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:47'),
(471, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:47'),
(472, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:48'),
(473, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:48'),
(474, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:48'),
(475, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:48'),
(476, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:49'),
(477, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:49'),
(478, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:49'),
(479, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:49'),
(480, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:49'),
(481, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:50'),
(482, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:50'),
(483, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:50'),
(484, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:51'),
(485, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:51'),
(486, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:51'),
(487, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:51'),
(488, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:52'),
(489, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:52'),
(490, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:52'),
(491, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:53'),
(492, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:53'),
(493, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:53'),
(494, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:53'),
(495, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:53'),
(496, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:54'),
(497, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:54'),
(498, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:54'),
(499, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:55'),
(500, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:55'),
(501, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:56'),
(502, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:56'),
(503, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:56'),
(504, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:56'),
(505, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:57'),
(506, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:57'),
(507, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:57'),
(508, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:20:57'),
(509, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:00'),
(510, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:01'),
(511, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:04'),
(512, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:05'),
(513, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:08'),
(514, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:08'),
(515, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:11'),
(516, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:12'),
(517, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:15'),
(518, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:15'),
(519, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:19'),
(520, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:19'),
(521, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:22'),
(522, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:23'),
(523, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:28'),
(524, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:28'),
(525, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:31'),
(526, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:32'),
(527, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:35'),
(528, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:35'),
(529, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:39'),
(530, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:21:39'),
(531, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-29 17:26:39'),
(532, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:44'),
(533, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:46'),
(534, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:46'),
(535, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:46'),
(536, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:46'),
(537, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:47'),
(538, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:47'),
(539, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:47'),
(540, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:48'),
(541, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:48'),
(542, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:48'),
(543, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:49'),
(544, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:49'),
(545, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:50'),
(546, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:50'),
(547, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:50'),
(548, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:51'),
(549, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:51'),
(550, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:52'),
(551, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:52'),
(552, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:53'),
(553, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:53'),
(554, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:53'),
(555, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:54'),
(556, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:54'),
(557, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:55'),
(558, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:55'),
(559, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:55'),
(560, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:56'),
(561, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:56'),
(562, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:57'),
(563, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:57'),
(564, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:57'),
(565, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:57'),
(566, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:58'),
(567, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:58'),
(568, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:58'),
(569, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:59'),
(570, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:59'),
(571, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:40:59'),
(572, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:00'),
(573, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:00'),
(574, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:01'),
(575, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:01'),
(576, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:01'),
(577, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:02'),
(578, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:02'),
(579, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:02'),
(580, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:03'),
(581, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:03'),
(582, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:03'),
(583, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:04'),
(584, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:04'),
(585, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:04'),
(586, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:05'),
(587, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:05'),
(588, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:05'),
(589, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:06'),
(590, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:06'),
(591, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:24'),
(592, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:25'),
(593, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:25'),
(594, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:26'),
(595, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:26'),
(596, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:26'),
(597, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:27'),
(598, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:27'),
(599, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:28'),
(600, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:28'),
(601, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:28'),
(602, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:29'),
(603, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:29'),
(604, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:30'),
(605, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:30'),
(606, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:31'),
(607, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:31'),
(608, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:31'),
(609, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:32'),
(610, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:32'),
(611, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:33'),
(612, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:33'),
(613, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:33'),
(614, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:34'),
(615, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:34'),
(616, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:34'),
(617, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:35'),
(618, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:35'),
(619, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:36'),
(620, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:36'),
(621, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:36'),
(622, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:37'),
(623, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:37'),
(624, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:37'),
(625, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:37'),
(626, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:38'),
(627, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:38'),
(628, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:38'),
(629, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:39'),
(630, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:39'),
(631, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:40'),
(632, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:40'),
(633, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:40'),
(634, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:40'),
(635, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:41'),
(636, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:41'),
(637, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:42'),
(638, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:42'),
(639, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:42'),
(640, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:43'),
(641, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:43'),
(642, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:43'),
(643, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:44'),
(644, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:44'),
(645, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:45'),
(646, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:45'),
(647, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:45'),
(648, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:46'),
(649, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-29 17:41:46'),
(650, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 04:20:46'),
(651, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 04:23:35'),
(652, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 04:26:15'),
(653, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 04:31:25'),
(654, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 04:31:44'),
(655, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 04:33:36'),
(656, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 04:33:44'),
(657, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 04:59:22'),
(658, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:48:55'),
(659, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:48:56'),
(660, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:48:56'),
(661, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:48:57'),
(662, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:48:57'),
(663, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:48:57'),
(664, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:48:58'),
(665, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:48:58'),
(666, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:48:59');
INSERT INTO `activity_log` (`log_id`, `user_id`, `actor_role`, `actor_name`, `action`, `module`, `entity_type`, `entity_id`, `summary`, `ip_address`, `user_agent`, `created_at`) VALUES
(667, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:48:59'),
(668, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:49:29'),
(669, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:52:28'),
(670, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:08'),
(671, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:09'),
(672, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:09'),
(673, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:10'),
(674, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:10'),
(675, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:10'),
(676, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:11'),
(677, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:11'),
(678, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:12'),
(679, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:12'),
(680, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:13'),
(681, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:13'),
(682, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:13'),
(683, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:14'),
(684, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:14'),
(685, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:14'),
(686, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:15'),
(687, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:15'),
(688, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:16'),
(689, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:16'),
(690, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:16'),
(691, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:17'),
(692, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:17'),
(693, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:17'),
(694, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:18'),
(695, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:18'),
(696, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:19'),
(697, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:19'),
(698, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:19'),
(699, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:20'),
(700, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:20'),
(701, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:21'),
(702, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:21'),
(703, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:21'),
(704, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:21'),
(705, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:22'),
(706, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:22'),
(707, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:22'),
(708, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:23'),
(709, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:23'),
(710, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:24'),
(711, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:24'),
(712, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:24'),
(713, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:25'),
(714, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:25'),
(715, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:26'),
(716, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:26'),
(717, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:26'),
(718, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:27'),
(719, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:27'),
(720, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:27'),
(721, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:28'),
(722, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:28'),
(723, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:28'),
(724, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:29'),
(725, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:29'),
(726, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:30'),
(727, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:30'),
(728, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:30'),
(729, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:31'),
(730, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:31'),
(731, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:32'),
(732, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:32'),
(733, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:33'),
(734, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:33'),
(735, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:53:59'),
(736, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:00'),
(737, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:01'),
(738, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:01'),
(739, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:01'),
(740, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:02'),
(741, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:02'),
(742, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:03'),
(743, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:03'),
(744, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:03'),
(745, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:04'),
(746, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:04'),
(747, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:05'),
(748, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:05'),
(749, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:06'),
(750, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:06'),
(751, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:07'),
(752, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:07'),
(753, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:07'),
(754, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:08'),
(755, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:08'),
(756, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:09'),
(757, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:09'),
(758, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:10'),
(759, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:10'),
(760, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:11'),
(761, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:11'),
(762, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:11'),
(763, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:11'),
(764, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:12'),
(765, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:12'),
(766, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:12'),
(767, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:13'),
(768, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:13'),
(769, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:13'),
(770, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:14'),
(771, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:14'),
(772, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:15'),
(773, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:15'),
(774, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:15'),
(775, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:16'),
(776, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:16'),
(777, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:17'),
(778, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:17'),
(779, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:17'),
(780, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:17'),
(781, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:18'),
(782, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:18'),
(783, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:18'),
(784, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:19'),
(785, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:19'),
(786, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:19'),
(787, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:20'),
(788, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:20'),
(789, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:20'),
(790, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:21'),
(791, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:21'),
(792, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:22'),
(793, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:22'),
(794, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:23'),
(795, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:23'),
(796, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:23'),
(797, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:24'),
(798, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:24'),
(799, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 15:54:25'),
(800, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 15:59:51'),
(801, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:00:02'),
(802, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:03:57'),
(803, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:03:59'),
(804, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:00'),
(805, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:00'),
(806, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:01'),
(807, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:52'),
(808, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:53'),
(809, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:53'),
(810, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:53'),
(811, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:54'),
(812, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:54'),
(813, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:54'),
(814, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:55'),
(815, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:55'),
(816, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:56'),
(817, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:56'),
(818, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:56'),
(819, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:57'),
(820, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:57'),
(821, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:58'),
(822, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:58'),
(823, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:59'),
(824, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:04:59'),
(825, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:00'),
(826, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:00'),
(827, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:01'),
(828, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:01'),
(829, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:02'),
(830, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:02'),
(831, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:02'),
(832, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:03'),
(833, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:03'),
(834, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:04'),
(835, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:04'),
(836, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:05'),
(837, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:05'),
(838, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:06'),
(839, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:06'),
(840, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:07'),
(841, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:07'),
(842, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:07'),
(843, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:08'),
(844, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:08'),
(845, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:09'),
(846, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:09'),
(847, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:09'),
(848, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:10'),
(849, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:10'),
(850, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:11'),
(851, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:11'),
(852, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:12'),
(853, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:12'),
(854, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:12'),
(855, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:13'),
(856, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:13'),
(857, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:14'),
(858, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:14'),
(859, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:14'),
(860, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:15'),
(861, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:15'),
(862, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:16'),
(863, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:16'),
(864, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:16'),
(865, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:17');
INSERT INTO `activity_log` (`log_id`, `user_id`, `actor_role`, `actor_name`, `action`, `module`, `entity_type`, `entity_id`, `summary`, `ip_address`, `user_agent`, `created_at`) VALUES
(866, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:42'),
(867, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:44'),
(868, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:44'),
(869, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:44'),
(870, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:45'),
(871, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:45'),
(872, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:45'),
(873, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:46'),
(874, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:47'),
(875, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:47'),
(876, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:47'),
(877, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:48'),
(878, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:48'),
(879, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:49'),
(880, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:49'),
(881, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:50'),
(882, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:50'),
(883, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:51'),
(884, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:51'),
(885, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:52'),
(886, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:52'),
(887, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:52'),
(888, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:53'),
(889, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:53'),
(890, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:54'),
(891, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:54'),
(892, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:55'),
(893, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:55'),
(894, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:55'),
(895, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:56'),
(896, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:56'),
(897, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:57'),
(898, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:57'),
(899, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:58'),
(900, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:58'),
(901, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:59'),
(902, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:05:59'),
(903, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:00'),
(904, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:00'),
(905, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:00'),
(906, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:01'),
(907, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:01'),
(908, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:02'),
(909, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:02'),
(910, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:02'),
(911, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:03'),
(912, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:03'),
(913, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:04'),
(914, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:04'),
(915, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:04'),
(916, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:05'),
(917, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:05'),
(918, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:06'),
(919, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:06'),
(920, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:07'),
(921, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:07'),
(922, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:08'),
(923, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:08'),
(924, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:06:08'),
(925, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:12:20'),
(926, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:12:32'),
(927, 4, 'customer', 'Jodel', 'auth.login', 'Authentication', 'user', 4, 'Customer signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:12:56'),
(928, 4, 'customer', 'Jodel', 'auth.logout', 'Authentication', 'user', 4, 'Customer signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:13:29'),
(929, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:13:44'),
(930, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-09-30 16:16:29'),
(931, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-09-30 16:16:31'),
(932, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:17:33'),
(933, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:17:34'),
(934, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:17:35'),
(935, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:17:35'),
(936, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:17:35'),
(937, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:17:36'),
(938, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:17:36'),
(939, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:17:48'),
(940, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:32'),
(941, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:34'),
(942, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:34'),
(943, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:34'),
(944, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:34'),
(945, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:35'),
(946, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:35'),
(947, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:36'),
(948, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:36'),
(949, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:36'),
(950, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:37'),
(951, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:37'),
(952, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:38'),
(953, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:38'),
(954, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:38'),
(955, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:39'),
(956, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:39'),
(957, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:39'),
(958, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:40'),
(959, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:40'),
(960, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:41'),
(961, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:41'),
(962, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:42'),
(963, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:42'),
(964, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:42'),
(965, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:43'),
(966, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:43'),
(967, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:44'),
(968, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:44'),
(969, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:45'),
(970, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:45'),
(971, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:45'),
(972, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:46'),
(973, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:46'),
(974, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:47'),
(975, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:47'),
(976, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:47'),
(977, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:48'),
(978, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:48'),
(979, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:48'),
(980, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:49'),
(981, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:49'),
(982, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:50'),
(983, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:50'),
(984, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:50'),
(985, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:51'),
(986, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:51'),
(987, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:51'),
(988, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:52'),
(989, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:52'),
(990, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:53'),
(991, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:53'),
(992, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:53'),
(993, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:54'),
(994, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:54'),
(995, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:54'),
(996, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:55'),
(997, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:55'),
(998, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:56'),
(999, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:56'),
(1000, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:57'),
(1001, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:57'),
(1002, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:57'),
(1003, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:58'),
(1004, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:58'),
(1005, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:58'),
(1006, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:59'),
(1007, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:18:59'),
(1008, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:00'),
(1009, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:00'),
(1010, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:00'),
(1011, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:01'),
(1012, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:01'),
(1013, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:01'),
(1014, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:02'),
(1015, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:23'),
(1016, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:24'),
(1017, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:24'),
(1018, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:24'),
(1019, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:25'),
(1020, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:25'),
(1021, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:25'),
(1022, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:26'),
(1023, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:26'),
(1024, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:26'),
(1025, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:27'),
(1026, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:27'),
(1027, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:28'),
(1028, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:28'),
(1029, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:28'),
(1030, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:29'),
(1031, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:29'),
(1032, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:30'),
(1033, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:30'),
(1034, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:31'),
(1035, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:31'),
(1036, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:31'),
(1037, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:32'),
(1038, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:32'),
(1039, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:33'),
(1040, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:33'),
(1041, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:34'),
(1042, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:34'),
(1043, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:35'),
(1044, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:35'),
(1045, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:35'),
(1046, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:36'),
(1047, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:36'),
(1048, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:37'),
(1049, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:37'),
(1050, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:37'),
(1051, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:38'),
(1052, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:38'),
(1053, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:38'),
(1054, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:39'),
(1055, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:39'),
(1056, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:40'),
(1057, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:40'),
(1058, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:40'),
(1059, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:41'),
(1060, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:41'),
(1061, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:41'),
(1062, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:42'),
(1063, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:42'),
(1064, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:43'),
(1065, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:43');
INSERT INTO `activity_log` (`log_id`, `user_id`, `actor_role`, `actor_name`, `action`, `module`, `entity_type`, `entity_id`, `summary`, `ip_address`, `user_agent`, `created_at`) VALUES
(1066, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:43'),
(1067, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:44'),
(1068, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:44'),
(1069, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:45'),
(1070, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:45'),
(1071, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:45'),
(1072, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:46'),
(1073, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:46'),
(1074, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:47'),
(1075, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:47'),
(1076, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:47'),
(1077, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:48'),
(1078, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:48'),
(1079, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:49'),
(1080, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:49'),
(1081, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:50'),
(1082, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:50'),
(1083, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:51'),
(1084, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:51'),
(1085, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:51'),
(1086, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:52'),
(1087, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:52'),
(1088, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:53'),
(1089, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-09-30 16:19:53'),
(1090, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:22:40'),
(1091, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:23:13'),
(1092, NULL, 'guest', 'Guest', 'auth.failed', 'Authentication', 'user', NULL, 'Failed sign-in for unknown account \"rhod_duldulaolhian@plpasig.edu.ph\"', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:23:24'),
(1093, 4, 'customer', 'Jodel', 'auth.login', 'Authentication', 'user', 4, 'Customer signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:23:54'),
(1094, 4, 'customer', 'Jodel', 'order.placed', 'Order Management', 'order', 5, 'Placed order #5 for ₱99.00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:27:27'),
(1095, 4, 'customer', 'Jodel', 'auth.logout', 'Authentication', 'user', 4, 'Customer signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:28:06'),
(1096, 1, 'admin', 'admin', 'auth.failed', 'Authentication', 'user', 1, 'Failed sign-in (attempt 1 of 5)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:28:15'),
(1097, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:28:21'),
(1098, 1, 'admin', 'admin', 'order.status', 'Order Management', 'order', 5, 'Moved order #5 from Pending to Processing', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:28:50'),
(1099, 1, 'admin', 'admin', 'order.status', 'Order Management', 'order', 5, 'Moved order #5 from Processing to On Shipping', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:29:07'),
(1100, 1, 'admin', 'admin', 'order.status', 'Order Management', 'order', 5, 'Moved order #5 from On Shipping to Completed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:29:10'),
(1101, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:29:20'),
(1102, 4, 'customer', 'Jodel', 'auth.failed', 'Authentication', 'user', 4, 'Failed sign-in (attempt 1 of 5)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:29:33'),
(1103, 4, 'customer', 'Jodel', 'auth.login', 'Authentication', 'user', 4, 'Customer signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-09-30 16:29:41'),
(1104, NULL, 'guest', 'Guest', 'auth.failed', 'Authentication', 'user', NULL, 'Failed sign-in for unknown account \"rhod_duldulaolhian@plpasig.edu.ph\"', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-03 01:25:44'),
(1105, NULL, 'guest', 'Guest', 'auth.failed', 'Authentication', 'user', NULL, 'Failed sign-in for unknown account \"rhod_duldulaolhian@plpasig.edu.ph\"', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-03 01:25:50'),
(1106, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-03 01:26:03'),
(1107, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-03 01:30:58'),
(1108, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-03 01:34:39'),
(1109, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-03 01:34:51'),
(1110, 6, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 6, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-03 01:36:02'),
(1111, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-03 01:36:10'),
(1112, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/155.0.0.0 Safari/537.36', '2026-10-03 14:42:24'),
(1113, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-10-03 14:53:51'),
(1114, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-10-03 14:54:10'),
(1115, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 14:55:01'),
(1116, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-10-03 15:00:58'),
(1117, 1, 'admin', 'admin', 'shape.uploaded', 'Customization Options', 'shape', 21, 'ZZTest_Valid (image not scanned)', '::1', 'curl/8.18.0', '2026-10-03 15:00:58'),
(1118, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-10-03 15:01:17'),
(1119, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-10-03 15:01:45'),
(1120, 1, 'admin', 'admin', 'shape.uploaded', 'Customization Options', 'shape', 22, 'ZZN_Good (image not scanned)', '::1', 'curl/8.18.0', '2026-10-03 15:01:47'),
(1121, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'curl/8.18.0', '2026-10-03 15:02:15'),
(1122, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-10-03 15:02:16'),
(1123, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-10-03 15:02:17'),
(1124, 1, 'admin', 'admin', 'shape.approved', 'Customization Options', 'shape', 21, 'Shape \"ZZTest_Valid\" approved', '::1', 'curl/8.18.0', '2026-10-03 15:02:18'),
(1125, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:04:51'),
(1126, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:05:10'),
(1127, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:05:14'),
(1128, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:02'),
(1129, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:03'),
(1130, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:03'),
(1131, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:04'),
(1132, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:04'),
(1133, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:04'),
(1134, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:05'),
(1135, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:05'),
(1136, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:05'),
(1137, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:05'),
(1138, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:06'),
(1139, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:06'),
(1140, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:06'),
(1141, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:06'),
(1142, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:07'),
(1143, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:07'),
(1144, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:07'),
(1145, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:08'),
(1146, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:08'),
(1147, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:08'),
(1148, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:09'),
(1149, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:09'),
(1150, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:09'),
(1151, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:10'),
(1152, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:10'),
(1153, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:10'),
(1154, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:10'),
(1155, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:11'),
(1156, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:11'),
(1157, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:11'),
(1158, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:12'),
(1159, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:12'),
(1160, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:12'),
(1161, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:13'),
(1162, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:13'),
(1163, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:13'),
(1164, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:13'),
(1165, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:14'),
(1166, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:14'),
(1167, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:14'),
(1168, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:14'),
(1169, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:15'),
(1170, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:15'),
(1171, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:15'),
(1172, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:15'),
(1173, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:15'),
(1174, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:16'),
(1175, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:16'),
(1176, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:16'),
(1177, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:17'),
(1178, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:17'),
(1179, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:17'),
(1180, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:18'),
(1181, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:18'),
(1182, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:18'),
(1183, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:18'),
(1184, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:19'),
(1185, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:19'),
(1186, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:19'),
(1187, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:20'),
(1188, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:20'),
(1189, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:31'),
(1190, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:32'),
(1191, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:33'),
(1192, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:33'),
(1193, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:33'),
(1194, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:34'),
(1195, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:34'),
(1196, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:34'),
(1197, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:34'),
(1198, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:35'),
(1199, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:35'),
(1200, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:35'),
(1201, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:35'),
(1202, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:36'),
(1203, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:36'),
(1204, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:36'),
(1205, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:37'),
(1206, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:37'),
(1207, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:37'),
(1208, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:38'),
(1209, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:38'),
(1210, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:38'),
(1211, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:38'),
(1212, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:39'),
(1213, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:39'),
(1214, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:39'),
(1215, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:40'),
(1216, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:40'),
(1217, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:40'),
(1218, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:41'),
(1219, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:41'),
(1220, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:41'),
(1221, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:42'),
(1222, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:42'),
(1223, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:42'),
(1224, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:42'),
(1225, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:43'),
(1226, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:43'),
(1227, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:43'),
(1228, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:43'),
(1229, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:44'),
(1230, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:44'),
(1231, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:44'),
(1232, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:44'),
(1233, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:45'),
(1234, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:45'),
(1235, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:45'),
(1236, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:45'),
(1237, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:45'),
(1238, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:46'),
(1239, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:46'),
(1240, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:46'),
(1241, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:46'),
(1242, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:47'),
(1243, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:47'),
(1244, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:47'),
(1245, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:48'),
(1246, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:48'),
(1247, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:48'),
(1248, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:48'),
(1249, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:06:49'),
(1250, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:07'),
(1251, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:09'),
(1252, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:09'),
(1253, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:09'),
(1254, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:10'),
(1255, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:10'),
(1256, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:10'),
(1257, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:10'),
(1258, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:11'),
(1259, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:11'),
(1260, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:11'),
(1261, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:12'),
(1262, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:12'),
(1263, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:12'),
(1264, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:13'),
(1265, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:13'),
(1266, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:13'),
(1267, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:14'),
(1268, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:14');
INSERT INTO `activity_log` (`log_id`, `user_id`, `actor_role`, `actor_name`, `action`, `module`, `entity_type`, `entity_id`, `summary`, `ip_address`, `user_agent`, `created_at`) VALUES
(1269, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:14'),
(1270, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:15'),
(1271, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:15'),
(1272, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:15'),
(1273, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:16'),
(1274, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:16'),
(1275, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:17'),
(1276, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:17'),
(1277, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:17'),
(1278, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:17'),
(1279, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:18'),
(1280, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:18'),
(1281, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:18'),
(1282, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:19'),
(1283, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:19'),
(1284, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:19'),
(1285, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:19'),
(1286, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:20'),
(1287, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:20'),
(1288, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:20'),
(1289, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:20'),
(1290, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:21'),
(1291, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:21'),
(1292, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:21'),
(1293, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:22'),
(1294, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:22'),
(1295, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:22'),
(1296, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:22'),
(1297, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:23'),
(1298, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:23'),
(1299, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:23'),
(1300, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:23'),
(1301, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:24'),
(1302, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:24'),
(1303, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:24'),
(1304, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:24'),
(1305, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:25'),
(1306, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:25'),
(1307, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:25'),
(1308, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:26'),
(1309, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:26'),
(1310, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/155.0.0.0 Safari/537.36', '2026-10-03 15:07:26'),
(1311, 2, 'customer', 'juana', 'auth.login', 'Authentication', 'user', 2, 'Customer signed in', '::1', 'curl/8.18.0', '2026-10-03 15:07:40'),
(1312, 6, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 6, 'Staff signed in', '::1', 'curl/8.18.0', '2026-10-03 15:07:44'),
(1318, 1, 'admin', 'admin', 'auth.failed', 'Authentication', 'user', 1, 'Failed sign-in (attempt 1 of 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 OPR/136.0.0.0', '2026-10-03 16:20:48'),
(1319, 1, 'admin', 'admin', 'auth.failed', 'Authentication', 'user', 1, 'Failed sign-in (attempt 2 of 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 OPR/136.0.0.0', '2026-10-03 16:21:08'),
(1320, 6, 'staff', 'staff', 'auth.failed', 'Authentication', 'user', 6, 'Failed sign-in (attempt 1 of 3)', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 OPR/136.0.0.0', '2026-10-03 16:26:46'),
(1321, 1, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 OPR/136.0.0.0', '2026-10-03 16:27:05'),
(1322, 1, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 1, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 OPR/136.0.0.0', '2026-10-03 16:28:52'),
(1323, 10, 'admin', 'admin', 'auth.login', 'Authentication', 'user', 10, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 OPR/136.0.0.0', '2026-10-03 16:39:12'),
(1324, 10, 'admin', 'admin', 'auth.logout', 'Authentication', 'user', 10, 'Administrator signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 OPR/136.0.0.0', '2026-10-03 16:39:22'),
(1325, 11, 'staff', 'staff', 'auth.login', 'Authentication', 'user', 11, 'Staff signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 OPR/136.0.0.0', '2026-10-03 16:39:34'),
(1326, 11, 'staff', 'staff', 'auth.logout', 'Authentication', 'user', 11, 'Staff signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 OPR/136.0.0.0', '2026-10-03 16:39:38'),
(1327, 12, 'customer', 'customer', 'auth.login', 'Authentication', 'user', 12, 'Customer signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 OPR/136.0.0.0', '2026-10-03 16:39:52'),
(1328, 12, 'customer', 'customer', 'auth.logout', 'Authentication', 'user', 12, 'Customer signed out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 OPR/136.0.0.0', '2026-10-03 16:39:55'),
(1329, NULL, 'guest', 'Guest', 'auth.failed', 'Authentication', 'user', NULL, 'Failed sign-in for unknown account \"duldulao_rhodlhian@plpasig.edu.ph\"', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 OPR/136.0.0.0', '2026-10-03 16:40:19'),
(1330, 1, 'admin', 'admin_old', 'auth.login', 'Authentication', 'user', 1, 'Administrator signed in', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/152.0.0.0 Safari/537.36 OPR/136.0.0.0', '2026-10-03 16:45:37');

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `cart_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`cart_id`, `user_id`, `created_at`) VALUES
(1, 2, '2026-09-20 16:55:48'),
(2, 3, '2026-09-20 16:58:02'),
(3, 4, '2026-09-21 06:39:04'),
(4, 1, '2026-09-25 03:42:36'),
(6, 6, '2026-09-30 16:22:56');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `cart_item_id` int(10) UNSIGNED NOT NULL,
  `cart_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `shape_id` int(10) UNSIGNED DEFAULT NULL,
  `design_id` int(10) UNSIGNED DEFAULT NULL,
  `custom_text` varchar(120) DEFAULT NULL,
  `custom_image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`cart_item_id`, `cart_id`, `product_id`, `quantity`, `shape_id`, `design_id`, `custom_text`, `custom_image_path`, `created_at`) VALUES
(12, 1, 1, 2, NULL, NULL, NULL, NULL, '2026-10-03 14:54:11');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `created_at`) VALUES
(1, 'Classic Bookmarks', 'Ready-made bookmark designs, no customization required test', '2026-09-14 18:15:21'),
(2, 'Personalized Bookmarks', 'Fully customizable bookmarks with your own photo and text', '2026-09-14 18:15:21'),
(3, 'Gift Sets', 'Bundled bookmark sets for gifting', '2026-09-14 18:15:21');

-- --------------------------------------------------------

--
-- Table structure for table `designs`
--

CREATE TABLE `designs` (
  `design_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  -- Added after this dump was taken: the admin upload form requires a type,
  -- and shape_save.php / design_save.php write it. Existing rows fall back to
  -- the default, so the INSERTs below need no change.
  `category` varchar(40) NOT NULL DEFAULT 'Uncategorized',
  `image_path` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_note` varchar(255) DEFAULT NULL,
  `virus_scanned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `designs`
--

INSERT INTO `designs` (`design_id`, `name`, `image_path`, `status`, `uploaded_by`, `reviewed_by`, `reviewed_at`, `review_note`, `virus_scanned`, `created_at`) VALUES
(1, 'Floral', 'floral.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-14 18:15:21'),
(2, 'Minimalist Lines', 'minimalist.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-14 18:15:21'),
(3, 'Starry Night', 'starry-night.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-14 18:15:21'),
(4, 'Plain Kraft', 'plain-kraft.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-14 18:15:21'),
(5, 'Minimal Dots', 'minimal-dot.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(6, 'Botanical', 'botanical.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(7, 'Pastel Waves', 'pastel-wave.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(8, 'Cute Hearts', 'cute-hearts.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(9, 'Quote Card', 'quote-lines.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(10, 'Geometric', 'geometric.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(11, 'Checkerboard', 'checker.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(12, 'Autumn', 'autumn.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(13, 'Abstract', 'abstract.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54');

-- --------------------------------------------------------

--
-- Table structure for table `email_verification_tokens`
--

CREATE TABLE `email_verification_tokens` (
  `token_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token_hash` char(64) NOT NULL,
  `purpose` enum('activation') NOT NULL DEFAULT 'activation',
  `expires_at` datetime NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback_messages`
--

CREATE TABLE `feedback_messages` (
  `feedback_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `feedback_messages`
--

INSERT INTO `feedback_messages` (`feedback_id`, `name`, `email`, `message`, `created_at`) VALUES
(1, 'Test Reader', 'test@example.com', 'Love the new look', '2026-09-20 16:58:01');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `contact_number` varchar(30) NOT NULL,
  `delivery_address` varchar(255) NOT NULL,
  `payment_method` enum('Cash on Delivery','Bank Transfer') NOT NULL DEFAULT 'Cash on Delivery',
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Processing','On Shipping','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `full_name`, `contact_number`, `delivery_address`, `payment_method`, `total_amount`, `status`, `created_at`, `updated_at`) VALUES
(1, 2, 'Juana Dela Cruz', '09179876543', '123 Rizal St, Manila', 'Bank Transfer', 247.00, 'Processing', '2026-09-20 16:56:23', '2026-09-20 16:57:03'),
(2, 2, 'Juana Dela Cruz', '09179876543', '123 Rizal St, Manila', 'Cash on Delivery', 99.00, 'Pending', '2026-09-25 03:34:05', '2026-09-28 12:44:39'),
(3, 2, 'Juana Dela Cruz', '09179876543', '123 Rizal St, Manila', 'Cash on Delivery', 129.00, 'Pending', '2026-09-25 04:11:23', '2026-09-25 05:48:44'),
(4, 2, 'Juana Dela Cruz', '09179876543', '45 Mabini Ave, Manila', 'Cash on Delivery', 297.00, 'Pending', '2026-09-25 06:33:20', '2026-09-25 06:33:20'),
(5, 4, 'Jodel Apoli', '09922384124', '79 C. Raymundo Ave, Pasig, Metro Manila, 1600', 'Cash on Delivery', 99.00, 'Completed', '2026-09-30 16:27:26', '2026-09-30 16:29:10');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `product_name_snapshot` varchar(150) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `shape_id` int(10) UNSIGNED DEFAULT NULL,
  `shape_name_snapshot` varchar(100) DEFAULT NULL,
  `design_id` int(10) UNSIGNED DEFAULT NULL,
  `design_name_snapshot` varchar(100) DEFAULT NULL,
  `custom_text` varchar(120) DEFAULT NULL,
  `custom_image_path` varchar(255) DEFAULT NULL,
  `line_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `product_id`, `product_name_snapshot`, `unit_price`, `quantity`, `shape_id`, `shape_name_snapshot`, `design_id`, `design_name_snapshot`, `custom_text`, `custom_image_path`, `line_total`) VALUES
(1, 1, 1, 'Photo Memory Bookmark', 99.00, 2, 3, 'Heart', 1, 'Floral', 'For Mama, who taught me to read', NULL, 198.00),
(2, 1, 3, 'Classic Floral Bookmark', 49.00, 1, NULL, NULL, NULL, NULL, NULL, NULL, 49.00),
(3, 2, 1, 'Photo Memory Bookmark', 99.00, 1, 1, 'Rectangular', 2, 'Minimalist Lines', 'Keep going.', NULL, 99.00),
(4, 3, 5, 'Reader\'s Gift Set (3-pack)', 129.00, 1, NULL, NULL, NULL, NULL, NULL, NULL, 129.00),
(5, 4, 1, 'Photo Memory Bookmark', 99.00, 3, 3, 'Heart', 1, 'Floral', 'ThisIsWayOverTwentyC', NULL, 297.00),
(6, 5, 1, 'Photo Memory Bookmark', 99.00, 1, 13, 'Cat', 13, 'Abstract', 'ily ayesha', NULL, 99.00);

-- --------------------------------------------------------

--
-- Table structure for table `password_history`
--

CREATE TABLE `password_history` (
  `history_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(10) UNSIGNED NOT NULL,
  `category_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `low_stock_threshold` int(10) UNSIGNED NOT NULL DEFAULT 5,
  `image_path` varchar(255) NOT NULL,
  `is_customizable` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `name`, `description`, `price`, `stock_quantity`, `low_stock_threshold`, `image_path`, `is_customizable`, `created_at`, `updated_at`) VALUES
(1, 2, 'Photo Memory Bookmark', 'Upload your favorite photo and a short message on a durable laminated bookmark.', 99.00, 65, 5, 'photo-memory.svg', 1, '2026-09-14 18:15:22', '2026-09-30 16:27:27'),
(2, 2, 'Name & Quote Bookmark', 'Add your name and a favorite quote to a custom-shaped bookmark.', 79.00, 35, 5, 'name-quote.svg', 1, '2026-09-14 18:15:22', '2026-09-27 13:57:33'),
(3, 1, 'Classic Floral Bookmark', 'Pre-made floral design bookmark, ready to ship.', 49.00, 99, 5, 'classic-floral.svg', 0, '2026-09-14 18:15:22', '2026-09-22 15:23:54'),
(4, 1, 'Minimalist Line Art Bookmark', 'Simple, elegant line-art bookmark for everyday reading.', 45.00, 16, 1, 'minimalist-lineart.svg', 0, '2026-09-14 18:15:22', '2026-09-29 01:01:47'),
(5, 3, 'Reader\'s Gift Set (3-pack)', 'A set of three classic bookmarks, ready for gifting.', 129.00, 24, 5, 'gift-set.svg', 0, '2026-09-14 18:15:22', '2026-09-25 04:11:23');

-- --------------------------------------------------------

--
-- Table structure for table `saved_designs`
--

CREATE TABLE `saved_designs` (
  `saved_design_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED DEFAULT NULL,
  `design_name` varchar(100) NOT NULL,
  `shape_id` int(10) UNSIGNED DEFAULT NULL,
  `design_id` int(10) UNSIGNED DEFAULT NULL,
  `custom_text` varchar(120) DEFAULT NULL,
  `custom_image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `saved_designs`
--

INSERT INTO `saved_designs` (`saved_design_id`, `user_id`, `product_id`, `design_name`, `shape_id`, `design_id`, `custom_text`, `custom_image_path`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Mama bookmark v2', 1, 2, 'Keep going.', NULL, '2026-09-25 03:32:37', '2026-09-25 03:33:26');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(60) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`, `updated_at`) VALUES
('allow_registration', '1', '2026-09-25 03:35:27'),
('lockout_minutes', '15', '2026-09-28 13:56:02'),
('low_stock_default', '5', '2026-09-25 08:52:32'),
('max_login_attempts', '3', '2026-10-03 15:09:02'),
('mfa_required_roles', 'admin,staff', '2026-09-27 14:15:19'),
('password_expiry_days', '90', '2026-09-27 14:15:19'),
('password_min_length', '12', '2026-09-27 14:15:19'),
('session_timeout_secs', '120', '2026-09-28 18:19:38'),
('store_address', 'Manila, Philippines', '2026-09-22 15:23:57'),
('store_email', 'hello@markme.test', '2026-09-22 15:23:57'),
('store_name', 'MarkMe', '2026-09-22 15:23:57'),
('store_phone', '09171234567', '2026-09-22 15:23:57'),
('store_tagline', 'Bookmarks worth keeping', '2026-09-25 03:35:08');

-- --------------------------------------------------------

--
-- Table structure for table `shapes`
--

CREATE TABLE `shapes` (
  `shape_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  -- Added after this dump was taken: the admin upload form requires a type,
  -- and shape_save.php / design_save.php write it. Existing rows fall back to
  -- the default, so the INSERTs below need no change.
  `category` varchar(40) NOT NULL DEFAULT 'Uncategorized',
  `image_path` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `uploaded_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_by` int(10) UNSIGNED DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_note` varchar(255) DEFAULT NULL,
  `virus_scanned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shapes`
--

INSERT INTO `shapes` (`shape_id`, `name`, `image_path`, `status`, `uploaded_by`, `reviewed_by`, `reviewed_at`, `review_note`, `virus_scanned`, `created_at`) VALUES
(1, 'Classic Rectangle', 'rectangle.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-14 18:15:21'),
(2, 'Rounded Rectangle', 'rounded.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-14 18:15:21'),
(3, 'Heart', 'heart.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-14 18:15:21'),
(4, 'Bookworm Tassel', 'tassel.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-14 18:15:21'),
(5, 'Arched', 'arched.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(6, 'Notched', 'notched.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(7, 'Circle', 'circle.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(8, 'Oval', 'oval.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(9, 'Star', 'star.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(10, 'Cloud', 'cloud.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(11, 'Flower', 'flower.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(12, 'Leaf', 'leaf.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(13, 'Cat', 'cat.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(14, 'Ribbon', 'ribbon.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(15, 'Corner Bookmark', 'corner.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(16, 'Ticket', 'ticket.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(17, 'Hexagon', 'hexagon.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54'),
(18, 'Shield', 'shield.svg', 'approved', NULL, NULL, NULL, NULL, 0, '2026-09-25 06:27:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(10) UNSIGNED NOT NULL,
  `role` enum('customer','staff','admin') NOT NULL DEFAULT 'customer',
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `mfa_secret` varchar(64) DEFAULT NULL,
  `mfa_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `mfa_confirmed_at` timestamp NULL DEFAULT NULL,
  `password_changed_at` timestamp NULL DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `avatar_path` varchar(255) DEFAULT NULL,
  `contact_number` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `postal_code` varchar(10) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `activated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role`, `username`, `email`, `password_hash`, `mfa_secret`, `mfa_enabled`, `mfa_confirmed_at`, `password_changed_at`, `full_name`, `avatar_path`, `contact_number`, `address`, `city`, `province`, `postal_code`, `is_active`, `activated_at`, `created_at`) VALUES
(1, 'admin', 'admin_old', 'duldulao_rhodlhian@plpasig.edu.ph', '$2y$10$.s2jyG8drFsvfxx1Zqk1QeVQUfqteDBxhtmafIs3SdH6sPwdu9qa.', NULL, 0, NULL, '2026-09-14 18:15:21', 'Rhodlhian Duldulao', NULL, '09171234567', 'MarkMe HQ', NULL, NULL, NULL, 1, '2026-09-14 18:15:21', '2026-09-14 18:15:21'),
(2, 'customer', 'juana', 'juana@example.test', '$2y$10$VHHb.JrzIPO25O40Hu.6UOL37DGJwfM7rNF31vd2T9u/DuiUM3oNi', NULL, 0, NULL, '2026-09-14 18:15:21', 'Juana Dela Cruz', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-09-14 18:15:21', '2026-09-14 18:15:21'),
(3, 'customer', 'maria_test', 'maria.test@example.com', '$2y$10$dJ6AsrdWL9goz4f2dujnf.jmQqOaC2G1lB0Y1ZymNcjdV.FzjUuJi', NULL, 0, NULL, '2026-09-20 16:58:02', 'Maria Santos', NULL, '09171112222', '45 Mabini St, Cebu', NULL, NULL, NULL, 1, '2026-09-20 16:58:02', '2026-09-20 16:58:02'),
(4, 'customer', 'Jodel', 'apoli_jodel@plpasig.edu.ph', '$2y$10$9G9KgQKdAt83/92DOzIo1OTrUx4Gq6CXGZagvkaqGW0s/TvtdGONO', NULL, 0, NULL, '2026-09-21 06:39:03', 'Jodel Apoli', NULL, '09922384124', '79 C. Raymundo Ave', 'Pasig', 'Metro Manila', '1600', 1, '2026-09-21 06:39:03', '2026-09-21 06:39:03'),
(6, 'staff', 'staff_old', 'samillano_johnrey@plpasig.edu.ph', '$2y$10$yMdvv.IcMr8kP1vvORlCNu0Qh71eRCxwO0x3YdUrJmkltm.asth7e', NULL, 0, NULL, '2026-09-25 05:43:08', 'John Rey Samillano', NULL, '09171112233', 'MarkMe HQ', NULL, NULL, NULL, 1, '2026-09-25 05:43:08', '2026-09-25 05:43:08'),
(10, 'admin', 'admin', 'admin@markme.test', '$2y$12$kY5Y1FrPqPbPboMltY/MseIyW1d/iCOaMGGswN4BSKENr64Y3cI3G', NULL, 0, NULL, '2026-10-03 16:38:06', 'System Administrator', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-10-03 16:38:06', '2026-10-03 16:38:06'),
(11, 'staff', 'staff', 'staff@markme.test', '$2y$12$dJeTQlVywd56ORJluRB6V.EK1M3Z9W6GSC4J2EcdZ7ynINXjjjBW6', NULL, 0, NULL, '2026-10-03 16:38:06', 'Staff Member', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-10-03 16:38:06', '2026-10-03 16:38:06'),
(12, 'customer', 'customer', 'customer@markme.test', '$2y$12$9/0Ss/8NEm022jfw4N86l.iEw5nZNggIquU7r6JEIH87/QHwJuqOe', NULL, 0, NULL, '2026-10-03 16:38:07', 'Test Customer', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-10-03 16:38:07', '2026-10-03 16:38:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_locks`
--
ALTER TABLE `account_locks`
  ADD PRIMARY KEY (`lock_id`),
  ADD UNIQUE KEY `uq_account_locks_user` (`user_id`),
  ADD KEY `idx_account_locks_locked` (`locked_at`);

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `fk_activity_user` (`user_id`),
  ADD KEY `idx_activity_created` (`created_at`),
  ADD KEY `idx_activity_role` (`actor_role`),
  ADD KEY `idx_activity_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_activity_module` (`module`),
  ADD KEY `idx_activity_action` (`action`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`cart_item_id`),
  ADD KEY `fk_cart_items_shape` (`shape_id`),
  ADD KEY `fk_cart_items_design` (`design_id`),
  ADD KEY `idx_cart_items_cart` (`cart_id`),
  ADD KEY `idx_cart_items_product` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `designs`
--
ALTER TABLE `designs`
  ADD PRIMARY KEY (`design_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_designs_status` (`status`),
  ADD KEY `fk_designs_uploader` (`uploaded_by`),
  ADD KEY `fk_designs_reviewer` (`reviewed_by`);

--
-- Indexes for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD PRIMARY KEY (`token_id`),
  ADD UNIQUE KEY `uq_evt_hash` (`token_hash`),
  ADD KEY `idx_evt_user` (`user_id`),
  ADD KEY `idx_evt_expires` (`expires_at`);

--
-- Indexes for table `feedback_messages`
--
ALTER TABLE `feedback_messages`
  ADD PRIMARY KEY (`feedback_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `fk_order_items_shape` (`shape_id`),
  ADD KEY `fk_order_items_design` (`design_id`),
  ADD KEY `idx_order_items_order` (`order_id`),
  ADD KEY `idx_order_items_product` (`product_id`);

--
-- Indexes for table `password_history`
--
ALTER TABLE `password_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `idx_pwhist_user` (`user_id`,`created_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_name` (`name`);

--
-- Indexes for table `saved_designs`
--
ALTER TABLE `saved_designs`
  ADD PRIMARY KEY (`saved_design_id`),
  ADD KEY `fk_saved_designs_product` (`product_id`),
  ADD KEY `fk_saved_designs_shape` (`shape_id`),
  ADD KEY `fk_saved_designs_design` (`design_id`),
  ADD KEY `idx_saved_designs_user` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `shapes`
--
ALTER TABLE `shapes`
  ADD PRIMARY KEY (`shape_id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_shapes_status` (`status`),
  ADD KEY `fk_shapes_uploader` (`uploaded_by`),
  ADD KEY `fk_shapes_reviewer` (`reviewed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_locks`
--
ALTER TABLE `account_locks`
  MODIFY `lock_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `log_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1331;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `cart_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `cart_item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `designs`
--
ALTER TABLE `designs`
  MODIFY `design_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  MODIFY `token_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `feedback_messages`
--
ALTER TABLE `feedback_messages`
  MODIFY `feedback_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `password_history`
--
ALTER TABLE `password_history`
  MODIFY `history_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `saved_designs`
--
ALTER TABLE `saved_designs`
  MODIFY `saved_design_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `shapes`
--
ALTER TABLE `shapes`
  MODIFY `shape_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `account_locks`
--
ALTER TABLE `account_locks`
  ADD CONSTRAINT `fk_account_locks_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `fk_carts_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `fk_cart_items_cart` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`cart_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cart_items_design` FOREIGN KEY (`design_id`) REFERENCES `designs` (`design_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cart_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cart_items_shape` FOREIGN KEY (`shape_id`) REFERENCES `shapes` (`shape_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `designs`
--
ALTER TABLE `designs`
  ADD CONSTRAINT `fk_designs_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_designs_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `email_verification_tokens`
--
ALTER TABLE `email_verification_tokens`
  ADD CONSTRAINT `fk_evt_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_design` FOREIGN KEY (`design_id`) REFERENCES `designs` (`design_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_order_items_shape` FOREIGN KEY (`shape_id`) REFERENCES `shapes` (`shape_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `password_history`
--
ALTER TABLE `password_history`
  ADD CONSTRAINT `fk_pwhist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON UPDATE CASCADE;

--
-- Constraints for table `saved_designs`
--
ALTER TABLE `saved_designs`
  ADD CONSTRAINT `fk_saved_designs_design` FOREIGN KEY (`design_id`) REFERENCES `designs` (`design_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_saved_designs_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_saved_designs_shape` FOREIGN KEY (`shape_id`) REFERENCES `shapes` (`shape_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_saved_designs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `shapes`
--
ALTER TABLE `shapes`
  ADD CONSTRAINT `fk_shapes_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_shapes_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
