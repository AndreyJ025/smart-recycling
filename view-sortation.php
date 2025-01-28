<?php
session_start();
include 'database.php';

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
                <h2 class="text-white text-3xl font-bold mb-8 text-center">Sortation Centers</h2>

                <div class="space-y-4">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <div class="bg-white/5 backdrop-blur-md rounded-xl p-6">
                                <h3 class="text-white text-xl font-bold mb-2">
                                    <?php echo htmlspecialchars($row["name"]); ?>
                                </h3>
                                <p class="text-white/90 mb-2">
                                    <?php echo htmlspecialchars($row["address"]); ?>
                                </p>
                                <p class="text-white/80 mb-4">
                                    <?php echo htmlspecialchars($row["description"]); ?>
                                </p>
                                <div class="text-white/80 flex items-center gap-2 mb-2">
                                    <span class="font-bold">Materials:</span>
                                    <?php echo htmlspecialchars($row["materials"]); ?>
                                </div>
                                <div class="text-white/80 flex items-center gap-2 mb-4">
                                    <span class="font-bold">Rating:</span>
                                    <?php echo str_repeat('⭐', $row["rating"]); ?>
                                </div>
                                <a href="<?php echo htmlspecialchars($row["link"]); ?>" 
                                   target="_blank"
                                   class="inline-block bg-white text-black px-6 py-2 rounded-xl font-bold hover:bg-opacity-90 transition-all">
                                    Visit Website
                                </a>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-white text-center bg-white/5 backdrop-blur-md rounded-xl p-8">
                            No centers found
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>