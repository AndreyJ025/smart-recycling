<?php

session_start();
session_destroy();

?>

<html>
  <head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  </head>
  <body>
    <style>
      html {
        background-color: #7ed957;
        margin: auto;
      }
      body{
        width: 100%;
      }
      .logo {
        width: 70%;
        margin-top: 80px;
        margin-bottom: 20px;
      }
      .form_text{
        width: 95%;
        padding: 35px 25px;
        margin: 8px 0;
        font-size: 36px;
        border-radius: 100px;
        border: 0;
      }
      .nav_button{
        text-align: center;
        width: 90%;
        background-color: white;
        color: black;
        font-weight: bold;
        font-size: 50px;
        border-radius: 100px;
        padding: 20px 0;
        margin: 15px 0;
        outline: 0;
        border: 0;
      }


      .bottom_menu{
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
      }
      .bottom_menu .menu_item{
        padding: 10px;
        width: 20%;
        display: inline-block;
      }
    </style>

    <center style="width: 100%;">
      <img class="logo" src="smart-recycling-logo.jpg"/>

      <form method="POST">
        <div style="height: 100px;"></div>

        <input class="form_text" name="text" type="fullname" placeholder="Enter Fullname..." value=""/>
        <input class="form_text" name="email" type="email" placeholder="Enter Email..." value=""/>
        <input class="form_text" name="password" type="password" placeholder="Enter Password..." value=""/>
        
        <div style="height: 40px;"></div>

        <input class="nav_button" type="submit" value="SIGN UP"/>
      </form>  
    </center>
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