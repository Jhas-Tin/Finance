-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 12, 2026 at 12:49 PM
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
-- Database: `school_finance`
--

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `student_id` varchar(20) NOT NULL,
  `class` varchar(10) NOT NULL,
  `tuition_fee` decimal(10,2) NOT NULL,
  `activities_fee` decimal(10,2) NOT NULL,
  `miscellaneous_fee` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_status` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_name`, `student_id`, `class`, `tuition_fee`, `activities_fee`, `miscellaneous_fee`, `total_amount`, `payment_status`, `created_at`) VALUES
(27, 'Maxine', '4545', 'BSIT', 50000.00, 3500.00, 2500.00, 56000.00, 'Pending', '2026-02-10 04:44:53'),
(28, 'Angel', '55454', 'BSCPE', 40000.00, 5000.00, 4000.00, 49000.00, 'Pending', '2026-02-10 04:45:30'),
(29, 'Joven', '1234342', 'BSIT', 40000.00, 3000.00, 4000.00, 47000.00, 'Overdue', '2026-02-11 04:46:37'),
(30, 'Marknel', '545489787', 'BSCE', 60000.00, 3500.00, 4000.00, 67500.00, 'Pending', '2026-02-11 04:47:12'),
(31, 'Lance', '123434298', 'BSCS', 45000.00, 3500.00, 2500.00, 51000.00, 'Paid', '2026-02-12 04:47:55'),
(32, 'Jhastin', '554546767', 'BSIT', 50000.00, 4500.00, 3500.00, 58000.00, 'Paid', '2026-02-12 04:48:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
