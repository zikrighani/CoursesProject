-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 03, 2025 at 09:54 AM
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
-- Database: `karaoke_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `packageID` int(11) NOT NULL,
  `packageName` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `pricePerHour` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`packageID`, `packageName`, `description`, `pricePerHour`) VALUES
(1, 'Standard', 'Cozy room, creating an inviting retreat.\nUp to 4 people\nBasic stereo\n2 basic microphones', 49.00),
(2, 'Deluxe', 'Bigger room with premium sound system.\nUp to 8 people\nEnhanced stereo\n4 wireless microphones\nPriority Booking', 99.00),
(3, 'VIP', 'Spacious room with comfy seating & extra features.\nUp to 12 people\nPremium surround sound\n6 wireless microphones\nPriority Booking', 119.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `paymentID` int(11) NOT NULL,
  `reservationID` int(11) DEFAULT NULL,
  `paymentMethod` varchar(50) DEFAULT NULL,
  `amountPaid` decimal(10,2) DEFAULT NULL,
  `paymentDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `paymentStatus` enum('pending','paid','refunded') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`paymentID`, `reservationID`, `paymentMethod`, `amountPaid`, `paymentDate`, `paymentStatus`) VALUES
(1, 3, 'E-Wallet', 160.00, '2025-05-25 09:20:08', 'refunded'),
(2, 4, 'Debit Card', 80.00, '2025-05-25 09:22:04', 'paid'),
(3, 5, 'Debit Card', 40.00, '2025-05-25 09:30:48', 'refunded'),
(4, 6, 'Debit Card', 40.00, '2025-05-25 11:25:55', 'paid'),
(5, 7, 'Debit Card', 40.00, '2025-05-25 11:41:18', 'paid'),
(6, 8, 'Online Banking', 297.00, '2025-05-27 00:28:26', 'paid'),
(7, 9, 'Credit Card', 65.00, '2025-05-27 01:00:21', 'paid'),
(8, 10, 'Online Banking', 65.00, '2025-05-27 01:09:27', 'pending'),
(9, 11, 'Credit Card', 65.00, '2025-05-27 07:48:37', 'refunded'),
(10, 12, 'Debit Card', 40.00, '2025-06-02 01:27:26', 'refunded'),
(11, 13, 'Online Banking', 49.00, '2025-06-02 11:35:51', 'paid'),
(12, 14, 'Credit Card', 44.10, '2025-06-03 04:09:41', 'paid'),
(13, 15, 'Debit Card', 428.40, '2025-06-03 04:17:55', 'paid'),
(14, 16, 'E-Wallet', 49.00, '2025-06-03 04:24:30', 'paid'),
(15, 17, 'Online Banking', 321.30, '2025-06-03 04:26:31', 'paid'),
(16, 18, 'Online Banking', 88.20, '2025-06-03 04:28:23', 'paid'),
(17, 19, 'Debit Card', 198.00, '2025-06-03 04:31:18', 'paid');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `reservationID` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `roomID` int(11) DEFAULT NULL,
  `reservationDate` date DEFAULT NULL,
  `startTime` time DEFAULT NULL,
  `endTime` time DEFAULT NULL,
  `totalPrice` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `addInfo` text NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`reservationID`, `userID`, `roomID`, `reservationDate`, `startTime`, `endTime`, `totalPrice`, `status`, `addInfo`, `createdAt`) VALUES
(2, 2, 16, '2025-05-20', '15:00:00', '16:00:00', 99.00, 'confirmed', '', '2025-05-20 06:51:17'),
(3, 1, 1, '2025-05-28', '15:00:00', '19:00:00', 160.00, 'cancelled', 'Cancellation Reason: Change of plans\nAdditional Comments: tukar bilik', '2025-05-25 09:20:08'),
(4, 1, 1, '2025-05-30', '15:00:00', '17:00:00', 80.00, 'confirmed', '', '2025-05-25 09:22:04'),
(5, 1, 1, '2025-07-22', '19:00:00', '20:00:00', 40.00, 'cancelled', '', '2025-05-25 09:30:48'),
(6, 1, 1, '2025-05-26', '13:00:00', '14:00:00', 40.00, 'confirmed', 'Make a birthday party setup', '2025-05-25 11:25:55'),
(7, 1, 2, '2025-05-26', '13:00:00', '14:00:00', 40.00, 'confirmed', '', '2025-05-25 11:41:18'),
(8, 1, 8, '2025-05-28', '18:00:00', '21:00:00', 297.00, 'confirmed', '', '2025-05-27 00:28:26'),
(9, 1, 5, '2025-05-30', '18:00:00', '19:00:00', 65.00, 'confirmed', '', '2025-05-27 01:00:21'),
(10, 1, 5, '2025-06-04', '10:00:00', '11:00:00', 65.00, 'cancelled', '', '2025-05-27 01:09:26'),
(11, 4, 5, '2025-05-28', '10:00:00', '11:00:00', 65.00, 'cancelled', 'Cancellation Reason: Health issues', '2025-05-27 07:48:37'),
(12, 1, 1, '2025-06-03', '19:00:00', '20:00:00', 40.00, 'cancelled', 'Cancellation Reason: Change of plans', '2025-06-02 01:27:26'),
(13, 1, 1, '2025-06-03', '18:00:00', '19:00:00', 49.00, 'confirmed', '', '2025-06-02 11:35:51'),
(14, 1, 1, '2025-06-24', '12:00:00', '13:00:00', 44.10, 'confirmed', 'no', '2025-06-03 04:09:41'),
(15, 1, 8, '2025-06-27', '17:00:00', '21:00:00', 428.40, 'confirmed', 'Want a good sound', '2025-06-03 04:17:55'),
(16, 2, 2, '2025-06-03', '18:00:00', '19:00:00', 49.00, 'confirmed', '', '2025-06-03 04:24:30'),
(17, 2, 9, '2025-06-27', '17:00:00', '20:00:00', 321.30, 'confirmed', '', '2025-06-03 04:26:31'),
(18, 2, 2, '2025-06-24', '12:00:00', '14:00:00', 88.20, 'confirmed', '', '2025-06-03 04:28:23'),
(19, 2, 5, '2025-06-05', '18:00:00', '20:00:00', 198.00, 'confirmed', '', '2025-06-03 04:31:18');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `roomID` int(11) NOT NULL,
  `roomName` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `status` enum('available','unavailable') DEFAULT 'available',
  `packageID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`roomID`, `roomName`, `capacity`, `status`, `packageID`) VALUES
(1, 'Room A', 4, 'available', 1),
(2, 'Room B', 4, 'available', 1),
(3, 'Room C', 4, 'available', 1),
(4, 'Room D', 4, 'available', 1),
(5, 'Room E', 8, 'available', 2),
(6, 'Room F', 8, 'available', 2),
(7, 'Room G', 8, 'available', 2),
(8, 'Room H', 12, 'available', 3),
(9, 'Room I', 12, 'available', 3),
(10, 'Room J', 12, 'available', 3),
(11, 'Room K', 4, 'available', 1),
(12, 'Room L', 4, 'available', 1),
(13, 'Room M', 4, 'available', 1),
(14, 'Room N', 4, 'available', 1),
(15, 'Room O', 4, 'available', 1),
(16, 'Room P', 4, 'available', 1),
(17, 'Room Q', 4, 'available', 1),
(18, 'Room R', 8, 'available', 2),
(19, 'Room S', 8, 'available', 2),
(20, 'Room T', 8, 'available', 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
  `fullName` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `fullName`, `email`, `phone`, `password`, `role`, `createdAt`) VALUES
(1, 'Arif Roshaimizam', 'r_mizam@yahoo.com', '60196822748', '$2y$10$kuniEfqumOaCw6mYQeuaYObR28GdRGJYdMfbq0H6Qxud9MQMY1Eky', 'user', '2025-05-19 07:58:27'),
(2, 'Abdul Hakim', 'hakim123@gmail.com', '60147483647', '$2y$10$jT9aghpQr5PmxHiXrywI9eOTy5IA0fDuZQDzj6.Nsw6wvr6zsvpja', 'user', '2025-05-20 06:45:14'),
(3, 'Umar Zikri', 'umarzikri00@gmail.com', '012-3456789', '$2y$10$l//XKsmWspsXWX4PbuXBd.BXcvwFQ9WbPuuHfp6FevGkwmNxawOUu', 'admin', '2025-05-27 06:59:03'),
(4, 'Miss Sarah', 'sarah@gmail.com', '0123346789', '$2y$10$39tT3WxZVCcxKAird8b/TOc0uu66QPrkpkoAL/8T4J/Bx1X1mpoES', 'user', '2025-05-27 07:45:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`packageID`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`paymentID`),
  ADD KEY `reservationID` (`reservationID`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`reservationID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `roomID` (`roomID`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`roomID`),
  ADD KEY `packageID` (`packageID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `packageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `paymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `roomID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`reservationID`) REFERENCES `reservations` (`reservationID`);

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`),
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`roomID`) REFERENCES `rooms` (`roomID`);

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`packageID`) REFERENCES `packages` (`packageID`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
