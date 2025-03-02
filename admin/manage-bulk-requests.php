<?php
session_start();
require_once '../database.php';

// Check admin access
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: ../home.php");
    exit();
}

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : 'all';
$date_range = isset($_GET['date_range']) ? $_GET['date_range'] : '30';

// Build query based on filters
$query = "
    SELECT br.*, u.fullname as business_name
    FROM tbl_bulk_requests br
    JOIN tbl_user u ON br.business_id = u.id
    WHERE br.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
";

if ($status !== 'all') {
    $query .= " AND br.status = ?";
}

$query .= " ORDER BY br.created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if ($status !== 'all') {
    $stmt->bind_param("is", $date_range, $status);
} else {
    $stmt->bind_param("i", $date_range);
}
$stmt->execute();
$requests = $stmt->get_result();

// Handle status update if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $request_id = $_POST['request_id'];
    $new_status = $_POST['new_status'];
    
    $update_stmt = $conn->prepare("UPDATE tbl_bulk_requests SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_status, $request_id);
    $update_stmt->execute();
    
    // Redirect to refresh the page
    header('Location: manage-bulk-requests.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bulk Requests - EcoLens Admin</title>
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
            background: rgba(0, 0, 0, 0.7);
        }
        .bg-overlay > div {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body class="font-[Poppins]">
    <!-- Navigation -->
    <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <img src="../assets/logo.png" alt="EcoLens Logo" class="h-8">
                    <span class="text-xl font-bold text-white">EcoLens</span>
                </div>
                <a href="admin-dashboard.php" class="text-white hover:text-[#436d2e] transition-all">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="bg-overlay">
        <div class="relative pt-24 pb-12 px-4">
            <div class="max-w-7xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-white mb-2">Bulk Recycling Requests</h1>
                    <p class="text-white/60">Manage and monitor business bulk recycling requests</p>
                </div>

                <!-- Filters -->
                <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm mb-8">
                    <form method="GET" class="flex gap-4">
                        <select name="status" class="px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e] [&>option]:text-white [&>option]:bg-[#1b1b1b]">
                            <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Statuses</option>
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                            <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        </select>
                        <select name="date_range" class="px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e] [&>option]:text-white [&>option]:bg-[#1b1b1b]">
                            <option value="30" <?php echo $date_range == '30' ? 'selected' : ''; ?>>Last 30 Days</option>
                            <option value="90" <?php echo $date_range == '90' ? 'selected' : ''; ?>>Last 90 Days</option>
                            <option value="365" <?php echo $date_range == '365' ? 'selected' : ''; ?>>Last Year</option>
                            <option value="9999" <?php echo $date_range == '9999' ? 'selected' : ''; ?>>All Time</option>
                        </select>
                        <button type="submit" class="px-6 py-2 bg-[#436d2e] text-white rounded-lg hover:bg-opacity-90">
                            <i class="fas fa-filter mr-2"></i>Apply Filters
                        </button>
                    </form>
                </div>

                <!-- Requests Table -->
                <div class="bg-white/5 rounded-xl backdrop-blur-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-[#436d2e]/20">
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-white">Business</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-white">Request Type</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-white">Materials</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-white">Quantity</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-white">Preferred Date</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-white">Status</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-white">Quotes</th>
                                    <th class="px-6 py-4 text-left text-sm font-semibold text-white">Created</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                <?php while($request = $requests->fetch_assoc()): ?>
                                <tr class="hover:bg-white/5">
                                    <td class="px-6 py-4">
                                        <div class="text-white font-medium"><?php echo htmlspecialchars($request['business_name']); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-white"><?php echo ucfirst($request['request_type']); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-white"><?php echo str_replace(',', ', ', ucwords($request['material_types'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-white"><?php echo number_format($request['estimated_quantity']); ?> kg</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-white"><?php echo date('M j, Y', strtotime($request['preferred_date'])); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($request['status'] === 'pending'): ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-500/20 text-yellow-500">
                                                Pending
                                            </span>
                                        <?php elseif ($request['status'] === 'approved'): ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-500">
                                                Approved
                                            </span>
                                        <?php elseif ($request['status'] === 'completed'): ?>
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-500/20 text-blue-500">
                                                Completed
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-white">
                                            <?php if ($request['quote_count'] > 0): ?>
                                                <?php echo $request['quote_count']; ?> quote(s)
                                                <?php if ($request['accepted_quotes'] > 0): ?>
                                                    <span class="text-green-400">(<?php echo $request['accepted_quotes']; ?> accepted)</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                No quotes yet
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-white/60"><?php echo date('M j, Y', strtotime($request['created_at'])); ?></div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                
                                <?php if ($requests->num_rows === 0): ?>
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-white/60">No requests found with the current filters.</td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-[#1b1b1b] p-6 rounded-xl w-full max-w-md">
            <h3 class="text-xl font-bold text-white mb-4">Update Request Status</h3>
            
            <form method="POST">
                <input type="hidden" name="request_id" id="requestId">
                
                <div class="mb-4">
                    <label class="block text-white/70 mb-2">Status</label>
                    <select name="new_status" id="statusSelect" class="w-full px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e] [&>option]:text-white [&>option]:bg-[#1b1b1b]">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-white/70 hover:text-white">
                        Cancel
                    </button>
                    <button type="submit" name="update_status" class="px-4 py-2 bg-[#436d2e] text-white rounded-lg hover:bg-opacity-90">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(requestId, currentStatus) {
            document.getElementById('requestId').value = requestId;
            document.getElementById('statusSelect').value = currentStatus;
            document.getElementById('statusModal').classList.remove('hidden');
        }
        
        function closeModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }
    </script>
</body>
</html>