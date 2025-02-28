<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Admin check
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: ../home.php");
    exit();
}

include '../database.php';

// Form processing
if(isset($_POST['submit'])) {
    $errors = [];
    
    // Validate inputs
    if (empty($_POST['name'])) $errors[] = "Center name is required";
    if (empty($_POST['address'])) $errors[] = "Address is required";
    if (empty($_POST['description'])) $errors[] = "Description is required";
    if (empty($_POST['categories'])) $errors[] = "Categories are required";
    if (empty($_POST['contact'])) $errors[] = "Contact number is required";
    if (!is_numeric($_POST['rating']) || $_POST['rating'] < 1 || $_POST['rating'] > 5) {
        $errors[] = "Rating must be between 1-5";
    }
    
    if(empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO tbl_sortation_centers (name, address, description, categories, contact, rating, link) VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            if(!$stmt) {
                throw new Exception($conn->error);
            }
            
            // Sanitize and prepare data
            $name = trim(htmlspecialchars($_POST['name']));
            $address = trim(htmlspecialchars($_POST['address']));
            $description = trim(htmlspecialchars($_POST['description']));
            $categories = trim(htmlspecialchars($_POST['categories']));
            $contact = trim(htmlspecialchars($_POST['contact']));
            $rating = (int)$_POST['rating'];
            $link = trim(htmlspecialchars($_POST['link']));
            
            $stmt->bind_param("sssssss", $name, $address, $description, $categories, $contact, $rating, $link);
            
            if($stmt->execute()) {
                $_SESSION['success'] = "Recycling center added successfully!";
                header("Location: admin-dashboard.php");
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
                <a href="admin-dashboard.php" class="text-white hover:text-[#22c55e] transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Back
                </a>
            </div>
        </div>
    </nav>

    <div class="bg-overlay">
        <div class="min-h-screen pt-24 pb-12 px-4">
            <div class="max-w-3xl mx-auto">
                <!-- Form Container -->
                <div class="bg-white/5 backdrop-blur-sm p-8 rounded-xl">
                    <h2 class="text-3xl font-bold text-white text-center mb-4">Add Recycling Center</h2>
                    <p class="text-white/80 text-center mb-8">Add a new recycling center to help people find places to recycle their items.</p>
    
                    <?php if(!empty($errors)): ?>
                        <div class="bg-red-500/20 text-red-200 p-4 rounded-xl mb-6">
                            <?php foreach($errors as $error): ?>
                                <div class="flex items-center gap-2 mb-2">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                    <p class="font-medium"><?php echo $error; ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
    
                    <form method="POST" class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="relative group">
                                <label class="block text-white/80 text-sm font-medium mb-2">Center Name</label>
                                <input type="text" name="name" placeholder="Enter center name..." 
                                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                                       class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all">
                                <i class="fa-solid fa-building absolute right-4 top-[60%] -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                            </div>
                    
                            <div class="relative group">
                                <label class="block text-white/80 text-sm font-medium mb-2">Contact Number</label>
                                <input type="text" name="contact" placeholder="e.g., (02) 8123-4567" 
                                       value="<?php echo htmlspecialchars($_POST['contact'] ?? ''); ?>"
                                       class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all">
                                <i class="fa-solid fa-phone absolute right-4 top-[60%] -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                            </div>
                        </div>
                    
                        <div class="relative group">
                            <label class="block text-white/80 text-sm font-medium mb-2">Address</label>
                            <input type="text" name="address" placeholder="Enter complete address..." 
                                   value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>"
                                   class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all">
                            <i class="fa-solid fa-location-dot absolute right-4 top-[60%] -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                        </div>
                    
                        <div class="relative group">
                            <label class="block text-white/80 text-sm font-medium mb-2">Description</label>
                            <textarea name="description" placeholder="Enter opening hours and other important details..." rows="3"
                                      class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <i class="fa-solid fa-align-left absolute right-4 top-11 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                        </div>
                    
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="relative group">
                                <label class="block text-white/80 text-sm font-medium mb-2">Categories</label>
                                <input type="text" name="categories" placeholder="plastic,paper,metal,glass,electronics" 
                                       value="<?php echo htmlspecialchars($_POST['categories'] ?? ''); ?>"
                                       class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all">
                                <i class="fa-solid fa-recycle absolute right-4 top-[60%] -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                                <small class="text-white/60 mt-1 block">Separate with commas, no spaces</small>
                            </div>
                    
                            <div class="relative group">
                                <label class="block text-white/80 text-sm font-medium mb-2">Rating (1-5)</label>
                                <input type="number" name="rating" placeholder="Enter rating..." min="1" max="5"
                                       value="<?php echo htmlspecialchars($_POST['rating'] ?? ''); ?>"
                                       class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all">
                                <i class="fa-solid fa-star absolute right-4 top-[60%] -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                            </div>
                        </div>
                    
                        <div class="relative group">
                            <label class="block text-white/80 text-sm font-medium mb-2">Website/Map Link</label>
                            <input type="text" name="link" placeholder="Enter website or Google Maps URL..." 
                                   value="<?php echo htmlspecialchars($_POST['link'] ?? ''); ?>"
                                   class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all">
                            <i class="fa-solid fa-link absolute right-4 top-[60%] -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                        </div>
                    
                        <button type="submit" name="submit" 
                                class="w-full bg-[#436d2e] text-white px-6 py-4 rounded-xl font-semibold text-lg hover:bg-opacity-90 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-plus"></i>
                            Add Recycling Center
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>