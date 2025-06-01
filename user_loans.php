<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan</title>
    <link rel="stylesheet" href="user_loans.css">   
    </head>

<body>
    <div class="loan-sc">
        <div class="sidebar">
        <div class="name">Stella Ann Mariz Montesines</div>
        <div class="account-type">Savings and Checking Account</div>
        <img class="img-user" src="images/img-user.png" />
        
        <div class="logo">
            <a href="user_dashboard.php">
            <img class="verde-icon" src="images/verde icon.png" />
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

        <a href="user_loans.php" class="sidebar-link">
          <img class="img-cards" src="images/img-acc.png" />
          <div class="txt-cards">Loan</div>
        </a>

      <a href="user_accounts.php" class="sidebar-link">
        <div class="txt-account">Account</div>
      </a>

      <a href="user_profile.php" class="sidebar-link">
        <img class="img-profile" src="images/img-profile.png" />
        <div class="txt-profile">Profile</div>
      </a>

        <img class="img-bills" src="images/img-bills.png" />
        <div class="line-2" src="images/line1.png"></div>
        <img class="line-1" src="images/line1.png" />
        <img class="money-bag-1" src="images/img-loan.png" />
        </div>



        <div class="loan-card personal-loan">
  <div class="card-inner">
    <!-- Front -->
    <div class="card-front">
      <div class="loan-type">Personal Loan</div>
      <div class="rectangle"></div>
      <div class="interest">1.0 % interest per day</div>
      <div class="info">Click to view description.</div>
      <div class="btn-apply" >
        <a href="user_apply_personal_loan.php" class="apply-now">Apply Now</a>
      </div>
    </div>
    
    <!-- Back (Description) -->
    <div class="card-back">
      <div class="description">
         <h1>Personal Loan</h1>
        <p>No need for collateral. <br>
        Credited to your checking account. <br>
        With a 1.0% interest rate per day. 
     </p>
           <div class="rectangle"></div>
      <div class="interest">1.0 % interest per day</div>
    </div>
    </div>
  </div>
</div>

<div class="loan-card secured-loan">
  <div class="card-inner">
    <div class="card-front">
      <div class="loan-type">Secured Loan</div>
      <div class="rectangle"></div>
      <div class="interest">0.4 % interest per day</div>
      <div class="info">Click to view description.</div>
      <div class="btn-apply">
          <a href="user_apply_secured_loan.php" class="apply-now">Apply Now</a>
      </div>
    </div>
    <div class="card-back">
      <div class="description">
        <h1>Secured Loan</h1>
        <p>Use savings as collateral.<br>
        Credited to your checking account.<br>
        Collateral can be claim if loan is overdue.</p>
        <div class="rectangle"></div>
      <div class="interest">0.4 % interest per day</div>
      </div>
    </div>
  </div>
</div>

    <div class="greetings">Verde Offered Loans</div>
    <div class="greetings2">My Loans</div>
    <div class="subheading">
        Get flexible funds or low-interest borrowing backed by collateral to meet
        your financial needs.
    </div>
    <div class="loan-list"></div>
</div>

<script>
  document.querySelectorAll('.loan-card').forEach(card => {
    // Flip on card click
    card.addEventListener('click', function () {
      this.classList.toggle('flipped');
    });

    // Prevent flip when "Apply Now" button is clicked
    const applyBtn = card.querySelector('.apply-now');
    if (applyBtn) {
      applyBtn.addEventListener('click', function (event) {
        event.stopPropagation();
      });
    }
  });
</script>



</body>

</html>