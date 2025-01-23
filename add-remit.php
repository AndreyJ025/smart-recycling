<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check session
if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

include 'database.php';

// Fetch centers
$lstCenters = [];
$sql = "SELECT * FROM tbl_sortation_centers";
$result = $conn->query($sql);

if ($result) {
    $lstCenters = $result->fetch_all(MYSQLI_ASSOC);
}

// Form processing
if (isset($_POST['submit'])) {
    $errors = [];
    
    // Validate inputs
    if (empty($_POST['center_id'])) {
        $errors[] = "Please select a center";
    }
    if (empty($_POST['item_name'])) {
        $errors[] = "Please enter item description";
    }
    if (empty($_POST['quantity']) || !is_numeric($_POST['quantity'])) {
        $errors[] = "Please enter valid quantity";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("INSERT INTO tbl_remit (item_name, sortation_center_id, user_id, item_quantity) VALUES (?, ?, ?, ?)");
            
            $user_id = (int)$_SESSION["user_id"];
            $item_name = trim($_POST['item_name']);
            $center_id = (int)$_POST['center_id'];
            $quantity = (int)$_POST['quantity'];
            
            $stmt->bind_param("siii", $item_name, $center_id, $user_id, $quantity);
            
            if ($stmt->execute()) {
                echo "<script>alert('Record added successfully!'); window.location.href='home.php';</script>";
                exit();
            } else {
                throw new Exception("Error saving record");
            }
        } catch (Exception $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#7ed957] max-w-[720px] mx-auto px-4 pb-24 lg:max-w-[900px]">
    <div class="flex flex-col items-center">
        <img class="w-[40%] max-w-[300px] mt-[clamp(40px,8vh,80px)] mb-5 md:w-[60%] md:mt-10" 
             src="smart-recycling-logo.jpg"/>

        <p class="text-[clamp(30px,5vw,50px)] font-bold text-white my-5">
            Register Center Remit
        </p>

        <?php if (!empty($errors)): ?>
            <div class="w-full max-w-[500px] bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="w-full max-w-[500px] flex flex-col gap-4">
            <select name="center_id" 
                    class="w-full px-4 py-3 text-[clamp(1rem,3vw,1.5rem)] rounded-lg border-0 focus:outline-none">
                <option value="" selected disabled>Select Center Name...</option>
                <?php foreach ($lstCenters as $center): ?>
                    <option value="<?php echo htmlspecialchars($center['id']); ?>">
                        <?php echo htmlspecialchars($center['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <textarea name="item_name" 
                      placeholder="Enter Items Description..." 
                      class="w-full px-4 py-3 text-[clamp(1rem,3vw,1.5rem)] rounded-lg border-0 focus:outline-none"
                      rows="3"><?php echo isset($_POST['item_name']) ? htmlspecialchars($_POST['item_name']) : ''; ?></textarea>

            <input type="number" 
                   name="quantity"
                   placeholder="Enter Item Quantity..."
                   value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : ''; ?>"
                   class="w-full px-4 py-3 text-[clamp(1rem,3vw,1.5rem)] rounded-lg border-0 focus:outline-none"/>

            <button type="submit" 
                    name="submit"
                    class="w-full bg-white text-black font-bold text-[clamp(1.2rem,4vw,2rem)] rounded-full py-4 mt-4 hover:bg-gray-100 hover:scale-[1.02] transition-all duration-200">
                Add New Remit
            </button>
        </form>

        <!-- Bottom Navigation -->
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
    </div>
</body>
</html>