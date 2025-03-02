<?php
session_start();
require_once '../database.php';

// Check if user is logged in and is a center
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'center') {
    header("Location: ../auth/login.php?error=unauthorized");
    exit();
}

$user_id = $_SESSION['user_id'];
$center_id = $_SESSION['center_id'];
$success_message = "";
$error_message = "";

// Handle form submission for center update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_center'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $contact = filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_STRING);
    // Remove opening_hours since it's not in the database
    
    // Process material types array into comma-separated string
    $categories = isset($_POST['categories_array']) ? implode(',', $_POST['categories_array']) : '';
    
    if (empty($name) || empty($address)) {
        $error_message = "Center name and address are required";
    } else {
        // Update sortation center record - remove opening_hours
        $stmt = $conn->prepare("UPDATE tbl_sortation_centers SET name = ?, address = ?, description = ?, categories = ?, contact = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $name, $address, $description, $categories, $contact, $center_id);
        
        if ($stmt->execute()) {
            $success_message = "Center profile updated successfully";
        } else {
            $error_message = "Error updating center profile: " . $conn->error;
        }
    }
}

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate password
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All password fields are required";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match";
    } elseif (strlen($new_password) < 5) {
        $error_message = "New password must be at least 5 characters";
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM tbl_user WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if (password_verify($current_password, $row['password']) || $current_password === $row['password']) { // Support for both hashed and plain passwords
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE tbl_user SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($update_stmt->execute()) {
                    $success_message = "Password updated successfully";
                } else {
                    $error_message = "Error updating password";
                }
            } else {
                $error_message = "Current password is incorrect";
            }
        } else {
            $error_message = "User not found";
        }
    }
}

// Get user and center details - specifically using username as email
$query = "SELECT u.id, u.username as email, u.password, u.user_type, 
                 c.id as center_id, c.name, c.address, c.description, c.categories, c.contact, c.rating 
          FROM tbl_user u 
          JOIN tbl_sortation_centers c ON u.center_id = c.id
          WHERE u.id = ?";

$stmt = $conn->prepare($query);
// Check if prepare failed
if ($stmt === false) {
    $error_message = "Database query error: " . $conn->error;
} else {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // If no user found, show error
    if (!$user) {
        $error_message = "User profile not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Center Profile - EcoLens</title>
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
        /* Animation for password form */
        .password-form {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease, opacity 0.3s ease, padding 0.3s ease, margin 0.3s ease;
            opacity: 0;
            padding-top: 0;
            padding-bottom: 0;
            margin-top: 0;
        }
        
        .password-form.active {
            max-height: 300px;
            opacity: 1;
            padding-top: 1rem;
            padding-bottom: 1rem;
            margin-top: 1rem;
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
                <a href="../index.php" class="block py-2 text-white hover:text-[#436d2e]">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="bg-overlay">
        <div class="relative min-h-screen pt-24 pb-12">
            <div class="container mx-auto px-4">
                <div class="max-w-5xl mx-auto">
                    <!-- Success/Error Messages -->
                    <?php if ($success_message): ?>
                    <div class="bg-green-600/20 backdrop-blur-sm text-green-200 p-4 rounded-lg mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-2xl mr-3"></i>
                            <p><?php echo $success_message; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                    <div class="bg-red-600/20 backdrop-blur-sm text-red-200 p-4 rounded-lg mb-6">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-2xl mr-3"></i>
                            <p><?php echo $error_message; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                
                    <div class="mb-8">
                        <h1 class="text-3xl font-bold text-white mb-2">Center Profile</h1>
                        <p class="text-green-100">Manage your recycling center information</p>
                    </div>
                    
                    <?php if (isset($user) && $user): ?>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Profile Sidebar -->
                        <div>
                            <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl mb-6">
                                <div class="text-center mb-4">
                                    <div class="inline-flex items-center justify-center w-24 h-24 bg-[#436d2e] rounded-full mb-4">
                                        <i class="fas fa-recycle text-4xl text-white"></i>
                                    </div>
                                    <h2 class="text-xl font-bold text-white"><?php echo htmlspecialchars($user['name']); ?></h2>
                                    <p class="text-green-100 text-sm">Recycling Center</p>
                                </div>
                                
                                <div class="border-t border-white/10 pt-4 mt-4">
                                    <div class="flex items-center gap-3 mb-3">
                                        <i class="fas fa-envelope w-5 text-white/70"></i>
                                        <span class="text-white">
                                            <?php echo htmlspecialchars($user['email'] ?? 'Not specified'); ?>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3 mb-3">
                                        <i class="fas fa-phone w-5 text-white/70"></i>
                                        <span class="text-white"><?php echo htmlspecialchars($user['contact']) ?: 'Not specified'; ?></span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <i class="fas fa-star w-5 text-white/70"></i>
                                        <span class="text-white"><?php echo number_format($user['rating'], 1); ?> / 5</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Password Update Section - Modified to be toggleable -->
                            <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-semibold text-white">Security Settings</h3>
                                    <button id="togglePasswordForm" type="button" class="text-green-300 hover:text-white transition-all flex items-center">
                                        <span>Change Password</span>
                                        <i class="fas fa-chevron-down ml-2 transition-transform duration-300" id="toggleIcon"></i>
                                    </button>
                                </div>
                                
                                <!-- Password form (hidden by default) -->
                                <form method="POST" class="password-form mt-0 border-t border-white/10" id="passwordForm">
                                    <div class="space-y-4 py-0">
                                        <div>
                                            <label class="block text-sm text-white/70 mb-1">Current Password</label>
                                            <input type="password" name="current_password" required 
                                                class="w-full px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm text-white/70 mb-1">New Password</label>
                                            <input type="password" name="new_password" required 
                                                class="w-full px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                                        </div>
                                        
                                        <div>
                                            <label class="block text-sm text-white/70 mb-1">Confirm New Password</label>
                                            <input type="password" name="confirm_password" required 
                                                class="w-full px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                                        </div>
                                        
                                        <div>
                                            <button type="submit" name="update_password" class="w-full bg-[#436d2e]/80 hover:bg-[#436d2e] text-white px-4 py-2 rounded-lg transition-all">
                                                Update Password
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                
                                <!-- Password tips -->
                                <div class="mt-4 text-white/60 text-xs">
                                    <p class="mb-1">For a strong password:</p>
                                    <ul class="list-disc pl-5 space-y-0.5">
                                        <li>Use at least 8 characters</li>
                                        <li>Include uppercase and lowercase letters</li>
                                        <li>Add numbers and special characters</li>
                                        <li>Avoid using easily guessed information</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Center Profile Form -->
                        <div class="lg:col-span-2">
                            <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                                <h3 class="text-xl font-semibold text-white mb-6">Center Information</h3>
                                
                                <form method="POST" class="space-y-6">
                                    <div>
                                        <label class="block text-sm text-white/70 mb-2">Center Name</label>
                                        <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required
                                               class="w-full px-4 py-3 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm text-white/70 mb-2">Address</label>
                                        <textarea name="address" rows="2" required
                                                  class="w-full px-4 py-3 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]"><?php echo htmlspecialchars($user['address']); ?></textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm text-white/70 mb-2">Contact Information</label>
                                        <input type="text" name="contact" value="<?php echo htmlspecialchars($user['contact']); ?>"
                                               class="w-full px-4 py-3 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]"
                                               placeholder="Phone, email, or other contact details">
                                    </div>
                                    
                                    <!-- Removed opening_hours field -->
                                    
                                    <div>
                                        <label class="block text-sm text-white/70 mb-2">Center Description</label>
                                        <textarea name="description" rows="4"
                                                  class="w-full px-4 py-3 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]"
                                                  placeholder="Describe your recycling center, services offered, etc."><?php echo htmlspecialchars($user['description']); ?></textarea>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm text-white/70 mb-4">Materials Accepted</label>
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                            <?php
                                            $categories = explode(',', $user['categories']);
                                            $material_types = [
                                                'plastic' => ['icon' => 'fas fa-wine-bottle', 'label' => 'Plastic'],
                                                'paper' => ['icon' => 'fas fa-newspaper', 'label' => 'Paper'],
                                                'metal' => ['icon' => 'fas fa-bolt', 'label' => 'Metal'],
                                                'glass' => ['icon' => 'fas fa-glass-martini', 'label' => 'Glass'],
                                                'electronics' => ['icon' => 'fas fa-laptop', 'label' => 'Electronics'],
                                                // Keep the most common material types only
                                                // Removed: organic, batteries, textile
                                            ];
                                            
                                            foreach ($material_types as $type => $info) {
                                                $checked = in_array($type, $categories) ? 'checked' : '';
                                                echo "
                                                <label class=\"flex items-center space-x-2 bg-white/10 px-4 py-3 rounded-lg hover:bg-white/15 transition-all cursor-pointer\">
                                                    <input type=\"checkbox\" name=\"categories_array[]\" value=\"$type\" class=\"rounded text-[#436d2e]\" $checked>
                                                    <span class=\"text-white\"><i class=\"{$info['icon']} mr-2\"></i> {$info['label']}</span>
                                                </label>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <button type="submit" name="update_center" class="px-6 py-3 bg-[#436d2e] text-white rounded-lg hover:bg-opacity-90 transition-all">
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                        <div class="text-center">
                            <i class="fas fa-exclamation-triangle text-yellow-300 text-4xl mb-4"></i>
                            <h2 class="text-xl font-bold text-white mb-2">Profile Data Not Available</h2>
                            <p class="text-white/70 mb-6">There was a problem retrieving your profile information. This might be due to a database connection issue or missing data.</p>
                            <a href="dashboard.php" class="inline-block px-6 py-3 bg-[#436d2e] text-white rounded-lg hover:bg-opacity-90 transition-all">
                                Return to Dashboard
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Toggle mobile menu
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });

        // Toggle password form
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.getElementById('togglePasswordForm');
            const passwordForm = document.getElementById('passwordForm');
            const toggleIcon = document.getElementById('toggleIcon');
            
            toggleButton.addEventListener('click', function() {
                passwordForm.classList.toggle('active');
                toggleIcon.style.transform = passwordForm.classList.contains('active') ? 'rotate(180deg)' : 'rotate(0)';
            });
        });

        // Show success message then fade out after 3 seconds
        <?php if ($success_message): ?>
        setTimeout(function() {
            const successMsg = document.querySelector('.bg-green-600\\/20');
            if (successMsg) {
                successMsg.style.transition = 'opacity 0.5s ease';
                successMsg.style.opacity = '0';
                setTimeout(() => successMsg.remove(), 500);
            }
        }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
