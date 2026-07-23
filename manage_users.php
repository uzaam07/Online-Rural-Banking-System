<?php
session_start();
require_once 'auth_check.php';
check_role('admin');

$page_title = 'Manage Users - Banking System';
include 'includes/header.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle user status updates
if (isset($_POST['update_status'])) {
    $user_id = $_POST['user_id'];
    $new_status = $_POST['new_status'];
    
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $user_id);
    $stmt->execute();
}

// Get filter parameters
$role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query with proper error handling
$query = "SELECT * FROM users WHERE 1=1";

if ($role_filter !== 'all') {
    $query .= " AND role = ?";
}

$query .= " ORDER BY created_at DESC";

// Prepare and execute the query
$stmt = $conn->prepare($query);
if ($stmt) {
    if ($role_filter !== 'all') {
        $stmt->bind_param("s", $role_filter);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("Query preparation failed: " . $conn->error);
}
?>

<div class="manage-users-container">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h2 class="card-title">Manage Users</h2>
            <a href="add_user.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Add New User
            </a>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select" id="role" name="role">
                        <option value="all" <?php echo $role_filter === 'all' ? 'selected' : ''; ?>>All Roles</option>
                        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="collector" <?php echo $role_filter === 'collector' ? 'selected' : ''; ?>>Collector</option>
                        <option value="customer" <?php echo $role_filter === 'customer' ? 'selected' : ''; ?>>Customer</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn filter-button">
                        <i class="fas fa-filter"></i> Apply Filter
                    </button>
                </div>
            </form>

            <!-- Users Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($user = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $user['role'] === 'admin' ? 'danger' : 
                                            ($user['role'] === 'collector' ? 'primary' : 'success'); 
                                    ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No users found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.manage-users-container {
    padding-top: 2rem;
}

.card-header {
    padding: 1.5rem;
    margin-bottom: 1rem;
}

.card-title {
    margin: 0;
    padding: 0.5rem 0;
}

.btn-group .btn {
    padding: 0.4rem 0.8rem;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: var(--accent-color);
    border: none;
    transition: all 0.3s ease;
}

.btn-group .btn:hover {
    background: #d13a52;
    transform: translateY(-2px);
}

.btn-group .btn i {
    font-size: 0.9rem;
}
</style>

<?php
if (isset($stmt)) {
    $stmt->close();
}
mysqli_close($conn);
include 'includes/footer.php';
?> 
