<?php
// Initialize session
session_start();

// Check if user is logged in and is a business
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'business') {
    header("Location: ../auth/login.php");
    exit();
}

// Include database connection
require_once '../database.php';

// Get business user data
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM tbl_user WHERE id = ? AND user_type = 'business'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $business = $result->fetch_assoc();
    $business_name = $business['business_name'];
    $business_email = $business['username']; // Using username as email
    $contact_person = $business['fullname']; // Using fullname as contact person
    $phone = $business['contact_number'];
    $address = $business['address'];
    $industry = isset($business['industry']) ? $business['industry'] : 'Other'; // Add default if missing
    $registration_number = isset($business['registration_number']) ? $business['registration_number'] : 'N/A'; // Add default if missing
} else {
    // Handle error - redirect to dashboard
    header("Location: dashboard.php");
    exit();
}

// Handle form submission for profile update
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Get form data
        $new_business_name = $_POST['business_name'];
        $new_contact_person = $_POST['contact_person'];
        $new_phone = $_POST['phone'];
        $new_address = $_POST['address'];
        $new_industry = $_POST['industry'];
        
        // Update profile in database - using tbl_user table instead of businesses
        $update_query = "UPDATE tbl_user SET 
                        business_name = ?, 
                        fullname = ?, 
                        contact_number = ?, 
                        address = ?, 
                        industry = ? 
                        WHERE id = ? AND user_type = 'business'";
        
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param(
            "sssssi",
            $new_business_name,
            $new_contact_person,
            $new_phone,
            $new_address,
            $new_industry,
            $user_id
        );
        
        if ($update_stmt->execute()) {
            $success_message = "Profile updated successfully!";
            
            // Update session variables if needed
            $_SESSION['business_name'] = $new_business_name;
            
            // Refresh the page data
            $business_name = $new_business_name;
            $contact_person = $new_contact_person;
            $phone = $new_phone;
            $address = $new_address;
            $industry = $new_industry;
        } else {
            $error_message = "Error updating profile: " . $conn->error;
        }
    } else if (isset($_POST['update_password'])) {
        // Handle password update
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // First get the user record from the tbl_user table (not users)
        $user_query = "SELECT password FROM tbl_user WHERE id = ?";
        $user_stmt = $conn->prepare($user_query);
        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user = $user_result->fetch_assoc();
        
        if ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match";
        } else if (!password_verify($current_password, $user['password'])) {
            $error_message = "Current password is incorrect";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_query = "UPDATE tbl_user SET password = ? WHERE id = ?";
            $password_stmt = $conn->prepare($password_query);
            $password_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($password_stmt->execute()) {
                $success_message = "Password updated successfully!";
            } else {
                $error_message = "Error updating password: " . $conn->error;
            }
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Business Profile - EcoLens</title>
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
                    <div class="hidden md:flex items-center space-x-6">
                        <a href="dashboard.php" class="text-white hover:text-[#436d2e] transition-all">
                            <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                        </a>
                        <a href="profile.php" class="text-white hover:text-[#436d2e] transition-all font-medium">
                            <i class="fas fa-user mr-1"></i> Profile
                        </a>
                        <a href="../index.php" class="text-white hover:text-[#436d2e] transition-all">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </a>
                    </div>
                    <div class="md:hidden">
                        <button id="mobile-menu-button" class="text-white hover:text-[#436d2e] transition-all">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                    </div>
                </div>
                <!-- Mobile menu (hidden by default) -->
                <div id="mobile-menu" class="hidden pt-4 pb-2">
                    <a href="dashboard.php" class="block py-2 text-white hover:text-[#436d2e]">
                        <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                    </a>
                    <a href="profile.php" class="block py-2 text-white hover:text-[#436d2e]">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                    <a href="../auth/logout.php" class="block py-2 text-white hover:text-[#436d2e]">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main content with proper padding for fixed navbar -->
        <main class="bg-overlay pt-24 pb-12 px-4">
            <div class="relative z-10">
                <div class="container mx-auto px-4 md:px-6 max-w-4xl">
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-white mb-2">Business Profile</h1>
                        <p class="text-green-100">Manage your business information and account settings</p>
                    </div>
                    
                    <?php if (!empty($success_message)): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p><?php echo $success_message; ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error_message)): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p><?php echo $error_message; ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Profile Information Card -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 mb-8">
                        <h2 class="text-2xl font-semibold text-white mb-6">Business Information</h2>
                        
                        <form action="profile.php" method="POST">
                            <div class="grid grid-cols-1 gap-6 mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="business_name" class="block text-green-100 mb-1">Business Name</label>
                                        <input type="text" id="business_name" name="business_name" value="<?php echo htmlspecialchars($business_name); ?>" 
                                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                                    </div>
                                    
                                    <div>
                                        <label for="email" class="block text-green-100 mb-1">Email Address</label>
                                        <input type="email" id="email" value="<?php echo htmlspecialchars($business_email); ?>" disabled
                                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-gray-400 focus:outline-none cursor-not-allowed">
                                        <p class="text-xs text-green-200 mt-1">Email cannot be changed. Contact support for assistance.</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="contact_person" class="block text-green-100 mb-1">Contact Person</label>
                                        <input type="text" id="contact_person" name="contact_person" value="<?php echo htmlspecialchars($contact_person); ?>" 
                                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                                    </div>
                                    
                                    <div>
                                        <label for="phone" class="block text-green-100 mb-1">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" 
                                            class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                                    </div>
                                </div>

                                <div>
                                    <label for="address" class="block text-green-100 mb-1">Business Address</label>
                                    <textarea id="address" name="address" rows="2" 
                                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500"><?php echo htmlspecialchars($address); ?></textarea>
                                </div>
                                
                                <div>
                                    <label for="industry" class="block text-green-100 mb-1">Industry</label>
                                    <select id="industry" name="industry" 
                                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                                        <option value="Manufacturing" class="text-black" <?php echo ($industry == 'Manufacturing') ? 'selected' : ''; ?>>Manufacturing</option>
                                        <option value="Retail" class="text-black" <?php echo ($industry == 'Retail') ? 'selected' : ''; ?>>Retail</option>
                                        <option value="Food & Beverage" class="text-black" <?php echo ($industry == 'Food & Beverage') ? 'selected' : ''; ?>>Food & Beverage</option>
                                        <option value="Healthcare" class="text-black" <?php echo ($industry == 'Healthcare') ? 'selected' : ''; ?>>Healthcare</option>
                                        <option value="Education" class="text-black" <?php echo ($industry == 'Education') ? 'selected' : ''; ?>>Education</option>
                                        <option value="Technology" class="text-black" <?php echo ($industry == 'Technology') ? 'selected' : ''; ?>>Technology</option>
                                        <option value="Hospitality" class="text-black" <?php echo ($industry == 'Hospitality') ? 'selected' : ''; ?>>Hospitality</option>
                                        <option value="Other" class="text-black" <?php echo ($industry == 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" name="update_profile" class="bg-[#436d2e] hover:bg-[#365a25] text-white font-medium rounded-lg px-6 py-2.5 transition-all">
                                    Update Profile
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Password Change Card -->
                    <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6">
                        <h2 class="text-2xl font-semibold text-white mb-6">Change Password</h2>
                        
                        <form action="profile.php" method="POST">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div class="md:col-span-2">
                                    <label for="current_password" class="block text-green-100 mb-1">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" required
                                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                                </div>
                                
                                <div>
                                    <label for="new_password" class="block text-green-100 mb-1">New Password</label>
                                    <input type="password" id="new_password" name="new_password" required
                                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                                </div>
                                
                                <div>
                                    <label for="confirm_password" class="block text-green-100 mb-1">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required
                                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                                </div>
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" name="update_password" class="bg-[#436d2e] hover:bg-[#365a25] text-white font-medium rounded-lg px-6 py-2.5 transition-all">
                                    Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>

        <footer class="bg-[#1b1b1b] py-6">
            <div class="container mx-auto px-4">
                <div class="text-center text-gray-400 text-sm">
                    <p>&copy; <?php echo date('Y'); ?> EcoLens. All rights reserved.</p>
                </div>
            </div>
        </footer>
        
        <script>
            // Toggle mobile menu
            document.getElementById('mobile-menu-button').addEventListener('click', function() {
                const mobileMenu = document.getElementById('mobile-menu');
                mobileMenu.classList.toggle('hidden');
            });
        </script>                       
    </body>
</html>