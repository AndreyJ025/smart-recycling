<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'database.php';  // Move this to top

// Check admin status
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: home.php");
    exit();
}

// SQL query with error checking
$sql = "SELECT 
            r.*,
            s.name as center_name,
            s.address as center_address,
            u.fullname as user_fullname 
        FROM tbl_remit r
        INNER JOIN tbl_sortation_centers s ON s.id = r.sortation_center_id 
        INNER JOIN tbl_user u ON u.id = r.user_id";
        
$result = $conn->query($sql);
if (!$result) {
    die("Query failed: " . $conn->error);
}

// Points update processing
if(isset($_POST['update_points'])) {
    $remit_id = (int)$_POST['remit_id'];
    $points = (int)$_POST['points'];
    $user_id = (int)$_POST['user_id'];
    
    if($points > 0) {
        try {
            // Start transaction
            $conn->begin_transaction();
            
            $stmt = $conn->prepare("UPDATE tbl_remit SET points = ? WHERE id = ?");
            if(!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("ii", $points, $remit_id);
            if(!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            // Update user points
            $stmt2 = $conn->prepare("UPDATE tbl_user SET total_points = total_points + ? WHERE id = ?");
            if(!$stmt2) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt2->bind_param("ii", $points, $user_id);
            if(!$stmt2->execute()) {
                throw new Exception("Execute failed: " . $stmt2->error);
            }
            
            $conn->commit();
            echo "<script>alert('Points updated successfully!');</script>";
            
        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
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
                <a href="admin-dashboard.php" class="text-white hover:text-[#22c55e] transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Back
                </a>
            </div>
        </div>
    </nav>

    <div class="bg-overlay">
        <div class="min-h-screen pt-24 pb-12 px-4">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl md:text-5xl font-bold text-white text-center mb-6">Manage Records</h2>
                <p class="text-white/80 text-center max-w-3xl mx-auto mb-12">Review and manage recycling records. Assign points to reward eco-friendly actions.</p>
    
                <!-- Stats Overview -->
                <div class="grid md:grid-cols-3 gap-6 mb-12">
                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                        <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-list-check"></i></div>
                        <div class="text-2xl font-bold text-white mb-1"><?php echo $result->num_rows; ?></div>
                        <div class="text-white/60">Total Records</div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                        <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-user-group"></i></div>
                        <div class="text-2xl font-bold text-white mb-1">
                            <?php echo $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM tbl_remit")->fetch_assoc()['count']; ?>
                        </div>
                        <div class="text-white/60">Active Users</div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                        <div class="text-[#436d2e] text-3xl mb-2"><i class="fa-solid fa-star"></i></div>
                        <div class="text-2xl font-bold text-white mb-1">
                            <?php echo $conn->query("SELECT SUM(points) as total FROM tbl_remit")->fetch_assoc()['total'] ?? 0; ?>
                        </div>
                        <div class="text-white/60">Total Points Awarded</div>
                    </div>
                </div>
    
                <!-- Search Bar -->
                <div class="max-w-xl mx-auto mb-12">
                    <div class="relative group">
                        <input type="text" 
                               id="searchRecord" 
                               placeholder="Search records..." 
                               class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all pl-12">
                        <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                    </div>
                </div>
    
                <!-- Records List -->
                <div class="space-y-4">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <div class="group bg-white/5 backdrop-blur-sm p-6 rounded-xl hover:bg-[#436d2e]/20 transition-all">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex items-start gap-4">
                                        <div class="bg-[#436d2e] w-12 h-12 rounded-full flex items-center justify-center shrink-0">
                                            <i class="fa-solid fa-recycle text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-white text-xl font-bold mb-1">
                                                <?php echo htmlspecialchars($row["user_fullname"]); ?>
                                                <span class="text-white/60 text-base font-normal ml-2">
                                                    (<?php echo htmlspecialchars($row["item_quantity"]); ?> items)
                                                </span>
                                            </h3>
                                            <p class="text-white/90 mb-1"><?php echo htmlspecialchars($row["item_name"]); ?></p>
                                            <p class="text-white/80 mb-1"><?php echo htmlspecialchars($row["center_name"]); ?></p>
                                            <p class="text-white/60 text-sm"><?php echo date('M d, Y', strtotime($row["created_at"])); ?></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Points Form -->
                                    <form method="POST" class="flex gap-2">
                                        <input type="hidden" name="remit_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                        <input type="number" 
                                               name="points" 
                                               placeholder="Points" 
                                               class="w-24 px-4 py-2 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all"
                                               min="1" 
                                               required>
                                        <button type="submit" 
                                                name="update_points" 
                                                class="px-4 py-2 bg-[#436d2e] text-white rounded-xl hover:bg-opacity-90 transition-all">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center bg-white/5 backdrop-blur-sm p-12 rounded-xl">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#436d2e] rounded-full mb-4">
                                <i class="fa-solid fa-clipboard text-white text-2xl"></i>
                            </div>
                            <h3 class="text-white text-xl font-bold mb-2">No Records Found</h3>
                            <p class="text-white/80">There are no recycling records to display.</p>
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