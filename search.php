<?php
session_start();
require_once 'role_check.php';

$page_title = "Search - Banking System";
include 'includes/header.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Initialize variables
$search_results = [];
$search_term = '';
$error = '';

// Handle search
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = mysqli_real_escape_string($conn, $_GET['search']);
    
    // Search query based on user role
    if ($_SESSION['role'] === 'admin') {
        $query = "SELECT g.*, u.username, u.email 
                 FROM MyGuest g 
                 JOIN users u ON g.customer_id = u.id 
                 WHERE u.username LIKE ? OR u.email LIKE ? OR g.loanamount LIKE ?";
        $stmt = $conn->prepare($query);
        $search_param = "%$search_term%";
        $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    } elseif ($_SESSION['role'] === 'collector') {
        $query = "SELECT g.*, u.username, u.email 
                 FROM MyGuest g 
                 JOIN users u ON g.customer_id = u.id 
                 WHERE (u.username LIKE ? OR u.email LIKE ? OR g.loanamount LIKE ?) 
                 AND g.collector_id = ?";
        $stmt = $conn->prepare($query);
        $search_param = "%$search_term%";
        $stmt->bind_param("sssi", $search_param, $search_param, $search_param, $_SESSION['user_id']);
    } else {
        $error = "Unauthorized access";
    }

    if (isset($stmt)) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
        $stmt->close();
    }
}
?>

<div class="main-content">
    <div class="search-container">
        <div class="search-header">
            <h1 class="search-title">Search Loans</h1>
            <form method="GET" class="search-form">
                <div class="input-group">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search_term); ?>" 
                           class="form-control" placeholder="Search by username, email, or loan amount...">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i> Search
                    </button>
                </div>
            </form>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (!empty($search_results)): ?>
            <div class="search-results">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Loan Amount</th>
                                <th>Monthly EMI</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($search_results as $result): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($result['username']); ?></td>
                                    <td><?php echo htmlspecialchars($result['email']); ?></td>
                                    <td>₹<?php echo number_format($result['loanamount'], 2); ?></td>
                                    <td>₹<?php echo number_format($result['monthlyemi'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $result['status'] === 'APPROVED' ? 'success' : 
                                                ($result['status'] === 'PENDING' ? 'warning' : 'danger'); 
                                        ?>">
                                            <?php echo ucfirst(strtolower($result['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="view_loan.php?id=<?php echo $result['id']; ?>" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php elseif (!empty($search_term)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> No results found for "<?php echo htmlspecialchars($search_term); ?>"
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.main-content {
    margin-top: 100px;
}

.search-container {
    padding: 2rem;
}

.search-header {
    margin-bottom: 2rem;
}

.search-title {
    color: var(--text-light);
    margin-bottom: 1.5rem;
}

.search-form {
    max-width: 600px;
}

.search-form .input-group {
    gap: 0.5rem;
}

.search-results {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    padding: 1.5rem;
    backdrop-filter: blur(10px);
}

.table {
    color: var(--text-light);
    margin: 0;
}

.table th {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    font-weight: 500;
    padding: 1rem;
}

.table td {
    padding: 1rem;
    vertical-align: middle;
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

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.alert {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: var(--text-light);
    padding: 1rem;
    border-radius: 10px;
    margin-bottom: 1rem;
}

.alert-danger {
    background: rgba(220, 53, 69, 0.1);
    border-color: rgba(220, 53, 69, 0.2);
    color: #dc3545;
}

.alert-info {
    background: rgba(23, 162, 184, 0.1);
    border-color: rgba(23, 162, 184, 0.2);
    color: #17a2b8;
}
</style>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?>
