<?php
// Database connection
$servername = "localhost";  // Change as per your DB configuration
$username = "root";         // Change as per your DB configuration
$password = "";             // Change as per your DB configuration
$dbname = "verde_bank_db";  // Change to your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start the session
session_start();
$user_id = $_SESSION['user_id']; // Assuming the user_id is stored in the session

// Query to fetch user data
$sql = "SELECT * FROM user_accounts WHERE user_id = '$user_id'"; 
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Fetch the data as an associative array
    $user = $result->fetch_assoc();
    
    // Return data as JSON
    echo json_encode([
        'fullname' => $user['fullname'],
        'birthday' => $user['birthday'],
        'address' => $user['address'],
        'email' => $user['email'],
        'savings_balance' => $user['savings_balance'],
        'checking_balance' => $user['checking_balance'],
        'pin' => $user['PIN'],
    ]);
} else {
    echo json_encode(['error' => 'User not found']);
}

// Close the connection
$conn->close();
?>
