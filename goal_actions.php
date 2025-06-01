<?php
session_start();
$userId = $_SESSION['user_id'] ?? 0;

if ($userId <= 0) {
    // Not logged in, redirect to login or home
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "verde_bank_db");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$action = $_POST['action'] ?? '';
$goal_name = $_POST['goal_name'] ?? '';

if (!$goal_name) {
    header("Location: user_goals.php");
    exit;
}

// Sanitize inputs
$goal_name = $conn->real_escape_string($goal_name);

if ($action === 'allocate') {
    $allocation = floatval($_POST['allocation'] ?? 0);

    if ($allocation <= 0) {
        $_SESSION['error'] = "Invalid allocation amount.";
        header("Location: user_goals.php");
        exit;
    }

    // Get current savings_balance
    $stmt = $conn->prepare("SELECT savings_balance FROM user_accounts WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($savings_balance);
    $stmt->fetch();
    $stmt->close();

    if ($allocation > $savings_balance) {
        $_SESSION['error'] = "Allocation exceeds your available savings balance.";
        header("Location: user_goals.php");
        exit;
    }

    // Get current amount_saved and target_amount for the goal
    $stmt = $conn->prepare("SELECT amount_saved, target_amount FROM savings_goals WHERE user_id = ? AND goal_name = ?");
    $stmt->bind_param("is", $userId, $goal_name);
    $stmt->execute();
    $stmt->bind_result($amount_saved, $target_amount);
    if (!$stmt->fetch()) {
        // Goal not found
        $stmt->close();
        $_SESSION['error'] = "Goal not found.";
        header("Location: user_goals.php");
        exit;
    }
    $stmt->close();

    // Calculate new saved amount, capped at target_amount
    $new_amount_saved = $amount_saved + $allocation;
    if ($new_amount_saved > $target_amount) {
        $allocation = $target_amount - $amount_saved;
        $new_amount_saved = $target_amount;
    }

    // Update savings_goals
    $stmt = $conn->prepare("UPDATE savings_goals SET amount_saved = ? WHERE user_id = ? AND goal_name = ?");
    $stmt->bind_param("dis", $new_amount_saved, $userId, $goal_name);
    $stmt->execute();
    $stmt->close();

    // Deduct allocation from user_accounts.savings_balance
    $stmt = $conn->prepare("UPDATE user_accounts SET savings_balance = savings_balance - ? WHERE user_id = ?");
    $stmt->bind_param("di", $allocation, $userId);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Added ₱" . number_format($allocation, 2) . " to \"$goal_name\".";

    header("Location: user_goals.php");
    exit;

} elseif ($action === 'achieve') {
    // Delete the goal when achieved (or you can mark it achieved if you want)
    $stmt = $conn->prepare("DELETE FROM savings_goals WHERE user_id = ? AND goal_name = ?");
    $stmt->bind_param("is", $userId, $goal_name);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Goal \"$goal_name\" marked as achieved and removed.";

    header("Location: user_goals.php");
    exit;

} elseif ($action === 'delete') {
    // Just delete the goal
    $stmt = $conn->prepare("DELETE FROM savings_goals WHERE user_id = ? AND goal_name = ?");
    $stmt->bind_param("is", $userId, $goal_name);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Goal \"$goal_name\" deleted.";

    header("Location: user_goals.php");
    exit;

} else {
    // Invalid action, redirect back
    header("Location: user_goals.php");
    exit;
}
?>
