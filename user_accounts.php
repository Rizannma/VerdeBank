<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

session_start();
$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "verde_bank_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch total_interest_earned
$stmt = $conn->prepare("SELECT fullname, savings_account_number, checking_account_number, savings_balance, checking_balance, last_interest_date, total_interest_earned FROM user_accounts WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    $fullname = $user['fullname'];
    $savings_number = $user['savings_account_number'];
    $checking_number = $user['checking_account_number'];
    $savings_balance = $user['savings_balance'];
    $checking_balance = $user['checking_balance'];
    $last_interest_date = $user['last_interest_date'];
    $total_interest_earned = $user['total_interest_earned'];

    // Calculate months passed
    $lastDate = new DateTime($last_interest_date);
    $now = new DateTime();
    $interval = $lastDate->diff($now);
    $months_passed = ($interval->y * 12) + $interval->m;

    if ($months_passed >= 1) {
        $interestRate = 0.035;
        $original_balance = $savings_balance;

        for ($i = 0; $i < $months_passed; $i++) {
            $savings_balance += $savings_balance * $interestRate;
        }
        $savings_interest = $savings_balance - $original_balance;

        // Add this interest to total_interest_earned
        $total_interest_earned += $savings_interest;

        $new_interest_date = $lastDate->modify("+$months_passed months")->format('Y-m-d');

        $update_stmt = $conn->prepare("UPDATE user_accounts SET savings_balance = ?, last_interest_date = ?, total_interest_earned = ? WHERE user_id = ?");
        $update_stmt->bind_param("dsdi", $savings_balance, $new_interest_date, $total_interest_earned, $user_id);
        $update_stmt->execute();

        // Fetch user's email
$email_stmt = $conn->prepare("SELECT email FROM user_accounts WHERE user_id = ?");
$email_stmt->bind_param("i", $user_id);
$email_stmt->execute();
$email_result = $email_stmt->get_result();
$user_email_data = $email_result->fetch_assoc();
$user_email = $user_email_data['email'];

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; 
    $mail->SMTPAuth = true;
    $mail->Username = 'verdebank.official@gmail.com';
    $mail->Password = 'yngu frcl nqkd drqd';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('verdebank.official@gmail.com', 'Verde Bank');
    $mail->addAddress($user_email, $fullname);

   
    $mail->isHTML(true);
    $mail->Subject = 'Savings Interest Update from VerdeBank';
    $mail->Body    = "
        <h2>Hello $fullname,</h2>
        <p>We're pleased to inform you that you've earned interest on your savings account.</p>
        <p><strong>Account Number:</strong> $savings_number</p>
        <p><strong>Interest Earned:</strong> ₱" . number_format($savings_interest, 2) . "</p>
        <p><strong>Total Interest Accumulated:</strong> ₱" . number_format($total_interest_earned, 2) . "</p>
        <p><strong>Updated Savings Balance:</strong> ₱" . number_format($savings_balance, 2) . "</p>
        <p>Thank you for banking with us.</p>
        <br><p><em>VerdeBank – Your Partner in Growth</em></p>
    ";

    $mail->send();
    $_SESSION['success'] = 'An email has been sent regarding your interest update.';
} catch (Exception $e) {
    $_SESSION['error'] = "Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
}

    } else {
        $savings_interest = 0;
    }
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Dashboard</title>
  <link rel="stylesheet" href="user_accounts.css?v=1.2" />
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans&display=swap" rel="stylesheet">

</head>
<body>
<?php
if (isset($_SESSION['error'])) {
    echo '<div id="popup-message" class="error">'
        . $_SESSION['error'] . '</div>';
    echo "<script>
            setTimeout(function() {
                var msg = document.getElementById('popup-message');
                if (msg) {
                    msg.style.opacity = '0';
                    setTimeout(function() {
                        msg.remove();
                    }, 500);
                }
            }, 3000);
          </script>";
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    echo '<div id="popup-message" class="success">'
        . $_SESSION['success'] . '</div>';
    echo "<script>
            setTimeout(function() {
                var msg = document.getElementById('popup-message');
                if (msg) {
                    msg.style.opacity = '0';
                    setTimeout(function() {
                        msg.remove();
                    }, 500);
                }
            }, 3000);
          </script>";
    unset($_SESSION['success']);
}
?>

  <div class="account-sc">
    <div class="greetings">My Account</div>
    <div class="subheading">
      A savings account enables you to establish financial goals and work toward achieving your larger aspirations. 
      It also provides the convenience of viewing all transactions from checking account and making direct transfers between  savings and checking accounts.
    </div>
    <div class="subheading2">Click the arrow to proceed transfer.</div>

    <!-- SAVINGS CARD -->
<div class="card savings-card" onclick="flipCard(this)">
  <div class="card-inner">
    
    <!-- FRONT SIDE -->
    <div class="card-face card-front">
      <div class="ellipse-1"></div>
      <div class="rectangle-1"></div>
      <div class="savings-account-number"><?php echo $savings_number; ?></div>
      <div class="full-name"><?php echo $fullname; ?></div>
      <div class="savings-account-type">Savings</div>
      <div class="account-number">Account Number</div>
      <img class="graph-6" src="images/logo1.png" />
      <div class="savings-verdebank">VerdeBank</div>
      <img class="toll2" src="images/toll1.png" />
    </div>

    <!-- BACK SIDE -->
<div class="card-face card-back">
  <div class="ellipse-1"></div>
  <div class="rectangle-1"></div>

  <div class="savings-account-number savings-balance-label">
    ₱<?php echo number_format($savings_balance, 2); ?>
  </div>

<div class="full-name savings-balance-amount"> 
    ₱<?php echo number_format($total_interest_earned, 2); ?>
</div>



  <div class="savings-account-type">
    Interest Earned
  </div>

  <div class="account-number">
    Account Balance
  </div>

  <img class="graph-6" src="images/logo1.png" />
  <div class="savings-verdebank">VerdeBank</div>
  <img class="toll2" src="images/toll1.png" />
</div>


  </div>
</div>

<!-- CHECKING CARD -->
<div class="card checking-card" onclick="flipCard(this)">
  <div class="card-inner">

    <!-- FRONT SIDE -->
    <div class="card-face card-front">
      <div class="ellipse-1"></div>
      <div class="rectangle-12"></div>
      <div class="checking-account-number"><?php echo $checking_number; ?></div>
      <div class="full-name2"><?php echo $fullname; ?></div>
      <div class="account-type2">Checking</div>
      <div class="account-number2">Account Number</div>
      <img class="graph-62" src="images/logo2.png" />
      <div class="verde-bank2">VerdeBank</div>
      <img class="toll4" src="images/toll2.png" />
    </div>

    <!-- BACK SIDE -->
    <div class="card-face card-back">
      <div class="ellipse-1"></div>
      <div class="rectangle-12"></div>
      <div class="checking-account-number checking-balance-label">₱<?php echo number_format($checking_balance, 2); ?></div>
      <div class="full-name2 checking-balance-amount">Available Balance</div>
      <div class="account-type2">Checking</div>
      <div class="account-number2">Account Balance</div>
      <img class="graph-62" src="images/logo2.png" />
      <div class="verde-bank2">VerdeBank</div>
      <img class="toll4" src="images/toll2.png" />
    </div>
 </div>
</div>

  </div>
</div>
  </div>
</div>

<script>
function flipCard(card) {
  card.classList.toggle("flip");
}

</script>

    <!-- Buttons -->
    <a href="user_goals.php" class="btn-goals">
      <div class="view-goals">View Goals</div>
    </a>

    <a href="user_transactions.php" class="btn-transactions">
      <div class="view-transactions">View Transactions</div>
    </a>

 <!-- Transfer Button -->
<button class="btn-transfer" onclick="showTransferPopup()">
  <img src="images/btn-transfer.png" alt="Transfer" />
</button>

<!-- Transfer Popup -->
<div id="overlayTransfer" class="overlay" style="display:none;">
  <form method="POST" action="internal_transfer.php" class="internal-transfer">
    <div class="internal-transfer2">Internal Transfer</div>
    
    <label class="sender-account" for="sender">Sender Account:</label>
    <select class="sender" id="sender" name="sender">
      <option value="" disabled selected>Select account</option>
      <option value="Savings">Savings</option>
      <option value="Checking">Checking</option>
    </select>
    
    <label class="receiver-account" for="receiver">Receiver Account:</label>
    <select class="receiver" id="receiver" name="receiver">
      <option value="" disabled selected>Select account</option>
      <option value="Savings">Savings</option>
      <option value="Checking">Checking</option>
    </select>
    
    <label class="amount" for="amount">Amount:</label>
    <input class="amount2" id="amount" name="amount" placeholder="₱0.00" type="number" min="0" step="0.01" required />

    <div class="pin-instruction">
      Please confirm with your 4-digit transaction PIN to proceed.
    </div>
    <input type="password" maxlength="4" class="pin-input" id="transactionPin" name="pin" placeholder="Enter 4-digit PIN" required />
    
    <div class="btn-cancel" type="button" onclick="closeTransferPopup()">
      <div class="cancel">Cancel</div>
    </div>
    <button type="submit" class="btn-confirm">
      <div class="confirm">Transfer</div>
    </button>
    <div class="line"></div>
  </form>
</div>

<script>
  function showTransferPopup() {
    document.getElementById("overlayTransfer").style.display = "flex";
  }
  function closeTransferPopup() {
    document.getElementById("overlayTransfer").style.display = "none";
  }
</script>

        <div class="sidebar">
            <div class="logo">
                <a href="user_dashboard.php">
                    <img class="verde-icon"  src="images/verde icon.png" />
                    <div class="verde-bank">VerdeBank</div>
                </a>
            </div>

            <img class="active" src="images/active.png" />

            <a href="user_dashboard.php" class="sidebar-link">
                <img class="img-dashboard" src="images/img-dashboard.png" />
                <div class="txt-dashboard">Dashboard</div>
            </a>

            <a href="user_pay_bills.php" class="sidebar-link">
                <div class="txt-bills">Pay Bills</div>
            </a>

            <a href="user_transfer.php" class="sidebar-link">
                <img class="img-transfer" src="images/img-transfer.png" />
                <div class="txt-transfer">Transfer</div>
            </a>

            <a href="user_deposit.php" class="sidebar-link">
                <img class="img-deposit" src="images/img-deposit.png" />
                <div class="txt-deposit">Deposit</div>
            </a>

            <a href="user_accounts.php" class="sidebar-link">
                <div class="txt-cards">Account</div>
            </a>

            <img class="img-bills" src="images/img-bills.png" />
            <img class="line-1" src="images/line1.png" />
            <img class="img-cards" src="images/img-acc.png" />
          
        </div>

  </div>
</body>
</html>
