-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 24, 2026 at 12:21 PM
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
-- Database: `vehicles`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `GetUserBookings` (IN `uid` INT)   BEGIN
    SELECT b.booking_id, v.vehicle_name, b.start_date, b.end_date, b.status
    FROM bookings b
    JOIN vehicles v ON b.vehicle_id = v.vehicle_id
    WHERE b.user_id = uid;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `active_bookings`
-- (See below for the actual view)
--
CREATE TABLE `active_bookings` (
`booking_id` int(11)
,`full_name` varchar(100)
,`vehicle_name` varchar(100)
,`start_date` date
,`end_date` date
,`status` enum('Pending','Confirmed','Cancelled','Rejected')
);

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password`) VALUES
(3, 'shreeya', '$2y$10$qnHT2hzbtcAeGRAwputwOeF4r23FxIdsw/3BuGzEPkIDX/9pnd0hi'),
(4, 'mahima', '$2y$10$Bx4muZ/ceAT8iObNaV2VKegIeXQxT829jc4QYMf9hJcd70xpa8Rua');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `return_time` time NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('Pending','Confirmed','Cancelled','Rejected') NOT NULL DEFAULT 'Pending',
  `notify_approved` tinyint(1) DEFAULT 0,
  `payment_status` enum('Pending','Paid') DEFAULT 'Pending',
  `returned_status` enum('Not Returned','Returned') DEFAULT 'Not Returned',
  `payment_date` datetime DEFAULT NULL,
  `returned_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `notify_end` tinyint(1) DEFAULT 1,
  `owner_updated` tinyint(1) DEFAULT 0,
  `end_popup_shown` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `vehicle_id`, `start_date`, `end_date`, `return_time`, `purpose`, `total_amount`, `status`, `notify_approved`, `payment_status`, `returned_status`, `payment_date`, `returned_date`, `created_at`, `notify_end`, `owner_updated`, `end_popup_shown`) VALUES
(113, 16, 15, '2026-01-11', '2026-01-11', '12:00:00', 'Daily Commute', 950.00, 'Confirmed', 1, 'Paid', 'Returned', '2026-01-11 14:00:24', '2026-01-11 14:00:24', '2026-01-11 13:59:55', 1, 1, 0),
(117, 16, 15, '2026-01-11', '2026-01-14', '12:00:00', 'Others', 3800.00, 'Confirmed', 1, 'Paid', 'Returned', '2026-01-11 22:01:39', '2026-01-15 00:20:44', '2026-01-11 17:24:42', 1, 1, 0),
(128, 16, 41, '2026-02-28', '2026-03-02', '12:00:00', 'Ride Out', 4800.00, 'Confirmed', 1, 'Pending', 'Not Returned', NULL, NULL, '2026-02-27 07:53:30', 1, 1, 0);

--
-- Triggers `bookings`
--
DELIMITER $$
CREATE TRIGGER `after_booking_insert` AFTER INSERT ON `bookings` FOR EACH ROW BEGIN
    INSERT INTO notifications(type, reference_id, message)
    VALUES('booking', NEW.booking_id, CONCAT('New booking by user ID ', NEW.user_id));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `type` enum('booking','registration') DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `message` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_read` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `reference_id`, `message`, `created_at`, `is_read`) VALUES
(187, 'booking', 118, 'New booking by user ID 16', '2026-01-13 17:52:27', 0),
(188, 'booking', 118, 'New booking: Toyota Corolla by user ID 16', '2026-01-13 17:52:27', 0),
(189, 'booking', 119, 'New booking by user ID 16', '2026-01-13 19:45:47', 0),
(190, 'booking', 119, 'New booking: Toyota Camry by user ID 16', '2026-01-13 19:45:47', 0),
(191, '', 66, 'New user registered: djfd dsfjk (ID: 66)', '2026-01-14 21:35:07', 0),
(192, '', 67, 'New user registered: gd fdv (ID: 67)', '2026-01-14 21:43:20', 0),
(193, '', 68, 'New user registered: Janet B (ID: 68)', '2026-01-14 22:03:43', 0),
(194, '', 69, 'New user registered: Jane Bone (ID: 69)', '2026-01-14 22:06:35', 0),
(195, '', 70, 'New user registered: Jane Bone (ID: 70)', '2026-01-14 22:08:37', 0),
(196, '', 71, 'New user registered: Jane Bony (ID: 71)', '2026-01-14 22:17:19', 0),
(197, '', 72, 'New user registered: hat bat (ID: 72)', '2026-01-14 22:20:58', 0),
(198, '', 73, 'New user registered: bat hat (ID: 73)', '2026-01-14 22:21:28', 0),
(199, '', 74, 'New user registered: sdf dfv (ID: 74)', '2026-01-14 22:24:00', 0),
(200, '', 75, 'New user registered: Lily H (ID: 75)', '2026-01-14 22:59:24', 0),
(201, 'booking', 120, 'New booking by user ID 72', '2026-01-15 00:20:38', 0),
(202, 'booking', 120, 'New booking: Toyota Camry by user ID 72', '2026-01-15 00:20:38', 0),
(203, 'booking', 121, 'New booking by user ID 16', '2026-01-15 20:34:37', 0),
(204, 'booking', 121, 'New booking: Toyota Corolla by user ID 16', '2026-01-15 20:34:37', 0),
(205, 'booking', 122, 'New booking by user ID 16', '2026-01-15 20:41:09', 0),
(206, 'booking', 122, 'New booking: Toyota Camry by user ID 16', '2026-01-15 20:41:09', 0),
(207, 'booking', 123, 'New booking by user ID 16', '2026-01-15 21:14:02', 0),
(208, 'booking', 123, 'New booking: Duke by user ID 16', '2026-01-15 21:14:02', 0),
(209, 'booking', 124, 'New booking by user ID 16', '2026-01-16 06:53:54', 0),
(210, 'booking', 124, 'New booking: Toyota Corolla by user ID 16', '2026-01-16 06:53:54', 0),
(211, 'booking', 125, 'New booking by user ID 16', '2026-01-16 09:13:16', 0),
(212, 'booking', 125, 'New booking: Toyota Corolla by user ID 16', '2026-01-16 09:13:16', 0),
(213, 'booking', 126, 'New booking by user ID 16', '2026-02-26 21:47:59', 0),
(214, 'booking', 126, 'New booking: Ineos Grenadier by user ID 16', '2026-02-26 21:47:59', 0),
(215, 'booking', 127, 'New booking by user ID 16', '2026-02-26 21:49:21', 0),
(216, 'booking', 127, 'New booking: Honda CB350 by user ID 16', '2026-02-26 21:49:21', 0),
(217, 'booking', 128, 'New booking by user ID 16', '2026-02-27 07:53:30', 0),
(218, 'booking', 128, 'New booking: Crossfire RM 250 by user ID 16', '2026-02-27 07:53:30', 0),
(219, 'booking', 129, 'New booking by user ID 16', '2026-02-27 08:21:10', 0),
(220, 'booking', 129, 'New booking: Ineos Grenadier by user ID 16', '2026-02-27 08:21:10', 0),
(221, 'booking', 130, 'New booking by user ID 16', '2026-02-27 10:34:51', 0),
(222, 'booking', 130, 'New booking: Toyota Corolla by user ID 16', '2026-02-27 10:34:51', 0),
(223, 'booking', 131, 'New booking by user ID 16', '2026-02-27 10:37:19', 0),
(224, 'booking', 131, 'New booking: Toyota Corolla by user ID 16', '2026-02-27 10:37:19', 0);

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('Pending','Completed','Failed') DEFAULT 'Pending',
  `transaction_date` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `booking_id`, `user_id`, `vehicle_id`, `owner_id`, `amount`, `payment_method`, `status`, `transaction_date`) VALUES
(1, 119, 16, 32, 49, 2700.00, 'eSewa', 'Completed', '2026-01-13 19:46:16'),
(2, 124, 16, 30, 49, 8400.00, 'eSewa', 'Completed', '2026-01-16 06:55:28'),
(3, 123, 16, 15, 49, 1900.00, 'Cash', 'Completed', '2026-01-16 09:10:29'),
(4, 120, 72, 32, 49, 10800.00, 'eSewa', 'Completed', '2026-02-13 20:15:21'),
(5, 127, 16, 12, 49, 3800.00, 'Cash', 'Completed', '2026-02-27 07:54:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','owner','admin') NOT NULL DEFAULT 'user',
  `is_approved` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `phone`, `email`, `password`, `role`, `is_approved`) VALUES
(16, 'A user', '9812345678', 'user@gmail.com', '$2y$10$TNuu.u1FuwZuED.WOJi2DOaP2u5kv8BzO2UYGO2oS7fM2OjoavSM2', 'user', 1),
(49, 'Owner', '9841293344', 'owner@gmail.com', '12345', 'owner', 1),
(54, 'Mahima M', '9841293345', 'mahima@gmail.com', '$2y$10$YjsJEFYNdTW84/dwtLgH8ePxhvlZB/AqJjESQrb8QmzPpABWZxTPu', 'user', 1),
(73, 'bat hat', '9712345678', 'bat@gmail.com', '$2y$10$J49aovn8NpmMQBR5uTvRMenEKBFwMUDmC1tP86Ovc7HfECCOexKtm', 'user', 1),
(78, 'Shreeya Bista', '9864546466', 'sdf@gfv.gfb', '$2y$10$AtsT0U5w3qFMfXxmTBIHgOrsmQSwMBvp4vyq73ibxZto6m32UfcHW', 'user', 1);

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `vehicle_id` int(11) NOT NULL,
  `vehicle_name` varchar(100) NOT NULL,
  `vehicle_type` enum('Car','Bike','Truck','Other') NOT NULL,
  `plate_number` varchar(50) NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `availability` enum('Available','Unavailable') DEFAULT 'Available',
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `posted_date` datetime DEFAULT current_timestamp(),
  `is_approved` tinyint(1) NOT NULL DEFAULT 0,
  `booked_units` int(11) NOT NULL DEFAULT 0,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `vehicle_name`, `vehicle_type`, `plate_number`, `price_per_day`, `availability`, `description`, `image`, `owner_id`, `quantity`, `posted_date`, `is_approved`, `booked_units`, `status`) VALUES
(12, 'Honda CB350', 'Bike', 'BA 23  KHA 6510', 950.00, 'Available', 'A good reliable bike for smooth ride. In great condition for any type of journey', '1767708419_hb.jpg', 49, 7, '2026-01-06 19:51:59', 1, 1, ''),
(15, 'Duke', 'Bike', 'BA 24  KHA 6510', 950.00, 'Available', 'Powerful and stylish street bike for excellent performance. It is ideal for riders who enjoy speed and sporting handling.', '1767717001_du.jpg', 49, 5, '2026-01-06 22:15:01', 1, 0, ''),
(32, 'Toyota Camry', 'Car', 'Ba 2 Kha 12', 2700.00, 'Available', 'The Toyota Camry is a popular mid-size sedan known for its reliability, comfort, and smooth performance. It features a stylish exterior design, a spacious and comfortable interior, and advanced safety and technology features.', '1768123526_tc.jpg', 49, 5, '2026-01-11 15:10:26', 1, 1, ''),
(40, 'Ineos Grenadier', 'Car', 'GI 12 K 3', 4500.00, 'Available', 'Experience ultimate off-road capability with the Ineos Grenadier. For tough terrains and long journeys, perfect for adventure trips, outdoor explorations, and heavy-duty use. With a durable design, powerful engine, and spacious interior, it combines comfort and reliability for every journey.', '1768492370_ia.jpg', 49, 5, '2026-01-15 21:37:50', 1, 1, ''),
(41, 'Crossfire RM 250', 'Bike', 'BA 67 KHA 6767', 1600.00, 'Available', 'Crossfire RM 250 is a powerful and sporty dirt bike made for riders who love adventure and off-road riding. It is lightweight, which makes it easy to control on rough roads and trails.', '1772122313_R.jpg', 49, 17, '2026-02-26 21:53:11', 1, 1, '');

-- --------------------------------------------------------

--
-- Structure for view `active_bookings`
--
DROP TABLE IF EXISTS `active_bookings`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_bookings`  AS SELECT `b`.`booking_id` AS `booking_id`, `u`.`full_name` AS `full_name`, `v`.`vehicle_name` AS `vehicle_name`, `b`.`start_date` AS `start_date`, `b`.`end_date` AS `end_date`, `b`.`status` AS `status` FROM ((`bookings` `b` join `users` `u` on(`b`.`user_id` = `u`.`user_id`)) join `vehicles` `v` on(`b`.`vehicle_id` = `v`.`vehicle_id`)) WHERE `b`.`status` = 'Confirmed' ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`vehicle_id`),
  ADD UNIQUE KEY `plate_number` (`plate_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=225;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
