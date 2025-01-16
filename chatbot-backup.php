<?php

ob_clean();
session_start();
session_destroy();

?>

<html>
  <head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.jsdelivr.net/npm/axios@0.27.2/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gemini-js@latest/dist/gemini.min.js"></script>
    <meta charset="utf-8" />
    <link rel="shortcut icon" type="image/svg+xml" href="favicon.svg" />
    <link rel="stylesheet" href="utils/main.css" />
    <link
      href="https://fonts.googleapis.com/css?family=Roboto:400,700"
      rel="stylesheet"
      type="text/css"
    />
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
        width: 40%;
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
      #userInput{
        display: inline-block;
        vertical-align: middle;
        width: 85%;
        padding: 35px 25px;
        margin: 8px 0;
        font-size: 36px;
        border-radius: 100px;
        border: 0;
      }
      #sendButton{
        display: inline-block;
        vertical-align: middle;
        background-color: transparent;
        color: white;
        border: 0;
        margin: 5px;
        font-size: 60px;
      }
    </style>

    <center style="width: 100%;">
      <img class="logo" src="smart-recycling-logo.jpg"/>

      <div id="chatContainer">

      </div>
      <div>
        <textarea id="userInput" placeholder="Enter Question Here..."></textarea>
        <button id="sendButton"><i class="fa-solid fa-paper-plane"></i></button>
      </div> 
    </center>

    <script>
      // // Get the API key from your OpenAI account
      // const apiKey = "sk-nY0MKIiPJrkX2RCXGMR-xFOmh6SMnMIlnktmsm5me8T3BlbkFJtRSfwVFodIlyyNurL8ke2kGodSh2GFEF2uImYSMLgA";

      // // Get references to DOM elements
      // const chatBox = document.getElementById("chatContainer");
      // const messageInput = document.getElementById("userInput");
      // const sendButton = document.getElementById("sendButton");

      // // Function to send a message and receive a response
      // async function sendMessage() {
      //   const message = messageInput.value;
      //   if (message.trim() === "") {
      //     return; // Ignore empty messages
      //   }

      //   // Create a new message element
      //   const messageElement = document.createElement("div");
      //   messageElement.classList.add("message", "sent");
      //   messageElement.textContent = message;
      //   chatBox.appendChild(messageElement);

      //   try {
      //     // Send the message to the ChatGPT API
      //     const response = await fetch("https://api.openai.com/v1/completions", {
      //       method: "POST",
      //       headers: {
      //         "Content-Type": "application/json",
      //         "Authorization": `Bearer ${apiKey}`,
      //       },
      //       body: JSON.stringify({
      //         model: "gpt-4o-mini", // You can experiment with different models
      //         prompt: message,
      //         temperature: 0.7, // Adjust temperature for creativity
      //         max_tokens: 1024, // Adjust max_tokens for response length
      //       }),
      //     });

      //     // Parse the response and display it
      //     const data = await response.json();
      //     const chatGPTResponse = data.choices[0].text.trim();

      //     const chatGPTMessageElement = document.createElement("div");
      //     chatGPTMessageElement.classList.add("message", "received");
      //     chatGPTMessageElement.textContent = chatGPTResponse;
      //     chatBox.appendChild(chatGPTMessageElement);

      //     // Scroll to the bottom of the chat box
      //     chatBox.scrollTop = chatBox.scrollHeight;
      //   } catch (error) {
      //     console.error("Error:", error);
      //     // Handle errors gracefully (e.g., display an error message to the user)
      //   }

      //   // Clear the input field
      //   messageInput.value = "";
      // }

      // // Event listener for the send button
      // sendButton.addEventListener("click", sendMessage);

      // // Event listener for the message input (enter key press)
      // messageInput.addEventListener("keydown", (event) => {
      //   if (event.key === "Enter") {
      //     sendMessage();
      //   }
      // });

      const gemini = new Gemini({
        apiKey: "AIzaSyAZcppSJ4afurjiS1BvoTi2F6F8ZpyUCqs", // Replace with your actual API key
        
      });

      function sendMessage() {
        const input = document.getElementById("userInput");
        const message = input.value;
        if (message.trim() !== "") {
          // Send the message to the Gemini API
          gemini.generateText({
            prompt: message,
          })
            .then((response) => {
              // Display the bot's response
              const botResponse = response.text;
              displayMessage("bot", botResponse);
              input.value = "";
            })
            .catch((error) => {
              console.error("Error:", error);
            });
        }
      }

      function displayMessage(sender, message) {
        const chatContainer = document.getElementById("chatContainer");
        const messageElement = document.createElement("div");

        messageElement.className = `message ${sender}`;
        messageElement.textContent = message;
        chatContainer.appendChild(messageElement);
      }

      const sendButton = document.getElementById("sendButton");
      sendButton.addEventListener("click", sendMessage);

      const input = document.getElementById("userInput");
      input.addEventListener("keypress", (event) => {
        if (event.key === "Enter") {
          sendMessage();
        }
      });
    </script>
  </body>
</html>


