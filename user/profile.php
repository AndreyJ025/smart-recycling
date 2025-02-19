<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check session
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

include '../database.php';

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

// Fetch user data + recent remits
$stmt = $conn->prepare("SELECT * FROM tbl_user WHERE id = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

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
        .modal {
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.1s ease-in-out;
        }
        .modal.show {
            transform: scale(1);
            opacity: 1;
        }
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 50;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
        }

        .modal-backdrop.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            transform: scale(0.95);
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
        }

        .modal-content.show {
            transform: scale(1);
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>
<body class="font-[Poppins]">
    <!-- Top Navigation -->
    <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex-shrink-0 flex items-center gap-3">
                    <img src="../assets/logo.png" alt="Smart Recycling Logo" class="h-10">
                    <h1 class="text-2xl font-bold">
                        <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                    </h1>
                </div>
                <a href="../home.php" class="text-white hover:text-[#22c55e] transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </nav>

    <div class="bg-overlay">
        <div class="min-h-screen pt-24 pb-12 px-4">
            <div class="max-w-7xl mx-auto">
                <!-- Success/Error Messages -->
                <?php if(isset($_SESSION['success_message'])): ?>
                    <div class="bg-[#436d2e]/20 text-[#436d2e] p-4 rounded-xl mb-6 animate-fade-in">
                        <div class="flex items-center gap-2">
                            <i class="fa-solid fa-check-circle"></i>
                            <p class="font-medium"><?php echo $_SESSION['success_message']; ?></p>
                        </div>
                    </div>
                    <?php unset($_SESSION['success_message']); ?>
                <?php endif; ?>

                <!-- Profile Overview -->
                <div class="grid md:grid-cols-3 gap-6 mb-8">
                    <!-- User Info -->
                    <div class="md:col-span-1 bg-white/5 backdrop-blur-sm p-8 rounded-xl">
                        <div class="text-center">
                            <div class="w-24 h-24 mx-auto bg-[#436d2e] rounded-full flex items-center justify-center mb-4">
                                <i class="fa-solid fa-user text-4xl text-white"></i>
                            </div>
                            <h2 class="text-white text-2xl font-bold mb-1"><?php echo htmlspecialchars($user['fullname']); ?></h2>
                            <p class="text-white/60 mb-6"><?php echo htmlspecialchars($user['username']); ?></p>
                            
                            <div class="flex flex-col gap-3">
                                <button onclick="showUsernameModal()" 
                                        class="w-full bg-white/10 hover:bg-[#436d2e] text-white px-4 py-3 rounded-xl transition-all flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-user-edit"></i>
                                    Change Name
                                </button>
                                <button onclick="showPasswordModal()" 
                                        class="w-full bg-white/10 hover:bg-[#436d2e] text-white px-4 py-3 rounded-xl transition-all flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-key"></i>
                                    Change Password
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Stats & Activity -->
                    <div class="md:col-span-2 space-y-6">
                        <!-- Points Overview -->
                        <div class="bg-white/5 backdrop-blur-sm p-8 rounded-xl">
                            <div class="grid md:grid-cols-2 gap-6">
                                <div class="text-center">
                                    <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-star"></i></div>
                                    <div class="text-2xl font-bold text-white mb-1"><?php echo $user['total_points']; ?></div>
                                    <div class="text-white/60">Total Points</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-recycle"></i></div>
                                    <div class="text-2xl font-bold text-white mb-1"><?php echo $recent_remits->num_rows; ?></div>
                                    <div class="text-white/60">Recent Activities</div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="bg-white/5 backdrop-blur-sm p-8 rounded-xl">
                            <h3 class="text-white text-xl font-bold mb-6">Recent Activity</h3>
                            <div class="space-y-4">
                                <?php if ($recent_remits->num_rows > 0): ?>
                                    <?php while($remit = $recent_remits->fetch_assoc()): ?>
                                        <div class="group bg-white/5 hover:bg-[#436d2e]/20 rounded-xl p-6 transition-all">
                                            <div class="flex justify-between items-center">
                                                <div class="flex items-center gap-4">
                                                    <div class="bg-[#436d2e] w-12 h-12 rounded-full flex items-center justify-center shrink-0">
                                                        <i class="fa-solid fa-recycle text-white text-xl"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-white font-medium"><?php echo htmlspecialchars($remit['item_name']); ?></h4>
                                                        <p class="text-white/60"><?php echo htmlspecialchars($remit['center_name']); ?></p>
                                                        <p class="text-white/60 text-sm"><?php echo date('M d, Y', strtotime($remit['created_at'])); ?></p>
                                                    </div>
                                                </div>
                                                <?php if ($remit['points'] > 0): ?>
                                                    <div class="bg-[#436d2e] text-white px-4 py-2 rounded-xl flex items-center gap-2">
                                                        <i class="fa-solid fa-star"></i>
                                                        <?php echo $remit['points']; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-8">
                                        <div class="inline-flex items-center justify-center w-16 h-16 bg-[#436d2e] rounded-full mb-4">
                                            <i class="fa-solid fa-clock text-white text-2xl"></i>
                                        </div>
                                        <h3 class="text-white text-xl font-bold mb-2">No Recent Activity</h3>
                                        <p class="text-white/60">Start your recycling journey today!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showUsernameModal() {
            const modal = document.getElementById('usernameModal');
            const content = modal.querySelector('.modal-content');
            modal.classList.remove('hidden');
            requestAnimationFrame(() => {
                modal.classList.add('show');
                content.classList.add('show');
            });
        }

        function hideUsernameModal() {
            const modal = document.getElementById('usernameModal');
            const content = modal.querySelector('.modal-content');
            modal.classList.remove('show');
            content.classList.remove('show');
            setTimeout(() => modal.classList.add('hidden'), 200);
        }

        function showPasswordModal() {
            const modal = document.getElementById('passwordModal');
            const content = modal.querySelector('.modal-content');
            modal.classList.remove('hidden');
            requestAnimationFrame(() => {
                modal.classList.add('show');
                content.classList.add('show');
            });
        }

        function hidePasswordModal() {
            const modal = document.getElementById('passwordModal');
            const content = modal.querySelector('.modal-content');
            modal.classList.remove('show');
            content.classList.remove('show');
            setTimeout(() => modal.classList.add('hidden'), 200);
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
    
    <!-- Username Change Modal -->
    <div id="usernameModal" class="modal-backdrop hidden">
        <div class="modal-content bg-[#1b1b1b] p-8 rounded-xl w-full max-w-md mx-4 relative">
            <h3 class="text-white text-2xl font-bold mb-6">Change Name</h3>
            
            <form method="POST" class="space-y-6">
                <div class="relative group">
                    <input type="text" 
                           name="new_fullname" 
                           placeholder="Enter new name..." 
                           class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all"
                           required>
                    <i class="fa-solid fa-user absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                </div>
    
                <div class="flex gap-4">
                    <button type="button" 
                            onclick="hideUsernameModal()" 
                            class="flex-1 bg-white/10 text-white px-6 py-3 rounded-xl hover:bg-white/20 transition-all">
                        Cancel
                    </button>
                    <button type="submit" 
                            name="update_fullname" 
                            class="flex-1 bg-[#436d2e] text-white px-6 py-3 rounded-xl hover:bg-opacity-90 transition-all">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Password Change Modal -->
    <div id="passwordModal" class="modal-backdrop hidden">
        <div class="modal-content bg-[#1b1b1b] p-8 rounded-xl w-full max-w-md mx-4 relative">
            <h3 class="text-white text-2xl font-bold mb-6">Change Password</h3>
            
            <form method="POST" class="space-y-6">
                <div class="relative group">
                    <input type="password" 
                           name="current_password" 
                           placeholder="Current password..." 
                           class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all"
                           required>
                    <i class="fa-solid fa-lock absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                </div>
    
                <div class="relative group">
                    <input type="password" 
                           name="new_password" 
                           placeholder="New password..." 
                           class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all"
                           required>
                    <i class="fa-solid fa-key absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                </div>
    
                <div class="relative group">
                    <input type="password" 
                           name="confirm_password" 
                           placeholder="Confirm new password..." 
                           class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all"
                           required>
                    <i class="fa-solid fa-check absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                </div>
    
                <div class="flex gap-4">
                    <button type="button" 
                            onclick="hidePasswordModal()" 
                            class="flex-1 bg-white/10 text-white px-6 py-3 rounded-xl hover:bg-white/20 transition-all">
                        Cancel
                    </button>
                    <button type="submit" 
                            name="update_password" 
                            class="flex-1 bg-[#436d2e] text-white px-6 py-3 rounded-xl hover:bg-opacity-90 transition-all">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>