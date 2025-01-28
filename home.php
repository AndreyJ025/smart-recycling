<?php 
ob_clean(); 
session_start(); 
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
      .dropdown-menu {
        transform-origin: top;
        transition: all 0.2s ease;
      }
      .dropdown-menu {
          transform-origin: top;
          transform: scale(0.95);
          opacity: 0;
          transition: all 0.1s ease-in-out;
      }

      .dropdown-menu:not(.hidden) {
          transform: scale(1);
          opacity: 1;
      }
    </style>
  </head>
  <body class="font-[Poppins]">
    <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
      <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center">
          <div class="flex-shrink-0 flex items-center gap-3">
              <img src="smart-recycling-logo.jpg" alt="Smart Recycling Logo" class="h-10">
              <h1 class="text-[#22c55e] text-2xl font-bold">EcoLens</h1>
          </div>
          
          <!-- Desktop Menu -->
          <div class="hidden md:flex items-center space-x-4">
            <a href="home.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Home</a>
            <a href="camera.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Camera</a>
            <a href="chatbot.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Chatbot</a>
            
            <!-- Profile Dropdown -->
            <div class="relative">
            <button onclick="toggleDropdown()" 
                    class="dropdown-toggle flex items-center text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">
                <i class="fa-solid fa-user mr-2"></i>
                <?php echo $_SESSION["user_fullname"] ?? "Profile" ?>
                <i class="fa-solid fa-chevron-down ml-2 text-sm"></i>
            </button>
              
              <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-48 rounded-xl bg-[#1b1b1b] shadow-lg border border-white/10 dropdown-menu">
                <div class="py-2">
                  <a href="profile.php" class="block px-4 py-2 text-white hover:bg-white hover:text-black transition-all">
                    <i class="fa-solid fa-user-circle mr-2"></i> View Profile
                  </a>
                  <hr class="my-2 border-white/10">
                  <a href="index.php" class="block px-4 py-2 text-white hover:bg-white hover:text-black transition-all">
                    <i class="fa-solid fa-right-from-bracket mr-2"></i> Logout
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- Mobile Menu Button -->
          <div class="md:hidden">
            <button onclick="toggleMenu()" class="text-white p-2">
              <i class="fa-solid fa-bars text-2xl"></i>
            </button>
          </div>
        </div>

        <!-- Mobile Menu Panel -->
        <div id="mobileMenu" class="hidden md:hidden mt-2">
          <div class="flex flex-col space-y-2">
            <a href="home.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Home</a>
            <a href="camera.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Camera</a>
            <a href="chatbot.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Chatbot</a>
            <a href="profile.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">
              <i class="fa-solid fa-user mr-2"></i> Profile
            </a>
            <a href="index.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">
              <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
          </div>
        </div>
      </div>
    </nav>

    <div class="bg-overlay">
      <div class="min-h-screen flex items-center justify-center px-4">
        <div class="text-center max-w-4xl mx-auto pt-20">
          <h1 class="text-4xl md:text-6xl font-semibold text-white mb-12">
            Welcome <?php echo $_SESSION["user_fullname"] ?? "" ?>!
          </h1>

          <?php if(isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == 1): ?>
          <div class="mb-16">
            <h2 class="text-3xl md:text-4xl font-semibold text-white mb-8">Admin Controls</h2>
            <div class="flex flex-col md:flex-row justify-center gap-6">
              <a href="add-sortation.php" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white bg-white text-black rounded-md hover:bg-opacity-90 transition-all text-xl font-medium">
                <i class="fa-solid fa-plus mr-2"></i> Add Center
              </a>
              <a href="view-remit.php" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white rounded-md hover:bg-white hover:text-black transition-all text-xl font-medium">
                <i class="fa-solid fa-list-check mr-2"></i> View Records
              </a>
            </div>
          </div>
          <?php endif; ?>

          <div>
            <h2 class="text-3xl md:text-4xl font-semibold text-white mb-8">Quick Actions</h2>
            <div class="flex flex-col md:flex-row justify-center gap-6">
              <a href="add-remit.php" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white bg-white text-black rounded-md hover:bg-opacity-90 transition-all text-xl font-medium">
                <i class="fa-solid fa-recycle mr-2"></i> Recycle
              </a>
              <a href="view-sortation.php" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white rounded-md hover:bg-white hover:text-black transition-all text-xl font-medium">
                <i class="fa-solid fa-map-location-dot mr-2"></i> Centers
              </a>
              <a href="view-user-remit.php" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white rounded-md hover:bg-white hover:text-black transition-all text-xl font-medium">
                <i class="fa-solid fa-clock-rotate-left mr-2"></i> Records
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <script>

      function toggleDropdown() {
          const dropdown = document.getElementById('profileDropdown');
          dropdown.classList.toggle('hidden');
      }

      // Close dropdown when clicking outside
      document.addEventListener('click', function(event) {
          const dropdown = document.getElementById('profileDropdown');
          const button = event.target.closest('.dropdown-toggle');
          
          if (!button && !dropdown.classList.contains('hidden')) {
              dropdown.classList.add('hidden');
          }
      });

      function toggleMenu() {
        const menu = document.getElementById('mobileMenu');
        menu.classList.toggle('hidden');
      }

      function toggleDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        dropdown.classList.toggle('hidden');
      }

      // Close dropdown when clicking outside
      window.onclick = function(event) {
        if (!event.target.matches('.dropdown-toggle')) {
          const dropdowns = document.getElementsByClassName('dropdown-menu');
          for (let dropdown of dropdowns) {
            if (!dropdown.classList.contains('hidden')) {
              dropdown.classList.add('hidden');
            }
          }
        }
      }
    </script>
  </body>
</html>