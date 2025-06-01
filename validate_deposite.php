<?php
// Database connection (adjust with your credentials)
$host = 'localhost'; // Change to your database host
$dbname = 'verde_bank_db';    // Change to your database name
$username = 'root';  // Change to your database username
$password = '';      // Change to your database password

try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $dbname :" . $e->getMessage());
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form data
    $account_number = filter_input(INPUT_POST, 'account_number', FILTER_SANITIZE_NUMBER_INT);
    $account_name = filter_input(INPUT_POST, 'account_name', FILTER_SANITIZE_STRING);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $credit_to = filter_input(INPUT_POST, 'credit_to', FILTER_SANITIZE_STRING);

    // Get selected payment method from localStorage (or a default value)
    // For this example, we'll assume it's passed from the form as an additional hidden field or AJAX request
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : "No payment method selected";

    // Validate form data
    if (empty($account_number) || empty($account_name) || empty($amount) || empty($credit_to)) {
        die("All fields are required.");
    }

    // Prepare SQL query to insert deposit
    $sql = "INSERT INTO deposits (account_number, account_name, amount, credit_to, payment_method, deposit_date) 
            VALUES (:account_number, :account_name, :amount, :credit_to, :payment_method, NOW())";

    $stmt = $pdo->prepare($sql);

    // Bind the parameters to the query
    $stmt->bindParam(':account_number', $account_number, PDO::PARAM_INT);
    $stmt->bindParam(':account_name', $account_name, PDO::PARAM_STR);
    $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
    $stmt->bindParam(':credit_to', $credit_to, PDO::PARAM_STR);
    $stmt->bindParam(':payment_method', $payment_method, PDO::PARAM_STR);

    // Execute the statement and insert the data
    if ($stmt->execute()) {
        echo "Deposit successfully processed!";
    } else {
        echo "Error processing deposit.";
    }
}
?>

?>