<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error_msg = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include 'database.php';
    
    // Sanitize inputs
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);
    
    // Modified query to include is_admin
    $stmt = $conn->prepare("SELECT id, fullname, is_admin FROM tbl_user WHERE username = ? AND password = ? LIMIT 1");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Set session variables including admin status
        $_SESSION["logged_in"] = true;
        $_SESSION["user_id"] = $user['id'];
        $_SESSION["user_fullname"] = $user['fullname'];
        $_SESSION["is_admin"] = $user['is_admin'];
        
        error_log("Login successful - User ID: " . $_SESSION["user_id"] . " Admin: " . $_SESSION["is_admin"]);
        
        header("Location: home.php");
        exit();
    } else {
        $error_msg = "Invalid email or password.";
    }
    
    $stmt->close();
    $conn->close();
}
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
    <div class="flex flex-col items-center w-full">
      <img class="w-[70%] max-w-[400px] mt-[80px] mb-5 md:w-[60%] md:mt-10" 
           src="smart-recycling-logo.jpg"/>

      <form method="POST" class="w-full flex flex-col items-center mt-[50px] max-w-[600px] mx-auto">
        <input class="w-[95%] px-4 py-3 my-2 text-[clamp(1rem,3vw,1.5rem)] rounded-full border-0 focus:outline-none" 
               name="email" 
               type="email" 
               placeholder="Enter Email..." />
               
        <input class="w-[95%] px-4 py-3 my-2 text-[clamp(1rem,3vw,1.5rem)] rounded-full border-0 focus:outline-none" 
               name="password" 
               type="password" 
               placeholder="Enter Password..." />
      
        <div class="h-[20px]"></div>
      
        <button type="submit" 
                class="w-[90%] bg-white text-black font-bold text-[clamp(1.5rem,4vw,2rem)] rounded-full py-4 my-2 hover:bg-gray-100 hover:scale-[1.02] transition-all duration-200">
          LOGIN
        </button>
      </form>
    </div>
  </body>
</html>