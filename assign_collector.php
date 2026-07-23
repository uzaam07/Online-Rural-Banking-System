<?php
session_start();
require_once 'auth_check.php';

// Check if user is admin
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

$page_title = "Assign Collectors - Banking System";
include 'includes/header.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle collector assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loan_id = $_POST['loan_id'];
    $collector_id = $_POST['collector_id'];
    
    $stmt = $conn->prepare("UPDATE MyGuest SET collector_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $collector_id, $loan_id);
    
    if ($stmt->execute()) {
        $success_message = "Collector assigned successfully.";
    } else {
        $error_message = "Error assigning collector: " . $conn->error;
    }
}

// Get unassigned loans
$query = "SELECT l.*, u.username, u.email 
          FROM MyGuest l 
          JOIN users u ON l.user_id = u.id 
          WHERE l.status = 'approved' AND l.collector_id IS NULL 
          ORDER BY l.created_at DESC";
$loans_result = $conn->query($query);

// Get available collectors
$query = "SELECT id, username, email 
          FROM users 
          WHERE role = 'collector' AND status = 'active' 
          ORDER BY username";
$collectors_result = $conn->query($query);
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Assign Collectors</h1>
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
                        <th>Current Balance</th>
                        <th>Monthly EMI</th>
                        <th>Approved Date</th>
                        <th>Assign Collector</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($loans_result->num_rows > 0): ?>
                        <?php while ($loan = $loans_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div><?php echo htmlspecialchars($loan['username']); ?></div>
                                    <div style="font-size: 0.875rem; color: var(--secondary-color);">
                                        <?php echo htmlspecialchars($loan['email']); ?>
                                    </div>
                                </td>
                                <td>₹<?php echo number_format($loan['loan'], 2); ?></td>
                                <td>₹<?php echo number_format($loan['closingbalance'], 2); ?></td>
                                <td>₹<?php echo number_format($loan['emi'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($loan['created_at'])); ?></td>
                                <td>
                                    <form method="POST" class="assign-form">
                                        <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                        <select name="collector_id" class="form-input" required>
                                            <option value="">Select Collector</option>
                                            <?php 
                                            $collectors_result->data_seek(0);
                                            while ($collector = $collectors_result->fetch_assoc()): 
                                            ?>
                                                <option value="<?php echo $collector['id']; ?>">
                                                    <?php echo htmlspecialchars($collector['username']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">Assign</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">No unassigned loans found</td>
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
