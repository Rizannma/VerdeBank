<?php
session_start();
$userId = $_SESSION['user_id'] ?? 0;

if ($userId === 0) {
    header("Location: user_login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "verde_bank_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate and sanitize inputs
$goal_name = trim($_POST['goal_name'] ?? '');
$target_amount = floatval($_POST['target_amount'] ?? 0);

if ($goal_name === '' || $target_amount <= 0) {
    $_SESSION['error'] = "Invalid goal input.";
    header("Location: user_goals.php");
    exit();
}

// Prepare and insert the goal
$stmt = $conn->prepare("INSERT INTO savings_goals (user_id, goal_name, target_amount, amount_saved) VALUES (?, ?, ?, 0)");
$stmt->bind_param("isd", $userId, $goal_name, $target_amount);

if ($stmt->execute()) {
    $_SESSION['message'] = "Goal added successfully!";
} else {
    $_SESSION['error'] = "Failed to add goal.";
}

$stmt->close();
$conn->close();

header("Location: user_goals.php");
exit();
