<?php
session_start();

// Check if user is logged in and is a center
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'center') {
    header("Location: ../auth/login.php");
    exit();
}

include '../database.php';

// Fix for missing center_id in session
if (!isset($_SESSION['center_id'])) {
    // Get center_id from the user record
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT center_id FROM tbl_user WHERE id = ? AND user_type = 'center'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['center_id'] = $row['center_id'];
    } else {
        // Fallback if no center_id is found
        header("Location: ../auth/login.php?error=invalid_center");
        exit();
    }
}

$center_id = $_SESSION['center_id'];

// Get center details
$stmt = $conn->prepare("SELECT * FROM tbl_sortation_centers WHERE id = ?");
$stmt->bind_param("i", $center_id);
$stmt->execute();
$center = $stmt->get_result()->fetch_assoc();

// Get inventory statistics
$inventory_query = "SELECT 
                      SUM(quantity) as total_quantity, 
                      SUM(capacity) as total_capacity,
                      COUNT(*) as material_types 
                    FROM tbl_inventory 
                    WHERE center_id = ?";
$stmt = $conn->prepare($inventory_query);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$inventory_stats = $stmt->get_result()->fetch_assoc();

// Get pending pickups
$pickup_query = "SELECT COUNT(*) as pending_pickups
                FROM tbl_pickups 
                WHERE status = 'confirmed' 
                AND (current_status = 'scheduled' OR current_status = 'in_transit')
                AND DATE(pickup_date) >= CURDATE()";
$stmt = $conn->prepare($pickup_query);
$stmt->execute();
$pickup_stats = $stmt->get_result()->fetch_assoc();

// Get pending bulk requests
$bulk_query = "SELECT COUNT(*) as pending_requests
              FROM tbl_bulk_requests 
              WHERE status = 'pending'";
$stmt = $conn->prepare($bulk_query);
$stmt->execute();
$bulk_stats = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Center Dashboard - EcoLens</title>
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
    <!-- Updated Navigation Bar to match Business Dashboard -->
    <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <img src="../assets/logo.png" alt="EcoLens Logo" class="h-8">
                    <span class="text-xl font-bold text-white">EcoLens</span>
                </div>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="dashboard.php" class="text-white hover:text-[#436d2e] transition-all font-medium">
                        <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                    </a>
                    <a href="profile.php" class="text-white hover:text-[#436d2e] transition-all">
                        <i class="fas fa-user mr-1"></i> Profile
                    </a>
                    <a href="../index.php" class="text-white hover:text-[#436d2e] transition-all">
                        <i class="fas fa-sign-out-alt mr-1"></i> Logout
                    </a>
                </div>
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="text-white hover:text-[#436d2e] transition-all">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            <!-- Mobile menu (hidden by default) -->
            <div id="mobile-menu" class="hidden pt-4 pb-2">
                <a href="dashboard.php" class="block py-2 text-white hover:text-[#436d2e]">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>
                <a href="profile.php" class="block py-2 text-white hover:text-[#436d2e]">
                    <i class="fas fa-user mr-2"></i> Profile
                </a>
                <a href="../index.php" class="block py-2 text-white hover:text-[#436d2e]">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="bg-overlay">
        <div class="relative min-h-screen pt-20 pb-12">
            <div class="container mx-auto px-4">
                <div class="max-w-6xl mx-auto">
                    <h1 class="text-3xl font-bold text-white mb-8">Recycling Center Dashboard</h1>
                    
                    <!-- Statistics Cards - Adjusted to fill space better with 3 cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                            <h3 class="text-lg text-white/80 mb-1">Inventory Usage</h3>
                            <p class="text-2xl font-bold text-white">
                                <?php 
                                $usage_percent = $inventory_stats['total_capacity'] > 0 
                                    ? round(($inventory_stats['total_quantity'] / $inventory_stats['total_capacity']) * 100) 
                                    : 0;
                                echo $usage_percent . '%'; 
                                ?>
                            </p>
                            <div class="w-full bg-white/10 rounded-full h-2 mt-2">
                                <div class="bg-[#436d2e] h-2 rounded-full" 
                                     style="width: <?php echo $usage_percent; ?>%"></div>
                            </div>
                        </div>
                        
                        <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                            <h3 class="text-lg text-white/80 mb-1">Material Types</h3>
                            <p class="text-2xl font-bold text-white">
                                <?php echo $inventory_stats['material_types'] ?? 0; ?>
                            </p>
                        </div>
                        
                        <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                            <h3 class="text-lg text-white/80 mb-1">Pending Pickups</h3>
                            <p class="text-2xl font-bold text-white">
                                <?php echo $pickup_stats['pending_pickups'] ?? 0; ?>
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Quick Actions - Adjusted for better layout with 3 items -->
                        <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                            <h2 class="text-2xl font-bold text-white mb-6">Management Center</h2>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <a href="inventory.php" class="flex items-center gap-3 p-4 bg-white/10 rounded-xl text-white hover:bg-[#436d2e]/50 transition-all">
                                    <i class="fas fa-box-open text-xl w-8"></i>
                                    <div>
                                        <h3 class="font-semibold">Inventory</h3>
                                        <p class="text-sm text-white/70">Track materials</p>
                                    </div>
                                </a>
                                
                                <a href="pickup_management.php" class="flex items-center gap-3 p-4 bg-white/10 rounded-xl text-white hover:bg-[#436d2e]/50 transition-all">
                                    <i class="fas fa-truck text-xl w-8"></i>
                                    <div>
                                        <h3 class="font-semibold">Pickups</h3>
                                        <p class="text-sm text-white/70">Manage requests</p>
                                    </div>
                                </a>
                                
                                <a href="reports.php" class="flex items-center gap-3 p-4 bg-white/10 rounded-xl text-white hover:bg-[#436d2e]/50 transition-all">
                                    <i class="fas fa-chart-bar text-xl w-8"></i>
                                    <div>
                                        <h3 class="font-semibold">Reports</h3>
                                        <p class="text-sm text-white/70">View analytics</p>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <!-- Center Status -->
                        <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                            <h2 class="text-2xl font-bold text-white mb-6">Center Status</h2>
                            <div class="space-y-5">
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-building w-6 text-white/80"></i>
                                    <span class="text-white"><?php echo htmlspecialchars($center['name']); ?></span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-map-marker-alt w-6 text-white/80"></i>
                                    <span class="text-white"><?php echo htmlspecialchars($center['address']); ?></span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-star w-6 text-white/80"></i>
                                    <span class="text-white"><?php echo number_format($center['rating'], 1); ?> / 5</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <i class="fas fa-tags w-6 text-white/80"></i>
                                    <span class="text-white">
                                        <?php
                                        $categories = explode(',', $center['categories']);
                                        foreach($categories as $category) {
                                            echo '<span class="inline-block bg-white/20 text-white text-xs px-2 py-1 rounded-full mr-1">' . ucfirst($category) . '</span>';
                                        }
                                        ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alerts and Notifications -->
                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                        <h2 class="text-2xl font-bold text-white mb-6">Alerts & Notifications</h2>
                        
                        <?php if ($bulk_stats['pending_requests'] > 0): ?>
                        <div class="flex items-center justify-between p-4 bg-yellow-600/20 text-yellow-200 rounded-lg mb-4">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-bell"></i>
                                <span><?php echo $bulk_stats['pending_requests']; ?> pending bulk recycling requests need your attention</span>
                            </div>
                            <a href="pickup_management.php?filter=bulk" class="text-yellow-200 hover:text-white transition-all">
                                Review Now
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (($inventory_stats['total_quantity'] / $inventory_stats['total_capacity'] * 100) > 80): ?>
                        <div class="flex items-center justify-between p-4 bg-red-600/20 text-red-200 rounded-lg">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-exclamation-triangle"></i>
                                <span>Storage capacity reaching critical levels (<?php echo round($inventory_stats['total_quantity'] / $inventory_stats['total_capacity'] * 100); ?>%)</span>
                            </div>
                            <a href="inventory.php" class="text-red-200 hover:text-white transition-all">
                                Manage Inventory
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (empty($bulk_stats['pending_requests']) && ($inventory_stats['total_quantity'] / $inventory_stats['total_capacity'] * 100) <= 80): ?>
                        <div class="flex items-center p-4 bg-green-600/20 text-green-200 rounded-lg">
                            <i class="fas fa-check-circle mr-3"></i>
                            <span>All systems running normally. No critical alerts at this time.</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mobile menu toggle script -->
    <script>
        // Toggle mobile menu
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });
    </script>
</body>
</html>