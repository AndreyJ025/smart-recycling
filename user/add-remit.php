<?php
session_start();
require_once '../database.php';

// Check if user is logged in, if not redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

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
                echo "<script>alert('Record added successfully!'); window.location.href='../home.php';</script>";
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
            <div class="max-w-3xl mx-auto">
                <!-- Form Container -->
                <div class="bg-white/5 backdrop-blur-sm p-8 rounded-xl">
                    <h2 class="text-3xl font-bold text-white text-center mb-8">Record Your Recycling</h2>
                    <p class="text-white/80 text-center mb-8">Help create a sustainable future by recording your recycling contributions. Every item counts towards a cleaner planet.</p>
    
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
    
                    <form method="POST" class="space-y-6">
                        <div class="relative group">
                            <select name="center_id" 
                                    class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all appearance-none">
                                <option value="" selected disabled>Select Recycling Center...</option>
                                <?php foreach ($lstCenters as $center): ?>
                                    <option value="<?php echo htmlspecialchars($center['id']); ?>"
                                            class="text-black">
                                        <?php echo htmlspecialchars($center['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fa-solid fa-building absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                        </div>
    
                        <div class="relative group">
                            <textarea name="item_name" 
                                    placeholder="Describe the items you're recycling..." 
                                    rows="3"
                                    class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all"><?php echo isset($_POST['item_name']) ? htmlspecialchars($_POST['item_name']) : ''; ?></textarea>
                            <i class="fa-solid fa-recycle absolute right-4 top-6 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                        </div>
    
                        <div class="relative group">
                            <input type="number" 
                                   name="quantity"
                                   placeholder="Number of items..."
                                   value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : ''; ?>"
                                   class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all"/>
                            <i class="fa-solid fa-hashtag absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                        </div>
    
                        <button type="submit" 
                                name="submit"
                                class="w-full bg-[#436d2e] text-white font-bold text-lg rounded-xl py-4 hover:bg-opacity-90 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i>
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>