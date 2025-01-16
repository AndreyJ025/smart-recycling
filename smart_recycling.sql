-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2024 at 10:19 AM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 8.1.12

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
CREATE DATABASE IF NOT EXISTS `smart_recycling` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `smart_recycling`;

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
  `item_quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_remit`
--

INSERT INTO `tbl_remit` (`id`, `item_name`, `item_points`, `sortation_center_id`, `user_id`, `item_quantity`) VALUES
(1, 'plastic bottles', 6, 1, 1, 3),
(2, 'plastic keyboard', 9, 2, 1, 5);

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
(1, 'Envirocycling Fiber Inc (Sauyo)', 'B1 L12 MANCHESTER INDUSTRIAL COMPOUND 2, Quezon City, 1116 Metro Manila, Philippines', 'It is open from 7:00 AM to 6:00 PM every day', 'plastic,papers', '5', 'https://maps.google.com/?cid=2997478909304072391'),
(2, 'Green Haven Scrap Materials Trading', 'P2C2+FH5, Sebastian St, Valenzuela, Metro Manila, Philippines', 'It is open from 8:00 AM to 4:00 PM every day except Sunday.', 'plastic,papers,burn', '5', 'https://maps.google.com/?cid=3675111170217877456'),
(3, 'RPJ - Valenzuela', 'CNWB Compd.,, 20-A 1447, Marton Road, Valenzuela, Metro Manila, Philippines', 'It is open from 9:00 AM to 6:00 PM every day except Sunday.', 'plastic,papers,burn', '5', 'https://maps.google.com/?cid=13959482081240542526'),
(4, '\"PAPER AND METAL SCRAP BUYER\" - TPC* SCRAP ENTERPRISES', 'Solar Urban Homes North, Solar Street Block 5, Lot 8, Phase 3, Caloocan, 1421 Metro Manila, Philippines', 'It is open 24 hours every day except Sunday, when it is open from 10:00 AM to 5:00 PM.', 'plastic,papers,burn', '5', 'https://tpcscrapenterprises.wordpress.com/'),
(5, 'YLJ Plastics - PET Bottle Scrap Buyer', 'PXHV+34Q, Valenzuela, Metro Manila, Philippines', 'It is open from 9:00 AM to 5:00 PM every day except Saturday and Sunday.', 'plastic,papers,burn', '5', 'https://maps.google.com/?cid=17228057435575507911');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `id` double NOT NULL,
  `fullname` text NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`id`, `fullname`, `username`, `password`) VALUES
(1, 'juan carlos', 'juan@gmail.com', '12345'),
(2, '', 'mike@gmail.com', '12345');

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
  MODIFY `id` double NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_sortation_centers`
--
ALTER TABLE `tbl_sortation_centers`
  MODIFY `id` double NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `id` double NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
