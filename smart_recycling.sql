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
  `id` double NOT NULL,
  `item_name` text NOT NULL,
  `item_points` double DEFAULT NULL,
  `sortation_center_id` double NOT NULL,
  `user_id` double NOT NULL,
  `item_quantity` int(11) NOT NULL,
  `points` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_remit`
--

INSERT INTO `tbl_remit` (`id`, `item_name`, `item_points`, `sortation_center_id`, `user_id`, `item_quantity`, `points`, `created_at`) VALUES
(1, 'Plastic Bottles', 5, 1, 2, 10, 0, '2024-03-15 14:30:00'),
(2, 'Cardboard Boxes', 3, 3, 4, 8, 0, '2024-03-15 15:45:00'),
(3, 'Aluminum Cans', 4, 2, 3, 15, 0, '2024-03-14 11:20:00'),
(4, 'Glass Bottles', 6, 5, 5, 6, 0, '2024-03-14 13:15:00'),
(5, 'Paper Waste', 2, 4, 6, 20, 0, '2024-03-13 16:00:00'),
(6, 'Electronic Waste', 10, 6, 2, 2, 0, '2024-03-13 09:45:00'),
(7, 'Metal Scraps', 7, 7, 4, 5, 0, '2024-03-12 14:20:00'),
(8, 'Plastic Containers', 4, 1, 3, 12, 0, '2024-03-12 10:30:00'),
(9, 'Old Newspapers', 3, 3, 5, 25, 0, '2024-03-11 15:50:00'),
(10, 'Used Batteries', 8, 6, 6, 4, 0, '2024-03-11 11:25:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_sortation_centers`
--

CREATE TABLE `tbl_sortation_centers` (
  `id` double NOT NULL,
  `name` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `materials` text DEFAULT NULL,
  `rating` text DEFAULT NULL,
  `link` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_sortation_centers`
--

INSERT INTO `tbl_sortation_centers` (`id`, `name`, `address`, `description`, `materials`, `rating`, `link`) VALUES
(1, 'Envirocycling Fiber Inc (Sauyo)', 'B1 L12 Manchester Industrial Compound 2, Quezon City, 1116 Metro Manila, Philippines', 'Open from 7:00 AM to 6:00 PM every day.', 'plastic, paper', 5, 'https://maps.google.com/?cid=2997478909304072391'),
(2, 'Green Haven Scrap Materials Trading', 'P2C2+FH5, Sebastian St, Valenzuela, Metro Manila, Philippines', 'Open from 8:00 AM to 4:00 PM every day except Sunday.', 'plastic, paper, metal', 5, 'https://maps.google.com/?cid=3675111170217877456'),
(3, 'RPJ - Valenzuela', 'CNWB Compound, 20-A 1447, Marton Road, Valenzuela, Metro Manila, Philippines', 'Open from 9:00 AM to 6:00 PM every day except Sunday.', 'plastic, paper, metal', 3, 'https://maps.google.com/?cid=13959482081240542526'),
(4, 'TPC Scrap Enterprises', 'Solar Urban Homes North, Solar Street Block 5, Lot 8, Phase 3, Caloocan, 1421 Metro Manila, Philippines', 'Open 24 hours every day except Sunday, when it is open from 10:00 AM to 5:00 PM.', 'plastic, paper, metal', 5, 'https://tpcscrapenterprises.wordpress.com/'),
(5, 'YLJ Plastics - PET Bottle Scrap Buyer', 'PXHV+34Q, Valenzuela, Metro Manila, Philippines', 'Open from 9:00 AM to 5:00 PM every day except Saturday and Sunday.', 'plastic', 5, 'https://maps.google.com/?cid=17228057435575507911'),
(6, 'FYM Scrap Trading', '202 Visayas Ave Extension, Novaliches, Quezon City, 1107 Metro Manila, Philippines', 'Open from 8:00 AM to 5:00 PM Monday to Saturday.', 'metal, paper, plastic', 5, 'https://maps.google.com/?cid=12345678901234567890'),
(7, 'Jepoy Junk Shop', 'M2RP+WFG, Don Julio Gregorio, Novaliches, Quezon City, Metro Manila, Philippines', 'Open from 8:00 AM to 5:00 PM Monday to Saturday.', 'metal, paper, plastic', 4, 'https://maps.google.com/?cid=12345678901234567891'),
(8, 'Malate Junkshop', '9 Gregorio Araneta Ave, Sto Domingo, Quezon City, 1114 Metro Manila, Philippines', 'Open from 8:00 AM to 5:00 PM Monday to Saturday.', 'metal, paper, plastic', 4, 'https://maps.google.com/?cid=12345678901234567892'),
(9, 'RNP Junkshop', '144 Ilocos Sur, Bago Bantay, Quezon City, 1105 Metro Manila, Philippines', 'Open from 8:00 AM to 5:00 PM Monday to Saturday.', 'metal, paper, plastic', 3, 'https://maps.google.com/?cid=12345678901234567893'),
(10, 'Puring Junkshop', '105 Kamias Rd, Diliman, Quezon City, 1101 Metro Manila, Philippines', 'Open from 8:00 AM to 5:00 PM Monday to Saturday.', 'metal, paper, plastic', 4, 'https://maps.google.com/?cid=12345678901234567894');


-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id` double NOT NULL,
  `fullname` text NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `total_points` int(11) DEFAULT 0,
  `is_admin` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id`, `fullname`, `username`, `password`, `total_points`, `is_admin`) VALUES
(1, 'Alexander Morgan', 'admin@gmail.com', '12345', 0, 1),
(2, 'Emily Rodriguez', 'user1@gmail.com', '12345', 0, 0),
(3, 'Marcus Chen', 'user2@gmail.com', '12345', 0, 0),
(4, 'Sofia Bennett', 'user3@gmail.com', '12345', 0, 0),
(5, 'Nathan Walker', 'user4@gmail.com', '12345', 0, 0),
(6, 'Isabella Thompson', 'user5@gmail.com', '12345', 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_remit`
--
ALTER TABLE `tbl_remit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_sortation_centers`
--
ALTER TABLE `tbl_sortation_centers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_remit`
--
ALTER TABLE `tbl_remit`
  MODIFY `id` double NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_sortation_centers`
--
ALTER TABLE `tbl_sortation_centers`
  MODIFY `id` double NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id` double NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
