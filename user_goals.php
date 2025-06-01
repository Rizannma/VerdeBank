<?php
session_start();
unset($_SESSION['error']);
unset($_SESSION['success']);
$userId = $_SESSION['user_id'] ?? 0;
$conn = new mysqli("localhost", "root", "", "verde_bank_db");

// Get user savings balance from user_accounts
$savings_balance = 0;
if ($userId > 0) {
    $stmt = $conn->prepare("SELECT savings_balance FROM user_accounts WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($savings_balance);
    $stmt->fetch();
    $stmt->close();
}

// Get user goals
$goals = [];
if ($userId > 0) {
    $query = "SELECT id, goal_name, target_amount, amount_saved FROM savings_goals WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $goals[] = $row;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Saving Goals</title>
  <link rel="stylesheet" href="user_goals.css">
</head>
<body>
  <div class="saving-goals">
    <div class="title">My Saving Goals</div>
    <img class="design" src="images/design.png" />
    <div class="box">
      <?php if (empty($goals)): ?>
        <div class="no-goals">You have no savings goals yet.<br>Create your first goal to start saving!</div>
      <?php else: ?>
        <div class="goals-container">
          <?php foreach ($goals as $goal): 
              $percent = min(100, ($goal['amount_saved'] / $goal['target_amount']) * 100);
          ?>
            <div class="goal-card">
              <div class="goal-name"><?= htmlspecialchars($goal['goal_name']) ?></div>
              
              <div class="goal-progress-info">
                <div class="goal-percentage"><?= round($percent) ?>% complete</div>
              </div>
              
              <div class="goal-progress-bar">
                <div class="goal-progress-fill" style="width: <?= $percent ?>%;"></div>
              </div>
              
              <div class="goal-amounts">
                <span class="amount-saved">₱<?= number_format($goal['amount_saved'], 2) ?></span>
                <span class="target-amount">of ₱<?= number_format($goal['target_amount'], 2) ?></span>
              </div>

              <div class="card-actions">
              <form method="POST" action="goal_actions.php" class="add-savings-form" onsubmit="return validateAllocation(this, <?= $savings_balance ?>)">
              <input type="hidden" name="goal_name" value="<?= htmlspecialchars($goal['goal_name']) ?>" />
              <input type="number" name="allocation" min="0.01" step="0.01" placeholder="Add amount" max="<?= $savings_balance ?>" required />
              <button type="submit" name="action" value="allocate">Add</button>
            </form>

            <form method="POST" action="goal_actions.php" class="goal-actions" onsubmit="return confirmGoalAction(event, <?= round($percent) ?>)">
              <input type="hidden" name="goal_name" value="<?= htmlspecialchars($goal['goal_name']) ?>" />
              <button type="submit" name="action" value="achieve">Mark Achieved</button>
              <button type="submit" name="action" value="delete">Delete</button>
            </form>

              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <button class="add-goal-btn" onclick="document.getElementById('add-goal-form').style.display='block'">Add New Goal</button>

      <div id="add-goal-form" class="goal-form" style="display: none;">
        <form method="POST" action="add_goal.php">
          <input type="text" name="goal_name" placeholder="Goal Name" required />
          <input type="number" name="target_amount" placeholder="Target Amount" required min="1" />
          <button type="submit">Save Goal</button>
          <button type="button" onclick="document.getElementById('add-goal-form').style.display='none'">Cancel</button>
        </form>
      </div>

    <div class="savings-balance-info">
      Available Savings Balance: &nbsp;
      <span class="balance-amount"> ₱<?= number_format($savings_balance, 2) ?></span>
    </div>
    </div>

    <div class="btn-back">
      <div class="txt-back" onclick="location.href='user_accounts.php'">Back to Account</div>
      <img class="arrow" src="images/arrow-back.png" />
    </div>
  </div>

  <script>
    function validateAllocation(form, maxBalance) {
      const input = form.allocation;
      const value = parseFloat(input.value);
      if (value > maxBalance) {
        alert("Allocation exceeds your available savings balance!");
        return false;
      }
      if (value <= 0) {
        alert("Please enter a positive amount.");
        return false;
      }
      return true;
    }
  </script>

  <script>
  function validateAllocation(form, maxBalance) {
    const input = form.allocation;
    const value = parseFloat(input.value);

    if (value > maxBalance) {
      alert("Allocation exceeds your available savings balance!");
      return false;
    }
    if (value <= 0) {
      alert("Please enter a positive amount.");
      return false;
    }

    return confirm(`Are you sure you want to add ₱${value.toFixed(2)} to this goal?`);
  }

  function confirmGoalAction(event, progress) {
    const action = event.submitter?.value;

    if (action === "delete") {
      return confirm("Are you sure you want to delete this goal?");
    }

    if (action === "achieve") {
      if (progress < 100) {
        alert("You can only mark this goal as achieved when it is 100% complete.");
        return false;
      }
      return confirm("Mark this goal as achieved?");
    }

    return true;
  }
</script>

</body>
</html>