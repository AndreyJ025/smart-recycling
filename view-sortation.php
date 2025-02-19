<?php
session_start();
include 'database.php';

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

// Fetch centers
$sql = "SELECT * FROM tbl_sortation_centers";
$result = $conn->query($sql);

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
            background: url('assets/background.jpg');
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
                    <img src="assets/logo.png" alt="Smart Recycling Logo" class="h-10">
                    <h1 class="text-2xl font-bold">
                        <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                    </h1>
                </div>
                <?php if(isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == 1): ?>
                    <a href="admin/admin-dashboard.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">
                        <i class="fa-solid fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                <?php else: ?>
                    <a href="home.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">
                        <i class="fa-solid fa-arrow-left mr-2"></i>Back to Home
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="bg-overlay">
        <div class="min-h-screen pt-24 pb-12 px-4">
            <div class="max-w-7xl mx-auto">
                <h2 class="text-3xl md:text-5xl font-bold text-white text-center mb-6">Recycling Centers</h2>
                <p class="text-white/80 text-center max-w-3xl mx-auto mb-12">Find the nearest recycling center and contribute to a sustainable future. Each center specializes in different materials.</p>
    
                <!-- Search Bar -->
                <div class="max-w-3xl mx-auto mb-12">
                    <form onsubmit="filterCenters(event)">
                        <div class="grid md:grid-cols-[1fr,auto] gap-4 items-center">
                            <!-- Category Filter -->
                            <select id="categoryFilter" 
                                    class="w-full px-6 py-3.5 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all appearance-none">
                                <option value="" class="text-gray-800">All Categories</option>
                                <option value="plastic" class="text-gray-800">Plastic</option>
                                <option value="paper" class="text-gray-800">Paper</option>
                                <option value="metal" class="text-gray-800">Metal</option>
                                <option value="glass" class="text-gray-800">Glass</option>
                                <option value="electronics" class="text-gray-800">Electronics</option>
                            </select>
                
                            <!-- Apply Filter Button -->
                            <button type="submit" 
                                    class="px-8 py-3.5 bg-[#436d2e] text-white rounded-xl font-semibold hover:bg-opacity-90 transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                                <i class="fa-solid fa-filter"></i>
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
    
                <!-- Centers Grid -->
                <div class="grid md:grid-cols-2 gap-6">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <div class="group bg-white/5 backdrop-blur-sm p-8 rounded-xl hover:bg-[#436d2e]/20 transition-all">
                                <div class="flex items-start gap-4 mb-4">
                                    <div class="bg-[#436d2e] w-12 h-12 rounded-full flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-recycle text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-white text-xl font-bold mb-1">
                                            <?php echo htmlspecialchars($row["name"]); ?>
                                        </h3>
                                        <p class="text-white/90">
                                            <?php echo htmlspecialchars($row["address"]); ?>
                                        </p>
                                    </div>
                                </div>
                                
                                <p class="text-white/80 mb-4 line-clamp-2">
                                    <?php echo htmlspecialchars($row["description"]); ?>
                                </p>
                                
                                <div class="space-y-2 mb-6">
                                    <div class="flex items-center gap-2 text-white/80">
                                        <i class="fa-solid fa-boxes-stacked w-5"></i>
                                        <div class="flex flex-wrap gap-2">
                                            <?php 
                                            $categories = explode(',', $row["categories"]);
                                            foreach($categories as $category): 
                                            ?>
                                                <span class="bg-[#436d2e]/20 text-white/90 text-sm px-3 py-1 rounded-full">
                                                    <?php echo htmlspecialchars(trim($category)); ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 text-white/80">
                                        <i class="fa-solid fa-star w-5 text-[#436d2e]"></i>
                                        <span><?php echo str_repeat('★', $row["rating"]); ?></span>
                                    </div>
                                </div>
    
                                <div class="flex justify-end">
                                    <a href="<?php echo htmlspecialchars($row["link"]); ?>" 
                                       target="_blank"
                                       class="flex items-center justify-center px-6 py-3 bg-[#436d2e] rounded-xl hover:bg-opacity-90 transition-all">
                                        <i class="fa-solid fa-location-dot text-white mr-2"></i>
                                        <span class="text-white font-semibold">View on Maps</span>
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="md:col-span-2 text-center bg-white/5 backdrop-blur-sm p-12 rounded-xl">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#436d2e] rounded-full mb-4">
                                <i class="fa-solid fa-map-location-dot text-white text-2xl"></i>
                            </div>
                            <h3 class="text-white text-xl font-bold mb-2">No Centers Found</h3>
                            <p class="text-white/80">Please check back later for updated listings.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function filterCenters(event) {
            if (event) event.preventDefault();
            
            try {
                // Get filter values with null check
                const categorySelect = document.getElementById('categoryFilter');
                if (!categorySelect) return;
    
                const selectedCategory = categorySelect.value.toLowerCase();
                const centers = document.querySelectorAll('.grid > .group');
                const noResults = document.querySelector('.md\\:col-span-2');
                let found = false;
        
                centers.forEach(center => {
                    try {
                        // Get categories
                        const categoryTags = center.querySelectorAll('.flex-wrap span');
                        const categories = Array.from(categoryTags).map(tag => tag.textContent.toLowerCase().trim());
                        
                        // Category match - only check if category is selected
                        const matchesCategory = selectedCategory === '' || categories.includes(selectedCategory);
                        
                        // Show/hide based on category match
                        center.style.display = matchesCategory ? '' : 'none';
                        if (matchesCategory) found = true;
                    } catch (error) {
                        console.error('Error processing center:', error);
                    }
                });
        
                // Toggle no results message
                if (noResults) {
                    noResults.style.display = !found ? '' : 'none';
                }
            } catch (error) {
                console.error('Error filtering centers:', error);
            }
        }
    
        // Initialize event listeners when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('form');
            const categorySelect = document.getElementById('categoryFilter');
            
            if (form && categorySelect) {
                form.addEventListener('submit', filterCenters);
                categorySelect.addEventListener('change', filterCenters);
            }
        });
    </script>
</body>
</html>