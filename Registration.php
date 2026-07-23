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

// Validate and sanitize input
$fname = filter_input(INPUT_POST, 'fname', FILTER_SANITIZE_STRING);
$lname = filter_input(INPUT_POST, 'lname', FILTER_SANITIZE_STRING);
$aadharno = filter_input(INPUT_POST, 'aadharno', FILTER_SANITIZE_NUMBER_INT);
$isdt = filter_input(INPUT_POST, 'isdt', FILTER_SANITIZE_STRING);
$closingdate = $isdt;
$loan = filter_input(INPUT_POST, 'loan', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$closingbalance = $loan;
$ip = filter_input(INPUT_POST, 'ip', FILTER_SANITIZE_NUMBER_INT);
$ir = filter_input(INPUT_POST, 'ir', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$ph = filter_input(INPUT_POST, 'ph', FILTER_SANITIZE_NUMBER_INT);
$ad = filter_input(INPUT_POST, 'ad', FILTER_SANITIZE_STRING);
$pc = filter_input(INPUT_POST, 'pc', FILTER_SANITIZE_NUMBER_INT);

// Validate required fields
if (!$fname || !$lname || !$aadharno || !$isdt || !$loan || !$ip || !$ir || !$email || !$ph || !$ad || !$pc) {
    die("All fields are required");
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Invalid email format");
}

// Validate Aadhar number (12 digits)
if (strlen($aadharno) != 12 || !is_numeric($aadharno)) {
    die("Invalid Aadhar number");
}

// Use prepared statement
$stmt = $conn->prepare("INSERT INTO MyGuest (fname, lname, aadharno, isdt, loan, ip, ir, email, ph, ad, pc, closingdate, closingbalance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssisdiisssiis", $fname, $lname, $aadharno, $isdt, $loan, $ip, $ir, $email, $ph, $ad, $pc, $closingdate, $closingbalance);

if ($stmt->execute()) {
    echo '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Success</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/stylegood.css">
    <p><font size="25" color="#ffff80">You have successfully Registered for Loan...Thank You</font></p>
</head>
<body>
    <a class="button" href="index.html">HOME</a>
</body>
</html>';
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
mysqli_close($conn);
?>
