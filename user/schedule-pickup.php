<?php
session_start();
require_once '../database.php';

// Check if user is logged in, if not redirect to login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit();
}

// Rest of the page code
if(isset($_POST['schedule_pickup'])) {
    $user_id = $_SESSION["user_id"];
    $pickup_date = $_POST['pickup_date'];
    $pickup_time = $_POST['pickup_time'];
    $address = $_POST['address'];
    $items = $_POST['items'];
    $recurring = isset($_POST['recurring']) ? 1 : 0;
    $frequency = $_POST['frequency'] ?? 'one-time';

    $stmt = $conn->prepare("INSERT INTO tbl_pickups (user_id, pickup_date, pickup_time, address, items, recurring, frequency, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->bind_param("issssss", $user_id, $pickup_date, $pickup_time, $address, $items, $recurring, $frequency);
    
    if($stmt->execute()) {
        $_SESSION['success'] = "Pickup scheduled successfully!";
    } else {
        $_SESSION['error'] = "Error scheduling pickup.";
    }
    
    header("Location: schedule-pickup.php");
    exit();
}

// Get user's scheduled pickups
$user_id = $_SESSION["user_id"];
$pickups = $conn->query("SELECT * FROM tbl_pickups WHERE user_id = $user_id ORDER BY pickup_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Schedule Pickup - EcoLens</title>
    <style>
        .bg-overlay {
            background: url('../assets/background.jpg');
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
    </style>
</head>
<body class="font-[Poppins]">
    <!-- Top Navigation -->
    <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex-shrink-0 flex items-center gap-3">
                    <img src="../assets/logo.png" alt="Smart Recycling Logo" class="h-10">
                    <h1 class="text-2xl font-bold">
                        <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                    </h1>
                </div>
                <a href="../home.php" class="text-white hover:text-[#22c55e] transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Back
                </a>
            </div>
        </div>
    </nav>

    <div class="bg-overlay">
        <div class="min-h-screen pt-24 pb-12 px-4">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-3xl md:text-5xl font-bold text-white text-center mb-6">Schedule a Pickup</h1>
                <p class="text-white/80 text-center max-w-3xl mx-auto mb-12">Schedule your recycling pickup at your convenience. We'll come to you!</p>

                <!-- Success/Error Messages -->
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-8 rounded-lg">
                        <p><?php echo $_SESSION['success']; ?></p>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded-lg">
                        <p><?php echo $_SESSION['error']; ?></p>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <!-- Pickup Form -->
                <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl mb-8">
                    <form method="POST" class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-white">Pickup Date</label>
                                <input type="date" name="pickup_date" required 
                                       class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-[#436d2e] transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-white">Preferred Time</label>
                                <select name="pickup_time" required 
                                        class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-[#436d2e] transition-all">
                                    <option value="morning" class="text-black">Morning (8AM - 12PM)</option>
                                    <option value="afternoon" class="text-black">Afternoon (12PM - 4PM)</option>
                                    <option value="evening" class="text-black">Evening (4PM - 8PM)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-white">Pickup Address</label>
                            <textarea name="address" required rows="3" 
                                      placeholder="House/Unit Number, Street Name&#10;Barangay/District&#10;City, Province, ZIP Code"
                                      class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-[#436d2e] transition-all"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-white">Items for Pickup</label>
                            <textarea name="items" required rows="3" 
                                      placeholder="List the items you want to recycle..."
                                      class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-[#436d2e] transition-all"></textarea>
                        </div>
                        
                        <div>
                            <label class="flex items-center">
                                <input type="checkbox" name="recurring" 
                                       class="rounded text-[#436d2e] focus:ring-[#436d2e] bg-white/10 border-white/20">
                                <span class="ml-2 text-sm text-white">Make this a recurring pickup</span>
                            </label>
                        </div>
                        
                        <div id="frequency" class="hidden">
                            <label class="block text-sm font-medium text-white">Frequency</label>
                            <select name="frequency" 
                                    class="mt-1 block w-full px-4 py-3 bg-white/10 border border-white/20 rounded-lg text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-[#436d2e] transition-all">
                                <option value="weekly">Weekly</option>
                                <option value="biweekly">Bi-weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        
                        <button type="submit" name="schedule_pickup" 
                                class="w-full bg-[#436d2e] text-white px-6 py-3 rounded-lg font-semibold hover:bg-opacity-90 transition-all">
                            Schedule Pickup
                        </button>
                    </form>
                </div>
                
                <!-- Scheduled Pickups -->
                <h2 class="text-2xl font-bold text-white mb-4">Your Scheduled Pickups</h2>
                <div class="bg-white/5 backdrop-blur-sm rounded-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead class="bg-black/20">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Type</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                <?php while($pickup = $pickups->fetch_assoc()): ?>
                                <tr class="text-white">
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo date('M d, Y', strtotime($pickup['pickup_date'])); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo ucfirst($pickup['pickup_time']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                   <?php echo $pickup['status'] == 'completed' ? 'bg-[#436d2e]/20 text-[#436d2e]' : 'bg-yellow-500/20 text-yellow-500'; ?>">
                                            <?php echo ucfirst($pickup['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?php echo $pickup['recurring'] ? 'Recurring' : 'One-time'; ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show/hide frequency selector based on recurring checkbox
        document.querySelector('input[name="recurring"]').addEventListener('change', function() {
            document.getElementById('frequency').style.display = this.checked ? 'block' : 'none';
        });
    </script>
</body>
</html>