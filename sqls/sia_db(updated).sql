-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 21, 2026 at 05:38 PM
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
(115, 59, 15000.00, '2026-02-21 11:53:07'),
(116, 59, 2500.00, '2026-02-21 11:53:19'),
(117, 59, 1200.00, '2026-02-21 11:56:44'),
(118, 59, 250.00, '2026-02-21 11:57:13'),
(119, 59, 1500.00, '2026-02-21 11:57:21'),
(120, 60, 15000.00, '2026-02-21 11:57:55'),
(121, 60, 2500.00, '2026-02-21 15:41:42');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `student_number` varchar(20) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `course_year` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_number`, `first_name`, `last_name`, `course_year`, `created_at`) VALUES
(1, '2025-001', 'Ricardo', 'Dalisay', 'BSCE-1', '2026-02-21 15:43:15'),
(2, '2025-002', 'Liza', 'Soberano', 'BSCE-1', '2026-02-21 15:43:15'),
(3, '2025-003', 'Enrique', 'Gil', 'BSCE-1', '2026-02-21 15:43:15'),
(4, '2025-004', 'Kathryn', 'Bernardo', 'BSCE-1', '2026-02-21 15:43:15'),
(5, '2025-005', 'Daniel', 'Padilla', 'BSCE-1', '2026-02-21 15:43:15'),
(6, '2025-006', 'Bea', 'Alonzo', 'BSCE-1', '2026-02-21 15:43:15'),
(7, '2025-007', 'John', 'Lloyd', 'BSCE-1', '2026-02-21 15:43:15'),
(8, '2025-008', 'Piolo', 'Pascual', 'BSCE-1', '2026-02-21 15:43:15'),
(9, '2025-009', 'Anne', 'Curtis', 'BSCE-1', '2026-02-21 15:43:15'),
(10, '2025-010', 'Vice', 'Ganda', 'BSCE-1', '2026-02-21 15:43:15'),
(11, '2025-011', 'Angel', 'Locsin', 'BSCPE-1', '2026-02-21 15:43:15'),
(12, '2025-012', 'Marian', 'Rivera', 'BSCPE-1', '2026-02-21 15:43:15'),
(13, '2025-013', 'Dingdong', 'Dantes', 'BSCPE-1', '2026-02-21 15:43:15'),
(14, '2025-014', 'Alden', 'Richards', 'BSCPE-1', '2026-02-21 15:43:15'),
(15, '2025-015', 'Maine', 'Mendoza', 'BSCPE-1', '2026-02-21 15:43:15'),
(16, '2025-016', 'Coco', 'Martin', 'BSCPE-1', '2026-02-21 15:43:15'),
(17, '2025-017', 'Julia', 'Barretto', 'BSCPE-1', '2026-02-21 15:43:15'),
(18, '2025-018', 'Joshua', 'Garcia', 'BSCPE-1', '2026-02-21 15:43:15'),
(19, '2025-019', 'James', 'Reid', 'BSCPE-1', '2026-02-21 15:43:15'),
(20, '2025-020', 'Nadine', 'Lustre', 'BSCPE-1', '2026-02-21 15:43:15'),
(21, '2025-021', 'Paulo', 'Avelino', 'BSCS-1', '2026-02-21 15:43:15'),
(22, '2025-022', 'Maja', 'Salvador', 'BSCS-1', '2026-02-21 15:43:15'),
(23, '2025-023', 'Gerald', 'Anderson', 'BSCS-1', '2026-02-21 15:43:15'),
(24, '2025-024', 'Kim', 'Chiu', 'BSCS-1', '2026-02-21 15:43:15'),
(25, '2025-025', 'Xian', 'Lim', 'BSCS-1', '2026-02-21 15:43:15'),
(26, '2025-026', 'Sarah', 'Geronimo', 'BSCS-1', '2026-02-21 15:43:15'),
(27, '2025-027', 'Matteo', 'Guidicelli', 'BSCS-1', '2026-02-21 15:43:15'),
(28, '2025-028', 'Janella', 'Salvador', 'BSCS-1', '2026-02-21 15:43:15'),
(29, '2025-029', 'Elmo', 'Magalona', 'BSCS-1', '2026-02-21 15:43:15'),
(30, '2025-030', 'Donny', 'Pangilinan', 'BSCS-1', '2026-02-21 15:43:15'),
(31, '2025-031', 'Belle', 'Mariano', 'BSIT-1', '2026-02-21 15:43:15'),
(32, '2025-032', 'Seth', 'Fedelin', 'BSIT-1', '2026-02-21 15:43:15'),
(33, '2025-033', 'Andrea', 'Brillantes', 'BSIT-1', '2026-02-21 15:43:15'),
(34, '2025-034', 'Kyle', 'Echarri', 'BSIT-1', '2026-02-21 15:43:15'),
(35, '2025-035', 'Francine', 'Diaz', 'BSIT-1', '2026-02-21 15:43:15'),
(36, '2025-036', 'Zaijan', 'Jaranilla', 'BSIT-1', '2026-02-21 15:43:15'),
(37, '2025-037', 'Grae', 'Fernandez', 'BSIT-1', '2026-02-21 15:43:15'),
(38, '2025-038', 'Kira', 'Balinger', 'BSIT-1', '2026-02-21 15:43:15'),
(39, '2025-039', 'Edward', 'Barber', 'BSIT-1', '2026-02-21 15:43:15'),
(40, '2025-040', 'Maymay', 'Entrata', 'BSIT-1', '2026-02-21 15:43:15'),
(41, '2025-041', 'Ivana', 'Alawi', 'BSPSY-1', '2026-02-21 15:43:15'),
(42, '2025-042', 'Zeinab', 'Harake', 'BSPSY-1', '2026-02-21 15:43:15'),
(43, '2025-043', 'Cong', 'TV', 'BSPSY-1', '2026-02-21 15:43:15'),
(44, '2025-044', 'Viy', 'Cortez', 'BSPSY-1', '2026-02-21 15:43:15'),
(45, '2025-045', 'Junnie', 'Boy', 'BSPSY-1', '2026-02-21 15:43:15'),
(46, '2025-046', 'Donnalyn', 'Bartolome', 'BSPSY-1', '2026-02-21 15:43:15'),
(47, '2025-047', 'Jelai', 'Andres', 'BSPSY-1', '2026-02-21 15:43:15'),
(48, '2025-048', 'Buboy', 'Villar', 'BSPSY-1', '2026-02-21 15:43:15'),
(49, '2025-049', 'Alex', 'Gonzaga', 'BSPSY-1', '2026-02-21 15:43:15'),
(50, '2025-050', 'Toni', 'Gonzaga', 'BSPSY-1', '2026-02-21 15:43:15'),
(59, '2025678', 'Jhastin', 'Narciso', 'BSIT', '2026-02-21 11:52:55'),
(60, '33353', 'Angel', 'Pucut', 'BSCPE', '2026-02-21 11:57:40'),
(63, '20256781', 'Lance', 'Mungcal', 'BSIT', '2026-02-21 15:46:06'),
(64, '20256782', 'Marknel', 'Gonzales', 'BSIT', '2026-02-21 15:46:28'),
(66, '20256783', 'Maxine', 'Pangilinan', 'BSIT', '2026-02-21 16:21:47');

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
(34, 59, 1, 15000.00, 15000.00, 0.00),
(35, 59, 2, 2500.00, 2500.00, 0.00),
(36, 59, 4, 1200.00, 1200.00, 0.00),
(37, 59, 5, 250.00, 250.00, 0.00),
(38, 59, 3, 1500.00, 1500.00, 0.00),
(39, 60, 1, 15000.00, 15000.00, 0.00),
(40, 60, 2, 2500.00, 2500.00, 0.00);

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
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `student_balances`
--
ALTER TABLE `student_balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

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
