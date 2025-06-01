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

if (isset($_POST['reactivate'])) {
    $user_id = $_POST['user_id'];

    // Get user details
    $getUser = $conn->prepare("SELECT fullname, email FROM user_accounts WHERE user_id = ?");
    $getUser->bind_param("i", $user_id);
    $getUser->execute();
    $result = $getUser->get_result();
    $user = $result->fetch_assoc();
    $getUser->close();

    // Reactivate the account
    $stmt = $conn->prepare("UPDATE user_accounts SET status='active', suspension_reason=NULL, suspension_duration=NULL WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();

    // Send reactivation email
    if ($user) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'verdebank.official@gmail.com'; 
            $mail->Password = 'yngu frcl nqkd drqd';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('your-email@gmail.com', 'Verde Bank');
            $mail->addAddress($user['email'], $user['fullname']);

            $mail->isHTML(true);
            $mail->Subject = 'Your Verde Account Has Been Reactivated';
            $mail->Body = "
                <p>Dear <strong>{$user['fullname']}</strong>,</p>
                <p>We are pleased to inform you that your account has been successfully reactivated.</p>
                <p>You may now log in and continue using our services.</p>
                <br>
                <p>Thank you,<br>Verde Bank Team</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Email failed to send: " . $mail->ErrorInfo);
            // Optional: you could notify the admin here
        }
    }

    header("Location: admin_accounts.php");
    exit;
}
?>
