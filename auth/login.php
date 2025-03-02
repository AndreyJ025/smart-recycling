<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$error_msg = '';
$redirect_url = isset($_GET['redirect']) ? $_GET['redirect'] : '../home.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../database.php';
    
    // Sanitize inputs
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $password = mysqli_real_escape_string($conn, $_POST["password"]);
    
    // Modified query to include is_admin and user_type
    $stmt = $conn->prepare("SELECT id, fullname, is_admin, user_type FROM tbl_user WHERE username = ? AND password = ? LIMIT 1");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Set session variables including admin status and user type
        $_SESSION["logged_in"] = true;
        $_SESSION["user_id"] = $user['id'];
        $_SESSION["user_fullname"] = $user['fullname'];
        $_SESSION["is_admin"] = $user['is_admin'];
        $_SESSION["user_type"] = $user['user_type'];
        
        error_log("Login successful - User ID: " . $_SESSION["user_id"] . " Admin: " . $_SESSION["is_admin"] . " Type: " . $_SESSION["user_type"]);
        
        // Get redirect URL from form or fall back to default redirects
        $redirect = isset($_POST['redirect']) && !empty($_POST['redirect']) 
                  ? $_POST['redirect'] 
                  : ($user['is_admin'] ? '../admin/admin-dashboard.php' : 
                    ($user['user_type'] === 'business' ? '../business/dashboard.php' : 
                    ($user['user_type'] === 'center' ? '../centers/dashboard.php' : '../home.php')));
        
        header("Location: " . $redirect);
        exit();
    } else {
        $error_msg = "Invalid email or password.";
    }
    
    $stmt->close();
    $conn->close();
}
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
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="w-full max-w-[500px]">
                <!-- Back Button -->
                <a href="../index.php" class="inline-flex items-center text-white mb-8 hover:text-[#436d2e] transition-all">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Back
                </a>
    
                <!-- Login Container -->
                <div class="bg-white/5 backdrop-blur-sm p-8 rounded-xl">
                    <!-- Logo Section -->
                    <div class="text-center mb-8">
                        <img src="../assets/logo.png" alt="Smart Recycling Logo" class="w-[40%] max-w-[200px] mx-auto mb-4">
                        <h1 class="text-2xl font-bold">
                            <span class="text-[#4e4e10]">Eco</span><span class="text-[#436d2e]">Lens</span>
                        </h1>
                    </div>
    
                    <?php if($error_msg): ?>
                        <div class="bg-red-500/20 text-red-200 p-4 rounded-xl mb-6">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                                <p class="font-medium"><?php echo $error_msg; ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
    
                    <!-- Login Form -->
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect_url); ?>">
                        
                        <div class="relative group">
                            <input type="email" 
                                   name="email" 
                                   placeholder="Enter Email..." 
                                   class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all"
                                   required>
                            <i class="fa-solid fa-envelope absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                        </div>
    
                        <div class="relative group">
                            <input type="password" 
                                   name="password" 
                                   placeholder="Enter Password..." 
                                   class="w-full px-6 py-4 bg-white/10 text-white rounded-xl border border-white/20 focus:outline-none focus:border-[#436d2e] transition-all"
                                   required>
                            <i class="fa-solid fa-lock absolute right-4 top-1/2 -translate-y-1/2 text-white/50 group-hover:text-[#436d2e] transition-colors"></i>
                        </div>
    
                        <button type="submit" 
                                class="w-full bg-[#436d2e] text-white font-bold text-lg rounded-xl py-4 hover:bg-opacity-90 transition-all flex items-center justify-center gap-2">
                            <i class="fa-solid fa-right-to-bracket"></i>
                            LOGIN
                        </button>
                    </form>
    
                    <!-- Sign Up Link -->
                    <p class="text-white/80 text-center mt-6">
                        Don't have an account? 
                        <a href="signup.php" class="text-[#436d2e] hover:text-white transition-all">Register</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>