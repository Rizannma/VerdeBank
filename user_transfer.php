<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transfer</title>
    <link rel="stylesheet" href="user_transfer.css">   
    </head>

    <body>
        <div class="transfer-sc">
    <div class="greetings">Easy External Transfer with Verde.</div>
    <div class="subheading">
        Instantly transfer money externally with ease—fast, simple, and secure.
    </div>
        <div class="subheading2">Choose bank to transfer.</div>

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


<!-- Bank -->
 <div class="card1 selectable-card" payment="ChinaBank">
        <div class="logo3">
        <div class="china-bank">ChinaBank</div>
        <img class="chinabank-icon" src="images/chinabank icon.png" />
        </div>
        <div class="rectangle-1"></div>
        <div class="free-of-charge">Click to Select</div>
    </div>

    <div class="card2 selectable-card" payment="MetroBank">
        <div class="rectangle-1"></div>
        <div class="free-of-charge">Click to Select</div>
        <div class="logo4">
        <img class="metrobank-icon" src="images/metrobank icon.png" />
        <div class="metro-bank">MetroBank</div>
        </div>
    </div>

    <div class="card3 selectable-card" payment="LandBank">
        <div class="logo3">
        <div class="land-bank">LandBank</div>
        <img class="landbank-icon" src="images/landbank icon.png" />
        </div>
        <div class="rectangle-1"></div>
        <div class="free-of-charge2">Click to Select</div>
    </div>

    <div class="card4 selectable-card" payment="SeaBank">
        <div class="rectangle-12"></div>
        <div class="logo6">
        <img class="seabank-icon" src="images/seabank icon.png" />
        <div class="sea-bank">SeaBank</div>
        </div>
        <div class="_15-fee-charge">Click to Select</div>
    </div>

        <div class="card5 selectable-card" payment="BDO">
        <div class="rectangle-1"></div>
        <div class="_30-fee-charge">Click to Select</div>
        <div class="logo5">
        <div class="bdo-icon">
            <div class="ellipse-5"></div>
            <div class="ellipse-6"></div>
        </div>
        <div class="bdo3">BDO</div>
        </div>
    </div>

    <div class="card6 selectable-card" payment="BPI">
        <div class="rectangle-1"></div>
        <div class="_10-fee-charge">Click to Select</div>
        <div class="logo2">
        <div class="bpi">BPI</div>
        <img class="bpi-icon" src="images/bpi icon.png" />
        </div>
    </div>



  <div class="btn-next">
    <button class="next">Next</button>
  </div>

</div>

      <!-- Script for Selecting Bank -->
   <script>
  const cards = document.querySelectorAll('.selectable-card');
  cards.forEach(card => {
    card.addEventListener('click', () => {
      // Optional: deselect others if only one selection allowed
      cards.forEach(c => c.classList.remove('selected'));
      card.classList.add('selected');

      // Store selected biller (optional)
      const selectedPayment = card.getAttribute('payment');
      localStorage.setItem('selectedPayment', selectedPayment);
    });
  });
</script>

  
  <script>
    document.querySelector('.next').addEventListener('click', () => {
      const selectedCard = document.querySelector('.selectable-card.selected');
    if (selectedCard) {
      window.location.href = 'user_transfer_form.php';
    } else {
      document.getElementById('alertModal').style.display = 'flex';
    }
  });
</script>

    </body>

</html>