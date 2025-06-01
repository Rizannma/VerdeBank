<!DOCTYPE html>
<html>
    <head>
        <title>Login</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="login.css">
    </head>

    <body>
        <div class="login">
            <div class="ellipse-11"></div>
            <div class="ellipse-10"></div>
            <img class="graph-6" src="images/login-logo.png" />
            <img class="rectangle-1" src="images/login-rectangle.png" />
            <div class="select-your-role">Select Your Role</div>

            <div
              class="rectangle-admin"
              onclick="window.location.href='admin_login.php';"
            ></div>

            <div class="admin">Admin</div>
            <img class="admin2" src="images/admin.png" />

            <div
              class="rectangle-user"
              onclick="window.location.href='user_login.php';"
            ></div>

            <div class="user">User</div>
            <img class="user2" src="images/user.png" />
            <div class="line-4"></div>
          </div>

          <div class="btn-back" onclick="window.location.href='index.php';">
            <div class="txt-back"> Back to Home</div>
            <img class="arrow" src="images/arrow-back.png" />
        </div>

    </body>
</html>