<?php
session_start();
require_once '../database.php';

// Check if user is logged in and is a business
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'business') {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get date range from query parameters
$end_date = $_GET['end_date'] ?? date('Y-m-d');
$start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));

// Get recycling metrics
$metrics_query = "
    SELECT 
        SUM(br.estimated_quantity) as total_quantity,
        COUNT(br.id) as total_requests,
        SUM(CASE WHEN br.status = 'completed' THEN 1 ELSE 0 END) as completed_requests,
        COUNT(DISTINCT br.material_types) as unique_materials
    FROM tbl_bulk_requests br
    WHERE br.business_id = ?
    AND DATE(br.created_at) BETWEEN ? AND ?";

$stmt = $conn->prepare($metrics_query);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$metrics = $stmt->get_result()->fetch_assoc();

// Get material distribution
$materials_query = "
    SELECT 
        material_types,
        SUM(estimated_quantity) as total_quantity,
        COUNT(*) as frequency
    FROM tbl_bulk_requests
    WHERE business_id = ?
    AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY material_types";

$stmt = $conn->prepare($materials_query);
$stmt->bind_param("iss", $user_id, $start_date, $end_date);
$stmt->execute();
$materials_result = $stmt->get_result();

// Format data for charts
$material_labels = [];
$material_data = [];
while ($row = $materials_result->fetch_assoc()) {
    $material_labels[] = $row['material_types'];
    $material_data[] = $row['total_quantity'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Reports - EcoLens</title>
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
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="bg-overlay">
        <div class="relative min-h-screen pt-20 pb-12">
            <div class="container mx-auto px-4 md:px-6">
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
                    </form>
                </div>

                <!-- Key Metrics -->
                <div class="grid md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white/5 p-6 rounded-xl backdrop-blur-sm">
                        <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-recycle"></i></div>
                        <div class="text-2xl font-bold text-white mb-1"><?php echo number_format($metrics['total_quantity']); ?> kg</div>
                        <div class="text-white/60">Total Recycled</div>
                    </div>

                    <div class="bg-white/5 p-6 rounded-xl backdrop-blur-sm">
                        <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-clipboard-list"></i></div>
                        <div class="text-2xl font-bold text-white mb-1"><?php echo $metrics['total_requests']; ?></div>
                        <div class="text-white/60">Total Requests</div>
                    </div>

                    <div class="bg-white/5 p-6 rounded-xl backdrop-blur-sm">
                        <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-check-circle"></i></div>
                        <div class="text-2xl font-bold text-white mb-1"><?php echo $metrics['completed_requests']; ?></div>
                        <div class="text-white/60">Completed Requests</div>
                    </div>

                    <div class="bg-white/5 p-6 rounded-xl backdrop-blur-sm">
                        <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-cube"></i></div>
                        <div class="text-2xl font-bold text-white mb-1"><?php echo $metrics['unique_materials']; ?></div>
                        <div class="text-white/60">Material Types</div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid md:grid-cols-2 gap-8">
                    <!-- Material Distribution -->
                    <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                        <h2 class="text-2xl font-bold text-white mb-6">Material Distribution</h2>
                        <canvas id="materialChart"></canvas>
                    </div>

                    <!-- Environmental Impact -->
                    <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                        <h2 class="text-2xl font-bold text-white mb-6">Environmental Impact</h2>
                        <div class="space-y-6">
                            <div>
                                <h3 class="text-lg text-white mb-2">CO₂ Reduction</h3>
                                <div class="bg-white/10 rounded-lg p-4">
                                    <p class="text-2xl font-bold text-white">
                                        <?php echo number_format($metrics['total_quantity'] * 2.5); ?> kg
                                    </p>
                                    <p class="text-green-100 text-sm">Estimated CO₂ emissions prevented</p>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-lg text-white mb-2">Trees Saved</h3>
                                <div class="bg-white/10 rounded-lg p-4">
                                    <p class="text-2xl font-bold text-white">
                                        <?php echo number_format($metrics['total_quantity'] / 100); ?>
                                    </p>
                                    <p class="text-green-100 text-sm">Equivalent trees preserved</p>
                                </div>
                            </div>
                            
                            <div>
                                <h3 class="text-lg text-white mb-2">Water Saved</h3>
                                <div class="bg-white/10 rounded-lg p-4">
                                    <p class="text-2xl font-bold text-white">
                                        <?php echo number_format($metrics['total_quantity'] * 20); ?> L
                                    </p>
                                    <p class="text-green-100 text-sm">Estimated water conservation</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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