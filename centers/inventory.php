<?php
session_start();
require_once '../database.php';

// Check if user is logged in and has center access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'center') {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

$center_id = $_SESSION['center_id'];
$success_message = "";
$error_message = "";

// Handle inventory updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_inventory'])) {
        $material_type = $_POST['material_type'];
        $quantity = $_POST['quantity'];
        $capacity = $_POST['capacity'];
        
        $stmt = $conn->prepare("INSERT INTO tbl_inventory (center_id, material_type, quantity, capacity) 
                               VALUES (?, ?, ?, ?) 
                               ON DUPLICATE KEY UPDATE quantity = ?, capacity = ?");
        $stmt->bind_param("isdddd", $center_id, $material_type, $quantity, $capacity, $quantity, $capacity);
        
        if ($stmt->execute()) {
            $success_message = "Inventory updated successfully";
        } else {
            $error_message = "Error updating inventory";
        }
    }
}

// Get current inventory
$inventory_query = "SELECT * FROM tbl_inventory WHERE center_id = ?";
$stmt = $conn->prepare($inventory_query);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$inventory = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management - EcoLens</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .bg-overlay {
            background: url('../assets/background.jpg');
            min-height: 100vh;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            position: relative;
        }
        .bg-overlay::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body class="font-[Poppins]">

    <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <img src="../assets/logo.png" alt="EcoLens Logo" class="h-8">
                    <span class="text-xl font-bold text-white">EcoLens</span>
                </div>
                <a href="dashboard.php" class="text-white hover:text-[#436d2e] transition-all">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="bg-overlay">
        <div class="relative min-h-screen pt-20 pb-12">
            <div class="container mx-auto px-4">
                <div class="max-w-4xl mx-auto">
                    <h1 class="text-3xl font-bold text-white mb-8">Inventory Management</h1>

                    <!-- Inventory Status Cards -->
                    <div class="grid md:grid-cols-3 gap-6 mb-8">
                        <?php while($item = $inventory->fetch_assoc()): ?>
                        <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                            <h3 class="text-lg font-semibold text-white mb-2"><?php echo htmlspecialchars($item['material_type']); ?></h3>
                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-white/70">Current</span>
                                    <span class="text-white font-medium"><?php echo number_format($item['quantity'], 2); ?> kg</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-white/70">Capacity</span>
                                    <span class="text-white font-medium"><?php echo number_format($item['capacity'], 2); ?> kg</span>
                                </div>
                                <div class="w-full bg-white/10 rounded-full h-2 mt-2">
                                    <div class="bg-[#436d2e] h-2 rounded-full" 
                                         style="width: <?php echo min(($item['quantity'] / $item['capacity']) * 100, 100); ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Update Inventory Form -->
                    <div class="bg-white/5 backdrop-blur-sm p-8 rounded-xl">
                        <h2 class="text-2xl font-bold text-white mb-6">Update Inventory</h2>
                        <form method="POST" class="space-y-6">
                            <div class="grid md:grid-cols-3 gap-6">
                                <div class="relative">
                                    <select name="material_type" required
                                            class="w-full px-4 py-3 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                                        <option value="" class="text-black">Select Material</option>
                                        <option value="plastic" class="text-black">Plastic</option>
                                        <option value="paper" class="text-black">Paper</option>
                                        <option value="metal" class="text-black">Metal</option>
                                        <option value="glass" class="text-black">Glass</option>
                                        <option value="electronics" class="text-black">Electronics</option>
                                    </select>
                                </div>
                                <div class="relative">
                                    <input type="number" name="quantity" step="0.01" required
                                           placeholder="Current Quantity (kg)"
                                           class="w-full px-4 py-3 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                                </div>
                                <div class="relative">
                                    <input type="number" name="capacity" step="0.01" required
                                           placeholder="Total Capacity (kg)"
                                           class="w-full px-4 py-3 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                                </div>
                            </div>
                            <button type="submit" name="update_inventory"
                                    class="w-full bg-[#436d2e] text-white px-6 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-all">
                                Update Inventory
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>