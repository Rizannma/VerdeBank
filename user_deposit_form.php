<?php
session_start();

$successMessage = "";
$errorMessages = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION['user_id'])) {
        $errorMessages[] = 'You must be logged in to make a deposit.';
    } else {
        $userId = $_SESSION['user_id'];

        $accountName = $_POST['account_name'] ?? null;
        $accountNumber = $_POST['account_number'] ?? null;
        $depositAmount = (float) ($_POST['amount'] ?? 0);
        $creditTo = strtolower(trim($_POST['credit_to'] ?? ''));
        $paymentMethod = $_POST['payment_method'] ?? null;
        $depositDate = date("Y-m-d H:i:s");

        // Validate inputs (paymentMethod optional)
        if (!$accountName || !$accountNumber || !$depositAmount || !$creditTo) {
            $errorMessages[] = "Please fill in all required fields except payment method.";
        }

        $minimumDeposit = 100;
        if ($depositAmount < $minimumDeposit) {
            $errorMessages[] = "Minimum deposit amount is ₱$minimumDeposit.";
        }

        if (count($errorMessages) === 0) {
            // Connect to DB
            $conn = new mysqli("localhost", "root", "", "verde_bank_db");
            if ($conn->connect_error) {
                $errorMessages[] = 'Connection failed: ' . $conn->connect_error;
            } else {
                // Check if user exists in accounts
                $userAccountStmt = $conn->prepare("SELECT savings_balance, checking_balance FROM user_accounts WHERE user_id = ?");
                $userAccountStmt->bind_param("i", $userId);
                $userAccountStmt->execute();
                $userAccountResult = $userAccountStmt->get_result();

                if ($userAccountResult->num_rows > 0) {
                    // Insert deposit record
                    $stmt = $conn->prepare("
                        INSERT INTO deposits 
                        (account_number, account_name, amount, credit_to, deposit_date, payment_method, user_id) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param("ssdsssi", $accountNumber, $accountName, $depositAmount, $creditTo, $depositDate, $paymentMethod, $userId);

                    if ($stmt->execute()) {
                        // Update balances
                        if ($creditTo === 'savings') {
                            $updateStmt = $conn->prepare("UPDATE user_accounts SET savings_balance = savings_balance + ? WHERE user_id = ?");
                            $updateStmt->bind_param("di", $depositAmount, $userId);
                        } elseif ($creditTo === 'checking') {
                            $updateStmt = $conn->prepare("UPDATE user_accounts SET checking_balance = checking_balance + ? WHERE user_id = ?");
                            $updateStmt->bind_param("di", $depositAmount, $userId);
                        }

                        if (isset($updateStmt) && $updateStmt->execute()) {
                            $successMessage = "Deposit recorded and balance updated.";
                        } else {
                            $errorMessages[] = "Error updating balance.";
                        }
                    } else {
                        $errorMessages[] = "Error inserting deposit";
                    }
                    $stmt->close();
                } else {
                    $errorMessages[] = "Logged-in user not found.";
                }
                $userAccountStmt->close();
                $conn->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Deposit Form</title>
  <link rel="stylesheet" href="user_deposit_form.css" />
</head>

<body>
  <div class="paybills-form">
    <img class="rectangle-1" src="images/form rectangle.png" />
    <div class="pay-your-bills">Deposit to your Account</div>

    <form method="POST" action="user_deposit_form.php" id="depositForm"> 
      <div class="frame-49">
        <div class="label-paymentmethod" id="selectedPaymentMethod">Selected Payment Method</div>
        <input type="text" class="txt-paymentmethod" name="payment_method" readonly value="<?= htmlspecialchars($_POST['payment_method'] ?? '') ?>" >

        <div class="to-account-number">Sender's Account Number:</div>
        <input type="number" class="txt-accountnumber" placeholder="Enter account number" name="account_number" required value="<?= htmlspecialchars($_POST['account_number'] ?? '') ?>" />

        <div class="to-account-name">Sender's Account Name:</div>
        <input type="text" class="txt-accountname" placeholder="Enter account name" name="account_name" required value="<?= htmlspecialchars($_POST['account_name'] ?? '') ?>" />

        <div class="amount">Deposit Amount</div>
        <input type="number" class="txt-amount" placeholder="Enter amount" name="amount" required value="<?= htmlspecialchars($_POST['amount'] ?? '') ?>" />

        <div class="credit-to-label">Credit to Account</div>
        <select class="txt-creditto" name="credit_to" required>
          <option value="" disabled <?= empty($_POST['credit_to']) ? 'selected' : '' ?>>Select account</option>
          <option value="savings" <?= (($_POST['credit_to'] ?? '') === 'savings') ? 'selected' : '' ?>>Savings</option>
          <option value="checking" <?= (($_POST['credit_to'] ?? '') === 'checking') ? 'selected' : '' ?>>Checking</option>
        </select>
      </div>

      <button type="button" class="button" id="confirmButton">
        <span class="confirm">Confirm</span>
      </button>
    </form>

    <div class="line-4"></div>
    <a href="user_deposit.php" class="btn-back">
      <img src="images/arrow-back.png" class="arrow" />
      <span class="txt-back">Back to Deposit</span>
    </a>
  </div>

<!-- Error Message Overlay -->
<?php if (count($errorMessages) > 0): ?>
<div class="error-overlay" id="errorOverlay" style="display:flex;">
    <div class="error-popup">
        <div>
            <?php foreach ($errorMessages as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
        <button onclick="document.getElementById('errorOverlay').style.display='none'">OK</button>
    </div>
</div>
<?php endif; ?>

<!-- Success Message Overlay -->
<?php if (!empty($successMessage)): ?>
<div class="success-overlay" id="successOverlay" style="display:flex;">
    <div class="success-popup">
        <p><?= htmlspecialchars($successMessage) ?></p>
        <button onclick="document.getElementById('successOverlay').style.display='none'">OK</button>
    </div>
</div>
<?php endif; ?>



  <!-- PIN Confirmation Modal -->
  <div id="pinOverlay" style="display: none;">
    <div id="pinModal">
      <h3>Enter Transaction PIN</h3>
      <input type="password" id="pinInput" placeholder="Enter your PIN" maxlength="4" />
      <p id="pinError"></p>
      <button id="submitPin">Submit</button>
      <button id="cancelPin" onclick="document.getElementById('pinOverlay').style.display='none'">Cancel</button>
    </div>
  </div>

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

.error-popup {
    background-color: #f2dede; /* Light red background for error */
    border: 1px solid #ebccd1; /* Border color for error */
    border-radius: 10px;
    padding: 20px 30px;
    text-align: center;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    font-family: "InstrumentSans-Regular", sans-serif;
    font-size: 16px;
    color: #a94442; /* Text color for error */
    margin-left: 85px;
}

.error-popup button {
    margin-top: 15px;
    padding: 8px 20px;
    background-color: #a94442; /* Dark red for error button */
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.error-popup button:hover {
    background-color: #922d2d; /* Darker red on hover */
}

/* Success Overlay Styles */
.success-overlay {
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

.success-popup {
    background-color: #dff0d8;
    border: 1px solid #d6e9c6;
    border-radius: 10px;
    padding: 20px 30px;
    text-align: center;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
    font-family: "InstrumentSans-Regular", sans-serif;
    font-size: 16px;
    color: #3c763d; /* Text color for success */
    margin-left: 85px;
}

.success-popup button {
    margin-top: 15px;
    padding: 8px 20px;
    background-color: #5cb85c; /* Green color for success button */
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.success-popup button:hover {
    background-color: #4cae4c; /* Darker green on hover */
}


    #pinOverlay {
      display: none; /* Initially hidden */
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.6);
      z-index: 9999;
      display: flex;
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

<script>
  const confirmBtn = document.getElementById('confirmButton');
  const form = document.querySelector('#depositForm');
  let pinValidated = false;

  // Prefill selected payment method
  const selectedPayment = localStorage.getItem('selectedPayment');
  if (selectedPayment) {
    document.querySelector('.txt-paymentmethod').value = selectedPayment;
  }

  confirmBtn.addEventListener('click', function (e) {
    e.preventDefault();
    if (!pinValidated) {
      document.getElementById('pinOverlay').style.display = 'flex';
    } else {
      form.submit();
    }
  });

  document.getElementById('submitPin').addEventListener('click', function () {
    const pin = document.getElementById('pinInput').value.trim();
    const errorText = document.getElementById('pinError');

    if (pin.length !== 4 || isNaN(pin)) {
      errorText.textContent = 'PIN must be 4 digits.';
      return;
    }

    errorText.textContent = '';

    fetch('validate_deposit_pin.php', { 
      method: 'POST',
      headers: {
          'Content-Type': 'application/json'
      },
      body: JSON.stringify({ pin: pin })
    })
    .then(response => response.json())
    .then(data => {
      if (data.valid) {
        pinValidated = true;
        document.getElementById('pinOverlay').style.display = 'none';
        form.submit();
      } else {
        errorText.textContent = data.message || 'Invalid PIN. Please try again.';
      }
    });
  });

  document.getElementById('cancelPin').addEventListener('click', function () {
    document.getElementById('pinInput').value = '';
    document.getElementById('pinError').textContent = '';
    document.getElementById('pinOverlay').style.display = 'none';
  });
</script>

</body>
</html>

