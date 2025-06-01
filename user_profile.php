<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="user_profile.css">   
</head>
<body>
    <div class="profile-sc">

        <div class="sidebar">
            <div class="name" id="userFullname">Stella Ann Mariz Montesines</div>
            <div class="account">Verified Account</div>
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
                <div class="txt-profile" style="color: #97e8e1;">Profile</div>
            </a>

            <img class="img-bills" src="images/img-bills.png" />
            <div class="line-2"></div>
            <img class="line-1" src="images/line1.png" />
            <img class="money-bag-1" src="images/img-loan.png" />
        </div>

        <div class="greetings">My Profile</div>
        <div class="fullname" id="fullName">Stella Ann Mariz Montesines</div>
        <div class="account-created">Member since January 2021</div>
        <div class="account-type2">
            <div class="verified-acc">Verified Account</div>
        </div>

        <div class="upload-container">
            <label for="profile-upload" class="profile-button">
                <img class="ellipse-4" src="images/profile pic.png" alt="Profile Picture" />
                <div class="group-1">
                    <div class="ellipse-12"></div>
                    <img class="add-photo-1" src="images/add-photo icon.png" alt="Add Photo" />
                </div>
            </label>
            <input type="file" id="profile-upload" accept="image/*" style="display: none" />
        </div>
        
        <div class="container">
            <div class="personal-information">Personal Information</div>
            <div class="full-name">Full Name</div>
            <div class="date-of-birth">Date of Birth</div>
            <div class="address">Address</div>
            <div class="email-address">Email Address</div>
            <div class="fullname2" id="fullName2"></div>
            <div class="dateofbirth" id="dob"></div>
            <div class="address2" id="address"></div>
            <div class="email" id="email"></div>
        </div>
    </div>

    <script>
        // Fetch user profile data when the page is loaded
        window.addEventListener('DOMContentLoaded', () => {
            fetch('get_user_profile.php')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error fetching user profile:', data.error);
                    } else {
                        // Populate the profile with the fetched data
                        document.getElementById('fullName').textContent = data.fullname;
                        document.getElementById('fullName2').textContent = data.fullname;
                        document.getElementById('dob').textContent = data.birthday;
                        document.getElementById('address').textContent = data.address;
                        document.getElementById('email').textContent = data.email;
                    }
                })
                .catch(error => {
                    console.error('Error fetching profile data:', error);
                });
        });
    </script>
</body>
</html>
