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

// Get collector's assigned customers
$stmt = $conn->prepare("SELECT u.*, g.loanamount, g.monthlyemi, g.closingbalance, g.closingdate FROM MyGuest g JOIN users u ON g.customer_id = u.id WHERE g.collector_id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$customers = $stmt->get_result();

// Get today's collections
$stmt = $conn->prepare("SELECT COUNT(*) as count, SUM(amount) as total 
                       FROM transactions 
                       WHERE collector_id = ? AND DATE(payment_date) = CURDATE()");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$today_stats = $stmt->get_result()->fetch_assoc();

// Get pending collections
$stmt = $conn->prepare("SELECT COUNT(*) as count, SUM(monthlyemi) as total FROM MyGuest WHERE collector_id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$pending_stats = $stmt->get_result()->fetch_assoc();

$page_title = "Collector Dashboard - Banking System";
include 'includes/header.php';
?>

<div class="container mt-5">
    <!-- Payment History Quick Link -->
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-end">
            <a href="collector_payment_history.php" class="btn btn-primary" style="min-width:200px; font-weight:600;">
                <i class="fas fa-history me-2"></i> Payment History
            </a>
        </div>
    </div>
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body">
                    <h5 class="card-title" style="color: #ffff80;">Today's Collections</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">₹<?php echo number_format($today_stats['total'] ?? 0, 2); ?></h2>
                            <p class="text-muted mb-0"><?php echo $today_stats['count'] ?? 0; ?> transactions</p>
                        </div>
                        <i class="fas fa-money-bill-wave fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body">
                    <h5 class="card-title" style="color: #ffff80;">Pending Collections</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">₹<?php echo number_format($pending_stats['total'] ?? 0, 2); ?></h2>
                            <p class="text-muted mb-0"><?php echo $pending_stats['count'] ?? 0; ?> customers</p>
                        </div>
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assigned Customers -->
    <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
        <div class="card-body">
            <h2 class="card-title mb-4" style="color: #ffff80;">Assigned Customers</h2>
            <div class="table-responsive">
                <table class="table table-dark">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Loan Amount</th>
                            <th>Monthly EMI</th>
                            <th>Closing Balance</th>
                            <th>Closing Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($customers->num_rows > 0): ?>
                            <?php while ($customer = $customers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                    <td>₹<?php echo number_format($customer['loanamount'], 2); ?></td>
                                    <td>₹<?php echo number_format($customer['monthlyemi'], 2); ?></td>
                                    <td>₹<?php echo number_format($customer['closingbalance'], 2); ?></td>
                                    <td><?php echo $customer['closingdate']; ?></td>
                                    <td>
                                        <a href="collect_payments.php?customer_id=<?php echo $customer['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-money-bill"></i> Collect
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No customers assigned</td>
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

.text-muted {
    color: #aaa !important;
}
</style>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?> 
