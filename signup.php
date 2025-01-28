<?php
ob_start();
session_start();
session_destroy();

$error_msg = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST["fullname"]; 
    $email = $_POST["email"];
    $password = $_POST["password"]; 

    $servername = "localhost";
    $username = "root";
    $pass = "";
    $dbname = "smart_recycling";

    $conn = new mysqli($servername, $username, $pass, $dbname);

    if ($conn->connect_error) {
        $error_msg = "Connection failed: " . $conn->connect_error;
    } else {
        $sql = "INSERT INTO tbl_user (fullname, username, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $fullname, $email, $password);
        
        if ($stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            $error_msg = "Registration Failed!";
        }
        $stmt->close();
        $conn->close();
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
    <div class="bg-overlay">
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="w-full max-w-[500px]">
                <!-- Back Button -->
                <a href="index.php" class="inline-flex items-center text-white mb-8 hover:text-[#22c55e] transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Back to Home
                </a>

                <!-- Signup Container -->
                <div class="bg-white/5 backdrop-blur-md rounded-xl p-8">
                    <!-- Logo Section -->
                    <div class="text-center mb-8">
                        <img src="logo.png" alt="Smart Recycling Logo" class="w-[40%] max-w-[200px] mx-auto mb-4">
                        <h1 class="text-2xl font-bold">
                            <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                        </h1>
                    </div>

                    <!-- Signup Form -->
                    <form method="POST" class="space-y-4">
                        <div class="relative">
                            <input type="text" name="fullname" placeholder="Enter Fullname..." 
                                   class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors"
                                   required>
                            <i class="fa-regular fa-user absolute right-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                        </div>

                        <div class="relative">
                            <input type="email" name="email" placeholder="Enter Email..." 
                                   class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors"
                                   required>
                            <i class="fa-regular fa-envelope absolute right-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                        </div>

                        <div class="relative">
                            <input type="password" name="password" placeholder="Enter Password..." 
                                   class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#22c55e] transition-colors"
                                   required>
                            <i class="fa-regular fa-lock absolute right-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                        </div>

                        <button type="submit" class="w-full bg-white text-black font-bold text-lg rounded-xl py-4 hover:bg-opacity-90 transition-all">
                            SIGN UP
                        </button>
                    </form>

                    <!-- Login Link -->
                    <p class="text-white/80 text-center mt-6">
                        Already have an account? 
                        <a href="login.php" class="text-[#22c55e] hover:text-white transition-all">Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>