<?php
session_start();
include 'database.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

// Get user's points
$user_id = $_SESSION["user_id"];
$user = $conn->query("SELECT total_points FROM tbl_user WHERE id = $user_id")->fetch_assoc();
$total_points = $user['total_points'];

// Get available rewards
$rewards = $conn->query("SELECT * FROM tbl_rewards ORDER BY points_required ASC");

// Handle reward redemption
if(isset($_POST['redeem_reward'])) {
    $reward_id = (int)$_POST['reward_id'];
    $points_required = (int)$_POST['points_required'];
    
    if($total_points >= $points_required) {
        $conn->begin_transaction();
        
        try {
            // Deduct points
            $stmt = $conn->prepare("UPDATE tbl_user SET total_points = total_points - ? WHERE id = ?");
            $stmt->bind_param("ii", $points_required, $user_id);
            $stmt->execute();
            
            // Record redemption
            $stmt = $conn->prepare("INSERT INTO tbl_redemptions (user_id, reward_id, points_used) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $user_id, $reward_id, $points_required);
            $stmt->execute();
            
            $conn->commit();
            $_SESSION['success'] = "Reward redeemed successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Error redeeming reward.";
        }
        
        header("Location: rewards.php");
        exit();
    }
}

// Get user's redemption history
$redemptions = $conn->query("
    SELECT r.*, rw.name as reward_name, rw.points_required 
    FROM tbl_redemptions r 
    JOIN tbl_rewards rw ON r.reward_id = rw.id 
    WHERE r.user_id = $user_id 
    ORDER BY r.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
    <title>Rewards - EcoLens</title>
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
    </style>
</head>
<body class="font-[Poppins]">
    <!-- Top Navigation -->
    <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex-shrink-0 flex items-center gap-3">
                    <img src="logo.png" alt="Smart Recycling Logo" class="h-10">
                    <h1 class="text-2xl font-bold">
                        <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                    </h1>
                </div>
                <a href="home.php" class="text-white hover:text-[#22c55e] transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Back
                </a>
            </div>
        </div>
    </nav>

    <div class="bg-overlay">
        <div class="min-h-screen pt-24 pb-12 px-4">
            <div class="max-w-7xl mx-auto">
                <h1 class="text-3xl md:text-5xl font-bold text-white text-center mb-6">Your Rewards</h1>
                <p class="text-white/80 text-center max-w-3xl mx-auto mb-12">Redeem your points for exciting rewards and eco-friendly products!</p>

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

                <!-- Points Display -->
                <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl mb-8 text-center">
                    <h2 class="text-white text-xl mb-2">Available Points</h2>
                    <div class="text-4xl font-bold text-[#436d2e]"><?php echo number_format($total_points); ?></div>
                </div>
                
                <!-- All Rewards -->
                <div class="space-y-8 mb-12">
                    <!-- Available Rewards -->
                    <div>
                        <h2 class="text-2xl font-bold text-white mb-6">Available Rewards</h2>
                        <div class="grid md:grid-cols-3 gap-6">
                            <?php 
                            $rewards->data_seek(0); // Reset pointer to start
                            while($reward = $rewards->fetch_assoc()): 
                                if($total_points >= $reward['points_required']):
                            ?>
                            <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl border border-[#436d2e]/30">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <div class="flex items-center gap-2 mb-2">
                                            <h3 class="text-xl font-semibold text-white"><?php echo htmlspecialchars($reward['name']); ?></h3>
                                            <span class="bg-[#436d2e] text-white px-2 py-1 rounded-full text-xs">Available</span>
                                        </div>
                                        <p class="text-white/70"><?php echo htmlspecialchars($reward['description']); ?></p>
                                    </div>
                                    <span class="bg-[#436d2e] text-white px-3 py-1 rounded-full text-sm font-semibold">
                                        <?php echo number_format($reward['points_required']); ?> points
                                    </span>
                                </div>
                                
                                <form method="POST">
                                    <input type="hidden" name="reward_id" value="<?php echo $reward['id']; ?>">
                                    <input type="hidden" name="points_required" value="<?php echo $reward['points_required']; ?>">
                                    <button type="submit" name="redeem_reward" 
                                            class="w-full bg-[#436d2e] text-white px-4 py-2 rounded-lg font-semibold hover:bg-opacity-90 transition-all">
                                        Redeem Reward
                                    </button>
                                </form>
                            </div>
                            <?php endif; endwhile; ?>
                        </div>
                    </div>
                
                    <!-- Locked Rewards -->
                    <div>
                        <h2 class="text-2xl font-bold text-white mb-6">More Rewards to Unlock</h2>
                        <div class="grid md:grid-cols-3 gap-6">
                            <?php 
                            $rewards->data_seek(0); // Reset pointer to start
                            while($reward = $rewards->fetch_assoc()): 
                                if($total_points < $reward['points_required']):
                            ?>
                            <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl border border-white/10 relative overflow-hidden">
                                <!-- Lock Overlay -->
                                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center">
                                    <div class="text-center">
                                        <i class="fa-solid fa-lock text-white/50 text-4xl mb-2"></i>
                                        <div class="text-white/70">
                                            Need <?php echo number_format($reward['points_required'] - $total_points); ?> more points
                                        </div>
                                    </div>
                                </div>
                
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-xl font-semibold text-white/50"><?php echo htmlspecialchars($reward['name']); ?></h3>
                                        <p class="text-white/40"><?php echo htmlspecialchars($reward['description']); ?></p>
                                    </div>
                                    <span class="bg-white/10 text-white/50 px-3 py-1 rounded-full text-sm font-semibold">
                                        <?php echo number_format($reward['points_required']); ?> points
                                    </span>
                                </div>
                                
                                <button disabled 
                                        class="w-full bg-white/10 text-white/50 px-4 py-2 rounded-lg font-semibold cursor-not-allowed">
                                    Redeem Reward
                                </button>
                            </div>
                            <?php endif; endwhile; ?>
                        </div>
                    </div>
                </div>

                <!-- Redemption History -->
                <h2 class="text-2xl font-bold text-white mb-4">Redemption History</h2>
                <div class="bg-white/5 backdrop-blur-sm rounded-xl overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-white/10">
                            <thead class="bg-black/20">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Reward</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Points Used</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                <?php while($redemption = $redemptions->fetch_assoc()): ?>
                                <tr class="text-white">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo date('M d, Y', strtotime($redemption['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo htmlspecialchars($redemption['reward_name']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php echo number_format($redemption['points_used']); ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>