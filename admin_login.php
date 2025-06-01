<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Connect to database
    $conn = new mysqli("localhost", "root", "", "verde_bank_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM admin_accounts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if ($password === $row['password']) {
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = 'admin';
            $_SESSION['success'] = "Login successful! Redirecting...";
            header("Location: login-verification.php");
            exit();
        } else {
            $error = "Admin account is found but the password is incorrect.<br>Check your password and try again.";
        }
    } else {
        $error = "No account found.<br>Check your email and try again.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="admin_login.css">
</head>
<body>
    <div class="admin-login">
        <div class="ellipse-11"></div>
        <div class="ellipse-10"></div>
        <img class="graph-6" src="images/login-logo.png" />
        <img class="rectangle-1" src="images/login-rectangle.png" />
        <div class="login-to-your-admin-account">Login to Your Admin Account</div>
        <div class="line-4"></div>

        <?php if (isset($error)): ?>
        <div class="error-overlay">
            <div class="error-popup">
                <p><?php echo $error; ?></p>
                <button onclick="closeErrorPopup()">OK</button>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input class="email" type="email" name="email" placeholder="Enter email" required />
            <input class="pass" type="password" name="password" placeholder="Enter password" required />
            <div class="txt-email">Email:</div>
            <div class="txt-pass">Password:</div>

            <div class="forgot-pass" onclick="window.location.href='admin-forgotpass.php';">Forgot Password?</div>

            <div class="login2"></div>
            <button class="login3">Login</button>
        </form>

        <div class="btn-back" onclick="window.location.href='login.php';">
            <div class="txt-back">Back to Login</div>
            <img class="arrow" src="images/arrow-back.png" />
        </div>
    </div>

    <script>
        function closeErrorPopup() {
            const popup = document.querySelector('.error-overlay');
            if (popup) popup.style.display = 'none';
        }
    </script>
</body>
</html>
