<?php
// Fix for missing center_id in session
if (!isset($_SESSION['center_id'])) {
    // Get center_id from the user record
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT center_id FROM tbl_user WHERE id = ? AND user_type = 'center'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $_SESSION['center_id'] = $row['center_id'];
    } else {
        // Fallback if no center_id is found
        header("Location: ../auth/login.php?error=invalid_center");
        exit();
    }
}
?>
