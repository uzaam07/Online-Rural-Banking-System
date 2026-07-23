<?php
session_start();
require_once 'auth_check.php';

// Check if user is collector
if ($_SESSION['role'] !== 'collector') {
    header("Location: dashboard.php");
    exit();
}

$page_title = "Record Payment - Banking System";
include 'includes/header.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$loan_id = $_GET['loan_id'] ?? null;
if (!$loan_id) {
    header("Location: collector_dashboard.php");
    exit();
}

// Get loan details
$query = "SELECT l.*, u.username as customer_name, u.email as customer_email 
          FROM MyGuest l 
          JOIN users u ON l.user_id = u.id 
          WHERE l.id = ? AND l.collector_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $loan_id, $_SESSION['user_id']);
$stmt->execute();
$loan = $stmt->get_result()->fetch_assoc();

if (!$loan) {
    header("Location: collector_dashboard.php");
    exit();
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = $_POST['amount'];
    $payment_date = $_POST['payment_date'];
    $payment_type = $_POST['payment_type'];
    $status = 'completed';
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Insert payment record
        $stmt = $conn->prepare("INSERT INTO transactions (loan_id, collector_id, amount, payment_date, payment_type, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iidsss", $loan_id, $_SESSION['user_id'], $amount, $payment_date, $payment_type, $status);
        $stmt->execute();
        
        // Update loan balance
        $new_balance = $loan['closingbalance'] - $amount;
        $stmt = $conn->prepare("UPDATE MyGuest SET closingbalance = ? WHERE id = ?");
        $stmt->bind_param("di", $new_balance, $loan_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        $success_message = "Payment recorded successfully.";
        
        // Refresh loan details
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $loan_id, $_SESSION['user_id']);
        $stmt->execute();
        $loan = $stmt->get_result()->fetch_assoc();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_message = "Error recording payment: " . $e->getMessage();
    }
}
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Record Payment</h1>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Loan Details</h2>
        <div class="loan-details">
            <div class="detail-item">
                <div class="detail-label">Customer</div>
                <div class="detail-value"><?php echo htmlspecialchars($loan['customer_name']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Email</div>
                <div class="detail-value"><?php echo htmlspecialchars($loan['customer_email']); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Loan Amount</div>
                <div class="detail-value">₹<?php echo number_format($loan['loan'], 2); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Current Balance</div>
                <div class="detail-value">₹<?php echo number_format($loan['closingbalance'], 2); ?></div>
            </div>
            <div class="detail-item">
                <div class="detail-label">Monthly EMI</div>
                <div class="detail-value">₹<?php echo number_format($loan['emi'], 2); ?></div>
            </div>
        </div>
    </div>

    <div class="card">
        <h2>Record New Payment</h2>
        <form method="POST" class="form">
            <div class="form-group">
                <label for="amount" class="form-label">Payment Amount</label>
                <input type="number" id="amount" name="amount" class="form-input" step="0.01" min="0" max="<?php echo $loan['closingbalance']; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="payment_date" class="form-label">Payment Date</label>
                <input type="date" id="payment_date" name="payment_date" class="form-input" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="payment_type" class="form-label">Payment Type</label>
                <select id="payment_type" name="payment_type" class="form-input" required>
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="upi">UPI</option>
                    <option value="cheque">Cheque</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Record Payment</button>
                <a href="collector_dashboard.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?> 
