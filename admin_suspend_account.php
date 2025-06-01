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

if (isset($_POST['suspend'])) {
    $user_id = $_POST['user_id'];
    $reason = $_POST['reason'];
    $duration = $_POST['duration'];

    $allowed_reasons = ['Fraud', 'Suspicious Activity', 'Overdue Loan'];
    if (!in_array($reason, $allowed_reasons)) {
        echo "<script>alert('Invalid suspension reason.'); window.history.back();</script>";
        exit;
    }

    if (!preg_match('/^\d+\s+(day|days|week|weeks|month|months|year|years)$/i', $duration)) {
        echo "<script>alert('Invalid duration format. Use formats like \"30 days\", \"2 weeks\", \"1 month\", or \"1 year\".'); window.history.back();</script>";
        exit;
    }

    // Fetch user data for email
    $query = $conn->prepare("SELECT fullname, email FROM user_accounts WHERE user_id = ?");
    $query->bind_param("i", $user_id);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();
    $query->close();

    // Suspend account
    $stmt = $conn->prepare("UPDATE user_accounts SET status='suspended', suspension_reason=?, suspension_duration=? WHERE user_id=?");
    $stmt->bind_param("ssi", $reason, $duration, $user_id);
    $stmt->execute();
    $stmt->close();

    // Send suspension email if user found
    if ($user) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'verdebank.official@gmail.com'; 
            $mail->Password = 'yngu frcl nqkd drqd'; // App password only
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('verdebank.official@gmail.com', 'Verde Bank');
            $mail->addAddress($user['email'], $user['fullname']);

            $mail->isHTML(true);
            $mail->Subject = 'Your Verde Bank Account Has Been Suspended';
            $mail->Body = "
                <p>Dear <strong>{$user['fullname']}</strong>,</p>
                <p>We regret to inform you that your Verde Bank account has been <strong>suspended</strong>.</p>
                <p><strong>Reason:</strong> {$reason}<br>
                   <strong>Duration:</strong> {$duration}</p>
                <p>If you believe this was done in error or need further assistance, please contact our support team immediately.</p>
                <br>
                <p>Sincerely,<br>The Verde Bank Team</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            error_log("Suspension email failed: " . $mail->ErrorInfo);
            // Optionally alert admin or log this visibly
        }
    }

    header("Location: admin_accounts.php");
    exit;
}
?>
