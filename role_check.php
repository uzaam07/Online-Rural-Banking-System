<?php
if (!isset($_SESSION)) {
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
        // Redirect to role-specific dashboard
        switch ($_SESSION['role']) {
            case 'admin':
                header("Location: admin_dashboard.php");
                break;
            case 'collector':
                header("Location: collector_dashboard.php");
                break;
            case 'customer':
                header("Location: customer_dashboard.php");
                break;
            default:
                header("Location: login.php");
        }
        exit();
    }
}
?> 
