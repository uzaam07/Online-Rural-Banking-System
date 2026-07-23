<?php
session_start();
require_once 'auth_check.php';

$page_title = "Settings - Banking System";
include 'includes/header.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle form submissions
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format";
        } else {
            // Check if email is already taken
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $_SESSION['user_id']);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $error = "Email is already taken";
            } else {
                // Update profile
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $name, $email, $_SESSION['user_id']);
                if ($stmt->execute()) {
                    $success = "Profile updated successfully";
                    $user['name'] = $name;
                    $user['email'] = $email;
                } else {
                    $error = "Error updating profile";
                }
            }
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if (!password_verify($current_password, $result['password'])) {
            $error = "Current password is incorrect";
        } elseif (strlen($new_password) < 8) {
            $error = "New password must be at least 8 characters long";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match";
        } else {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
            if ($stmt->execute()) {
                $success = "Password changed successfully";
            } else {
                $error = "Error changing password";
            }
        }
    } elseif (isset($_POST['update_notifications'])) {
        $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
        $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
        $transaction_alerts = isset($_POST['transaction_alerts']) ? 1 : 0;
        $security_alerts = isset($_POST['security_alerts']) ? 1 : 0;
        
        // Update notification preferences
        $stmt = $conn->prepare("
            UPDATE users 
            SET email_notifications = ?, 
                sms_notifications = ?, 
                transaction_alerts = ?, 
                security_alerts = ? 
            WHERE id = ?
        ");
        $stmt->bind_param("iiiii", $email_notifications, $sms_notifications, $transaction_alerts, $security_alerts, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $success = "Notification preferences updated successfully";
            $user['email_notifications'] = $email_notifications;
            $user['sms_notifications'] = $sms_notifications;
            $user['transaction_alerts'] = $transaction_alerts;
            $user['security_alerts'] = $security_alerts;
        } else {
            $error = "Error updating notification preferences";
        }
    }
}
?>

<div class="settings-container">
    <div class="settings-header">
        <h1 class="settings-title">Account Settings</h1>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Profile Settings -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-user me-2"></i> Profile Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="settings-form">
                        <div class="form-group mb-4">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group mb-4">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Password Settings -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-lock me-2"></i> Password Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="settings-form">
                        <div class="form-group mb-4">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        <div class="form-group mb-4">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="form-text">Password must be at least 8 characters long</div>
                        </div>
                        <div class="form-group mb-4">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary w-100">
                            <i class="fas fa-key me-2"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-bell me-2"></i> Notification Settings
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="settings-form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-4">
                                    <input type="checkbox" class="form-check-input" id="email_notifications" name="email_notifications" <?php echo $user['email_notifications'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="email_notifications">Email Notifications</label>
                                </div>
                                <div class="form-check mb-4">
                                    <input type="checkbox" class="form-check-input" id="sms_notifications" name="sms_notifications" <?php echo $user['sms_notifications'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="sms_notifications">SMS Notifications</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-4">
                                    <input type="checkbox" class="form-check-input" id="transaction_alerts" name="transaction_alerts" <?php echo $user['transaction_alerts'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="transaction_alerts">Transaction Alerts</label>
                                </div>
                                <div class="form-check mb-4">
                                    <input type="checkbox" class="form-check-input" id="security_alerts" name="security_alerts" <?php echo $user['security_alerts'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="security_alerts">Security Alerts</label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" name="update_notifications" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i> Update Notification Preferences
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.settings-container {
    padding: 2rem;
}

.settings-header {
    margin-bottom: 2rem;
}

.settings-title {
    color: var(--text-light);
    margin: 0;
}

.card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 15px;
    height: 100%;
}

.card-header {
    background: rgba(255, 255, 255, 0.05);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 1.5rem;
}

.card-title {
    color: var(--text-light);
    margin: 0;
    display: flex;
    align-items: center;
}

.card-body {
    padding: 1.5rem;
}

.form-label {
    color: var(--text-light);
    font-weight: 500;
    margin-bottom: 0.5rem;
}

.form-control {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: var(--text-light);
    padding: 0.75rem 1rem;
}

.form-control:focus {
    background: rgba(255, 255, 255, 0.15);
    border-color: var(--accent-color);
    box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.25);
    color: var(--text-light);
}

.form-text {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.form-check-input {
    background-color: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.form-check-input:checked {
    background-color: var(--accent-color);
    border-color: var(--accent-color);
}

.form-check-label {
    color: var(--text-light);
    cursor: pointer;
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

.alert {
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
}

.alert-success {
    background: rgba(40, 167, 69, 0.1);
    border: 1px solid rgba(40, 167, 69, 0.2);
    color: #28a745;
}

.alert-danger {
    background: rgba(220, 53, 69, 0.1);
    border: 1px solid rgba(220, 53, 69, 0.2);
    color: #dc3545;
}

@media (max-width: 768px) {
    .settings-container {
        padding: 1rem;
    }
}
</style>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?> 
