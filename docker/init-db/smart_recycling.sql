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
-- Database: `smart_recycling`
--
CREATE DATABASE IF NOT EXISTS `smart_recycling`;
USE `smart_recycling`;
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_remit`
--

CREATE TABLE `tbl_remit` (
  `id` double NOT NULL AUTO_INCREMENT,
  `item_name` text NOT NULL,
  `item_points` double DEFAULT NULL,
  `sortation_center_id` double NOT NULL,
  `user_id` double NOT NULL,
  `item_quantity` int(11) NOT NULL,
  `points` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sortation_centers`
CREATE TABLE `tbl_sortation_centers` (
  `id` double NOT NULL AUTO_INCREMENT,
  `name` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `categories` varchar(255) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `rating` text DEFAULT NULL,
  `link` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id` double NOT NULL AUTO_INCREMENT,
  `fullname` text NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `total_points` int(11) DEFAULT 0,
  `is_admin` tinyint(1) DEFAULT 0,
  `user_type` enum('individual', 'business', 'center') NOT NULL DEFAULT 'individual',
  `business_name` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `center_id` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`center_id`) REFERENCES `tbl_sortation_centers`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Default admin account (minimal required data)
INSERT INTO `tbl_user` (`fullname`, `username`, `password`, `is_admin`, `user_type`) VALUES
('Administrator', 'admin@gmail.com', '12345', 1, 'individual');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pickups`
--

CREATE TABLE `tbl_pickups` (
  `id` double NOT NULL AUTO_INCREMENT,
  `user_id` double NOT NULL,
  `pickup_date` DATE NOT NULL,
  `pickup_time` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `items` text NOT NULL,
  `recurring` tinyint(1) DEFAULT 0,
  `frequency` varchar(20) DEFAULT 'one-time',
  `status` varchar(20) DEFAULT 'pending',
  `current_status` enum('scheduled','in_transit','arrived','completed','cancelled') NOT NULL DEFAULT 'scheduled',
  `estimated_completion` timestamp NULL DEFAULT NULL,
  `actual_completion` timestamp NULL DEFAULT NULL,
  `capacity_confirmed` tinyint(1) DEFAULT 0,
  `vehicle_assigned` varchar(50) DEFAULT NULL,
  `driver_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `tbl_user`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_rewards`
--

CREATE TABLE `tbl_rewards` (
  `id` double NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `points_required` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_redemptions`
--

CREATE TABLE `tbl_redemptions` (
  `id` double NOT NULL AUTO_INCREMENT,
  `user_id` double NOT NULL,
  `reward_id` double NOT NULL,
  `points_used` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `tbl_user`(`id`),
  FOREIGN KEY (`reward_id`) REFERENCES `tbl_rewards`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_faqs`
--

CREATE TABLE `tbl_faqs` (
  `id` double NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(50) DEFAULT 'General',
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_bulk_requests`
--

CREATE TABLE `tbl_bulk_requests` (
  `id` double NOT NULL AUTO_INCREMENT,
  `business_id` double NOT NULL,
  `request_type` enum('pickup', 'drop-off') NOT NULL,
  `material_types` text NOT NULL,
  `estimated_quantity` int(11) NOT NULL,
  `preferred_date` DATE NOT NULL,
  `address` text NOT NULL,
  `additional_notes` text,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`business_id`) REFERENCES `tbl_user`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_quotes`
--

CREATE TABLE `tbl_quotes` (
  `id` double NOT NULL AUTO_INCREMENT,
  `request_id` double NOT NULL,
  `center_id` double NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `estimated_points` int(11) NOT NULL,
  `notes` text,
  `expiration_date` DATE NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`request_id`) REFERENCES `tbl_bulk_requests`(`id`),
  FOREIGN KEY (`center_id`) REFERENCES `tbl_sortation_centers`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_inventory`
--

CREATE TABLE `tbl_inventory` (
  `id` double NOT NULL AUTO_INCREMENT,
  `center_id` double NOT NULL,
  `material_type` varchar(50) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0,
  `capacity` decimal(10,2) NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`center_id`) REFERENCES `tbl_sortation_centers`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

-- REMOVE this section:
-- Table structure for table `tbl_processing`
-- CREATE TABLE `tbl_processing` (
--  `id` double NOT NULL AUTO_INCREMENT,
--  `center_id` double NOT NULL,
--  `batch_id` varchar(20) NOT NULL,
--  `material_type` varchar(50) NOT NULL,
--  `quantity` decimal(10,2) NOT NULL,
--  `status` enum('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
--  `start_date` timestamp NULL DEFAULT NULL,
--  `completion_date` timestamp NULL DEFAULT NULL,
--  `notes` text DEFAULT NULL,
--  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
--  PRIMARY KEY (`id`),
--  FOREIGN KEY (`center_id`) REFERENCES `tbl_sortation_centers`(`id`)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_pickup_notifications`
--

CREATE TABLE `tbl_pickup_notifications` (
  `id` double NOT NULL AUTO_INCREMENT,
  `pickup_id` double NOT NULL,
  `message` text NOT NULL,
  `notification_type` enum('status_update','reminder','capacity_alert') NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`pickup_id`) REFERENCES `tbl_pickups`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;