<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check session
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

include 'database.php';

// Fetch centers
$lstCenters = [];
$sql = "SELECT * FROM tbl_sortation_centers";
$result = $conn->query($sql);

if ($result) {
    $lstCenters = $result->fetch_all(MYSQLI_ASSOC);
}

// Form processing
if (isset($_POST['submit'])) {
    $errors = [];
    
    // Validate inputs
    if (empty($_POST['center_id'])) {
        $errors[] = "Please select a center";
    }
    if (empty($_POST['item_name'])) {
        $errors[] = "Please enter item description";
    }
    if (empty($_POST['quantity']) || !is_numeric($_POST['quantity'])) {
        $errors[] = "Please enter valid quantity";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO tbl_remit (item_name, sortation_center_id, user_id, item_quantity) VALUES (?, ?, ?, ?)");
            
            $user_id = (int)$_SESSION["user_id"];
            $item_name = trim($_POST['item_name']);
            $center_id = (int)$_POST['center_id'];
            $quantity = (int)$_POST['quantity'];
            
            $stmt->bind_param("siii", $item_name, $center_id, $user_id, $quantity);
            
            if ($stmt->execute()) {
                echo "<script>alert('Record added successfully!'); window.location.href='home.php';</script>";
                exit();
            } else {
                throw new Exception("Error saving record");
            }
        } catch (Exception $e) {
            $errors[] = "Error: " . $e->getMessage();
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
            <div class="w-full max-w-[500px]">
                <!-- Form Container -->
                <div class="bg-white/5 backdrop-blur-md rounded-xl p-8 mt-20">
                    <h2 class="text-white text-2xl font-bold mb-8 text-center">Register Center Remit</h2>

                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-500/20 text-red-200 p-4 rounded-lg mb-6">
                            <?php foreach($errors as $error): ?>
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <p class="font-medium"><?php echo $error; ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-4">
                        <div class="relative">
                            <select name="center_id" 
                                    class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors appearance-none">
                                <option value="" selected disabled>Select Center Name...</option>
                                <?php foreach ($lstCenters as $center): ?>
                                    <option value="<?php echo htmlspecialchars($center['id']); ?>"
                                            class="text-black">
                                        <?php echo htmlspecialchars($center['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fa-solid fa-building absolute right-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                        </div>

                        <div class="relative">
                            <textarea name="item_name" 
                                      placeholder="Enter Items Description..." 
                                      rows="3"
                                      class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors"><?php echo isset($_POST['item_name']) ? htmlspecialchars($_POST['item_name']) : ''; ?></textarea>
                            <i class="fa-solid fa-box absolute right-4 top-6 text-white/50"></i>
                        </div>

                        <div class="relative">
                            <input type="number" 
                                   name="quantity"
                                   placeholder="Enter Item Quantity..."
                                   value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : ''; ?>"
                                   class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors"/>
                            <i class="fa-solid fa-hashtag absolute right-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                        </div>

                        <button type="submit" 
                                name="submit"
                                class="w-full bg-white text-black font-bold text-lg rounded-xl py-4 hover:bg-opacity-90 transition-all">
                            Add New Remit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>