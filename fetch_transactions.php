<?php
$host = "localhost";
$dbname = "verde_bank_db";
$username = "root";
$password = "";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Use lowercase types matching backend expectations
$sql = "
    SELECT 'Payment' AS type, id, user_id, biller AS recipient, amount, payment_date AS date, suspicious FROM payments
    UNION ALL
    SELECT 'Deposit' AS type, id, user_id, account_name AS recipient, amount, deposit_date AS date, suspicious FROM deposits
    UNION ALL
    SELECT 'External Transfer' AS type, id, user_id, bank AS recipient, amount, transfer_date AS date, suspicious FROM external_transfers
    UNION ALL
    SELECT 'Internal Transfer' AS type, id, user_id, to_account AS recipient, amount, transfer_date AS date, suspicious FROM internal_transfers
    ORDER BY date DESC
";

$result = $conn->query($sql);
$transactions = [];
$suspicious_count = 0;

while($row = $result->fetch_assoc()) {
    $row['suspicious'] = intval($row['suspicious']) === 1;
    if ($row['suspicious']) {
        $suspicious_count++;
    }
    $transactions[] = $row;
}

echo json_encode([
    'transactions' => $transactions,
    'security_alerts' => $suspicious_count
]);

$conn->close();
?>
