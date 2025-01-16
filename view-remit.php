<?php session_start(); ?>

<?php

include 'database.php';

?>

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

      <p class="welcome_text">Remit Records</p>

      <div>
        <?php
          // SQL query to fetch data
          $sql = "SELECT * FROM tbl_remit INNER JOIN tbl_sortation_centers ON tbl_sortation_centers.id = tbl_remit.sortation_center_id INNER JOIN tbl_user ON tbl_user.id = tbl_remit.user_id";
          $result = $conn->query($sql);

          // Check if there are results
          if ($result->num_rows > 0) {
            // Output data of each row
            while($row = $result->fetch_assoc()) {
              ?>
              <div class="card bg-light mb-3" style="max-width: 20rem;">
                <div class="card-header"><?= strtoupper($row["fullname"]); ?></div>
                <div class="card-body">
                  <!-- <h4 class="card-title">Light card title</h4> -->
                  <p class="card-text text-bold"><b><?= $row["item_name"]; ?></b></p>
                  <p class="card-text text-bold"><?= $row["item_quantity"]; ?> PCS.</p>
                  <p class="card-text text-bold"><?= $row["name"]; ?></p>
                  <p class="card-text text-bold"><?= $row["address"]; ?></p>

                  <br/>
                  <a href="<?= strtoupper($row["link"]); ?>"><button class="btn btn-success">Set Points</button></a>
                </div>
              </div>
              <?php
            }
          }
        ?>
      </div>

      <br/><br/><br/>

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
