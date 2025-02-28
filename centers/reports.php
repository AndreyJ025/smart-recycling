<?php
session_start();
require_once '../database.php';

// Check if user is logged in and has center access
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'center') {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

$center_id = $_SESSION['center_id'];

// Get filter parameters
$date_range = isset($_GET['range']) ? $_GET['range'] : 'month';
$material_filter = isset($_GET['material']) ? $_GET['material'] : '';

// Set date range based on filter
$end_date = date('Y-m-d'); // Today
switch($date_range) {
    case 'week':
        $start_date = date('Y-m-d', strtotime('-1 week'));
        break;
    case 'month':
        $start_date = date('Y-m-d', strtotime('-1 month'));
        break;
    case 'quarter':
        $start_date = date('Y-m-d', strtotime('-3 months'));
        break;
    case 'year':
        $start_date = date('Y-m-d', strtotime('-1 year'));
        break;
    default:
        $start_date = date('Y-m-d', strtotime('-1 month'));
        $date_range = 'month';
}

// Processing volume by material type
$material_query = "SELECT 
                    material_type, 
                    SUM(quantity) as total_quantity 
                   FROM tbl_processing 
                   WHERE center_id = ? 
                   AND created_at BETWEEN ? AND ? 
                   AND status = 'completed'";

if ($material_filter) {
    $material_query .= " AND material_type = '$material_filter'";
}

$material_query .= " GROUP BY material_type ORDER BY total_quantity DESC";

$stmt = $conn->prepare($material_query);
$stmt->bind_param("iss", $center_id, $start_date, $end_date);
$stmt->execute();
$material_stats = $stmt->get_result();

// Pickup statistics
$pickup_query = "SELECT 
                  COUNT(*) as total_pickups,
                  SUM(CASE WHEN current_status = 'completed' THEN 1 ELSE 0 END) as completed_pickups,
                  SUM(CASE WHEN current_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_pickups,
                  AVG(TIME_TO_SEC(TIMEDIFF(actual_completion, estimated_completion))/60) as avg_completion_diff_minutes
                 FROM tbl_pickups
                 WHERE pickup_date BETWEEN ? AND ?";
$stmt = $conn->prepare($pickup_query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$pickup_stats = $stmt->get_result()->fetch_assoc();

// Processing efficiency
$efficiency_query = "SELECT 
                      DATE_FORMAT(completion_date, '%Y-%m-%d') as process_date,
                      COUNT(*) as batch_count,
                      SUM(quantity) as total_processed
                     FROM tbl_processing
                     WHERE center_id = ?
                     AND status = 'completed'
                     AND completion_date BETWEEN ? AND ?
                     GROUP BY DATE_FORMAT(completion_date, '%Y-%m-%d')
                     ORDER BY process_date";
$stmt = $conn->prepare($efficiency_query);
$stmt->bind_param("iss", $center_id, $start_date, $end_date);
$stmt->execute();
$efficiency_data = $stmt->get_result();

// Capacity utilization over time
$capacity_query = "SELECT 
                    DATE_FORMAT(last_updated, '%Y-%m-%d') as update_date,
                    SUM(quantity) as total_quantity,
                    SUM(capacity) as total_capacity,
                    (SUM(quantity)/SUM(capacity))*100 as utilization_percentage
                   FROM tbl_inventory
                   WHERE center_id = ?
                   GROUP BY DATE_FORMAT(last_updated, '%Y-%m-%d')
                   ORDER BY update_date DESC
                   LIMIT 10";
$stmt = $conn->prepare($capacity_query);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$capacity_data = $stmt->get_result();

// Get all material types for filter dropdown
$materials_query = "SELECT DISTINCT material_type FROM tbl_inventory WHERE center_id = ?";
$stmt = $conn->prepare($materials_query);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$materials_result = $stmt->get_result();
$materials = array();
while ($row = $materials_result->fetch_assoc()) {
    $materials[] = $row['material_type'];
}

// Summary statistics
$summary_query = "SELECT 
                   COUNT(DISTINCT batch_id) as total_batches,
                   SUM(quantity) as total_processed,
                   MAX(quantity) as largest_batch,
                   AVG(quantity) as average_batch_size
                  FROM tbl_processing
                  WHERE center_id = ?
                  AND created_at BETWEEN ? AND ?";
$stmt = $conn->prepare($summary_query);
$stmt->bind_param("iss", $center_id, $start_date, $end_date);
$stmt->execute();
$summary_stats = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports & Analytics - EcoLens</title>
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
                <div class="max-w-6xl mx-auto">
                    <h1 class="text-3xl font-bold text-white mb-8">Reports & Analytics</h1>
                    
                    <!-- Filters -->
                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl mb-8">
                        <form method="GET" class="flex flex-wrap gap-4">
                            <div>
                                <label class="block text-white/70 mb-1 text-sm">Date Range</label>
                                <select name="range" class="px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                                    <option value="week" class="text-black" <?php echo $date_range === 'week' ? 'selected' : ''; ?>>Last Week</option>
                                    <option value="month" class="text-black" <?php echo $date_range === 'month' ? 'selected' : ''; ?>>Last Month</option>
                                    <option value="quarter" class="text-black" <?php echo $date_range === 'quarter' ? 'selected' : ''; ?>>Last 3 Months</option>
                                    <option value="year" class="text-black" <?php echo $date_range === 'year' ? 'selected' : ''; ?>>Last Year</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-white/70 mb-1 text-sm">Material Type</label>
                                <select name="material" class="px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                                    <option value="" class="text-black">All Materials</option>
                                    <?php foreach($materials as $material): ?>
                                        <option value="<?php echo $material; ?>" class="text-black" <?php echo $material_filter === $material ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($material); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button type="submit" class="px-6 py-2 bg-[#436d2e] text-white rounded-lg font-medium hover:bg-opacity-90 transition-all">
                                    Apply Filters
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Summary Statistics -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                        <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                            <h3 class="text-lg text-white/80 mb-1">Total Processed</h3>
                            <p class="text-2xl font-bold text-white">
                                <?php echo number_format($summary_stats['total_processed'], 2); ?> kg
                            </p>
                        </div>
                        
                        <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                            <h3 class="text-lg text-white/80 mb-1">Processing Batches</h3>
                            <p class="text-2xl font-bold text-white">
                                <?php echo number_format($summary_stats['total_batches']); ?>
                            </p>
                        </div>
                        
                        <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                            <h3 class="text-lg text-white/80 mb-1">Average Batch Size</h3>
                            <p class="text-2xl font-bold text-white">
                                <?php echo number_format($summary_stats['average_batch_size'], 2); ?> kg
                            </p>
                        </div>
                        
                        <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                            <h3 class="text-lg text-white/80 mb-1">Largest Batch</h3>
                            <p class="text-2xl font-bold text-white">
                                <?php echo number_format($summary_stats['largest_batch'], 2); ?> kg
                            </p>
                        </div>
                    </div>

                    <!-- Material Processing Chart -->
                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl mb-8">
                        <h2 class="text-2xl font-bold text-white mb-6">Material Processing Volume</h2>
                        <div class="h-64">
                            <canvas id="materialsChart"></canvas>
                        </div>
                    </div>

                    <!-- Processing Efficiency -->
                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl mb-8">
                        <h2 class="text-2xl font-bold text-white mb-6">Processing Trends</h2>
                        <div class="h-64">
                            <canvas id="processChart"></canvas>
                        </div>
                    </div>

                    <!-- Pickup Performance -->
                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl mb-8">
                        <h2 class="text-2xl font-bold text-white mb-6">Pickup Performance</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-white/10 p-6 rounded-lg">
                                <h3 class="text-lg text-white/80 mb-2">Completion Rate</h3>
                                <div class="flex items-end gap-2">
                                    <?php 
                                    $completion_rate = ($pickup_stats['total_pickups'] > 0) 
                                        ? round(($pickup_stats['completed_pickups'] / $pickup_stats['total_pickups']) * 100) 
                                        : 0;
                                    ?>
                                    <span class="text-3xl font-bold text-white"><?php echo $completion_rate; ?>%</span>
                                    <span class="text-white/60 text-sm mb-1">(<?php echo $pickup_stats['completed_pickups']; ?>/<?php echo $pickup_stats['total_pickups']; ?>)</span>
                                </div>
                                <div class="w-full bg-white/10 rounded-full h-2 mt-2">
                                    <div class="bg-[#436d2e] h-2 rounded-full" style="width: <?php echo $completion_rate; ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="bg-white/10 p-6 rounded-lg">
                                <h3 class="text-lg text-white/80 mb-2">Cancellation Rate</h3>
                                <div class="flex items-end gap-2">
                                    <?php 
                                    $cancellation_rate = ($pickup_stats['total_pickups'] > 0) 
                                        ? round(($pickup_stats['cancelled_pickups'] / $pickup_stats['total_pickups']) * 100) 
                                        : 0;
                                    ?>
                                    <span class="text-3xl font-bold text-white"><?php echo $cancellation_rate; ?>%</span>
                                    <span class="text-white/60 text-sm mb-1">(<?php echo $pickup_stats['cancelled_pickups']; ?>/<?php echo $pickup_stats['total_pickups']; ?>)</span>
                                </div>
                                <div class="w-full bg-white/10 rounded-full h-2 mt-2">
                                    <div class="bg-red-500 h-2 rounded-full" style="width: <?php echo $cancellation_rate; ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="bg-white/10 p-6 rounded-lg">
                                <h3 class="text-lg text-white/80 mb-2">Avg Time Accuracy</h3>
                                <div class="flex items-end gap-2">
                                    <?php 
                                    $avg_diff = round($pickup_stats['avg_completion_diff_minutes']);
                                    $diff_text = $avg_diff >= 0 
                                        ? "+" . $avg_diff . " mins" 
                                        : $avg_diff . " mins";
                                    $diff_class = $avg_diff > 10 
                                        ? "text-red-400" 
                                        : ($avg_diff < -10 ? "text-green-400" : "text-white");
                                    ?>
                                    <span class="text-3xl font-bold <?php echo $diff_class; ?>"><?php echo $diff_text; ?></span>
                                </div>
                                <p class="text-white/60 text-sm mt-2">
                                    <?php 
                                    if ($avg_diff > 10) {
                                        echo "Pickups are typically late";
                                    } else if ($avg_diff < -10) {
                                        echo "Pickups typically arrive early";
                                    } else {
                                        echo "Pickups are typically on time";
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Capacity Utilization -->
                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                        <h2 class="text-2xl font-bold text-white mb-6">Capacity Utilization</h2>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="text-left text-sm font-medium text-white border-b border-white/10">
                                        <th class="px-6 py-3">Date</th>
                                        <th class="px-6 py-3">Current Quantity</th>
                                        <th class="px-6 py-3">Total Capacity</th>
                                        <th class="px-6 py-3">Utilization %</th>
                                        <th class="px-6 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10">
                                    <?php if ($capacity_data->num_rows == 0): ?>
                                        <tr>
                                            <td colspan="5" class="px-6 py-4 text-white/70 text-center">No capacity data available.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php while ($row = $capacity_data->fetch_assoc()): ?>
                                            <tr class="text-white/80 hover:bg-white/5">
                                                <td class="px-6 py-4"><?php echo date('M d, Y', strtotime($row['update_date'])); ?></td>
                                                <td class="px-6 py-4"><?php echo number_format($row['total_quantity'], 2); ?> kg</td>
                                                <td class="px-6 py-4"><?php echo number_format($row['total_capacity'], 2); ?> kg</td>
                                                <td class="px-6 py-4"><?php echo number_format($row['utilization_percentage'], 1); ?>%</td>
                                                <td class="px-6 py-4">
                                                    <?php
                                                    $percent = $row['utilization_percentage'];
                                                    if ($percent >= 90) {
                                                        echo '<span class="px-2 py-1 rounded-full text-xs bg-red-600/20 text-red-200">Critical</span>';
                                                    } else if ($percent >= 75) {
                                                        echo '<span class="px-2 py-1 rounded-full text-xs bg-yellow-600/20 text-yellow-200">Warning</span>';
                                                    } else {
                                                        echo '<span class="px-2 py-1 rounded-full text-xs bg-green-600/20 text-green-200">Good</span>';
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Format data for charts
        document.addEventListener('DOMContentLoaded', function() {
            // Materials Chart
            const materialsCtx = document.getElementById('materialsChart').getContext('2d');
            const materialsChart = new Chart(materialsCtx, {
                type: 'bar',
                data: {
                    labels: [
                        <?php 
                        $materials_data = array();
                        $materials_labels = array();
                        $materials_colors = array();
                        
                        // Color palette
                        $colors = [
                            'rgba(67, 109, 46, 0.8)',
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                        ];
                        
                        $i = 0;
                        while($row = $material_stats->fetch_assoc()) {
                            echo "'" . ucfirst($row['material_type']) . "', ";
                            $materials_data[] = $row['total_quantity'];
                            $materials_labels[] = ucfirst($row['material_type']);
                            $materials_colors[] = $colors[$i % count($colors)];
                            $i++;
                        }
                        ?>
                    ],
                    datasets: [{
                        label: 'Processed Material (kg)',
                        data: [<?php echo implode(', ', $materials_data); ?>],
                        backgroundColor: [<?php echo "'" . implode("', '", $materials_colors) . "'"; ?>],
                        borderWidth: 0,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
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
                                display: false
                            },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.7)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Processing Trends Chart
            const processCtx = document.getElementById('processChart').getContext('2d');
            const processChart = new Chart(processCtx, {
                type: 'line',
                data: {
                    labels: [
                        <?php
                        $process_dates = array();
                        $process_quantities = array();
                        
                        while($row = $efficiency_data->fetch_assoc()) {
                            echo "'" . date('M d', strtotime($row['process_date'])) . "', ";
                            $process_dates[] = date('M d', strtotime($row['process_date']));
                            $process_quantities[] = $row['total_processed'];
                        }
                        ?>
                    ],
                    datasets: [{
                        label: 'Daily Processing Volume (kg)',
                        data: [<?php echo implode(', ', $process_quantities); ?>],
                        borderColor: 'rgba(67, 109, 46, 1)',
                        backgroundColor: 'rgba(67, 109, 46, 0.2)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: 'rgba(67, 109, 46, 1)',
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
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
                    },
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                color: 'rgba(255, 255, 255, 0.7)'
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.7)',
                            titleColor: 'rgba(255, 255, 255, 0.9)',
                            bodyColor: 'rgba(255, 255, 255, 0.9)',
                            displayColors: false
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>