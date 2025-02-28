<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../database.php';

// Check user login
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

// Fetch total points
$total_points_sql = "SELECT total_points FROM tbl_user WHERE id = ?";
$stmt_points = $conn->prepare($total_points_sql);
$stmt_points->bind_param("i", $_SESSION["user_id"]);
$stmt_points->execute();
$total_points_result = $stmt_points->get_result();
$total_points = $total_points_result->fetch_assoc()["total_points"] ?? 0;

// Fetch remit records
$sql = "SELECT 
            r.*,
            s.name as center_name,
            s.address as center_address
        FROM tbl_remit r
        INNER JOIN tbl_sortation_centers s ON s.id = r.sortation_center_id 
        WHERE r.user_id = ?";
        
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
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
                <h2 class="text-3xl md:text-5xl font-bold text-white text-center mb-6">My Recycling Journey</h2>
                <p class="text-white/80 text-center max-w-3xl mx-auto mb-12">Track your contributions to a sustainable future and earn rewards for your eco-friendly actions.</p>
    
                <!-- Stats Overview -->
                <div class="grid md:grid-cols-2 gap-6 mb-12">
                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                        <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-star"></i></div>
                        <div class="text-2xl font-bold text-white mb-1"><?php echo $total_points; ?></div>
                        <div class="text-white/60">Total Points</div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                        <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-recycle"></i></div>
                        <div class="text-2xl font-bold text-white mb-1"><?php echo $result->num_rows; ?></div>
                        <div class="text-white/60">Total Records</div>
                    </div>
                </div>
    
                <!-- Search and Filter -->
                <div class="flex gap-4 mb-8">
                    <div class="relative group flex-1">
                        <input type="text" 
                               id="searchRecord" 
                               placeholder="Search records..." 
                               class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all pl-12">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                    </div>
                </div>
    
                <!-- Records Timeline -->
                <div class="space-y-4">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <div class="group bg-white/5 backdrop-blur-sm p-6 rounded-xl hover:bg-[#436d2e]/20 transition-all">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-4">
                                        <div class="bg-[#436d2e] w-12 h-12 rounded-full flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-recycle text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-white text-xl font-bold mb-1">
                                                <?php echo htmlspecialchars($row["item_name"]); ?>
                                                <span class="text-white/60 text-base font-normal ml-2">
                                                    (<?php echo htmlspecialchars($row["item_quantity"]); ?> items)
                                                </span>
                                            </h3>
                                            <p class="text-white/80">
                                                <?php echo htmlspecialchars($row["center_name"]); ?> • 
                                                <?php echo date('M d, Y', strtotime($row["created_at"])); ?>
                                            </p>
                                        </div>
                                    </div>
                                    <?php if ($row["points"] > 0): ?>
                                        <div class="bg-[#436d2e] text-white px-4 py-2 rounded-xl flex items-center gap-2">
                                            <i class="fa-solid fa-star"></i>
                                            <?php echo $row["points"]; ?> Points
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="pl-16">
                                    <p class="text-white/60">
                                        <?php echo htmlspecialchars($row["center_address"]); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center bg-white/5 backdrop-blur-sm p-12 rounded-xl">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#436d2e] rounded-full mb-4">
                                <i class="fa-solid fa-clipboard text-white text-2xl"></i>
                            </div>
                            <h3 class="text-white text-xl font-bold mb-2">No Records Yet</h3>
                            <p class="text-white/80 mb-6">Start your recycling journey today!</p>
                            <a href="add-remit.php" class="inline-flex items-center justify-center px-6 py-3 bg-[#436d2e] text-white rounded-xl hover:bg-opacity-90 transition-all">
                                <i class="fa-solid fa-plus mr-2"></i>
                                Add New Record
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    document.getElementById('searchRecord').addEventListener('input', function(e) {
        const searchText = e.target.value.toLowerCase();
        const records = document.querySelectorAll('.space-y-4 > div');
        
        records.forEach(record => {
            const text = record.textContent.toLowerCase();
            record.style.display = text.includes(searchText) ? 'block' : 'none';
        });
    });
    </script>
</body>
</html>