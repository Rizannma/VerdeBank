<?php
$message = '';
$messageClass = '';
$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "verde_bank_db");

    if ($conn->connect_error) {
        $message = "Connection failed: " . $conn->connect_error;
        $messageClass = "error";
    } else {
        $fullname = trim($_POST['fullname']);
        $birthday = $_POST['birthday'];
        $address = trim($_POST['address']);
        $email = trim($_POST['email']);
        $PIN = $_POST['pass'];
        $valid_id = $_FILES['valid_id']['name'];
        $is_verified = 0;

        // Keep old values safely for redisplay
        $old = [
            'fullname' => htmlspecialchars($fullname),
            'birthday' => $birthday,
            'address' => htmlspecialchars($address),
            'email' => htmlspecialchars($email),
            'pass' => htmlspecialchars($PIN),
        ];

        // Validate email uniqueness
        $checkEmail = $conn->prepare("SELECT email FROM user_accounts WHERE email = ?");
        $checkEmail->bind_param("s", $email);
        $checkEmail->execute();
        $checkEmail->store_result();

        if ($checkEmail->num_rows > 0) {
            $errors['email'] = "Email already exists.";
        }
        // Validate birthday (must be 18+)
        elseif ((date('Y') - date('Y', strtotime($birthday))) < 18) {
            $errors['birthday'] = "You must be at least 18 years old to register.";
        }
        // Validate 4-digit PIN
        elseif (!preg_match('/^\d{4}$/', $PIN)) {
            $errors['pass'] = "PIN must be exactly 4 digits.";
        }
        elseif (!isset($_FILES['valid_id']) || $_FILES['valid_id']['error'] !== UPLOAD_ERR_OK) {
            $errors['valid_id'] = "Please upload a valid ID file.";
        }
        else {
            // Save uploaded ID
            $target_dir = "Uploads/";
            $target_file = $target_dir . basename($_FILES["valid_id"]["name"]);

            if (move_uploaded_file($_FILES["valid_id"]["tmp_name"], $target_file)) {
                $sql = $conn->prepare("INSERT INTO user_accounts (fullname, birthday, address, email, PIN, valid_id, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
                $sql->bind_param("ssssss", $fullname, $birthday, $address, $email, $PIN, $valid_id);

                if ($sql->execute()) {
                    $message = "Account submitted for verification. You will be notified once approved.";
                    $messageClass = "success";
                    $old = []; // Clear old inputs on success
                } else {
                    $message = "Error submitting account: " . $conn->error;
                    $messageClass = "error";
                }
            } else {
                $errors['valid_id'] = "Failed to upload valid ID.";
            }
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Create Account - Verde</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="create-account.css" />
    <style>
        /* Inline CSS for error highlights and messages */
        .input-error {
            border: 2px solid #f44336;
            background-color: #ffe6e6;
        }
        .error-message {
            color: #f44336;
            font-size: 0.85em;
            margin-top: 4px;
            display: block;
        }
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="page-container">
        <header class="page-header">
            <h2>Create Your Bank Account</h2>
            <hr />
        </header>

        <div class="form-container">

            <?php if ($message): ?>
                <div class="message <?php echo $messageClass; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form id="create-account-form" action="#" method="post" enctype="multipart/form-data">
                <div class="btn-back" onclick="window.location.href='user_login.php';">
                    <img class="arrow" src="images/arrow-back.png" alt="<" />
                    <div class="txt-back">Back to Login</div>
                </div>

                <div class="form-group">
                    <label for="fullname">Name</label>
                    <input
                        type="text"
                        id="fullname"
                        name="fullname"
                        placeholder="Enter your full name."
                        required
                        value="<?php echo $old['fullname'] ?? ''; ?>"
                        class="<?php echo isset($errors['fullname']) ? 'input-error' : ''; ?>"
                    />
                    <?php if (isset($errors['fullname'])): ?>
                        <small class="error-message"><?php echo $errors['fullname']; ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="birthday">Date of Birth</label>
                    <input
                        type="date"
                        id="birthday"
                        name="birthday"
                        required
                        value="<?php echo $old['birthday'] ?? ''; ?>"
                        class="<?php echo isset($errors['birthday']) ? 'input-error' : ''; ?>"
                    />
                    <?php if (isset($errors['birthday'])): ?>
                        <small class="error-message"><?php echo $errors['birthday']; ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea
                        id="address"
                        name="address"
                        rows="3"
                        placeholder="Enter your complete address."
                        required
                        class="<?php echo isset($errors['address']) ? 'input-error' : ''; ?>"
                    ><?php echo $old['address'] ?? ''; ?></textarea>
                    <?php if (isset($errors['address'])): ?>
                        <small class="error-message"><?php echo $errors['address']; ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email."
                        required
                        value="<?php echo $old['email'] ?? ''; ?>"
                        class="<?php echo isset($errors['email']) ? 'input-error' : ''; ?>"
                    />
                    <?php if (isset($errors['email'])): ?>
                        <small class="error-message"><?php echo $errors['email']; ?></small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="pass">PIN</label>
                    <input
                        type="pass"
                        id="pass"
                        name="pass"
                        placeholder="Enter your PIN."
                        pattern="\d{4}"
                        title="PIN must be 4 digits"
                        required
                        value="<?php echo $old['pass'] ?? ''; ?>"
                        class="<?php echo isset($errors['pass']) ? 'input-error' : ''; ?>"
                    />
                    <?php if (isset($errors['pass'])): ?>
                        <small class="error-message"><?php echo $errors['pass']; ?></small>
                    <?php endif; ?>
                    <p class="file-note">
                        This PIN will be used for login and transactions once your account is approved.
                    </p>
                </div>

                <div class="form-group">
                    <label for="valid_id">Upload Valid ID</label>
                    <input
                        type="file"
                        id="valid_id"
                        name="valid_id"
                        accept="image/jpeg, image/png, application/pdf"
                        required
                        class="<?php echo isset($errors['valid_id']) ? 'input-error' : ''; ?>"
                    />
                    <?php if (isset($errors['valid_id'])): ?>
                        <small class="error-message"><?php echo $errors['valid_id']; ?></small>
                    <?php endif; ?>
                    <p class="file-note">
                        Upload a clear photo of your ID (e.g., National ID, Passport, Driver's License). PDF, JPG, PNG accepted.
                    </p>
                </div>

                <div class="form-group">
                    <button type="submit" class="submit-btn">Submit for Verification</button>
                </div>

                <div class="form-group">
                    <p class="verification-info">
                        The details and ID will be submitted for verification by Verde Administration before creation.
                    </p>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
