-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 03, 2025 at 06:58 PM
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
-- Database: `web`
--

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expiry` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `password_resets`
--
DELIMITER $$
CREATE TRIGGER `check_expiry_time` BEFORE INSERT ON `password_resets` FOR EACH ROW BEGIN
    IF NEW.expiry < NOW() THEN
        SET NEW.expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR);
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','staff','customer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'System', 'Administrator', 'your_new_email@example.com', '$2y$10$oEYkMoAkuFK.g75HpyQlTOoSb78zuK7UngRY5CkjKh7xg6HPLkeAC', 'admin', '2025-01-03 13:02:49', '2025-01-03 17:56:24');

-- --------------------------------------------------------

--
-- Table structure for table `verification_attempts`
--

CREATE TABLE `verification_attempts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `verification_code` varchar(6) NOT NULL,
  `expiry` datetime NOT NULL,
  `is_used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verification_attempts`
--

INSERT INTO `verification_attempts` (`id`, `user_id`, `verification_code`, `expiry`, `is_used`, `created_at`) VALUES
(7, 1, '796844', '2025-01-03 18:31:51', 0, '2025-01-03 17:21:54'),
(8, 1, '115733', '2025-01-03 18:33:23', 1, '2025-01-03 17:23:27'),
(9, 1, '954432', '2025-01-03 18:54:19', 1, '2025-01-03 17:44:22'),
(10, 1, '182657', '2025-01-03 18:57:49', 1, '2025-01-03 17:47:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expiry` (`expiry`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_username` (`username`);

--
-- Indexes for table `verification_attempts`
--
ALTER TABLE `verification_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_verification` (`user_id`,`verification_code`),
  ADD KEY `idx_expiry` (`expiry`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `verification_attempts`
--
ALTER TABLE `verification_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `verification_attempts`
--
ALTER TABLE `verification_attempts`
  ADD CONSTRAINT `verification_attempts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `cleanup_expired_verifications` ON SCHEDULE EVERY 1 DAY STARTS '2025-01-03 21:02:49' ON COMPLETION NOT PRESERVE ENABLE DO BEGIN
    -- Delete expired verification attempts
    DELETE FROM verification_attempts 
    WHERE expiry < NOW() OR is_used = TRUE;
    
    -- Delete expired password reset tokens
    DELETE FROM password_resets 
    WHERE expiry < NOW() OR is_used = TRUE;
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
