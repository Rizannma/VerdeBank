<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$successMessage = "";
$errorMessages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = new mysqli("localhost", "root", "", "verde_bank_db");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $user_id = intval($_SESSION['user_id']);
    $bank = isset($_POST['bank']) ? trim($_POST['bank']) : '';
    $account_name = trim($_POST['account_name']);
    $account_number = trim($_POST['account_number']);
    $amount = floatval($_POST['amount']);
    $from_account = isset($_POST['from_account']) ? trim($_POST['from_account']) : '';

    // Basic validations
    if (empty($bank)) {
        $errorMessages[] = "Bank is required.";
    }
    if (empty($from_account) || !in_array($from_account, ['savings', 'checking'])) {
        $errorMessages[] = "Please select a valid from account.";
    }
    if ($amount <= 0) {
        $errorMessages[] = "Amount must be positive.";
    }
    if (preg_match('/\d/', $account_name)) {
        $errorMessages[] = "Account name must not contain numbers.";
    }
    if (empty($account_name)) {
        $errorMessages[] = "Account name is required.";
    }
    if (empty($account_number)) {
        $errorMessages[] = "Account number is required.";
    }

    // Prepare field names based on from_account
    $account_name_field = "fullname"; // user full name in user_accounts table
    $account_number_field = $from_account . "_account_number"; // savings_account_number or checking_account_number
    $balance_field = $from_account . "_balance"; // savings_balance or checking_balance

    if (empty($errorMessages)) {
        // Fetch user account info for validation
        $stmt = $conn->prepare("SELECT $account_name_field, $account_number_field, $balance_field FROM user_accounts WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $row = $result->fetch_assoc()) {
            $db_account_name = trim($row[$account_name_field]);
            $db_account_number = trim($row[$account_number_field]);
            $balance = floatval($row[$balance_field]);

            // Remove all spaces before comparing
            $clean_db_account_number = str_replace(' ', '', $db_account_number);
            $clean_input_account_number = str_replace(' ', '', $account_number);

            if ($clean_db_account_number !== $clean_input_account_number) {
                $errorMessages[] = "Account number does not match your $from_account account.";
            }
      
            if ($amount > $balance) {
                $errorMessages[] = "Insufficient $from_account balance.";
            }
        } else {
            $errorMessages[] = "User  account information not found.";
        }

        $stmt->close();
    }

    if (empty($errorMessages)) {
        // Insert transfer record into external_transfers
        $stmt = $conn->prepare("INSERT INTO external_transfers (user_id, bank, account_name, account_number, amount, transfer_date, from_account) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param("isssds", $user_id, $bank, $account_name, $account_number, $amount, $from_account);
        if ($stmt->execute()) {
            // Deduct amount from user's balance
            $update_stmt = $conn->prepare("UPDATE user_accounts SET $balance_field = $balance_field - ? WHERE user_id = ?");
            $update_stmt->bind_param("di", $amount, $user_id);
            $update_stmt->execute();
            $update_stmt->close();

            $successMessage = "Transfer successful!";
        } else {
            $errorMessages[] = "Failed to process transfer. Please try again.";
        }
        $stmt->close();
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Transfer Form</title>
  <link rel="stylesheet" href="user_transfer_form.css" />

  <style>
    /* Styles for error and success messages */
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

    .error-popup {
      background-color: #f2dede;
      border: 1px solid #ebccd1;
      border-radius: 10px;
      padding: 20px 30px;
      text-align: center;
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
      font-family: "InstrumentSans-Regular", sans-serif;
      font-size: 16px;
      color: #a94442;
      margin-left: 85px;
    }

    .error-popup button {
      margin-top: 15px;
      padding: 8px 20px;
      background-color: #a94442;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .error-popup button:hover {
      background-color: #922d2d;
    }

    #pinOverlay {
      display: none; /* Keep modal hidden by default */
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.6);
      z-index: 9999;
      justify-content: center;
      align-items: center;
    }

    #pinModal {
      margin-left: 85px;
      height: 210px;
      background: #3A3838;
      padding: 24px 20px;
      border-radius: 12px;
      width: 320px;
      text-align: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      color: white; /* Make all text inside white by default */
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3); /* minimal subtle shadow */
    }

    #pinModal h3 {
      margin-bottom: 20px;
      font-weight: 600;
      font-size: 1.3rem;
    }

    #pinInput {
      width: 100%;
      padding: 12px 14px;
      border: 1px solid #ffffff;
      border-radius: 8px;
      background: transparent;
      color: white;
      font-size: 1rem;
      outline-color: transparent;
      box-sizing: border-box;
      transition: border-color 0.25s ease, outline-color 0.25s ease;
    }

    #pinInput::placeholder {
      color: #c0c0c0; 
    }

    #pinError {
      color: #FF6B6B; 
      font-size: 0.9rem;
      margin-top: 10px;
      min-height: 20px;
      font-weight: 500;
    }

    #submitPin, #cancelPin {
      width: 120px;
      font-family: "InstrumentSans-Bold", sans-serif;
      border-radius: 16px;
      border: 1px solid #ffffff;
      margin-top: 0;
      padding: 10px 25px;
      border: none;
      border-radius: 20px;
      cursor: pointer;
      font-weight: 500;
      font-size: 1rem;
      transition: background-color 0.3s ease;
      color: white; /* text white on buttons */
    }

    #submitPin {
      font-weight: 600;
      background-color: #4d4a4a;;
      border: 1px solid #ffffff;
    }

    #submitPin:hover {
      background-color: #ffffff;
      color: #3a3838;
    }

    #cancelPin {
      background-color: transparent;
      color: #ffffff;
      border: 1px solid #ffffff;
      font-family: "InstrumentSans-Regular", sans-serif;
      font-size: 15px;
      font-weight: 400;
      cursor: pointer;
      transition: color 0.3s ease;
      margin-left: 15px;
    }

    #cancelPin:hover {
      background-color: #ffffff;
      color: #3a3838;
    }
  </style>
</head>
<body>
  <?php
    // Display error messages in an overlay
    if (!empty($errorMessages)) {
      echo "<div class='error-overlay'><div class='error-popup'>";
      foreach ($errorMessages as $err) {
        echo "<p>$err</p>";
      }
      echo "<button onclick='document.querySelector(\".error-overlay\").style.display=\"none\"'>OK</button>";
      echo "</div></div>";
    }

    // Display success message in an overlay
    if (!empty($successMessage)) {
      echo "<div class='error-overlay'><div class='error-popup' style='background-color: #dff0d8; border-color: #d6e9c6; color: #3c763d;'>";
      echo "<p>$successMessage</p>";
      echo "<button onclick='document.querySelector(\".error-overlay\").style.display=\"none\"'>OK</button>";
      echo "</div></div>";
    }
  ?>

  <form class="transfer-form" method="POST" action="">
    <img class="rectangle-1" src="images/form rectangle.png" />
    <div class="transfer-from-your-account">Transfer from your Account</div>

    <div class="frame-49">
      <div class="biller" id="selectedBiller">Selected Biller</div>
      <input type="text" class="txt-biller" name="bank" readonly />

      <div class="to-account-name">Sender's Account Name</div>
      <input type="text" class="txt-accountname" name="account_name" placeholder="Enter account name" required />

      <div class="to-account-number">Sender's Account Number</div>
      <input type="number" class="txt-accountnumber" name="account_number" placeholder="Enter account number" required/>

      <div class="amount">Transfer Amount</div>
      <input type="number" class="txt-amount" name="amount" placeholder="Enter amount" min="0" step="0.01" required/>

      <div class="from-account-checking-savings">From Account</div>
      <select class="txt-message" name="from_account" id="from-account" required>
        <option value="" disabled selected>Select Account Type</option>
        <option value="savings">Savings</option>
        <option value="checking">Checking</option>
      </select>
    </div>

    <div class="line-4"></div>

    <button class="button" type="submit">
      <span class="confirm">Confirm</span>
    </button>

    <a href="user_transfer.php" class="btn-back">
      <img src="images/arrow-back.png" class="arrow" />
      <span class="txt-back">Back to Transfer</span>
    </a>
  </form>

  <!-- PIN Confirmation Modal -->
  <div id="pinOverlay">
    <div id="pinModal">
      <h3>Enter Transaction PIN</h3>
      <input type="password" id="pinInput" placeholder="Enter your PIN" maxlength="6" />
      <p id="pinError"></p>
      <button id="submitPin">Submit</button>
      <button id="cancelPin" onclick="document.getElementById('pinOverlay').style.display='none'">Cancel</button>
    </div>
  </div>

  <script>
    const form = document.querySelector('.transfer-form');
    let pinValidated = false;

    form.addEventListener('submit', function (e) {
      // Prevent showing the PIN overlay if there are errors
      if (document.querySelectorAll('.error-message').length > 0) {
        e.preventDefault(); // Prevent form submission
        return; // Do not proceed to show PIN overlay
      }

      // Show PIN overlay if no errors
      if (!pinValidated) {
        e.preventDefault();
        document.getElementById('pinOverlay').style.display = 'flex';
      }
    });

    document.getElementById('submitPin').addEventListener('click', function () {
      const pin = document.getElementById('pinInput').value;
      const errorText = document.getElementById('pinError');

      if (!pin) {
        errorText.textContent = 'PIN is required.';
        return;
      }

      fetch('validate_pin.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `pin=${encodeURIComponent(pin)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.valid) {
          pinValidated = true;
          errorText.textContent = '';
          document.getElementById('pinOverlay').style.display = 'none';
          form.submit(); // Submit form again after validation
        } else {
          errorText.textContent = 'Invalid PIN. Please try again.';
        }
      })
      .catch(error => {
        errorText.textContent = 'An error occurred. Please try again.';
      });
    });

    document.getElementById('cancelPin').addEventListener('click', function () {
      document.getElementById('pinInput').value = '';
      document.getElementById('pinError').textContent = '';
      document.getElementById('pinOverlay').style.display = 'none';
    });

    // Fill in selected payment
    const selectedPayment = localStorage.getItem('selectedPayment');
    if (selectedPayment) {
      document.querySelector('.txt-biller').value = selectedPayment;
    }
  </script>

</body>
</html>
