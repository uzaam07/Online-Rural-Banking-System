<?php
session_start();
require_once 'auth_check.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Mark all notifications as read
$stmt = $conn->prepare("UPDATE notifications SET `read` = 1 WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();

// Redirect back to the previous page
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'dashboard.php';
header("Location: $redirect");
exit();
?> 
