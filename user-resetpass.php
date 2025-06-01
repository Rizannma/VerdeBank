<?php
session_start();

// Restrict access to user account reset only
if (!isset($_SESSION['reset_account_number']) || $_SESSION['account_type'] !== 'user') {
    header("Location: forgot-password.php");
    exit();
}

$popup = ""; // for error/success message
$popupClass = ""; // for popup style class (success or error)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "verde_bank_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $account_number = $_SESSION['reset_account_number'];
    $new_pin = $_POST['new_pin'];
    $confirm_pin = $_POST['confirm_pin'];

    // Validate PIN format: exactly 4 digits, all numeric
    if (!preg_match('/^\d{4}$/', $new_pin)) {
        $popup = "PIN must be exactly 4 numeric digits.";
        $popupClass = "error-popup";
    } elseif ($new_pin !== $confirm_pin) {
        $popup = "PINs do not match. Please try again.";
        $popupClass = "error-popup";
    } else {
        // Use prepared statement to update the PIN for the account number
        $stmt = $conn->prepare("UPDATE user_accounts SET pin = ? WHERE savings_account_number = ? OR checking_account_number = ?");
        $stmt->bind_param("sss", $new_pin, $account_number, $account_number);

        if ($stmt->execute()) {
            unset($_SESSION['reset_account_number']);
            unset($_SESSION['account_type']);
            $popup = "PIN has been successfully changed. You can now log in.";
            $popupClass = "success-popup"; // Success message style
            $redirect = true;
        } else {
            $popup = "Failed to reset PIN.";
            $popupClass = "error-popup"; // Error message style
        }

        $stmt->close();
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
  <label for="new-pin" class="enter-new-password">Enter New PIN:</label>
  <input type="password" id="new-pin" name="new_pin" class="txt-new-pass" required />

  <label for="confirm-pin" class="confirm-password">Confirm PIN:</label>
  <input type="password" id="confirm-pin" name="confirm_pin" class="txt-confirm-pass" required />

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
                window.location.href = "user_login.php";
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
