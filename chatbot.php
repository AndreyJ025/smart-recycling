<?php ob_clean(); session_start(); session_destroy(); ?>

<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" 
          integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" 
          crossorigin="anonymous" 
          referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios@0.27.2/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gemini-js@latest/dist/gemini.min.js"></script>

    <style>
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
    </style>

  </head>
    <body class="bg-[#7ed957] max-w-[720px] mx-auto px-4 pb-24 lg:max-w-[900px]">
      <div class="flex flex-col items-center w-full">
        <img class="w-[40%] max-w-[300px] mt-[clamp(40px,8vh,80px)] mb-5" 
             src="smart-recycling-logo.jpg"/>
  
        <div class="container w-full">
          <div id="chat-history" class="text-white text-left pb-[200px] mx-auto max-w-[600px]"></div>
        </div>
  
        <div class="fixed bottom-[120px] left-0 right-0 z-40">
          <form id="form" class="flex justify-between items-center max-w-[400px] mx-auto px-4 py-3 bg-white rounded-full shadow-lg hover:shadow-xl transition-all duration-200 sm:max-w-[450px] md:max-w-[500px] lg:max-w-[550px]">
            <input id="prompt" 
                   class="w-[80%] px-3 py-2 text-sm rounded-full border-0 focus:outline-none sm:text-base md:text-lg" 
                   placeholder="Enter Question here..."/>
            <button type="submit" class="text-xl text-[#7ed957] hover:text-gray-400 transition-colors sm:text-2xl md:text-3xl">
              <i class="fa-solid fa-paper-plane"></i>
            </button>
          </form>
        </div>
  
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
    
      <script>

        function scrollToDocumentBottom() {
            window.scrollTo({
              top: document.documentElement.scrollHeight,
              behavior: 'smooth'
            });
          }

        // Move FAQ handling to global scope
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

            // Add AI response
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
        // 1. Import utilities
        import {
          getGenerativeModel,
          scrollToDocumentBottom,
          updateUI,
        } from "./utils/shared.js";
      
        // 2. Global variables
        let promptInput = document.querySelector("#prompt");
        let historyElement = document.querySelector("#chat-history");
        let chat;
      
        // 3. System prompt
        const systemPrompt = {
          parts: [{
            text: `Format responses as follows:

            1. Start with a clear title/overview
            2. Use # for main points
            3. Use >> for sub-points
            4. Add line breaks between sections
            5. Example format:

            OVERVIEW
            # Main point 1
            >> Sub detail
            # Main point 2
            >> Sub detail A
            >> Sub detail B
            
            
            Note: Do not bold or italicize text.`
          }]
        };
      
        // 4. Chat initialization
        document.querySelector("#form").addEventListener("submit", async (event) => {
          event.preventDefault();

          try {
            const userInput = promptInput.value;
            
            // Format message correctly for Gemini API
            const userMessage = {
              parts: [{
                text: userInput
              }]
            };
            
            promptInput.value = "";

            // Add response text processing
            const processResponse = (text) => {
              return text
                .replace(/^#\s(.+)$/gm, '<div class="main-point">• $1</div>') // Main points
                .replace(/^>>\s(.+)$/gm, '<div class="sub-point">- $1</div>') // Sub points
                .replace(/\n{3,}/g, '\n\n'); // Clean spacing
            };

            // Update UI with user message first
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
            const response = await chat.sendMessage(userMessage.parts[0].text);
            const responseText = response.response.text();

            // Update UI with AI response
            historyElement.innerHTML += `
              <div class="bg-white/20 rounded-lg p-4 mb-4">
                <div class="flex items-center gap-2 mb-2">
                  <div class="bg-green-500 p-2 rounded-full">
                    <i class="fa-solid fa-robot text-white"></i>
                  </div>
                  <span class="text-white font-bold">AI Assistant</span>
                </div>
                <div class="prose prose-invert">
                  <blockquote class="text-white/90 leading-relaxed">
                    ${processResponse(responseText)}
                  </blockquote>
                </div>
              </div>`;

            scrollToDocumentBottom();
          } catch (error) {
            console.error("Chat error:", error);
          }
        });
      
        // 5. Initial chat setup
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
      
            // Display FAQ section
            historyElement.innerHTML = `<center><h2 class="text-white mb-4">FAQ from Users</h2></center>`;
      
            questionsFAQ.forEach((element, i) => {
              historyElement.innerHTML += `
                <center>
                  <div class="faq_item bg-white/20 rounded-lg p-3 mb-2 cursor-pointer hover:bg-white/30" 
                       onclick="sendQuestion(${i});">
                    ${element}
                  </div>
                </center>`;
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