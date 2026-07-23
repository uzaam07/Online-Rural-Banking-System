<?php
// Get unread notifications
$stmt = $conn->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? AND `read` = 0 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$notifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get total unread count
$stmt = $conn->prepare("
    SELECT COUNT(*) as count 
    FROM notifications 
    WHERE user_id = ? AND `read` = 0
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$unread_count = $stmt->get_result()->fetch_assoc()['count'];
?>

<div class="navbar-item has-dropdown">
    <a class="navbar-link">
        <i class="fas fa-bell"></i>
        <?php if ($unread_count > 0): ?>
            <span class="notification-badge"><?php echo $unread_count; ?></span>
        <?php endif; ?>
    </a>
    
    <div class="navbar-dropdown notification-dropdown">
        <div class="notification-header">
            <h3 class="notification-title">Notifications</h3>
            <?php if ($unread_count > 0): ?>
                <a href="mark_all_read.php" class="notification-action">Mark all as read</a>
            <?php endif; ?>
        </div>
        
        <?php if (empty($notifications)): ?>
            <div class="notification-empty">
                <p>No new notifications</p>
            </div>
        <?php else: ?>
            <div class="notification-list">
                <?php foreach ($notifications as $notification): ?>
                    <a href="view_notification.php?id=<?php echo $notification['id']; ?>" 
                       class="notification-item <?php echo $notification['read'] ? '' : 'unread'; ?>">
                        <div class="notification-icon">
                            <?php
                            switch ($notification['type']) {
                                case 'loan_approved':
                                    echo '<i class="fas fa-check-circle text-success"></i>';
                                    break;
                                case 'loan_rejected':
                                    echo '<i class="fas fa-times-circle text-danger"></i>';
                                    break;
                                case 'payment_due':
                                    echo '<i class="fas fa-exclamation-circle text-warning"></i>';
                                    break;
                                case 'payment_received':
                                    echo '<i class="fas fa-money-bill-wave text-success"></i>';
                                    break;
                                default:
                                    echo '<i class="fas fa-bell text-primary"></i>';
                            }
                            ?>
                        </div>
                        <div class="notification-content">
                            <p class="notification-message"><?php echo htmlspecialchars($notification['message']); ?></p>
                            <span class="notification-time">
                                <?php
                                $time = strtotime($notification['created_at']);
                                $now = time();
                                $diff = $now - $time;
                                
                                if ($diff < 60) {
                                    echo 'Just now';
                                } elseif ($diff < 3600) {
                                    echo floor($diff / 60) . ' minutes ago';
                                } elseif ($diff < 86400) {
                                    echo floor($diff / 3600) . ' hours ago';
                                } else {
                                    echo date('M d, Y', $time);
                                }
                                ?>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <?php if ($unread_count > 5): ?>
                <div class="notification-footer">
                    <a href="notifications.php" class="notification-action">View all notifications</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: var(--danger-color);
    color: white;
    font-size: 0.75rem;
    padding: 0.25rem 0.5rem;
    border-radius: 1rem;
    min-width: 1.5rem;
    text-align: center;
}

.notification-dropdown {
    width: 320px;
    padding: 0;
}

.notification-header {
    padding: var(--spacing);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.notification-title {
    font-size: 1rem;
    margin: 0;
}

.notification-action {
    font-size: 0.875rem;
    color: var(--primary-color);
}

.notification-empty {
    padding: var(--spacing);
    text-align: center;
    color: var(--text-muted);
}

.notification-list {
    max-height: 400px;
    overflow-y: auto;
}

.notification-item {
    display: flex;
    padding: var(--spacing);
    border-bottom: 1px solid var(--border-color);
    text-decoration: none;
    color: var(--text-color);
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: var(--light-color);
}

.notification-item.unread {
    background-color: var(--light-color);
}

.notification-icon {
    margin-right: var(--spacing);
    font-size: 1.25rem;
}

.notification-content {
    flex: 1;
}

.notification-message {
    margin: 0 0 var(--spacing-xs);
    font-size: 0.875rem;
}

.notification-time {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.notification-footer {
    padding: var(--spacing);
    border-top: 1px solid var(--border-color);
    text-align: center;
}

.text-success { color: var(--success-color); }
.text-danger { color: var(--danger-color); }
.text-warning { color: var(--warning-color); }
.text-primary { color: var(--primary-color); }
</style> 
