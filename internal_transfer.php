<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "verde_bank_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];
$sender = $_POST['sender'] ?? '';
$receiver = $_POST['receiver'] ?? '';
$amount = floatval($_POST['amount'] ?? 0);
$pin = $_POST['pin'] ?? '';

$errors = [];

// Basic validation
if ($sender === $receiver) {
    $errors[] = "Sender and receiver must be different.";
}
if (!in_array($sender, ['Savings', 'Checking']) || !in_array($receiver, ['Savings', 'Checking'])) {
    $errors[] = "Invalid account types.";
}
if ($amount <= 0) {
    $errors[] = "Amount must be greater than 0.";
}
if (!preg_match('/^\d{4}$/', $pin)) {
    $errors[] = "PIN must be exactly 4 digits.";
}

if (empty($errors)) {
    // Fetch PIN and balances from DB
    $stmt = $conn->prepare("SELECT PIN, savings_balance, checking_balance FROM user_accounts WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($db_pin, $savings_balance, $checking_balance);
    $stmt->fetch();
    $stmt->close();

    // Trim and compare PINs as strings
    $db_pin = trim($db_pin);
    $pin = trim($pin);

    if ((string)$db_pin !== (string)$pin) {
        $errors[] = "Incorrect PIN.";
    } else {
        $sender_balance = $sender === 'savings' ? $savings_balance : $checking_balance;

        if ($amount > $sender_balance) {
            $errors[] = "Insufficient funds in your $sender account.";
        } else {
            // Proceed with transfer: update balances atomically
            $update = $conn->prepare("
                UPDATE user_accounts 
                SET {$sender}_balance = {$sender}_balance - ?, 
                    {$receiver}_balance = {$receiver}_balance + ? 
                WHERE user_id = ?");
            $update->bind_param("ddi", $amount, $amount, $user_id);
            $update->execute();
            $update->close();

            // Record transaction in internal_transfers table
            $insert = $conn->prepare("INSERT INTO internal_transfers (user_id, from_account, to_account, amount) VALUES (?, ?, ?, ?)");
            $insert->bind_param("issd", $user_id, $sender, $receiver, $amount);
            $insert->execute();
            $insert->close();

            $_SESSION['success'] = "✅ Transfer successful!";
            header("Location: user_accounts.php");
            exit();
        }
    }
}

$conn->close();

// On errors, save messages and redirect back
$_SESSION['error'] = implode("<br>", $errors);
header("Location: user_accounts.php");
exit();
?>
