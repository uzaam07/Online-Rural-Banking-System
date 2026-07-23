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

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loan_id = filter_input(INPUT_POST, 'loan_id', FILTER_VALIDATE_INT);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $payment_type = filter_input(INPUT_POST, 'payment_type', FILTER_SANITIZE_STRING);
    $remarks = filter_input(INPUT_POST, 'remarks', FILTER_SANITIZE_STRING);

    if (!$loan_id || !$amount || !$payment_type) {
        $error = "Please fill in all required fields";
    } else {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("INSERT INTO transactions (loan_id, customer_id, collector_id, amount, payment_type, remarks) SELECT id, customer_id, ?, ?, ?, ? FROM MyGuest WHERE id = ?");
            if (!$stmt) { throw new Exception("Prepare failed: " . $conn->error); }
            $stmt->bind_param("idssi", $_SESSION['user_id'], $amount, $payment_type, $remarks, $loan_id);
            $stmt->execute();
            $stmt = $conn->prepare("UPDATE MyGuest SET closingbalance = closingbalance - ? WHERE id = ?");
            if (!$stmt) { throw new Exception("Prepare failed: " . $conn->error); }
            $stmt->bind_param("di", $amount, $loan_id);
            $stmt->execute();
            $conn->commit();
            $success = "Payment recorded successfully";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error recording payment: " . $e->getMessage();
        }
    }
}

$stmt = $conn->prepare("SELECT m.*, u.username as customer_name FROM MyGuest m JOIN users u ON m.customer_id = u.id WHERE m.collector_id = ? AND m.closingbalance > 0");
if (!$stmt) { die("Prepare failed: " . $conn->error); }
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$loans = $stmt->get_result();

include 'includes/header.php';
?>
<link rel="stylesheet" href="css/style.css">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body">
                    <h1 class="display-5 mb-4" style="color: #ffff80;">Collect Payments</h1>
                    <?php if ($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>
                    <?php if ($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                    <form method="POST" class="mb-4">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label for="loan_id" class="form-label">Select Loan</label>
                                <select name="loan_id" id="loan_id" class="form-select" required>
                                    <option value="">Choose...</option>
                                    <?php while ($loan = $loans->fetch_assoc()): ?>
                                        <option value="<?php echo $loan['id']; ?>">
                                            <?php echo htmlspecialchars($loan['customer_name']) . " (₹" . number_format($loan['closingbalance'], 2) . ")"; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="amount" class="form-label">Amount</label>
                                <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label for="payment_type" class="form-label">Payment Type</label>
                                <select name="payment_type" id="payment_type" class="form-select" required>
                                    <option value="EMI">EMI</option>
                                    <option value="EARLY_PAYMENT">Early Payment</option>
                                    <option value="LATE_PAYMENT">Late Payment</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="remarks" class="form-label">Remarks</label>
                                <input type="text" name="remarks" id="remarks" class="form-control">
                            </div>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary">Record Payment</button>
                            <a href="payment_history.php" class="btn btn-secondary ms-2">View Payment History</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.card {
    background: #22223b;
    border-radius: 12px;
    color: #fff;
}
</style>
<?php
mysqli_close($conn);
include 'includes/footer.php';
?> 
