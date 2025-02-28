<?php
session_start();
include '../database.php';

$error_msg = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate inputs
    $fullname = trim($_POST["fullname"]);
    $email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $password = $_POST["password"];
    $user_type = $_POST["user_type"];
    
    // Business/Center fields
    $business_name = in_array($user_type, ['business', 'center']) ? trim($_POST["business_name"]) : null;
    $address = in_array($user_type, ['business', 'center']) ? trim($_POST["address"]) : null;
    $contact_number = in_array($user_type, ['business', 'center']) ? trim($_POST["contact_number"]) : null;
    
    // Center-specific fields
    $description = $user_type === 'center' ? trim($_POST["description"]) : null;
    $categories = $user_type === 'center' ? trim($_POST["categories"]) : null;
    $link = $user_type === 'center' ? trim($_POST["link"]) : null;

    // Validation
    if (empty($fullname) || empty($email) || empty($password)) {
        $error_msg = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Invalid email format";
    } elseif (strlen($password) < 5) {
        $error_msg = "Password must be at least 5 characters";
    } elseif (in_array($user_type, ['business', 'center']) && (empty($business_name) || empty($address) || empty($contact_number))) {
        $error_msg = "All organization fields are required";
    } elseif ($user_type === 'center' && (empty($description) || empty($categories))) {
        $error_msg = "All center fields are required";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Check for duplicate email
            $check = $conn->prepare("SELECT id FROM tbl_user WHERE username = ?");
            $check->bind_param("s", $email);
            $check->execute();
            
            if ($check->get_result()->num_rows > 0) {
                $error_msg = "Email already registered";
                $conn->rollback();
            } else {
                $center_id = null;
                                
                // If registering as a center, create sortation center entry first
                if ($user_type === 'center') {
                    $stmt = $conn->prepare("INSERT INTO tbl_sortation_centers (
                        name, 
                        address, 
                        contact,
                        description,
                        categories,
                        rating,
                        link
                    ) VALUES (?, ?, ?, ?, ?, 3, ?)");
                    
                    $stmt->bind_param("ssssss", 
                        $business_name, 
                        $address, 
                        $contact_number,
                        $description,
                        $categories,
                        $link
                    );
                    $stmt->execute();
                    $center_id = $conn->insert_id;
                }
                
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO tbl_user (
                    fullname, 
                    username, 
                    password, 
                    is_admin, 
                    total_points, 
                    user_type, 
                    business_name, 
                    address, 
                    contact_number, 
                    center_id
                ) VALUES (?, ?, ?, 0, 0, ?, ?, ?, ?, ?)");
                
                $stmt->bind_param("sssssssi", 
                    $fullname, 
                    $email, 
                    $password, 
                    $user_type, 
                    $business_name, 
                    $address, 
                    $contact_number, 
                    $center_id
                );
                
                if ($stmt->execute()) {
                    $conn->commit();
                    header("Location: login.php?registration=success");
                    exit();
                } else {
                    throw new Exception("Registration failed");
                }
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error_msg = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        .bg-overlay > div {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body class="font-[Poppins]">
    <div class="bg-overlay">
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="w-full max-w-[800px]">
                <!-- Back Button -->
                <a href="../index.php" class="inline-flex items-center text-white mb-8 hover:text-[#436d2e] transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Back
                </a>

                <!-- Signup Container -->
                <div class="bg-white/5 backdrop-blur-sm p-8 rounded-xl">
                    <!-- Logo Section -->
                    <div class="text-center mb-8">
                        <img src="../assets/logo.png" alt="Smart Recycling Logo" class="w-[40%] max-w-[200px] mx-auto mb-4">
                        <h1 class="text-2xl font-bold">
                            <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                        </h1>
                    </div>

                    <?php if($error_msg): ?>
                        <div class="bg-red-500/20 text-red-200 p-4 rounded-xl mb-6">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <p class="font-medium"><?php echo $error_msg; ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Signup Form -->
                    <form method="POST" class="space-y-6">
                        <!-- Basic Information - Two Column Layout -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="relative group">
                                <input type="text" name="fullname" placeholder="Enter Fullname..." 
                                       class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all"
                                       required>
                                <i class="fa-solid fa-user absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                            </div>

                            <div class="relative group">
                                <input type="email" name="email" placeholder="Enter Email..." 
                                       class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all"
                                       required>
                                <i class="fa-solid fa-envelope absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                            </div>

                            <div class="relative group">
                                <input type="password" name="password" placeholder="Enter Password..." 
                                       class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all"
                                       required>
                                <i class="fa-solid fa-lock absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                            </div>

                            <div class="relative group">
                                <select name="user_type" 
                                        class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all appearance-none pr-16"
                                        onchange="toggleBusinessFields(this.value)"
                                        required>
                                    <option value="individual" class="text-black">Individual Account</option>
                                    <option value="business" class="text-black">Business Account</option>
                                    <option value="center" class="text-black">Recycling Center Account</option>
                                </select>
                                <i class="fa-solid fa-users absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                                <i class="fa-solid fa-chevron-down absolute right-10 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                            </div>
                        </div>

                        <!-- Business Fields (Hidden by default) -->
                        <div id="businessFields" class="hidden">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="relative group">
                                    <input type="text" name="business_name" placeholder="Enter Business Name..." 
                                           class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all">
                                    <i class="fa-solid fa-building absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                                </div>

                                <div class="relative group">
                                    <input type="text" name="contact_number" placeholder="Enter Contact Number..." 
                                           class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all">
                                    <i class="fa-solid fa-phone absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                                </div>

                                <div class="relative group md:col-span-2">
                                    <input type="text" name="address" placeholder="Enter Business Address..." 
                                           class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all">
                                    <i class="fa-solid fa-location-dot absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Center-specific Fields -->
                        <div id="centerFields" class="hidden space-y-6">
                            <div class="relative group">
                                <textarea name="description" placeholder="Enter center description, operating hours, and other important details..." rows="3"
                                        class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all"></textarea>
                                <i class="fa-solid fa-align-left absolute right-4 top-8 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="relative group">
                                    <input type="text" name="categories" placeholder="Enter accepted materials" 
                                        class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all">
                                    <i class="fa-solid fa-recycle absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                                    <small class="text-white/60 mt-1 block">Separate with commas (plastic,paper,metal)</small>
                                </div>

                                <div class="relative group">
                                    <input type="text" name="link" placeholder="Enter website or Maps URL (optional)" 
                                        class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all">
                                    <i class="fa-solid fa-link absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                                </div>
                            </div>
                        </div>

                        <button type="submit" 
                                class="w-full bg-[#436d2e] text-white font-bold text-lg rounded-xl py-4 hover:bg-opacity-90 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-user-plus"></i>
                            REGISTER
                        </button>
                    </form>

                    <!-- Login Link -->
                    <p class="text-white/80 text-center mt-6">
                        Already have an account? 
                        <a href="login.php" class="text-[#436d2e] hover:text-white transition-all">Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleBusinessFields(userType) {
            const businessFields = document.getElementById('businessFields');
            const centerFields = document.getElementById('centerFields');
            const fields = businessFields.getElementsByTagName('input');
            const businessNameField = fields[0];
            const addressField = fields[1];
            
            if (userType === 'business' || userType === 'center') {
                businessFields.classList.remove('hidden');
                for (let field of fields) {
                    field.required = true;
                }
                
                // Handle center-specific fields
                if (userType === 'center') {
                    centerFields.classList.remove('hidden');
                    document.querySelector('textarea[name="description"]').required = true;
                    document.querySelector('input[name="categories"]').required = true;
                    businessNameField.placeholder = 'Enter Center Name...';
                    addressField.placeholder = 'Enter Center Contact Address...';
                } else {
                    centerFields.classList.add('hidden');
                    document.querySelector('textarea[name="description"]').required = false;
                    document.querySelector('input[name="categories"]').required = false;
                    businessNameField.placeholder = 'Enter Business Name...';
                    addressField.placeholder = 'Enter Business Contact Address...';
                }
            } else {
                businessFields.classList.add('hidden');
                centerFields.classList.add('hidden');
                for (let field of fields) {
                    field.required = false;
                    field.value = '';
                }
            }
        }
    </script>
</body>
</html>