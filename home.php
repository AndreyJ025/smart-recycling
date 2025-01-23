<?php 
ob_clean(); 
session_start(); 
?>

<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" 
          integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" 
          crossorigin="anonymous" 
          referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com"></script>
  </head>
  <body class="bg-[#7ed957] max-w-[720px] mx-auto px-4 pb-24 lg:max-w-[900px]">
    <div class="flex flex-col items-center">
      <img class="w-[40%] max-w-[300px] mt-[clamp(40px,8vh,80px)] mb-5 md:w-[60%] md:mt-10" 
           src="smart-recycling-logo.jpg"/>

      <p class="text-[clamp(30px,5vw,50px)] font-bold text-white my-5">
        Welcome <?php echo $_SESSION["user_fullname"] ?? "" ?>!
      </p>

      <hr class="w-full border-white my-4"/>
          
      <!-- Admin Only Options -->
      <?php if(isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == 1): ?>
        <div class="w-full flex flex-col items-center gap-2">
          <a href="add-sortation.php" class="w-[80%] max-w-[400px]">
            <button class="w-full bg-white text-black font-bold text-[clamp(0.8rem,2.5vw,1.5rem)] rounded-full py-2 hover:bg-gray-100 hover:scale-[1.02] transition-all duration-200">
              Add New Center
            </button>
          </a>
          <a href="view-remit.php" class="w-[80%] max-w-[400px]">
            <button class="w-full bg-white text-black font-bold text-[clamp(0.8rem,2.5vw,1.5rem)] rounded-full py-2 hover:bg-gray-100 hover:scale-[1.02] transition-all duration-200">
              View Remit Records
            </button>
          </a>
        </div>
        <hr class="w-full border-white my-4"/>
      <?php endif; ?>
        
      <!-- User Options - Available to All -->
      <div class="w-full flex flex-col items-center gap-2">
        <a href="add-remit.php" class="w-[80%] max-w-[400px]">
          <button class="w-full bg-white text-black font-bold text-[clamp(0.8rem,2.5vw,1.5rem)] rounded-full py-2 hover:bg-gray-100 hover:scale-[1.02] transition-all duration-200">
            Recycle an Item
          </button>
        </a>
        <a href="view-sortation.php" class="w-[80%] max-w-[400px]">
            <button class="w-full bg-white text-black font-bold text-[clamp(0.8rem,2.5vw,1.5rem)] rounded-full py-2 hover:bg-gray-100 hover:scale-[1.02] transition-all duration-200">
              View Centers
            </button>
          </a>
        <a href="view-user-remit.php" class="w-[80%] max-w-[400px]">
          <button class="w-full bg-white text-black font-bold text-[clamp(0.8rem,2.5vw,1.5rem)] rounded-full py-2 hover:bg-gray-100 hover:scale-[1.02] transition-all duration-200">
            My Remit Records
          </button>
        </a>
      </div>

        <hr class="w-full border-white my-4"/>

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