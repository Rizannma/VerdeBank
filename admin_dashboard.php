<?php
session_start();
include 'db_connection.php';

$activeCount = $pendingCount = $suspendedCount = 0;$securityCount = 0;
$recent = [];
$summary = [
  'Deposit' => 0,
  'Payment' => 0,
  'External Transfer' => 0,
  'Internal Transfer' => 0,
];

if (isset($_SESSION['email']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $admin_email = $_SESSION['email'];

  // Account Status Counts
  $activeCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_accounts WHERE status = 'active'"))['total'];
  $pendingCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_accounts WHERE status = 'pending'"))['total'];
  $suspendedCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_accounts WHERE status = 'suspended'"))['total'];

// Security Alerts Count
$query = "
    SELECT 
        (SELECT COUNT(*) FROM payments WHERE suspicious = 1) +
        (SELECT COUNT(*) FROM deposits WHERE suspicious = 1) +
        (SELECT COUNT(*) FROM internal_transfers WHERE suspicious = 1) +
        (SELECT COUNT(*) FROM external_transfers WHERE suspicious = 1) 
    AS total_alerts
";

$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$securityCount = $row['total_alerts'];

 // Deposits
$d_stmt = $conn->prepare("SELECT payment_method AS type, amount, deposit_date AS date FROM deposits");
$d_stmt->execute();
$d_res = $d_stmt->get_result();
while ($row = $d_res->fetch_assoc()) {
  $row['type'] = 'Deposit';
  $recent[] = $row;
  $summary['Deposit'] += $row['amount'];
}

// Payments
$p_stmt = $conn->prepare("SELECT biller AS type, amount, payment_date AS date FROM payments");
$p_stmt->execute();
$p_res = $p_stmt->get_result();
while ($row = $p_res->fetch_assoc()) {
  $row['type'] = 'Payment';
  $recent[] = $row;
  $summary['Payment'] += $row['amount'];
}

// External Transfers
$e_stmt = $conn->prepare("SELECT bank AS type, amount, transfer_date AS date FROM external_transfers");
$e_stmt->execute();
$e_res = $e_stmt->get_result();
while ($row = $e_res->fetch_assoc()) {
  $row['type'] = 'External Transfer';
  $recent[] = $row;
  $summary['External Transfer'] += $row['amount'];
}

// Internal Transfers
$i_stmt = $conn->prepare("SELECT to_account AS type, amount, transfer_date AS date FROM internal_transfers");
$i_stmt->execute();
$i_res = $i_stmt->get_result();
while ($row = $i_res->fetch_assoc()) {
  $row['type'] = 'Internal Transfer';
  $recent[] = $row;
  $summary['Internal Transfer'] += $row['amount'];
}

  // Sort and limit to 5
  usort($recent, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
  $recent = array_slice($recent, 0, 5);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="admin_dashboard.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
<div class="admin-dashboard">
  <div class="greetings">Welcome Back, Admin!</div>
  <div class="subheading">Here’s your management overview for today.</div>

  <!-- Account Status Boxes -->
<div class="active-accounts">
  <div class="active-num"><?php echo $activeCount; ?></div>
  <div class="active-accounts2">Active Accounts</div>
  <div class="rectangle3"></div>
  <div class="bg2"></div>
  <img class="icon-1" src="images/icon1.png" />
</div>

<div class="pending-accounts">
  <div class="pending-num"><?php echo $pendingCount; ?></div>
  <div class="pending">Pending</div>
  <div class="rectangle2"></div>
  <div class="bg"></div>
  <img class="icon-2" src="images/icon2.png" />
</div>

 <div class="suspended-acc">
  <div class="suspended-num"><?php echo $suspendedCount; ?></div>
  <div class="suspended">Suspended</div>
  <div class="rectangle"></div>
  <div class="bg"></div>
  <img class="icon-2" src="images/icon2.png" />
</div>
<div class="security">
  <div class="security-num"><?php echo $securityCount; ?></div>
  <div class="security-alerts">Security Alerts</div>
  <div class="rectangle"></div>
  <div class="bg"></div>
  <img class="icon-3" src="images/icon3.png" />
</div>

<form action="admin_logout.php" method="post" class="btn-logout">
    <button type="submit" class="logout">Logout</button>
</form>

  <!-- Recent Transactions -->
   <div class="title-recent-transaction">Recent Transactions</div>
       <button class="btn-view-all" onclick="window.location.href='admin_transactions.php'">View All</button>
  <div class="bg-recent-transactions">
    <table class="transac-table">
      <thead>
        <tr>
          <th>Type</th>
          <th>Amount</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent as $row): ?>
          <tr>
            <td><?= htmlspecialchars($row['type']) ?></td>
            <td>₱<?= number_format($row['amount'], 2) ?></td>
            <td><?= date("M d, Y h:i A", strtotime($row['date'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Chart -->
  <div class="title-transac">Transaction Summary</div>
  <div class="bg-transac-summary">
    <canvas id="transactionChart" width="1000" height="160"></canvas>
  </div>

  <!-- Time and Date -->
  <div class="bg-time"></div>
  <div class="time2" id="time">00:00:00</div>
  <div class="lbl-time">hours:minutes:seconds</div>

  <div class="bg-date"></div>
  <div class="date2" id="date">
    May 9, 2025
    <br />
    Monday
  </div>
  <div class="txt-today-is">Today is</div>
  <div class="title-date-time">Date and Time</div>

  <!-- Sidebar -->
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

<!-- JS Date Time and Chart -->
<script>
function updateDateTime() {
  const now = new Date();

  // Format time as HH:MM:SS
  const hours = String(now.getHours()).padStart(2, "0");
  const minutes = String(now.getMinutes()).padStart(2, "0");
  const seconds = String(now.getSeconds()).padStart(2, "0");
  const timeString = `${hours}:${minutes}:${seconds}`;
  document.getElementById("time").textContent = timeString;

  // Format date as Month DD, YYYY on one line
  const optionsDate = { year: "numeric", month: "long", day: "numeric" };
  const dateString = now.toLocaleDateString("en-US", optionsDate);

  // Format weekday separately
  const optionsWeekday = { weekday: "long" };
  const weekdayString = now.toLocaleDateString("en-US", optionsWeekday);

  // Set innerHTML with date on one line, weekday below
  document.getElementById("date").innerHTML = `${dateString}<br>${weekdayString}`;
}

// Initial call so it shows immediately on page load
updateDateTime();

// Update every second to keep time and date fresh
setInterval(updateDateTime, 1000);

// Chart.js Transaction Summary
const ctx = document.getElementById('transactionChart').getContext('2d');
const chart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: ['Deposit', 'Payment', 'External Transfer', 'Internal Transfer'],
    datasets: [{
      label: 'Amount (₱)',
      data: [
        <?= $summary['Deposit'] ?>,
        <?= $summary['Payment'] ?>,
        <?= $summary['External Transfer'] ?>,
        <?= $summary['Internal Transfer'] ?>
      ],
      backgroundColor: ['#07A378', '#1F7685', '#97E8E1', '#1EAE85']
    }]
  },
  options: {
    scales: {
      y: {
        beginAtZero: true,
        ticks: { color: 'white' }
      },
      x: {
        ticks: { color: 'white' }
      }
    },
    plugins: {
      legend: { labels: { color: 'white' } }
    }
  }
});
</script>
</body>
</html>
