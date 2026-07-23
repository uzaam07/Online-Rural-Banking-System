<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Function to check if user has required role
function check_role($required_role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
        header("Location: dashboard.php");
        exit();
    }
}

// Function to check if user has any of the required roles
function check_roles($required_roles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $required_roles)) {
        header("Location: dashboard.php");
        exit();
    }
}
?> 
