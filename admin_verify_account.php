<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

$conn = new mysqli("localhost", "root", "", "verde_bank_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate and sanitize user_id
if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
    die("Invalid user ID.");
}
$userId = intval($_POST['user_id']);

// Fetch user
$stmt = $conn->prepare("SELECT * FROM user_accounts WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();
$email = $user['email'];
$fullname = $user['fullname'];

// Generate a unique 16-digit account number with prefix based on account type
function generateUniqueAccountNumber($conn, $prefix) {
    do {
        $raw = $prefix . str_pad(rand(0, 99999999999999), 14, '0', STR_PAD_LEFT);
        $formatted = chunk_split($raw, 4, ' ');
        $check = $conn->prepare("SELECT * FROM user_accounts WHERE savings_account_number = ? OR checking_account_number = ?");
        $check->bind_param("ss", $formatted, $formatted);
        $check->execute();
        $result = $check->get_result();
    } while ($result->num_rows > 0);
    return $formatted;
}

$mail = new PHPMailer(true);

try {
    // SMTP Setup
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'verdebank.official@gmail.com';
    $mail->Password = 'yngu frcl nqkd drqd'; // Gmail App Password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('verdebank.official@gmail.com', 'Verde Bank');
    $mail->addAddress($email, $fullname);
    $mail->isHTML(true);

    if (isset($_POST['approve'])) {
        $savingsAccountNumber = generateUniqueAccountNumber($conn, '01'); // prefix 01 for savings
        $checkingAccountNumber = generateUniqueAccountNumber($conn, '02'); // prefix 02 for checking

        // Update database with both account numbers and activate user
        $update = $conn->prepare("UPDATE user_accounts SET status = 'active', is_verified = 1, savings_account_number = ?, checking_account_number = ?, rejection_reason = NULL WHERE user_id = ?");
        $update->bind_param("ssi", $savingsAccountNumber, $checkingAccountNumber, $userId);
        $update->execute();

        // Send approval email
        $mail->Subject = 'VerdeBank Account Approved';
        $mail->Body = "
            <p>Dear $fullname,</p>
            <p>Congratulations! Your VerdeBank account has been approved.</p>
            <p><strong>Your Savings Account Number:</strong> $savingsAccountNumber</p>
            <p><strong>Your Checking Account Number:</strong> $checkingAccountNumber</p>
            <p>Welcome aboard,<br>VerdeBank Team</p>
        ";
        $mail->send();

    } elseif (isset($_POST['reject_confirm'])) {
        $rejectReason = trim($_POST['reject_reason']);
        if (empty($rejectReason)) {
            die("Rejection reason is required.");
        }

        // Begin transaction for atomicity
        $conn->begin_transaction();

        try {
            // Insert into archive_accounts
            // Adjust column names/types based on your archive_accounts table schema
            $archiveStmt = $conn->prepare("
                INSERT INTO archived_accounts 
                (user_id, fullname, email, rejection_reason, rejected_at)
                VALUES (?, ?, ?, ?, NOW())
            ");
                $archiveStmt = $conn->prepare("
                    INSERT INTO archived_accounts 
                    (user_id, fullname, email, rejection_reason, rejected_at)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $archiveStmt->bind_param(
                    "isss", // Correct: 1 int + 3 strings
                    $user['user_id'],
                    $user['fullname'],
                    $user['email'],
                    $rejectReason
                );

            $archiveStmt->execute();

            // Delete from user_accounts
            $deleteStmt = $conn->prepare("DELETE FROM user_accounts WHERE user_id = ?");
            $deleteStmt->bind_param("i", $userId);
            $deleteStmt->execute();

            // Commit transaction
            $conn->commit();

            // Send rejection email
            $mail->Subject = 'VerdeBank Account Application - Rejected';
            $mail->Body = "
                <p>Dear $fullname,</p>
                <p>We regret to inform you that your VerdeBank account application has been declined for the following reason:</p>
                <p><em>$rejectReason</em></p>
                <p>If you believe this was a mistake, feel free to apply again or contact support.</p>
                <p>Best regards,<br>VerdeBank Team</p>
            ";
            $mail->send();

        } catch (Exception $e) {
            $conn->rollback();
            die("Error archiving and deleting user: " . $conn->error);
        }

    } else {
        die("Invalid action.");
    }

    header("Location: admin_accounts.php?status=success");
    exit();

} catch (Exception $e) {
    echo "Mailer Error: {$mail->ErrorInfo}";
}
?>
