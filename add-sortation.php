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
                echo "<script>alert('Center added successfully!'); window.location.href='home.php';</script>";
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
            Add Sortation Center
        </p>

        <form method="POST" class="w-full max-w-[500px] flex flex-col gap-4">
            <input type="text" 
                   name="name" 
                   placeholder="Enter Center Name..."
                   class="w-full px-4 py-3 text-[clamp(1rem,3vw,1.5rem)] rounded-lg border-0 focus:outline-none"/>
            
            <input type="text" 
                   name="address" 
                   placeholder="Enter Center Address..."
                   class="w-full px-4 py-3 text-[clamp(1rem,3vw,1.5rem)] rounded-lg border-0 focus:outline-none"/>
            
            <textarea name="description" 
                      placeholder="Enter Description..." 
                      class="w-full px-4 py-3 text-[clamp(1rem,3vw,1.5rem)] rounded-lg border-0 focus:outline-none"
                      rows="3"></textarea>
            
            <input type="text" 
                   name="materials" 
                   placeholder="Enter Materials (separated,)..."
                   class="w-full px-4 py-3 text-[clamp(1rem,3vw,1.5rem)] rounded-lg border-0 focus:outline-none"/>
            
            <input type="text" 
                   name="link" 
                   placeholder="Enter Website Link..."
                   class="w-full px-4 py-3 text-[clamp(1rem,3vw,1.5rem)] rounded-lg border-0 focus:outline-none"/>
            
            <input type="number" 
                   name="rating" 
                   placeholder="Enter Rating..."
                   class="w-full px-4 py-3 text-[clamp(1rem,3vw,1.5rem)] rounded-lg border-0 focus:outline-none"/>

            <button type="submit" 
                    name="submit"
                    class="w-full bg-white text-black font-bold text-[clamp(1.2rem,4vw,2rem)] rounded-full py-4 mt-4 hover:bg-gray-100 hover:scale-[1.02] transition-all duration-200">
                Add New Center
            </button>
        </form>

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