<?php
session_start();
require_once '../database.php';

// Check admin access
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: ../home.php");
    exit();
}

// Get filter parameters
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : '';
$user_search = isset($_GET['user_search']) ? $_GET['user_search'] : '';
$date_range = isset($_GET['date_range']) ? $_GET['date_range'] : '7';

// Fetch users for filter dropdown
$users = $conn->query("SELECT id, fullname FROM tbl_user ORDER BY fullname ASC");

// Build query based on filters
$query = "
    SELECT 
        r.id, r.user_id, r.item_name, r.item_quantity, r.points, r.created_at,
        u.fullname,
        sc.name as center_name,
        DATE(r.created_at) as date
    FROM tbl_remit r
    JOIN tbl_user u ON r.user_id = u.id
    JOIN tbl_sortation_centers sc ON r.sortation_center_id = sc.id
    WHERE r.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
";

if ($user_id) {
    $query .= " AND r.user_id = ?";
}

$query .= " ORDER BY r.created_at DESC";

// Debug the query
// echo $query; // Uncomment to see the actual query

// Prepare statement with error handling
$stmt = $conn->prepare($query);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind parameters based on whether user_id filter is applied
if ($user_id) {
    if (!$stmt->bind_param("is", $date_range, $user_id)) {
        die("Error binding parameters: " . $stmt->error);
    }
} else {
    if (!$stmt->bind_param("i", $date_range)) {
        die("Error binding parameters: " . $stmt->error);
    }
}

// Execute with error handling
if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
}

$result = $stmt->get_result();

// Process the results to get daily summaries
$activities = [];
$itemStats = [];
$summary = [
    'total_items' => 0,
    'total_points' => 0,
    'unique_users' => [],
    'popular_items' => []
];

while ($row = $result->fetch_assoc()) {
    $date = $row['date'];
    $userId = $row['user_id'];
    $dateUserId = $date . '_' . $userId;
    
    // Initialize the array for this date-user if it doesn't exist
    if (!isset($activities[$dateUserId])) {
        $activities[$dateUserId] = [
            'user_id' => $userId,
            'fullname' => $row['fullname'],
            'date' => $date,
            'center_name' => $row['center_name'],
            'daily_items' => 0,
            'daily_points' => 0
        ];
    }
    
    // Add this item to the daily totals
    $activities[$dateUserId]['daily_items'] += $row['item_quantity'];
    $activities[$dateUserId]['daily_points'] += $row['points'];
    
    // Update summary statistics
    $summary['total_items'] += $row['item_quantity'];
    $summary['total_points'] += $row['points'];
    $summary['unique_users'][$userId] = $row['fullname'];
    
    // Track item statistics
    if (!isset($itemStats[$row['item_name']])) {
        $itemStats[$row['item_name']] = 0;
    }
    $itemStats[$row['item_name']] += $row['item_quantity'];
}

// Sort activities by date (newest first)
uasort($activities, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

// Sort item statistics and get top 5
arsort($itemStats);
$summary['popular_items'] = array_slice($itemStats, 0, 5, true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Activity - EcoLens</title>
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
                <!-- Filters -->
                <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm mb-8">
                    <form method="GET" class="flex gap-4">
                        <div class="relative flex-1">
                            <input type="text" 
                                   id="userSearch" 
                                   name="user_search" 
                                   placeholder="Search user..." 
                                   class="w-full px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]"
                                   value="<?php echo isset($_GET['user_search']) ? htmlspecialchars($_GET['user_search']) : ''; ?>">
                            <input type="hidden" 
                                   id="userId" 
                                   name="user_id" 
                                   value="<?php echo $user_id; ?>">
                            <div id="searchResults" 
                                 class="absolute z-10 w-full mt-1 bg-[#1b1b1b] border border-white/20 rounded-lg hidden">
                            </div>
                        </div>
                        <select name="date_range" class="px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e] [&>option]:text-white [&>option]:bg-[#1b1b1b]">
                            <option value="7" <?php echo $date_range == '7' ? 'selected' : ''; ?>>Last 7 Days</option>
                            <option value="30" <?php echo $date_range == '30' ? 'selected' : ''; ?>>Last 30 Days</option>
                            <option value="90" <?php echo $date_range == '90' ? 'selected' : ''; ?>>Last 90 Days</option>
                        </select>
                        <button type="submit" class="px-6 py-2 bg-[#436d2e] text-white rounded-lg hover:bg-opacity-90">
                            <i class="fas fa-filter mr-2"></i>Apply Filters
                        </button>
                    </form>
                </div>

                <!-- Summary Stats -->
                <div class="grid md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white/5 p-6 rounded-xl backdrop-blur-sm">
                        <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-recycle"></i></div>
                        <div class="text-2xl font-bold text-white mb-1"><?php echo $summary['total_items']; ?></div>
                        <div class="text-white/60">Total Items</div>
                    </div>
                    <div class="bg-white/5 p-6 rounded-xl backdrop-blur-sm">
                        <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-star"></i></div>
                        <div class="text-2xl font-bold text-white mb-1"><?php echo $summary['total_points']; ?></div>
                        <div class="text-white/60">Total Points</div>
                    </div>
                    <div class="bg-white/5 p-6 rounded-xl backdrop-blur-sm">
                        <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-users"></i></div>
                        <div class="text-2xl font-bold text-white mb-1"><?php echo count($summary['unique_users']); ?></div>
                        <div class="text-white/60">Active Users</div>
                    </div>
                    <div class="bg-white/5 p-6 rounded-xl backdrop-blur-sm">
                        <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-chart-line"></i></div>
                        <div class="text-2xl font-bold text-white mb-1">
                            <?php echo $summary['total_items'] ? round($summary['total_points'] / $summary['total_items'], 1) : 0; ?>
                        </div>
                        <div class="text-white/60">Avg. Points per Item</div>
                    </div>
                </div>

                <!-- Popular Items -->
                <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm mb-8">
                    <h2 class="text-2xl font-bold text-white mb-6">Most Recycled Items</h2>
                    <div class="grid md:grid-cols-5 gap-4">
                        <?php foreach($summary['popular_items'] as $item => $quantity): ?>
                        <div class="bg-white/5 p-4 rounded-lg">
                            <div class="text-white font-medium mb-2"><?php echo htmlspecialchars($item); ?></div>
                            <div class="text-[#436d2e] text-lg font-bold"><?php echo $quantity; ?> items</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Activity Log -->
                <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                    <h2 class="text-2xl font-bold text-white mb-6">Activity Log</h2>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="text-white/60 text-left">
                                    <th class="pb-4">Date</th>
                                    <th class="pb-4">User</th>
                                    <th class="pb-4">Items</th>
                                    <th class="pb-4">Points</th>
                                    <th class="pb-4">Center</th>
                                </tr>
                            </thead>
                            <tbody class="text-white">
                                <?php 
                                foreach($activities as $activity): 
                                ?>
                                <tr class="border-t border-white/10">
                                    <td class="py-4"><?php echo date('M d, Y', strtotime($activity['date'])); ?></td>
                                    <td class="py-4"><?php echo htmlspecialchars($activity['fullname']); ?></td>
                                    <td class="py-4"><?php echo $activity['daily_items']; ?></td>
                                    <td class="py-4"><?php echo $activity['daily_points']; ?></td>
                                    <td class="py-4"><?php echo htmlspecialchars($activity['center_name']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const userSearch = document.getElementById('userSearch');
        const searchResults = document.getElementById('searchResults');
        const userIdInput = document.getElementById('userId');

        let users = <?php 
            $users->data_seek(0);
            $usersList = [];
            while($user = $users->fetch_assoc()) {
                $usersList[] = [
                    'id' => $user['id'],
                    'name' => $user['fullname']
                ];
            }
            echo json_encode($usersList);
        ?>;

        userSearch.addEventListener('input', function() {
            const search = this.value.toLowerCase();
            if (search.length < 1) {
                searchResults.classList.add('hidden');
                userIdInput.value = '';
                return;
            }

            const matches = users.filter(user => 
                user.name.toLowerCase().includes(search)
            );

            if (matches.length > 0) {
                searchResults.innerHTML = matches.map(user => `
                    <div class="p-2 hover:bg-white/10 cursor-pointer text-white"
                        onclick="selectUser('${user.id}', '${user.name.replace("'", "\\'")}')">
                        ${user.name}
                    </div>
                `).join('');
                searchResults.classList.remove('hidden');
            } else {
                searchResults.innerHTML = `
                    <div class="p-2 text-white/60">No users found</div>
                `;
                searchResults.classList.remove('hidden');
            }
        });

        function selectUser(id, name) {
            userSearch.value = name;
            userIdInput.value = id;
            searchResults.classList.add('hidden');
        }

        // Close search results when clicking outside
        document.addEventListener('click', function(e) {
            if (!userSearch.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
    </script>

</body>
</html>