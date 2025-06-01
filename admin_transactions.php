<?php
include 'db_connection.php'; // adjust this to your DB connection file

// Count total transactions
$totalPayments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM payments"))['count'];
$totalDeposits = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM deposits"))['count'];
$totalInternal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM internal_transfers"))['count'];
$totalExternal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM external_transfers"))['count'];

$totalTransactions = $totalPayments + $totalDeposits + $totalInternal + $totalExternal;
$totalTransferDeposit = $totalDeposits + $totalInternal + $totalExternal;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Transaction Management</title>
  <link rel="stylesheet" href="admin_transactions.css" />
</head>

<body>
<div class="transaction">
  <div class="total-transac">
    <div class="total-transac-num"><?php echo $totalTransactions; ?></div>
    <div class="total-transactions">Total Transactions</div>
    <div class="rectangle"></div>
  </div>

  <div class="transfer-deposit">
    <div class="transfer-deposit2"><?php echo $totalTransferDeposit; ?></div>
    <div class="transfer-deposit3">Transfer & Deposit</div>
    <div class="rectangle2"></div>
  </div>
</div>

    <img class="design" src="images/design.png" />
    <div class="title-transaction-list">Transactions List</div>

    <div style="margin: 10px 20px; display: flex; justify-content: flex-end;">
  <select id="filterType" class="filter-btn">
    <option value="all">All</option>
    <option value="Deposit">Deposit</option>
    <option value="Payment">Payment</option>
    <option value="External Transfer">External Transfer</option>
    <option value="Internal Transfer">Internal Transfer</option>
    <option value="Suspicious">Suspicious</option>
  </select>
</div>

    <div class="container">
      <div class="table-wrapper">
        <table class="transaction-table">
          <thead>
            <tr>
              <th>Type</th>
              <th>User ID</th>
              <th>Recipient</th>
              <th>Amount</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="transaction-list">
            <!-- Transaction rows will be inserted here -->
          </tbody>
        </table>
      </div>
      </div>


    <div class="title">Transaction<br />Management</div>

    <div class="security-alerts">
      <div class="security-alert-num">20</div>
      <div class="security-alerts2">Security Alerts</div>
      <div class="rectangle3"></div>
      <div class="bg"></div>
      <img class="security-icon" src="images/icon3.png" />
    </div>

    <div class="sidebar">
      <div class="logo">
        <a href="admin_dashboard.php">
          <img class="verde-icon" src="images/verde icon.png" />
          <div class="verde-bank">VerdeBank</div>
        </a>
      </div>

      <img class="line-1" src="images/line1.png" />
      <img class="active" src="images/active.png" />

      <a href="admin_dashboard.php">
        <img class="img-dashboard" src="images/img-dashboard.png" />
        <div class="txt-dashboard">Dashboard</div>
      </a>
`
      <a href="admin_accounts.php">
        <img class="img-acc" src="images/img-acc.png" />
        <div class="txt-acc">Account</div>
      </a>

      <a href="admin_transactions.php">
        <img class="img-bills" src="images/img-bills.png" />
        <div class="txt-transactions">Transactions</div>
      </a>
    </div>
  </div>

<script>
document.addEventListener("DOMContentLoaded", function () {
  fetch("fetch_transactions.php")
    .then(response => response.json())
    .then(data => {
      const tbody = document.getElementById('transaction-list');
      const alertCountEl = document.querySelector('.security-alert-num');
      let alertCount = parseInt(data.security_alerts) || 0;
      alertCountEl.textContent = alertCount;

      data.transactions.forEach(tx => {
        const tr = document.createElement("tr");

        const isSuspicious = tx.suspicious;

        tr.innerHTML = `
          <td>${tx.type}</td>
          <td>${tx.user_id}</td>
          <td>${tx.recipient}</td>
          <td>₱${parseFloat(tx.amount).toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
          <td>${new Date(tx.date).toLocaleString()}</td>
          <td>
            <button class="mark-alert ${isSuspicious ? 'suspicious' : ''}">
              ${isSuspicious ? 'Unmark Suspicious' : 'Mark Suspicious'}
            </button>
          </td>
        `;

        const button = tr.querySelector("button");

        button.addEventListener("click", () => {
          // Toggle the button text between Mark and Unmark Suspicious
          const isCurrentlySuspicious = button.classList.contains("suspicious");
          const action = isCurrentlySuspicious ? "unmark" : "mark"; // Set action based on current state

        fetch("mark_suspicious.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            id: tx.id,               
            type: tx.type,           
            action: action          
          })
        })
          .then(res => res.json())
          .then(response => {
            if (response.success) {
              if (isCurrentlySuspicious) {
                button.textContent = "Mark Suspicious";
                button.classList.remove("suspicious");
                alertCount--;  // Decrease alert count when unmarked
              } else {
                button.textContent = "Unmark Suspicious";
                button.classList.add("suspicious");
                alertCount++;  // Increase alert count when marked
              }
              alertCountEl.textContent = alertCount;
            } else {
              alert("Failed to update transaction: " + response.message);
            }
          })
          .catch(() => {
            alert("Error updating transaction.");
          });
        });

        tbody.appendChild(tr);
      });
    });
});

</script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
  fetch("fetch_transactions.php")
    .then(response => response.json())
    .then(data => {
      const tbody = document.getElementById('transaction-list');
      const alertCountEl = document.querySelector('.security-alert-num');
      const filterSelect = document.getElementById('filterType');
      let alertCount = parseInt(data.security_alerts) || 0;
      alertCountEl.textContent = alertCount;

      // Save all transactions for filtering later
      let allTransactions = data.transactions;

      function renderTransactions(transactions) {
        tbody.innerHTML = ''; // Clear current rows

        transactions.forEach(tx => {
          const tr = document.createElement("tr");
          const isSuspicious = tx.suspicious;

          tr.innerHTML = `
            <td>${tx.type}</td>
            <td>${tx.user_id}</td>
            <td>${tx.recipient}</td>
            <td>₱${parseFloat(tx.amount).toLocaleString('en-PH', {minimumFractionDigits: 2})}</td>
            <td>${new Date(tx.date).toLocaleString()}</td>
            <td>
              <button class="mark-alert ${isSuspicious ? 'suspicious' : ''}">
                ${isSuspicious ? 'Unmark Suspicious' : 'Mark Suspicious'}
              </button>
            </td>
          `;

          const button = tr.querySelector("button");
          button.addEventListener("click", () => {
            const isCurrentlySuspicious = button.classList.contains("suspicious");
            const action = isCurrentlySuspicious ? "unmark" : "mark";

            fetch("mark_suspicious.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({
                id: tx.id,
                type: tx.type,
                action: action
              })
            })
            .then(res => res.json())
            .then(response => {
              if (response.success) {
                if (isCurrentlySuspicious) {
                  button.textContent = "Mark Suspicious";
                  button.classList.remove("suspicious");
                  alertCount--;
                } else {
                  button.textContent = "Unmark Suspicious";
                  button.classList.add("suspicious");
                  alertCount++;
                }
                alertCountEl.textContent = alertCount;
              } else {
                alert("Failed to update transaction: " + response.message);
              }
            })
            .catch(() => {
              alert("Error updating transaction.");
            });
          });

          tbody.appendChild(tr);
        });
      }
      
      renderTransactions(allTransactions); // Initial render

      // ✅ Filter event listener
    filterSelect.addEventListener("change", () => {
      const selected = filterSelect.value;
      const filtered = 
        selected === "all" ? allTransactions :
        selected === "Suspicious" ? allTransactions.filter(tx => tx.suspicious == 1) :
        allTransactions.filter(tx => tx.type.toLowerCase() === selected.toLowerCase());
      renderTransactions(filtered);
    });

    });
});
</script>
</body>
</html>
