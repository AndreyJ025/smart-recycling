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
    <div class="flex flex-col items-center w-full">
      <img class="w-[70%] max-w-[400px] mt-[80px] mb-5 md:w-[60%] md:mt-10" 
           src="smart-recycling-logo.jpg"/>

      <form method="POST" class="w-full flex flex-col items-center mt-[50px] max-w-[600px] mx-auto">
        <input class="w-[95%] px-4 py-3 my-2 text-[clamp(1rem,3vw,1.5rem)] rounded-full border-0 focus:outline-none" 
               name="fullname" 
               type="text" 
               placeholder="Enter Fullname..." />
               
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
          SIGN UP
        </button>
      </form>
    </div>
  </body>
</html>

<?php

// Handle login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $fullname = $_POST["fullname"]; 
  $email = $_POST["email"];
  $password = $_POST["password"]; 

  // Database connection parameters
  // $servername = "localhost";
  // $username = "alldzqjh_smart_recycling";
  // $pass = "RPe4fmEzfngBc@U";
  // $dbname = "alldzqjh_smart_recycling";

  $servername = "localhost";
  $username = "root";
  $pass = "";
  $dbname = "smart_recycling";

  // Create a connection
  $conn = new mysqli($servername, $username, $pass, $dbname);

  // Check connection
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error); 
  }

  // Prepare and execute SQL statement to check if credentials are valid
  $sql = "INSERT INTO tbl_user (fullname, username, password) VALUES ('$fullname', '$email', '$password')";
  $result = $conn->query($sql);

  if ($result) {
    // Credentials are valid, log in the user    
    header("location: login.php");
  } else {
    // Credentials are invalid, display an error message
    echo "<center>Registration Failed!</center>";
  }

  // Close the connection
  $conn->close();
}

?>