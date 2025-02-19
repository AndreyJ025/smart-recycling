-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2025 at 06:37 PM
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

--
-- Dumping data for table `tbl_remit`
--

INSERT INTO tbl_remit (item_name, item_points, sortation_center_id, user_id, item_quantity, points, created_at) VALUES
('Plastic Bottles', 5, 1, 2, 10, 50, '2025-02-18 14:30:00'),
('Cardboard Boxes', 3, 3, 4, 8, 24, '2025-02-18 15:45:00'),
('Aluminum Cans', 4, 2, 3, 15, 60, '2025-02-17 11:20:00'),
('Glass Bottles', 6, 5, 5, 6, 36, '2025-02-17 13:15:00'),
('Paper Waste', 2, 4, 6, 20, 40, '2025-02-16 16:00:00'),
('Electronic Waste', 10, 6, 2, 2, 20, '2025-02-16 09:45:00'),
('Metal Scraps', 7, 7, 4, 5, 35, '2025-02-15 14:20:00'),
('Plastic Containers', 4, 1, 3, 12, 48, '2025-02-15 10:30:00'),
('Old Newspapers', 3, 3, 5, 25, 75, '2025-02-14 15:50:00'),
('Used Batteries', 8, 6, 6, 4, 32, '2025-02-14 11:25:00'),
('Plastic Bottles', 5, 2, 2, 15, 75, '2025-02-13 13:40:00'),
('Glass Bottles', 6, 5, 4, 8, 48, '2025-02-13 09:15:00'),
('Aluminum Cans', 4, 2, 3, 20, 80, '2025-02-12 16:30:00'),
('Paper Waste', 2, 4, 5, 30, 60, '2025-02-12 14:20:00'),
('Electronic Waste', 10, 6, 6, 3, 30, '2025-02-11 10:45:00');

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

--
-- Dumping data for table `tbl_sortation_centers`
--

INSERT INTO `tbl_sortation_centers` (`id`, `name`, `address`, `description`, `categories`, `contact`, `rating`, `link`) VALUES
(1, 'Envirocycling Fiber Inc (Sauyo)', 'B1 L12 Manchester Industrial Compound 2, Quezon City, 1116 Metro Manila, Philippines', 'Open from 7:00 AM to 6:00 PM every day.', 'plastic,paper', '(02) 8363-7121', '5', 'https://maps.google.com/?cid=2997478909304072391'),
(2, 'Green Haven Scrap Materials Trading', 'P2C2+FH5, Sebastian St, Valenzuela, Metro Manila, Philippines', 'Open from 8:00 AM to 4:00 PM every day except Sunday.', 'plastic,paper,metal', '(02) 8291-5432', '5', 'https://maps.google.com/?cid=3675111170217877456'),
(3, 'RPJ - Valenzuela', 'CNWB Compound, 20-A 1447, Marton Road, Valenzuela, Metro Manila, Philippines', 'Open from 9:00 AM to 6:00 PM every day except Sunday.', 'plastic,paper,metal', '(02) 8442-1234', '3', 'https://maps.google.com/?cid=13959482081240542526'),
(4, 'TPC Scrap Enterprises', 'Solar Urban Homes North, Solar Street Block 5, Lot 8, Phase 3, Caloocan, 1421 Metro Manila, Philippines', 'Open 24 hours every day except Sunday, when it is open from 10:00 AM to 5:00 PM.', 'plastic,paper,metal,electronics', '(02) 8512-7890', '5', 'https://tpcscrapenterprises.wordpress.com/'),
(5, 'YLJ Plastics - PET Bottle Scrap Buyer', 'PXHV+34Q, Valenzuela, Metro Manila, Philippines', 'Open from 9:00 AM to 5:00 PM every day except Saturday and Sunday.', 'plastic', '(02) 8665-4321', '5', 'https://maps.google.com/?cid=17228057435575507911'),
(6, 'FYM Scrap Trading', '202 Visayas Ave Extension, Novaliches, Quezon City, 1107 Metro Manila, Philippines', 'Open from 8:00 AM to 5:00 PM Monday to Saturday.', 'metal,paper,plastic,electronics', '(02) 8123-4567', '5', 'https://maps.google.com/?cid=12345678901234567890'),
(7, 'Jepoy Junk Shop', 'M2RP+WFG, Don Julio Gregorio, Novaliches, Quezon City, Metro Manila, Philippines', 'Open from 8:00 AM to 5:00 PM Monday to Saturday.', 'metal,paper,plastic,glass', '(02) 8234-5678', '4', 'https://maps.google.com/?cid=12345678901234567891'),
(8, 'Malate Junkshop', '9 Gregorio Araneta Ave, Sto Domingo, Quezon City, 1114 Metro Manila, Philippines', 'Open from 8:00 AM to 5:00 PM Monday to Saturday.', 'metal,paper,plastic,glass', '(02) 8345-6789', '4', 'https://maps.google.com/?cid=12345678901234567892'),
(9, 'RNP Junkshop', '144 Ilocos Sur, Bago Bantay, Quezon City, 1105 Metro Manila, Philippines', 'Open from 8:00 AM to 5:00 PM Monday to Saturday.', 'metal,paper,plastic', '(02) 8456-7890', '3', 'https://maps.google.com/?cid=12345678901234567893'),
(10, 'Puring Junkshop', '105 Kamias Rd, Diliman, Quezon City, 1101 Metro Manila, Philippines', 'Open from 8:00 AM to 5:00 PM Monday to Saturday.', 'metal,paper,plastic,electronics,glass', '(02) 8567-8901', '4', 'https://maps.google.com/?cid=12345678901234567894');


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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id`, `fullname`, `username`, `password`, `total_points`, `is_admin`) VALUES
(1, 'Alexander Morgan', 'admin@gmail.com', '12345', 0, 1),
(2, 'Emily Rodriguez', 'user1@gmail.com', '12345', 145, 0),
(3, 'Marcus Chen', 'user2@gmail.com', '12345', 188, 0),
(4, 'Sofia Bennett', 'user3@gmail.com', '12345', 107, 0),
(5, 'Nathan Walker', 'user4@gmail.com', '12345', 171, 0),
(6, 'Isabella Thompson', 'user5@gmail.com', '12345', 102, 0);


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


-- For pickups
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `tbl_user`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample pickup requests
INSERT INTO tbl_pickups (user_id, pickup_date, pickup_time, address, items, recurring, frequency, status) VALUES
(2, '2025-02-20', 'morning', '123 Green St, Manila', 'Plastic bottles, cardboard boxes', 1, 'weekly', 'pending'),
(3, '2025-02-21', 'afternoon', '456 Eco Ave, Quezon City', 'Electronic waste, batteries', 0, 'one-time', 'confirmed'),
(4, '2025-02-22', 'morning', '789 Recycling Rd, Makati', 'Metal scraps, aluminum cans', 0, 'one-time', 'pending'),
(5, '2025-02-23', 'evening', '321 Earth Blvd, Pasig', 'Glass bottles, newspapers', 1, 'monthly', 'confirmed'),
(6, '2025-02-24', 'afternoon', '654 Nature St, Taguig', 'Paper waste, plastic containers', 0, 'one-time', 'completed'),
(2, '2025-02-25', 'morning', '123 Green St, Manila', 'Plastic bottles', 1, 'weekly', 'pending'),
(3, '2025-02-26', 'evening', '456 Eco Ave, Quezon City', 'Metal waste', 0, 'one-time', 'pending'),
(4, '2025-02-27', 'afternoon', '789 Recycling Rd, Makati', 'Paper waste', 0, 'one-time', 'pending'),
(5, '2025-02-28', 'morning', '321 Earth Blvd, Pasig', 'Glass materials', 1, 'monthly', 'pending'),
(6, '2025-03-01', 'evening', '654 Nature St, Taguig', 'Electronic waste', 0, 'one-time', 'pending');

-- For rewards
CREATE TABLE `tbl_rewards` (
  `id` double NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `points_required` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Insert sample rewards
INSERT INTO tbl_rewards (name, description, points_required) VALUES
('Eco-Friendly Water Bottle', 'Reusable stainless steel water bottle with bamboo cap, 750ml capacity', 500),
('Recycled Tote Bag', 'Large tote bag made from recycled materials, perfect for shopping', 300),
('Tree Planting Certificate', 'We\'ll plant a tree in your name and send you a certificate', 1000),
('₱100 GrabFood Voucher', 'Digital voucher for food delivery services', 800),
('Solar Power Bank', 'Portable 10000mAh solar-powered charging bank', 1500),
('Bamboo Utensil Set', 'Eco-friendly travel cutlery set with carrying case', 400),
('5% Recycling Bonus', 'Get 5% extra points on your next recycling transaction', 600),
('Metal Straw Set', 'Set of 4 stainless steel straws with cleaning brush', 200),
('Composting Starter Kit', 'Basic home composting kit with guide book', 1200),
('EcoLens Premium Status', 'Special badge and 2x points for 1 month', 2000),
('₱200 Mercury Drug Gift Card', 'Gift card for pharmacy purchases', 1600),
('Eco-Friendly Phone Case', 'Biodegradable phone case made from plant materials', 700),
('Local Farm Produce Box', 'Fresh vegetables from local sustainable farms', 900),
('Public Transport Card ₱150', 'Preloaded card for public transportation', 1000),
('Zero-Waste Starter Kit', 'Collection of basic zero-waste lifestyle products', 1800);

-- For redemptions
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

-- Sample redemptions
INSERT INTO tbl_redemptions (user_id, reward_id, points_used, created_at) VALUES
(2, 1, 500, '2025-02-17 10:30:00'), -- Water Bottle
(3, 2, 300, '2025-02-17 11:45:00'), -- Tote Bag
(4, 6, 400, '2025-02-16 14:20:00'), -- Utensil Set
(5, 8, 200, '2025-02-16 15:30:00'), -- Straw Set
(2, 4, 800, '2025-02-15 09:15:00'), -- GrabFood Voucher
(6, 2, 300, '2025-02-15 10:45:00'), -- Tote Bag
(3, 7, 600, '2025-02-14 13:20:00'), -- Recycling Bonus
(4, 8, 200, '2025-02-14 14:30:00'), -- Straw Set
(5, 3, 1000, '2025-02-13 16:45:00'), -- Tree Planting
(6, 6, 400, '2025-02-13 11:30:00'); -- Utensil Set

-- Create FAQ table
CREATE TABLE `tbl_faqs` (
  `id` double NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(50) DEFAULT 'General',
  `is_published` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample FAQs
INSERT INTO `tbl_faqs` (`question`, `answer`, `category`) VALUES
('What items can I recycle?', 'We accept plastic bottles, paper, cardboard, glass, and metal containers. All items should be clean and dry.', 'Recycling'),
('How do I earn points?', 'You earn points by recycling items at our partner centers. Different items have different point values.', 'Points'),
('Where are the recycling centers located?', 'We have partner centers across Metro Manila. Check our locations page for the nearest center.', 'Centers');
