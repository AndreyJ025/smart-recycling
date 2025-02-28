<?php
session_start();
require_once '../database.php';

// Check admin access
if (!isset($_SESSION["is_admin"]) || $_SESSION["is_admin"] != 1) {
    header("Location: ../home.php");
    exit();
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $stmt = $conn->prepare("INSERT INTO tbl_faqs (question, answer, category) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $_POST['question'], $_POST['answer'], $_POST['category']);
                $stmt->execute();
                break;
                
            case 'edit':
                $stmt = $conn->prepare("UPDATE tbl_faqs SET question=?, answer=?, category=? WHERE id=?");
                $stmt->bind_param("sssi", $_POST['question'], $_POST['answer'], $_POST['category'], $_POST['id']);
                $stmt->execute();
                break;
                
            case 'delete':
                $stmt = $conn->prepare("DELETE FROM tbl_faqs WHERE id=?");
                $stmt->bind_param("i", $_POST['id']);
                $stmt->execute();
                break;
        }
        header("Location: manage-faqs.php");
        exit();
    }
}

// Fetch all FAQs
$faqs = $conn->query("SELECT * FROM tbl_faqs ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage FAQs - EcoLens</title>
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
    </style>
</head>
<body class="font-[Poppins]">
    <div class="bg-overlay">
        <!-- Move all content inside this div -->
        <nav class="fixed w-full bg-[#1b1b1b] py-4 z-50">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex justify-between items-center">
                    <div class="flex-shrink-0 flex items-center gap-3">
                        <img src="../assets/logo.png" alt="Smart Recycling Logo" class="h-10">
                        <h1 class="text-2xl font-bold">
                            <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                        </h1>
                    </div>
                    <a href="admin-dashboard.php" class="text-white hover:text-[#22c55e] transition-all">
                        <i class="fa-solid fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </nav>

        <div class="pt-24 pb-12 px-4">
            <div class="max-w-7xl mx-auto">
                <!-- Add FAQ Form -->
                <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm mb-8">
                    <h2 class="text-2xl font-bold text-white mb-6">Add New FAQ</h2>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="add">
                        <div>
                            <label class="block text-white mb-2">Question</label>
                            <input type="text" name="question" required
                                   placeholder="Enter a frequently asked question"
                                   class="w-full px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                        </div>
                        <div>
                            <label class="block text-white mb-2">Answer</label>
                            <textarea name="answer" required rows="4"
                                      placeholder="Provide a clear and helpful answer"
                                      class="w-full px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]"></textarea>
                        </div>
                        <div>
                            <label class="block text-white mb-2">Category</label>
                            <input type="text" name="category" required
                                   placeholder="e.g., General, Recycling Tips, Points System"
                                   class="w-full px-4 py-2 bg-white/10 text-white rounded-lg border border-white/20 focus:outline-none focus:border-[#436d2e]">
                        </div>
                        <button type="submit" class="bg-[#436d2e] text-white px-6 py-2 rounded-lg hover:bg-opacity-90 transition-all">
                            <i class="fa-solid fa-plus mr-2"></i>Add FAQ
                        </button>
                    </form>
                </div>

                <!-- FAQ List -->
                <div class="bg-white/5 p-8 rounded-xl backdrop-blur-sm">
                    <h2 class="text-2xl font-bold text-white mb-6">Manage FAQs</h2>
                    <div class="space-y-4">
                        <?php while($faq = $faqs->fetch_assoc()): ?>
                        <div class="bg-white/5 p-6 rounded-lg">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-white font-semibold"><?php echo htmlspecialchars($faq['question']); ?></h3>
                                    <p class="text-white/70 mt-2"><?php echo htmlspecialchars($faq['answer']); ?></p>
                                    <span class="text-sm text-[#436d2e] mt-2 inline-block"><?php echo htmlspecialchars($faq['category']); ?></span>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick="editFaq(<?php echo $faq['id']; ?>)" 
                                            class="text-white/70 hover:text-white">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this FAQ?');">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $faq['id']; ?>">
                                        <button type="submit" class="text-white/70 hover:text-white">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function editFaq(id) {
        // Implement edit functionality
        // You can use a modal or redirect to an edit page
        alert('Edit FAQ ' + id);
    }
    </script>
</body>
</html>