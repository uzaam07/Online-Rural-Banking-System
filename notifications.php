<?php
session_start();
require_once 'auth_check.php';

$page_title = "Notifications - Banking System";
include 'includes/header.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get user's notifications
$stmt = $conn->prepare("
    SELECT n.*, 
           CASE 
               WHEN n.type = 'transaction' THEN t.type
               WHEN n.type = 'account' THEN a.account_type
               ELSE n.type
           END as subtype
    FROM notifications n
    LEFT JOIN transactions t ON n.reference_id = t.id AND n.type = 'transaction'
    LEFT JOIN accounts a ON n.reference_id = a.id AND n.type = 'account'
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$notifications = $stmt->get_result();

// Mark notifications as read
if (isset($_POST['mark_read'])) {
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    header("Location: notifications.php");
    exit();
}
?>

<div class="notifications-container">
    <div class="notifications-header">
        <h1 class="notifications-title">Notifications</h1>
        <form method="POST" class="mark-read-form">
            <button type="submit" name="mark_read" class="btn btn-primary">
                <i class="fas fa-check-double me-2"></i> Mark All as Read
            </button>
        </form>
    </div>

    <div class="notifications-list">
        <?php if ($notifications->num_rows > 0): ?>
            <?php while ($notification = $notifications->fetch_assoc()): ?>
                <div class="notification-item <?php echo $notification['is_read'] ? 'read' : 'unread'; ?>">
                    <div class="notification-icon">
                        <?php
                        $icon = match($notification['type']) {
                            'transaction' => 'fa-exchange-alt',
                            'account' => 'fa-wallet',
                            'security' => 'fa-shield-alt',
                            'system' => 'fa-cog',
                            default => 'fa-bell'
                        };
                        ?>
                        <i class="fas <?php echo $icon; ?>"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-header">
                            <h5 class="notification-title"><?php echo htmlspecialchars($notification['title']); ?></h5>
                            <span class="notification-time">
                                <?php echo date('M d, Y H:i', strtotime($notification['created_at'])); ?>
                            </span>
                        </div>
                        <p class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></p>
                        <?php if ($notification['subtype']): ?>
                            <span class="notification-badge">
                                <?php echo ucfirst($notification['subtype']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-notifications">
                <i class="fas fa-bell-slash"></i>
                <p>No notifications yet</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.notifications-container {
    padding: 2rem;
}

.notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.notifications-title {
    color: var(--text-light);
    margin: 0;
}

.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.notification-item {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 15px;
    padding: 1.5rem;
    display: flex;
    gap: 1.5rem;
    transition: all 0.3s ease;
}

.notification-item:hover {
    transform: translateY(-2px);
    background: rgba(255, 255, 255, 0.15);
}

.notification-item.unread {
    border-left: 4px solid var(--accent-color);
}

.notification-icon {
    width: 48px;
    height: 48px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    color: var(--accent-color);
}

.notification-content {
    flex: 1;
}

.notification-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 0.5rem;
}

.notification-title {
    color: var(--text-light);
    margin: 0;
    font-size: 1.1rem;
}

.notification-time {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.875rem;
}

.notification-message {
    color: rgba(255, 255, 255, 0.8);
    margin: 0 0 0.5rem 0;
    line-height: 1.5;
}

.notification-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    color: var(--text-light);
    font-size: 0.875rem;
}

.no-notifications {
    text-align: center;
    padding: 3rem;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 15px;
    color: rgba(255, 255, 255, 0.6);
}

.no-notifications i {
    font-size: 3rem;
    margin-bottom: 1rem;
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

@media (max-width: 768px) {
    .notifications-header {
        flex-direction: column;
        gap: 1rem;
    }

    .notification-item {
        flex-direction: column;
        gap: 1rem;
    }

    .notification-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
}
</style>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?> 
