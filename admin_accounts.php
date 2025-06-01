<?php

$conn = new mysqli("localhost", "root", "", "verde_bank_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch each category of users
$activeQuery = "SELECT * FROM user_accounts WHERE status = 'active'";
$pendingQuery = "SELECT * FROM user_accounts WHERE status = 'pending'";
$suspendedQuery = "SELECT * FROM user_accounts WHERE status = 'suspended'";

$activeResult = $conn->query($activeQuery);
$pendingResult = $conn->query($pendingQuery);
$suspendedResult = $conn->query($suspendedQuery);

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Account Management</title>
  <link rel="stylesheet" href="admin_accounts.css" />

</head>
<body>
<div class="account">
  <!-- Stats Panels -->
   <a href="#suspended-accounts">
  <div class="suspended-acc">
    <div class="suspended-num"><?php echo $suspendedResult->num_rows; ?></div>
    <div class="suspended">Suspended</div>
    <div class="rectangle"></div>
    <div class="bg"></div>
    <img class="icon-2" src="images/icon2.png" />
  </div>
  </a>

  <a href="#pending-accounts">
  <div class="pending-acc">
    <div class="pending-num"><?php echo $pendingResult->num_rows; ?></div>
    <div class="pending">Pending</div>
    <div class="rectangle2"></div>
    <div class="bg"></div>
    <img class="icon-2" src="images/icon2.png" />
  </div>
  </a>
  
   <a href="#active-accounts">
  <div class="active-acc">
    <div class="active-num"><?php echo $activeResult->num_rows; ?></div>
    <div class="active-accounts">Active Accounts</div>
    <div class="rectangle3"></div>
    <div class="bg2"></div>
    <img class="icon-1" src="images/icon1.png" />
  </div>
</a> 
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
    <a href="admin_dashboard.php"><img class="img-dashboard" src="images/img-dashboard.png" /><div class="txt-dashboard">Dashboard</div></a>
    <a href="admin_accounts.php"><img class="img-acc" src="images/img-acc.png" /><div class="txt-acc">Account</div></a>
    <a href="admin_transactions.php"><img class="img-bills" src="images/img-bills.png" /><div class="txt-transactions">Transactions</div></a>
  </div>

  <div class="title">Account Management</div>

<!-- JAVASCRIPT -->
<script>
  function toggleRejectBox(userId) {
    const box = document.getElementById('reject-box-' + userId);
    box.style.display = box.style.display === 'none' ? 'block' : 'none';
  }

  function confirmApprove(event) {
    if (!confirm('Are you sure you want to approve this account?')) {
      event.preventDefault();
    }
  }

  function confirmSuspend(event) {
    if (!confirm('Suspend this account for the provided reason and duration?')) {
      event.preventDefault();
    }
  }

  function confirmReactivate(event) {
    if (!confirm('Reactivate this account?')) {
      event.preventDefault();
    }
  }
</script>


<!-- ACTIVE ACCOUNTS -->
<div id="active-accounts" class="title-active-accounts">Active Accounts</div>
<div class="container_active-accounts">
  <table>
    <tr>
      <th>Full Name</th>
      <th>Email</th>
      <th>Address</th>
      <th>Savings Account No.</th>
      <th>Checking Account No.</th>
      <th>Action</th>
    </tr>
    <?php while($row = $activeResult->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['fullname']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= nl2br(htmlspecialchars($row['address'])) ?></td>
        <td><?= !empty($row['savings_account_number']) ? htmlspecialchars($row['savings_account_number']) : '-' ?></td>
        <td><?= !empty($row['checking_account_number']) ? htmlspecialchars($row['checking_account_number']) : '-' ?></td>
        <td>
          <!-- Suspend button -->
          <button type="button" class="btn btn-suspend show-suspend-form-btn" data-user-id="<?= $row['user_id'] ?>">
            Suspend
          </button>

          <!-- Hidden suspend form -->
          <form method="POST" action="admin_suspend_account.php" class="suspend-form" id="suspend-form-<?= $row['user_id'] ?>">
            <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>" />
            <select name="reason" required>
              <option value="">Reason</option>
              <option value="Fraud">Fraud</option>
              <option value="Suspicious Activity">Suspicious Activity</option>
            </select>
            <input type="text" name="duration" placeholder="Duration (e.g. 30 days)" required />
            <button type="submit" name="suspend" class="btn btn-danger">Confirm Suspend</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>

<script>
  // Show suspend form and hide suspend button
  document.querySelectorAll('.show-suspend-form-btn').forEach(button => {
    button.addEventListener('click', () => {
      const userId = button.getAttribute('data-user-id');
      const form = document.getElementById('suspend-form-' + userId);
      if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'flex';
        button.style.display = 'none'; // hide suspend button while form shows
      }
    });
  });

  // Add confirmation before form submission
  document.querySelectorAll('.suspend-form').forEach(form => {
    form.addEventListener('submit', e => {
      const confirmed = confirm('Suspend this account?');
      if (!confirmed) {
        e.preventDefault(); // Cancel submission if not confirmed
      }
    });
  });
</script>

<style>
  .suspend-form {
    display: none;
    flex-direction: column;
    gap: 8px;
    margin-top: 8px;
  }
</style>


<!-- PENDING ACCOUNTS -->
<div id="pending-accounts" class="title-pending-accounts">Pending Accounts</div>
<div class="container_pending-accounts">
  <table>
    <tr>
      <th>Full Name</th>
      <th>Email</th>
      <th>Address</th>
      <th>Birthday</th>
      <th>ID</th>
      <th>Action</th>
    </tr>
    <?php while($row = $pendingResult->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['fullname']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['address']) ?></td>
        <td><?= htmlspecialchars($row['birthday']) ?></td>
        <td><a href="uploads/<?= htmlspecialchars($row['valid_id']) ?>" target="_blank">View</a></td>
        <td>
          <form method="POST" action="admin_verify_account.php" style="margin: 0;" id="form-<?= $row['user_id'] ?>">
            <input type="hidden" name="user_id" value="<?= $row['user_id'] ?>" />

            <div style="display: flex; justify-content: center; gap: 10px; margin-bottom: 8px;">
              <!-- Approve button -->
              <button type="submit" name="approve" class="approve-btn" style="min-width: 90px;" 
                onclick="return confirmApprove(<?= $row['user_id'] ?>)">Approve</button>

              <!-- Reject toggle button -->
              <button type="button" class="reject-btn" style="min-width: 90px;" 
                onclick="toggleRejectBox(<?= $row['user_id'] ?>)">Reject</button>
            </div>

           <!-- Reject reason box (hidden by default) -->
<div id="reject-box-<?= $row['user_id'] ?>" class="reject-reason-container" style="display:none;">
  <textarea name="reject_reason" rows="3" placeholder="Reason for rejection..." style="width: 100%;"></textarea>
  <div class="action-group" style="display: flex; justify-content: center; gap: 10px; margin-top: 6px;">
    <!-- Reject confirm button -->
    <button type="submit" name="reject_confirm" class="reject-confirm" style="min-width: 90px;"
      onclick="return confirmReject(<?= $row['user_id'] ?>)">Confirm</button>

    <!-- Cancel button -->
    <button type="button" class="reject-btn" style="min-width: 90px;" 
      onclick="toggleRejectBox(<?= $row['user_id'] ?>)">Cancel</button>
  </div>
</div>

    
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>

<script>
  // Toggle the reject reason textarea box
  function toggleRejectBox(userId) {
    const box = document.getElementById('reject-box-' + userId);
    const isVisible = box.style.display === 'flex' || box.style.display === 'block';
    box.style.display = isVisible ? 'none' : 'block';

    // Optionally, clear the textarea when hiding
    if (!isVisible) {
      box.querySelector('textarea').focus();
    } else {
      box.querySelector('textarea').value = '';
    }
  }

  // Confirm approval with alert
  function confirmApprove(userId) {
    return confirm('Approve this account?');
  }

  // Confirm rejection with alert
  function confirmReject(userId) {
    // Optional: check if reason is filled
    const box = document.getElementById('reject-box-' + userId);
    const reason = box.querySelector('textarea').value.trim();
    if (reason === '') {
      alert('Please provide a reason for rejection.');
      return false;
    }
    return confirm('Reject this account?');
  }
</script>

<!-- SUSPENDED ACCOUNTS -->
<div id="suspended-accounts" class="title-suspended-accounts">Suspended Accounts</div>
<div class="container_suspended-accounts">
  <table>
    <tr>
      <th>Full Name</th>
      <th>Email</th>
      <th>Savings Account No.</th>
      <th>Checking Account No.</th>
      <th>Reason</th>
      <th>Duration</th>
      <th>Action</th>
    </tr>
    <?php while($row = $suspendedResult->fetch_assoc()): ?>
      <tr>
        <td><?= htmlspecialchars($row['fullname']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= !empty($row['savings_account_number']) ? htmlspecialchars($row['savings_account_number']) : '-' ?></td>
        <td><?= !empty($row['checking_account_number']) ? htmlspecialchars($row['checking_account_number']) : '-' ?></td>
        <td><?= htmlspecialchars($row['suspension_reason']) ?></td>
        <td><?= htmlspecialchars($row['suspension_duration']) ?></td>
        <td>
          <form method="POST" action="admin_reactivate_account.php" onsubmit="confirmReactivate(event)">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($row['user_id']) ?>" />
            <button type="submit" name="reactivate" class="approve-btn">Reactivate</button>
          </form>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>


  <img class="design" src="images/design.png" />
</div>

</body>
</html>
