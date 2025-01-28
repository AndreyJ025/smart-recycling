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
                    <img src="smart-recycling-logo.jpg" alt="Smart Recycling Logo" class="h-10">
                    <h1 class="text-[#22c55e] text-2xl font-bold">EcoLens</h1>
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
                <h2 class="text-white text-3xl font-bold mb-8 text-center">Remit Records</h2>

                <div class="space-y-4">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <div class="bg-white/5 backdrop-blur-md rounded-xl p-6">
                                <h3 class="text-white text-xl font-bold mb-2">
                                    <?php echo htmlspecialchars($row["user_fullname"]); ?>
                                </h3>
                                <p class="text-white/90 font-medium mb-2">
                                    <?php echo htmlspecialchars($row["item_name"]); ?>
                                </p>
                                <p class="text-white/80 mb-2">
                                    <?php echo htmlspecialchars($row["item_quantity"]); ?> PCS.
                                </p>
                                <p class="text-white/80 mb-2">
                                    <?php echo htmlspecialchars($row["center_name"]); ?>
                                </p>
                                <p class="text-white/80 mb-4">
                                    <?php echo htmlspecialchars($row["center_address"]); ?>
                                </p>
                                
                                <!-- Points Form -->
                                <form method="POST" class="mt-4">
                                    <input type="hidden" name="remit_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                    <div class="flex gap-2">
                                        <input type="number" 
                                               name="points" 
                                               placeholder="Enter points..." 
                                               class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors"
                                               min="1" 
                                               required>
                                        <button type="submit" 
                                                name="update_points" 
                                                class="px-6 py-4 bg-white text-black font-bold rounded-xl hover:bg-opacity-90 transition-all whitespace-nowrap">
                                            Set Points
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-white text-center bg-white/5 backdrop-blur-md rounded-xl p-8">
                            No records found
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>