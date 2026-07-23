<?php
session_start();
require_once 'auth_check.php';

$page_title = "Transactions - Banking System";
include 'includes/header.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get user's accounts
$stmt = $conn->prepare("
    SELECT a.*, t.name as account_type_name 
    FROM accounts a
    JOIN account_types t ON a.type_id = t.id
    WHERE a.user_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$accounts = $stmt->get_result();

// Get date range from request
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$account_id = isset($_GET['account_id']) ? $_GET['account_id'] : '';

// Build transaction query
$query = "
    SELECT t.*, a.account_number, at.name as account_type
    FROM transactions t
    JOIN accounts a ON t.account_id = a.id
    JOIN account_types at ON a.type_id = at.id
    WHERE a.user_id = ? AND t.created_at BETWEEN ? AND ?
";
$params = [$_SESSION['user_id'], $start_date, $end_date];
$types = "iss";

if ($account_id) {
    $query .= " AND t.account_id = ?";
    $params[] = $account_id;
    $types .= "i";
}

$query .= " ORDER BY t.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$transactions = $stmt->get_result();
?>

<div class="transactions-container">
    <div class="transactions-header">
        <h1 class="transactions-title">Transaction History</h1>
        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="input-group">
                    <select name="account_id" class="form-select">
                        <option value="">All Accounts</option>
                        <?php while ($account = $accounts->fetch_assoc()): ?>
                            <option value="<?php echo $account['id']; ?>" <?php echo $account_id == $account['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($account['account_number'] . ' - ' . $account['account_type_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>" class="form-control">
                    <span class="input-group-text">to</span>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>" class="form-control">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-2"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Account</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($transaction = $transactions->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y H:i', strtotime($transaction['created_at'])); ?></td>
                                <td>
                                    <div class="account-info">
                                        <span class="account-number"><?php echo substr($transaction['account_number'], -4); ?></span>
                                        <span class="account-type"><?php echo htmlspecialchars($transaction['account_type']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $transaction['type'] === 'deposit' ? 'success' : 
                                            ($transaction['type'] === 'withdrawal' ? 'warning' : 'info'); 
                                    ?>">
                                        <?php echo ucfirst($transaction['type']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($transaction['description']); ?></td>
                                <td class="amount <?php echo $transaction['type'] === 'deposit' ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $transaction['type'] === 'deposit' ? '+' : '-'; ?>
                                    $<?php echo number_format($transaction['amount'], 2); ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $transaction['status'] === 'completed' ? 'success' : 
                                            ($transaction['status'] === 'pending' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($transaction['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.transactions-container {
    padding: 2rem;
}

.transactions-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.transactions-title {
    color: var(--text-light);
    margin: 0;
}

.filters {
    background: rgba(255, 255, 255, 0.1);
    padding: 1rem;
    border-radius: 10px;
    backdrop-filter: blur(10px);
}

.filter-form .input-group {
    gap: 0.5rem;
}

.card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 15px;
}

.card-body {
    padding: 1.5rem;
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

.account-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.account-number {
    font-weight: 500;
}

.account-type {
    font-size: 0.875rem;
    opacity: 0.8;
}

.amount {
    font-weight: 500;
}

.text-success {
    color: #28a745 !important;
}

.text-danger {
    color: #dc3545 !important;
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

.bg-info {
    background: rgba(23, 162, 184, 0.2) !important;
    color: #17a2b8;
}

.bg-danger {
    background: rgba(220, 53, 69, 0.2) !important;
    color: #dc3545;
}

.form-control, .form-select {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: var(--text-light);
    padding: 0.75rem 1rem;
}

.form-control:focus, .form-select:focus {
    background: rgba(255, 255, 255, 0.15);
    border-color: var(--accent-color);
    box-shadow: 0 0 0 0.2rem rgba(233, 69, 96, 0.25);
    color: var(--text-light);
}

.input-group-text {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: var(--text-light);
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
    .transactions-header {
        flex-direction: column;
        gap: 1rem;
    }

    .filters {
        width: 100%;
    }

    .filter-form .input-group {
        flex-direction: column;
    }
}
</style>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?> 
