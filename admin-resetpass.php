<?php
session_start();

// Restrict access to admin account reset only
if (!isset($_SESSION['reset_email']) || $_SESSION['account_type'] !== 'admin') {
    header("Location: forgot-password.php");
    exit();
}

$popup = ""; // for error/success message
$popupClass = ""; // for message style (error or success)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "verde_bank_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = $_SESSION['reset_email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $popup = "Passwords do not match. Please try again.";
        $popupClass = "error-popup"; // Error message style
    } else {
        $sql = "UPDATE admin_accounts SET password = '$new_password' WHERE email = '$email'";
        if ($conn->query($sql) === TRUE) {
            unset($_SESSION['reset_email']);
            unset($_SESSION['account_type']);
            $popup = "Password has been successfully changed. You can now log in.";
            $popupClass = "success-popup"; // Success message style
            $redirect = true;
        } else {
            $popup = "Failed to reset password.";
            $popupClass = "error-popup"; // Error message style
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <link rel="stylesheet" href="user-resetpass.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body>

<div class="reset-password">
  <img class="rectangle-1" src="images/login-rectangle.png" />
  <div class="reset-password2">Reset Password</div>
  <div class="line-4"></div>

  <form method="POST" action="">
      <label for="new-password" class="enter-new-password">Enter New Password:</label>
      <input type="password" id="new-password" name="new_password" class="txt-new-pass" required />

      <label for="confirm-password" class="confirm-password">Confirm Password:</label>
      <input type="password" id="confirm-password" name="confirm_password" class="txt-confirm-pass" required />

      <div class="btn-confirm">
          <button class="confirm">Confirm</button>
      </div>
  </form>

  <div class="btn-back" onclick="window.location.href='login.php';">
      <div class="txt-back">Back to Login</div>
      <img class="arrow" src="images/arrow-back.png" />
  </div>
</div>

<?php if (!empty($popup)): ?>
    <div class="error-overlay" id="popup">
        <div class="<?php echo $popupClass; ?>">
            <p><?php echo $popup; ?></p>
            <button onclick="closeErrorPopup()">OK</button>
        </div>
    </div>
    <?php if (isset($redirect) && $redirect): ?>
        <script>
            function closeErrorPopup() {
                document.getElementById('popup').style.display = 'none';
                window.location.href = "admin_login.php";
            }
        </script>
    <?php else: ?>
        <script>
            function closeErrorPopup() {
                document.getElementById('popup').style.display = 'none';
            }
        </script>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>
