-- Sample Data for Smart Recycling Platform
-- This file contains sample/test data for development purposes

-- Sample sortation centers
INSERT INTO `tbl_sortation_centers` (`id`, `name`, `address`, `description`, `categories`, `contact`, `rating`, `link`) VALUES
(1, 'Envirocycling Fiber Inc', 'Manchester Industrial Compound, Quezon City', 'Open from 7:00 AM to 6:00 PM every day.', 'plastic,paper', '(02) 8363-7121', '5', 'https://maps.google.com/?cid=2997478909304072391'),
(2, 'Green Haven Recycling', 'Sebastian St, Valenzuela, Metro Manila', 'Open from 8:00 AM to 4:00 PM except Sunday.', 'plastic,paper,metal', '(02) 8291-5432', '5', 'https://maps.google.com/?cid=3675111170217877456'),
(3, 'RPJ Recycling Center', 'Marton Road, Valenzuela, Metro Manila', 'Open from 9:00 AM to 6:00 PM except Sunday.', 'plastic,paper,metal', '(02) 8442-1234', '3', 'https://maps.google.com/?cid=13959482081240542526'),
(4, 'TPC Scrap Enterprises', 'Solar Street, Caloocan, Metro Manila', 'Open 24 hours every day except Sunday.', 'plastic,paper,metal,electronics', '(02) 8512-7890', '5', 'https://tpcscrapenterprises.wordpress.com/'),
(5, 'YLJ Plastics', 'Valenzuela, Metro Manila', 'Open from 9:00 AM to 5:00 PM Mon-Fri.', 'plastic', '(02) 8665-4321', '5', 'https://maps.google.com/?cid=17228057435575507911');

-- Sample users (admin, individuals, businesses, center representatives)
INSERT INTO `tbl_user` (`fullname`, `username`, `password`, `total_points`, `is_admin`, `user_type`, `business_name`, `address`, `contact_number`, `center_id`) VALUES
-- Admin user (already added in schema)
-- ('Administrator', 'admin@gmail.com', '12345', 0, 1, 'individual', NULL, NULL, NULL, NULL),
-- Individual users
('Emily Rodriguez', 'user1@gmail.com', '12345', 145, 0, 'individual', NULL, 'Makati City', '09123456789', NULL),
('Marcus Chen', 'user2@gmail.com', '12345', 188, 0, 'individual', NULL, 'Quezon City', '09187654321', NULL),
('Sofia Bennett', 'user3@gmail.com', '12345', 107, 0, 'individual', NULL, 'Pasig City', '09198765432', NULL),
-- Business users
('Nathan Walker', 'business1@gmail.com', '12345', 171, 0, 'business', 'GreenTech Solutions', '123 Eco Street, Makati City', '09151234567', NULL),
('Isabella Thompson', 'business2@gmail.com', '12345', 102, 0, 'business', 'EcoFriendly Corp', '456 Sustainability Ave, BGC', '09171234567', NULL),
-- Center users
('John Martinez', 'center1@gmail.com', '12345', 0, 0, 'center', 'Envirocycling Fiber Inc', NULL, '09191234567', 1),
('Sarah Lee', 'center2@gmail.com', '12345', 0, 0, 'center', 'Green Haven Recycling', NULL, '09181234567', 2);

-- Sample remits (recycling transactions)
INSERT INTO `tbl_remit` (`item_name`, `item_points`, `sortation_center_id`, `user_id`, `item_quantity`, `points`, `created_at`) VALUES
('Plastic Bottles', 5, 1, 2, 10, 50, NOW() - INTERVAL 7 DAY),
('Cardboard Boxes', 3, 3, 4, 8, 24, NOW() - INTERVAL 6 DAY),
('Aluminum Cans', 4, 2, 3, 15, 60, NOW() - INTERVAL 5 DAY),
('Glass Bottles', 6, 5, 5, 6, 36, NOW() - INTERVAL 5 DAY),
('Paper Waste', 2, 4, 2, 20, 40, NOW() - INTERVAL 4 DAY);

-- Sample rewards
INSERT INTO `tbl_rewards` (`name`, `description`, `points_required`) VALUES
('Eco-Friendly Water Bottle', 'Reusable stainless steel water bottle with bamboo cap, 750ml capacity', 500),
('Recycled Tote Bag', 'Large tote bag made from recycled materials, perfect for shopping', 300),
('Tree Planting Certificate', 'Well plant a tree in your name and send you a certificate', 1000),
('₱100 GrabFood Voucher', 'Digital voucher for food delivery services', 800),
('Solar Power Bank', 'Portable 10000mAh solar-powered charging bank', 1500);

-- Sample redemptions
INSERT INTO `tbl_redemptions` (`user_id`, `reward_id`, `points_used`, `created_at`) VALUES
(2, 2, 300, NOW() - INTERVAL 10 DAY),
(3, 1, 500, NOW() - INTERVAL 8 DAY),
(2, 4, 800, NOW() - INTERVAL 5 DAY);

-- Sample FAQs
INSERT INTO `tbl_faqs` (`question`, `answer`, `category`) VALUES
('What items can I recycle?', 'We accept plastic bottles, paper, cardboard, glass, and metal containers. All items should be clean and dry.', 'Recycling'),
('How do I earn points?', 'You earn points by recycling items at our partner centers. Different items have different point values.', 'Points'),
('Where are the recycling centers located?', 'We have partner centers across Metro Manila. Check our locations page for the nearest center.', 'Centers');

-- Sample pickups
INSERT INTO `tbl_pickups` (`user_id`, `pickup_date`, `pickup_time`, `address`, `items`, `recurring`, `frequency`, `status`, `current_status`, `estimated_completion`)
VALUES
(2, CURDATE() + INTERVAL 2 DAY, 'morning', 'Makati City, Philippines', 'Plastic bottles, cardboard boxes', 0, 'one-time', 'confirmed', 'scheduled', NOW() + INTERVAL 2 DAY),
(3, CURDATE() + INTERVAL 3 DAY, 'afternoon', 'Quezon City, Philippines', 'Electronic waste, batteries', 0, 'one-time', 'confirmed', 'scheduled', NOW() + INTERVAL 3 DAY),
(4, CURDATE() - INTERVAL 1 DAY, 'morning', 'Makati City, Philippines', 'Metal scraps, aluminum cans', 0, 'one-time', 'completed', 'completed', NOW() - INTERVAL 1 DAY);

-- Sample bulk requests from businesses
INSERT INTO `tbl_bulk_requests` (`business_id`, `request_type`, `material_types`, `estimated_quantity`, `preferred_date`, `address`, `additional_notes`, `status`)
VALUES
(4, 'pickup', 'plastic,paper', 500, CURDATE() + INTERVAL 5 DAY, 'Makati City, Philippines', 'Office cleanup - mostly plastic bottles and paper documents', 'pending'),
(5, 'drop-off', 'metal,electronics', 250, CURDATE() + INTERVAL 7 DAY, 'BGC, Taguig, Philippines', 'Old office equipment and fixtures', 'approved');

-- Sample quotes for bulk requests
INSERT INTO `tbl_quotes` (`request_id`, `center_id`, `price`, `estimated_points`, `notes`, `expiration_date`, `status`)
VALUES
(1, 1, 2500.00, 1200, 'We can process all materials in one pickup', CURDATE() + INTERVAL 10 DAY, 'pending'),
(1, 3, 2200.00, 1000, 'Two separate pickups may be required due to volume', CURDATE() + INTERVAL 9 DAY, 'pending'),
(2, 2, 1800.00, 800, 'Please drop off during business hours 9am-4pm', CURDATE() + INTERVAL 12 DAY, 'accepted');

-- Sample inventory data
INSERT INTO `tbl_inventory` (`center_id`, `material_type`, `quantity`, `capacity`, `last_updated`)
VALUES
(1, 'plastic', 1200.50, 5000.00, NOW()),
(1, 'paper', 800.75, 3000.00, NOW()),
(2, 'plastic', 2500.00, 6000.00, NOW()),
(2, 'metal', 1500.25, 4000.00, NOW()),
(3, 'paper', 1800.00, 4000.00, NOW());

-- Sample pickup notifications
INSERT INTO `tbl_pickup_notifications` (`pickup_id`, `message`, `notification_type`, `is_read`)
VALUES
(1, 'Your pickup has been scheduled for tomorrow morning', 'reminder', 0),
(2, 'Driver is en route to your location', 'status_update', 1),
(3, 'Pickup completed successfully', 'status_update', 1);
