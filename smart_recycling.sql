-- Additional requests for EcoFriendly Corp (business_id: 6)
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
  `user_type` enum('individual', 'business') NOT NULL DEFAULT 'individual',
  `business_name` text DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Sample data with both individual and business users
INSERT INTO `tbl_user` (`id`, `fullname`, `username`, `password`, `total_points`, `is_admin`, `user_type`, `business_name`, `address`, `contact_number`) VALUES
-- Admin user
(1, 'Alexander Morgan', 'admin@gmail.com', '12345', 0, 1, 'individual', NULL, NULL, NULL),
-- Individual users
(2, 'Emily Rodriguez', 'user1@gmail.com', '12345', 145, 0, 'individual', NULL, NULL, NULL),
(3, 'Marcus Chen', 'user2@gmail.com', '12345', 188, 0, 'individual', NULL, NULL, NULL),
(4, 'Sofia Bennett', 'user3@gmail.com', '12345', 107, 0, 'individual', NULL, NULL, NULL),
-- Business users
(5, 'Nathan Walker', 'greentech@gmail.com', '12345', 171, 0, 'business', 'GreenTech Solutions', '123 Eco Street, Makati City', '+63-915-555-0101'),
(6, 'Isabella Thompson', 'ecofriendly@gmail.com', '12345', 102, 0, 'business', 'EcoFriendly Corp', '456 Sustainability Ave, BGC', '+63-917-555-0202'),
(7, 'James Wilson', 'cleanearth@gmail.com', '12345', 250, 0, 'business', 'Clean Earth Industries', '789 Green Building, Ortigas', '+63-918-555-0303'),
(8, 'Maria Santos', 'zerowaste@gmail.com', '12345', 180, 0, 'business', 'Zero Waste Solutions', '321 Recycling Road, Pasig', '+63-919-555-0404');


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

-- Sample pickup requests with enhanced data
INSERT INTO tbl_pickups (
    user_id, 
    pickup_date, 
    pickup_time, 
    address, 
    items, 
    recurring, 
    frequency, 
    status, 
    current_status, 
    estimated_completion, 
    actual_completion, 
    capacity_confirmed, 
    vehicle_assigned, 
    driver_notes
) VALUES
(2, '2025-02-20', 'morning', '123 Green St, Manila', 'Plastic bottles, cardboard boxes', 1, 'weekly', 'confirmed', 'in_transit', '2025-02-20 12:00:00', NULL, 1, 'Truck-A123', 'Regular pickup location, accessible via main entrance'),
(3, '2025-02-21', 'afternoon', '456 Eco Ave, Quezon City', 'Electronic waste, batteries', 0, 'one-time', 'confirmed', 'scheduled', '2025-02-21 16:00:00', NULL, 1, 'Truck-B456', 'Special handling required for batteries'),
(4, '2025-02-22', 'morning', '789 Recycling Rd, Makati', 'Metal scraps, aluminum cans', 0, 'one-time', 'completed', 'completed', '2025-02-22 11:00:00', '2025-02-22 10:45:00', 1, 'Truck-C789', 'Successfully collected, customer very cooperative'),
(5, '2025-02-23', 'evening', '321 Earth Blvd, Pasig', 'Glass bottles, newspapers', 1, 'monthly', 'confirmed', 'arrived', '2025-02-23 19:00:00', NULL, 1, 'Truck-D012', 'Loading area at back of building'),
(6, '2025-02-24', 'afternoon', '654 Nature St, Taguig', 'Paper waste, plastic containers', 0, 'one-time', 'cancelled', 'cancelled', NULL, NULL, 0, NULL, 'Cancelled by customer - rescheduling needed'),
(2, '2025-02-25', 'morning', '123 Green St, Manila', 'Plastic bottles', 1, 'weekly', 'pending', 'scheduled', '2025-02-25 11:00:00', NULL, 1, 'Truck-E345', 'Weekly recurring pickup - regular customer'),
(3, '2025-02-26', 'evening', '456 Eco Ave, Quezon City', 'Metal waste', 0, 'one-time', 'confirmed', 'scheduled', '2025-02-26 18:00:00', NULL, 1, 'Truck-F678', 'Large volume - may need additional capacity'),
(4, '2025-02-27', 'afternoon', '789 Recycling Rd, Makati', 'Paper waste', 0, 'one-time', 'pending', 'scheduled', '2025-02-27 15:00:00', NULL, 0, NULL, 'Awaiting capacity confirmation'),
(5, '2025-02-28', 'morning', '321 Earth Blvd, Pasig', 'Glass materials', 1, 'monthly', 'confirmed', 'scheduled', '2025-02-28 10:00:00', NULL, 1, 'Truck-G901', 'Handle glass materials with care'),
(6, '2025-03-01', 'evening', '654 Nature St, Taguig', 'Electronic waste', 0, 'one-time', 'pending', 'scheduled', '2025-03-01 19:00:00', NULL, 0, NULL, 'Requires special handling equipment');

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

-- For business bulk recycling requests
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

-- Sample data for tbl_bulk_requests
INSERT INTO `tbl_bulk_requests` (`business_id`, `request_type`, `material_types`, `estimated_quantity`, `preferred_date`, `address`, `additional_notes`, `status`, `created_at`) VALUES
(2, 'pickup', 'plastic,paper', 500, '2025-03-10', '123 Green St, Manila', 'Office cleanup - mostly plastic bottles and paper documents', 'pending', '2025-02-20 09:30:00'),
(3, 'drop-off', 'metal,electronics', 250, '2025-03-12', '456 Eco Ave, Quezon City', 'Old office equipment and fixtures', 'approved', '2025-02-20 11:45:00'),
(4, 'pickup', 'plastic,glass,metal', 750, '2025-03-15', '789 Recycling Rd, Makati', 'Post-event cleanup materials', 'pending', '2025-02-21 14:20:00'),
(5, 'pickup', 'paper,plastic', 1000, '2025-03-20', '321 Earth Blvd, Pasig', 'Monthly document shredding and plastics disposal', 'completed', '2025-02-15 10:30:00'),
(6, 'drop-off', 'electronics', 100, '2025-03-05', '654 Nature St, Taguig', 'Upgrading all computer peripherals', 'rejected', '2025-02-18 16:45:00'),
(2, 'pickup', 'plastic,metal', 650, '2025-03-25', '123 Green St, Manila', 'Warehouse clearance', 'approved', '2025-02-22 13:15:00');

-- For service quotes
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

-- Sample data for tbl_quotes
INSERT INTO `tbl_quotes` (`request_id`, `center_id`, `price`, `estimated_points`, `notes`, `expiration_date`, `status`, `created_at`) VALUES
(1, 1, 2500.00, 1200, 'We can process all materials in one pickup', '2025-03-05', 'pending', '2025-02-21 10:15:00'),
(1, 3, 2200.00, 1000, 'Two separate pickups may be required due to volume', '2025-03-04', 'pending', '2025-02-21 11:30:00'),
(2, 6, 1800.00, 800, 'Please drop off during business hours 9am-4pm', '2025-03-08', 'accepted', '2025-02-22 09:45:00'),
(3, 4, 3500.00, 2000, 'Can provide special containers for glass disposal', '2025-03-10', 'pending', '2025-02-22 14:30:00'),
(3, 7, 3200.00, 1800, 'Free sorting services included', '2025-03-09', 'rejected', '2025-02-22 15:45:00'),
(4, 10, 4200.00, 2500, 'Completed - materials processed successfully', '2025-03-01', 'completed', '2025-02-16 09:20:00'),
(5, 6, 1200.00, 500, 'Unable to process these materials currently', '2025-02-28', 'rejected', '2025-02-19 10:30:00'),
(6, 2, 3000.00, 1700, 'We can offer a premium rate for these materials', '2025-03-15', 'accepted', '2025-02-23 11:15:00');

-- For inventory tracking
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

-- For processing status tracking
CREATE TABLE `tbl_processing` (
  `id` double NOT NULL AUTO_INCREMENT,
  `center_id` double NOT NULL,
  `batch_id` varchar(20) NOT NULL,
  `material_type` varchar(50) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') NOT NULL DEFAULT 'pending',
  `start_date` timestamp NULL DEFAULT NULL,
  `completion_date` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`center_id`) REFERENCES `tbl_sortation_centers`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- For pickup notifications
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

-- Sample data for tbl_inventory
INSERT INTO `tbl_inventory` (`center_id`, `material_type`, `quantity`, `capacity`, `last_updated`) VALUES
(1, 'plastic', 1200.50, 5000.00, '2025-02-25 09:00:00'),
(1, 'paper', 800.75, 3000.00, '2025-02-25 09:00:00'),
(2, 'plastic', 2500.00, 6000.00, '2025-02-25 10:30:00'),
(2, 'metal', 1500.25, 4000.00, '2025-02-25 10:30:00'),
(3, 'paper', 1800.00, 4000.00, '2025-02-25 11:45:00'),
(3, 'metal', 900.50, 2000.00, '2025-02-25 11:45:00'),
(4, 'electronics', 500.00, 1000.00, '2025-02-25 14:20:00'),
(5, 'plastic', 3500.75, 8000.00, '2025-02-25 15:30:00'),
(6, 'electronics', 750.25, 2000.00, '2025-02-25 16:45:00'),
(6, 'metal', 1200.00, 3000.00, '2025-02-25 16:45:00');

-- Sample data for tbl_processing
INSERT INTO `tbl_processing` (`center_id`, `batch_id`, `material_type`, `quantity`, `status`, `start_date`, `completion_date`, `notes`) VALUES
(1, 'BATCH-2025-001', 'plastic', 500.00, 'completed', '2025-02-24 09:00:00', '2025-02-24 15:00:00', 'Regular processing completed'),
(1, 'BATCH-2025-002', 'paper', 300.00, 'processing', '2025-02-25 10:00:00', NULL, 'Sorting in progress'),
(2, 'BATCH-2025-003', 'metal', 250.00, 'pending', NULL, NULL, 'Scheduled for tomorrow'),
(3, 'BATCH-2025-004', 'plastic', 800.00, 'processing', '2025-02-25 08:00:00', NULL, 'Large batch processing'),
(4, 'BATCH-2025-005', 'electronics', 150.00, 'completed', '2025-02-23 11:00:00', '2025-02-24 16:00:00', 'Special handling completed'),
(5, 'BATCH-2025-006', 'plastic', 1000.00, 'pending', NULL, NULL, 'Awaiting equipment maintenance'),
(6, 'BATCH-2025-007', 'metal', 400.00, 'processing', '2025-02-25 13:00:00', NULL, 'Processing on schedule');

-- Sample data for tbl_pickup_notifications
INSERT INTO `tbl_pickup_notifications` (`pickup_id`, `message`, `notification_type`, `is_read`, `created_at`) VALUES
(1, 'Your pickup has been scheduled for tomorrow morning', 'reminder', 0, '2025-02-25 08:00:00'),
(2, 'Driver is en route to your location', 'status_update', 1, '2025-02-25 09:30:00'),
(3, 'Center capacity is currently at 90%. Minor delays possible', 'capacity_alert', 0, '2025-02-25 10:15:00'),
(4, 'Pickup completed successfully', 'status_update', 1, '2025-02-25 11:45:00'),
(5, 'Reminder: Scheduled pickup tomorrow at 2 PM', 'reminder', 0, '2025-02-25 13:00:00'),
(1, 'Vehicle assigned: Truck-A123', 'status_update', 0, '2025-02-25 14:30:00'),
(2, 'Driver has arrived at your location', 'status_update', 0, '2025-02-25 15:45:00'),
(6, 'Processing center capacity update: Ready for pickup', 'capacity_alert', 1, '2025-02-25 16:20:00'),
(7, 'Your pickup request has been confirmed', 'status_update', 0, '2025-02-25 17:00:00'),
(8, 'Reminder: Please ensure materials are properly sorted', 'reminder', 0, '2025-02-25 17:30:00');

-- Update the user_type enum to include 'center'
ALTER TABLE `tbl_user` 
MODIFY COLUMN `user_type` enum('individual', 'business', 'center') NOT NULL DEFAULT 'individual';

-- Add center_id column for center users
ALTER TABLE `tbl_user` 
ADD COLUMN `center_id` double DEFAULT NULL AFTER `contact_number`,
ADD FOREIGN KEY (`center_id`) REFERENCES `tbl_sortation_centers`(`id`);

-- Add sample data for center users
INSERT INTO `tbl_user` (`fullname`, `username`, `password`, `total_points`, `is_admin`, `user_type`, `business_name`, `address`, `contact_number`, `center_id`) VALUES
-- Center users
('John Martinez', 'envirocycling@gmail.com', '12345', 0, 0, 'center', 'Envirocycling Fiber Inc', NULL, '(02) 8363-7121', 1),
('Sarah Lee', 'greenhaven@gmail.com', '12345', 0, 0, 'center', 'Green Haven Scrap Materials', NULL, '(02) 8291-5432', 2),
('Michael Garcia', 'rpj.center@gmail.com', '12345', 0, 0, 'center', 'RPJ - Valenzuela', NULL, '(02) 8442-1234', 3),
('Patricia Cruz', 'tpc.scrap@gmail.com', '12345', 0, 0, 'center', 'TPC Scrap Enterprises', NULL, '(02) 8512-7890', 4),
('Robert Tan', 'ylj.plastics@gmail.com', '12345', 0, 0, 'center', 'YLJ Plastics', NULL, '(02) 8665-4321', 5),
('Lisa Santos', 'fym.trading@gmail.com', '12345', 0, 0, 'center', 'FYM Scrap Trading', NULL, '(02) 8123-4567', 6),
('David Lim', 'jepoy.junk@gmail.com', '12345', 0, 0, 'center', 'Jepoy Junk Shop', NULL, '(02) 8234-5678', 7),
('Carmen Reyes', 'malate.junk@gmail.com', '12345', 0, 0, 'center', 'Malate Junkshop', NULL, '(02) 8345-6789', 8),
('Ricardo Flores', 'rnp.junk@gmail.com', '12345', 0, 0, 'center', 'RNP Junkshop', NULL, '(02) 8456-7890', 9),
('Maria Puring', 'puring.junk@gmail.com', '12345', 0, 0, 'center', 'Puring Junkshop', NULL, '(02) 8567-8901', 10);