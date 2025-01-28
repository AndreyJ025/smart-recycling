<?php ob_clean(); session_start(); session_destroy(); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://cdn.jsdelivr.net/npm/axios@0.27.2/dist/axios.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/gemini-js@latest/dist/gemini.min.js"></script>
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
            .prose blockquote {
                white-space: pre-line;
                line-height: 1.2;
                padding: 1rem;
            }
            .prose blockquote ul {
                margin-left: 1.5rem;
            }
            .main-point {
                font-size: 1.5rem;
                font-weight: 700;
                margin-top: 0.75rem;
                margin-bottom: 0.5rem;
            }
            .sub-point {
                font-size: 1rem;
                margin-left: 1.5rem;
                color: rgba(255, 255, 255, 0.8);
            }
            .chat-container {
                max-height: calc(100vh - 180px);
                overflow-y: auto;
            }
        </style>
    </head>
    <body class="font-[Poppins]">
        <!-- Navigation -->
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

        <!-- Main Content -->
        <div class="bg-overlay">
            <div class="min-h-screen pt-20 px-4">
                <div class="max-w-4xl mx-auto">
                    <!-- Chat Container -->
                    <div class="bg-white/5 backdrop-blur-md rounded-xl p-6">
                        <div id="chat-history" class="chat-container space-y-4 mb-6"></div>
                        
                        <!-- Chat Input -->
                        <form id="form" class="relative">
                            <input id="prompt" 
                                   class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-green-500 transition-colors"
                                   placeholder="Ask me anything about recycling..."
                            />
                            <button type="submit" class="absolute right-4 top-1/2 -translate-y-1/2 text-2xl text-green-500 hover:text-green-400 transition-colors">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function toggleMenu() {
                const menu = document.getElementById('mobileMenu');
                menu.classList.toggle('hidden');
            }

            function scrollToDocumentBottom() {
                window.scrollTo({
                    top: document.documentElement.scrollHeight,
                    behavior: 'smooth'
                });
            }

            // FAQ System
            let historyElement = document.querySelector("#chat-history");

            const questionsFAQ = [
                'How do I use the image recognition feature?',
                'How does the app detect if something is recyclable?',
                'What other features does this app offer?'
            ];

            function sendQuestion(userMessageIndex) {
                const answersFAQ = [
                    `How to use Image Recognition:
                    • Open the camera feature
                    • Take a photo of the item using your device's camera
                    • Wait while the app processes the image (few seconds)
                    • Review the recognition results
                    • Get recyclability status and recommendations
                    • Follow the suggested disposal instructions`,

                    `How Our Recognition Works:
                    • Takes a photo of your item
                    • Analyzes the item's features:
                        - Shape
                        - Color
                    • Uses AI to identify the item
                    • Determines if item is recyclable
                    • Provides DIY recycling tips:
                        - How to recycle
                        - How to dispose
                        - Where to recycle/dispose/donate`,

                    `Main App Features:
                    • Chatbot Assistant
                    • AI Recognition System
                    • DIY Project Recommendations`
                ];

                if (historyElement) {
                    historyElement.innerHTML += `
                        <div class="bg-white/20 rounded-lg p-4 mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="bg-green-500 p-2 rounded-full">
                                    <i class="fa-solid fa-circle-user text-white"></i>
                                </div>
                                <span class="text-white font-bold">User</span>
                            </div>
                            <div class="prose prose-invert">
                                <blockquote class="text-white/90 leading-relaxed whitespace-pre-line">
                                    ${questionsFAQ[userMessageIndex]}
                                </blockquote>
                            </div>
                        </div>`;

                    historyElement.innerHTML += `
                        <div class="bg-white/20 rounded-lg p-4 mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="bg-green-500 p-2 rounded-full">
                                    <i class="fa-solid fa-robot text-white"></i>
                                </div>
                                <span class="text-white font-bold">AI Assistant</span>
                            </div>
                            <div class="prose prose-invert">
                                <blockquote class="text-white/90 leading-relaxed whitespace-pre-line">
                                    ${answersFAQ[userMessageIndex].replace(/•/g, '\n•').replace(/-/g, '\n -')}
                                </blockquote>
                            </div>
                        </div>`;

                    scrollToDocumentBottom();
                }
            }
        </script>

        <script type="module">
            import {
                getGenerativeModel,
                scrollToDocumentBottom,
                updateUI,
            } from "./utils/shared.js";
        
            let promptInput = document.querySelector("#prompt");
            let historyElement = document.querySelector("#chat-history");
            let chat;
        
            const systemPrompt = {
                parts: [{
                    text: `You are a recycling expert AI assistant. Format all responses as follows:
        
                            GUIDELINES FOR RESPONSE:
                            1. Structure:
                              • Use clear, descriptive titles in CAPS
                              • Organize content in bullet points (•)
                              • Use sub-bullets (>) for details
                              • Add emoji icons where relevant
                              • Include line breaks between sections
                            
                            2. Content Requirements:
                              • Be concise and practical
                              • Focus on actionable steps
                              • Include environmental impact
                              • Add tips and best practices
                            
                            3. Example Format:
                            PLASTIC RECYCLING GUIDE 🌱
                            
                            • Preparation Steps
                              - Remove labels and caps
                              - Rinse thoroughly
                              - Check recycling number
                            
                            • Environmental Benefits
                              - Reduces landfill waste
                              - Saves natural resources
                              - Prevents ocean pollution
                            
                            • Pro Tips ✨
                              - Check local guidelines
                              - Avoid contamination
                              - Store efficiently
                            
                            Always maintain this structure and formatting for consistency.`
                                    }]
            };
        
            document.querySelector("#form").addEventListener("submit", async (event) => {
                event.preventDefault();
        
                try {
                    const userInput = promptInput.value;
                    promptInput.value = "";
                    
                    // Add user message to chat
                    historyElement.innerHTML += `
                        <div class="bg-white/20 rounded-lg p-4 mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="bg-green-500 p-2 rounded-full">
                                    <i class="fa-solid fa-circle-user text-white"></i>
                                </div>
                                <span class="text-white font-bold">User</span>
                            </div>
                            <div class="prose prose-invert">
                                <blockquote class="text-white/90 leading-relaxed whitespace-pre-line">
                                    ${userInput}
                                </blockquote>
                            </div>
                        </div>`;
        
                    // Get AI response
                    const response = await chat.sendMessage(userInput);
                    const responseText = response.response.text();
        
                    // Add AI response to chat
                    historyElement.innerHTML += `
                        <div class="bg-white/20 rounded-lg p-4 mb-4">
                            <div class="flex items-center gap-2 mb-2">
                                <div class="bg-green-500 p-2 rounded-full">
                                    <i class="fa-solid fa-robot text-white"></i>
                                </div>
                                <span class="text-white font-bold">AI Assistant</span>
                            </div>
                            <div class="prose prose-invert">
                                <blockquote class="text-white/90 leading-relaxed whitespace-pre-line">
                                    ${responseText}
                                </blockquote>
                            </div>
                        </div>`;
        
                    scrollToDocumentBottom();
                } catch (error) {
                    console.error("Chat error:", error);
                    // Show error message in chat
                    historyElement.innerHTML += `
                        <div class="bg-red-500/20 text-red-200 p-4 rounded-lg mt-4">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <p class="font-medium">Sorry, there was an error processing your request.</p>
                            </div>
                        </div>`;
                }
            });
        
            async function startChat() {
                try {
                    if (!chat) {
                        const model = await getGenerativeModel({ model: "gemini-1.5-flash" });
                        chat = model.startChat({
                            generationConfig: {
                                maxOutputTokens: 1000,
                                temperature: 0.7,
                            },
                            history: [{
                                role: "user",
                                parts: systemPrompt.parts
                            }]
                        });
                    }
        
                    // Initialize FAQ section
                    historyElement.innerHTML = `
                        <div class="text-center mb-8">
                            <h2 class="text-white text-2xl font-bold mb-4">Frequently Asked Questions</h2>
                        </div>`;
        
                    questionsFAQ.forEach((element, i) => {
                        historyElement.innerHTML += `
                            <div class="faq_item bg-white/20 rounded-lg p-4 mb-4 cursor-pointer hover:bg-white/30 transition-all" 
                                 onclick="sendQuestion(${i});">
                                <div class="flex items-center gap-3">
                                    <i class="fa-solid fa-circle-question text-green-500"></i>
                                    <span class="text-white">${element}</span>
                                </div>
                            </div>`;
                    });
        
                    scrollToDocumentBottom();
                } catch (error) {
                    console.error("Start chat error:", error);
                }
            }
        
            startChat();
        </script>    
    </body>
</html>