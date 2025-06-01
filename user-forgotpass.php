<?php
session_start();
$popup = "";
$isSuccess = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "verde_bank_db");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $account_number = $_POST['account_number'];
    $account_number_no_spaces = str_replace(' ', '', $account_number);

    // Validate account number: must be exactly 16 digits numeric (spaces ignored)
    if (!preg_match('/^\d{16}$/', $account_number_no_spaces)) {
        $popup = "Account number must be exactly 16 digits.";
    } else {
        // Prepare statement to check both savings_account_number and checking_account_number
        $stmt = $conn->prepare("SELECT * FROM user_accounts WHERE REPLACE(savings_account_number, ' ', '') = ? OR REPLACE(checking_account_number, ' ', '') = ?");
        $stmt->bind_param("ss", $account_number_no_spaces, $account_number_no_spaces);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['is_verified'] == 1) {
                $_SESSION['reset_account_number'] = $account_number;
                $_SESSION['account_type'] = 'user';
                $popup = "Account found. Redirecting to reset password...";
                $isSuccess = true;
                $redirect = true;
            } else {
                $popup = "Account not yet verified. You cannot reset the password until your account is approved.";
            }
        } else {
            $popup = "Account number not found.";
        }

        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="user-forgotpass.css">
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
    <div class="forgot-password2">Forgot your PIN</div>
    <div class="line-4"></div>

    <form method="POST" action="">
        <label for="account_number" class="email">Account Number:</label>
        <input type="text" id="account_number" name="account_number" class="txt-email" required />

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
                window.location.href = "user-resetpass.php";
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
