<?php
    session_start();
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    include '../database.php';

    // Check admin status
    if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
        header("Location: ../home.php");
        exit();
    }

    // SQL query for pending submissions first, then others
    $sql = "SELECT 
                r.*,
                s.name as center_name,
                s.address as center_address,
                u.fullname as user_fullname 
            FROM tbl_remit r
            INNER JOIN tbl_sortation_centers s ON s.id = r.sortation_center_id 
            INNER JOIN tbl_user u ON u.id = r.user_id
            ORDER BY r.points ASC, r.created_at DESC";
            
    $result = $conn->query($sql);
    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    // Update points processing
    if(isset($_POST['update_points'])) {
        $remit_id = (int)$_POST['remit_id'];
        $points = (int)$_POST['points'];
        $user_id = (int)$_POST['user_id'];
        
        if($points > 0) {
            try {
                $conn->begin_transaction();
                
                // Get old points value
                $stmt_old = $conn->prepare("SELECT points FROM tbl_remit WHERE id = ?");
                if(!$stmt_old) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $stmt_old->bind_param("i", $remit_id);
                if(!$stmt_old->execute()) {
                    throw new Exception("Execute failed: " . $stmt_old->error);
                }
                
                $result_old = $stmt_old->get_result();
                $old_points = $result_old->fetch_assoc()['points'] ?? 0;
                
                $points_difference = $points - $old_points;
                
                // Update remit points
                $stmt = $conn->prepare("UPDATE tbl_remit SET points = ? WHERE id = ?");
                $stmt->bind_param("ii", $points, $remit_id);
                $stmt->execute();
                
                // Update user total points
                $stmt2 = $conn->prepare("UPDATE tbl_user SET total_points = total_points + ? WHERE id = ?");
                $stmt2->bind_param("ii", $points_difference, $user_id);
                $stmt2->execute();
                
                $conn->commit();
                $_SESSION['show_toast'] = true;
                
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
                
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['error'] = $e->getMessage();
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Points - EcoLens</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap" rel="stylesheet">
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
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 9999;
            pointer-events: none;
        }
        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }
    </style>
</head>
<body class="font-[Poppins]">
    <!-- Toast Notification -->
    <div id="toast" class="toast bg-[#436d2e] text-white px-6 py-4 rounded-xl shadow-lg flex items-center gap-3">
        <i class="fa-solid fa-circle-check"></i>
        <span>Points updated successfully!</span>
    </div>

    <!-- Navigation -->
    <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="flex-shrink-0 flex items-center gap-3">
                    <img src="../assets/logo.png" alt="Smart Recycling Logo" class="h-10">
                    <h1 class="text-2xl font-bold">
                        <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                    </h1>
                </div>
                <a href="admin-dashboard.php" class="text-white hover:bg-white hover:text-black px-3 py-2 rounded-md text-lg font-medium transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="bg-overlay">
        <div class="min-h-screen pt-24 pb-12 px-4">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="text-center mb-12">
                    <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">Points Management</h2>
                    <p class="text-white/80 max-w-3xl mx-auto">Review and award points to users for their recycling submissions</p>
                </div>

                <!-- Stats -->
                <div class="grid md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                        <div class="flex items-center gap-4">
                            <div class="bg-[#436d2e] p-3 rounded-xl">
                                <i class="fa-solid fa-clock-rotate-left text-white text-xl"></i>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white"><?php echo $conn->query("SELECT COUNT(*) as count FROM tbl_remit WHERE points = 0")->fetch_assoc()['count']; ?></div>
                                <div class="text-white/60">Pending Reviews</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                        <div class="flex items-center gap-4">
                            <div class="bg-[#436d2e] p-3 rounded-xl">
                                <i class="fa-solid fa-star text-white text-xl"></i>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white"><?php echo $conn->query("SELECT SUM(points) as total FROM tbl_remit")->fetch_assoc()['total'] ?? 0; ?></div>
                                <div class="text-white/60">Total Points</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl">
                        <div class="flex items-center gap-4">
                            <div class="bg-[#436d2e] p-3 rounded-xl">
                                <i class="fa-solid fa-users text-white text-xl"></i>
                            </div>
                            <div>
                                <div class="text-2xl font-bold text-white"><?php echo $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM tbl_remit")->fetch_assoc()['count']; ?></div>
                                <div class="text-white/60">Active Users</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl mb-8">
                    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="flex items-center gap-4">
                            <button onclick="filterRecords('all')" class="px-6 py-3 bg-[#436d2e] text-white rounded-lg font-semibold hover:bg-opacity-90 transition-all">
                                All Submissions
                            </button>
                            <button onclick="filterRecords('pending')" class="px-6 py-3 border-2 border-[#436d2e] text-[#436d2e] rounded-lg font-semibold hover:bg-[#436d2e] hover:text-white transition-all">
                                Pending Points
                            </button>
                        </div>
                        <div class="relative flex-1 max-w-md">
                            <input type="text" 
                                   id="searchRecord" 
                                   placeholder="Search by user or item..." 
                                   class="w-full px-6 py-3 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all pl-12">
                            <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-white/50"></i>
                        </div>
                    </div>
                </div>

                <!-- Submissions List -->
                <div class="space-y-4" id="recordsList">
                    <?php if ($result->num_rows > 0): 
                        while($row = $result->fetch_assoc()): ?>
                        <div class="bg-white/5 backdrop-blur-sm p-6 rounded-xl hover:bg-[#436d2e]/10 transition-all" 
                             data-points="<?php echo $row['points']; ?>">
                            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                                <div class="flex items-start gap-4 flex-1">
                                    <div class="bg-[#436d2e] w-12 h-12 rounded-xl flex items-center justify-center shrink-0">
                                        <i class="fa-solid fa-recycle text-white text-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-white font-bold mb-1"><?php echo htmlspecialchars($row["user_fullname"]); ?></h3>
                                        <p class="text-white/80">
                                            <?php echo htmlspecialchars($row["item_quantity"]); ?> × <?php echo htmlspecialchars($row["item_name"]); ?>
                                        </p>
                                        <div class="flex flex-wrap items-center gap-3 mt-2">
                                            <span class="text-white/60 text-sm">
                                                <i class="fa-solid fa-location-dot mr-1"></i>
                                                <?php echo htmlspecialchars($row["center_name"]); ?>
                                            </span>
                                            <span class="text-white/60 text-sm">
                                                <i class="fa-regular fa-clock mr-1"></i>
                                                <?php echo date('M d, Y', strtotime($row["created_at"])); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <form method="POST" class="flex items-center gap-2">
                                    <input type="hidden" name="remit_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                                    <input type="number" 
                                           name="points" 
                                           placeholder="Points" 
                                           value="<?php echo $row['points']; ?>"
                                           class="w-24 px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all"
                                           min="1" 
                                           required>
                                    <button type="submit" 
                                            name="update_points" 
                                            class="px-6 py-2 bg-[#436d2e] text-white rounded-lg font-semibold hover:bg-opacity-90 transition-all">
                                        <?php echo $row['points'] > 0 ? 'Update' : 'Award'; ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile;
                    else: ?>
                        <div class="text-center bg-white/5 backdrop-blur-sm p-12 rounded-xl">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-[#436d2e] rounded-xl mb-4">
                                <i class="fa-solid fa-box-open text-white text-2xl"></i>
                            </div>
                            <h3 class="text-white text-xl font-bold mb-2">No Submissions Yet</h3>
                            <p class="text-white/80">There are no recycling submissions to review at this time.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filterRecords(type) {
            const records = document.querySelectorAll('#recordsList > div[data-points]');
            records.forEach(record => {
                const points = parseInt(record.dataset.points);
                if (type === 'all') {
                    record.style.display = 'block';
                } else if (type === 'pending') {
                    record.style.display = points === 0 ? 'block' : 'none';
                }
            });
        }

        document.getElementById('searchRecord').addEventListener('input', function(e) {
            const searchText = e.target.value.toLowerCase();
            const records = document.querySelectorAll('#recordsList > div[data-points]');
            
            records.forEach(record => {
                const text = record.textContent.toLowerCase();
                record.style.display = text.includes(searchText) ? 'block' : 'none';
            });
        });

        <?php if(isset($_SESSION['show_toast'])): ?>
        document.addEventListener('DOMContentLoaded', () => {
            const toast = document.getElementById('toast');
            if(toast) {
                toast.classList.add('show');
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 3000);
            }
        });
        <?php 
        unset($_SESSION['show_toast']);
        endif; 
        ?>
    </script>
</body>
</html>