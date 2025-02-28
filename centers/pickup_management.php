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
    // Update pickup request status
    if (isset($_POST['update_pickup'])) {
        $pickup_id = $_POST['pickup_id'];
        $status = $_POST['status'];
        $vehicle = $_POST['vehicle'] ?? null;
        $notes = $_POST['notes'] ?? null;
        $estimated = $status === 'in_transit' ? date('Y-m-d H:i:s', strtotime('+1 hour')) : null;
        
        $stmt = $conn->prepare("UPDATE tbl_pickups SET current_status = ?, vehicle_assigned = ?, driver_notes = ?, estimated_completion = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $status, $vehicle, $notes, $estimated, $pickup_id);
        
        if ($stmt->execute()) {
            // Create notification
            $message = getStatusMessage($status);
            $stmt = $conn->prepare("INSERT INTO tbl_pickup_notifications (pickup_id, message, notification_type) VALUES (?, ?, 'status_update')");
            $stmt->bind_param("is", $pickup_id, $message);
            $stmt->execute();
            
            $success_message = "Pickup status updated successfully";
        } else {
            $error_message = "Error updating pickup status";
        }
    }

    // Handle bulk request response
    if (isset($_POST['respond_bulk'])) {
        $request_id = $_POST['request_id'];
        $action = $_POST['action'];
        $price = $_POST['price'] ?? 0;
        $points = $_POST['points'] ?? 0;
        $notes = $_POST['notes'] ?? '';
        
        // Update the bulk request status
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        $stmt = $conn->prepare("UPDATE tbl_bulk_requests SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $request_id);
        
        if ($stmt->execute()) {
            // If approved, create a quote
            if ($action === 'approve') {
                $expiration = date('Y-m-d', strtotime('+7 days'));
                $stmt = $conn->prepare("INSERT INTO tbl_quotes (request_id, center_id, price, estimated_points, notes, expiration_date) 
                                       VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iidiss", $request_id, $center_id, $price, $points, $notes, $expiration);
                $stmt->execute();
            }
            
            $success_message = "Bulk request " . ($action === 'approve' ? 'approved' : 'rejected') . " successfully";
        } else {
            $error_message = "Error processing bulk request";
        }
    }
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

// Query for pickups
$pickup_query = "SELECT p.*, u.fullname, u.business_name, u.user_type 
                FROM tbl_pickups p 
                JOIN tbl_user u ON p.user_id = u.id 
                WHERE 1=1";

// Apply filters
if ($status_filter) {
    $pickup_query .= " AND p.current_status = '$status_filter'";
}
if ($type_filter) {
    $pickup_query .= " AND p.frequency = '$type_filter'";
}

$pickup_query .= " ORDER BY p.pickup_date ASC, p.pickup_time ASC";

$pickups = $conn->query($pickup_query);

// Query for bulk requests
$bulk_query = "SELECT b.*, u.fullname, u.business_name 
              FROM tbl_bulk_requests b 
              JOIN tbl_user u ON b.business_id = u.id 
              WHERE 1=1";

// Apply status filter
if ($filter === 'bulk' && $status_filter) {
    $bulk_query .= " AND b.status = '$status_filter'";
} elseif ($filter === 'bulk') {
    $bulk_query .= " AND b.status = 'pending'";
}

$bulk_query .= " ORDER BY b.created_at DESC";

$bulk_requests = $conn->query($bulk_query);

// Helper function to get status message
function getStatusMessage($status) {
    switch ($status) {
        case 'scheduled':
            return 'Your pickup has been scheduled';
        case 'in_transit':
            return 'Driver is en route to your location';
        case 'arrived':
            return 'Driver has arrived at your location';
        case 'completed':
            return 'Pickup has been completed successfully';
        case 'cancelled':
            return 'Pickup has been cancelled';
        default:
            return 'Your pickup status has been updated';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pickup Management - EcoLens</title>
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
                    <h1 class="text-3xl font-bold text-white mb-8">Pickup & Request Management</h1>
                    
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

                    <!-- Tabs -->
                    <div class="mb-8">
                        <div class="flex space-x-1">
                            <button onclick="switchTab('pickups')" id="pickups-tab" class="px-4 py-2 bg-white/10 text-white rounded-t-lg font-medium <?php echo ($filter !== 'bulk') ? 'active-tab' : ''; ?>">
                                Regular Pickups
                            </button>
                            <button onclick="switchTab('bulk')" id="bulk-tab" class="px-4 py-2 bg-white/5 text-white/70 rounded-t-lg font-medium <?php echo ($filter === 'bulk') ? 'active-tab' : ''; ?>">
                                Bulk Requests
                            </button>
                        </div>
                        <div class="h-0.5 bg-white/20"></div>
                    </div>

                    <!-- Regular Pickups Tab -->
                    <div id="pickups-content" class="tab-content <?php echo ($filter === 'bulk') ? 'hidden' : ''; ?>">
                        <!-- Filters -->
                        <div class="flex flex-wrap gap-3 mb-6">
                            <a href="?status=scheduled" class="px-3 py-1 bg-white/10 hover:bg-white/20 text-white rounded-full text-sm">
                                Scheduled
                            </a>
                            <a href="?status=in_transit" class="px-3 py-1 bg-white/10 hover:bg-white/20 text-white rounded-full text-sm">
                                In Transit
                            </a>
                            <a href="?status=arrived" class="px-3 py-1 bg-white/10 hover:bg-white/20 text-white rounded-full text-sm">
                                Arrived
                            </a>
                            <a href="?status=completed" class="px-3 py-1 bg-white/10 hover:bg-white/20 text-white rounded-full text-sm">
                                Completed
                            </a>
                            <a href="?type=weekly" class="px-3 py-1 bg-white/10 hover:bg-white/20 text-white rounded-full text-sm">
                                Weekly
                            </a>
                            <a href="?type=monthly" class="px-3 py-1 bg-white/10 hover:bg-white/20 text-white rounded-full text-sm">
                                Monthly
                            </a>
                            <a href="pickup_management.php" class="px-3 py-1 bg-[#436d2e]/50 hover:bg-[#436d2e] text-white rounded-full text-sm">
                                Clear Filters
                            </a>
                        </div>

                        <!-- Pickups List -->
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="text-left text-sm font-medium text-white border-b border-white/10">
                                            <th class="px-6 py-3">ID</th>
                                            <th class="px-6 py-3">User</th>
                                            <th class="px-6 py-3">Date & Time</th>
                                            <th class="px-6 py-3">Items</th>
                                            <th class="px-6 py-3">Status</th>
                                            <th class="px-6 py-3">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/10">
                                        <?php if ($pickups->num_rows == 0): ?>
                                            <tr>
                                                <td colspan="6" class="px-6 py-4 text-white/70 text-center">No pickup requests found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php while ($pickup = $pickups->fetch_assoc()): ?>
                                                <tr class="text-white/80 hover:bg-white/5">
                                                    <td class="px-6 py-4">#<?php echo $pickup['id']; ?></td>
                                                    <td class="px-6 py-4">
                                                        <?php echo $pickup['user_type'] === 'business' ? $pickup['business_name'] : $pickup['fullname']; ?>
                                                        <?php if ($pickup['recurring']): ?>
                                                            <span class="ml-1 px-1.5 py-0.5 bg-blue-600/20 text-blue-200 text-xs rounded-full"><?php echo ucfirst($pickup['frequency']); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <?php echo date('M d, Y', strtotime($pickup['pickup_date'])); ?><br>
                                                        <span class="text-sm text-white/50"><?php echo ucfirst($pickup['pickup_time']); ?></span>
                                                    </td>
                                                    <td class="px-6 py-4"><?php echo $pickup['items']; ?></td>
                                                    <td class="px-6 py-4">
                                                        <?php
                                                        $status_colors = [
                                                            'scheduled' => 'bg-yellow-600/20 text-yellow-200',
                                                            'in_transit' => 'bg-blue-600/20 text-blue-200',
                                                            'arrived' => 'bg-purple-600/20 text-purple-200',
                                                            'completed' => 'bg-green-600/20 text-green-200',
                                                            'cancelled' => 'bg-red-600/20 text-red-200'
                                                        ];
                                                        $status_color = $status_colors[$pickup['current_status']] ?? 'bg-gray-600/20 text-gray-200';
                                                        ?>
                                                        <span class="px-2 py-1 rounded-full text-xs <?php echo $status_color; ?>">
                                                            <?php echo ucfirst($pickup['current_status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <button onclick="openPickupModal(<?php echo htmlspecialchars(json_encode($pickup)); ?>)" 
                                                                class="text-[#436d2e] hover:text-white transition-all">
                                                            Manage
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Bulk Requests Tab -->
                    <div id="bulk-content" class="tab-content <?php echo ($filter !== 'bulk') ? 'hidden' : ''; ?>">
                        <!-- Filters -->
                        <div class="flex flex-wrap gap-3 mb-6">
                            <a href="?filter=bulk&status=pending" class="px-3 py-1 bg-white/10 hover:bg-white/20 text-white rounded-full text-sm">
                                Pending
                            </a>
                            <a href="?filter=bulk&status=approved" class="px-3 py-1 bg-white/10 hover:bg-white/20 text-white rounded-full text-sm">
                                Approved
                            </a>
                            <a href="?filter=bulk&status=rejected" class="px-3 py-1 bg-white/10 hover:bg-white/20 text-white rounded-full text-sm">
                                Rejected
                            </a>
                            <a href="?filter=bulk&status=completed" class="px-3 py-1 bg-white/10 hover:bg-white/20 text-white rounded-full text-sm">
                                Completed
                            </a>
                            <a href="?filter=bulk" class="px-3 py-1 bg-[#436d2e]/50 hover:bg-[#436d2e] text-white rounded-full text-sm">
                                Clear Filters
                            </a>
                        </div>

                        <!-- Bulk Requests List -->
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="text-left text-sm font-medium text-white border-b border-white/10">
                                            <th class="px-6 py-3">ID</th>
                                            <th class="px-6 py-3">Business</th>
                                            <th class="px-6 py-3">Request Type</th>
                                            <th class="px-6 py-3">Materials</th>
                                            <th class="px-6 py-3">Est. Quantity</th>
                                            <th class="px-6 py-3">Status</th>
                                            <th class="px-6 py-3">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/10">
                                        <?php if ($bulk_requests->num_rows == 0): ?>
                                            <tr>
                                                <td colspan="7" class="px-6 py-4 text-white/70 text-center">No bulk requests found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php while ($request = $bulk_requests->fetch_assoc()): ?>
                                                <tr class="text-white/80 hover:bg-white/5">
                                                    <td class="px-6 py-4">#<?php echo $request['id']; ?></td>
                                                    <td class="px-6 py-4"><?php echo $request['business_name']; ?></td>
                                                    <td class="px-6 py-4"><?php echo ucfirst($request['request_type']); ?></td>
                                                    <td class="px-6 py-4">
                                                        <?php 
                                                        $materials = explode(',', $request['material_types']);
                                                        foreach ($materials as $material) {
                                                            echo '<span class="inline-block px-1.5 py-0.5 bg-white/20 text-white text-xs rounded-full mr-1 mb-1">' . ucfirst($material) . '</span>';
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="px-6 py-4"><?php echo number_format($request['estimated_quantity']); ?> kg</td>
                                                    <td class="px-6 py-4">
                                                        <?php
                                                        $status_colors = [
                                                            'pending' => 'bg-yellow-600/20 text-yellow-200',
                                                            'approved' => 'bg-green-600/20 text-green-200',
                                                            'rejected' => 'bg-red-600/20 text-red-200',
                                                            'completed' => 'bg-blue-600/20 text-blue-200'
                                                        ];
                                                        $status_color = $status_colors[$request['status']] ?? 'bg-gray-600/20 text-gray-200';
                                                        ?>
                                                        <span class="px-2 py-1 rounded-full text-xs <?php echo $status_color; ?>">
                                                            <?php echo ucfirst($request['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <?php if ($request['status'] === 'pending'): ?>
                                                            <button onclick="openBulkModal(<?php echo htmlspecialchars(json_encode($request)); ?>)" 
                                                                    class="text-[#436d2e] hover:text-white transition-all">
                                                                Respond
                                                            </button>
                                                        <?php else: ?>
                                                            <button onclick="openBulkModal(<?php echo htmlspecialchars(json_encode($request)); ?>)" 
                                                                    class="text-[#436d2e] hover:text-white transition-all">
                                                                View Details
                                                            </button>
                                                        <?php endif; ?>
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
    </div>

    <!-- Pickup Modal -->
    <div id="pickup-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-[#1b1b1b] rounded-xl w-full max-w-2xl mx-4 overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white">Manage Pickup Request</h3>
                <button onclick="closePickupModal()" class="text-white/70 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-6">
                <form id="pickup-form" method="POST">
                    <input type="hidden" name="pickup_id" id="pickup-id">
                    
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-white/70 mb-1">Customer</label>
                            <p id="pickup-customer" class="text-white font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-white/70 mb-1">Contact</label>
                            <p id="pickup-contact" class="text-white font-medium"></p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-white/70 mb-1">Date & Time</label>
                            <p id="pickup-datetime" class="text-white font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-white/70 mb-1">Address</label>
                            <p id="pickup-address" class="text-white font-medium"></p>
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-white/70 mb-1">Items</label>
                        <p id="pickup-items" class="text-white font-medium"></p>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-white/70 mb-1">Update Status</label>
                        <select name="status" id="pickup-status" required
                                class="w-full px-4 py-3 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                            <option value="scheduled" class="text-black">Scheduled</option>
                            <option value="in_transit" class="text-black">In Transit</option>
                            <option value="arrived" class="text-black">Arrived</option>
                            <option value="completed" class="text-black">Completed</option>
                            <option value="cancelled" class="text-black">Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-white/70 mb-1">Assign Vehicle (Optional)</label>
                            <input type="text" name="vehicle" id="pickup-vehicle"
                                   class="w-full px-4 py-3 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                        </div>
                        <div>
                            <label class="block text-white/70 mb-1">Driver Notes (Optional)</label>
                            <input type="text" name="notes" id="pickup-notes"
                                   class="w-full px-4 py-3 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="button" onclick="closePickupModal()" class="px-4 py-2 text-white/70 hover:text-white mr-3">
                            Cancel
                        </button>
                        <button type="submit" name="update_pickup" class="px-6 py-2 bg-[#436d2e] text-white rounded-lg hover:bg-opacity-90">
                            Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bulk Request Modal -->
    <div id="bulk-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-[#1b1b1b] rounded-xl w-full max-w-2xl mx-4 overflow-hidden max-h-[90vh] flex flex-col">
            <div class="px-6 py-3 border-b border-white/10 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white">Bulk Recycling Request</h3>
                <button onclick="closeBulkModal()" class="text-white/70 hover:text-white">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="p-4 overflow-y-auto">
                <form id="bulk-form" method="POST">
                    <input type="hidden" name="request_id" id="bulk-id">
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-white/70 mb-1 text-sm">Business</label>
                            <p id="bulk-business" class="text-white font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-white/70 mb-1 text-sm">Request Type</label>
                            <p id="bulk-type" class="text-white font-medium"></p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-white/70 mb-1 text-sm">Preferred Date</label>
                            <p id="bulk-date" class="text-white font-medium"></p>
                        </div>
                        <div>
                            <label class="block text-white/70 mb-1 text-sm">Est. Quantity</label>
                            <p id="bulk-quantity" class="text-white font-medium"></p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-white/70 mb-1 text-sm">Materials</label>
                        <p id="bulk-materials" class="text-white font-medium"></p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-white/70 mb-1 text-sm">Address</label>
                        <p id="bulk-address" class="text-white font-medium"></p>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-white/70 mb-1 text-sm">Additional Notes</label>
                        <p id="bulk-notes" class="text-white font-medium"></p>
                    </div>
                    
                    <div id="response-fields" class="border-t border-white/10 pt-4 mt-4">
                        <h4 class="text-lg font-medium text-white mb-3">Your Response</h4>
                        
                        <div class="mb-3">
                            <label class="block text-white/70 mb-1 text-sm">Price Quote (₱)</label>
                            <input type="number" name="price" id="bulk-price" step="0.01"
                                   class="w-full px-3 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                        </div>
                        
                        <div class="mb-3">
                            <label class="block text-white/70 mb-1 text-sm">Estimated Points</label>
                            <input type="number" name="points" id="bulk-points"
                                   class="w-full px-3 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-white/70 mb-1 text-sm">Response Notes</label>
                            <textarea name="notes" id="bulk-response-notes" rows="2"
                                     class="w-full px-3 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]"></textarea>
                        </div>
                        
                        <div class="flex justify-end">
                            <div id="approval-buttons">
                                <button type="button" onclick="closeBulkModal()" class="px-3 py-1.5 text-white/70 hover:text-white mr-2">
                                    Cancel
                                </button>
                                <button type="submit" name="respond_bulk" class="px-4 py-1.5 bg-red-600/80 text-white rounded-lg hover:bg-red-600 mr-2" onclick="setAction('reject')">
                                    <i class="fas fa-times mr-1"></i> Reject
                                </button>
                                <button type="submit" name="respond_bulk" class="px-4 py-1.5 bg-[#436d2e] text-white rounded-lg hover:bg-opacity-90" onclick="setAction('approve')">
                                    <i class="fas fa-check mr-1"></i> Approve
                                </button>
                            </div>
                            <div id="view-only-buttons" class="hidden">
                                <button type="button" onclick="closeBulkModal()" class="px-4 py-1.5 bg-white/20 text-white rounded-lg hover:bg-white/30">
                                    Close
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="action" id="action-input">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            // Update URL with filter parameter
            let url = new URL(window.location.href);
            if (tab === 'bulk') {
                url.searchParams.set('filter', 'bulk');
            } else {
                url.searchParams.delete('filter');
            }
            window.history.replaceState({}, '', url);
            
            // Show/hide content
            document.getElementById('pickups-content').classList.toggle('hidden', tab === 'bulk');
            document.getElementById('bulk-content').classList.toggle('hidden', tab === 'pickups');
            
            // Update tab styling
            document.getElementById('pickups-tab').classList.toggle('bg-white/10', tab === 'pickups');
            document.getElementById('pickups-tab').classList.toggle('bg-white/5', tab === 'bulk');
            document.getElementById('pickups-tab').classList.toggle('text-white', tab === 'pickups');
            document.getElementById('pickups-tab').classList.toggle('text-white/70', tab === 'bulk');
            
            document.getElementById('bulk-tab').classList.toggle('bg-white/10', tab === 'bulk');
            document.getElementById('bulk-tab').classList.toggle('bg-white/5', tab === 'pickups');
            document.getElementById('bulk-tab').classList.toggle('text-white', tab === 'bulk');
            document.getElementById('bulk-tab').classList.toggle('text-white/70', tab === 'pickups');
        }

        function openPickupModal(pickup) {
            document.getElementById('pickup-id').value = pickup.id;
            document.getElementById('pickup-customer').textContent = pickup.user_type === 'business' ? pickup.business_name : pickup.fullname;
            document.getElementById('pickup-contact').textContent = 'Contact info here'; // Not included in data, would need to be added
            document.getElementById('pickup-datetime').textContent = `${formatDate(pickup.pickup_date)} (${pickup.pickup_time})`;
            document.getElementById('pickup-address').textContent = pickup.address;
            document.getElementById('pickup-items').textContent = pickup.items;
            document.getElementById('pickup-status').value = pickup.current_status;
            document.getElementById('pickup-vehicle').value = pickup.vehicle_assigned || '';
            document.getElementById('pickup-notes').value = pickup.driver_notes || '';
            
            document.getElementById('pickup-modal').classList.remove('hidden');
        }
        
        function closePickupModal() {
            document.getElementById('pickup-modal').classList.add('hidden');
        }
        
        function openBulkModal(request) {
            document.getElementById('bulk-id').value = request.id;
            document.getElementById('bulk-business').textContent = request.business_name;
            document.getElementById('bulk-type').textContent = request.request_type.charAt(0).toUpperCase() + request.request_type.slice(1);
            document.getElementById('bulk-date').textContent = formatDate(request.preferred_date);
            document.getElementById('bulk-quantity').textContent = `${request.estimated_quantity} kg`;
            
            // Format materials as badges
            let materialsHtml = '';
            const materials = request.material_types.split(',');
            materials.forEach(material => {
                materialsHtml += `<span class="inline-block px-2 py-0.5 bg-white/20 text-white text-sm rounded-full mr-1 mb-1">${material.charAt(0).toUpperCase() + material.slice(1)}</span>`;
            });
            document.getElementById('bulk-materials').innerHTML = materialsHtml;
            
            document.getElementById('bulk-address').textContent = request.address;
            document.getElementById('bulk-notes').textContent = request.additional_notes || 'No additional notes';
            
            // Show/hide buttons based on status
            if (request.status === 'pending') {
                document.getElementById('response-fields').classList.remove('hidden');
                document.getElementById('approval-buttons').classList.remove('hidden');
                document.getElementById('view-only-buttons').classList.add('hidden');
            } else {
                document.getElementById('response-fields').classList.add('hidden');
                document.getElementById('approval-buttons').classList.add('hidden');
                document.getElementById('view-only-buttons').classList.remove('hidden');
            }
            
            document.getElementById('bulk-modal').classList.remove('hidden');
        }
        
        function closeBulkModal() {
            document.getElementById('bulk-modal').classList.add('hidden');
        }
        
        function setAction(action) {
            document.getElementById('action-input').value = action;
        }
        
        function formatDate(dateString) {
            const options = { year: 'numeric', month: 'short', day: 'numeric' };
            return new Date(dateString).toLocaleDateString('en-US', options);
        }
    </script>
</body>
</html>
