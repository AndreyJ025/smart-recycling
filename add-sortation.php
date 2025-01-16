<?php session_start(); ?>

<?php

include 'database.php';

if(!empty($_POST['submit'])){
  // Prepare and bind data
  $sql = "INSERT INTO tbl_sortation_centers (name, address, description, materials, rating, link) VALUES (?, ?, ?, ?, ?, ?)";
  $stmt = $conn->prepare($sql);

  // Adjust data types as needed
  $stmt->bind_param("ssssis", $param1, $param2, $param3, $param4, $param5, $param6);
  
  // Set parameters and execute
  $param1 = $_POST['name'];
  $param2 = $_POST['address'];
  $param3 = $_POST['description'];
  $param4 = $_POST['materials'];
  $param5 = $_POST['rating'];
  $param6 = $_POST['link'];
  $stmt->execute();
  
  if ($stmt->affected_rows > 0) {
      echo '<center><h6 style="font-weight: bold; color: white; margin: 3px;">New record created successfully!</h6></center>';
  } else {
      echo "Error: " . $stmt->error;
  }
  
  $stmt->close();
  $conn->close();
}

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

      <p class="welcome_text">Sortation Centers!</p>


      <div>
        <form method="POST">
          <input class="form-control" type="text" name="name" placeholder="Enter Center Name..."/>
          <input class="form-control" type="text" name="address" placeholder="Enter Center Address..."/>
          <input class="form-control" type="text" name="description" placeholder="Enter Description..."/>
          <input class="form-control" type="text" name="materials" placeholder="Enter Materials (separated,)..."/>
          <input class="form-control" type="text" name="link" placeholder="Enter Website Link..."/>
          <input class="form-control" type="number" name="rating" placeholder="Enter Rating..."/>

          <br/>
          <input class="btn btn-success font-bold w-75" type="submit" name="submit" value="Add New Center"/>
        </form>
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
