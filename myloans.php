<?php
session_start();
require_once 'role_check.php';

// Only allow customers to access this page
if ($_SESSION['role'] !== 'customer') {
    header('Location: index.php');
    exit();
}

$page_title = "My Loans - Banking System";
include 'includes/header.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get customer's loans
$stmt = $conn->prepare("
    SELECT g.*, u.username, u.email 
    FROM MyGuest g 
    JOIN users u ON g.customer_id = u.id 
    WHERE g.customer_id = ?
    ORDER BY g.created_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$loans = $stmt->get_result();
?>

<div class="main-content myloans-container">
    <div class="myloans-header">
        <h1 class="myloans-title">My Loans</h1>
        <a href="apply_loan.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i> Apply for New Loan
        </a>
    </div>

    <?php if ($loans->num_rows > 0): ?>
        <div class="loans-grid">
            <?php while ($loan = $loans->fetch_assoc()): ?>
                <div class="loan-card">
                    <div class="loan-header">
                        <h3>Loan #<?php echo $loan['id']; ?></h3>
                        <span class="badge bg-<?php 
                            echo $loan['status'] === 'APPROVED' ? 'success' : 
                                ($loan['status'] === 'PENDING' ? 'warning' : 'danger'); 
                        ?>">
                            <?php echo ucfirst(strtolower($loan['status'])); ?>
                        </span>
                    </div>
                    <div class="loan-details">
                        <div class="detail-item">
                            <span class="label">Loan Amount:</span>
                            <span class="value">₹<?php echo number_format($loan['loanamount'], 2); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Monthly EMI:</span>
                            <span class="value">₹<?php echo number_format($loan['monthlyemi'], 2); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Interest Rate:</span>
                            <span class="value"><?php echo $loan['interest_rate']; ?>%</span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Applied On:</span>
                            <span class="value"><?php echo date('M d, Y', strtotime($loan['created_at'])); ?></span>
                        </div>
                    </div>
                    <div class="loan-actions">
                        <a href="view_loan.php?id=<?php echo $loan['id']; ?>" class="btn btn-info">
                            <i class="fas fa-eye me-1"></i> View Details
                        </a>
                        <?php if ($loan['status'] === 'APPROVED'): ?>
                            <a href="make_payment.php?loan_id=<?php echo $loan['id']; ?>" class="btn btn-success">
                                <i class="fas fa-money-bill me-1"></i> Make Payment
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-loans">
            <div class="no-loans-icon">
                <i class="fas fa-file-invoice"></i>
            </div>
            <h2>No Loans Yet</h2>
            <p>You haven't applied for any loans yet. Click the button above to apply for your first loan!</p>
        </div>
    <?php endif; ?>
</div>

<style>
.main-content {
    margin-top: 100px;
}

.myloans-container {
    padding: 2rem;
}

.myloans-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.myloans-title {
    color: var(--text-light);
    margin: 0;
}

.loans-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.loan-card {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    padding: 1.5rem;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.loan-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.loan-header h3 {
    color: var(--text-light);
    margin: 0;
    font-size: 1.25rem;
}

.loan-details {
    margin-bottom: 1.5rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    color: var(--text-light);
}

.detail-item .label {
    opacity: 0.8;
}

.detail-item .value {
    font-weight: 500;
}

.loan-actions {
    display: flex;
    gap: 0.75rem;
}

.no-loans {
    text-align: center;
    padding: 3rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 15px;
    backdrop-filter: blur(10px);
}

.no-loans-icon {
    font-size: 4rem;
    color: var(--text-light);
    opacity: 0.5;
    margin-bottom: 1rem;
}

.no-loans h2 {
    color: var(--text-light);
    margin-bottom: 1rem;
}

.no-loans p {
    color: var(--text-light);
    opacity: 0.8;
    margin: 0;
}

.badge {
    padding: 0.5rem 1rem;
    font-weight: 500;
}

.bg-success {
    background: rgba(40, 167, 69, 0.2) !important;
    color: #28a745;
}

.bg-warning {
    background: rgba(255, 193, 7, 0.2) !important;
    color: #ffc107;
}

.bg-danger {
    background: rgba(220, 53, 69, 0.2) !important;
    color: #dc3545;
}

.btn {
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-primary {
    background: var(--accent-color);
    border: none;
}

.btn-primary:hover {
    background: #d13a52;
    transform: translateY(-2px);
}

.btn-info {
    background: rgba(23, 162, 184, 0.2);
    border: none;
    color: #17a2b8;
}

.btn-info:hover {
    background: rgba(23, 162, 184, 0.3);
    transform: translateY(-2px);
}

.btn-success {
    background: rgba(40, 167, 69, 0.2);
    border: none;
    color: #28a745;
}

.btn-success:hover {
    background: rgba(40, 167, 69, 0.3);
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .myloans-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }

    .loans-grid {
        grid-template-columns: 1fr;
    }

    .loan-actions {
        flex-direction: column;
    }
}
</style>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?> 
