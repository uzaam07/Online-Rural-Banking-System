<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bank";

// Test database connection
echo "<h2>Testing Database Connection</h2>";
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Database connection successful!<br><br>";

// Test tables
echo "<h2>Testing Database Tables</h2>";
$tables = array('users', 'MyGuest', 'transactions');
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "Table '$table' exists and contains $count records<br>";
    } else {
        echo "Error checking table '$table': " . $conn->error . "<br>";
    }
}
echo "<br>";

// Test user roles
echo "<h2>Testing User Roles</h2>";
$roles = array('admin', 'collector', 'customer');
foreach ($roles as $role) {
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = '$role'");
    if ($result) {
        $count = $result->fetch_assoc()['count'];
        echo "Found $count users with role '$role'<br>";
    }
}
echo "<br>";

// Test active loans
echo "<h2>Testing Active Loans</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM MyGuest WHERE closingbalance > 0");
if ($result) {
    $count = $result->fetch_assoc()['count'];
    echo "Found $count active loans<br>";
}
echo "<br>";

// Test recent transactions
echo "<h2>Testing Recent Transactions</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
if ($result) {
    $count = $result->fetch_assoc()['count'];
    echo "Found $count transactions in the last 30 days<br>";
}
echo "<br>";

// Test collector assignments
echo "<h2>Testing Collector Assignments</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM MyGuest WHERE collector_id IS NOT NULL");
if ($result) {
    $count = $result->fetch_assoc()['count'];
    echo "Found $count loans assigned to collectors<br>";
}
echo "<br>";

mysqli_close($conn);
?> 
