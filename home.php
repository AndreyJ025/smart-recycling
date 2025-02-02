<?php 
ob_clean(); 
session_start(); 
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
      html {
        scroll-behavior: smooth;
      }
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
      .dropdown-menu {
        transform-origin: top;
        transition: all 0.2s ease;
      }
      .dropdown-menu {
          transform-origin: top;
          transform: scale(0.95);
          opacity: 0;
          transition: all 0.1s ease-in-out;
      }

      .dropdown-menu:not(.hidden) {
          transform: scale(1);
          opacity: 1;
      }

        /* Hide scrollbar for Chrome, Safari and Opera */
      ::-webkit-scrollbar {
        display: none;
      }

      /* Hide scrollbar for IE, Edge and Firefox */
      body {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
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
              <!-- Home Dropdown -->
              <div class="relative group">
                  <button class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all flex items-center">
                      Home
                      <i class="fa-solid fa-chevron-down ml-2 text-sm"></i>
                  </button>
                  <div class="hidden group-hover:block absolute left-0 mt-2 w-56 rounded-xl bg-[#1b1b1b] shadow-lg border border-white/10">
                      <div class="py-2">
                          <a href="#mission" class="block px-4 py-2 text-white hover:bg-white hover:text-black transition-all">
                              <i class="fa-solid fa-bullseye mr-2"></i> Our Mission
                          </a>
                          <a href="#quick-actions" class="block px-4 py-2 text-white hover:bg-white hover:text-black transition-all">
                              <i class="fa-solid fa-bolt mr-2"></i> Quick Actions
                          </a>
                          <a href="#testimonials" class="block px-4 py-2 text-white hover:bg-white hover:text-black transition-all">
                              <i class="fa-solid fa-quote-left mr-2"></i> Testimonials
                          </a>
                          <a href="#about" class="block px-4 py-2 text-white hover:bg-white hover:text-black transition-all">
                              <i class="fa-solid fa-info-circle mr-2"></i> About Us
                          </a>
                          <a href="#contact" class="block px-4 py-2 text-white hover:bg-white hover:text-black transition-all">
                              <i class="fa-solid fa-envelope mr-2"></i> Contact
                          </a>
                      </div>
                  </div>
              </div>
              
              <a href="camera.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Camera</a>
              <a href="chatbot.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Chatbot</a>
            
            <!-- Profile Dropdown -->
            <div class="relative">
            <button onclick="toggleDropdown()" 
                    class="dropdown-toggle flex items-center text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">
                <i class="fa-solid fa-user mr-2"></i>
                <?php echo $_SESSION["user_fullname"] ?? "Profile" ?>
                <i class="fa-solid fa-chevron-down ml-2 text-sm"></i>
            </button>
              
              <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-48 rounded-xl bg-[#1b1b1b] shadow-lg border border-white/10 dropdown-menu">
                  <div class="py-2">
                      <a href="profile.php" class="block px-4 py-2 text-white hover:bg-white hover:text-black transition-all">
                          <i class="fa-solid fa-user-circle mr-2"></i> View Profile
                      </a>
                      <?php if(isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == 1): ?>
                      <a href="admin-dashboard.php" class="block px-4 py-2 text-white hover:bg-white hover:text-black transition-all">
                          <i class="fa-solid fa-gauge-high mr-2"></i> Admin Dashboard
                      </a>
                      <?php endif; ?>
                      <hr class="my-2 border-white/10">
                      <a href="index.php" class="block px-4 py-2 text-white hover:bg-white hover:text-black transition-all">
                          <i class="fa-solid fa-right-from-bracket mr-2"></i> Logout
                      </a>
                  </div>
              </div>
            </div>
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
              <!-- Home Section -->
              <div class="space-y-2">
                  <a href="home.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Home</a>
                  <div class="pl-6 space-y-2">
                      <a href="#mission" class="text-white/80 hover:bg-white hover:text-black px-3 py-2 rounded-md text-base transition-all flex items-center">
                          <i class="fa-solid fa-bullseye mr-2"></i> Our Mission
                      </a>
                      <a href="#quick-actions" class="text-white/80 hover:bg-white hover:text-black px-3 py-2 rounded-md text-base transition-all flex items-center">
                          <i class="fa-solid fa-bolt mr-2"></i> Quick Actions
                      </a>
                      <a href="#testimonials" class="text-white/80 hover:bg-white hover:text-black px-3 py-2 rounded-md text-base transition-all flex items-center">
                          <i class="fa-solid fa-quote-left mr-2"></i> Testimonials
                      </a>
                      <a href="#about" class="text-white/80 hover:bg-white hover:text-black px-3 py-2 rounded-md text-base transition-all flex items-center">
                          <i class="fa-solid fa-info-circle mr-2"></i> About Us
                      </a>
                      <a href="#contact" class="text-white/80 hover:bg-white hover:text-black px-3 py-2 rounded-md text-base transition-all flex items-center">
                          <i class="fa-solid fa-envelope mr-2"></i> Contact
                      </a>
                  </div>
              </div>
            <a href="camera.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Camera</a>
            <a href="chatbot.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">Chatbot</a>
            <!-- Profile Section with submenu -->
            <div class="space-y-2">
                <div class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">
                    <i class="fa-solid fa-user mr-2"></i> <?php echo $_SESSION["user_fullname"] ?? "Profile" ?>
                </div>
                <div class="pl-6 space-y-2">
                    <a href="profile.php" class="text-white/80 hover:bg-white hover:text-black px-3 py-2 rounded-md text-base transition-all flex items-center">
                        <i class="fa-solid fa-user-circle mr-2"></i> View Profile
                    </a>
                    <?php if(isset($_SESSION["is_admin"]) && $_SESSION["is_admin"] == 1): ?>
                    <a href="admin-dashboard.php" class="text-white/80 hover:bg-white hover:text-black px-3 py-2 rounded-md text-base transition-all flex items-center">
                        <i class="fa-solid fa-gauge-high mr-2"></i> Admin Dashboard
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <hr class="border-white/10 my-2">
            
            <a href="index.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">
                <i class="fa-solid fa-right-from-bracket mr-2"></i> Logout
            </a>
          </div>
        </div>
      </div>
    </nav>

    <div class="bg-overlay">
      <div class="min-h-screen">
        <!-- Hero Section -->
        <div class="pt-32 pb-20 px-4">
          <div class="max-w-7xl mx-auto text-center">
            <h1 class="text-4xl md:text-7xl font-bold text-white mb-6">
              Turn Waste Into <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Solutions</span>
            </h1>
            <p class="text-xl text-white/80 mb-12 max-w-2xl mx-auto">
              Join us in building a sustainable future through smart recycling solutions and innovative waste management.
            </p>
            <a href="camera.php" 
                class="inline-flex items-center justify-center px-8 py-4 text-xl font-semibold text-black bg-white rounded-full hover:bg-opacity-90 transition-all">
              Start Recycling Now
              <i class="fa-solid fa-arrow-right ml-2"></i>
            </a>
          </div>
        </div>

        <!-- Mission Section -->
        <div id="mission" class="py-20 bg-black/30">
          <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-2 gap-12 items-center">
              <div>
                <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">Our Mission</h2>
                <p class="text-white/80 text-lg mb-6">
                  EcoLens is revolutionizing recycling through AI-powered technology. We make it easy for everyone to contribute to a cleaner planet.
                </p>
                <div class="grid grid-cols-2 gap-6">
                  <div class="bg-white/5 p-6 rounded-xl">
                    <i class="fa-solid fa-leaf text-[#436d2e] text-3xl mb-4"></i>
                    <h3 class="text-white font-semibold text-xl mb-2">Eco-Friendly</h3>
                    <p class="text-white/70">Promoting sustainable practices for a better future</p>
                  </div>
                  <div class="bg-white/5 p-6 rounded-xl">
                    <i class="fa-solid fa-robot text-[#436d2e] text-3xl mb-4"></i>
                    <h3 class="text-white font-semibold text-xl mb-2">AI-Powered</h3>
                    <p class="text-white/70">Smart recognition to assist with recycling</p>
                  </div>
                </div>
              </div>
              <div class="relative">
                  <img src="about-image.jpg" alt="Recycling Process" class="rounded-xl shadow-lg">
                  <div class="absolute -bottom-6 right-8 bg-[#436d2e] p-6 rounded-xl shadow-lg">
                      <div class="text-white text-center">
                          <div class="text-4xl font-bold mb-2">1000+</div>
                          <div class="text-sm">Items Recycled</div>
                      </div>
                  </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Quick Actions Section -->
        <div id="quick-actions" class="py-20 bg-black/30">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-3xl md:text-5xl font-bold text-white text-center mb-12">Quick Actions</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <a href="add-remit.php" class="group bg-white/5 backdrop-blur-sm p-8 rounded-xl hover:bg-[#436d2e] transition-all">
                        <div class="bg-[#436d2e] group-hover:bg-white w-16 h-16 rounded-full flex items-center justify-center mb-6">
                            <i class="fa-solid fa-recycle text-white group-hover:text-[#436d2e] text-2xl"></i>
                        </div>
                        <h3 class="text-white text-2xl font-semibold mb-2">Recycle or Donate</h3>
                        <p class="text-white/70 group-hover:text-white/90">Start your eco-journey by recycling or donating items</p>
                    </a>
                    <a href="view-sortation.php" class="group bg-white/5 backdrop-blur-sm p-8 rounded-xl hover:bg-[#436d2e] transition-all">
                        <div class="bg-[#436d2e] group-hover:bg-white w-16 h-16 rounded-full flex items-center justify-center mb-6">
                            <i class="fa-solid fa-map-location-dot text-white group-hover:text-[#436d2e] text-2xl"></i>
                        </div>
                        <h3 class="text-white text-2xl font-semibold mb-2">Find Centers</h3>
                        <p class="text-white/70 group-hover:text-white/90">Locate recycling centers near you</p>
                    </a>
                    <a href="view-user-remit.php" class="group bg-white/5 backdrop-blur-sm p-8 rounded-xl hover:bg-[#436d2e] transition-all">
                        <div class="bg-[#436d2e] group-hover:bg-white w-16 h-16 rounded-full flex items-center justify-center mb-6">
                            <i class="fa-solid fa-clock-rotate-left text-white group-hover:text-[#436d2e] text-2xl"></i>
                        </div>
                        <h3 class="text-white text-2xl font-semibold mb-2">Your Records</h3>
                        <p class="text-white/70 group-hover:text-white/90">Track your recycling history</p>
                    </a>
                </div>
            </div>
        </div>

        <!-- Testimonials -->
        <div id="testimonials" class="py-20 bg-black/20">
          <div class="max-w-7xl mx-auto px-4">
            <h2 class="text-3xl md:text-5xl font-bold text-white text-center mb-12">What People Say</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <!-- First testimonial (existing) -->
                <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                    <div class="text-[#436d2e] text-2xl mb-4">★★★★★</div>
                    <p class="text-white/80 mb-6">"EcoLens made recycling so much easier. The AI recognition is incredibly accurate!"</p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-[#436d2e] rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-user text-white"></i>
                        </div>
                        <div>
                            <div class="text-white font-semibold">John Doe</div>
                            <div class="text-white/60 text-sm">Regular User</div>
                        </div>
                    </div>
                </div>
            
                <!-- Second testimonial -->
                <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                    <div class="text-[#436d2e] text-2xl mb-4">★★★★★</div>
                    <p class="text-white/80 mb-6">"As a business owner, EcoLens has helped us implement better recycling practices. Great initiative!"</p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-[#436d2e] rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-briefcase text-white"></i>
                        </div>
                        <div>
                            <div class="text-white font-semibold">Sarah Williams</div>
                            <div class="text-white/60 text-sm">Business Owner</div>
                        </div>
                    </div>
                </div>
            
                <!-- Third testimonial -->
                <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                    <div class="text-[#436d2e] text-2xl mb-4">★★★★★</div>
                    <p class="text-white/80 mb-6">"The chatbot feature is incredibly helpful. It answers all my recycling questions instantly!"</p>
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-[#436d2e] rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-graduation-cap text-white"></i>
                        </div>
                        <div>
                            <div class="text-white font-semibold">Mike Chen</div>
                            <div class="text-white/60 text-sm">Student</div>
                        </div>
                    </div>
                </div>
            </div>
          </div>
        </div>

        <!-- About Us Section -->
        <div id="about" class="py-20 bg-black/30">
            <div class="max-w-7xl mx-auto px-4">
                <h2 class="text-3xl md:text-5xl font-bold text-white text-center mb-12">About EcoLens</h2>
                
                <!-- Mission & Vision -->
                <div class="text-center max-w-3xl mx-auto mb-16">
                    <p class="text-white/80 text-lg leading-relaxed">
                        At EcoLens, we're dedicated to revolutionizing recycling through cutting-edge AI technology. Our mission is to make sustainable living accessible and rewarding for everyone.
                    </p>
                </div>
        
                <!-- Features Grid -->
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="group bg-white/5 backdrop-blur-sm p-8 rounded-xl hover:bg-[#436d2e] transition-all">
                        <div class="bg-[#436d2e] group-hover:bg-white w-16 h-16 rounded-full flex items-center justify-center mb-6">
                            <i class="fa-solid fa-leaf text-white group-hover:text-[#436d2e] text-2xl"></i>
                        </div>
                        <h3 class="text-white text-2xl font-semibold mb-2">Environmental Impact</h3>
                        <p class="text-white/70 group-hover:text-white/90">Contributing to a cleaner planet through smart recycling solutions and waste reduction.</p>
                    </div>
                    <div class="group bg-white/5 backdrop-blur-sm p-8 rounded-xl hover:bg-[#436d2e] transition-all">
                        <div class="bg-[#436d2e] group-hover:bg-white w-16 h-16 rounded-full flex items-center justify-center mb-6">
                            <i class="fa-solid fa-users text-white group-hover:text-[#436d2e] text-2xl"></i>
                        </div>
                        <h3 class="text-white text-2xl font-semibold mb-2">Community Focus</h3>
                        <p class="text-white/70 group-hover:text-white/90">Building a network of environmentally conscious individuals and organizations.</p>
                    </div>
                    <div class="group bg-white/5 backdrop-blur-sm p-8 rounded-xl hover:bg-[#436d2e] transition-all">
                        <div class="bg-[#436d2e] group-hover:bg-white w-16 h-16 rounded-full flex items-center justify-center mb-6">
                            <i class="fa-solid fa-robot text-white group-hover:text-[#436d2e] text-2xl"></i>
                        </div>
                        <h3 class="text-white text-2xl font-semibold mb-2">Innovation First</h3>
                        <p class="text-white/70 group-hover:text-white/90">Leveraging AI technology to make recycling more accessible and efficient.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Section -->
        <div id="contact" class="py-20">
          <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-2 gap-12">
              <div>
                <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">Get in Touch</h2>
                <p class="text-white/80 mb-8">Have questions about recycling? We're here to help!</p>
                <div class="space-y-6">
                  <div class="flex items-center gap-4">
                    <div class="bg-[#436d2e] p-4 rounded-full">
                      <i class="fa-solid fa-envelope text-white"></i>
                    </div>
                    <div>
                      <div class="text-white/60">Email</div>
                      <div class="text-white">contact@ecolens.com</div>
                    </div>
                  </div>
                  <div class="flex items-center gap-4">
                    <div class="bg-[#436d2e] p-4 rounded-full">
                      <i class="fa-solid fa-phone text-white"></i>
                    </div>
                    <div>
                      <div class="text-white/60">Phone</div>
                      <div class="text-white">+1 234 567 890</div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-white/5 p-6 rounded-xl">
                <!-- Contact Form -->
                <form class="space-y-6">
                    <div>
                        <label for="name" class="block text-white/80 text-sm font-medium mb-2">Name</label>
                        <input type="text" id="name" name="name" 
                               class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-[#436d2e] transition-all"
                               placeholder="Your name">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-white/80 text-sm font-medium mb-2">Email</label>
                        <input type="email" id="email" name="email" 
                               class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-[#436d2e] transition-all"
                               placeholder="your.email@example.com">
                    </div>
                    
                    <div>
                        <label for="message" class="block text-white/80 text-sm font-medium mb-2">Message</label>
                        <textarea id="message" name="message" rows="4" 
                                  class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-[#436d2e] transition-all"
                                  placeholder="How can we help you?"></textarea>
                    </div>
                    
                    <button type="submit" 
                            class="w-full bg-[#436d2e] text-white px-6 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-all flex items-center justify-center gap-2">
                        Send Message
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <footer class="py-12 bg-black/30">
          <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8 mb-8">
              <div>
                <div class="flex items-center gap-3 mb-6">
                  <img src="logo.png" alt="EcoLens Logo" class="h-10">
                  <h3 class="text-2xl font-bold">
                    <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                  </h3>
                </div>
                <p class="text-white/60">Making recycling smarter through technology</p>
              </div>
              <div>
                <h4 class="text-white font-semibold mb-4">Quick Links</h4>
                <ul class="space-y-2">
                  <li><a href="home.php" class="text-white/60 hover:text-white transition-colors">Home</a></li>
                  <li><a href="camera.php" class="text-white/60 hover:text-white transition-colors">Camera</a></li>
                  <li><a href="chatbot.php" class="text-white/60 hover:text-white transition-colors">Chatbot</a></li>
                </ul>
              </div>
              <div>
                <h4 class="text-white font-semibold mb-4">Follow Us</h4>
                <div class="flex gap-4">
                  <a href="#" class="text-white/60 hover:text-white transition-colors">
                    <i class="fa-brands fa-facebook text-xl"></i>
                  </a>
                  <a href="#" class="text-white/60 hover:text-white transition-colors">
                    <i class="fa-brands fa-twitter text-xl"></i>
                  </a>
                  <a href="#" class="text-white/60 hover:text-white transition-colors">
                    <i class="fa-brands fa-instagram text-xl"></i>
                  </a>
                </div>
              </div>
            </div>
            <div class="border-t border-white/10 pt-8 text-center">
              <p class="text-white/60">&copy; <?php echo date('Y'); ?> EcoLens. All rights reserved.</p>
            </div>
          </div>
        </footer>
      </div>
    </div>

    <script>

      function toggleDropdown() {
          const dropdown = document.getElementById('profileDropdown');
          dropdown.classList.toggle('hidden');
      }

      // Close dropdown when clicking outside
      document.addEventListener('click', function(event) {
          const dropdown = document.getElementById('profileDropdown');
          const button = event.target.closest('.dropdown-toggle');
          
          if (!button && !dropdown.classList.contains('hidden')) {
              dropdown.classList.add('hidden');
          }
      });

      function toggleMenu() {
        const menu = document.getElementById('mobileMenu');
        menu.classList.toggle('hidden');
      }

      function toggleDropdown() {
        const dropdown = document.getElementById('profileDropdown');
        dropdown.classList.toggle('hidden');
      }

      // Close dropdown when clicking outside
      window.onclick = function(event) {
        if (!event.target.matches('.dropdown-toggle')) {
          const dropdowns = document.getElementsByClassName('dropdown-menu');
          for (let dropdown of dropdowns) {
            if (!dropdown.classList.contains('hidden')) {
              dropdown.classList.add('hidden');
            }
          }
        }
      }
    </script>
  </body>
</html>