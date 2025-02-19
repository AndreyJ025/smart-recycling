<?php
require_once '../database.php';

if (isset($_GET['question'])) {
    $stmt = $conn->prepare("SELECT answer FROM tbl_faqs WHERE question = ?");
    $stmt->bind_param("s", $_GET['question']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['answer' => $row['answer']]);
    } else {
        echo json_encode(['answer' => 'Sorry, I couldn\'t find an answer to that question.']);
    }
} else {
    echo json_encode(['answer' => 'Invalid request']);
}