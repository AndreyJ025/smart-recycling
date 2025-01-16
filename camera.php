<?php

session_start();
header('Access-Control-Allow-Origin: *');

?>

<html>
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script src="https://cdn.jsdelivr.net/npm/axios@0.27.2/dist/axios.min.js"></script>

        <!-- Load TensorFlow.js. This is required to use MobileNet. -->
        <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.17.0"> </script>
        <!-- Load the MobileNet model. -->
        <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet@2.1.1"> </script>

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
            function showTab(n) {
                var x = document.getElementsByClassName("tab-content-item");
                for (i = 0; i < x.length; i++) {
                    x[i].style.display = "none";
                }
                
                document.getElementById("tab" + (n + 1)).style.display = "block";
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
            width: 10%;
            margin-top: 10px;
            margin-bottom: 10px;
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
        .text_result{
            text-align: center;
            width: 90%;
            background-color: white;
            color: black;
            font-weight: bold;
            font-size: 50px;
            padding: 20px 0;
            margin: 5px 0;
            outline: 0;
            border: 0;
        }
        .text_result_light{
            color: black;
            font-weight: bold;
            padding: 10px 10px;
            margin: 15px 0;
            outline: 0;
            border: 1px solid black;
            width: fit-content;
        }
            

        #chat-history, #chat-history-sortation{
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

        .tab-header {
            display: grid;
            grid-template-columns: auto auto auto;
        }
        .tab-header-button {
            background-color: #56ab0e;
            color: white;
            border: 1px solid #ddd;
            padding: 10px 20px;
            cursor: pointer;
            font-weight: bold;
        }
        .tab-header-button:hover {
            background-color: #ddd;
        }

        .tab-content-item {
            display: none;
        }

        .show {
            display: block;
        }
        </style>

        <center style="width: 100%;">
            <img class="logo" src="smart-recycling-logo.jpg"/>

            <div id="captureContainer" style="display: block;">
                <video id="video" width="98%" height="480" autoplay></video>
                <button id="captureButton" class="nav_button"><i class="fa-solid fa-camera"></i></button>
            </div>

            <div id="captureResultContainer" style="width: 100%; display: none;">
                <canvas id="canvas" width="350" height="470"
                    style="width: 30%; display: inline-block; vertical-align: top;"></canvas>
                <div id="captureResultPrediction" 
                    style="width: 65%; display: inline-block;"></div>
                <br/>
                <button id="reCaptureButton" class="nav_button"
                    style="position: fixed; bottom: 2%; left: 2%; width: 70px; font-size: 2em;"><i class="fa-solid fa-camera"></i></button>
                <br/>

                <div id="low-level-indicator" style="background-color: darkgreen; color: white; display: none; width: fit-content;padding: 5px 8px;margin: 8px;border-radius: 10px;">
                    Low Accuracy Level: You can rescan try rescanning again. This is noted and will be incorporated soon...
                </div>
                
                <h2>Projects Recommendations</h2>
                <div class="container">
                    <div class="tab-container">
                        <div class="tab-header">
                            <button class="tab-header-button" onclick="showTab(0)">PROJECTS<br/>IDEAS</button>
                            <button class="tab-header-button" onclick="showTab(1)">IMAGE<br/>IDEAS</button>
                            <button class="tab-header-button" onclick="showTab(2)">SORTATION<br/>CENTER</button>
                        </div>
                        <div class="tab-content">
                            <div id="tab1" class="tab-content-item">
                                <div id="chat-history"></div>
                            </div>
                            <div id="tab2" class="tab-content-item">
                                <div id="chat-images"></div>
                            </div>
                            <div id="tab3" class="tab-content-item">
                                <div id="chat-history-sortation"></div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </center>

        <!-- Replace this with your image. Make sure CORS settings allow reading the image! -->
        <!-- <img id="img" src="plastic-bottle.webp"></img> -->

        
        <script type="module">
            import {
                getGenerativeModel,
                scrollToDocumentBottom,
                updateUI,
            } from "./utils/shared.js";

            var historyElement = document.querySelector("#chat-history");
            var historySortationElement = document.querySelector("#chat-history-sortation");
            var historyImagesElement = document.querySelector("#chat-images");
            var lowLevelIndicatorElement = document.querySelector("#low-level-indicator");
            let chat;

            async function startChat(messagetext){
                if (!chat) {
                const model = await getGenerativeModel({ model: "gemini-1.5-flash" });
                chat = model.startChat({
                    generationConfig: {
                    maxOutputTokens: 5000,
                    },
                });
                }

                const userMessage = messagetext;
                const historyMessage = historyElement.innerHTML;

                // Create UI for the new user / assistant messages pair
                // // <div class="history-item user-role">
                // // <div class="name"><i class="fa-solid fa-circle-user"></i></div>
                // // <blockquote>${userMessage}</blockquote>
                // // </div>

                historyElement.innerHTML += `<div class="history-item model-role">
                    <div class="name" style="color: greenyellow;"><i class="fa-solid fa-robot"></i></div>
                    <blockquote></blockquote>
                </div>`;

                // historyElement.insertAdjacentHTML("afterbegin", `<div class="history-item model-role">
                //     <div class="name" style="color: greenyellow;"><i class="fa-solid fa-robot"></i></div>
                //     <blockquote></blockquote>
                // </div>`);

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

            async function startChatSortation(messagetext){
                if (!chat) {
                const model = await getGenerativeModel({ model: "gemini-1.5-flash" });
                chat = model.startChat({
                    generationConfig: {
                    maxOutputTokens: 5000,
                    },
                });
                }

                const userMessage = messagetext;
                const historyMessage = historySortationElement.innerHTML;

                historySortationElement.innerHTML += `<div class="history-item model-role">
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
            }


            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const ctx = canvas.getContext('2d');
            const captureButton = document.getElementById('captureButton'); 
            const reCaptureButton = document.getElementById('reCaptureButton'); 

            const captureContainer = document.getElementById('captureContainer');
            const captureResultContainer = document.getElementById('captureResultContainer');
            const captureResultPrediction = document.getElementById('captureResultPrediction');

            let stream;


            function captureRecognize(){
                const img = document.getElementById('canvas');

                captureResultPrediction.innerHTML = "";
                captureButton.innerHTML = "Loading...";

                // Load the model.
                mobilenet.load().then(model => {
                    // Classify the image.
                    model.classify(img).then(predictions => {
                        console.log('Predictions: ');
                        console.log(predictions);

                        predictions.forEach((item, index)=>{
                            if(index < 1) {
                                var nameArr = item.className.split(',');
                                var rawProbability = parseFloat(item.probability) * 100;

                                captureResultPrediction.innerHTML += '<p class="text_result" style="font-size: 1em;">' + nameArr[0] + '</p>'
                                    + '<p class="text_result_light" style="font-size: 1em;">' + rawProbability.toFixed(2) + '%</p>';

                                startChat("Recycling Project Ideas for " + nameArr[0] + " and where to buy " + nameArr[0]);
                                startChatSortation("Philippines sortation center with address for " + nameArr[0] + " recycling");
                                //startChatSortation("Philippines sortation center with address for plastic bottle recycling");

                                if(rawProbability < 30){
                                    lowLevelIndicatorElement.style.display = "block";
                                }else{
                                    lowLevelIndicatorElement.style.display = "none";
                                }

                                searchImages("Recycling Project Ideas for " + nameArr[0])
                                .then(imageLinks => {
                                    console.log(imageLinks);
                                    //historyImagesElement.innerHTML = imageLinks;
                                })
                                .catch(error => {
                                    console.error(error);
                                });
                            }
                        })

                        captureContainer.style.display = "none";
                        captureResultContainer.style.display = "block";
                    });
                });
            }


            async function searchImages(query) {
                // const response = await fetch(`https://alldaily.app/smart-recycling/search_images.php?q=` + query);

                // // Extract image links from the API response
                // const imageLinks = response.data;
                // console.log(response.data)

                // return imageLinks;

                const xhr = new XMLHttpRequest();
                xhr.open('GET', `https://alldaily.app/smart-recycling/search_images.php?q=` + query);
                xhr.send();

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        historyImagesElement.innerHTML = xhr.responseText; 
                        return xhr.responseText;
                    } else {
                        console.error('Request failed.  Returned status of ' + xhr.status);
                    }
                };
            }


            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(s => {
                stream = s;
                video.srcObject = s;
            })
            .catch(err => {
                console.error('Error accessing camera:', err);
            });
            captureButton.addEventListener('click', () => {
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                const capturedImage = canvas.toDataURL('image/png');
                // Do something with the captured image, e.g., save it or send it to a server
                // console.log(capturedImage);

                captureRecognize();
            });
            reCaptureButton.addEventListener('click', () => {
                captureContainer.style.display = "block";
                captureResultContainer.style.display = "none";
                captureButton.innerHTML = '<i class="fa-solid fa-camera"></i>';
            });

            showTab(0);
        </script>
    </body>
</html>