<?php
session_start();
require_once '../database.php';

// Check admin access
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: ../home.php");
    exit();
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_role':
                // Handle role updates - now includes user_type field
                $stmt = $conn->prepare("UPDATE tbl_user SET user_type = ? WHERE id = ?");
                $stmt->bind_param("si", $_POST['user_type'], $_POST['user_id']);
                $stmt->execute();
                
                // Update is_admin flag if needed
                $is_admin = ($_POST['user_type'] === 'admin') ? 1 : 0;
                $stmt = $conn->prepare("UPDATE tbl_user SET is_admin = ? WHERE id = ?");
                $stmt->bind_param("ii", $is_admin, $_POST['user_id']);
                $stmt->execute();
                break;
                
            case 'update_points':
                $stmt = $conn->prepare("UPDATE tbl_user SET total_points = ? WHERE id = ?");
                $stmt->bind_param("ii", $_POST['points'], $_POST['user_id']);
                $stmt->execute();
                break;
                
            case 'delete':
                $stmt = $conn->prepare("DELETE FROM tbl_user WHERE id = ?");
                $stmt->bind_param("i", $_POST['user_id']);
                $stmt->execute();
                break;
        }
        header("Location: manage-users.php");
        exit();
    }
}

// Get search and filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$role = isset($_GET['role']) ? $_GET['role'] : 'all';

// Build query
$query = "SELECT * FROM tbl_user WHERE 1=1";
if ($search) {
    $query .= " AND (fullname LIKE ? OR username LIKE ?)";
}
if ($role !== 'all') {
    // Filter based on user_type instead of just is_admin
    if ($role === 'admin') {
        $query .= " AND is_admin = 1";
    } else {
        $query .= " AND user_type = ?";
    }
}
$query .= " ORDER BY fullname ASC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if ($search && $role !== 'all' && $role !== 'admin') {
    $search = "%$search%";
    $stmt->bind_param("sss", $search, $search, $role);
} elseif ($search && $role === 'admin') {
    $search = "%$search%";
    $stmt->bind_param("ss", $search, $search);
} elseif ($search) {
    $search = "%$search%";
    $stmt->bind_param("ss", $search, $search);
} elseif ($role !== 'all' && $role !== 'admin') {
    $stmt->bind_param("s", $role);
}
$stmt->execute();
$users = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - EcoLens</title>
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
                <div class="flex-shrink-0 flex items-center gap-3">
                    <img src="../assets/logo.png" alt="Smart Recycling Logo" class="h-10">
                    <h1 class="text-2xl font-bold">
                        <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                    </h1>
                </div>
                <a href="admin-dashboard.php" class="text-white hover:text-[#22c55e] transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="bg-overlay">
        <div class="relative pt-24 pb-12 px-4">
            <div class="max-w-7xl mx-auto">
                <!-- Search and Filter -->
                <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm mb-8">
                    <form method="GET" class="flex gap-4">
                        <input type="text" name="search" placeholder="Search users..." value="<?php echo htmlspecialchars($search); ?>"
                               class="flex-1 px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                        <select name="role" class="px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e] [&>option]:text-white [&>option]:bg-[#1b1b1b]">
                            <option value="all" <?php echo $role === 'all' ? 'selected' : ''; ?>>All Users</option>
                            <option value="user" <?php echo $role === 'user' ? 'selected' : ''; ?>>Regular Users</option>
                            <option value="admin" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Admins</option>
                            <option value="center" <?php echo $role === 'center' ? 'selected' : ''; ?>>Recycling Centers</option>
                            <option value="business" <?php echo $role === 'business' ? 'selected' : ''; ?>>Businesses</option>
                        </select>
                        <button type="submit" class="px-6 py-2 bg-[#436d2e] text-white rounded-lg hover:bg-opacity-90">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                    </form>
                </div>

                <!-- Users List -->
                <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                    <h2 class="text-2xl font-bold text-white mb-6">Manage Users</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-white/60 text-left">
                                    <th class="pb-4">Name</th>
                                    <th class="pb-4">Email</th>
                                    <th class="pb-4">Role</th>
                                    <th class="pb-4">Points</th>
                                    <th class="pb-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="text-white">
                                <?php while($user = $users->fetch_assoc()): ?>
                                <tr class="border-t border-white/10">
                                    <td class="py-4"><?php echo htmlspecialchars($user['fullname']); ?></td>
                                    <td class="py-4"><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td class="py-4">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="update_role">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <select name="user_type" onchange="this.form.submit()" 
                                                    class="bg-white/10 text-white rounded px-2 py-1 [&>option]:bg-[#1b1b1b] [&>option]:text-white">
                                                <option value="user" <?php echo $user['user_type'] === 'user' ? 'selected' : ''; ?>>User</option>
                                                <option value="admin" <?php echo $user['is_admin'] == 1 ? 'selected' : ''; ?>>Admin</option>
                                                <option value="center" <?php echo $user['user_type'] === 'center' ? 'selected' : ''; ?>>Center</option>
                                                <option value="business" <?php echo $user['user_type'] === 'business' ? 'selected' : ''; ?>>Business</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="py-4">
                                        <form method="POST" class="inline">
                                            <input type="hidden" name="action" value="update_points">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="number" name="points" value="<?php echo $user['total_points']; ?>"
                                                   class="w-20 bg-white/10 text-white rounded px-2 py-1">
                                            <button type="submit" class="text-[#436d2e] hover:text-white ml-2">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-4">
                                        <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="text-red-500 hover:text-red-400">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>