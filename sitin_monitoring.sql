-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2025 at 10:17 AM
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
-- Database: `sitin_monitoring`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student') NOT NULL DEFAULT 'admin',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `role`, `status`) VALUES
(2, 'admin', 'admin@gmail.com', '$2y$10$2n1BenxedIaKMRQl4jnbfuGBY5wENFCfrLme0JvaE9yHwlg6R6kIC', 'admin', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `created_at`) VALUES
(5, 'University of Cebu', 'UC has it again', '2025-05-16 07:43:05'),
(6, 'UC', 'UC sit in students', '2025-05-16 07:45:58');

-- --------------------------------------------------------

--
-- Table structure for table `booking_requests`
--

CREATE TABLE `booking_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `pc_number` varchar(10) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `status` varchar(255) DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('lab','service','other') NOT NULL DEFAULT 'other',
  `room_number` varchar(10) NOT NULL,
  `feedback_text` text NOT NULL,
  `status` enum('pending','responded','resolved') NOT NULL DEFAULT 'pending',
  `admin_response` text DEFAULT NULL,
  `submitted_at` datetime NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `session_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `labs`
--

CREATE TABLE `labs` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `location` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `status` enum('available','maintenance','unavailable') NOT NULL DEFAULT 'available',
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `labs`
--

INSERT INTO `labs` (`id`, `name`, `location`, `capacity`, `status`, `last_updated`) VALUES
(1, 'Lab 1', 'First Floor', 30, 'available', '2025-05-17 05:35:11'),
(2, 'Lab 2', 'Second Floor', 25, 'available', '2025-05-17 05:35:11'),
(3, 'Lab 3', 'Third Floor', 20, 'available', '2025-05-17 05:35:11');

-- --------------------------------------------------------

--
-- Table structure for table `lab_pcs`
--

CREATE TABLE `lab_pcs` (
  `id` int(11) NOT NULL,
  `lab` varchar(10) NOT NULL,
  `pc_number` int(11) NOT NULL,
  `status` enum('available','used') NOT NULL DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_pcs`
--

INSERT INTO `lab_pcs` (`id`, `lab`, `pc_number`, `status`) VALUES
(1, '524', 1, 'available'),
(2, '526', 1, 'available'),
(3, '530', 1, 'available'),
(4, '524', 2, 'used'),
(5, '526', 2, 'available'),
(6, '530', 2, 'available'),
(7, '524', 3, 'used'),
(8, '526', 3, 'available'),
(9, '530', 3, 'available'),
(10, '524', 4, 'used'),
(11, '526', 4, 'available'),
(12, '530', 4, 'available'),
(13, '524', 5, 'available'),
(14, '526', 5, 'available'),
(15, '530', 5, 'available'),
(16, '524', 6, 'available'),
(17, '526', 6, 'available'),
(18, '530', 6, 'available'),
(19, '524', 7, 'available'),
(20, '526', 7, 'available'),
(21, '530', 7, 'available'),
(22, '524', 8, 'available'),
(23, '526', 8, 'available'),
(24, '530', 8, 'available'),
(25, '524', 9, 'available'),
(26, '526', 9, 'available'),
(27, '530', 9, 'available'),
(28, '524', 10, 'available'),
(29, '526', 10, 'available'),
(30, '530', 10, 'available'),
(31, '524', 11, 'available'),
(32, '526', 11, 'available'),
(33, '530', 11, 'available'),
(34, '524', 12, 'available'),
(35, '526', 12, 'available'),
(36, '530', 12, 'available'),
(37, '524', 13, 'available'),
(38, '526', 13, 'available'),
(39, '530', 13, 'available'),
(40, '524', 14, 'available'),
(41, '526', 14, 'available'),
(42, '530', 14, 'available'),
(43, '524', 15, 'available'),
(44, '526', 15, 'available'),
(45, '530', 15, 'available'),
(46, '524', 16, 'available'),
(47, '526', 16, 'available'),
(48, '530', 16, 'available'),
(49, '524', 17, 'available'),
(50, '526', 17, 'available'),
(51, '530', 17, 'available'),
(52, '524', 18, 'available'),
(53, '526', 18, 'available'),
(54, '530', 18, 'available'),
(55, '524', 19, 'available'),
(56, '526', 19, 'available'),
(57, '530', 19, 'available'),
(58, '524', 20, 'available'),
(59, '526', 20, 'available'),
(60, '530', 20, 'available'),
(61, '524', 21, 'available'),
(62, '526', 21, 'available'),
(63, '530', 21, 'available'),
(64, '524', 22, 'available'),
(65, '526', 22, 'available'),
(66, '530', 22, 'available'),
(67, '524', 23, 'available'),
(68, '526', 23, 'available'),
(69, '530', 23, 'available'),
(70, '524', 24, 'available'),
(71, '526', 24, 'available'),
(72, '530', 24, 'available'),
(73, '524', 25, 'available'),
(74, '526', 25, 'available'),
(75, '530', 25, 'available'),
(76, '524', 26, 'available'),
(77, '526', 26, 'available'),
(78, '530', 26, 'available'),
(79, '524', 27, 'available'),
(80, '526', 27, 'available'),
(81, '530', 27, 'available'),
(82, '524', 28, 'available'),
(83, '526', 28, 'available'),
(84, '530', 28, 'available'),
(85, '524', 29, 'available'),
(86, '526', 29, 'available'),
(87, '530', 29, 'available'),
(88, '524', 30, 'available'),
(89, '526', 30, 'available'),
(90, '530', 30, 'available'),
(91, '524', 31, 'available'),
(92, '526', 31, 'available'),
(93, '530', 31, 'available'),
(94, '524', 32, 'available'),
(95, '526', 32, 'available'),
(96, '530', 32, 'available'),
(97, '524', 33, 'available'),
(98, '526', 33, 'available'),
(99, '530', 33, 'available'),
(100, '524', 34, 'available'),
(101, '526', 34, 'available'),
(102, '530', 34, 'available'),
(103, '524', 35, 'available'),
(104, '526', 35, 'available'),
(105, '530', 35, 'available'),
(106, '524', 36, 'available'),
(107, '526', 36, 'available'),
(108, '530', 36, 'available'),
(109, '524', 37, 'available'),
(110, '526', 37, 'available'),
(111, '530', 37, 'available'),
(112, '524', 38, 'available'),
(113, '526', 38, 'available'),
(114, '530', 38, 'available'),
(115, '524', 39, 'available'),
(116, '526', 39, 'available'),
(117, '530', 39, 'available'),
(118, '524', 40, 'available'),
(119, '526', 40, 'available'),
(120, '530', 40, 'available'),
(121, '524', 41, 'available'),
(122, '526', 41, 'available'),
(123, '530', 41, 'available'),
(124, '524', 42, 'available'),
(125, '526', 42, 'available'),
(126, '530', 42, 'available'),
(127, '524', 43, 'available'),
(128, '526', 43, 'available'),
(129, '530', 43, 'available'),
(130, '524', 44, 'available'),
(131, '526', 44, 'available'),
(132, '530', 44, 'available'),
(133, '524', 45, 'available'),
(134, '526', 45, 'available'),
(135, '530', 45, 'available'),
(136, '524', 46, 'available'),
(137, '526', 46, 'available'),
(138, '530', 46, 'available'),
(139, '524', 47, 'available'),
(140, '526', 47, 'available'),
(141, '530', 47, 'available'),
(142, '524', 48, 'available'),
(143, '526', 48, 'available'),
(144, '530', 48, 'available'),
(145, '524', 49, 'available'),
(146, '526', 49, 'available'),
(147, '530', 49, 'available'),
(148, '524', 50, 'available'),
(149, '526', 50, 'available'),
(150, '530', 50, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `received_announcements`
--

CREATE TABLE `received_announcements` (
  `id` int(11) NOT NULL,
  `announcement_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `received_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `report_text` text NOT NULL,
  `submitted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time_slot` varchar(20) NOT NULL,
  `lab` varchar(50) NOT NULL,
  `purpose` varchar(100) NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `date`, `time_slot`, `lab`, `purpose`, `status`, `admin_notes`, `created_at`) VALUES
(1, 3, '2025-05-17', '12:08', '524', 'C Programming', 'rejected', 'PC: PC1', '2025-05-17 06:09:05'),
(2, 3, '2025-05-17', '16:11', '524', 'C Programming', 'approved', 'PC: PC1', '2025-05-17 06:11:39'),
(3, 6, '2025-05-17', '18:00', '530', 'C Programming', 'pending', 'PC: PC10', '2025-05-17 07:29:38'),
(4, 2, '2025-05-17', '16:34', '530', 'Python Programming', 'approved', 'PC: PC9', '2025-05-17 07:34:24');

-- --------------------------------------------------------

--
-- Table structure for table `setin_records`
--

CREATE TABLE `setin_records` (
  `record_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) NOT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `lab` varchar(50) DEFAULT NULL,
  `time_started` datetime DEFAULT NULL,
  `time_stopped` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sit_in_records`
--

CREATE TABLE `sit_in_records` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `purpose` text NOT NULL,
  `lab` varchar(50) NOT NULL,
  `pc` int(11) DEFAULT NULL,
  `time_in` time NOT NULL,
  `time_out` time DEFAULT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sit_in_records`
--

INSERT INTO `sit_in_records` (`id`, `user_id`, `purpose`, `lab`, `pc`, `time_in`, `time_out`, `date`) VALUES
(1, 6, 'C Programming', '526', 1, '16:14:21', NULL, '2025-05-17'),
(2, 3, 'C Programming', '530', 6, '16:14:41', '16:15:30', '2025-05-17');

-- --------------------------------------------------------

--
-- Table structure for table `student_sessions`
--

CREATE TABLE `student_sessions` (
  `session_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `lastname` varchar(50) NOT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `lab` varchar(50) DEFAULT NULL,
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  `pc_number` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `idno` varchar(20) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `course` varchar(10) NOT NULL,
  `yearlevel` varchar(10) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `profile_pic` varchar(255) DEFAULT 'default.png',
  `session` int(11) DEFAULT 30,
  `remaining_sessions` int(11) DEFAULT 30,
  `role` enum('admin','student') NOT NULL DEFAULT 'student',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `idno`, `lastname`, `firstname`, `middlename`, `course`, `yearlevel`, `username`, `password`, `email`, `profile_pic`, `session`, `remaining_sessions`, `role`, `status`, `address`) VALUES
(2, '6000', 'Oyao', 'Erick', 'Caparida', 'BSIT', '1st Year', 'ej', '$2y$10$G1B0frKFY/bBtfO.Qv4DoesJmZPzBHfA3A2VWeYytYwsMmyMDpfw6', 'ej@gmail.com', '1743021078_1740629493_user1.png', 30, 18, 'student', 'active', 'Lipata'),
(3, '7000', 'Alicaya', 'kyle', '', 'BSIT', '4th Year', 'kyle', '$2y$10$Zt.YIGTQ96MkYR/bHbk62e75zesUMcTqcXqfCmv0wLYtz77Z5k4t6', 'kyle@gmail.com', 'default.png', 30, 18, 'student', 'active', 'Linao Minglanilla'),
(4, 'TEST123', 'Test', 'User', 'Middle', 'BSIT', '1st Year', 'testuser', '$2y$10$IMBKEuK6ddovsneNNvwoFOsJsiymfw6vxvt0usJHSG50uKWviONia', 'test@example.com', 'default.png', 30, 30, 'student', 'active', 'Test Address'),
(5, 'TEST456', 'Test', 'User2', 'Middle', 'BSIT', '1st Year', 'testuser2', '$2y$10$G0XgSma.wliPOZZEn5vjhez.O.gTtBGueybhzpPg8zT/jH3wLtMaK', 'test2@example.com', 'default.png', 30, 30, 'student', 'active', 'Test Address'),
(6, '8000', 'medina', 'metch', 'C', 'BSIT', '4th Year', 'Metch', '$2y$10$AItiBvvv7AigQ..16vLBQ.AOj8HNFGiRRHMydHyTQTFHsSzB3u7py', 'Metch@gmail.com', 'default.png', 30, 30, 'student', 'active', 'Mambaling Cebu City');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_requests`
--
ALTER TABLE `booking_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `labs`
--
ALTER TABLE `labs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `lab_pcs`
--
ALTER TABLE `lab_pcs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lab` (`lab`,`pc_number`);

--
-- Indexes for table `received_announcements`
--
ALTER TABLE `received_announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcement_id` (`announcement_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `setin_records`
--
ALTER TABLE `setin_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `sit_in_records`
--
ALTER TABLE `sit_in_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sit_in_records_user_fk` (`user_id`),
  ADD KEY `idx_date` (`date`),
  ADD KEY `idx_time_in` (`time_in`),
  ADD KEY `idx_time_out` (`time_out`);

--
-- Indexes for table `student_sessions`
--
ALTER TABLE `student_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idno` (`idno`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lab_pcs`
--
ALTER TABLE `lab_pcs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5521;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sit_in_records`
--
ALTER TABLE `sit_in_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_sessions`
--
ALTER TABLE `student_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `student_sessions`
--
ALTER TABLE `student_sessions`
  ADD CONSTRAINT `student_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
