<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

session_start();

// Role-based validation
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$email = '';
$current_time = time();
$otp_valid_duration = 60;
$msg_type = '';
$msg = '';

// Admin: Use email directly
if ($_SESSION['role'] === 'admin') {
    if (!isset($_SESSION['email'])) {
        header("Location: login.php");
        exit();
    }
    $email = $_SESSION['email'];
} 
// User: Fetch email using either savings or checking account number
else {
    if (!isset($_SESSION['account_number'])) {
        header("Location: login.php");
        exit();
    }

    $account_number = $_SESSION['account_number'];
    $account_number_no_spaces = str_replace(' ', '', $account_number);

    $conn = new mysqli("localhost", "root", "", "verde_bank_db");
    if ($conn->connect_error) {
        die("Database connection failed: " . $conn->connect_error);
    }

    // Check both savings_account_number and checking_account_number with spaces removed
    $stmt = $conn->prepare("
        SELECT email FROM user_accounts 
        WHERE REPLACE(savings_account_number, ' ', '') = ? 
           OR REPLACE(checking_account_number, ' ', '') = ?
    ");
    $stmt->bind_param("ss", $account_number_no_spaces, $account_number_no_spaces);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        $conn->close();
        header("Location: login.php");
        exit();
    }

    $user = $result->fetch_assoc();
    $email = $user['email'];
    $conn->close();
}


// Send OTP if not already sent or expired
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (!isset($_SESSION['otp_sent']) || $current_time > ($_SESSION['otp_expiration'] ?? 0)) {
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        $_SESSION['otp_sent'] = true;
        $_SESSION['otp_expiration'] = $current_time + $otp_valid_duration;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'verdebank.official@gmail.com';
            $mail->Password = 'yngu frcl nqkd drqd'; // Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('verdebank.official@gmail.com', 'Verde Bank');
            $mail->addAddress($email);
            $mail->Subject = 'Your Verde OTP';
            $mail->Body    = "Your one-time password (OTP) is: $otp. It is valid for 60 seconds.";

            $mail->send();
            $msg = "OTP has been successfully sent to $email. <br>Please check your email.";
            $msg_type = 'success';
        } catch (Exception $e) {
            $msg = "Failed to send OTP. Mailer Error: " . $mail->ErrorInfo;
            $msg_type = 'error';
        }
    }
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Resend OTP
    if (isset($_POST['resend_otp'])) {
        if ($current_time > ($_SESSION['otp_expiration'] ?? 0)) {
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_sent'] = true;
            $_SESSION['otp_expiration'] = $current_time + $otp_valid_duration;

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'verdebank.official@gmail.com';
                $mail->Password = 'yngu frcl nqkd drqd';
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('verdebank.official@gmail.com', 'Verde Bank');
                $mail->addAddress($email);
                $mail->Subject = 'Your Verde OTP';
                $mail->Body    = "Your one-time password (OTP) is: $otp. It is valid for 60 seconds.";

                $mail->send();
                $msg = "OTP has been successfully resent to $email.<br>Please check your email.";
                $msg_type = 'success';
            } catch (Exception $e) {
                $msg = "Failed to resend OTP. Mailer Error: " . $mail->ErrorInfo;
                $msg_type = 'error';
            }
        } else {
            $msg = "Please wait until the current OTP expires before requesting a new one.";
            $msg_type = 'error';
        }
    }

    // Verify OTP
    if (isset($_POST['verify_otp'])) {
        $entered_otp = trim($_POST['entered_otp'] ?? '');
        if ($entered_otp == ($_SESSION['otp'] ?? '')) {
            unset($_SESSION['otp'], $_SESSION['otp_sent'], $_SESSION['otp_expiration']);

            if ($_SESSION['role'] === 'admin') {
                header("Location: admin_dashboard.php");
                exit();
            } else {
                header("Location: user_dashboard.php");
                exit();
            }
        } else {
            $msg = "Invalid OTP. Please try again.";
            $msg_type = 'error';
        }
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Verification - Verde</title>
  <link rel="stylesheet" href="login-verification.css?v=1.0">

  </head>

<body>
  <div class="login-verification">
  <img class="verification-rectangle" src="images/verification-rectangle.png" />
  <div class="login-verification2">Login Verification</div>
    <div class="line-4"></div>

          <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
               <div class="otp">OTP:</div>
              <input class="txt-otp" type="text" name="entered_otp" required />

               <div class="btn">
              <button type="submit" name="verify_otp" class="verify">Confirm</button>
              </div>

          <button type="submit" name="resend_otp" class="btn-resend-otp" formnovalidate>Click Here to Resend</button>

           
          </form>
<?php if (!empty($msg)): ?>
<div class="overlay">
    <div class="popup <?php echo $msg_type === 'success' ? 'success' : 'error'; ?>">
        <p><?php echo $msg; ?></p>
        <button onclick="closePopup()">OK</button>
    </div>
</div>
<?php endif; ?>
<script>
function closePopup() {
    const popup = document.querySelector('.overlay');
    if (popup) popup.style.display = 'none';
}
</script>

            <div id="otp-timer" class="otp-status"></div>

    <div class="btn-back" onclick="window.location.href='login.php';">
    <div class="txt-back">Back to Login</div>
    <img class="arrow" src="images/arrow-back.png" />
  </div>


  
</div>

    <script>
    // Countdown timer for OTP expiration
    const expirationTimestamp = <?php echo $_SESSION['otp_expiration'] * 1000; ?>;
    const timerElement = document.getElementById("otp-timer");

    function updateTimer() {
        const now = new Date().getTime();
        const distance = expirationTimestamp - now;

        if (distance <= 0) {
            timerElement.innerText = "OTP expired.";
        } else {
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);
            timerElement.innerText = `Expires in ${seconds}s`;
            setTimeout(updateTimer, 1000);
        }
    }

    updateTimer();
</script>

</body>
</html>


