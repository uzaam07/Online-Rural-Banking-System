<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bank";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Validate and sanitize inputs
$firstname = filter_input(INPUT_POST, 'fname', FILTER_SANITIZE_STRING);
$amount = filter_input(INPUT_POST, 'amt', FILTER_VALIDATE_FLOAT);
$confirm_amount = filter_input(INPUT_POST, 'camt', FILTER_VALIDATE_FLOAT);
$closingdate = filter_input(INPUT_POST, 'closingdate', FILTER_SANITIZE_STRING);
$closingbalance = filter_input(INPUT_POST, 'closingbalance', FILTER_VALIDATE_FLOAT);

// Validate all required fields
if (!$firstname || !$amount || !$confirm_amount || !$closingdate || !$closingbalance) {
    die("All fields are required");
}

// Validate amount matches confirmation
if ($amount !== $confirm_amount) {
    die("Amount and confirmation amount do not match");
}

// Validate amount is positive
if ($amount <= 0) {
    die("Amount must be greater than 0");
}

// Calculate new closing balance
$new_closing_balance = $closingbalance - $amount;

// Use prepared statement to update
$stmt = $conn->prepare("UPDATE MyGuest SET closingbalance = ?, closingdate = ? WHERE fname = ?");
$stmt->bind_param("dss", $new_closing_balance, $closingdate, $firstname);

if ($stmt->execute()) {
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Successful</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/stylegood.css">
    <p><font size="25" color="#ffff80">Record updated successfully!</font></p>
</head>
<body>
    <a class="button" href="index.html">HOME</a>
</body>
</html>';
} else {
    echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Failed</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/stylegood.css">
    <p><font size="25" color="#ffff80">Error updating record: ' . htmlspecialchars($stmt->error) . '</font></p>
</head>
<body>
    <a class="button" href="index.html">HOME</a>
</body>
</html>';
}

$stmt->close();
mysqli_close($conn);
?>
