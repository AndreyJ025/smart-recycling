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
    $preferred_date = filter_input(INPUT_POST, 'preferred_date', FILTER_SANITIZE_STRING);
    $estimated_quantity = filter_input(INPUT_POST, 'estimated_quantity', FILTER_VALIDATE_INT);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $additional_notes = filter_input(INPUT_POST, 'additional_notes', FILTER_SANITIZE_STRING);
    
    // Process material types array into comma-separated string
    $material_types = isset($_POST['materials']) ? implode(',', $_POST['materials']) : '';
    
    // Set request type to pickup
    $request_type = 'pickup';
    
    // Validate inputs
    if (empty($preferred_date) || empty($estimated_quantity) || empty($address) || empty($material_types)) {
        $error_message = "All required fields must be completed";
    } else {
        // Insert into database
        $query = "INSERT INTO tbl_bulk_requests (business_id, request_type, material_types, estimated_quantity, preferred_date, address, additional_notes) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issssss", $user_id, $request_type, $material_types, $estimated_quantity, $preferred_date, $address, $additional_notes);
        
        if ($stmt->execute()) {
            $success_message = "Your bulk pickup request has been submitted successfully!";
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
    <title>Bulk Pickup Request - EcoLens</title>
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
                    <h1 class="text-3xl font-bold text-white mb-2">Bulk Pickup Request</h1>
                    <p class="text-white/70 mb-6">Schedule a pickup for your business's recyclable materials</p>
                    
                    <?php if ($success_message): ?>
                    <div class="bg-green-600/80 backdrop-blur-sm text-white p-4 rounded-lg mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-2xl mr-3"></i>
                            <div>
                                <p class="font-medium"><?php echo $success_message; ?></p>
                                <p class="text-sm mt-1">You'll receive a confirmation when a recycling center accepts your request.</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                    <div class="bg-red-600/80 backdrop-blur-sm text-white p-4 rounded-lg mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-2xl mr-3"></i>
                            <p><?php echo $error_message; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                        <form method="POST" class="space-y-6">
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-white">Preferred Pickup Date</label>
                                    <div class="relative">
                                        <input type="date" name="preferred_date" required
                                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                               class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#436d2e]">
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-white/50">
                                        </div>
                                    </div>
                                    <p class="text-xs text-white/60 mt-1">Schedule at least 1 day in advance</p>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-white">Estimated Quantity (kg)</label>
                                    <div class="relative">
                                        <input type="number" name="estimated_quantity" required min="1"
                                               placeholder="Enter weight in kilograms"
                                               class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#436d2e]">
                                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-white/50">
                                            <i class="fas fa-weight-hanging"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Material types -->
                            <div>
                                <label class="block text-sm font-medium text-white mb-2">Material Types</label>
                                <p class="text-xs text-white/60 mb-3">Select all materials that will be included</p>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                    <label class="flex items-center space-x-2 bg-white/10 px-4 py-3 rounded-lg hover:bg-white/20 transition-all cursor-pointer">
                                        <input type="checkbox" name="materials[]" value="plastic" class="rounded text-[#436d2e]">
                                        <span class="text-white"><i class="fas fa-wine-bottle mr-2"></i> Plastic</span>
                                    </label>
                                    <label class="flex items-center space-x-2 bg-white/10 px-4 py-3 rounded-lg hover:bg-white/20 transition-all cursor-pointer">
                                        <input type="checkbox" name="materials[]" value="paper" class="rounded text-[#436d2e]">
                                        <span class="text-white"><i class="fas fa-newspaper mr-2"></i> Paper</span>
                                    </label>
                                    <label class="flex items-center space-x-2 bg-white/10 px-4 py-3 rounded-lg hover:bg-white/20 transition-all cursor-pointer">
                                        <input type="checkbox" name="materials[]" value="glass" class="rounded text-[#436d2e]">
                                        <span class="text-white"><i class="fas fa-glass-martini mr-2"></i> Glass</span>
                                    </label>
                                    <label class="flex items-center space-x-2 bg-white/10 px-4 py-3 rounded-lg hover:bg-white/20 transition-all cursor-pointer">
                                        <input type="checkbox" name="materials[]" value="metal" class="rounded text-[#436d2e]">
                                        <span class="text-white"><i class="fas fa-bolt mr-2"></i> Metal</span>
                                    </label>
                                    <label class="flex items-center space-x-2 bg-white/10 px-4 py-3 rounded-lg hover:bg-white/20 transition-all cursor-pointer">
                                        <input type="checkbox" name="materials[]" value="electronics" class="rounded text-[#436d2e]">
                                        <span class="text-white"><i class="fas fa-laptop mr-2"></i> Electronics</span>
                                    </label>
                                    <label class="flex items-center space-x-2 bg-white/10 px-4 py-3 rounded-lg hover:bg-white/20 transition-all cursor-pointer">
                                        <input type="checkbox" name="materials[]" value="other" class="rounded text-[#436d2e]">
                                        <span class="text-white"><i class="fas fa-boxes mr-2"></i> Other</span>
                                    </label>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-white">Pickup Address</label>
                                <div class="relative">
                                    <textarea name="address" required rows="2" 
                                             placeholder="Enter the pickup location address"
                                             class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#436d2e]"><?php echo htmlspecialchars($business_address); ?></textarea>
                                    <div class="absolute right-3 top-6 pointer-events-none text-white/50">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                </div>
                                <p class="text-xs text-white/60 mt-1">Please provide complete address details for accurate pickup</p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-white">Additional Notes</label>
                                <textarea name="additional_notes" rows="3" 
                                          placeholder="Special instructions, access information, or details about materials"
                                          class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-[#436d2e]"></textarea>
                            </div>

                            <div class="bg-white/10 p-4 rounded-lg">
                                <h4 class="font-medium text-white flex items-center"><i class="fas fa-info-circle mr-2"></i> What happens next?</h4>
                                <ul class="mt-2 text-sm text-white/80 space-y-2">
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-green-400 mt-1 mr-2"></i>
                                        <span>Your request will be sent to partner recycling centers</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-green-400 mt-1 mr-2"></i>
                                        <span>You'll receive quotes based on your materials and quantity</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-green-400 mt-1 mr-2"></i>
                                        <span>Once you accept a quote, the pickup will be scheduled</span>
                                    </li>
                                </ul>
                            </div>

                            <button type="submit" name="request_bulk" 
                                    class="w-full bg-[#436d2e] text-white px-6 py-4 rounded-lg font-semibold hover:bg-opacity-90 transition-all flex items-center justify-center">
                                <i class="fas fa-truck mr-2"></i>
                                Schedule Pickup Request
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>