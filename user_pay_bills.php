<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Bills</title>
    <link rel="stylesheet" href="user_pay_bills.css">   
    </head>
<body>
 <div class="paybills-sc">
  <div class="greetings">Pay your bills with Verde.</div>
  <div class="subheading">Select a recipient and pay in just a few clicks.</div>
  <div class="lbl-choose-biller">Click to select your biller.</div>
 
<div class="converge selectable-card" data-biller="Converge">
    <div class="rectangle-1"></div>
    <div class="_10-fee-charge">₱10 fee charge</div>
    <div class="logo1">
      <div class="ellipse-5"></div>
      <div class="ellipse-6"></div>
      <div class="converge2">Converge</div>
    </div>
  </div>

  <div class="meralco  selectable-card" data-biller="Meralco">
    <div class="image-2"></div>
    <div class="logo2">
      <img class="meralco-icon" src="images/meralco icon.png" />
      <div class="meralco2">Meralco</div>
    </div>
    <div class="rectangle-1"></div>
    <div class="free-of-charge">Free of Charge</div>
  </div>

  <div class="maynilad  selectable-card" data-biller="Maynilad">
    <div class="rectangle-1"></div>
    <div class="free-of-charge">Free of Charge</div>
    <div class="logo3">
      <img class="maynilad-icon" src="images/maynilad icon.png" />
      <div class="maynilad2">Maynilad</div>
    </div>
  </div>

  <div class="verde-loan  selectable-card" data-biller="Verde Loan">
    <div class="logo4">
      <div class="verde-loan2">Verde Loan</div>
      <img class="verde-icon" src="images/verde icon white.png" />
    </div>
    <div class="rectangle-1"></div>
    <div class="free-of-charge2">Free of Charge</div>
  </div>

  <div class="globe  selectable-card" data-biller="Globe">
    <div class="rectangle-1"></div>
    <div class="_5-fee-charge">₱5 fee charge</div>
    <div class="logo5">
      <img class="globe-icon" src="images/globe icon.png" />
      <div class="globe2">Globe</div>
    </div>
  </div>

  <div class="pldt  selectable-card" data-biller="PLDT">
    <div class="rectangle-12"></div>
    <div class="logo6">
      <img class="pldt-icon" src="images/pldt icon.png" />
      <div class="pldt2">PLDT</div>
    </div>
    <div class="_15-fee-charge">₱15 fee charge</div>
  </div>

 <script>
  const cards = document.querySelectorAll('.selectable-card');
  cards.forEach(card => {
    card.addEventListener('click', () => {
      // Optional: deselect others if only one selection allowed
      cards.forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');

      // Store selected biller (optional)
      const selectedBiller = card.getAttribute('data-biller');
      localStorage.setItem('selectedBiller', selectedBiller);
    });
  });
</script>



  <div class="btn-next">
    <button class="next">Next</button>
  </div>

  <script>
    document.querySelector('.next').addEventListener('click', () => {
      const selectedCard = document.querySelector('.selectable-card.selected');
    if (selectedCard) {
      window.location.href = 'user_pay_form.php';
    } else {
      document.getElementById('alertModal').style.display = 'flex';
    }
  });
</script>

  <!-- SideBar -->
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