<?php
session_start();
require_once 'auth_check.php';

$page_title = 'Dashboard - Banking System';
include 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user information
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get account information
$stmt = $conn->prepare("SELECT * FROM accounts WHERE user_id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$accounts = $stmt->get_result();

// Get recent transactions
$stmt = $conn->prepare("
    SELECT t.*, a.account_number 
    FROM transactions t 
    JOIN accounts a ON t.account_id = a.id 
    WHERE a.user_id = ? 
    ORDER BY t.transaction_date DESC 
    LIMIT 5
");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions = $stmt->get_result();

// Get statistics based on user role
$stats = [];
$user_role = $_SESSION['role'];
if ($user_role === 'admin') {
    // Total users
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    // Active loans
    $result = $conn->query("SELECT COUNT(*) as count FROM MyGuest WHERE status = 'approved'");
    $stats['active_loans'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    // Total collectors
    $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'collector'");
    $stats['total_collectors'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    // Recent payments
    $result = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
    $stats['recent_payments'] = $result ? $result->fetch_assoc()['count'] : 0;
} elseif ($user_role === 'collector') {
    // Assigned loans
    $result = $conn->query("SELECT COUNT(*) as count FROM MyGuest WHERE collector_id = $user_id");
    $stats['assigned_loans'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    // Collected payments
    $result = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE collector_id = $user_id");
    $stats['collected_payments'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    // Pending collections
    $result = $conn->query("SELECT COUNT(*) as count FROM MyGuest WHERE collector_id = $user_id AND status = 'approved'");
    $stats['pending_collections'] = $result ? $result->fetch_assoc()['count'] : 0;
} elseif ($user_role === 'customer') {
    // Active loans
    $result = $conn->query("SELECT COUNT(*) as count FROM MyGuest WHERE user_id = $user_id AND status = 'approved'");
    $stats['active_loans'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    // Total payments
    $result = $conn->query("SELECT COUNT(*) as count FROM transactions WHERE user_id = $user_id");
    $stats['total_payments'] = $result ? $result->fetch_assoc()['count'] : 0;
    
    // Next payment due
    $result = $conn->query("SELECT MIN(payment_date) as next_payment FROM transactions WHERE user_id = $user_id AND payment_date > NOW()");
    $stats['next_payment'] = $result ? ($result->fetch_assoc()['next_payment'] ?? 'No upcoming payments') : 'No upcoming payments';
}
?>

<div class="dashboard-container">
    <div class="row">
        <!-- User Profile Card -->
        <div class="col-md-4 mb-4">
            <div class="card profile-card">
                <div class="card-body text-center">
                    <div class="profile-image mb-3">
                        <i class="fas fa-user-circle fa-4x"></i>
                    </div>
                    <h3 class="card-title"><?php echo htmlspecialchars($user['username']); ?></h3>
                    <p class="text-muted"><?php echo ucfirst($user['role']); ?></p>
                    <div class="profile-stats">
                        <div class="stat-item">
                            <i class="fas fa-envelope"></i>
                            <span><?php echo htmlspecialchars($user['email']); ?></span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Summary -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Account Summary</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php while ($account = $accounts->fetch_assoc()): ?>
                            <div class="col-md-6 mb-3">
                                <div class="account-card">
                                    <div class="account-icon">
                                        <i class="fas fa-wallet"></i>
                                    </div>
                                    <div class="account-info">
                                        <h5>Account #<?php echo substr($account['account_number'], -4); ?></h5>
                                        <p class="balance">$<?php echo number_format($account['balance'], 2); ?></p>
                                        <span class="account-type"><?php echo ucfirst($account['account_type']); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Recent Transactions</h4>
                    <a href="transactions.php" class="btn btn-primary btn-sm">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Account</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($transaction = $transactions->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($transaction['transaction_date'])); ?></td>
                                        <td>****<?php echo substr($transaction['account_number'], -4); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $transaction['type'] === 'deposit' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($transaction['type']); ?>
                                            </span>
                                        </td>
                                        <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $transaction['status'] === 'completed' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($transaction['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard-stats">
        <?php foreach ($stats as $key => $value): ?>
            <div class="stat-card">
                <div class="stat-title"><?php echo ucwords(str_replace('_', ' ', $key)); ?></div>
                <div class="stat-value"><?php echo $value; ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php if ($user_role === 'admin'): ?>
        <div class="card">
            <h2>Quick Actions</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                <a href="manage_users.php" class="btn btn-primary">Manage Users</a>
                <a href="loan_approvals.php" class="btn btn-primary">Loan Approvals</a>
                <a href="assign_collector.php" class="btn btn-primary">Assign Collectors</a>
                <a href="reports.php" class="btn btn-primary">View Reports</a>
            </div>
        </div>
    <?php elseif ($user_role === 'collector'): ?>
        <div class="card">
            <h2>Quick Actions</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                <a href="collect_payments.php" class="btn btn-primary">Collect Payments</a>
                <a href="payment_history.php" class="btn btn-primary">Payment History</a>
            </div>
        </div>
    <?php elseif ($user_role === 'customer'): ?>
        <div class="card">
            <h2>Quick Actions</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 1rem;">
                <a href="view_loan.php" class="btn btn-primary">View Loans</a>
                <a href="payment_history.php" class="btn btn-primary">Payment History</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.dashboard-container {
    padding: 2rem;
}

.card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 15px;
    margin-bottom: 1.5rem;
}

.card-header {
    background: rgba(255, 255, 255, 0.05);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 1.5rem;
}

.card-title {
    color: var(--text-light);
    margin: 0;
}

.card-body {
    padding: 1.5rem;
}

.profile-card {
    text-align: center;
}

.profile-image {
    width: 100px;
    height: 100px;
    margin: 0 auto 1.5rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.profile-image i {
    color: var(--text-light);
}

.profile-stats {
    margin-top: 1.5rem;
}

.stat-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    color: var(--text-light);
}

.stat-item i {
    margin-right: 0.5rem;
    color: var(--accent-color);
}

.account-card {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

.account-card:hover {
    transform: translateY(-5px);
    background: rgba(255, 255, 255, 0.1);
}

.account-icon {
    width: 50px;
    height: 50px;
    background: var(--accent-color);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 1rem;
}

.account-icon i {
    color: white;
    font-size: 1.5rem;
}

.account-info h5 {
    color: var(--text-light);
    margin: 0 0 0.5rem;
    font-size: 1rem;
}

.balance {
    color: var(--accent-color);
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0 0 0.5rem;
}

.account-type {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.875rem;
}

.table {
    color: var(--text-light);
}

.table th {
    border-top: none;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.7);
    font-weight: 500;
}

.table td {
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    vertical-align: middle;
}

.badge {
    padding: 0.5rem 0.75rem;
    font-weight: 500;
}

.btn-primary {
    background: var(--accent-color);
    border: none;
    padding: 0.5rem 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: #d13a52;
    transform: translateY(-2px);
}

.text-muted {
    color: rgba(255, 255, 255, 0.7) !important;
}
</style>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?> 
