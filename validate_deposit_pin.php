<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['valid' => false, 'message' => 'User  not logged in.']);
    exit();
}

// Get the PIN from the POST request
$data = json_decode(file_get_contents('php://input'), true);
$pin = $data['pin'] ?? '';
$user_id = intval($_SESSION['user_id']);

// Establish a database connection
$conn = new mysqli("localhost", "root", "", "verde_bank_db");

// Check for connection errors
if ($conn->connect_error) {
    echo json_encode(['valid' => false, 'error' => 'Database connection failed.']);
    exit();
}

// Prepare and execute the SQL statement
$stmt = $conn->prepare("SELECT PIN FROM user_accounts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$isValid = false;
if ($row = $result->fetch_assoc()) {
    // Type match: cast both to strings for comparison
    if (strval($row['PIN']) === strval($pin)) {
        $isValid = true;
    }
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Return the result as JSON
echo json_encode(['valid' => $isValid]);
