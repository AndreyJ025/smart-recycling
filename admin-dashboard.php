<?php
session_start();
require_once 'database.php';

// Check admin access
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: home.php");
    exit();
}

// Fetch statistics
$stats = [
    'users' => $conn->query("SELECT COUNT(*) as count FROM tbl_user")->fetch_assoc()['count'],
    'items' => $conn->query("SELECT SUM(item_quantity) as count FROM tbl_remit")->fetch_assoc()['count'] ?? 0,
    'centers' => $conn->query("SELECT COUNT(*) as count FROM tbl_sortation_centers")->fetch_assoc()['count'],
    'points' => $conn->query("SELECT SUM(points) as total FROM tbl_remit")->fetch_assoc()['total'] ?? 0
];

// Fetch recent activities
$recentActivities = $conn->query("
    SELECT r.*, u.fullname, s.name as center_name 
    FROM tbl_remit r 
    JOIN tbl_user u ON r.user_id = u.id 
    JOIN tbl_sortation_centers s ON r.sortation_center_id = s.id 
    ORDER BY r.created_at DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EcoLens</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="font-[Poppins] bg-[#1b1b1b]">
    <!-- Navigation -->
    <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center">
                <a href="home.php" class="flex-shrink-0 flex items-center gap-3">
                    <img src="logo.png" alt="Smart Recycling Logo" class="h-10">
                    <h1 class="text-2xl font-bold">
                        <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                    </h1>
                </a>
                <a href="home.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Home
                </a>
            </div>
        </div>
    </nav>

    <div class="pt-24 pb-12 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Stats Overview -->
            <div class="grid md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white/5 p-6 rounded-xl backdrop-blur-sm">
                    <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-users"></i></div>
                    <div class="text-2xl font-bold text-white mb-1"><?php echo $stats['users']; ?></div>
                    <div class="text-white/60">Total Users</div>
                </div>
                <div class="bg-white/5 p-6 rounded-xl backdrop-blur-sm">
                    <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-recycle"></i></div>
                    <div class="text-2xl font-bold text-white mb-1"><?php echo $stats['items']; ?></div>
                    <div class="text-white/60">Items Recycled | Donated</div>
                </div>
                <div class="bg-white/5 p-6 rounded-xl backdrop-blur-sm">
                    <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-location-dot"></i></div>
                    <div class="text-2xl font-bold text-white mb-1"><?php echo $stats['centers']; ?></div>
                    <div class="text-white/60">Centers</div>
                </div>
                <div class="bg-white/5 p-6 rounded-xl backdrop-blur-sm">
                    <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-star"></i></div>
                    <div class="text-2xl font-bold text-white mb-1"><?php echo $stats['points']; ?></div>
                    <div class="text-white/60">Total Distributed Points</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="grid md:grid-cols-2 gap-8 mb-8">
                <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                    <h2 class="text-2xl font-bold text-white mb-6">Manage Centers</h2>
                    <div class="flex gap-4">
                        <a href="add-sortation.php" class="flex-1 bg-[#436d2e] text-white px-6 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-all text-center">
                            <i class="fa-solid fa-plus mr-2"></i> Add New Center
                        </a>
                        <a href="view-sortation.php" class="flex-1 border-2 border-[#436d2e] text-[#436d2e] px-6 py-3 rounded-lg font-semibold hover:bg-[#436d2e] hover:text-white transition-all text-center">
                            <i class="fa-solid fa-list mr-2"></i> View All Centers
                        </a>
                    </div>
                </div>
                <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                    <h2 class="text-2xl font-bold text-white mb-6">Manage Records</h2>
                    <div class="flex gap-4">
                        <a href="view-remit.php" class="flex-1 bg-[#436d2e] text-white px-6 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-all text-center">
                            <i class="fa-solid fa-clock-rotate-left mr-2"></i> View Records
                        </a>
                        <a href="export-records.php" class="flex-1 border-2 border-[#436d2e] text-[#436d2e] px-6 py-3 rounded-lg font-semibold hover:bg-[#436d2e] hover:text-white transition-all text-center">
                            <i class="fa-solid fa-download mr-2"></i> Export Data
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                <h2 class="text-2xl font-bold text-white mb-6">Recent Activity</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-white/60 text-left">
                                <th class="pb-4">User</th>
                                <th class="pb-4">Item</th>
                                <th class="pb-4">Center</th>
                                <th class="pb-4">Quantity</th>
                                <th class="pb-4">Points</th>
                                <th class="pb-4">Date</th>
                            </tr>
                        </thead>
                        <tbody class="text-white">
                            <?php while($activity = $recentActivities->fetch_assoc()): ?>
                            <tr class="border-t border-white/10">
                                <td class="py-4"><?php echo htmlspecialchars($activity['fullname']); ?></td>
                                <td class="py-4"><?php echo htmlspecialchars($activity['item_name']); ?></td>
                                <td class="py-4"><?php echo htmlspecialchars($activity['center_name']); ?></td>
                                <td class="py-4"><?php echo $activity['item_quantity']; ?></td>
                                <td class="py-4"><?php echo $activity['points']; ?></td>
                                <td class="py-4"><?php echo date('M d, Y', strtotime($activity['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>