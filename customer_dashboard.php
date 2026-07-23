<?php
session_start();
require_once 'auth_check.php';
check_role('customer');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bank";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get customer's loan details
$stmt = $conn->prepare("SELECT * FROM MyGuest WHERE customer_id = ?");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$loan = $stmt->get_result()->fetch_assoc();

// Get payment history
$stmt = $conn->prepare("SELECT * FROM transactions WHERE customer_id = ? ORDER BY payment_date DESC LIMIT 5");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$payments = $stmt->get_result();

// Calculate remaining balance and next payment
$total_paid = 0;
$stmt = $conn->prepare("SELECT SUM(amount) as total FROM transactions WHERE customer_id = ? AND status = 'COMPLETED'");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$total_paid = $stmt->get_result()->fetch_assoc()['total'] ?? 0;

$remaining_balance = $loan ? ($loan['loan'] - $total_paid) : 0;
$next_payment = $loan ? $loan['emi'] : 0;

$page_title = "Customer Dashboard - Banking System";
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row">
        <!-- Loan Overview Card -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body">
                    <h2 class="card-title mb-4" style="color: #ffff80;">Loan Overview</h2>
                    <?php if ($loan): ?>
                        <div class="loan-details">
                            <div class="detail-item">
                                <span class="label">Loan Amount:</span>
                                <span class="value">₹<?php echo number_format($loan['loan'], 2); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Interest Rate:</span>
                                <span class="value"><?php echo $loan['ir']; ?>% p.a.</span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Monthly EMI:</span>
                                <span class="value">₹<?php echo number_format($loan['emi'], 2); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Remaining Balance:</span>
                                <span class="value">₹<?php echo number_format($remaining_balance, 2); ?></span>
                            </div>
                            <div class="detail-item">
                                <span class="label">Next Payment Due:</span>
                                <span class="value"><?php echo date('M d, Y', strtotime('+1 month')); ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <p>No active loan found.</p>
                        <a href="apply_loan.php" class="btn btn-primary">Apply for a Loan</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Financial Tips Card -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body">
                    <h2 class="card-title mb-4" style="color: #ffff80;">Financial Tips</h2>
                    <div class="tips-container">
                        <div class="tip-item">
                            <i class="fas fa-lightbulb text-warning"></i>
                            <p>Pay your EMI before the due date to maintain a good credit score.</p>
                        </div>
                        <div class="tip-item">
                            <i class="fas fa-chart-line text-success"></i>
                            <p>Consider making extra payments to reduce your interest burden.</p>
                        </div>
                        <div class="tip-item">
                            <i class="fas fa-piggy-bank text-info"></i>
                            <p>Maintain an emergency fund equivalent to 3-6 months of expenses.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body">
                    <h2 class="card-title mb-4" style="color: #ffff80;">Recent Payments</h2>
                    <div class="table-responsive">
                        <table class="table table-dark">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Receipt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($payments->num_rows > 0): ?>
                                    <?php while ($payment = $payments->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                            <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $payment['status'] === 'COMPLETED' ? 'success' : 'warning'; ?>">
                                                    <?php echo ucfirst($payment['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="generate_receipt.php?id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center">No payment history found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body">
                    <h2 class="card-title mb-4" style="color: #ffff80;">Quick Actions</h2>
                    <div class="quick-actions">
                        <a href="myloans.php" class="btn btn-primary">
                            <i class="fas fa-list me-2"></i> View My Loans
                        </a>
                        <a href="payment_history.php" class="btn btn-primary">
                            <i class="fas fa-history me-2"></i> Payment History
                        </a>
                        <a href="apply_loan.php" class="btn btn-primary">
                            <i class="fas fa-file-signature me-2"></i> Apply for New Loan
                        </a>
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
    margin-bottom: 2rem;
}

.loan-details {
    display: grid;
    gap: 1rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
}

.label {
    color: #aaa;
}

.value {
    font-weight: 600;
    color: #fff;
}

.tips-container {
    display: grid;
    gap: 1rem;
}

.tip-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
}

.tip-item i {
    font-size: 1.5rem;
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

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.quick-actions .btn {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    font-weight: 500;
}
</style>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?> 
