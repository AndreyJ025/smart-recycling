<?php
session_start();
require_once '../database.php';

// Check admin access
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: ../home.php");
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

#Statistics Section
$extendedStats = [
    'active_users' => $conn->query("
        SELECT COUNT(DISTINCT user_id) as count 
        FROM tbl_remit 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ")->fetch_assoc()['count'],
    
    'top_center' => $conn->query("
        SELECT sc.name, COUNT(*) as visit_count 
        FROM tbl_remit r 
        JOIN tbl_sortation_centers sc ON r.sortation_center_id = sc.id 
        GROUP BY sc.id 
        ORDER BY visit_count DESC 
        LIMIT 1
    ")->fetch_assoc(),
    
    'monthly_items' => $conn->query("
        SELECT SUM(item_quantity) as count 
        FROM tbl_remit 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ")->fetch_assoc()['count'] ?? 0,
    
    'top_recyclers' => $conn->query("
        SELECT u.fullname, SUM(r.points) as total_points 
        FROM tbl_remit r 
        JOIN tbl_user u ON r.user_id = u.id 
        GROUP BY u.id 
        ORDER BY total_points DESC 
        LIMIT 5
    ")
];

// Fetch weekly activity data
$weeklyActivity = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%a') as day,
        COUNT(*) as count
    FROM tbl_remit
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE_FORMAT(created_at, '%a')
    ORDER BY created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Format data for chart
$chartData = [];
$chartLabels = [];
foreach ($weeklyActivity as $day) {
    $chartLabels[] = $day['day'];
    $chartData[] = $day['count'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EcoLens</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .bg-overlay > div {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body class="font-[Poppins] bg-[#1b1b1b]">
    <!-- Navigation -->
    <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center">
                <a href="../home.php" class="flex-shrink-0 flex items-center gap-3">
                    <img src="../assets/logo.png" alt="Smart Recycling Logo" class="h-10">
                    <h1 class="text-2xl font-bold">
                        <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                    </h1>
                </a>
                <a href="../home.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Back to Home
                </a>
            </div>
        </div>
    </nav>

    <div class="bg-overlay">
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

                <div class="grid md:grid-cols-2 gap-8 mb-8">
                    <!-- Activity Timeline -->
                    <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                        <h2 class="text-2xl font-bold text-white mb-6">24h Activity</h2>
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex-1">
                                <div class="text-3xl font-bold text-white mb-1"><?php echo $extendedStats['active_users']; ?></div>
                                <div class="text-white/60">Active Users</div>
                            </div>
                            <div class="flex-1">
                                <div class="text-3xl font-bold text-white mb-1"><?php echo $extendedStats['monthly_items']; ?></div>
                                <div class="text-white/60">Items This Month</div>
                            </div>
                        </div>
                        <canvas id="activityChart" class="w-full"></canvas>
                    </div>
                
                    <!-- Top Performers -->
                    <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                        <h2 class="text-2xl font-bold text-white mb-6">Top Recyclers</h2>
                        <div class="space-y-4">
                            <?php while($recycler = $extendedStats['top_recyclers']->fetch_assoc()): ?>
                            <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-[#436d2e] rounded-full flex items-center justify-center">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                    <div>
                                        <div class="text-white font-medium"><?php echo htmlspecialchars($recycler['fullname']); ?></div>
                                        <div class="text-white/60 text-sm"><?php echo number_format($recycler['total_points']); ?> points</div>
                                    </div>
                                </div>
                                <i class="fas fa-trophy text-[#436d2e]"></i>
                            </div>
                            <?php endwhile; ?>
                        </div>
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
                            <a href="../view-sortation.php" class="flex-1 border-2 border-[#436d2e] text-[#436d2e] px-6 py-3 rounded-lg font-semibold hover:bg-[#436d2e] hover:text-white transition-all text-center">
                                <i class="fa-solid fa-list mr-2"></i> View All Centers
                            </a>
                        </div>
                    </div>
                    <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                        <h2 class="text-2xl font-bold text-white mb-6">Manage Points</h2>
                        <div class="flex">
                            <a href="manage-rewards.php" class="flex-1 bg-[#436d2e] text-white px-6 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-all text-center">
                                <i class="fa-solid fa-star mr-2"></i> Award Points
                            </a>
                        </div>
                    </div>
                    <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                        <h2 class="text-2xl font-bold text-white mb-6">Manage Content</h2>
                        <div class="flex gap-4">
                            <a href="manage-faqs.php" class="flex-1 bg-[#436d2e] text-white px-6 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-all text-center">
                                <i class="fa-solid fa-question-circle mr-2"></i> Manage FAQs
                            </a>
                            <!-- Add more content management options here -->
                        </div>
                    </div>
                    <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                        <h2 class="text-2xl font-bold text-white mb-6">Manage Users</h2>
                        <div class="flex gap-4">
                            <a href="manage-users.php" class="flex-1 bg-[#436d2e] text-white px-6 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-all text-center">
                                <i class="fa-solid fa-users-gear mr-2"></i> Manage Users
                            </a>
                            <a href="user-activity.php" class="flex-1 border-2 border-[#436d2e] text-[#436d2e] px-6 py-3 rounded-lg font-semibold hover:bg-[#436d2e] hover:text-white transition-all text-center">
                                <i class="fa-solid fa-chart-line mr-2"></i> View Activity
                            </a>
                        </div>
                    </div>

                    <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                        <h2 class="text-2xl font-bold text-white mb-6">Manage Requests</h2>
                        <div class="flex gap-4">
                            <a href="manage-bulk-requests.php" class="flex-1 bg-[#436d2e] text-white px-6 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-all text-center">
                                <i class="fa-solid fa-truck-loading mr-2"></i> Bulk Requests
                            </a>
                        </div>
                    </div>

                    <!-- Analytics & Reports -->
                    <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                        <h2 class="text-2xl font-bold text-white mb-6">Analytics & Reports</h2>
                        <div class="flex gap-4">
                            <a href="analytics.php" class="flex-1 bg-[#436d2e] text-white px-6 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-all text-center">
                                <i class="fa-solid fa-chart-pie mr-2"></i> View Analytics
                            </a>
                            <a href="generate-report.php" class="flex-1 border-2 border-[#436d2e] text-[#436d2e] px-6 py-3 rounded-lg font-semibold hover:bg-[#436d2e] hover:text-white transition-all text-center">
                                <i class="fa-solid fa-file-excel mr-2"></i> Generate Reports
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
    </div>
    
    <script>
        const ctx = document.getElementById('activityChart');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Recycling Activity',
                    data: <?php echo json_encode($chartData); ?>,
                    borderColor: '#436d2e',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    }
                }
            }
        });
    </script>
    
</body>
</html>