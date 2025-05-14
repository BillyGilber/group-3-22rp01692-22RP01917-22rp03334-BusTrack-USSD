-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 14, 2025 at 03:54 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ussd_bus_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `bus_id` varchar(10) NOT NULL,
  `passenger_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `phone_number`, `bus_id`, `passenger_name`, `created_at`) VALUES
(2, '+250785071037', 'RT40', 'PHOEBE', '2025-05-12 16:38:06'),
(3, '+250785071037', 'RT40', 'EZEBIE', '2025-05-12 16:50:35'),
(4, '+250785071037', 'RT40', 'FERDINA', '2025-05-12 17:04:51'),
(6, '+250785275072', 'RT25', 'olivier', '2025-05-12 18:21:26'),
(7, '+254790029270', 'RT40', 'gervais', '2025-05-13 20:29:27'),
(8, '', 'RT30', 'professor', '2025-05-13 22:27:24'),
(9, '', 'RT25', 'professor', '2025-05-13 22:29:25'),
(10, '', 'RT25', 'KEI', '2025-05-13 22:59:25'),
(11, '+250785071037', 'RT30', 'kalisa', '2025-05-14 11:02:27'),
(12, '+250785071037', 'RT25', 'OLIVIER', '2025-05-14 11:05:48'),
(13, '+250785071037', 'RT30', 'OLIVIERR', '2025-05-14 12:18:36'),
(14, '+250784062884', 'RT40', 'MANZI', '2025-05-14 12:53:28');

-- --------------------------------------------------------

--
-- Table structure for table `buses`
--

CREATE TABLE `buses` (
  `id` int(11) NOT NULL,
  `route_id` varchar(10) NOT NULL,
  `location` varchar(50) NOT NULL,
  `eta` varchar(50) NOT NULL,
  `seats_available` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `buses`
--

INSERT INTO `buses` (`id`, `route_id`, `location`, `eta`, `seats_available`) VALUES
(1, 'RT25', 'Downtown', '12:30 PM', 6),
(2, 'RT30', 'Kimironko', '1:15 PM', 5),
(3, 'RT40', 'Nyabugogo', '1:45 PM', 0),
(4, 'RT50', 'RUBAVU', '12:00 PM', 6),
(6, 'RT60', 'Westlands', '15 minutes', 30),
(7, 'RT45', 'Kasarani', '45 minutes', 50);

-- --------------------------------------------------------

--
-- Table structure for table `sms_history`
--

CREATE TABLE `sms_history` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `message` text NOT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `message_type` enum('sent','received') DEFAULT 'sent'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sms_history`
--

INSERT INTO `sms_history` (`id`, `phone_number`, `message`, `sent_at`, `message_type`) VALUES
(1, '+250785071037', 'SEAT AVAILABILITY\nBus: RT25\nAvailable seats: 10\nCurrent location: Downtown\nEstimated arrival: 12:30 PM\nCheck time: 12/05/2025 19:19\nSeats are filling up fast. Book soon to secure your spot!', '2025-05-12 17:19:45', 'sent'),
(2, '+250785071037', 'Would you like to book one of the 10 available seats on bus RT25? Dial *123# and select option 4 to book now. - SmartBus', '2025-05-12 17:19:49', 'sent'),
(3, '+250784062884', 'BOOKING CONFIRMATION\nHi iriho,\nYour seat on bus RT25 is confirmed.\nCurrent location: Downtown\nETA: 12:30 PM\nRemaining seats: 9\nBooking time: 12/05/2025 19:22\nThank you for choosing SmartBus!', '2025-05-12 17:22:00', 'sent'),
(4, '+250784062884', 'REMINDER: Your bus RT25 is scheduled to arrive at 12:30 PM. Please be at the pickup location on time. - SmartBus', '2025-05-12 17:22:04', 'sent'),
(5, '+250784062884', 'BOOKING CANCELLATION\nHi iriho,\nYour booking on bus RT25 has been cancelled.\nCancellation time: 12/05/2025 19:25\nCurrent available seats: 11\nThank you for using SmartBus.', '2025-05-12 17:25:07', 'sent'),
(6, '+250784062884', 'Your refund for the cancelled booking on bus RT25 has been processed. Please allow 3-5 business days for the amount to reflect in your account. - SmartBus', '2025-05-12 17:25:10', 'sent'),
(7, '+250785275072', 'SEAT AVAILABILITY\nBus: RT40\nAvailable seats: 2\nCurrent location: Nyabugogo\nEstimated arrival: 1:45 PM\nCheck time: 12/05/2025 19:58\nSeats are filling up fast. Book soon to secure your spot!', '2025-05-12 17:58:37', 'sent'),
(8, '+250785275072', 'Would you like to book one of the 2 available seats on bus RT40? Dial *123# and select option 4 to book now. - SmartBus', '2025-05-12 17:58:38', 'sent'),
(9, '+250785275072', 'SEAT AVAILABILITY\nBus: RT30\nAvailable seats: 8\nCurrent location: Kimironko\nEstimated arrival: 1:15 PM\nCheck time: 12/05/2025 20:09\nSeats are filling up fast. Book soon to secure your spot!', '2025-05-12 18:09:07', 'sent'),
(10, '+250785275072', 'Would you like to book one of the 8 available seats on bus RT30? Dial *123# and select option 4 to book now. - SmartBus', '2025-05-12 18:09:08', 'sent'),
(11, '+250785275072', 'BOOKING CONFIRMATION\nHi olivier,\nYour seat on bus RT25 is confirmed.\nCurrent location: Downtown\nETA: 12:30 PM\nRemaining seats: 9\nBooking time: 12/05/2025 20:21\nThank you for choosing SmartBus!', '2025-05-12 18:21:27', 'sent'),
(12, '+250785275072', 'REMINDER: Your bus RT25 is scheduled to arrive at 12:30 PM. Please be at the pickup location on time. - SmartBus', '2025-05-12 18:21:29', 'sent'),
(13, '+250785275072', 'Welcome to SmartBus, iriho! Your registration is complete. Dial *123# to access our services anytime.', '2025-05-12 18:50:33', 'sent'),
(14, '+250785275072', 'SmartBus Tips:\n1. Track buses in real-time\n2. Book seats in advance\n3. Check your SMS history for important updates\n4. Cancel bookings if your plans change', '2025-05-12 18:50:35', 'sent'),
(15, '+250785275072', 'SEAT AVAILABILITY\nBus: RT40\nAvailable seats: 2\nCurrent location: Nyabugogo\nEstimated arrival: 1:45 PM\nCheck time: 12/05/2025 20:51\nSeats are filling up fast. Book soon to secure your spot!', '2025-05-12 18:51:11', 'sent'),
(16, '+250785275072', 'Would you like to book one of the 2 available seats on bus RT40? Dial *123# and select option 4 to book now. - SmartBus', '2025-05-12 18:51:13', 'sent'),
(17, '+254790029270', 'Welcome to SmartBus, gervais! Your registration is complete. Dial *123# to access our services anytime.', '2025-05-13 20:24:35', 'sent'),
(18, '+254790029270', 'SmartBus Tips:\n1. Track buses in real-time\n2. Book seats in advance\n3. Check your SMS history for important updates\n4. Cancel bookings if your plans change', '2025-05-13 20:24:39', 'sent'),
(19, '+254790029270', 'BUSES WITH 2+ SEATS\nCurrent time: 13/05/2025 22:26\n\nBus: RT25\n- ETA: 12:30 PM\n- Available seats: 9\n\nBus: RT30\n- ETA: 1:15 PM\n- Available seats: 8\n\nBus: RT40\n- ETA: 1:45 PM\n- Available seats: 2\n\nBus: RT50\n- ETA: 12:00 PM\n- Available seats: 6\n\nFound 4 buses with 2+ seats available.', '2025-05-13 20:26:35', 'sent'),
(20, '+254790029270', 'SEAT AVAILABILITY\nBus: RT40\nAvailable seats: 2\nCurrent location: Nyabugogo\nEstimated arrival: 1:45 PM\nCheck time: 13/05/2025 22:27\nSeats are filling up fast. Book soon to secure your spot!', '2025-05-13 20:27:16', 'sent'),
(21, '+254790029270', 'Would you like to book one of the 2 available seats on bus RT40? Dial *123# and select option 4 to book now. - SmartBus', '2025-05-13 20:27:19', 'sent'),
(22, '+254790029270', 'SEAT AVAILABILITY\nBus: RT40\nAvailable seats: 2\nCurrent location: Nyabugogo\nEstimated arrival: 1:45 PM\nCheck time: 13/05/2025 22:27\nSeats are filling up fast. Book soon to secure your spot!', '2025-05-13 20:27:41', 'sent'),
(23, '+254790029270', 'Would you like to book one of the 2 available seats on bus RT40? Dial *123# and select option 4 to book now. - SmartBus', '2025-05-13 20:27:44', 'sent'),
(24, '+254790029270', 'BUS TRACKING INFORMATION\nBus: RT40\nCurrent location: Nyabugogo\nEstimated arrival: 1:45 PM\nAvailable seats: 2\nTracking time: 13/05/2025 22:28\nThank you for using SmartBus tracking.', '2025-05-13 20:28:19', 'sent'),
(25, '+254790029270', 'Would you like to book a seat on bus RT40? Dial *123# and select option 4 to book a seat. - SmartBus', '2025-05-13 20:28:23', 'sent'),
(26, '+254790029270', 'BOOKING CONFIRMATION\nHi gervais,\nYour seat on bus RT40 is confirmed.\nCurrent location: Nyabugogo\nETA: 1:45 PM\nRemaining seats: 1\nBooking time: 13/05/2025 22:29\nThank you for choosing SmartBus!', '2025-05-13 20:29:27', 'sent'),
(27, '+254790029270', 'REMINDER: Your bus RT40 is scheduled to arrive at 1:45 PM. Please be at the pickup location on time. - SmartBus', '2025-05-13 20:29:30', 'sent'),
(28, '+250785071037', 'Welcome to SmartBus, kalisa! Your registration is complete. Dial *123# to access our services anytime.', '2025-05-13 21:25:29', 'sent'),
(29, '+250785071037', 'SmartBus Tips:\n1. Track buses in real-time\n2. Book seats in advance\n3. Check your SMS history for important updates\n4. Cancel bookings if your plans change', '2025-05-13 21:25:31', 'sent'),
(30, '+250785071037', 'SEAT AVAILABILITY\nBus: RT40\nAvailable seats: 1\nCurrent location: Nyabugogo\nEstimated arrival: 1:45 PM\nCheck time: 13/05/2025 23:26\nSeats are filling up fast. Book soon to secure your spot!', '2025-05-13 21:26:13', 'sent'),
(31, '+250785071037', 'Would you like to book one of the 1 available seats on bus RT40? Dial *123# and select option 4 to book now. - SmartBus', '2025-05-13 21:26:15', 'sent'),
(32, '+250785071037', 'SEAT AVAILABILITY\nBus: RT30\nAvailable seats: 8\nCurrent location: Kimironko\nEstimated arrival: 1:15 PM\nCheck time: 13/05/2025 23:43\nSeats are filling up fast. Book soon to secure your spot!', '2025-05-13 21:43:18', 'sent'),
(33, '+250785071037', 'Would you like to book one of the 8 available seats on bus RT30? Dial *123# and select option 4 to book now. - SmartBus', '2025-05-13 21:43:20', 'sent'),
(34, '+254712345678', 'Hello from SmartBus! This is a test message.', '2025-05-13 22:55:23', 'sent'),
(35, '+254712345678', 'BUS TRACKING INFORMATION\nBus: RT60\nCurrent location: Westlands\nETA: 15 minutes\nAvailable seats: 30\nTracking time: 14/05/2025 00:55', '2025-05-13 22:55:23', 'sent'),
(36, '+254712345678', 'BOOKING CONFIRMATION\nHi Test User,\nYour seat on bus RT60 is confirmed.\nCurrent location: Westlands\nETA: 15 minutes\nBooking time: 14/05/2025 00:55\nThank you for choosing SmartBus!', '2025-05-13 22:55:23', 'sent'),
(37, '+250784062884', 'COMPLETE SMS HISTORY\n\n[12/05/2025 19:25] 📤 Sent\nYour refund for the cancelled booking on bus RT25 has been processed. Please allow 3-5 business days for the amount to reflect in your account. - SmartBus\n\n[12/05/2025 19:25] 📤 Sent\nBOOKING CANCELLATION\nHi iriho,\nYour booking on bus RT25 has been cancelled.\nCancellation time: 12/05/2025 19:25\nCurrent available seats: 11\nThank you for using SmartBus.\n\n[12/05/2025 19:22] 📤 Sent\nREMINDER: Your bus RT25 is scheduled to arrive at 12:30 PM. Please be at the pickup location on time. - SmartBus\n\n[12/05/2025 19:22] 📤 Sent\nBOOKING CONFIRMATION\nHi iriho,\nYour seat on bus RT25 is confirmed.\nCurrent location: Downtown\nETA: 12:30 PM\nRemaining seats: 9\nBooking time: 12/05/2025 19:22\nThank you for choosing SmartBus!\n\n', '2025-05-14 13:05:44', 'sent'),
(38, '+250784062884', 'SmartBus Update: Thank you for using SmartBus! For assistance, dial *123# anytime.', '2025-05-14 13:05:46', 'sent'),
(39, '+250784062884', 'BUS SCHEDULE\nCurrent time: 14/05/2025 15:22\n\nBus: RT25\n- ETA: 12:30 PM\n- Location: Downtown\n- Available seats: 6\n\nBus: RT30\n- ETA: 1:15 PM\n- Location: Kimironko\n- Available seats: 5\n\nBus: RT40\n- ETA: 1:45 PM\n- Location: Nyabugogo\n- Available seats: 0\n\nBus: RT50\n- ETA: 12:00 PM\n- Location: RUBAVU\n- Available seats: 6\n\nBus: RT60\n- ETA: 15 minutes\n- Location: Westlands\n- Available seats: 30\n\nBus: RT45\n- ETA: 45 minutes\n- Location: Kasarani\n- Available seats: 50\n\nThank you for using SmartBus scheduling service.', '2025-05-14 13:22:39', 'sent'),
(40, '+250784062884', 'SmartBus Update: Thank you for using SmartBus! For assistance, dial *123# anytime.', '2025-05-14 13:22:43', 'sent'),
(41, '+250786525963', 'Welcome to SmartBus, bill! Your registration is complete. Dial *123# to access our services anytime.', '2025-05-14 13:25:31', 'sent'),
(42, '+250786525963', 'SmartBus Tips:\n1. Track buses in real-time\n2. Book seats in advance\n3. Check your SMS history for important updates\n4. Cancel bookings if your plans change', '2025-05-14 13:25:37', 'sent'),
(43, '+250786525963', 'BUS TRACKING INFORMATION\nBus: RT40\nCurrent location: Nyabugogo\nEstimated arrival: 1:45 PM\nAvailable seats: 0\nTracking time: 14/05/2025 15:26\nThank you for using SmartBus tracking.', '2025-05-14 13:26:15', 'sent'),
(44, '+250786525963', 'Would you like to book a seat on bus RT40? Dial *123# and select option 4 to book a seat. - SmartBus', '2025-05-14 13:26:16', 'sent'),
(45, '+250786525963', 'SmartBus Update: Thank you for using SmartBus! For assistance, dial *123# anytime.', '2025-05-14 13:26:18', 'sent'),
(46, '+250786525963', 'SEAT AVAILABILITY\nBus: RT25\nAvailable seats: 6\nCurrent location: Downtown\nEstimated arrival: 12:30 PM\nCheck time: 14/05/2025 15:28\nSeats are filling up fast. Book soon to secure your spot!', '2025-05-14 13:28:24', 'sent'),
(47, '+250786525963', 'Would you like to book one of the 6 available seats on bus RT25? Dial *123# and select option 4 to book now. - SmartBus', '2025-05-14 13:28:25', 'sent'),
(48, '+250786525963', 'SmartBus Update: Thank you for using SmartBus! For assistance, dial *123# anytime.', '2025-05-14 13:28:28', 'sent'),
(49, '+250786525963', 'BUS TRACKING INFORMATION\nBus: RT40\nCurrent location: Nyabugogo\nEstimated arrival: 1:45 PM\nAvailable seats: 0\nTracking time: 14/05/2025 15:41\nThank you for using SmartBus tracking.', '2025-05-14 13:41:08', 'sent'),
(50, '+250786525963', 'Would you like to book a seat on bus RT40? Dial *123# and select option 4 to book a seat. - SmartBus', '2025-05-14 13:41:09', 'sent'),
(51, '+250786525963', 'SmartBus Update: Thank you for using SmartBus! For assistance, dial *123# anytime.', '2025-05-14 13:41:10', 'sent');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `phone_number`, `full_name`, `email`, `registration_date`) VALUES
(1, '+250785275072', 'iriho', 'iriho@gmail.com', '2025-05-12 18:50:33'),
(2, '+254790029270', 'gervais', 'gervais@gmail.com', '2025-05-13 20:24:35'),
(3, '+250785071037', 'kalisa', '', '2025-05-13 21:25:29'),
(4, '', 'professor', 'profe@gmail.com', '2025-05-13 22:23:26'),
(5, '+250784062884', 'kaka', '', '2025-05-14 12:33:33'),
(6, '+254784062884', 'sophia', '', '2025-05-14 12:36:06'),
(7, '+250786525963', 'bill', '', '2025-05-14 13:25:23');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `buses`
--
ALTER TABLE `buses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `route_id` (`route_id`);

--
-- Indexes for table `sms_history`
--
ALTER TABLE `sms_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `phone_number` (`phone_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `buses`
--
ALTER TABLE `buses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sms_history`
--
ALTER TABLE `sms_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
