-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 19, 2026 at 03:38 PM
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
-- Database: `sia_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `assessment_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `fee_id` int(11) DEFAULT NULL,
  `amount_charged` decimal(10,2) DEFAULT NULL,
  `assessment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fee_categories`
--

CREATE TABLE `fee_categories` (
  `fee_id` int(11) NOT NULL,
  `fee_name` varchar(50) NOT NULL,
  `default_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fee_categories`
--

INSERT INTO `fee_categories` (`fee_id`, `fee_name`, `default_amount`) VALUES
(1, 'Tuition Fee', 15000.00),
(2, 'Misc Fee', 2500.00),
(3, 'Lab Fee', 1500.00),
(4, 'Uniform', 1200.00),
(5, 'ID Request', 250.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `student_id`, `amount_paid`, `payment_date`) VALUES
(1, 2, 2000.00, '2026-02-12 03:47:09'),
(2, 8, 1200.00, '2026-02-12 03:47:34'),
(3, 12, 5000.00, '2026-02-12 03:47:53'),
(4, 13, 7000.00, '2026-02-12 03:48:15'),
(5, 14, 2000.00, '2026-02-12 03:48:43'),
(6, 9, 5000.00, '2026-02-12 08:02:35'),
(7, 4, 6000.00, '2026-02-12 08:11:26'),
(8, 16, 700.00, '2026-02-12 08:11:55'),
(9, 11, 15000.00, '2026-02-12 08:34:43'),
(10, 12, 2500.00, '2026-02-12 08:36:10'),
(11, 1, 2.00, '2026-02-18 07:31:18'),
(56, 12, 1000.00, '2026-02-18 08:04:04'),
(97, 6, 10000.00, '2026-02-18 10:36:04'),
(98, 10, 10000.00, '2026-02-19 13:46:43'),
(99, 7, 10000.00, '2026-02-19 13:48:19'),
(100, 2, 1200.00, '2026-02-19 13:50:08'),
(101, 6, 2500.00, '2026-02-19 14:35:38'),
(102, 6, 2500.00, '2026-02-19 14:36:15');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `course_year` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_number`, `first_name`, `last_name`, `course_year`) VALUES
(1, '2025-001', 'Ricardo', 'Dalisay', 'BSCE-1'),
(2, '2025-002', 'Liza', 'Soberano', 'BSCE-1'),
(3, '2025-003', 'Enrique', 'Gil', 'BSCE-1'),
(4, '2025-004', 'Kathryn', 'Bernardo', 'BSCE-1'),
(5, '2025-005', 'Daniel', 'Padilla', 'BSCE-1'),
(6, '2025-006', 'Bea', 'Alonzo', 'BSCE-1'),
(7, '2025-007', 'John', 'Lloyd', 'BSCE-1'),
(8, '2025-008', 'Piolo', 'Pascual', 'BSCE-1'),
(9, '2025-009', 'Anne', 'Curtis', 'BSCE-1'),
(10, '2025-010', 'Vice', 'Ganda', 'BSCE-1'),
(11, '2025-011', 'Angel', 'Locsin', 'BSCPE-1'),
(12, '2025-012', 'Marian', 'Rivera', 'BSCPE-1'),
(13, '2025-013', 'Dingdong', 'Dantes', 'BSCPE-1'),
(14, '2025-014', 'Alden', 'Richards', 'BSCPE-1'),
(15, '2025-015', 'Maine', 'Mendoza', 'BSCPE-1'),
(16, '2025-016', 'Coco', 'Martin', 'BSCPE-1'),
(17, '2025-017', 'Julia', 'Barretto', 'BSCPE-1'),
(18, '2025-018', 'Joshua', 'Garcia', 'BSCPE-1'),
(19, '2025-019', 'James', 'Reid', 'BSCPE-1'),
(20, '2025-020', 'Nadine', 'Lustre', 'BSCPE-1'),
(21, '2025-021', 'Paulo', 'Avelino', 'BSCS-1'),
(22, '2025-022', 'Maja', 'Salvador', 'BSCS-1'),
(23, '2025-023', 'Gerald', 'Anderson', 'BSCS-1'),
(24, '2025-024', 'Kim', 'Chiu', 'BSCS-1'),
(25, '2025-025', 'Xian', 'Lim', 'BSCS-1'),
(26, '2025-026', 'Sarah', 'Geronimo', 'BSCS-1'),
(27, '2025-027', 'Matteo', 'Guidicelli', 'BSCS-1'),
(28, '2025-028', 'Janella', 'Salvador', 'BSCS-1'),
(29, '2025-029', 'Elmo', 'Magalona', 'BSCS-1'),
(30, '2025-030', 'Donny', 'Pangilinan', 'BSCS-1'),
(31, '2025-031', 'Belle', 'Mariano', 'BSIT-1'),
(32, '2025-032', 'Seth', 'Fedelin', 'BSIT-1'),
(33, '2025-033', 'Andrea', 'Brillantes', 'BSIT-1'),
(34, '2025-034', 'Kyle', 'Echarri', 'BSIT-1'),
(35, '2025-035', 'Francine', 'Diaz', 'BSIT-1'),
(36, '2025-036', 'Zaijan', 'Jaranilla', 'BSIT-1'),
(37, '2025-037', 'Grae', 'Fernandez', 'BSIT-1'),
(38, '2025-038', 'Kira', 'Balinger', 'BSIT-1'),
(39, '2025-039', 'Edward', 'Barber', 'BSIT-1'),
(40, '2025-040', 'Maymay', 'Entrata', 'BSIT-1'),
(41, '2025-041', 'Ivana', 'Alawi', 'BSPSY-1'),
(42, '2025-042', 'Zeinab', 'Harake', 'BSPSY-1'),
(43, '2025-043', 'Cong', 'TV', 'BSPSY-1'),
(44, '2025-044', 'Viy', 'Cortez', 'BSPSY-1'),
(45, '2025-045', 'Junnie', 'Boy', 'BSPSY-1'),
(46, '2025-046', 'Donnalyn', 'Bartolome', 'BSPSY-1'),
(47, '2025-047', 'Jelai', 'Andres', 'BSPSY-1'),
(48, '2025-048', 'Buboy', 'Villar', 'BSPSY-1'),
(49, '2025-049', 'Alex', 'Gonzaga', 'BSPSY-1'),
(50, '2025-050', 'Toni', 'Gonzaga', 'BSPSY-1');

-- --------------------------------------------------------

--
-- Table structure for table `student_balances`
--

CREATE TABLE `student_balances` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `fee_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `remaining_balance` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_balances`
--

INSERT INTO `student_balances` (`id`, `student_id`, `fee_id`, `total_amount`, `paid_amount`, `remaining_balance`) VALUES
(1, 2, 1, 15000.00, 2000.00, 13000.00),
(2, 8, 4, 1200.00, 1200.00, 0.00),
(3, 12, 1, 15000.00, 6000.00, 9000.00),
(4, 13, 1, 15000.00, 7000.00, 8000.00),
(5, 14, 5, 250.00, 250.00, 0.00),
(6, 14, 2, 2500.00, 1750.00, 750.00),
(7, 9, 1, 15000.00, 5000.00, 10000.00),
(8, 4, 1, 15000.00, 6000.00, 9000.00),
(9, 16, 4, 1200.00, 700.00, 500.00),
(10, 11, 1, 15000.00, 15000.00, 0.00),
(11, 12, 2, 2500.00, 2500.00, 0.00),
(12, 1, 5, 250.00, 2.00, 248.00),
(13, 6, 1, 15000.00, 15000.00, 0.00),
(14, 10, 1, 15000.00, 10000.00, 5000.00),
(15, 7, 1, 15000.00, 10000.00, 5000.00),
(16, 2, 4, 1200.00, 1200.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`) VALUES
(1, 'user', 'user', 'user'),
(2, 'admin', 'admin', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`assessment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fee_id` (`fee_id`);

--
-- Indexes for table `fee_categories`
--
ALTER TABLE `fee_categories`
  ADD PRIMARY KEY (`fee_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `student_number` (`student_number`);

--
-- Indexes for table `student_balances`
--
ALTER TABLE `student_balances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fee_id` (`fee_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `assessment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fee_categories`
--
ALTER TABLE `fee_categories`
  MODIFY `fee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `student_balances`
--
ALTER TABLE `student_balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assessments`
--
ALTER TABLE `assessments`
  ADD CONSTRAINT `assessments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `assessments_ibfk_2` FOREIGN KEY (`fee_id`) REFERENCES `fee_categories` (`fee_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `student_balances`
--
ALTER TABLE `student_balances`
  ADD CONSTRAINT `student_balances_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `student_balances_ibfk_2` FOREIGN KEY (`fee_id`) REFERENCES `fee_categories` (`fee_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
