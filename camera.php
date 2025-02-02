<?php 
session_start();
header('Access-Control-Allow-Origin: *');
require_once __DIR__ . '/vendor/serpapi/google-search-results-php/google-search-results.php';
require_once  'config/serpapi-config.php';
//$serpapi = new GoogleSearchResults($serpapi_key);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <script src="https://cdn.jsdelivr.net/npm/axios@0.27.2/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@4.17.0"></script>
        <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/mobilenet@2.1.1"></script>
        <script src="https://cdn.jsdelivr.net/npm/gemini-js@latest/dist/gemini.min.js"></script>
        <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            .bg-overlay {
                background: url('background.jpg');
                min-height: 100vh;
                background-size: cover;
                background-position: center;
                background-attachment: fixed;
                position: relative;
            }
            .bg-overlay::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
            }
            .bg-overlay > div {
                position: relative;
                z-index: 1;
            }
            .tab-btn {
                position: relative;
                transition: all 0.3s ease;
            }
            .tab-btn.active {
                background: rgba(255, 255, 255, 0.1);
                transform: translateY(-2px);
            }
            .tab-btn.active::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 2px;
                background: #22c55e;
            }
            .tab-content {
                min-height: 300px;
                backdrop-filter: blur(8px);
            }
            @media (max-width: 640px) {
                .tab-container {
                    flex-direction: column;
                }
                .tab-btn {
                    width: 100%;
                }
            }
        </style>
    </head>
    <body class="font-[Poppins]">
        <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between items-center">
                    <div class="flex-shrink-0 flex items-center gap-3">
                        <img src="logo.png" alt="Smart Recycling Logo" class="h-10">
                        <h1 class="text-2xl font-bold">
                            <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                        </h1>
                    </div>
                    
                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center space-x-4">
                        <a href="home.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Home</a>
                        <a href="camera.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Camera</a>
                        <a href="chatbot.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Chatbot</a>
                        <a href="index.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">
                            <i class="fa-solid fa-right-from-bracket"></i> Logout
                        </a>
                    </div>
    
                    <!-- Mobile Menu Button -->
                    <div class="md:hidden">
                        <button onclick="toggleMenu()" class="text-white p-2">
                            <i class="fa-solid fa-bars text-2xl"></i>
                        </button>
                    </div>
                </div>
    
                <!-- Mobile Menu Panel -->
                <div id="mobileMenu" class="hidden md:hidden mt-2">
                    <div class="flex flex-col space-y-2">
                        <a href="home.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Home</a>
                        <a href="camera.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Camera</a>
                        <a href="chatbot.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Chatbot</a>
                        <a href="index.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">
                            <i class="fa-solid fa-right-from-bracket"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    
        <div class="bg-overlay">
            <div class="min-h-screen flex items-center justify-center px-4 pt-20">
                <div class="w-full max-w-4xl">
                    <!-- Replace Camera Interface section -->
                    <div id="captureContainer" class="w-full max-w-[500px] mx-auto space-y-4">
                        <video id="video" class="w-full h-auto rounded-2xl shadow-lg" playsinline autoplay></video>
                        <canvas id="canvas" class="hidden"></canvas>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <button id="captureButton" class="w-full bg-white text-black font-bold rounded-full py-4 hover:bg-opacity-90 transition-all">
                                <div class="flex flex-col items-center">
                                    <i class="fa-solid fa-camera text-[clamp(1.2rem,4vw,2rem)]"></i>
                                    <span class="text-sm mt-1">Capture</span>
                                </div>
                            </button>
                            
                            <label class="w-full cursor-pointer">
                                <input type="file" id="imageUpload" accept="image/*" class="hidden">
                                <div class="w-full bg-white text-black font-bold rounded-full py-4 hover:bg-opacity-90 transition-all text-center">
                                    <div class="flex flex-col items-center">
                                        <i class="fa-solid fa-upload text-[clamp(1.2rem,4vw,2rem)]"></i>
                                        <span class="text-sm mt-1">Upload</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
    
                    <!-- Results Interface -->
                    <div id="captureResultContainer" class="w-full max-w-[1000px] mx-auto hidden">
                        <div class="grid md:grid-cols-[350px,1fr] gap-8">
                            <!-- Left Column - Captured Image -->
                            <div class="space-y-4">
                                <div class="bg-white/5 rounded-xl p-4">
                                    <div class="max-w-[250px] mx-auto"> <!-- Added max-width and center alignment -->
                                        <div class="aspect-[3/4] rounded-lg overflow-hidden">
                                            <img id="capturedImage" class="w-full h-full object-cover" />
                                        </div>
                                    </div>
                                </div>
                                <button id="reCaptureButton" class="w-full bg-white text-black font-bold text-lg rounded-full py-4 hover:bg-opacity-90 transition-all">
                                    <i class="fa-solid fa-camera-rotate mr-2"></i> Take Another Photo
                                </button>
                            </div>
                    
                            <!-- Right Column - Results -->
                            <div class="space-y-6">
                                <!-- Result Preview -->
                                <div id="captureResultPrediction" class="bg-white/5 rounded-xl p-4"></div>
                                
                                <!-- Tabs Container -->
                                <div class="tab-container grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                    <button onclick="showTab(0)" class="tab-btn flex items-center justify-center space-x-3 bg-white/5 p-4 rounded-xl hover:bg-white/10 transition-all">
                                        <div class="bg-green-500 p-2 rounded-full">
                                            <i class="fa-solid fa-lightbulb text-white text-xl"></i>
                                        </div>
                                        <span class="text-white font-medium">Project Ideas</span>
                                    </button>
                                    <button onclick="showTab(1)" class="tab-btn flex items-center justify-center space-x-3 bg-white/5 p-4 rounded-xl hover:bg-white/10 transition-all">
                                        <div class="bg-green-500 p-2 rounded-full">
                                            <i class="fa-solid fa-images text-white text-xl"></i>
                                        </div>
                                        <span class="text-white font-medium">Image Ideas</span>
                                    </button>
                                    <button onclick="showTab(2)" class="tab-btn flex items-center justify-center space-x-3 bg-white/5 p-4 rounded-xl hover:bg-white/10 transition-all">
                                        <div class="bg-green-500 p-2 rounded-full">
                                            <i class="fa-solid fa-location-dot text-white text-xl"></i>
                                        </div>
                                        <span class="text-white font-medium">Sortation Centers</span>
                                    </button>
                                </div>
                    
                                <!-- Tab Contents -->
                                <div class="tab-content bg-white/5 rounded-xl p-6 max-h-[500px] overflow-y-auto">
                                    <div id="tab1" class="tab-content-item">
                                        <div id="chat-history" class="space-y-4"></div>
                                    </div>
                                    <div id="tab2" class="tab-content-item hidden">
                                        <div id="chat-images" class="space-y-4"></div>
                                    </div>
                                    <div id="tab3" class="tab-content-item hidden">
                                        <div id="chat-history-sortation" class="space-y-4"></div>
                                    </div>
                                </div>
                    
                                <!-- Low Level Indicator -->
                                <div id="low-level-indicator" class="hidden bg-yellow-500/20 text-yellow-200 p-4 rounded-lg">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        <p class="font-medium">Low confidence detection. Results may not be accurate.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function toggleMenu() {
                const menu = document.getElementById('mobileMenu');
                menu.classList.toggle('hidden');
            }
        
            function showTab(n) {
                const tabs = document.getElementsByClassName("tab-content-item");
                const buttons = document.querySelectorAll('.tab-btn');
                
                // Hide all tabs and remove active states
                for (let i = 0; i < tabs.length; i++) {
                    tabs[i].classList.add('hidden');
                    buttons[i].classList.remove('active');
                }
                
                // Show selected tab and add active state
                document.getElementById("tab" + (n + 1)).classList.remove('hidden');
                buttons[n].classList.add('active');
            }
            
            // Initialize first tab as active
            document.addEventListener('DOMContentLoaded', () => {
                showTab(0);
            });
        </script>

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
            
            function captureRecognize(){
                // Get the canvas image data
                const dataUrl = canvas.toDataURL('image/jpeg');
                
                // Set the captured image
                document.getElementById('capturedImage').src = dataUrl;
                
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
                                For each project, include:
                                🎯 Project Name
                                📋 Materials Needed
                                ⚡ Difficulty Level (Easy/Medium/Hard)
                                📝 Step-by-Step Instructions (numbered)
                                ♻️ Environmental Impact
                                💡 Pro Tips
                                
                                Format the response with clear headings and spacing for better readability.`;
                                const sortationPrompt = `List recycling centers in the Philippines that accept ${itemName}...`;
                                const imagePrompt = `Find DIY recycling project images using ${itemName}`;
                    
                                startChat(projectPrompt);
                                startChatSortation(sortationPrompt); 
                                search(imagePrompt);
                    
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

            async function search(query) { // Search images function using fetch | search_images.php | search_images_v2.php | search_images_v3.php
                try {
                    const response = await fetch(`search_images_v3.php?q=${encodeURIComponent(query)}`);
                    if (!response.ok) throw new Error('Network response was not ok');
                    const html = await response.text();
                    historyImagesElement.innerHTML = html;
                } catch (error) {
                    console.error('Error fetching images:', error);
                    historyImagesElement.innerHTML = '<div class="text-center text-red-500 p-4">Failed to load images</div>';
                }
            }

            // Search images function using search_images.php from server
            async function searchImages(query) {
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
                
                // Existing camera button listeners
                captureButton.addEventListener('click', () => {
                    if (stream) {
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                        captureRecognize();
                    }
                });
                
                reCaptureButton.addEventListener('click', () => {
                    captureContainer.style.display = "block";
                    captureResultContainer.style.display = "none";
                    captureButton.innerHTML = '<i class="fa-solid fa-camera"></i>';
                    initCamera();
                });
            
                // Add image upload handler
                const imageUpload = document.getElementById('imageUpload');
                imageUpload.addEventListener('change', (e) => {
                    if (e.target.files && e.target.files[0]) {
                        const reader = new FileReader();
                        
                        reader.onload = (e) => {
                            const img = new Image();
                            img.onload = () => {
                                canvas.width = img.width;
                                canvas.height = img.height;
                                ctx.drawImage(img, 0, 0);
                                captureRecognize();
                            }
                            img.src = e.target.result;
                        }
                        
                        reader.readAsDataURL(e.target.files[0]);
                    }
                });
            });
            
            showTab(0);
        </script>
    </body>
</html>