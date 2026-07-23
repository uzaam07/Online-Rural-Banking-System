<?php
session_start();
require_once 'auth_check.php';

$page_title = "View Notification - Banking System";
include 'includes/header.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get notification ID
$notification_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($notification_id > 0) {
    // Get notification details
    $stmt = $conn->prepare("
        SELECT * FROM notifications 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $notification_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $notification = $result->fetch_assoc();
        
        // Mark notification as read
        $stmt = $conn->prepare("UPDATE notifications SET `read` = 1 WHERE id = ?");
        $stmt->bind_param("i", $notification_id);
        $stmt->execute();
        
        // Get related data based on notification type
        $related_data = null;
        switch ($notification['type']) {
            case 'loan_approved':
            case 'loan_rejected':
                $stmt = $conn->prepare("
                    SELECT l.*, u.username as customer_name
                    FROM MyGuest l
                    JOIN users u ON l.user_id = u.id
                    WHERE l.loan_id = ?
                ");
                $stmt->bind_param("i", $notification['reference_id']);
                $stmt->execute();
                $related_data = $stmt->get_result()->fetch_assoc();
                break;
                
            case 'payment_due':
            case 'payment_received':
                $stmt = $conn->prepare("
                    SELECT t.*, l.loan_id, u.username as customer_name
                    FROM transactions t
                    JOIN MyGuest l ON t.loan_id = l.loan_id
                    JOIN users u ON l.user_id = u.id
                    WHERE t.transaction_id = ?
                ");
                $stmt->bind_param("i", $notification['reference_id']);
                $stmt->execute();
                $related_data = $stmt->get_result()->fetch_assoc();
                break;
        }
    } else {
        header('Location: dashboard.php');
        exit();
    }
} else {
    header('Location: dashboard.php');
    exit();
}
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Notification Details</h1>
    </div>
    
    <div class="card">
        <div class="notification-detail">
            <div class="notification-icon">
                <?php
                switch ($notification['type']) {
                    case 'loan_approved':
                        echo '<i class="fas fa-check-circle text-success fa-2x"></i>';
                        break;
                    case 'loan_rejected':
                        echo '<i class="fas fa-times-circle text-danger fa-2x"></i>';
                        break;
                    case 'payment_due':
                        echo '<i class="fas fa-exclamation-circle text-warning fa-2x"></i>';
                        break;
                    case 'payment_received':
                        echo '<i class="fas fa-money-bill-wave text-success fa-2x"></i>';
                        break;
                    default:
                        echo '<i class="fas fa-bell text-primary fa-2x"></i>';
                }
                ?>
            </div>
            
            <div class="notification-content">
                <p class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></p>
                <span class="notification-time">
                    <?php echo date('F d, Y g:i A', strtotime($notification['created_at'])); ?>
                </span>
            </div>
        </div>
        
        <?php if ($related_data): ?>
            <div class="notification-details">
                <h3>Related Information</h3>
                
                <?php if (in_array($notification['type'], ['loan_approved', 'loan_rejected'])): ?>
                    <div class="detail-group">
                        <label>Loan ID:</label>
                        <span>LOAN-<?php echo str_pad($related_data['loan_id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="detail-group">
                        <label>Customer:</label>
                        <span><?php echo htmlspecialchars($related_data['customer_name']); ?></span>
                    </div>
                    <div class="detail-group">
                        <label>Loan Amount:</label>
                        <span><?php echo number_format($related_data['amount'], 2); ?></span>
                    </div>
                    <div class="detail-group">
                        <label>Term:</label>
                        <span><?php echo $related_data['term']; ?> months</span>
                    </div>
                    <div class="detail-group">
                        <label>Interest Rate:</label>
                        <span><?php echo $related_data['interest_rate']; ?>%</span>
                    </div>
                    <div class="detail-group">
                        <label>Monthly EMI:</label>
                        <span><?php echo number_format($related_data['emi'], 2); ?></span>
                    </div>
                <?php elseif (in_array($notification['type'], ['payment_due', 'payment_received'])): ?>
                    <div class="detail-group">
                        <label>Transaction ID:</label>
                        <span>REC-<?php echo str_pad($related_data['transaction_id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="detail-group">
                        <label>Loan ID:</label>
                        <span>LOAN-<?php echo str_pad($related_data['loan_id'], 6, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="detail-group">
                        <label>Customer:</label>
                        <span><?php echo htmlspecialchars($related_data['customer_name']); ?></span>
                    </div>
                    <div class="detail-group">
                        <label>Amount:</label>
                        <span><?php echo number_format($related_data['amount'], 2); ?></span>
                    </div>
                    <div class="detail-group">
                        <label>Payment Date:</label>
                        <span><?php echo date('F d, Y', strtotime($related_data['payment_date'])); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="notification-actions">
            <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
            <?php if ($notification['type'] === 'payment_received'): ?>
                <a href="generate_receipt.php?id=<?php echo $related_data['transaction_id']; ?>" class="btn btn-secondary">
                    Download Receipt
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.notification-detail {
    display: flex;
    align-items: flex-start;
    margin-bottom: var(--spacing-lg);
}

.notification-icon {
    margin-right: var(--spacing);
}

.notification-content {
    flex: 1;
}

.notification-message {
    font-size: 1.125rem;
    margin-bottom: var(--spacing-xs);
}

.notification-time {
    color: var(--text-muted);
    font-size: 0.875rem;
}

.notification-details {
    background-color: var(--light-color);
    border-radius: var(--radius);
    padding: var(--spacing);
    margin-bottom: var(--spacing-lg);
}

.notification-details h3 {
    margin-bottom: var(--spacing);
    font-size: 1.125rem;
}

.detail-group {
    display: flex;
    margin-bottom: var(--spacing-sm);
}

.detail-group label {
    width: 150px;
    font-weight: 500;
    color: var(--text-muted);
}

.notification-actions {
    display: flex;
    gap: var(--spacing);
}
</style>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?> 
