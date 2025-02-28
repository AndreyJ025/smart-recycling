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

// Process bulk request form submission
if (isset($_POST['request_bulk'])) {
    // Validate and sanitize input
    $request_type = filter_input(INPUT_POST, 'request_type', FILTER_SANITIZE_STRING);
    $preferred_date = filter_input(INPUT_POST, 'preferred_date', FILTER_SANITIZE_STRING);
    $estimated_quantity = filter_input(INPUT_POST, 'estimated_quantity', FILTER_VALIDATE_INT);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $additional_notes = filter_input(INPUT_POST, 'additional_notes', FILTER_SANITIZE_STRING);
    
    // Process material types array into comma-separated string
    $material_types = isset($_POST['materials']) ? implode(',', $_POST['materials']) : '';
    
    // Validate inputs
    if (empty($request_type) || empty($preferred_date) || empty($estimated_quantity) || empty($address) || empty($material_types)) {
        $error_message = "All required fields must be completed";
    } else {
        // Insert into database
        $query = "INSERT INTO tbl_bulk_requests (business_id, request_type, material_types, estimated_quantity, preferred_date, address, additional_notes) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issssss", $user_id, $request_type, $material_types, $estimated_quantity, $preferred_date, $address, $additional_notes);
        
        if ($stmt->execute()) {
            $success_message = "Your bulk recycling request has been submitted successfully!";
        } else {
            $error_message = "Error submitting request: " . $conn->error;
        }
        $stmt->close();
    }
}

// Get business address for prefill
$address_query = "SELECT address FROM tbl_user WHERE id = ?";
$stmt = $conn->prepare($address_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user_data = $result->fetch_assoc();
$business_address = $user_data['address'] ?? '';
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Recycling Request - EcoLens</title>
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
                <div class="max-w-4xl mx-auto">
                    <h1 class="text-3xl font-bold text-white mb-6">Business Bulk Recycling Request</h1>
                    
                    <?php if ($success_message): ?>
                    <div class="bg-green-600/80 backdrop-blur-sm text-white p-4 rounded-lg mb-6">
                        <?php echo $success_message; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                    <div class="bg-red-600/80 backdrop-blur-sm text-white p-4 rounded-lg mb-6">
                        <?php echo $error_message; ?>
                    </div>
                    <?php endif; ?>

                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                        <form method="POST" class="space-y-6">
                            <!-- Form content from your original file -->
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-white">Request Type</label>
                                    <select name="request_type" required
                                            class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#436d2e]">
                                        <option value="pickup" class="text-black">Pickup (We come to you)</option>
                                        <option value="drop-off" class="text-black">Drop-off (You deliver to center)</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-white">Preferred Date</label>
                                    <input type="date" name="preferred_date" required
                                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                           class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#436d2e]">
                                </div>
                            </div>

                            <!-- Material types -->
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Material Types</label>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" name="materials[]" value="plastic" class="rounded text-[#436d2e]">
                                        <span class="text-white">Plastic</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" name="materials[]" value="paper" class="rounded text-[#436d2e]">
                                        <span class="text-white">Paper</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" name="materials[]" value="glass" class="rounded text-[#436d2e]">
                                        <span class="text-white">Glass</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" name="materials[]" value="metal" class="rounded text-[#436d2e]">
                                        <span class="text-white">Metal</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" name="materials[]" value="electronics" class="rounded text-[#436d2e]">
                                        <span class="text-white">Electronics</span>
                                    </label>
                                </div>
                            </div>

                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-white">Estimated Quantity (kg)</label>
                                    <input type="number" name="estimated_quantity" required min="1"
                                           class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#436d2e]">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-white">Address</label>
                                    <input type="text" name="address" required
                                           value="<?php echo htmlspecialchars($business_address); ?>"
                                           class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#436d2e]">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-white">Additional Notes</label>
                                <textarea name="additional_notes" rows="3"
                                          class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#436d2e]"></textarea>
                            </div>

                            <button type="submit" name="request_bulk" 
                                    class="w-full bg-[#436d2e] text-white px-6 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-all">
                                Submit Request
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>