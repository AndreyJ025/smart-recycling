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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_batch'])) {
        // Create new processing batch
        $batch_id = 'BATCH-' . date('Y') . '-' . sprintf('%03d', rand(1, 999));
        $material_type = $_POST['material_type'];
        $quantity = $_POST['quantity'];
        $notes = $_POST['notes'];

        $stmt = $conn->prepare("INSERT INTO tbl_processing (center_id, batch_id, material_type, quantity, notes) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issds", $center_id, $batch_id, $material_type, $quantity, $notes);
        
        if ($stmt->execute()) {
            $success_message = "Processing batch created successfully";
        } else {
            $error_message = "Error creating processing batch";
        }
    }

    if (isset($_POST['update_status'])) {
        // Update processing status
        $batch_id = $_POST['batch_id'];
        $status = $_POST['status'];
        $completion_date = $status === 'completed' ? date('Y-m-d H:i:s') : null;

        $stmt = $conn->prepare("UPDATE tbl_processing SET status = ?, completion_date = ? WHERE batch_id = ? AND center_id = ?");
        $stmt->bind_param("sssi", $status, $completion_date, $batch_id, $center_id);
        
        if ($stmt->execute()) {
            $success_message = "Status updated successfully";
        } else {
            $error_message = "Error updating status";
        }
    }
}

// Get processing batches
$batches_query = "SELECT * FROM tbl_processing WHERE center_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($batches_query);
$stmt->bind_param("i", $center_id);
$stmt->execute();
$batches = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Processing - EcoLens</title>
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
                <div class="max-w-6xl mx-auto">
                    <!-- Messages -->
                    <?php if ($success_message): ?>
                        <div class="bg-green-600/20 text-green-200 px-4 py-3 rounded-lg mb-6">
                            <?php echo $success_message; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="bg-red-600/20 text-red-200 px-4 py-3 rounded-lg mb-6">
                            <?php echo $error_message; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Create New Batch -->
                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl mb-8">
                        <h2 class="text-2xl font-bold text-white mb-6">Create Processing Batch</h2>
                        <form method="POST" class="grid md:grid-cols-3 gap-6">
                            <div>
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
                            <div>
                                <input type="number" name="quantity" step="0.01" required
                                       placeholder="Quantity (kg)"
                                       class="w-full px-4 py-3 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                            </div>
                            <div>
                                <input type="text" name="notes" placeholder="Processing Notes"
                                       class="w-full px-4 py-3 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                            </div>
                            <div class="md:col-span-3">
                                <button type="submit" name="create_batch"
                                        class="w-full bg-[#436d2e] text-white px-6 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-all">
                                    Create Processing Batch
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Processing Batches -->
                    <div class="bg-white/5 backdrop-blur-sm rounded-xl overflow-hidden">
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-white mb-6">Processing Batches</h2>
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="text-left text-sm font-medium text-white border-b border-white/10">
                                            <th class="px-6 py-3">Batch ID</th>
                                            <th class="px-6 py-3">Material</th>
                                            <th class="px-6 py-3">Quantity</th>
                                            <th class="px-6 py-3">Status</th>
                                            <th class="px-6 py-3">Start Date</th>
                                            <th class="px-6 py-3">Completion</th>
                                            <th class="px-6 py-3">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/10">
                                        <?php while ($batch = $batches->fetch_assoc()): ?>
                                            <tr class="text-white/80 hover:bg-white/5">
                                                <td class="px-6 py-4"><?php echo $batch['batch_id']; ?></td>
                                                <td class="px-6 py-4"><?php echo ucfirst($batch['material_type']); ?></td>
                                                <td class="px-6 py-4"><?php echo number_format($batch['quantity'], 2); ?> kg</td>
                                                <td class="px-6 py-4">
                                                    <?php
                                                    $status_colors = [
                                                        'pending' => 'bg-yellow-600/20 text-yellow-200',
                                                        'processing' => 'bg-blue-600/20 text-blue-200',
                                                        'completed' => 'bg-green-600/20 text-green-200',
                                                        'cancelled' => 'bg-red-600/20 text-red-200'
                                                    ];
                                                    $status_color = $status_colors[$batch['status']] ?? 'bg-gray-600/20 text-gray-200';
                                                    ?>
                                                    <span class="px-2 py-1 rounded-full text-xs <?php echo $status_color; ?>">
                                                        <?php echo ucfirst($batch['status']); ?>
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?php echo $batch['start_date'] ? date('M d, Y H:i', strtotime($batch['start_date'])) : '-'; ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?php echo $batch['completion_date'] ? date('M d, Y H:i', strtotime($batch['completion_date'])) : '-'; ?>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <?php if ($batch['status'] !== 'completed' && $batch['status'] !== 'cancelled'): ?>
                                                        <form method="POST" class="inline-block">
                                                            <input type="hidden" name="batch_id" value="<?php echo $batch['batch_id']; ?>">
                                                            <input type="hidden" name="status" value="<?php echo $batch['status'] === 'pending' ? 'processing' : 'completed'; ?>">
                                                            <button type="submit" name="update_status" 
                                                                    class="text-[#436d2e] hover:text-white transition-all">
                                                                <?php echo $batch['status'] === 'pending' ? 'Start Processing' : 'Mark Complete'; ?>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>