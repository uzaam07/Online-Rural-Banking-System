<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bank";

// Create connection
$conn = mysqli_connect($servername, $username, $password);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected to MySQL successfully<br>";

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully or already exists<br>";
} else {
    echo "Error creating database: " . mysqli_error($conn) . "<br>";
}

// Select the database
mysqli_select_db($conn, $dbname);

// Create users table if not exists
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('admin', 'collector', 'customer') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($conn, $sql)) {
    echo "Users table created successfully or already exists<br>";
} else {
    echo "Error creating users table: " . mysqli_error($conn) . "<br>";
}

// Check if admin user exists
$sql = "SELECT * FROM users WHERE username = 'admin'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    // Create admin user
    $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password, email, role, status) 
            VALUES ('admin', '$admin_password', 'admin@example.com', 'admin', 'active')";
    
    if (mysqli_query($conn, $sql)) {
        echo "Admin user created successfully<br>";
    } else {
        echo "Error creating admin user: " . mysqli_error($conn) . "<br>";
    }
} else {
    echo "Admin user already exists<br>";
}

mysqli_close($conn);
?> 
