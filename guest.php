<?php session_start(); ?>

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
        width: 40%;
        margin-top: 80px;
        margin-bottom: 20px;
      }
      .welcome_text{
        font-size: 60px;
        font-weight: bold;
        color: white;
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

      .bottom_menu{
        position: absolute;
        bottom: 0;
        left: 0;
        padding: 30px 0;
        width: 100%;
        background-color: white;
      }
      .bottom_menu .menu_item{
        padding: 10px;
        width: 21%;
        display: inline-block;
        font-size: 60px;
      }
    </style>

    <center>
      <img class="logo" src="smart-recycling-logo.jpg"/>

      <p class="welcome_text">Welcome GUEST!</p>

      <div class="bottom_menu">
        <center>
          <a href="guest.php">
            <div class="menu_item"><i class="fa-solid fa-house"></i></div>
          </a>
          <a href="camera.php">
            <div class="menu_item"><i class="fa-solid fa-camera-retro"></i></div>
          </a>
          <a href="chatbot.php">
            <div class="menu_item"><i class="fa-solid fa-robot"></i></div>
          </a>
          <a href="index.php">
            <div class="menu_item"><i class="fa-solid fa-right-from-bracket"></i></div>
          </a>
        </center>
      </div>
    </center>
  </body>
</html>
