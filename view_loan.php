<?php
require_once 'auth_check.php';
check_role(['admin', 'collector']);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bank";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get loan ID from URL
$loan_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$loan_id) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'collector_dashboard.php'));
    exit;
}

// Get loan details
$stmt = $conn->prepare("
    SELECT g.*, 
           c.username as customer_name,
           col.username as collector_name,
           c.email as customer_email,
           c.phone as customer_phone
    FROM MyGuest g
    JOIN users c ON g.customer_id = c.id
    LEFT JOIN users col ON g.collector_id = col.id
    WHERE g.id = ?
");
$stmt->bind_param("i", $loan_id);
$stmt->execute();
$result = $stmt->get_result();
$loan = $result->fetch_assoc();

if (!$loan) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'collector_dashboard.php'));
    exit;
}

// Get payment history
$stmt = $conn->prepare("
    SELECT t.*, u.username as collector_name
    FROM transactions t
    LEFT JOIN users u ON t.collector_id = u.id
    WHERE t.loan_id = ?
    ORDER BY t.payment_date DESC
");
$stmt->bind_param("i", $loan_id);
$stmt->execute();
$payments = $stmt->get_result();

// Calculate statistics
$total_paid = 0;
$total_pending = 0;
$last_payment_date = null;

while ($payment = $payments->fetch_assoc()) {
    if ($payment['status'] === 'COMPLETED') {
        $total_paid += $payment['amount'];
        if (!$last_payment_date) {
            $last_payment_date = $payment['payment_date'];
        }
    } else if ($payment['status'] === 'PENDING') {
        $total_pending += $payment['amount'];
    }
}

// Reset pointer for display
$payments->data_seek(0);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Loan Details - Banking System</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .section {
            background-color: #4d3319;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .section h2 {
            color: #ffff80;
            margin-top: 0;
        }
        .loan-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .loan-info-item {
            background-color: #862d59;
            padding: 15px;
            border-radius: 8px;
        }
        .loan-info-item h4 {
            color: #ffff80;
            margin: 0 0 10px 0;
        }
        .loan-info-item .value {
            color: #fff;
            font-size: 18px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            color: #fff;
        }
        .table th {
            background-color: #862d59;
            color: #ffff80;
        }
        .status-completed {
            color: #00ff00;
        }
        .status-pending {
            color: #ffff00;
        }
        .status-failed {
            color: #ff0000;
        }
        .button {
            background-color: #862d59;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
        }
        .button:hover {
            background-color: #6b2347;
        }
        .button.cancel {
            background-color: #666;
        }
        .button.cancel:hover {
            background-color: #444;
        }
        .customer-details {
            background-color: #862d59;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .customer-details h3 {
            color: #ffff80;
            margin-top: 0;
        }
        .customer-details p {
            color: #fff;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: #ffff80;">Loan Details</h1>

        <div class="section">
            <h2>Loan Information</h2>
            <div class="loan-info">
                <div class="loan-info-item">
                    <h4>Loan Amount</h4>
                    <div class="value">₹<?php echo number_format($loan['loanamount'], 2); ?></div>
                </div>
                <div class="loan-info-item">
                    <h4>Current Balance</h4>
                    <div class="value">₹<?php echo number_format($loan['closingbalance'], 2); ?></div>
                </div>
                <div class="loan-info-item">
                    <h4>Monthly EMI</h4>
                    <div class="value">₹<?php echo number_format($loan['monthlyemi'], 2); ?></div>
                </div>
                <div class="loan-info-item">
                    <h4>Next Payment Date</h4>
                    <div class="value"><?php echo date('Y-m-d', strtotime($loan['next_payment_date'])); ?></div>
                </div>
                <div class="loan-info-item">
                    <h4>Total Paid</h4>
                    <div class="value">₹<?php echo number_format($total_paid, 2); ?></div>
                </div>
                <div class="loan-info-item">
                    <h4>Pending Payments</h4>
                    <div class="value">₹<?php echo number_format($total_pending, 2); ?></div>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>Customer Information</h2>
            <div class="customer-details">
                <h3><?php echo htmlspecialchars($loan['customer_name']); ?></h3>
                <p>Email: <?php echo htmlspecialchars($loan['customer_email']); ?></p>
                <p>Phone: <?php echo htmlspecialchars($loan['customer_phone']); ?></p>
                <p>Address: <?php echo htmlspecialchars($loan['ad']); ?></p>
                <p>Pincode: <?php echo htmlspecialchars($loan['pc']); ?></p>
            </div>
        </div>

        <div class="section">
            <h2>Payment History</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Collected By</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($payment = $payments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('Y-m-d H:i', strtotime($payment['payment_date'])); ?></td>
                            <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                            <td><?php echo ucfirst(strtolower($payment['payment_type'])); ?></td>
                            <td class="status-<?php echo strtolower($payment['status']); ?>">
                                <?php echo ucfirst(strtolower($payment['status'])); ?>
                            </td>
                            <td><?php echo htmlspecialchars($payment['collector_name'] ?? 'System'); ?></td>
                            <td><?php echo htmlspecialchars($payment['remarks'] ?? ''); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <?php if ($payments->num_rows === 0): ?>
                <p style="text-align: center; color: #ffff80;">No payment history found.</p>
            <?php endif; ?>
        </div>

        <div class="button-group">
            <?php if ($_SESSION['role'] === 'collector'): ?>
                <a href="collect_payments.php?loan_id=<?php echo $loan_id; ?>" class="button">Collect Payment</a>
            <?php endif; ?>
            <a href="<?php echo $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'collector_dashboard.php'; ?>" class="button cancel">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
<?php
mysqli_close($conn);
?> 
