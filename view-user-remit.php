<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'database.php';

// Check user login
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$total_points_sql = "SELECT total_points FROM tbl_user WHERE id = ?";
$stmt_points = $conn->prepare($total_points_sql);
$stmt_points->bind_param("i", $_SESSION["user_id"]);
$stmt_points->execute();
$total_points_result = $stmt_points->get_result();
$total_points = $total_points_result->fetch_assoc()["total_points"] ?? 0;

// SQL query filtered by user_id
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
            My Remit Records
        </p>

        <div class="w-full max-w-[500px] bg-white rounded-lg p-6 shadow-lg mb-4">
            <p class="text-[clamp(1.2rem,3vw,1.8rem)] font-bold text-center">
                Total Points: <?php echo $total_points; ?>
            </p>
        </div>

        <div class="w-full max-w-[500px] flex flex-col gap-4">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="bg-white rounded-lg p-6 shadow-lg">
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
                        <?php if ($row["points"] > 0): ?>
                            <div class="bg-green-100 text-green-800 px-4 py-2 rounded-full inline-block">
                                Points Earned: <?php echo $row["points"]; ?>
                            </div>
                        <?php endif; ?>
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