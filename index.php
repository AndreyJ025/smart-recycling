<?php
session_start();
session_destroy();
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
    <div class="bg-overlay">
        <div class="min-h-screen flex flex-col items-center justify-center px-4">
            <div class="w-full max-w-[500px] text-center">
                <!-- Logo Container -->
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-8 mb-12 transform hover:scale-105 transition-all">
                    <img src="assets/logo.png" 
                         alt="Smart Recycling Logo" 
                         class="w-[80%] max-w-[250px] mx-auto mb-4" />
                    <h1 class="text-3xl font-bold">
                        <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                    </h1>
                </div>
    
                <!-- Buttons Container -->
                <div class="space-y-4">
                    <a href="auth/signup.php" class="block">
                        <button class="w-full bg-[#436d2e] text-white font-bold text-lg rounded-xl py-4 hover:bg-opacity-90 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-user-plus"></i>
                            Register
                        </button>
                    </a>
                    
                    <a href="auth/login.php" class="block">
                        <button class="w-full bg-white/10 backdrop-blur-sm text-white font-bold text-lg rounded-xl py-4 hover:bg-white hover:text-black transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            Login
                        </button>
                    </a>
                    
                    <a href="home.php" class="block">
                        <button class="w-full bg-white/5 backdrop-blur-sm text-white font-bold text-lg rounded-xl py-4 hover:bg-white hover:text-black transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-user"></i>
                            Continue as Guest
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>