-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2025 at 10:24 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `origin_driving`
--

-- --------------------------------------------------------

--
-- Table structure for table `attachments`
--

CREATE TABLE `attachments` (
  `id` int(11) NOT NULL,
  `related_type` varchar(50) NOT NULL,
  `related_id` int(11) NOT NULL,
  `filename` varchar(191) NOT NULL,
  `path` varchar(255) NOT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `size_bytes` int(11) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(120) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `address_line1` varchar(191) DEFAULT NULL,
  `address_line2` varchar(191) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `location` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `phone`, `email`, `address_line1`, `address_line2`, `city`, `state`, `postal_code`, `created_at`, `location`) VALUES
(1, 'CBD Branch', '04023432434', 'cbdods@gmail.com', '', '', 'Melbourne', '', '', '2025-09-29 08:51:42', 'Melbourne Central'),
(2, 'Richmond', '040032232032', 'richmondods@gmail.com', '', '', 'Richmond', 'Victoria', 'V3M 0B2', '2025-09-29 09:23:53', 'Richmond'),
(3, 'Docklands Branch', '+6134793263', 'docklandsods@gmail.com', NULL, NULL, NULL, NULL, NULL, '2025-09-29 20:37:23', 'Docklands');

-- --------------------------------------------------------

--
-- Table structure for table `broadcasts`
--

CREATE TABLE `broadcasts` (
  `id` int(11) NOT NULL,
  `channel` enum('email','sms','inapp') NOT NULL,
  `roles_csv` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `broadcasts`
--

INSERT INTO `broadcasts` (`id`, `channel`, `roles_csv`, `title`, `body`, `created_by`, `created_at`) VALUES
(1, 'inapp', 'student', 'lesson', 'attend', 1, '2025-09-29 12:33:05'),
(2, 'inapp', 'student', 'lesson', 'attend', 1, '2025-09-29 12:38:34'),
(3, 'inapp', 'student', 'lesson', 'attend', 1, '2025-09-29 12:38:39'),
(4, 'inapp', 'student', 'xssdcd', 'xsxs', 1, '2025-09-29 12:38:48'),
(5, 'inapp', 'student', 'xssdcd', 'xsxs', 1, '2025-09-29 12:41:06'),
(6, 'inapp', 'student', 'xssdcd', 'xsxs', 1, '2025-09-29 12:41:11'),
(7, 'inapp', 'student', 'xssdcd', 'xsxs', 1, '2025-09-29 12:44:39'),
(8, 'inapp', 'student', 'lesson', 'you have a beginner lesson on 10th october', 1, '2025-09-30 01:09:34'),
(9, 'inapp', 'student', 'exam', 'you will have an exam on 5th october', 1, '2025-09-30 01:15:27'),
(10, 'inapp', 'student', 'exam', 'you will have an exam on 5th october', 1, '2025-09-30 01:16:39'),
(11, 'inapp', 'instructor', 'schedule change', 'check your schedule', 1, '2025-09-30 01:17:10'),
(12, 'inapp', 'student', 'sessions', 'attend the sessions', 1, '2025-09-30 08:27:16');

-- --------------------------------------------------------

--
-- Table structure for table `broadcast_recipients`
--

CREATE TABLE `broadcast_recipients` (
  `id` int(11) NOT NULL,
  `broadcast_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `to_addr` varchar(255) DEFAULT NULL,
  `status` enum('queued','sent','failed','skipped') NOT NULL DEFAULT 'queued',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `broadcast_recipients`
--

INSERT INTO `broadcast_recipients` (`id`, `broadcast_id`, `user_id`, `to_addr`, `status`, `created_at`) VALUES
(1, 7, 1, NULL, 'queued', '2025-09-29 12:44:39'),
(2, 7, 2, NULL, 'queued', '2025-09-29 12:44:39'),
(3, 7, 3, NULL, 'queued', '2025-09-29 12:44:39'),
(4, 8, 1, NULL, 'queued', '2025-09-30 01:09:34'),
(5, 8, 2, NULL, 'queued', '2025-09-30 01:09:34'),
(6, 8, 3, NULL, 'queued', '2025-09-30 01:09:35'),
(7, 8, 4, NULL, 'queued', '2025-09-30 01:09:35'),
(8, 8, 5, NULL, 'queued', '2025-09-30 01:09:35'),
(9, 8, 6, NULL, 'queued', '2025-09-30 01:09:35'),
(10, 8, 7, NULL, 'queued', '2025-09-30 01:09:35'),
(11, 8, 8, NULL, 'queued', '2025-09-30 01:09:35'),
(12, 9, 1, NULL, 'queued', '2025-09-30 01:15:27'),
(13, 9, 2, NULL, 'queued', '2025-09-30 01:15:27'),
(14, 9, 3, NULL, 'queued', '2025-09-30 01:15:27'),
(15, 9, 4, NULL, 'queued', '2025-09-30 01:15:27'),
(16, 9, 5, NULL, 'queued', '2025-09-30 01:15:27'),
(17, 9, 6, NULL, 'queued', '2025-09-30 01:15:27'),
(18, 9, 7, NULL, 'queued', '2025-09-30 01:15:27'),
(19, 9, 8, NULL, 'queued', '2025-09-30 01:15:27'),
(20, 10, 1, NULL, 'queued', '2025-09-30 01:16:39'),
(21, 10, 2, NULL, 'queued', '2025-09-30 01:16:39'),
(22, 10, 3, NULL, 'queued', '2025-09-30 01:16:39'),
(23, 10, 4, NULL, 'queued', '2025-09-30 01:16:39'),
(24, 10, 5, NULL, 'queued', '2025-09-30 01:16:39'),
(25, 10, 6, NULL, 'queued', '2025-09-30 01:16:39'),
(26, 10, 7, NULL, 'queued', '2025-09-30 01:16:39'),
(27, 10, 8, NULL, 'queued', '2025-09-30 01:16:39'),
(28, 12, 1, NULL, 'queued', '2025-09-30 08:27:16'),
(29, 12, 2, NULL, 'queued', '2025-09-30 08:27:16'),
(30, 12, 3, NULL, 'queued', '2025-09-30 08:27:16'),
(31, 12, 4, NULL, 'queued', '2025-09-30 08:27:16'),
(32, 12, 5, NULL, 'queued', '2025-09-30 08:27:16'),
(33, 12, 6, NULL, 'queued', '2025-09-30 08:27:16'),
(34, 12, 7, NULL, 'queued', '2025-09-30 08:27:16'),
(35, 12, 8, NULL, 'queued', '2025-09-30 08:27:16'),
(36, 12, 9, NULL, 'queued', '2025-09-30 08:27:16');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price_cents` int(11) NOT NULL DEFAULT 0,
  `lessons_count` int(11) NOT NULL DEFAULT 0,
  `total_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `branch_id`, `name`, `description`, `price_cents`, `lessons_count`, `total_hours`, `is_active`, `created_at`) VALUES
(1, 1, 'Beginner Package', 'Introductory driving lessons', 250000, 10, 15.00, 1, '2025-09-29 08:51:42'),
(2, 2, 'Road Test', 'Road test based on distance', 200000, 5, 8.00, 1, '2025-09-29 09:25:44'),
(3, 3, 'Parking', 'Parking on steep hills, CBD and other challenging areas', 15000, 5, 10.00, 1, '2025-09-30 09:29:40');

-- --------------------------------------------------------

--
-- Table structure for table `course_instructors`
--

CREATE TABLE `course_instructors` (
  `course_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `status` enum('active','completed','cancelled') NOT NULL DEFAULT 'active',
  `enrolled_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `status`, `enrolled_at`) VALUES
(2, 1, 2, 'active', '2025-09-29 13:18:00'),
(3, 1, 1, 'active', '2025-09-29 18:51:37'),
(4, 2, 3, 'active', '2025-09-30 11:41:13');

-- --------------------------------------------------------

--
-- Table structure for table `entity_files`
--

CREATE TABLE `entity_files` (
  `id` int(11) NOT NULL,
  `note_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `path` varchar(500) NOT NULL,
  `size` int(11) NOT NULL DEFAULT 0,
  `mime` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_notes`
--

CREATE TABLE `entity_notes` (
  `id` int(11) NOT NULL,
  `entity_type` varchar(32) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exams`
--

CREATE TABLE `exams` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `location` varchar(191) DEFAULT NULL,
  `result` enum('pending','pass','fail') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_bookings`
--

CREATE TABLE `exam_bookings` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `enrollment_id` int(11) DEFAULT NULL,
  `status` enum('booked','attended','no_show','cancelled') NOT NULL DEFAULT 'booked',
  `score` decimal(5,2) DEFAULT NULL,
  `result` enum('pending','pass','fail') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_bookings`
--

INSERT INTO `exam_bookings` (`id`, `session_id`, `student_id`, `enrollment_id`, `status`, `score`, `result`, `notes`, `created_by`, `created_at`) VALUES
(1, 1, 1, 2, 'booked', NULL, 'pending', NULL, 1, '2025-09-29 11:09:25'),
(2, 2, 2, 3, 'booked', NULL, 'pending', NULL, 1, '2025-09-30 05:17:00');

-- --------------------------------------------------------

--
-- Table structure for table `exam_sessions`
--

CREATE TABLE `exam_sessions` (
  `id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_sessions`
--

INSERT INTO `exam_sessions` (`id`, `type_id`, `start_time`, `end_time`, `location`, `notes`, `created_by`, `created_at`) VALUES
(1, 2, '2025-09-30 11:08:00', '2025-10-01 11:09:00', 'CBD', 'ddwedewde', 1, '2025-09-29 11:09:13'),
(2, 1, '2025-10-04 05:16:00', '2025-10-10 05:16:00', 'Docklands', 'dfsdfsd', 1, '2025-09-30 05:16:26');

-- --------------------------------------------------------

--
-- Table structure for table `exam_types`
--

CREATE TABLE `exam_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exam_types`
--

INSERT INTO `exam_types` (`id`, `name`) VALUES
(2, 'practical'),
(1, 'theory');

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `license_no` varchar(100) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`id`, `user_id`, `branch_id`, `license_no`, `hire_date`, `status`, `created_at`) VALUES
(1, 2, 1, '434323', '2025-09-28', 'active', '2025-09-29 10:12:13'),
(2, 7, 3, '234309', '2025-09-19', 'active', '2025-09-30 07:46:42'),
(3, 8, 2, '423974', '2025-09-18', 'active', '2025-09-30 07:48:29'),
(4, 9, 2, '839432', '2025-09-01', 'active', '2025-09-30 09:26:41');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_availability`
--

CREATE TABLE `instructor_availability` (
  `id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `day_of_week` tinyint(4) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_availability`
--

INSERT INTO `instructor_availability` (`id`, `instructor_id`, `day_of_week`, `start_time`, `end_time`) VALUES
(1, 1, 0, '04:15:00', '23:58:00');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `enrollment_id` int(11) DEFAULT NULL,
  `number` varchar(64) DEFAULT NULL,
  `issue_date` date NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('draft','sent','partial','paid','overdue','void') NOT NULL DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 1,
  `total_cents` int(11) NOT NULL DEFAULT 0,
  `balance_cents` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `student_id`, `enrollment_id`, `number`, `issue_date`, `due_date`, `status`, `notes`, `created_by`, `total_cents`, `balance_cents`, `created_at`) VALUES
(1, 1, 2, 'INV-1', '2025-09-29', '2025-10-09', 'sent', 'please pay', 1, 0, 0, '2025-09-29 18:26:08'),
(3, 1, 2, 'INV-20250929-203906-2981', '2025-09-29', '2025-10-02', 'sent', 'please pay', 1, 0, 0, '2025-09-29 18:39:06'),
(4, 1, 2, 'INV-20250929-203912-2822', '2025-09-29', '2025-10-02', 'sent', 'please pay', 1, 0, 0, '2025-09-29 18:39:12'),
(5, 1, 2, 'INV-20250929-204259-7216', '2025-09-29', '2025-10-02', 'sent', 'please pay', 1, 0, 0, '2025-09-29 18:42:59'),
(6, 1, 2, 'INV-20250929-204356-4753', '2025-09-29', '2025-10-02', 'sent', 'please pay', 1, 0, 0, '2025-09-29 18:43:56'),
(7, 1, 2, 'INV-20250929-204801-0034', '2025-09-29', '2025-10-02', 'sent', 'please pay', 1, 0, 0, '2025-09-29 18:48:01'),
(8, 1, 2, 'INV-20250929-205047-3121', '2025-09-29', '2025-10-02', 'sent', 'please pay', 1, 0, 0, '2025-09-29 18:50:47'),
(10, 2, 3, 'INV-20250930-150445-2429', '2025-09-30', '2025-10-03', 'sent', 'make payments', 1, 0, 0, '2025-09-30 13:04:45'),
(11, 2, 2, 'INV-20250930-151533-3641', '2025-09-30', '2025-10-06', 'sent', 'ddfefeds', 1, 0, 0, '2025-09-30 13:15:33');

-- --------------------------------------------------------

--
-- Table structure for table `invoice_items`
--

CREATE TABLE `invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `description` varchar(191) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_cents` int(11) NOT NULL DEFAULT 0,
  `line_total_cents` int(11) GENERATED ALWAYS AS (`quantity` * `unit_cents`) STORED,
  `unit_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(10,2) NOT NULL DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice_items`
--

INSERT INTO `invoice_items` (`id`, `invoice_id`, `description`, `quantity`, `unit_cents`, `unit_price`, `amount`) VALUES
(1, 7, 'road test', 1, 0, 50000.00, 1.00),
(2, 8, 'Road test 100 kms', 1, 0, 26000.00, 1.00);

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `status` enum('scheduled','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `enrollment_id`, `instructor_id`, `vehicle_id`, `start_time`, `end_time`, `status`, `notes`, `created_at`) VALUES
(1, 2, 1, 1, '2025-09-30 06:36:00', '2025-10-02 06:36:00', 'completed', 'xsxs\r\nAttachment: /origin-driving/uploads/lesson_notes/1/Student_notes.pdf\r\nAttachment: /origin-driving/uploads/lesson_notes/1/Student_notes.pdf', '2025-09-29 13:38:02'),
(2, 3, 1, 1, '2025-10-03 14:15:00', '2025-10-04 14:15:00', 'scheduled', 'hjyuj', '2025-09-29 21:16:04'),
(3, 4, 4, 2, '2025-10-04 04:42:00', '2025-10-06 04:42:00', 'scheduled', 'parking trials', '2025-09-30 11:42:41'),
(4, 3, 1, NULL, '2025-10-01 14:23:00', '2025-10-01 15:24:00', '', NULL, '2025-09-30 19:21:53');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `channel` enum('email','sms') NOT NULL,
  `to_address` varchar(191) NOT NULL,
  `subject` varchar(191) DEFAULT NULL,
  `body` text NOT NULL,
  `status` enum('queued','sent','failed') NOT NULL DEFAULT 'queued',
  `related_type` varchar(50) DEFAULT NULL,
  `related_id` int(11) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `related_type` varchar(50) NOT NULL,
  `related_id` int(11) NOT NULL,
  `author_user_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `is_private` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(191) NOT NULL,
  `body` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` datetime DEFAULT NULL,
  `link_url` varchar(512) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `title`, `body`, `created_at`, `read_at`, `link_url`, `is_read`) VALUES
(1, 1, 'xssdcd', 'xsxs', '2025-09-29 19:44:39', NULL, NULL, 0),
(2, 2, 'xssdcd', 'xsxs', '2025-09-29 19:44:39', NULL, NULL, 0),
(4, 1, 'lesson', 'you have a beginner lesson on 10th october', '2025-09-30 08:09:34', NULL, NULL, 0),
(5, 2, 'lesson', 'you have a beginner lesson on 10th october', '2025-09-30 08:09:34', NULL, NULL, 0),
(8, 5, 'lesson', 'you have a beginner lesson on 10th october', '2025-09-30 08:09:35', NULL, NULL, 0),
(9, 6, 'lesson', 'you have a beginner lesson on 10th october', '2025-09-30 08:09:35', NULL, NULL, 0),
(10, 7, 'lesson', 'you have a beginner lesson on 10th october', '2025-09-30 08:09:35', NULL, NULL, 0),
(11, 8, 'lesson', 'you have a beginner lesson on 10th october', '2025-09-30 08:09:35', NULL, NULL, 0),
(12, 1, 'exam', 'you will have an exam on 5th october', '2025-09-30 08:15:27', NULL, NULL, 0),
(13, 2, 'exam', 'you will have an exam on 5th october', '2025-09-30 08:15:27', NULL, NULL, 0),
(16, 5, 'exam', 'you will have an exam on 5th october', '2025-09-30 08:15:27', NULL, NULL, 0),
(17, 6, 'exam', 'you will have an exam on 5th october', '2025-09-30 08:15:27', NULL, NULL, 0),
(18, 7, 'exam', 'you will have an exam on 5th october', '2025-09-30 08:15:27', NULL, NULL, 0),
(19, 8, 'exam', 'you will have an exam on 5th october', '2025-09-30 08:15:27', NULL, NULL, 0),
(20, 1, 'exam', 'you will have an exam on 5th october', '2025-09-30 08:16:39', NULL, NULL, 0),
(21, 2, 'exam', 'you will have an exam on 5th october', '2025-09-30 08:16:39', NULL, NULL, 0),
(24, 5, 'exam', 'you will have an exam on 5th october', '2025-09-30 08:16:39', NULL, NULL, 0),
(25, 6, 'exam', 'you will have an exam on 5th october', '2025-09-30 08:16:39', NULL, NULL, 0),
(26, 7, 'exam', 'you will have an exam on 5th october', '2025-09-30 08:16:39', NULL, NULL, 0),
(27, 8, 'exam', 'you will have an exam on 5th october', '2025-09-30 08:16:39', NULL, NULL, 0),
(28, 1, 'sessions', 'attend the sessions', '2025-09-30 15:27:16', NULL, NULL, 0),
(29, 2, 'sessions', 'attend the sessions', '2025-09-30 15:27:16', NULL, NULL, 0),
(32, 5, 'sessions', 'attend the sessions', '2025-09-30 15:27:16', NULL, NULL, 0),
(33, 6, 'sessions', 'attend the sessions', '2025-09-30 15:27:16', NULL, NULL, 0),
(34, 7, 'sessions', 'attend the sessions', '2025-09-30 15:27:16', NULL, NULL, 0),
(35, 8, 'sessions', 'attend the sessions', '2025-09-30 15:27:16', NULL, NULL, 0),
(36, 9, 'sessions', 'attend the sessions', '2025-09-30 15:27:16', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `outbox_messages`
--

CREATE TABLE `outbox_messages` (
  `id` int(11) NOT NULL,
  `channel` enum('email','sms') NOT NULL,
  `to_addr` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text NOT NULL,
  `meta` text DEFAULT NULL,
  `status` enum('queued','sent','failed') NOT NULL DEFAULT 'queued',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `sent_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `amount_cents` int(11) NOT NULL,
  `paid_at` datetime NOT NULL,
  `method` enum('cash','card','mpesa','bank') NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `invoice_id`, `amount_cents`, `paid_at`, `method`, `reference`, `created_at`, `amount`) VALUES
(1, 7, 0, '2025-09-30 10:08:13', 'cash', '', '2025-09-30 08:08:31', 20000.00);

-- --------------------------------------------------------

--
-- Table structure for table `reminders`
--

CREATE TABLE `reminders` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `type` enum('payment_due','class_upcoming') NOT NULL,
  `target_datetime` datetime NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reminders`
--

INSERT INTO `reminders` (`id`, `student_id`, `type`, `target_datetime`, `sent_at`, `payload`, `created_at`) VALUES
(1, 1, '', '0000-00-00 00:00:00', NULL, NULL, '2025-09-29 19:19:43'),
(3, 2, 'payment_due', '0000-00-00 00:00:00', NULL, NULL, '2025-09-30 15:22:38');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`) VALUES
(1, 'admin', '2025-09-29 08:41:33'),
(2, 'staff', '2025-09-29 08:41:33'),
(3, 'instructor', '2025-09-29 08:41:33'),
(4, 'student', '2025-09-29 08:41:33');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `title` varchar(120) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `emergency_contact_name` varchar(120) DEFAULT NULL,
  `emergency_contact_phone` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `user_id`, `branch_id`, `date_of_birth`, `emergency_contact_name`, `emergency_contact_phone`, `created_at`) VALUES
(1, 5, 1, '2000-08-01', '3424334323', '3423432423432', '2025-09-29 11:26:26'),
(2, 6, 3, '2005-01-01', 'Brewser', '04234934324', '2025-09-29 22:00:15');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','staff','instructor','student') NOT NULL DEFAULT 'student'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password_hash`, `first_name`, `last_name`, `phone`, `is_active`, `created_at`, `role`) VALUES
(1, 'admin@kentods.com', '$2y$10$G7xIEG2nO.CwkYb/VCH4CeqQXW9ZB4DHLXV03Gq983BG5jpEuaYFW', 'Site', 'Admin', NULL, 1, '2025-09-29 09:03:55', 'student'),
(2, 'gershon@gmail.com', '$2y$10$ufzGgnziAjSrMnijj6GJkeDgnCAaC9y7b2mBta0juFbbj3aqP1aZW', 'Gershon', 'Mutai', '04414047377', 1, '2025-09-29 10:02:55', 'student'),
(5, 'rotich51@icloud.com', '$2y$10$n3xGUSViJwt3/beBHuUPuezaRRHdeuSuD9AapvYdLyN1x1LH7q5gW', 'Mutai', 'G', '042383403', 1, '2025-09-29 21:27:16', 'student'),
(6, 'alupoi@gmail.com', '$2y$10$qu2J1zzsmXtHUQocxacoOuI3TA8FfRP/Tdq3F3.ylW/SEt17FXCUu', 'alupoi', 'gr', '034382343', 1, '2025-09-29 22:00:15', 'student'),
(7, 'murphy@yahoo.com', '$2y$10$i5zGDzn1zmqf6eyaBjltOexi6s6s4DgqbS1Trnx4cSJBHppuNroky', 'Murphy', 'Makins', '0423432432', 1, '2025-09-30 07:46:42', 'student'),
(8, 'oliver@gmail.com', '$2y$10$Sebv73MmMLFNs1eaoqmNte7Mh8dNHz5Tu.zcQ5uYOcHEDyl3V8pAO', 'Oliver', 'Williams', '04073432343', 1, '2025-09-30 07:48:29', 'student'),
(9, 'lucas@gmail.com', '$2y$10$ADb6m7sdPgQU4Ynf9FgwV.lUo8TTbCz4TAiNlR9w8m754CoYpcLSe', 'Lucas', 'Berrham', '0423384294', 1, '2025-09-30 09:26:41', 'student');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`, `assigned_at`) VALUES
(1, 1, '2025-09-29 09:03:55'),
(2, 3, '2025-09-29 10:02:55'),
(5, 4, '2025-09-29 21:27:16'),
(6, 4, '2025-09-29 22:00:15'),
(7, 3, '2025-09-30 07:46:42'),
(8, 3, '2025-09-30 07:48:29'),
(9, 3, '2025-09-30 09:26:41');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `plate_no` varchar(50) NOT NULL,
  `make` varchar(80) DEFAULT NULL,
  `model` varchar(80) DEFAULT NULL,
  `vehicle_year` smallint(6) DEFAULT NULL,
  `transmission` enum('manual','automatic') DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`id`, `branch_id`, `plate_no`, `make`, `model`, `vehicle_year`, `transmission`, `is_available`, `created_at`) VALUES
(1, NULL, 'WA23F32', 'Toyota', 'Yaris', NULL, 'automatic', 1, '2025-09-29 13:35:48'),
(2, NULL, 'ACT23823', 'Mazda', 'CX3', NULL, 'automatic', 1, '2025-09-30 09:30:31');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_invoice_summary`
-- (See below for the actual view)
--
CREATE TABLE `v_invoice_summary` (
`invoice_id` int(11)
,`items_total_cents` decimal(32,0)
,`payments_total_cents` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Structure for view `v_invoice_summary`
--
DROP TABLE IF EXISTS `v_invoice_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_invoice_summary`  AS SELECT `i`.`id` AS `invoice_id`, coalesce(sum(`ii`.`line_total_cents`),0) AS `items_total_cents`, coalesce((select sum(`p`.`amount_cents`) from `payments` `p` where `p`.`invoice_id` = `i`.`id`),0) AS `payments_total_cents` FROM (`invoices` `i` left join `invoice_items` `ii` on(`ii`.`invoice_id` = `i`.`id`)) GROUP BY `i`.`id` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attachments`
--
ALTER TABLE `attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_attachments_user` (`uploaded_by`),
  ADD KEY `idx_attach_rel` (`related_type`,`related_id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_audit_user` (`user_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `broadcasts`
--
ALTER TABLE `broadcasts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `broadcast_recipients`
--
ALTER TABLE `broadcast_recipients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `broadcast_id` (`broadcast_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_courses_branch` (`branch_id`);

--
-- Indexes for table `course_instructors`
--
ALTER TABLE `course_instructors`
  ADD PRIMARY KEY (`course_id`,`instructor_id`),
  ADD KEY `fk_ci_instructor` (`instructor_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_enroll_course` (`course_id`),
  ADD KEY `idx_enrollments_student` (`student_id`);

--
-- Indexes for table `entity_files`
--
ALTER TABLE `entity_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_entity_files_note` (`note_id`);

--
-- Indexes for table `entity_notes`
--
ALTER TABLE `entity_notes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exams`
--
ALTER TABLE `exams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_exam_instructor_time` (`instructor_id`,`start_time`),
  ADD UNIQUE KEY `ux_exam_student_time` (`enrollment_id`,`start_time`),
  ADD KEY `idx_exams_instructor_time` (`instructor_id`,`start_time`);

--
-- Indexes for table `exam_bookings`
--
ALTER TABLE `exam_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_exam_bookings_session` (`session_id`),
  ADD KEY `fk_exam_bookings_student` (`student_id`),
  ADD KEY `fk_exam_bookings_enroll` (`enrollment_id`);

--
-- Indexes for table `exam_sessions`
--
ALTER TABLE `exam_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_exam_sessions_type` (`type_id`);

--
-- Indexes for table `exam_types`
--
ALTER TABLE `exam_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `fk_instructors_branch` (`branch_id`);

--
-- Indexes for table `instructor_availability`
--
ALTER TABLE `instructor_availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_avail_instructor` (`instructor_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`),
  ADD KEY `fk_invoices_enrollment` (`enrollment_id`),
  ADD KEY `idx_invoices_student` (`student_id`,`issue_date`),
  ADD KEY `idx_invoices_status` (`status`);

--
-- Indexes for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_items_invoice` (`invoice_id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_lesson_instructor_time` (`instructor_id`,`start_time`),
  ADD UNIQUE KEY `ux_lesson_student_time` (`enrollment_id`,`start_time`),
  ADD UNIQUE KEY `ux_lesson_vehicle_time` (`vehicle_id`,`start_time`),
  ADD KEY `idx_lessons_instructor_time` (`instructor_id`,`start_time`),
  ADD KEY `idx_lessons_enrollment_time` (`enrollment_id`,`start_time`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_messages_user` (`user_id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notes_author` (`author_user_id`),
  ADD KEY `idx_notes_rel` (`related_type`,`related_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_user` (`user_id`);

--
-- Indexes for table `outbox_messages`
--
ALTER TABLE `outbox_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_payment_reference` (`reference`),
  ADD KEY `idx_payments_invoice` (`invoice_id`);

--
-- Indexes for table `reminders`
--
ALTER TABLE `reminders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reminders_student` (`student_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `fk_staff_branch` (`branch_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `fk_students_branch` (`branch_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `fk_user_roles_role` (`role_id`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate_no` (`plate_no`),
  ADD KEY `fk_vehicles_branch` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attachments`
--
ALTER TABLE `attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `broadcasts`
--
ALTER TABLE `broadcasts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `broadcast_recipients`
--
ALTER TABLE `broadcast_recipients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `entity_files`
--
ALTER TABLE `entity_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_notes`
--
ALTER TABLE `entity_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exams`
--
ALTER TABLE `exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_bookings`
--
ALTER TABLE `exam_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `exam_sessions`
--
ALTER TABLE `exam_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `exam_types`
--
ALTER TABLE `exam_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `instructor_availability`
--
ALTER TABLE `instructor_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `invoice_items`
--
ALTER TABLE `invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `outbox_messages`
--
ALTER TABLE `outbox_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reminders`
--
ALTER TABLE `reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attachments`
--
ALTER TABLE `attachments`
  ADD CONSTRAINT `fk_attachments_user` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `broadcast_recipients`
--
ALTER TABLE `broadcast_recipients`
  ADD CONSTRAINT `broadcast_recipients_ibfk_1` FOREIGN KEY (`broadcast_id`) REFERENCES `broadcasts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_courses_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `course_instructors`
--
ALTER TABLE `course_instructors`
  ADD CONSTRAINT `fk_ci_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ci_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `fk_enroll_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `fk_enroll_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `entity_files`
--
ALTER TABLE `entity_files`
  ADD CONSTRAINT `fk_entity_files_note` FOREIGN KEY (`note_id`) REFERENCES `entity_notes` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exams`
--
ALTER TABLE `exams`
  ADD CONSTRAINT `fk_exams_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exams_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`);

--
-- Constraints for table `exam_bookings`
--
ALTER TABLE `exam_bookings`
  ADD CONSTRAINT `fk_exam_bookings_enroll` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_exam_bookings_session` FOREIGN KEY (`session_id`) REFERENCES `exam_sessions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exam_bookings_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_sessions`
--
ALTER TABLE `exam_sessions`
  ADD CONSTRAINT `fk_exam_sessions_type` FOREIGN KEY (`type_id`) REFERENCES `exam_types` (`id`);

--
-- Constraints for table `instructors`
--
ALTER TABLE `instructors`
  ADD CONSTRAINT `fk_instructors_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_instructors_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `instructor_availability`
--
ALTER TABLE `instructor_availability`
  ADD CONSTRAINT `fk_avail_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `fk_invoices_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_invoices_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `invoice_items`
--
ALTER TABLE `invoice_items`
  ADD CONSTRAINT `fk_items_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `fk_lessons_enrollment` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_lessons_instructor` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`id`),
  ADD CONSTRAINT `fk_lessons_vehicle` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `fk_messages_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notes`
--
ALTER TABLE `notes`
  ADD CONSTRAINT `fk_notes_author` FOREIGN KEY (`author_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reminders`
--
ALTER TABLE `reminders`
  ADD CONSTRAINT `fk_reminders_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff`
--
ALTER TABLE `staff`
  ADD CONSTRAINT `fk_staff_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_staff_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `fk_students_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_students_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_user_roles_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_roles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD CONSTRAINT `fk_vehicles_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
