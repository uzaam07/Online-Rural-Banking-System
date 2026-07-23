<?php
session_start();
require_once 'auth_check.php';

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$page_title = "Loan Approvals - Banking System";
include 'includes/header.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle loan approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loan_id = $_POST['loan_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $status = 'approved';
        $message = "Loan has been approved successfully.";
    } else {
        $status = 'rejected';
        $message = "Loan has been rejected.";
    }
    
    $stmt = $conn->prepare("UPDATE MyGuest SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $loan_id);
    
    if ($stmt->execute()) {
        $success_message = $message;
    } else {
        $error_message = "Error updating loan status: " . $conn->error;
    }
}

// Get pending loans
$query = "SELECT l.*, u.username, u.email 
          FROM MyGuest l 
          JOIN users u ON l.user_id = u.id 
          WHERE l.status = 'pending' 
          ORDER BY l.created_at DESC";
$result = $conn->query($query);
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Loan Approvals</h1>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Loan Amount</th>
                        <th>Interest Rate</th>
                        <th>Term (Months)</th>
                        <th>Monthly EMI</th>
                        <th>Applied Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($loan = $result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div><?php echo htmlspecialchars($loan['username']); ?></div>
                                    <div style="font-size: 0.875rem; color: var(--secondary-color);">
                                        <?php echo htmlspecialchars($loan['email']); ?>
                                    </div>
                                </td>
                                <td>₹<?php echo number_format($loan['loan'], 2); ?></td>
                                <td><?php echo $loan['interest_rate']; ?>%</td>
                                <td><?php echo $loan['term_months']; ?></td>
                                <td>₹<?php echo number_format($loan['emi'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($loan['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-primary" style="margin-right: 0.5rem;">Approve</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn" style="background-color: var(--danger-color); color: white;">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No pending loan applications</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?> 
