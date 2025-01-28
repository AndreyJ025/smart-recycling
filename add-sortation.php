<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Admin check
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: home.php");
    exit();
}

include 'database.php';

// Form processing
if(isset($_POST['submit'])) {
    $errors = [];
    
    // Validate inputs
    if (empty($_POST['name'])) $errors[] = "Center name is required";
    if (empty($_POST['address'])) $errors[] = "Address is required";
    if (empty($_POST['description'])) $errors[] = "Description is required";
    if (empty($_POST['materials'])) $errors[] = "Materials are required";
    if (!is_numeric($_POST['rating']) || $_POST['rating'] < 1 || $_POST['rating'] > 5) {
        $errors[] = "Rating must be between 1-5";
    }
    
    if(empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO tbl_sortation_centers (name, address, description, materials, rating, link) VALUES (?, ?, ?, ?, ?, ?)");
            
            if(!$stmt) {
                throw new Exception($conn->error);
            }
            
            // Sanitize and prepare data
            $name = trim(htmlspecialchars($_POST['name']));
            $address = trim(htmlspecialchars($_POST['address']));
            $description = trim(htmlspecialchars($_POST['description']));
            $materials = trim(htmlspecialchars($_POST['materials']));
            $rating = (int)$_POST['rating'];
            $link = trim(htmlspecialchars($_POST['link']));
            
            $stmt->bind_param("ssssis", $name, $address, $description, $materials, $rating, $link);
            
            if($stmt->execute()) {
                header("Location: home.php");
                exit();
            } else {
                throw new Exception("Error executing query");
            }
            
        } catch(Exception $e) {
            $errors[] = "Database Error: " . $e->getMessage();
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
                    <h2 class="text-white text-2xl font-bold mb-8 text-center">Add Sortation Center</h2>

                    <?php if(!empty($errors)): ?>
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
                            <input type="text" name="name" placeholder="Enter Center Name..." 
                                   class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors">
                            <i class="fa-solid fa-building absolute right-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                        </div>

                        <div class="relative">
                            <input type="text" name="address" placeholder="Enter Center Address..." 
                                   class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors">
                            <i class="fa-solid fa-location-dot absolute right-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                        </div>

                        <div class="relative">
                            <textarea name="description" placeholder="Enter Description..." rows="3"
                                      class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors"></textarea>
                            <i class="fa-solid fa-align-left absolute right-4 top-6 text-white/50"></i>
                        </div>

                        <div class="relative">
                            <input type="text" name="materials" placeholder="Enter Materials (separated by comma)" 
                                   class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors">
                            <i class="fa-solid fa-recycle absolute right-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                        </div>

                        <div class="relative">
                            <input type="text" name="link" placeholder="Enter Website Link..." 
                                   class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors">
                            <i class="fa-solid fa-link absolute right-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                        </div>

                        <div class="relative">
                            <input type="number" name="rating" placeholder="Enter Rating (1-5)" min="1" max="5"
                                   class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors">
                            <i class="fa-solid fa-star absolute right-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                        </div>

                        <button type="submit" name="submit" 
                                class="w-full bg-white text-black font-bold text-lg rounded-xl py-4 hover:bg-opacity-90 transition-all">
                            Add Center
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>