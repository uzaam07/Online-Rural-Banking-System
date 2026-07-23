<?php
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) die("Connection failed: " . mysqli_connect_error());
$admin_password = password_hash("admin123", PASSWORD_DEFAULT);
$sql = "INSERT IGNORE INTO users (username, password, email, role, status) VALUES ('admin', '$admin_password', 'admin@example.com', 'admin', 'active')";
if (mysqli_query($conn, $sql)) {
    echo "Admin user created!";
} else {
    echo "Error: " . mysqli_error($conn);
}
mysqli_close($conn);
?>
