<?php
session_start();
require_once 'auth_check.php';

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$type = $_GET['type'] ?? '';
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $type . '_report_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');

if ($type === 'loans') {
    // Get loan data
    $query = "SELECT 
                l.id,
                u.username as customer_name,
                l.loan as amount,
                l.interest_rate,
                l.term_months,
                l.emi,
                l.status,
                l.created_at,
                c.username as collector_name
              FROM MyGuest l
              LEFT JOIN users u ON l.user_id = u.id
              LEFT JOIN users c ON l.collector_id = c.id
              WHERE l.created_at BETWEEN ? AND ?
              ORDER BY l.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Output Excel headers
    echo "Loan ID\tCustomer\tAmount\tInterest Rate\tTerm (Months)\tEMI\tStatus\tCreated Date\tCollector\n";
    
    // Output data rows
    while ($row = $result->fetch_assoc()) {
        echo implode("\t", [
            $row['id'],
            $row['customer_name'],
            $row['amount'],
            $row['interest_rate'] . '%',
            $row['term_months'],
            $row['emi'],
            ucfirst($row['status']),
            date('Y-m-d', strtotime($row['created_at'])),
            $row['collector_name'] ?? 'Not Assigned'
        ]) . "\n";
    }
} else if ($type === 'collections') {
    // Get collection data
    $query = "SELECT 
                t.id,
                u.username as customer_name,
                t.amount,
                t.payment_date,
                t.status,
                c.username as collector_name
              FROM transactions t
              JOIN MyGuest l ON t.loan_id = l.id
              JOIN users u ON l.user_id = u.id
              LEFT JOIN users c ON t.collector_id = c.id
              WHERE t.payment_date BETWEEN ? AND ?
              ORDER BY t.payment_date DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Output Excel headers
    echo "Transaction ID\tCustomer\tAmount\tPayment Date\tStatus\tCollector\n";
    
    // Output data rows
    while ($row = $result->fetch_assoc()) {
        echo implode("\t", [
            $row['id'],
            $row['customer_name'],
            $row['amount'],
            date('Y-m-d', strtotime($row['payment_date'])),
            ucfirst($row['status']),
            $row['collector_name'] ?? 'Not Assigned'
        ]) . "\n";
    }
}

mysqli_close($conn);
?> 
