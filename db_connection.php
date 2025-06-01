<?php
$servername = "localhost";
$username = "root";  // default XAMPP user
$password = "";      // default XAMPP password (empty)
$dbname = "verde_bank_db";  // Replace with your DB name

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
