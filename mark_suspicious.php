<?php
header("Content-Type: application/json");
require 'db_connection.php'; // Adjust path if needed

$data = json_decode(file_get_contents("php://input"), true);

// Debug log (optional — remove in production)
error_log("Received data: " . print_r($data, true));

// Extract data
$transactionId = $data['id'] ?? null; // Matches your schema
$action = $data['action'] ?? null;
$type = $data['type'] ?? null;

// Validate input
if (!$transactionId || !$action || !$type) {
    echo json_encode(["success" => false, "message" => "Missing data"]);
    exit;
}

// Map normalized type to correct table
$validTypes = [
    "payment" => "payments",
    "deposit" => "deposits",
    "internal_transfer" => "internal_transfers",
    "external_transfer" => "external_transfers"
];

// Normalize type: lowercase and replace spaces with underscores
$normalizedType = strtolower(str_replace(' ', '_', $type));
$table = $validTypes[$normalizedType] ?? null;

if (!$table) {
    echo json_encode(["success" => false, "message" => "Invalid transaction type"]);
    exit;
}

// Determine value for suspicious flag
$suspiciousValue = null;
if ($action === "mark") {
    $suspiciousValue = 1;
} elseif ($action === "unmark") {
    $suspiciousValue = 0;
} else {
    echo json_encode(["success" => false, "message" => "Invalid action"]);
    exit;
}

// Prepare and execute update
$stmt = $conn->prepare("UPDATE `$table` SET suspicious = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param("ii", $suspiciousValue, $transactionId);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    error_log("Update failed: " . $stmt->error);
    echo json_encode(["success" => false, "message" => "Failed to update transaction"]);
}

$stmt->close();
$conn->close();
?>
