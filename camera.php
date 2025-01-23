<?php
session_start();
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/vendor/serpapi/google-search-results-php/google-search-results.php';

// Load config
$config = require __DIR__ . '/config/serpapi-config.php';

// Initialize search client
$serpapi = new GoogleSearchResults($config['api_key']);

?>

<html>
    <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        <script src="https://cdn.jsdelivr.net/npm/axios@0.27.2/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.17.0"></script>
        <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet@2.1.1"></script>
        <script src="https://cdn.jsdelivr.net/npm/gemini-js@latest/dist/gemini.min.js"></script>
        <script src="https://cdn.tailwindcss.com"></script>
        <meta charset="utf-8" />
        <link rel="shortcut icon" type="image/svg+xml" href="favicon.svg" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body class="bg-[#7ed957] max-w-[720px] mx-auto px-4 pb-24 lg:max-w-[900px]">
        <script>
            function showTab(n) {
                var tabs = document.getElementsByClassName("tab-content-item");
                for (var i = 0; i < tabs.length; i++) {
                    tabs[i].classList.add('hidden');
                }
                document.getElementById("tab" + (n + 1)).classList.remove('hidden');
                
                // Update active tab button styling
                var buttons = document.querySelectorAll('.tab-container button');
                buttons.forEach((btn, index) => {
                    if (index === n) {
                        btn.classList.add('bg-green-600');
                    } else {
                        btn.classList.remove('bg-green-600');
                    }
                });
            }
        </script>

        <center class="w-full">
            <img class="w-[40%] max-w-[300px] mt-[clamp(40px,8vh,80px)] mb-5 md:w-[60%] md:mt-10" src="smart-recycling-logo.jpg"/>

            <div id="captureContainer" class="block">
                <video id="video" class="w-[98%] h-[480px]" playsinline autoplay></video>
                <button id="captureButton" class="w-[80%] max-w-[400px] bg-white text-black font-bold text-[clamp(0.8rem,2.5vw,1.5rem)] rounded-full py-2 hover:bg-gray-100 hover:scale-[1.02] transition-all duration-200 my-4">
                    <i class="fa-solid fa-camera"></i>
                </button>
            </div>

            <div id="captureResultContainer" class="w-full hidden">
                <canvas id="canvas" width="350" height="470" class="w-[30%] inline-block align-top"></canvas>
                <div id="captureResultPrediction" class="w-[65%] inline-block"></div>
                <br/>
                <button id="reCaptureButton" class="fixed bottom-[2%] left-[2%] w-[70px] text-[2em] bg-white text-black font-bold rounded-full py-2 hover:bg-gray-100 hover:scale-[1.02] transition-all duration-200">
                    <i class="fa-solid fa-camera"></i>
                </button>
                <br/>

                <div id="low-level-indicator" class="hidden bg-green-800 text-white p-2 rounded-lg mx-2 my-2 inline-block">
                    Low Accuracy Level: You can rescan try rescanning again. This is noted and will be incorporated soon...
                </div>
                
                <h2 class="text-white text-[clamp(1.2rem,3vw,2rem)] font-bold my-4">Projects Recommendations</h2>
                
                <div class="container mx-auto">
                    <div class="tab-container">
                        <!-- Tab Navigation -->
                        <div class="flex mb-4">
                            <button onclick="showTab(0)" class="flex-1 bg-[#56ab0e] text-white border border-white px-4 py-3 text-sm font-bold hover:bg-green-600 transition-all duration-200">
                                PROJECTS<br/>IDEAS
                            </button>
                            <button onclick="showTab(1)" class="flex-1 bg-[#56ab0e] text-white border border-white px-4 py-3 text-sm font-bold hover:bg-green-600 transition-all duration-200">
                                IMAGE<br/>IDEAS
                            </button>
                            <button onclick="showTab(2)" class="flex-1 bg-[#56ab0e] text-white border border-white px-4 py-3 text-sm font-bold hover:bg-green-600 transition-all duration-200">
                                SORTATION<br/>CENTER
                            </button>
                        </div>

                        <!-- Tab Content -->
                        <div class="bg-white/10 rounded-lg p-4">
                            <div id="tab1" class="tab-content-item">
                                <div id="chat-history" class="text-white text-left pb-[200px]"></div>
                            </div>
                            <div id="tab2" class="tab-content-item hidden">
                                <div id="chat-images" class="grid grid-cols-2 gap-4"></div>
                            </div>
                            <div id="tab3" class="tab-content-item hidden">
                                <div id="chat-history-sortation" class="text-white text-left pb-[200px]"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </center>

        <!-- Bottom Navigation Menu -->
        <div class="fixed bottom-0 left-0 right-0 bg-white py-4 shadow-md z-50 lg:left-1/2 lg:transform lg:-translate-x-1/2 lg:w-[720px] lg:rounded-t-2xl">
            <div class="flex justify-around max-w-[720px] mx-auto lg:px-5">
                <a href="home.php" class="flex flex-col items-center">
                    <div class="text-[clamp(1.5rem,4vw,2rem)] text-[#7ed957] p-3 rounded-full hover:bg-[#7ed957] hover:text-white hover:-translate-y-1 transition-all duration-200">
                        <i class="fa-solid fa-house"></i>
                    </div>
                    <span class="text-xs text-[#7ed957] mt-1">Home</span>
                </a>
                <a href="camera.php" class="flex flex-col items-center">
                    <div class="text-[clamp(1.5rem,4vw,2rem)] text-[#7ed957] p-3 rounded-full hover:bg-[#7ed957] hover:text-white hover:-translate-y-1 transition-all duration-200">
                        <i class="fa-solid fa-camera-retro"></i>
                    </div>
                    <span class="text-xs text-[#7ed957] mt-1">Camera</span>
                </a>
                <a href="chatbot.php" class="flex flex-col items-center">
                    <div class="text-[clamp(1.5rem,4vw,2rem)] text-[#7ed957] p-3 rounded-full hover:bg-[#7ed957] hover:text-white hover:-translate-y-1 transition-all duration-200">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                    <span class="text-xs text-[#7ed957] mt-1">Chatbot</span>
                </a>
                <a href="index.php" class="flex flex-col items-center">
                    <div class="text-[clamp(1.5rem,4vw,2rem)] text-[#7ed957] p-3 rounded-full hover:bg-[#7ed957] hover:text-white hover:-translate-y-1 transition-all duration-200">
                        <i class="fa-solid fa-right-from-bracket"></i>
                    </div>
                    <span class="text-xs text-[#7ed957] mt-1">Logout</span>
                </a>
            </div>
        </div>

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
                
                historyElement.innerHTML += `
                    <div class="bg-white/20 rounded-lg p-4 mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="bg-green-500 p-2 rounded-full">
                                <i class="fa-solid fa-robot text-white"></i>
                            </div>
                            <span class="text-white font-bold">AI Assistant</span>
                        </div>
                        <div class="prose prose-invert">
                            <blockquote class="text-white/90 leading-relaxed"></blockquote>
                        </div>
                    </div>
                `;
            
                scrollToDocumentBottom();
                const resultEls = document.querySelectorAll(".prose > blockquote");
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
            
                historySortationElement.innerHTML += `
                    <div class="bg-white/20 rounded-lg p-4 mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="bg-green-500 p-2 rounded-full">
                                <i class="fa-solid fa-robot text-white"></i>
                            </div>
                            <span class="text-white font-bold">AI Assistant</span>
                        </div>
                        <div class="prose prose-invert">
                            <blockquote class="text-white/90 leading-relaxed"></blockquote>
                        </div>
                    </div>
                `;
            
                scrollToDocumentBottom();
                const resultEls = document.querySelectorAll(".prose > blockquote");
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


            // Find the captureRecognize() function and modify it:
            
            function captureRecognize(){
                // Clear previous results first
                captureResultPrediction.innerHTML = "";
                historyElement.innerHTML = "";
                historySortationElement.innerHTML = "";
                historyImagesElement.innerHTML = "";
                captureButton.innerHTML = "Loading...";
                
                // Reset chat instance
                chat = null;
            
                const img = document.getElementById('canvas');
                
                mobilenet.load().then(model => {
                    model.classify(img).then(predictions => {
                        console.log('Predictions: ', predictions);
            
                        predictions.forEach((item, index)=>{
                            if(index < 1) {
                                var nameArr = item.className.split(',');
                                var rawProbability = parseFloat(item.probability) * 100;
                                var itemName = nameArr[0];
            
                                captureResultPrediction.innerHTML += `
                                    <div class="bg-white/20 p-4 rounded-lg mb-4">
                                        <div class="flex items-center gap-3">
                                            <div class="bg-green-500 p-3 rounded-full">
                                                <i class="fa-solid fa-recycle text-white text-xl"></i>
                                            </div>
                                            <div>
                                                <p class="text-white font-bold text-lg">${itemName}</p>
                                                <div class="flex items-center gap-2">
                                                    <div class="w-24 h-2 bg-white/20 rounded-full overflow-hidden">
                                                        <div class="h-full bg-green-500 rounded-full" style="width: ${rawProbability}%"></div>
                                                    </div>
                                                    <p class="text-white/80 text-sm">${rawProbability.toFixed(2)}%</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
            
                                // Customize prompts based on item detected
                                const projectPrompt = `As a recycling expert, provide 3 creative DIY project ideas using ${itemName}.
                                    Format your response exactly like this without any other text:

                                    Project 1: [project name]
                                    Materials:
                                    - [list materials]
                                    Steps:
                                    1. [step details]
                                    Estimated Time: [time]
                                    Difficulty: [easy/medium/hard]

                                    Project 2: [project name]
                                    [same format]

                                    Project 3: [project name]
                                    [same format]

                                    Where to buy ${itemName}:
                                    - [locations]
                                    Note: Do not include any introduction of what you are like "I am a recycling expert" or "I am a bot".`;
            
                                const sortationPrompt = `List recycling centers in the Philippines that accept ${itemName}.
                                    Include for each center:
                                    - Complete address
                                    - Contact information
                                    - Operating hours
                                    - Types of ${itemName} they accept
                                    - Any special requirements for dropping off`;
            
                                const imagePrompt = `Find me DIY recycling project images using ${itemName}`;
            
                                startChat(projectPrompt);
                                startChatSortation(sortationPrompt); 
                                searchImages(imagePrompt);
            
                                if(rawProbability < 30){
                                    lowLevelIndicatorElement.style.display = "block";
                                } else {
                                    lowLevelIndicatorElement.style.display = "none";
                                }
                            }
                        });
            
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

            // async function searchImages(query) {
            //     try {
            //         // Prepare search parameters
            //         $params = [
            //             "q" => $query,
            //             "tbm" => "isch", // Image search
            //             "num" => "8"     // Number of results
            //         ];

            //         // Execute search
            //         $results = $serpapi->get_json($params);
                    
            //         // Display results
            //         if (isset($results->images_results)) {
            //             historyImagesElement.innerHTML = formatImageResults($results->images_results);
            //         }
            //     } catch(Exception $e) {
            //         console.error('Search error:', $e->getMessage());
            //     }
            // }

            // function formatImageResults($images) {
            //     return `
            //         <div class="grid grid-cols-2 gap-4">
            //             ${images.map(img => `
            //                 <img src="${img.thumbnail}" 
            //                     alt="${img.title}"
            //                     class="w-full rounded-lg shadow-lg hover:scale-105 transition-transform" />
            //             `).join('')}
            //         </div>
            //     `;
            // }

            async function initCamera() {
                try {
                    const constraints = { 
                        video: { 
                            facingMode: 'environment',
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        } 
                    };
                    
                    stream = await navigator.mediaDevices.getUserMedia(constraints);
                    video.srcObject = stream;
                    
                    // Wait for video to be ready
                    await new Promise((resolve) => {
                        video.onloadedmetadata = () => {
                            resolve();
                        };
                    });
                    
                    video.play();
                    captureButton.disabled = false;
                    
                } catch (err) {
                    console.error('Camera error:', err);
                    alert('Could not access camera. Please ensure camera permissions are granted.');
                }
            }
            
            // Update event listeners
            document.addEventListener('DOMContentLoaded', () => {
                initCamera();
                
                captureButton.addEventListener('click', () => {
                    if (stream) {
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                        captureRecognize();
                    }
                });
                
                reCaptureButton.addEventListener('click', () => {
                    captureContainer.style.display = "block";
                    captureResultContainer.style.display = "none";
                    captureButton.innerHTML = '<i class="fa-solid fa-camera"></i>';
                    initCamera(); // Reinitialize camera
                });
            });

            showTab(0);
        </script>
    </body>
</html>