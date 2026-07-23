<?php
session_start();
require_once 'auth_check.php';
check_role('collector');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bank";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get date range from request
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get payment statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_transactions,
        SUM(CASE WHEN status = 'COMPLETED' THEN amount ELSE 0 END) as total_collected,
        SUM(CASE WHEN status = 'PENDING' THEN amount ELSE 0 END) as total_pending,
        AVG(CASE WHEN status = 'COMPLETED' THEN amount ELSE NULL END) as avg_payment
    FROM transactions 
    WHERE collector_id = ? AND payment_date BETWEEN ? AND ?
");
if (!$stmt) { die('Prepare failed (stats): ' . $conn->error); }
$stmt->bind_param("iss", $_SESSION['user_id'], $start_date, $end_date);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get recent transactions
$stmt = $conn->prepare("
    SELECT t.*, u.username as customer_name, l.loanamount as loan_amount
    FROM transactions t
    JOIN users u ON t.customer_id = u.id
    JOIN MyGuest l ON t.loan_id = l.id
    WHERE t.collector_id = ? AND t.payment_date BETWEEN ? AND ?
    ORDER BY t.payment_date DESC
");
if (!$stmt) { die('Prepare failed (transactions): ' . $conn->error); }
$stmt->bind_param("iss", $_SESSION['user_id'], $start_date, $end_date);
$stmt->execute();
$transactions = $stmt->get_result();

include 'includes/header.php';
?>

<div class="main-content container mt-5">
    <div class="row">
        <!-- Statistics Cards -->
        <div class="col-md-3 mb-4">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body text-center">
                    <h3 class="card-title" style="color: #ffff80;">Total Transactions</h3>
                    <h2 class="display-4"><?php echo number_format($stats['total_transactions']); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body text-center">
                    <h3 class="card-title" style="color: #ffff80;">Total Collected</h3>
                    <h2 class="display-4">₹<?php echo number_format($stats['total_collected'], 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body text-center">
                    <h3 class="card-title" style="color: #ffff80;">Pending Amount</h3>
                    <h2 class="display-4">₹<?php echo number_format($stats['total_pending'], 2); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-4">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body text-center">
                    <h3 class="card-title" style="color: #ffff80;">Average Payment</h3>
                    <h2 class="display-4">₹<?php echo number_format($stats['avg_payment'], 2); ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="form-control">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter me-2"></i> Apply Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body">
                    <h2 class="card-title mb-4" style="color: #ffff80;">Payment History</h2>
                    <div class="table-responsive">
                        <table class="table table-dark">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Loan Amount</th>
                                    <th>Payment Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($transactions->num_rows > 0): ?>
                                    <?php while ($transaction = $transactions->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($transaction['payment_date'])); ?></td>
                                            <td><?php echo htmlspecialchars($transaction['customer_name']); ?></td>
                                            <td>₹<?php echo number_format($transaction['loan_amount'], 2); ?></td>
                                            <td>₹<?php echo number_format($transaction['amount'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $transaction['status'] === 'COMPLETED' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($transaction['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="generate_receipt.php?id=<?php echo $transaction['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download"></i> Receipt
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6">
                                            <div class="no-payments">
                                                <div class="no-payments-icon">
                                                    <i class="fas fa-receipt"></i>
                                                </div>
                                                <h2>No Payments Yet</h2>
                                                <p>You haven't collected any payments yet. Payments you collect will appear here.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    border: none;
    border-radius: 15px;
}

.card-title {
    font-size: 1.2rem;
    margin-bottom: 1rem;
}

.display-4 {
    font-size: 2rem;
    font-weight: 600;
    margin: 0;
}

.form-control {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #fff;
}

.form-control:focus {
    background: rgba(255, 255, 255, 0.15);
    border-color: #e94560;
    box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.25);
    color: #fff;
}

.form-label {
    color: #aaa;
}

.btn-primary {
    background: #e94560;
    border: none;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: #d13a52;
    transform: translateY(-2px);
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

.no-payments {
    text-align: center;
    padding: 3rem 1rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    backdrop-filter: blur(10px);
}
.no-payments-icon {
    font-size: 4rem;
    color: var(--text-light);
    opacity: 0.5;
    margin-bottom: 1rem;
}
.no-payments h2 {
    color: var(--text-light);
    margin-bottom: 1rem;
}
.no-payments p {
    color: var(--text-light);
    opacity: 0.8;
    margin: 0;
}

.main-content {
    margin-top: 100px;
}
</style>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?> 
