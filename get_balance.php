<?php
// Start the session
session_start();

// Assuming you have the user ID stored in the session
$user_id = $_SESSION['user_id']; // Get the user ID (adjust based on your session structure)

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "verde_bank_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get checking and savings balances
$sql = "SELECT checking_balance, savings_balance FROM user_accounts WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);  // 'i' is for integer (user_id)
$stmt->execute();
$result = $stmt->get_result();

// Check if the user exists
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $checking_balance = $row['checking_balance'];
    $savings_balance = $row['savings_balance'];
    $total_balance = $checking_balance + $savings_balance;
} else {
    $checking_balance = 0;
    $savings_balance = 0;
    $total_balance = 0;
}

$stmt->close();
$conn->close();

// Return the balances as JSON
echo json_encode([
    'checking_balance' => number_format($checking_balance, 2),
    'savings_balance' => number_format($savings_balance, 2),
    'total_balance' => number_format($total_balance, 2)
]);
?>
