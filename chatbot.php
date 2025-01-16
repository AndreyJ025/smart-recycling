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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>
  <body>
    <script>
      let promptInput;
      let historyElement;

      const questionsFAQ = [
        'How do I use the image recognition feature?',
        'How does the app detect if something is recyclable?',
        'What other features does this app offer?'
      ];

      function sendQuestion(userMessageIndex){
        const answersFAQ = [
          'Navigate to the image recognition section The user must take a photo using their device’s camera Once the image is captured. The app will process the image and analyze it. This may take a few seconds.After the image is processed, the app will display the result After the image is processed, the app will display the recognition results, such as identifying whether the object is recyclable  or providing further recommendations.Based on the results, it will proceed to the recommendation system.',
          'The app first captures an image of the object. <br/>Using image recognition algorithms the app identifies the object in the image captured.This could be done by comparing features of the object (e.g., shape, color, texture) to a pre-trained database of common recyclable items (like plastic bottles, glass jars, or paper).Once the object is recognized, the app classifies it based on its material. The app uses the trained model to determine whether the object is made of recyclable materials (such as certain plastics, metals, glass, or paper).The app then checks a recycling database or set of rules that define which materials are recyclable.After analyzing the material, the app provides feedback to the user, stating whether the object is recyclable or not. It may also include details like which bin to place the item in , a recommendation system or how to properly dispose of it.',
          'Chatbot Feature <br/>Recommendation system <br/>Object Recognition <br/>User Account setting'
        ];
        
        const userMessage = userMessageIndex;

        // Create UI for the new user / assistant messages pair
        historyElement.innerHTML += `<div class="history-item user-role">
          <div class="name"><i class="fa-solid fa-circle-user"></i></div>
          <blockquote>` + questionsFAQ[userMessageIndex] + `</blockquote>
        </div>
        <div class="history-item model-role">
          <div class="name" style="color: greenyellow;"><i class="fa-solid fa-robot"></i></div>
          <blockquote>` + answersFAQ[userMessageIndex] + `</blockquote>
        </div>`;

        scrollToDocumentBottom();
      }
    </script>

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

      <div class="container">
        <div id="chat-history"></div>
      </div>
      <div class="form-container bottom_menu">
        <form id="form">
          <input id="prompt" class="userInput" placeholder="Enter Question here..."/>
          <button type="submit" class="sendButton"><i class="fa-solid fa-paper-plane"></i></button>
        </form>
        <template id="thumb-template">
          <img class="thumb" />
        </template>
      </div>
    </center>

    <script type="module">
      import {
        getGenerativeModel,
        scrollToDocumentBottom,
        updateUI,
      } from "./utils/shared.js";

  
      promptInput = document.querySelector("#prompt");
      historyElement = document.querySelector("#chat-history");
      let chat;

      document
        .querySelector("#form")
        .addEventListener("submit", async (event) => {
          event.preventDefault();

          if (!chat) {
            const model = await getGenerativeModel({ model: "gemini-1.5-flash" });
            chat = model.startChat({
              generationConfig: {
                maxOutputTokens: 100,
              },
            });
          }

          const userMessage = promptInput.value;
          promptInput.value = "";

          // Create UI for the new user / assistant messages pair
          historyElement.innerHTML += `<div class="history-item user-role">
            <div class="name"><i class="fa-solid fa-circle-user"></i></div>
            <blockquote>${userMessage}</blockquote>
          </div>
          <div class="history-item model-role">
            <div class="name" style="color: greenyellow;"><i class="fa-solid fa-robot"></i></div>
            <blockquote></blockquote>
          </div>`;

          scrollToDocumentBottom();
          const resultEls = document.querySelectorAll(
            ".model-role > blockquote",
          );
          await updateUI(
            resultEls[resultEls.length - 1],
            () => chat.sendMessageStream(userMessage),
            true,
          );
        });

      async function startChat(){
        if (!chat) {
          const model = await getGenerativeModel({ model: "gemini-1.5-flash" });
          chat = model.startChat({
            generationConfig: {
              maxOutputTokens: 100,
            },
          });
        }

        const userMessage = "Hello Bot!";
        promptInput.value = "";

        // Create UI for the new user / assistant messages pair
        historyElement.innerHTML += `<div class="history-item user-role">
          <div class="name"><i class="fa-solid fa-circle-user"></i></div>
          <blockquote>${userMessage}</blockquote>
        </div>
        <div class="history-item model-role">
          <div class="name" style="color: greenyellow;"><i class="fa-solid fa-robot"></i></div>
          <blockquote></blockquote>
        </div>`;

        historyElement.innerHTML += `<center><h2>FAQ from Users</h2></center>`;

        questionsFAQ.forEach((element, i) => {
          historyElement.innerHTML += `<center><div class="faq_item" onclick="sendQuestion(` + i + `);">` + element + `</div></center>`;
        });

        scrollToDocumentBottom();
        const resultEls = document.querySelectorAll(
          ".model-role > blockquote",
        );
        await updateUI(
          resultEls[resultEls.length - 1],
          () => chat.sendMessageStream(userMessage),
          true,
        );
      }

      startChat();
    </script>
  </body>
</html>


