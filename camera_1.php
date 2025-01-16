<?php

ob_clean();
session_start();
session_destroy();

?>

<html>
  <head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/axios@0.27.2/dist/axios.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest/dist/tf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet@latest/dist/mobilenet.min.js"></script>
    <script src="recycling_model.js"></script>

    <meta charset="utf-8" />
    <link rel="shortcut icon" type="image/svg+xml" href="favicon.svg" />
    <link rel="stylesheet" href="utils/main.css" />
    <link
      href="https://fonts.googleapis.com/css?family=Roboto:400,700"
      rel="stylesheet"
      type="text/css"
    />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        width: 30%;
        margin-top: 30px;
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

      #chat-history{
        color: white;
        text-align: left;
        padding-bottom: 200px;
      }
      .faq_item{
        background-color: white;
        color: black;
        width: fit-content;
        padding: 10px 20px;
        font-size: 14px;
        border-radius: 100px;
        margin: 10px;
      }


      .bottom_menu{
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
      }
      .bottom_menu .menu_item{
        padding: 10px;
        width: 20%;
        display: inline-block;
      }
      .userInput{
        display: inline-block;
        vertical-align: middle;
        width: 74%;
        padding: 20px 10px;
        margin: 8px 0;
        font-size: 16px;
        border-radius: 100px;
        border: 0;
      }
      .sendButton{
        display: inline-block;
        vertical-align: middle;
        background-color: transparent;
        color: white;
        border: 0;
        margin: 7px;
        font-size: 30px;
      }
    </style>

    <center style="width: 100%;">
      <img class="logo" src="smart-recycling-logo.jpg"/>

      <video id="video" width="640" height="480" autoplay></video>
      <canvas id="canvas" width="640" height="480"></canvas>
      <div id="result"></div>

      <div class="bottom_menu">
        <center>
          <a href="home.php">
            <div class="menu_item"><i class="fa-solid fa-house"></i></div>
          </a>
          <a href="login.php">
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

    <script>
      const video = document.getElementById('video');
      const canvas = document.getElementById('canvas');
      const ctx = canvas.getContext('2d');

      let model;

      async function loadModel() {
        model = await mobilenet.load();
      }

      async function detectObjects() {
        const image = tf.browser.fromPixels(video);
        const predictions = await model.classify(image);

        // Filter predictions for plastic bottles (adjust threshold as needed)
        const bottlePredictions = predictions.filter(prediction => prediction.className === 'plastic bottle' && prediction.probability > 0.7);

        // Draw bounding boxes and labels
        for (const prediction of bottlePredictions) {
          const x = prediction.bbox[0] * video.width;
          const y = prediction.bbox[1] * video.height;
          const width = prediction.bbox[2] * video.width;
          const height = prediction.bbox[3] * video.height;

          ctx.strokeStyle = 'red';
          ctx.lineWidth = 2;
          ctx.strokeRect(x, y, width, height);

          ctx.font = '16px Arial';
          ctx.fillText(prediction.className + ': ' + prediction.probability.toFixed(2), x, y - 10);
        }

        // Request the next frame
        requestAnimationFrame(detectObjects);
      }

      // Start the video and load the model
      navigator.mediaDevices.getUserMedia({ video: true }).then(stream => {
        video.srcObject = stream;
        video.onloadedmetadata = () => {
          video.play();
          loadModel().then(() => {
            detectObjects();
          });
        };
      });
    </script>
  </body>
</html>


