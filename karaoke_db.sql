-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 05, 2025 at 04:25 PM
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
  `image` varchar(255) DEFAULT NULL,
  `pricePerHour` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`packageID`, `packageName`, `description`, `image`, `pricePerHour`) VALUES
(1, 'Standard', 'Cozy room, creating an inviting retreat.\r\nUp to 4 people\r\nBasic stereo\r\n2 basic microphones', 'standard-room.png', 49.00),
(2, 'Deluxe', 'Bigger room with premium sound system.\r\nUp to 8 people\r\nEnhanced stereo\r\n4 wireless microphones\r\nPriority Booking', 'deluxe-room.png', 99.00),
(3, 'VIP', 'Spacious room with comfy seating & extra features.\r\nUp to 12 people\r\nPremium surround sound\r\n6 wireless microphones\r\nPriority Booking', 'vip-room.png', 119.00);

-- --------------------------------------------------------

--
-- Table structure for table `package_features`
--

CREATE TABLE `package_features` (
  `featureID` int(11) NOT NULL,
  `packageID` int(11) NOT NULL,
  `featureText` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `package_features`
--

INSERT INTO `package_features` (`featureID`, `packageID`, `featureText`) VALUES
(5, 1, 'Basic sound system'),
(6, 1, '2 Microphones'),
(7, 1, '40\" TV Screen'),
(8, 1, 'Perfect for 2-4 people'),
(9, 2, 'Enhanced sound system'),
(10, 2, '4 Microphones'),
(11, 2, '50\" TV Screen'),
(12, 2, 'Great for 5-8 people'),
(13, 3, 'Premium sound system'),
(14, 3, '6 Microphones'),
(15, 3, '65\" TV Screen'),
(16, 3, 'Luxury for 8-12 people');

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
(8, 10, 'Online Banking', 65.00, '2025-05-27 01:09:27', 'refunded'),
(9, 11, 'Credit Card', 65.00, '2025-05-27 07:48:37', 'refunded'),
(10, 12, 'Debit Card', 40.00, '2025-06-02 01:27:26', 'refunded'),
(11, 13, 'Online Banking', 49.00, '2025-06-02 11:35:51', 'paid'),
(12, 14, 'Credit Card', 44.10, '2025-06-03 04:09:41', 'paid'),
(13, 15, 'Debit Card', 428.40, '2025-06-03 04:17:55', 'paid'),
(14, 16, 'E-Wallet', 49.00, '2025-06-03 04:24:30', 'paid'),
(15, 17, 'Online Banking', 321.30, '2025-06-03 04:26:31', 'paid'),
(16, 18, 'Online Banking', 88.20, '2025-06-03 04:28:23', 'paid'),
(17, 19, 'Debit Card', 198.00, '2025-06-03 04:31:18', 'paid'),
(18, 20, 'Debit Card', 88.20, '2025-06-28 08:38:04', 'paid'),
(19, 21, 'Debit Card', 119.00, '2025-06-29 17:19:56', 'paid'),
(20, 22, 'Debit Card', 214.20, '2025-06-30 04:34:53', 'paid'),
(21, 23, 'Debit Card', 810.00, '2025-06-30 06:30:21', 'paid'),
(22, 24, 'E-Wallet', 238.00, '2025-07-01 16:10:46', 'paid'),
(23, 25, 'Online Banking', 356.40, '2025-07-01 16:13:48', 'refunded'),
(24, 26, 'Debit Card', 178.20, '2025-07-01 17:04:42', 'refunded'),
(25, 27, 'Debit Card', 98.00, '2025-07-03 02:40:15', 'refunded'),
(26, 28, 'Debit Card', 98.00, '2025-07-03 02:42:38', 'refunded'),
(27, 29, 'Debit Card', 198.00, '2025-07-03 02:50:24', 'refunded'),
(28, 30, 'E-Wallet', 357.00, '2025-07-03 04:04:41', 'paid');

-- --------------------------------------------------------

--
-- Table structure for table `requestchanges`
--

CREATE TABLE `requestchanges` (
  `reqID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `emailChange` enum('yes','no') NOT NULL,
  `phnoChange` enum('yes','no') NOT NULL,
  `newEmail` varchar(255) DEFAULT NULL,
  `newPNo` varchar(20) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(19, 2, 5, '2025-06-05', '18:00:00', '20:00:00', 198.00, 'confirmed', '', '2025-06-03 04:31:18'),
(20, 1, 1, '2025-07-10', '18:00:00', '20:00:00', 88.20, 'confirmed', '', '2025-06-28 08:38:04'),
(21, 345678, 8, '2025-07-02', '10:00:00', '11:00:00', 119.00, 'confirmed', '', '2025-06-29 17:19:56'),
(22, 345678, 8, '2025-07-05', '11:00:00', '13:00:00', 214.20, 'confirmed', '', '2025-06-30 04:34:53'),
(23, 345678, 20, '2025-07-03', '20:00:00', '22:00:00', 810.00, 'confirmed', '', '2025-06-30 06:30:21'),
(24, 345681, 8, '2025-07-02', '21:00:00', '23:00:00', 238.00, 'confirmed', 'nak air milo', '2025-07-01 16:10:46'),
(25, 345681, 5, '2025-08-01', '13:00:00', '17:00:00', 356.40, 'cancelled', '', '2025-07-01 16:13:48'),
(26, 345681, 5, '2025-07-10', '14:00:00', '16:00:00', 178.20, 'cancelled', 'Cancellation Reason: Change of plans', '2025-07-01 17:04:42'),
(27, 345683, 1, '2025-07-05', '11:00:00', '13:00:00', 98.00, 'cancelled', '', '2025-07-03 02:40:15'),
(28, 345683, 1, '2025-07-05', '11:00:00', '13:00:00', 98.00, 'cancelled', '', '2025-07-03 02:42:38'),
(29, 345684, 5, '2025-07-05', '11:00:00', '13:00:00', 198.00, 'cancelled', '', '2025-07-03 02:50:24'),
(30, 345685, 8, '2025-07-05', '15:00:00', '18:00:00', 357.00, 'confirmed', '', '2025-07-03 04:04:41');

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
(4, 'Miss Sarah', 'sarah@gmail.com', '0123346789', '$2y$10$39tT3WxZVCcxKAird8b/TOc0uu66QPrkpkoAL/8T4J/Bx1X1mpoES', 'user', '2025-05-27 07:45:33'),
(345678, 'umar zikri', 'umarzikri00@gmail.com', '012-3456789', '$2y$10$l//XKsmWspsXWX4PbuXBd.BXcvwFQ9WbPuuHfp6FevGkwmNxawOUu', 'admin', '2025-05-27 06:59:03'),
(345681, 'John Doe', 'john123@gmail.com', '0123456789', '$2y$10$ui3RGiQKD5toRcDSiSYns.vCDcBoQ79jpqQ93dtaGJsA0xcJnuFga', 'user', '2025-07-01 16:09:32'),
(345683, 'oyennbro', 'oyen00@gmail.com', '0123456789', '$2y$10$D7RiyPx5/JhLH6uP5bcgMO/WTZcpApTIFOt/bk.BjwY.5Yr1UCQzi', 'user', '2025-07-03 02:38:31'),
(345684, 'oyenboyy', 'oyen0000@gmail.com', '0123456789', '$2y$10$oFki9kdd2UqmoB7gQCOpaOSsK10inhk460AzvL0ddfa8WCy.tcq/q', 'user', '2025-07-03 02:49:04'),
(345685, 'Maisarah binti Abdul', 'maisarah_11@yahoo.com', '0123456789', '$2y$10$5FUsdSJdYy5Wf5qEYyE0QOZDvTsyLW0F7U7Bn9ocDlJSELdAAErwy', 'user', '2025-07-03 04:02:49'),
(345686, 'Sir Admin', 'admin00@gmail.com', '0198765432', '$2y$10$.zeP/yOt5OE72mYWFWs8fOVwDnjyxoLVbUNarJ2aGdK0vXw40dtMK', 'admin', '2025-07-05 13:56:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`packageID`);

--
-- Indexes for table `package_features`
--
ALTER TABLE `package_features`
  ADD PRIMARY KEY (`featureID`),
  ADD KEY `packageID` (`packageID`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`paymentID`),
  ADD KEY `reservationID` (`reservationID`);

--
-- Indexes for table `requestchanges`
--
ALTER TABLE `requestchanges`
  ADD PRIMARY KEY (`reqID`),
  ADD KEY `userID` (`userID`);

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
  MODIFY `packageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `package_features`
--
ALTER TABLE `package_features`
  MODIFY `featureID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `paymentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `requestchanges`
--
ALTER TABLE `requestchanges`
  MODIFY `reqID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `reservationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `roomID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=345687;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `package_features`
--
ALTER TABLE `package_features`
  ADD CONSTRAINT `package_features_ibfk_1` FOREIGN KEY (`packageID`) REFERENCES `packages` (`packageID`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`reservationID`) REFERENCES `reservations` (`reservationID`);

--
-- Constraints for table `requestchanges`
--
ALTER TABLE `requestchanges`
  ADD CONSTRAINT `requestchanges_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`);

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
