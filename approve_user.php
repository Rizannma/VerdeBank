<?php
// approve_user.php?user_id=123

$conn = new mysqli("localhost", "root", "", "verde_bank_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_GET['user_id'];

// Generate a unique 16-digit account number
function generateAccountNumber() {
    $raw = str_pad(mt_rand(0, 9999999999999999), 16, "0", STR_PAD_LEFT);
    return substr(chunk_split($raw, 4, ' '), 0, 19); // e.g., 0000 1234 5678 9012
}

$account_number = generateAccountNumber();

// Approve user and set account number
$update_sql = "UPDATE user_accounts 
               SET status = 'active', is_verified = 1, account_number = ?
               WHERE id = ?";

$stmt = $conn->prepare($update_sql);
$stmt->bind_param("si", $account_number, $user_id);

if ($stmt->execute()) {
    echo "User approved successfully with Account No: $account_number";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
