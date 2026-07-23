<?php
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) die("Connection failed: " . mysqli_connect_error());
$new_hash = password_hash("admin123", PASSWORD_DEFAULT);
$sql = "UPDATE users SET password='$new_hash' WHERE username='admin'";
if (mysqli_query($conn, $sql)) {
    echo "Admin password reset to admin123!";
} else {
    echo "Error: " . mysqli_error($conn);
}
mysqli_close($conn);
?>
