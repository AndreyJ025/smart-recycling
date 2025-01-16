<?php ob_clean(); session_start(); ?>

<html>
  <head>
    <meta name="viewport" content="initial-scale=1, width=device-width" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@4.5.2/dist/cosmo/bootstrap.min.css" />
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
        background-color: transparent;
      }
      .logo {
        width: 30%;
        margin-top: 20px;
        margin-bottom: 20px;
      }
      .welcome_text{
        font-size: 28px;
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
        position: fixed;
        bottom: 0;
        left: 0;
        padding: 15px 0;
        width: 100%;
        background-color: white;
      }
      .bottom_menu .menu_item{
        padding: 10px;
        width: 22%;
        display: inline-block;
        font-size: 32px;
      }
    </style>

    <center>
      <img class="logo" src="smart-recycling-logo.jpg"/>

      <p class="welcome_text">Welcome <?php echo $_SESSION["user_fullname"] ?? "" ?>!</p>

      <hr/>

      <div>
        <a href="add-sortation.php"><button class="btn btn-primary w-75">Add New Center</button></a><br/><br/>
        <a href="view-sortation.php"><button class="btn btn-info w-75">View Centers</button></a><br/><br/>
        <a href="view-remit.php"><button class="btn btn-secondary w-75">View Remit Records</button></a>
      </div>

      <hr/>

      <div>
        <a href="add-remit.php"><button class="btn btn-success w-75">Recycle an Item</button></a><br/><br/>
        <a href="view-sortation.php"><button class="btn btn-secondary w-75">My Remit Records</button></a>
      </div>

      <div class="bottom_menu">
        <center>
          <a href="home.php">
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
