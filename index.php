<?php
session_start();
session_destroy();
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
  <body class="bg-[#7ed957] max-w-[720px] mx-auto px-4 lg:max-w-[900px]">
    <div class="flex flex-col items-center">
      <img 
        class="w-[80%] max-w-[400px] mt-[5vh] mb-5 md:w-[60%] md:mt-10" 
        src="smart-recycling-logo.jpg"
      />

      <div class="mt-[110px] flex flex-col items-center w-full">
        <a href="signup.php" class="w-[90%] max-w-[500px]">
          <button class="w-full bg-white text-black font-bold text-[clamp(1.2rem,4vw,2.5rem)] rounded-full py-4 my-2.5 hover:bg-gray-100 hover:scale-[1.02] transition-all duration-200">
            SIGN UP
          </button>
        </a>
        <a href="login.php" class="w-[90%] max-w-[500px]">
          <button class="w-full bg-white text-black font-bold text-[clamp(1.2rem,4vw,2.5rem)] rounded-full py-4 my-2.5 hover:bg-gray-100 hover:scale-[1.02] transition-all duration-200">
            LOGIN
          </button>
        </a>
        <a href="guest.php" class="w-[90%] max-w-[500px]">
          <button class="w-full bg-white text-black font-bold text-[clamp(1.2rem,4vw,2.5rem)] rounded-full py-4 my-2.5 hover:bg-gray-100 hover:scale-[1.02] transition-all duration-200">
            CONTINUE AS GUEST
          </button>
        </a>
      </div>
    </div>
  </body>
</html>