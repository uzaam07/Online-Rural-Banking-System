<?php
session_start();
require_once 'auth_check.php';
check_role('admin');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bank";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get total users count
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) as total_customers,
    SUM(CASE WHEN role = 'collector' THEN 1 ELSE 0 END) as total_collectors
    FROM users");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->execute();
$user_stats = $stmt->get_result()->fetch_assoc();

// Get loan statistics
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total_loans,
    SUM(loanamount) as total_amount
    FROM MyGuest");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->execute();
$loan_stats = $stmt->get_result()->fetch_assoc();

// Get recent transactions
$stmt = $conn->prepare("SELECT t.*, u.username as customer_name, c.username as collector_name 
                       FROM transactions t 
                       JOIN users u ON t.customer_id = u.id 
                       LEFT JOIN users c ON t.collector_id = c.id 
                       ORDER BY t.payment_date DESC LIMIT 5");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->execute();
$recent_transactions = $stmt->get_result();

$page_title = "Admin Dashboard - Banking System";
include 'includes/header.php';
?>

<div class="container mt-5">
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body">
                    <h5 class="card-title" style="color: #ffff80;">Total Users</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0"><?php echo number_format($user_stats['total_users']); ?></h2>
                            <p class="text-muted mb-0">
                                <?php echo number_format($user_stats['total_customers']); ?> customers
                            </p>
                        </div>
                        <i class="fas fa-users fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body">
                    <h5 class="card-title" style="color: #ffff80;">Total Collectors</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0"><?php echo number_format($user_stats['total_collectors']); ?></h2>
                            <p class="text-muted mb-0">Active collectors</p>
                        </div>
                        <i class="fas fa-user-tie fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body">
                    <h5 class="card-title" style="color: #ffff80;">Total Loans</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0"><?php echo number_format($loan_stats['total_loans']); ?></h2>
                            <p class="text-muted mb-0">Active loans</p>
                        </div>
                        <i class="fas fa-file-invoice-dollar fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body">
                    <h5 class="card-title" style="color: #ffff80;">Total Amount</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">₹<?php echo number_format($loan_stats['total_amount'], 2); ?></h2>
                            <p class="text-muted mb-0">Loans disbursed</p>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
        <div class="card-body">
            <h2 class="card-title mb-4" style="color: #ffff80;">Recent Transactions</h2>
            <div class="table-responsive">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Collector</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_transactions->num_rows > 0): ?>
                            <?php while ($transaction = $recent_transactions->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('M d, Y', strtotime($transaction['payment_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($transaction['collector_name'] ?? 'N/A'); ?></td>
                                    <td>₹<?php echo number_format($transaction['amount'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $transaction['status'] === 'COMPLETED' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($transaction['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No recent transactions</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 15px;
    margin-bottom: 2rem;
}

.table {
    margin: 0;
}

.table th {
    border-top: none;
    font-weight: 500;
}

.badge {
    padding: 0.5rem 1rem;
    font-weight: 500;
}

.text-muted {
    color: #aaa !important;
}
</style>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?> 
