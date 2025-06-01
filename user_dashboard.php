<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="user_dashboard.css">
</head>
<body>
    <div class="user-dashboard">
        <!-- Sidebar -->
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

        <!-- Main content area -->
        <div class="main-content">
            <div class="greetings">Welcome Back, User!</div>
            <div class="subheading">Here’s your financial overview for today.</div>

            <!-- Checking Account Balance -->
            <div class="checking-balance">
                <div class="_100-000-00">₱ 100,000.00</div> <!-- Will be updated dynamically -->
                <div class="account-balance">Account Balance</div>
                <div class="checking-account">Checking Account</div>
                <img class="card-logo-2" src="images/card-logo2.png" />
            </div>
            
<form action="logout.php" method="post" class="btn-logout">
    <button type="submit" class="logout">Logout</button>
</form>

            <!-- Savings Account Balance -->
            <div class="savings-balance">
                <div class="_200-000-00">₱ 200,000.00</div> <!-- Will be updated dynamically -->
                <div class="account-balance">Account Balance</div>
                <div class="savings-account">Savings Account</div>
                <img class="card-logo-22" src="images/card-logo2.png" />
            </div>

            <!-- Total Balance (Savings + Checking) -->
            <div class="total-balance">
                <div class="_300-000-00">₱ 300,000.00</div> <!-- Will be updated dynamically -->
                <div class="total-balance2">Total Balance</div>
                <div class="savings-checking-account">Savings & Checking Account</div>
                <img class="card-logo-1" src="images/card-logo1.png" />
            </div>

<!-- Recent Transactions Section -->
<div class="recent-transactions">
    <?php
    $conn = new mysqli("localhost", "root", "", "verde_bank_db");
    $sql = "
        SELECT id, amount, deposit_date AS date, 'Deposit' AS type FROM deposits
        UNION ALL
        SELECT id, amount, payment_date AS date, 'Payment' AS type FROM payments
        UNION ALL
        SELECT id, amount, transfer_date AS date, 'Internal Transfer' AS type FROM internal_transfers
        UNION ALL
        SELECT id, amount, transfer_date AS date, 'External Transfer' AS type FROM external_transfers
        ORDER BY date DESC
        LIMIT 5
    ";
    $result = $conn->query($sql);
    ?>

    <div class="bg-recent-transactions">
        <?php if ($result->num_rows > 0): ?>
            <ul class="recent-list">
                <?php while ($row = $result->fetch_assoc()):
                    $type = $row['type'];
                    $amountValue = $row['amount'];
                    $amount = number_format($amountValue, 2);
                    $isAddition = in_array($type, ['Deposit']); // only Deposit adds funds
                    $colorClass = $isAddition ? 'amount-add' : 'amount-deduct';
                    $sign = $isAddition ? '+' : '-';
                ?>
                    <li class="recent-item">
                        <span><strong><?= htmlspecialchars($type) ?></strong></span>
                        <span class="<?= $colorClass ?>"><?= $sign ?>₱<?= $amount ?></span>
                       <span class="date"><?= date('M d, Y', strtotime($row['date'])) ?></span>

                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p class="no-transactions">No recent transactions found.</p>
        <?php endif; ?>
    </div>
    <div class="title-recent-transaction">Recent Transactions</div>
  <button class="btn-view-all" onclick="window.location.href='user_transactions.php'">View All</button>
</div>
<style>
.recent-list {
  list-style: none;
  padding: 20px;
  margin-top: 20px;
  font-family: "InstrumentSans-Regular", sans-serif;
  color: #ffffff;
  font-size: 15px;
}

.recent-item {
  margin-top: 10px;
  margin-bottom: 14px;
  padding: 10px 0;
  display: flex;
  justify-content: space-between;
  align-items: flex-start; 
}

.recent-item .date {
  color: #a1a1a1;
  font-size: 13px;
  font-style: italic;
  margin-top: 25px; 
  margin-left: 20px;
}


.recent-item strong {
  font-family: "InstrumentSans-Bold", sans-serif;
  font-weight: 600;
  color: #ffffff;
}

.amount-add {
  color: #3cd77b;
  font-weight: 600;
}

.amount-deduct {
  color: #ff6b6b;
  font-weight: 600;
}

.no-transactions {
  padding: 20px;
  font-family: "InstrumentSans-Regular", sans-serif;
  font-size: 15px;
  color: #999999;
}

.bg-recent-transactions {
  width: 460px;
  height: 311px;
}
</style>


 <?php
// Debug session
if (!isset($_SESSION)) {
    echo "Session is not started!";
} else {
    echo "Session is started.";
}

$userId = $_SESSION['user_id'] ?? 0;
if ($userId == 0) {
    echo "No user ID in session.";
    exit;
}

$conn = new mysqli("localhost", "root", "", "verde_bank_db");

$query = "SELECT savings_balance, checking_balance FROM user_accounts WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$balances = $result->fetch_assoc();

$savings = $balances['savings_balance'] ?? 0;
$checking = $balances['checking_balance'] ?? 0;
?>

<div class="account-summary">
    <div class="bg-account-summary">
        <canvas id="accountBalanceChart" width="330" height="311"></canvas>
    </div>
    <div class="title-account-summary">Account Summary</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const ctx = document.getElementById('accountBalanceChart').getContext('2d');
const accountBalanceChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Savings', 'Checking'],
        datasets: [{
            label: '₱ Balance',
            data: [<?= (float)$savings ?>, <?= (float)$checking ?>],
            backgroundColor: ['#07A378', '#1F7685'],
            borderRadius: 8,
        }]
    },
    options: {
        responsive: false,
        plugins: {
            legend: {
                display: true,
                labels: {
                    font: {
                        size: 10,         // smaller font size for thinner legend
                        weight: '400'     // normal or lighter weight; '300' if you want lighter
                    },
                    padding: 20           // adds space around each legend label (left/right)
                },
                // You can also add some padding around legend box if needed:
                // padding: { top: 10, bottom: 10, left: 15, right: 15 }
            }
        },
        scales: {
            x: { ticks: { color: '#fff' }, grid: { display: false } },
            y: { ticks: { color: '#ccc' }, grid: { color: 'rgba(255,255,255,0.05)' } }
        }
    }
});

</script>


            <!-- Date and Time -->
            <div class="title-date-time">Date and Time</div>
            <div class="bg-date"></div>
            <div class="date2" id="dateDisplay">May 16, 2025<br>Friday</div>
            <div class="txt-today-is">Today is</div>
            <div class="bg-time"></div>
            <div class="time2" id="timeDisplay">00:00:00</div>
            <div class="lbl-time">hours:minutes:seconds</div>
        </div>
    </div>

    <!-- JavaScript for real-time date and time -->
    <script>
        function updateDateTime() {
            const dateElement = document.getElementById("dateDisplay");
            const timeElement = document.getElementById("timeDisplay");

            const now = new Date();

            // Get formatted parts
            const options = { month: 'long', day: 'numeric', year: 'numeric' };
            const formattedDate = now.toLocaleDateString('en-US', options);
            const weekday = now.toLocaleDateString('en-US', { weekday: 'long' });

            const formattedTime = now.toLocaleTimeString('en-US', { hour12: false });

            // Set values
            dateElement.innerHTML = `${formattedDate}<br>${weekday}`;
            timeElement.textContent = formattedTime;
        }

        // Run immediately and update every second
        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Fetch balance data from PHP (get_balance.php)
        fetch('get_balance.php')
            .then(response => response.json())
            .then(data => {
                // Dynamically update balance fields
                document.querySelector('._100-000-00').textContent = `₱ ${data.checking_balance}`;
                document.querySelector('._200-000-00').textContent = `₱ ${data.savings_balance}`;
                document.querySelector('._300-000-00').textContent = `₱ ${data.total_balance}`;
            })
            .catch(error => {
                console.error('Error fetching balance data:', error);
            });
    </script>

</body>
</html>
