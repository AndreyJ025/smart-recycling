<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check session
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

include 'database.php';

// Handle username update
if(isset($_POST['update_fullname'])) {
    $new_fullname = trim($_POST['new_fullname']);
    if(!empty($new_fullname)) {
        $stmt = $conn->prepare("UPDATE tbl_user SET fullname = ? WHERE id = ?");
        $stmt->bind_param("si", $new_fullname, $_SESSION["user_id"]);
        if($stmt->execute()) {
            $_SESSION['success_message'] = "Name updated successfully!";
            $_SESSION['user_fullname'] = $new_fullname; // Update session
        } else {
            $_SESSION['error_message'] = "Failed to update full name.";
        }
    }
    header("Location: profile.php");
    exit();
}

// Handle password update
if(isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM tbl_user WHERE id = ?");
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    
    if($current_password === $user_data['password']) {
        if($new_password === $confirm_password) {
            $stmt = $conn->prepare("UPDATE tbl_user SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_password, $_SESSION["user_id"]);
            if($stmt->execute()) {
                $_SESSION['success_message'] = "Password updated successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to update password.";
            }
        } else {
            $_SESSION['error_message'] = "New passwords do not match.";
        }
    } else {
        $_SESSION['error_message'] = "Current password is incorrect.";
    }
    header("Location: profile.php");
    exit();
}

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM tbl_user WHERE id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Fetch recent remits
$stmt = $conn->prepare("
    SELECT r.*, s.name as center_name 
    FROM tbl_remit r
    JOIN tbl_sortation_centers s ON s.id = r.sortation_center_id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC LIMIT 5
");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$recent_remits = $stmt->get_result();
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
            background: url('background.jpg');
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
        .modal {
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.1s ease-in-out;
        }
        .modal.show {
            transform: scale(1);
            opacity: 1;
        }
    </style>
</head>
<body class="font-[Poppins]">
    <!-- Top Navigation -->
    <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex-shrink-0 flex items-center gap-3">
                    <img src="logo.png" alt="Smart Recycling Logo" class="h-10">
                    <h1 class="text-2xl font-bold">
                        <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                    </h1>
                </div>
                <a href="home.php" class="text-white hover:text-[#22c55e] transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </nav>

    <div class="bg-overlay">
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="w-full max-w-4xl pt-20">
                <!-- Success/Error Messages -->
                <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="bg-[#22c55e]/20 text-[#22c55e] p-4 rounded-xl mb-6">
                        <?php 
                        echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error_message'])): ?>
                    <div class="bg-red-500/20 text-red-200 p-4 rounded-xl mb-6">
                        <?php 
                        echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']);
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Profile Card -->
                <div class="bg-white/5 backdrop-blur-md rounded-xl p-8 mb-8">
                    <div class="text-center mb-6">
                        <div class="w-24 h-24 mx-auto bg-[#22c55e] rounded-full flex items-center justify-center mb-4">
                            <i class="fa-solid fa-user text-4xl text-white"></i>
                        </div>
                        <h2 class="text-white text-2xl font-bold"><?php echo htmlspecialchars($user['fullname']); ?></h2>
                        <p class="text-white/80"><?php echo htmlspecialchars($user['username']); ?></p>
                    </div>

                    <div class="text-center mb-6">
                        <div class="bg-[#22c55e]/20 rounded-xl p-4">
                            <h3 class="text-[#22c55e] text-xl font-bold mb-2">Total Points</h3>
                            <p class="text-white text-3xl font-bold"><?php echo $user['total_points']; ?></p>
                        </div>
                    </div>

                    <div class="flex justify-center gap-4">
                        <button onclick="showUsernameModal()" 
                                class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl transition-all">
                            Change Name
                        </button>
                        <button onclick="showPasswordModal()" 
                                class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-xl transition-all">
                            Change Password
                        </button>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white/5 backdrop-blur-md rounded-xl p-8">
                    <h3 class="text-white text-xl font-bold mb-6">Recent Activity</h3>
                    <div class="space-y-4">
                        <?php if ($recent_remits->num_rows > 0): ?>
                            <?php while($remit = $recent_remits->fetch_assoc()): ?>
                                <div class="bg-white/5 rounded-xl p-4">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h4 class="text-white font-medium"><?php echo htmlspecialchars($remit['item_name']); ?></h4>
                                            <p class="text-white/80 text-sm"><?php echo htmlspecialchars($remit['center_name']); ?></p>
                                        </div>
                                        <?php if ($remit['points'] > 0): ?>
                                            <div class="bg-[#22c55e] text-white px-3 py-1 rounded-xl text-sm">
                                                +<?php echo $remit['points']; ?> points
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-white/80 text-center">No recent activity</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Username Modal -->
    <div id="usernameModal" class="modal hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50">
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="bg-[#1b1b1b] rounded-xl p-8 w-full max-w-md relative">
                <button onclick="hideUsernameModal()" class="absolute top-4 right-4 text-white/50 hover:text-white">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
                <h3 class="text-white text-xl font-bold mb-6">Change Name</h3>
                <form method="POST" class="space-y-4">
                    <div class="relative">
                        <input type="text" 
                            name="new_fullname" 
                            placeholder="Enter new full name" 
                            value="<?php echo htmlspecialchars($user['fullname']); ?>"
                            required
                            class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors">
                    </div>
                    <button type="submit" 
                            name="update_fullname"
                            class="w-full bg-white text-black font-bold rounded-xl py-4 hover:bg-opacity-90 transition-all">
                        Update Name
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Password Modal -->
    <div id="passwordModal" class="modal hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50">
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="bg-[#1b1b1b] rounded-xl p-8 w-full max-w-md relative">
                <button onclick="hidePasswordModal()" class="absolute top-4 right-4 text-white/50 hover:text-white">
                    <i class="fa-solid fa-times text-xl"></i>
                </button>
                <h3 class="text-white text-xl font-bold mb-6">Change Password</h3>
                <form method="POST" class="space-y-4">
                    <div class="relative">
                        <input type="password" name="current_password" placeholder="Current password" required
                               class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors">
                    </div>
                    <div class="relative">
                        <input type="password" name="new_password" placeholder="New password" required
                               class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors">
                    </div>
                    <div class="relative">
                        <input type="password" name="confirm_password" placeholder="Confirm new password" required
                               class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors">
                    </div>
                    <button type="submit" name="update_password"
                            class="w-full bg-white text-black font-bold rounded-xl py-4 hover:bg-opacity-90 transition-all">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showUsernameModal() {
            const modal = document.getElementById('usernameModal');
            modal.classList.remove('hidden');
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function hideUsernameModal() {
            const modal = document.getElementById('usernameModal');
            modal.classList.remove('show');
            setTimeout(() => modal.classList.add('hidden'), 100);
        }

        function showPasswordModal() {
            const modal = document.getElementById('passwordModal');
            modal.classList.remove('hidden');
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function hidePasswordModal() {
            const modal = document.getElementById('passwordModal');
            modal.classList.remove('show');
            setTimeout(() => modal.classList.add('hidden'), 100);
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const usernameModal = document.getElementById('usernameModal');
            const passwordModal = document.getElementById('passwordModal');
            
            if (event.target === usernameModal) {
                hideUsernameModal();
            }
            if (event.target === passwordModal) {
                hidePasswordModal();
            }
        }
    </script>
</body>
</html>