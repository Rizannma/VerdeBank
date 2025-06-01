<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "verde_bank_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$transactions = [];

// Deposits
$sql = "SELECT 
            deposit_date AS trans_date, 
            'Deposit' AS trans_type,
            amount,
            CONCAT('Deposit to ', credit_to, ' account') AS description
        FROM deposits 
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

// External transfers
$sql = "SELECT 
            transfer_date AS trans_date,
            'External Transfer' AS trans_type,
            amount,
            CONCAT('To ', bank, ' - ', account_name, ' (', account_number, ') from ', from_account) AS description
        FROM external_transfers
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

// Internal transfers
$sql = "SELECT 
            transfer_date AS trans_date,
            'Internal Transfer' AS trans_type,
            amount,
            CONCAT('From ', from_account, ' to ', to_account) AS description
        FROM internal_transfers
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

// Payments
$sql = "SELECT 
            payment_date AS trans_date,
            'Payment' AS trans_type,
            amount,
            CONCAT('Payment to ', biller) AS description
        FROM payments
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

$conn->close();

// Sort by trans_date descending
usort($transactions, function($a, $b) {
    return strtotime($b['trans_date']) - strtotime($a['trans_date']);
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Transfer</title>
<link rel="stylesheet" href="user_transactions.css?v=1.0" />

</head>
<body>
<div class="transactions">
  <div class="title">Transaction History</div>
  <div class="btn-back">
    <div class="txt-back" onclick="location.href='user_accounts.php'">Back to Account</div>
    <img class="arrow" src="images/arrow-back.png" />
  </div>
  <div class="box">
<div class="filter-section">
  <input class="txtbox"type="text" id="filterInput" placeholder="Filter transactions..." />
  <button onclick="filterTransactions()">Filter</button>
</div>


<?php if (empty($transactions)): ?>
  <div class="no-transactions">No transactions found.</div>
<?php else: ?>
  <table id="transactionsTable">
    <thead>
      <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Amount (₱)</th>
        <th>Description</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($transactions as $trans): ?>
        <tr>
          <td><?= htmlspecialchars(date("Y-m-d H:i", strtotime($trans['trans_date']))) ?></td>
          <td><?= htmlspecialchars($trans['trans_type']) ?></td>
          <td class="amount"><?= number_format($trans['amount'], 2) ?></td>
          <td><?= htmlspecialchars($trans['description']) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

  </div>
  <img class="design" src="images/design.png" />
</div>
<script>
function filterTransactions() {
  const inputElem = document.getElementById('filterInput');
  const input = inputElem.value.toLowerCase();
  const button = inputElem.nextElementSibling; // the filter/unfilter button
  const table = document.getElementById('transactionsTable');
  const trs = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

  if (button.textContent === "Filter") {
    if (!input) {
      alert('Please enter a filter term');
      return;
    }

    // Apply filter and highlight matches
    for (let row of trs) {
      let matched = false;

      // Loop through each cell and highlight matches
      for (let i = 0; i < row.cells.length; i++) {
        const cell = row.cells[i];
        const text = cell.textContent;
        const lowerText = text.toLowerCase();

        if (lowerText.includes(input)) {
          matched = true;

          // Highlight the matched part
          const regex = new RegExp(`(${input.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&')})`, 'gi');
          cell.innerHTML = text.replace(regex, '<span class="highlight">$1</span>');
        } else {
          // Remove any existing highlights if no match here
          cell.innerHTML = text;
        }
      }

      // Show or hide row depending on match
      row.style.display = matched ? '' : 'none';
    }

    // Change button text and disable input
    button.textContent = "Unfilter";
    inputElem.disabled = true;

  } else {
    // Unfilter: show all rows, clear highlights, enable input
    for (let row of trs) {
      row.style.display = '';
      for (let i = 0; i < row.cells.length; i++) {
        const cell = row.cells[i];
        // Remove highlights by resetting innerHTML to plain text
        cell.innerHTML = cell.textContent;
      }
    }

    inputElem.value = '';
    inputElem.disabled = false;
    button.textContent = "Filter";
  }
}
</script>

</body>
</html>
