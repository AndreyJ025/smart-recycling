<?php
// This is a snippet showing how the login should set up session variables
// Assuming $user contains the user data from the database after successful login

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_fullname'] = $user['fullname'];
$_SESSION['user_type'] = $user['user_type'];

// If user is a center, also set the center_id
if ($user['user_type'] === 'center') {
    $_SESSION['center_id'] = $user['center_id'];
}

// Redirect based on user type
// ...
?>
