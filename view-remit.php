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
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#7ed957] max-w-[720px] mx-auto px-4 pb-24 lg:max-w-[900px]">
    <div class="flex flex-col items-center">
        <img class="w-[40%] max-w-[300px] mt-[clamp(40px,8vh,80px)] mb-5 md:w-[60%] md:mt-10" 
             src="smart-recycling-logo.jpg"/>

        <p class="text-[clamp(30px,5vw,50px)] font-bold text-white my-5">
            Remit Records
        </p>

        <div class="w-full max-w-[500px] flex flex-col gap-4">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg p-6 shadow-lg">
                        <h3 class="text-[clamp(1.2rem,3vw,1.8rem)] font-bold mb-2">
                            <?php echo htmlspecialchars($row["user_fullname"]); ?>
                        </h3>
                        <p class="font-bold text-gray-800 mb-2">
                            <?php echo htmlspecialchars($row["item_name"]); ?>
                        </p>
                        <p class="text-gray-600 mb-2">
                            <?php echo htmlspecialchars($row["item_quantity"]); ?> PCS.
                        </p>
                        <p class="text-gray-600 mb-2">
                            <?php echo htmlspecialchars($row["center_name"]); ?>
                        </p>
                        <p class="text-gray-600 mb-4">
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
                                      class="px-4 py-2 rounded-lg border border-gray-200 focus:outline-none focus:border-[#7ed957]"
                                      min="1" 
                                      required>
                                <button type="submit" 
                                        name="update_points" 
                                        class="bg-[#7ed957] text-white px-6 py-2 rounded-full hover:bg-opacity-90 transition-all duration-200">
                                    Set Points
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-white text-center">No records found</p>
            <?php endif; ?>
        </div>

        <!-- Bottom Navigation -->
        <div class="fixed bottom-0 left-0 right-0 bg-white py-4 shadow-md z-50 lg:left-1/2 lg:transform lg:-translate-x-1/2 lg:w-[720px] lg:rounded-t-2xl">
            <div class="flex justify-around max-w-[720px] mx-auto lg:px-5">
                <a href="home.php" class="flex flex-col items-center">
                    <div class="text-[clamp(1.5rem,4vw,2rem)] text-[#7ed957] p-3 rounded-full hover:bg-[#7ed957] hover:text-white hover:-translate-y-1 transition-all duration-200">
                        <i class="fa-solid fa-house"></i>
                    </div>
                    <span class="text-xs text-[#7ed957] mt-1">Home</span>
                </a>
                <a href="camera.php" class="flex flex-col items-center">
                    <div class="text-[clamp(1.5rem,4vw,2rem)] text-[#7ed957] p-3 rounded-full hover:bg-[#7ed957] hover:text-white hover:-translate-y-1 transition-all duration-200">
                        <i class="fa-solid fa-camera-retro"></i>
                    </div>
                    <span class="text-xs text-[#7ed957] mt-1">Camera</span>
                </a>
                <a href="chatbot.php" class="flex flex-col items-center">
                    <div class="text-[clamp(1.5rem,4vw,2rem)] text-[#7ed957] p-3 rounded-full hover:bg-[#7ed957] hover:text-white hover:-translate-y-1 transition-all duration-200">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <span class="text-xs text-[#7ed957] mt-1">Chatbot</span>
                </a>
                <a href="index.php" class="flex flex-col items-center">
                    <div class="text-[clamp(1.5rem,4vw,2rem)] text-[#7ed957] p-3 rounded-full hover:bg-[#7ed957] hover:text-white hover:-translate-y-1 transition-all duration-200">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </div>
                    <span class="text-xs text-[#7ed957] mt-1">Logout</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>