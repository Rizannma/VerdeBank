<?php
// Database connection
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "verde_bank_db"; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Capture form data
$full_name = $_POST['full_name'];
$loan_purpose = $_POST['loan_purpose'];
$loan_amount = $_POST['loan_amount'];
$loan_tenure = $_POST['loan_tenure'];
$terms_agreed = isset($_POST['terms_agreed']) ? 1 : 0;
$loan_type = $_POST['loan_type']; // New field for loan type

// Validate form data
if (empty($full_name)  || empty($loan_purpose) || empty($loan_amount) || empty($loan_tenure) || !$terms_agreed) {
    echo json_encode(["success" => false, "error" => "All fields are required and terms must be agreed."]);
    exit();
}

// Insert loan application into database
$sql_insert = "INSERT INTO loan_applications (full_name,  loan_purpose, loan_amount, loan_tenure, terms_agreed, loan_type)
VALUES ('$full_name', '$loan_purpose', '$loan_amount', '$loan_tenure', '$terms_agreed', '$loan_type')";

if ($conn->query($sql_insert) === TRUE) {
    echo json_encode(["success" => true, "redirect" => "user_loans.php"]);
} else {
    echo json_encode(["success" => false, "error" => $conn->error]);
}

$conn->close();
?>
