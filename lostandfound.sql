-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 02, 2025 at 07:36 PM
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
-- Database: `lostandfound`
--

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claims`
--

INSERT INTO `claims` (`id`, `item_id`, `name`, `phone`, `email`) VALUES
(1, 8, 'atiqah', '0197352233', 'ikabahar25@gmail.com'),
(2, 2, 'atiqah', '0197352233', 'ikabahar25@gmail.com'),
(3, 12, 'atiqah', '0197352233', 'ikabahar25@gmail.com'),
(4, 7, 'atiqah', '0197352233', 'ikabahar25@gmail.com'),
(5, 7, 'atiqah', '0197352233', 'ikabahar25@gmail.com'),
(6, 5, 'atiqah', '0197352233', 'ikabahar25@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `found_items`
--

CREATE TABLE `found_items` (
  `id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `status` enum('claimed','unclaimed') DEFAULT 'unclaimed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `found_items`
--

INSERT INTO `found_items` (`id`, `item_name`, `description`, `image`, `status`, `created_at`) VALUES
(1, 'Black Wallet', 'A black leather wallet with multiple card slots.', 'wallet.jpg', 'unclaimed', '2025-01-02 17:00:24'),
(2, 'Red Backpack', 'A red backpack containing notebooks and pens.', 'backpack.jpg', 'unclaimed', '2025-01-02 17:00:24'),
(3, 'Smartphone', 'A smartphone with a cracked screen.', 'smartphone.jpg', 'unclaimed', '2025-01-02 17:00:24'),
(4, 'Lost Wallet', 'A black leather wallet found near the toilet station 31.', 'wallet.jpg', 'unclaimed', '2025-01-02 17:27:41'),
(5, 'Lost Phone', 'An iPhone 12 found at the bus stop.', 'iphone12.jpg', 'unclaimed', '2025-01-02 17:27:41'),
(6, 'Keys Found', 'A set of keys with a keychain found at the men toilet.', 'keys.jpg', 'unclaimed', '2025-01-02 17:27:41'),
(7, 'Found Glasses', 'A pair of prescription glasses found in toilet near the station with name on it', 'glasses.jpg', 'unclaimed', '2025-01-02 17:27:41'),
(8, 'Lost Watch', 'A silver wristwatch found at the seat next to the toilet at the station near masjid jamek.', 'watch.jpg', 'unclaimed', '2025-01-02 17:27:41'),
(9, 'Lost Jacket', 'A red jacket found in the train station.', 'jacket.jpg', 'unclaimed', '2025-01-02 17:29:10'),
(10, 'Found Shoes', 'A ring found near the ticket center on the station 1', 'shoes.jpg', 'unclaimed', '2025-01-02 17:29:10'),
(11, 'Lost Backpack', 'A black backpack found on the sidewalk.', 'backpack.jpg', 'unclaimed', '2025-01-02 17:29:10'),
(12, 'Found Umbrella', 'A blue umbrella found bench on near the station. ', 'umbrella.jpg', 'unclaimed', '2025-01-02 17:29:10');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `found_items`
--
ALTER TABLE `found_items`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `found_items`
--
ALTER TABLE `found_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `found_items` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
