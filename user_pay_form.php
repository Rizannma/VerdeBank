<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction_pin'])) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "verde_bank_db";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("<p style='color:red'>Connection failed: " . $conn->connect_error . "</p>");
    }

    $user_id = intval($_SESSION['user_id']);
    $biller = $conn->real_escape_string($_POST['biller']);
    $account_name = $conn->real_escape_string($_POST['account_name']);
    $amount = floatval($_POST['amount']);
    $fee = floatval($_POST['fee']);
    $total_amount = $amount + $fee;
    $entered_pin = $_POST['transaction_pin'] ?? '';
    $errors = [];

    if ($amount <= 0) {
        $errors[] = "Amount must be a positive number.";
    }

    if (preg_match('/\d/', $account_name)) {
        $errors[] = "Account name should not contain numbers.";
    }

    $pin_sql = "SELECT PIN FROM user_accounts WHERE user_id = $user_id";
    $pin_result = $conn->query($pin_sql);

    if ($pin_result && $pin_result->num_rows > 0) {
        $pin_row = $pin_result->fetch_assoc();
        $stored_pin = $pin_row['PIN'];

        if ($entered_pin !== $stored_pin) {
            $errors[] = "Incorrect transaction PIN.";
        }
    } else {
        $errors[] = "PIN validation failed.";
    }

    $balance_sql = "SELECT checking_balance FROM user_accounts WHERE user_id = $user_id";
    $balance_result = $conn->query($balance_sql);

    if ($balance_result && $balance_result->num_rows > 0) {
        $row = $balance_result->fetch_assoc();
        $current_balance = floatval($row['checking_balance']);

        if ($total_amount > $current_balance) {
            $errors[] = "Insufficient checking account balance.";
        }
    } else {
        $errors[] = "User account not found.";
    }

    if (!empty($errors)) {
        $all_errors = implode("\\n", $errors);
        echo "<script>alert('{$all_errors}'); window.history.back();</script>";
        exit();
    }

    $insert_sql = "INSERT INTO payments (user_id, biller, account_name, amount, payment_date)
                   VALUES ($user_id, '$biller', '$account_name', $total_amount, NOW())";

    $update_sql = "UPDATE user_accounts 
                   SET checking_balance = checking_balance - $total_amount 
                   WHERE user_id = $user_id";

    if ($conn->query($insert_sql) === TRUE && $conn->query($update_sql) === TRUE) {
        echo "<script>alert('Payment successful!'); window.location.href='user_dashboard.php';</script>";
    } else {
        echo "<p style='color:red'>Error: " . $conn->error . "</p>";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Payment Bill Form</title>
  <link rel="stylesheet" href="user_pay_form.css" />
</head>

<body>
  <div class="paybills-form">
    <img class="rectangle-1" src="images/form rectangle.png" />
    <div class="pay-your-bills">Payment Bill Form</div>

    <form id="mainForm" class="frame-49">
      <div class="to-account-number" id="selectedBiller">Selected Biller</div>
      <input type="text" class="txt-accountnumber" name="biller" readonly />

      <div class="to-account-name">From Account Name</div>
      <input type="text" class="txt-accountname" placeholder="Enter account name" name="account_name" required/>

      <div class="amount">Amount</div>
      <input type="number" class="txt-amount" placeholder="Enter amount" name="amount" required/>
      <input type="hidden" name="fee" class="txt-fee-hidden" />
    </form>

    <div class="line-4"></div>
    <a href="user_pay_bills.php" class="btn-back">
      <img src="images/arrow-back.png" class="arrow" />
      <span class="txt-back">Back to Pay Bills</span>
    </a>

    <button class="button" id="confirmBtn">
      <span class="confirm">Confirm</span>
    </button>
  </div>

  <!-- 🔒 Modal for PIN confirmation -->
  <div class="overlay" style="display:none;">
    <div class="paybills-confirmation">
      <div class="payment-confirmation">Payment Confirmation</div>
      <div class="you-are-about-to-pay-amount-with-a-fee-of-0-to-biller-this-will-be-deducted-to-your-checking-account" id="confirmationText">
        <!-- dynamic content -->
      </div>
      <div class="please-confirm-with-your-4-digit-transaction-pin-to-proceed">
        Please confirm with your 4-digit transaction PIN to proceed.
      </div>
      <form method="POST" id="pinForm">
        <div class='frame-39'>
          <input type='password' name='transaction_pin' maxlength='4' pattern='[0-9]{4}' required 
                class='transaction-pin' placeholder='Enter 4-digit PIN'/>
        </div>
        <input type="hidden" name="biller" />
        <input type="hidden" name="account_name" />
        <input type="hidden" name="amount" />
        <input type="hidden" name="fee" />

        <div class="frame-40"><div class="cancel">Cancel</div></div>
        <button class="frame-41"class="bill-confirm">Confirm</button></>
        <div class="line-7"></div>
      </form>
    </div>
  </div>

  <style>
/* Overlay Modal */
.overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(26, 26, 26, 0.8);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

/* Modal Container */
.paybills-confirmation {
  width: 360px;
  max-width: 90%;
  position: relative;
  padding: 20px;
  background: #3a3838;
  border-radius: 12px;
  height: 360px;
  box-sizing: border-box;
  overflow: hidden;
  margin-top: 150px;
  margin-left: 535px;
}
.payment-confirmation {
  color: #ffffff;
  text-align: left;
  font-family: "InstrumentSans-Bold", sans-serif;
  font-size: 16px;
  font-weight: 700;
  position: absolute;
  left: 90px;
  top: 28px;
}

.you-are-about-to-pay-amount-with-a-fee-of-0-to-biller-this-will-be-deducted-to-your-checking-account {
  color: #ffffff;
  text-align: justify;
  font-family: "InstrumentSans-Regular", sans-serif;
  font-size: 14px;
  font-weight: 400;
  position: absolute;
  left: 30px;
  top: 80px;
  width: 300px;
}

.please-confirm-with-your-4-digit-transaction-pin-to-proceed {
  color: #ffffff;
  text-align: left;
  font-family: "InstrumentSans-Regular", sans-serif;
  font-size: 14px;
  font-weight: 400;
  position: absolute;
  left: 30px;
  top: 155px;
  width: 300px;
}

.frame-39 {
  border-radius: 8px;
  border: 1px solid #ffffff;
  width: 300px;
  height: 38px;
  position: absolute;
  left: 30px;
  top: 200px;
  overflow: hidden;
}
.transaction-pin {
  width: 100%;
  height: 100%;
  border: none;
  padding: 6px 12px;
  font-size: 14px;
  background-color: transparent;
  color: white;
  outline: none;
  box-sizing: border-box; 
}

.transaction-pin::placeholder {
  color: #aaa;
}


.frame-40, .frame-41 {
  font-family: "InstrumentSans-Bold", sans-serif;
  border-radius: 16px;
  border: 1px solid #ffffff;
  height: 36px;
  position: absolute;
  top: 275px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.frame-40 {
  width: 120px;
  left: 32px;
  background-color: transparent;
}

.cancel {
  color: #ffffff;
  font-family: "InstrumentSans-Regular", sans-serif;
  font-size: 15px;
  font-weight: 400;
  cursor: pointer;
  transition: color 0.3s ease;
}

.frame-40:hover {
  background-color: #ffffff;
}

.frame-40:hover .cancel {
  color: #3a3838;
}

.frame-41 {
  color: #ffffff;
  font-family: "InstrumentSans-Bold", sans-serif;
  font-size: 15px;
  font-weight: 700;
  text-align: center;
  cursor: pointer;
  transition: color 0.3s ease;
  width: 130px;
  left: 200px;
  background-color: #4d4a4a;
}

.frame-41:hover{
  background-color: #ffffff;
  color: #3a3838;
}


.line-7 {
  margin-top: -1px;
  border-top: 1px solid #bfbbbb;
  width: 85%;
  height: 0;
  position: absolute;
  left: 27px;
  top: 65px;
}
  </style>

<script>
  const fees = {
    "PLDT": 15,
    "Globe": 5,
    "Converge": 10
  };

  const selectedBiller = localStorage.getItem('selectedBiller');
  if (selectedBiller) {
    document.querySelector('.txt-accountnumber').value = selectedBiller;
    const fee = fees[selectedBiller] || 0;
    document.querySelector('.txt-fee-hidden').value = fee;
  }

  // Confirm button click -> show modal with data
  document.getElementById('confirmBtn').addEventListener('click', function () {
    const form = document.getElementById('mainForm');
    const biller = form.biller.value;
    const name = form.account_name.value;
    const amount = parseFloat(form.amount.value);
    const fee = parseFloat(form.fee.value || 0);
    const total = (amount + fee).toFixed(2);

    if (!biller || !name || !amount) {
      alert("Please complete the form.");
      return;
    }

    document.querySelector('#confirmationText').innerHTML = `
      <span>
        <span>You are about to pay </span>
        <span class='highlight'>₱${amount.toFixed(2)}</span>
        <span> with a fee of </span>
        <span class='highlight'>₱${fee.toFixed(2)}</span>
        <span> to </span>
        <span class='highlight'>${biller}</span>.
        <span> This will be deducted to your checking account.</span>
      </span>
    `;

    // Populate hidden fields in modal form
    const modalForm = document.getElementById('pinForm');
    modalForm.biller.value = biller;
    modalForm.account_name.value = name;
    modalForm.amount.value = amount;
    modalForm.fee.value = fee;

    document.querySelector('.overlay').style.display = 'block';
  });

  // Hide modal
  document.querySelector('.cancel').addEventListener('click', () => {
    document.querySelector('.overlay').style.display = 'none';
  });
</script>

</body>
</html>
