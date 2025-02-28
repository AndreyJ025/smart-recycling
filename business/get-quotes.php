<?php
session_start();
require_once '../database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['request_id'])) {
    exit(json_encode(['error' => 'Unauthorized']));
}

$request_id = filter_input(INPUT_GET, 'request_id', FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'];

// Verify this request belongs to the business
$verify_query = "SELECT * FROM tbl_bulk_requests WHERE id = ? AND business_id = ?";
$stmt = $conn->prepare($verify_query);
$stmt->bind_param("ii", $request_id, $user_id);
$stmt->execute();
$request_result = $stmt->get_result();
$request = $request_result->fetch_assoc();

if (!$request) {
    exit(json_encode(['error' => 'Request not found']));
}

// Get quotes for this request
$quotes_query = "SELECT q.*, s.name as center_name, s.address as center_address 
                 FROM tbl_quotes q
                 JOIN tbl_sortation_centers s ON q.center_id = s.id
                 WHERE q.request_id = ?";
$stmt = $conn->prepare($quotes_query);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$quotes_result = $stmt->get_result();
$quotes = [];
while ($row = $quotes_result->fetch_assoc()) {
    $quotes[] = $row;
}

echo json_encode([
    'request' => $request,
    'quotes' => $quotes
]);

$conn->close();
?>