<?php
session_start();
$popup = "";
$isSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "verde_bank_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = $_POST['email'];

    // Only check admin_accounts 
    $user_sql = "SELECT * FROM admin_accounts WHERE email = '$email'";
    $user_result = $conn->query($user_sql);

    if ($user_result->num_rows === 1) {
        $_SESSION['reset_email'] = $email;
        $_SESSION['account_type'] = 'admin';
        $popup = "Admin found. Redirecting to reset password...";
        $isSuccess = true;
        $redirect = true;
    } else {
        $popup = "Email not found in admin accounts.";
    }

    $conn->close();
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="admin-forgotpass.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .error-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .error-popup, .success-popup {
            border-radius: 10px;
            padding: 20px 30px;
            text-align: center;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            font-family: "InstrumentSans-Regular", sans-serif;
            font-size: 16px;
            margin-left: 85px;
        }

        .error-popup {
            background-color: #f2dede;
            border: 1px solid #ebccd1;
            color: #a94442;
        }

        .success-popup {
            background-color: #dff0d8;
            border: 1px solid #d6e9c6;
            color: #3c763d;
        }

        .error-popup button,
        .success-popup button {
            margin-top: 15px;
            padding: 8px 20px;
            background-color: inherit;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }

        .error-popup button {
            background-color: #a94442;
        }

        .error-popup button:hover {
            background-color: #922d2d;
        }

        .success-popup button {
            background-color: #3c763d;
        }

        .success-popup button:hover {
            background-color: #2e5d2e;
        }
    </style>
</head>
<body>
<div class="forgot-password">
    <img class="rectangle" src="images/login-rectangle.png" />
    <div class="forgot-password2">Forgot Password</div>
    <div class="line-4"></div>

    <form method="POST" action="">
        <label for="email" class="email">Email:</label>
        <input type="email" id="email" name="email" class="txt-email" required />

        <div class="btn-next">
            <button class="next">Next</button>
        </div>
    </form>

    <div class="btn-back" onclick="window.location.href='login.php';">
        <img class="arrow" src="images/arrow-back.png" />
        <div class="txt-back">Back to Login</div>
    </div>   
</div>

<?php if (!empty($popup)): ?>
    <div class="error-overlay" id="popup">
        <div class="<?php echo $isSuccess ? 'success-popup' : 'error-popup'; ?>">
            <p><?php echo $popup; ?></p>
            <button onclick="closeErrorPopup()">OK</button>
        </div>
    </div>

    <?php if (isset($redirect) && $redirect): ?>
        <script>
            function closeErrorPopup() {
                document.getElementById('popup').style.display = 'none';
                window.location.href = "admin-resetpass.php";
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
