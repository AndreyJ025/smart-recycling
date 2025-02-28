<?php
session_start();
require_once '../database.php';

// Check if user is logged in and is a business
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = "";
$error_message = "";

// Process quote actions (accept/reject)
if (isset($_POST['quote_action'])) {
    $quote_id = filter_input(INPUT_POST, 'quote_id', FILTER_VALIDATE_INT);
    $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
    
    if ($quote_id && ($action === 'accept' || $action === 'reject')) {
        // First verify this quote belongs to the business
        $verify_query = "SELECT q.id 
                         FROM tbl_quotes q 
                         JOIN tbl_bulk_requests b ON q.request_id = b.id 
                         WHERE q.id = ? AND b.business_id = ?";
        $stmt = $conn->prepare($verify_query);
        $stmt->bind_param("ii", $quote_id, $user_id);
        $stmt->execute();
        $verify_result = $stmt->get_result();
        
        if ($verify_result->num_rows > 0) {
            // Update quote status based on the action (accept/reject)
            $status = ($action === 'accept') ? 'accepted' : 'rejected';
            $update_query = "UPDATE tbl_quotes SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $status, $quote_id);
            
            if ($stmt->execute()) {
                // If accepting, reject all other quotes for this request
                if ($action === 'accept') {
                    // Get the request ID for this quote
                    $request_query = "SELECT request_id FROM tbl_quotes WHERE id = ?";
                    $stmt = $conn->prepare($request_query);
                    $stmt->bind_param("i", $quote_id);
                    $stmt->execute();
                    $request_result = $stmt->get_result();
                    $request_data = $request_result->fetch_assoc();
                    $request_id = $request_data['request_id'];
                    
                    // Reject all other quotes for this request
                    $reject_others = "UPDATE tbl_quotes SET status = 'rejected' 
                                      WHERE request_id = ? AND id != ? AND status = 'pending'";
                    $stmt = $conn->prepare($reject_others);
                    $stmt->bind_param("ii", $request_id, $quote_id);
                    $stmt->execute();
                    
                    // Update the request status to 'approved' when a quote is accepted
                    $update_request = "UPDATE tbl_bulk_requests SET status = 'approved' WHERE id = ?";
                    $stmt = $conn->prepare($update_request);
                    $stmt->bind_param("i", $request_id);
                    $stmt->execute();
                }
                
                $success_message = "Quote has been " . ($action === 'accept' ? 'accepted' : 'rejected') . " successfully!";
            } else {
                $error_message = "Error updating quote: " . $conn->error;
            }
        } else {
            $error_message = "You don't have permission to perform this action.";
        }
        $stmt->close();
    }
}

// Filter settings
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$status_condition = ($status_filter !== 'all') ? "AND q.status = '$status_filter'" : "";

// Get all quotes for this business's requests
$quotes_query = "SELECT q.*, b.request_type, b.material_types, b.estimated_quantity, b.preferred_date,
                 s.name AS center_name, s.address AS center_location
                 FROM tbl_quotes q
                 JOIN tbl_bulk_requests b ON q.request_id = b.id
                 JOIN tbl_sortation_centers s ON q.center_id = s.id
                 WHERE b.business_id = ? $status_condition
                 ORDER BY q.created_at DESC";
$stmt = $conn->prepare($quotes_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$quotes_result = $stmt->get_result();
$quotes = [];
while ($row = $quotes_result->fetch_assoc()) {
    $quotes[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Quotes - EcoLens</title>
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

    <!-- Navigation -->
    <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <img src="../assets/logo.png" alt="EcoLens Logo" class="h-8">
                    <span class="text-xl font-bold text-white">EcoLens</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-white hover:text-[#436d2e] transition-all">
                        <i class="fa-solid fa-arrow-left mr-2"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="bg-overlay pt-24 pb-12 px-4">
        <div class="relative z-10">
            <div class="container mx-auto px-4 md:px-6">
                <div class="mb-6">
                    <h1 class="text-3xl font-bold text-white mb-2">Recycling Service Quotes</h1>
                    <p class="text-green-100">View and manage quotes for your bulk recycling requests</p>
                </div>
                
                <?php if ($success_message): ?>
                <div class="bg-green-600/80 backdrop-blur-sm text-white p-4 rounded-lg mb-6">
                    <p><?php echo $success_message; ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                <div class="bg-red-600/80 backdrop-blur-sm text-white p-4 rounded-lg mb-6">
                    <p><?php echo $error_message; ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Filter controls -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 space-y-4 md:space-y-0">
                    <a href="bulk-request.php" class="bg-green-700 hover:bg-green-800 text-white px-4 py-2 rounded-lg">
                        <i class="fas fa-plus mr-2"></i> New Bulk Request
                    </a>
                    
                    <div class="flex items-center space-x-4">
                        <span class="text-white">Filter by status:</span>
                        <div class="flex space-x-2">
                            <a href="?status=all" class="<?php echo $status_filter === 'all' ? 'bg-green-700' : 'bg-white/10'; ?> px-3 py-1 rounded-lg text-white text-sm">
                                All
                            </a>
                            <a href="?status=pending" class="<?php echo $status_filter === 'pending' ? 'bg-yellow-600' : 'bg-white/10'; ?> px-3 py-1 rounded-lg text-white text-sm">
                                Pending
                            </a>
                            <a href="?status=accepted" class="<?php echo $status_filter === 'accepted' ? 'bg-green-600' : 'bg-white/10'; ?> px-3 py-1 rounded-lg text-white text-sm">
                                Accepted
                            </a>
                            <a href="?status=rejected" class="<?php echo $status_filter === 'rejected' ? 'bg-red-600' : 'bg-white/10'; ?> px-3 py-1 rounded-lg text-white text-sm">
                                Rejected
                            </a>
                        </div>
                    </div>
                </div>
                
                <?php if (empty($quotes)): ?>
                    <div class="bg-white/5 backdrop-blur-sm p-8 rounded-xl text-center">
                        <h3 class="text-xl font-medium text-white mb-2">No quotes found</h3>
                        <p class="text-green-100">
                            <?php if ($status_filter !== 'all'): ?>
                                No <?php echo $status_filter; ?> quotes found. Try a different filter or
                            <?php endif; ?>
                            <a href="bulk-request.php" class="text-green-300 underline">create a new bulk recycling request</a>.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($quotes as $quote): ?>
                            <div class="bg-white/5 backdrop-blur-sm rounded-xl overflow-hidden border border-white/10">
                                <div class="p-6">
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="text-xl font-semibold text-white">
                                                <?php echo htmlspecialchars($quote['center_name']); ?>
                                            </h3>
                                            <p class="text-sm text-green-100"><?php echo htmlspecialchars($quote['center_location']); ?></p>
                                        </div>
                                        <div class="flex items-center">
                                            <?php if ($quote['status'] === 'pending'): ?>
                                                <span class="bg-yellow-600/80 text-white text-xs px-2 py-1 rounded">Pending</span>
                                            <?php elseif ($quote['status'] === 'accepted'): ?>
                                                <span class="bg-green-600/80 text-white text-xs px-2 py-1 rounded">Accepted</span>
                                            <?php elseif ($quote['status'] === 'rejected'): ?>
                                                <span class="bg-red-600/80 text-white text-xs px-2 py-1 rounded">Rejected</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <p class="text-sm text-green-200">Price</p>
                                            <p class="text-xl font-semibold text-white">₱<?php echo number_format($quote['price'], 2); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-green-200">Estimated Points</p>
                                            <p class="text-xl font-semibold text-white"><?php echo number_format($quote['estimated_points']); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-green-200">Expiration</p>
                                            <p class="text-white"><?php echo date('M j, Y', strtotime($quote['expiration_date'])); ?></p>
                                        </div>
                                        <div>
                                            <p class="text-sm text-green-200">Created</p>
                                            <p class="text-white"><?php echo date('M j, Y', strtotime($quote['created_at'])); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="border-t border-white/10 pt-4 mb-4">
                                        <p class="text-sm text-green-200 mb-1">Request Details</p>
                                        <p class="text-white"><span class="font-medium">Type:</span> <?php echo ucfirst($quote['request_type']); ?></p>
                                        <p class="text-white"><span class="font-medium">Materials:</span> <?php echo str_replace(',', ', ', ucfirst($quote['material_types'])); ?></p>
                                        <p class="text-white"><span class="font-medium">Quantity:</span> <?php echo number_format($quote['estimated_quantity']); ?> kg</p>
                                        <p class="text-white"><span class="font-medium">Date:</span> <?php echo date('M j, Y', strtotime($quote['preferred_date'])); ?></p>
                                    </div>
                                    
                                    <?php if (!empty($quote['notes'])): ?>
                                        <div class="bg-white/10 p-3 rounded-lg mb-4">
                                            <p class="text-sm text-green-100"><?php echo htmlspecialchars($quote['notes']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($quote['status'] === 'pending'): ?>
                                        <div class="grid grid-cols-2 gap-3">
                                            <form method="POST">
                                                <input type="hidden" name="quote_id" value="<?php echo $quote['id']; ?>">
                                                <input type="hidden" name="action" value="accept">
                                                <button type="submit" name="quote_action" class="w-full bg-green-700 hover:bg-green-800 text-white py-2 rounded-lg transition">
                                                    Accept Quote
                                                </button>
                                            </form>
                                            <form method="POST">
                                                <input type="hidden" name="quote_id" value="<?php echo $quote['id']; ?>">
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" name="quote_action" class="w-full bg-white/10 hover:bg-white/20 text-white py-2 rounded-lg transition">
                                                    Reject Quote
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="bg-[#1b1b1b] py-6">
        <div class="container mx-auto px-4">
            <p class="text-center text-white/60">&copy; <?php echo date('Y'); ?> EcoLens. All rights reserved.</p>
        </div>
    </footer>

<?php
$conn->close();
?>