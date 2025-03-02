<?php

// Database connection details

// $servername = "localhost";
// $username = "alldzqjh_smart_recycling";
// $pass = "RPe4fmEzfngBc@U";
// $dbname = "alldzqjh_smart_recycling";

// Database connection details
$servername = getenv('DB_HOST') ?: "localhost";
$username = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASSWORD') ?: "";
$dbname = getenv('DB_NAME') ?: "smart_recycling";

// Create connection
$conn = new mysqli($servername, $username, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}