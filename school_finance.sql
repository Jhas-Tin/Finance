-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2026 at 05:13 PM
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
(4, 'Jhastin', '1234342', '6A', 50000.00, 3000.00, 1500.00, 54500.00, 'Pending', '2026-02-04 15:39:20'),
(5, 'Lance', '56434', '5C', 40000.00, 3000.00, 2500.00, 45500.00, 'Pending', '2026-02-04 15:39:55'),
(6, 'Maxine', '6886', '6B', 4500.00, 7000.00, 1000.00, 12500.00, 'Overdue', '2026-02-04 15:40:28'),
(7, 'Angel', '63567', '4B', 45000.00, 6000.00, 1900.00, 52900.00, 'Pending', '2026-02-04 15:41:09'),
(8, 'Marknel', '33241`55', '7B', 50000.00, 7000.00, 1000.00, 58000.00, 'Pending', '2026-02-04 15:43:52');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
