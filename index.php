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
      }
      body{
        max-width: 720px;
        margin: auto;
      }
      .logo {
        width: 80%;
        margin-top: 80px;
        margin-bottom: 20px;
      }
      .nav_button{
        text-align: center;
        width: 90%;
        background-color: white;
        color: black;
        font-weight: bold;
        font-size: 40px;
        border-radius: 100px;
        padding: 20px 0;
        margin: 15px 0;
        outline: 0;
        border: 0;
      }
    </style>

    <center>
      <img class="logo" src="smart-recycling-logo.jpg"/>

      <div style="margin-top: 110px;">
        <a href="signup.php">
          <button class="nav_button">SIGN UP</button>
        </a>
        <a href="login.php">
          <button class="nav_button">LOGIN</button>
        </a>
        <a href="guest.php">
          <button class="nav_button">CONTINUE AS GUEST</button>
        </a>
      </div>
    </center>
  </body>
</html>
