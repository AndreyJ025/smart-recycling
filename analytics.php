<?php
session_start();
require_once 'database.php';

// Check admin access
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: home.php");
    exit();
}

// Get date range parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Fetch analytics data
$analytics = [
    'total_items' => $conn->query("
        SELECT SUM(item_quantity) as count 
        FROM tbl_remit 
        WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    ")->fetch_assoc()['count'] ?? 0,
    
    'total_points' => $conn->query("
        SELECT SUM(points) as total 
        FROM tbl_remit 
        WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    ")->fetch_assoc()['total'] ?? 0,
    
    'active_users' => $conn->query("
        SELECT COUNT(DISTINCT user_id) as count 
        FROM tbl_remit 
        WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    ")->fetch_assoc()['count'] ?? 0,
    
    'center_performance' => $conn->query("
        SELECT 
            sc.name,
            COUNT(r.id) as total_visits,
            SUM(r.item_quantity) as total_items,
            SUM(r.points) as total_points
        FROM tbl_sortation_centers sc
        LEFT JOIN tbl_remit r ON sc.id = r.sortation_center_id
        WHERE DATE(r.created_at) BETWEEN '$start_date' AND '$end_date'
        GROUP BY sc.id
        ORDER BY total_items DESC
    "),
    
    'material_distribution' => $conn->query("
        SELECT 
            item_name,
            SUM(item_quantity) as quantity,
            COUNT(*) as frequency
        FROM tbl_remit
        WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
        GROUP BY item_name
        ORDER BY quantity DESC
    "),
    
    'daily_activity' => $conn->query("
        SELECT 
            DATE(created_at) as date,
            COUNT(*) as transactions,
            SUM(item_quantity) as items,
            SUM(points) as points
        FROM tbl_remit
        WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'
        GROUP BY DATE(created_at)
        ORDER BY date ASC
    ")
];

// Calculate environmental impact
$environmental_impact = [
    'plastic_saved' => $conn->query("
        SELECT SUM(item_quantity) as quantity
        FROM tbl_remit
        WHERE item_name LIKE '%plastic%'
        AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    ")->fetch_assoc()['quantity'] ?? 0,
    
    'paper_saved' => $conn->query("
        SELECT SUM(item_quantity) as quantity
        FROM tbl_remit
        WHERE item_name LIKE '%paper%'
        AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'
    ")->fetch_assoc()['quantity'] ?? 0
];

// Format data for charts
$daily_data = [];
$daily_labels = [];
while ($row = $analytics['daily_activity']->fetch_assoc()) {
    $daily_labels[] = date('M d', strtotime($row['date']));
    $daily_data[] = $row['items'];
}

$material_labels = [];
$material_data = [];
while ($row = $analytics['material_distribution']->fetch_assoc()) {
    $material_labels[] = $row['item_name'];
    $material_data[] = $row['quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - EcoLens</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="font-[Poppins] bg-[#1b1b1b]">
    <!-- Navigation -->
    <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex-shrink-0 flex items-center gap-3">
                    <img src="logo.png" alt="Smart Recycling Logo" class="h-10">
                    <h1 class="text-2xl font-bold">
                        <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                    </h1>
                </div>
                <a href="admin-dashboard.php" class="text-white hover:text-[#22c55e] transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="pt-24 pb-12 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Date Range Filter -->
            <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm mb-8">
                <form method="GET" class="flex gap-4">
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>"
                           class="px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>"
                           class="px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                    <button type="submit" class="px-6 py-2 bg-[#436d2e] text-white rounded-lg hover:bg-opacity-90">
                        <i class="fas fa-filter mr-2"></i>Apply Filter
                    </button>
                    <a href="generate-report.php?start_date=<?php echo $start_date; ?>&end_date=<?php echo $end_date; ?>" 
                       class="px-6 py-2 border-2 border-[#436d2e] text-[#436d2e] rounded-lg hover:bg-[#436d2e] hover:text-white transition-all">
                        <i class="fas fa-download mr-2"></i>Export Report
                    </a>
                </form>
            </div>

            <!-- Key Metrics -->
            <div class="grid md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white/5 p-6 rounded-xl backdrop-blur-sm">
                    <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-recycle"></i></div>
                    <div class="text-2xl font-bold text-white mb-1"><?php echo number_format($analytics['total_items']); ?></div>
                    <div class="text-white/60">Items Recycled</div>
                </div>
                <div class="bg-white/5 p-6 rounded-xl backdrop-blur-sm">
                    <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-star"></i></div>
                    <div class="text-2xl font-bold text-white mb-1"><?php echo number_format($analytics['total_points']); ?></div>
                    <div class="text-white/60">Points Awarded</div>
                </div>
                <div class="bg-white/5 p-6 rounded-xl backdrop-blur-sm">
                    <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-users"></i></div>
                    <div class="text-2xl font-bold text-white mb-1"><?php echo number_format($analytics['active_users']); ?></div>
                    <div class="text-white/60">Active Users</div>
                </div>
                <div class="bg-white/5 p-6 rounded-xl backdrop-blur-sm">
                    <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-leaf"></i></div>
                    <div class="text-2xl font-bold text-white mb-1"><?php echo number_format($environmental_impact['plastic_saved']); ?></div>
                    <div class="text-white/60">Plastic Items Saved</div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid md:grid-cols-2 gap-8 mb-8">
                <!-- Activity Timeline -->
                <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                    <h2 class="text-2xl font-bold text-white mb-6">Daily Activity</h2>
                    <canvas id="activityChart"></canvas>
                </div>
                
                <!-- Material Distribution -->
                <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                    <h2 class="text-2xl font-bold text-white mb-6">Material Distribution</h2>
                    <canvas id="materialChart"></canvas>
                </div>
            </div>

            <!-- Center Performance -->
            <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm mb-8">
                <h2 class="text-2xl font-bold text-white mb-6">Center Performance</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-white/60 text-left">
                                <th class="pb-4">Center</th>
                                <th class="pb-4">Total Visits</th>
                                <th class="pb-4">Items Collected</th>
                                <th class="pb-4">Points Awarded</th>
                                <th class="pb-4">Efficiency</th>
                            </tr>
                        </thead>
                        <tbody class="text-white">
                            <?php while($center = $analytics['center_performance']->fetch_assoc()): ?>
                            <tr class="border-t border-white/10">
                                <td class="py-4"><?php echo htmlspecialchars($center['name']); ?></td>
                                <td class="py-4"><?php echo number_format($center['total_visits']); ?></td>
                                <td class="py-4"><?php echo number_format($center['total_items']); ?></td>
                                <td class="py-4"><?php echo number_format($center['total_points']); ?></td>
                                <td class="py-4">
                                    <?php echo $center['total_visits'] ? 
                                        number_format($center['total_items'] / $center['total_visits'], 1) : 0; ?> 
                                    items/visit
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Activity Chart
    new Chart(document.getElementById('activityChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($daily_labels); ?>,
            datasets: [{
                label: 'Items Recycled',
                data: <?php echo json_encode($daily_data); ?>,
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

    // Material Distribution Chart
    new Chart(document.getElementById('materialChart'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($material_labels); ?>,
            datasets: [{
                data: <?php echo json_encode($material_data); ?>,
                backgroundColor: [
                    '#436d2e',
                    '#4e4e10',
                    '#6b9c46',
                    '#8f8f1b',
                    '#2b4b1d'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: 'rgba(255, 255, 255, 0.7)'
                    }
                }
            }
        }
    });
    </script>
</body>
</html>