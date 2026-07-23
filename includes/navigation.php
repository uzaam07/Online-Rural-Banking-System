<?php
if (!isset($_SESSION)) {
    session_start();
}

$user_role = $_SESSION['role'] ?? '';
$user_id = $_SESSION['user_id'] ?? '';

// Navigation component
$current_page = basename($_SERVER['PHP_SELF']);

// Get role-specific dashboard URL
$dashboard_url = match($_SESSION['role']) {
    'admin' => 'admin_dashboard.php',
    'collector' => 'collector_dashboard.php',
    'customer' => 'customer_dashboard.php',
    default => 'login.php'
};
?>

<nav class="navbar">
    <div class="navbar-brand">
        <a href="<?php echo $dashboard_url; ?>" class="navbar-logo">Banking System</a>
    </div>
    
    <div class="navbar-menu">
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="admin_dashboard.php" class="navbar-item <?php echo $current_page === 'admin_dashboard.php' ? 'active' : ''; ?>">
                Dashboard
            </a>
            <a href="loan_approvals.php" class="navbar-item <?php echo $current_page === 'loan_approvals.php' ? 'active' : ''; ?>">
                Loan Approvals
            </a>
            <a href="assign_collector.php" class="navbar-item <?php echo $current_page === 'assign_collector.php' ? 'active' : ''; ?>">
                Assign Collectors
            </a>
            <a href="reports.php" class="navbar-item <?php echo $current_page === 'reports.php' ? 'active' : ''; ?>">
                Reports
            </a>
        <?php elseif ($_SESSION['role'] === 'collector'): ?>
            <a href="collector_dashboard.php" class="navbar-item <?php echo $current_page === 'collector_dashboard.php' ? 'active' : ''; ?>">
                Dashboard
            </a>
            <a href="collect_payments.php" class="navbar-item <?php echo $current_page === 'collect_payments.php' ? 'active' : ''; ?>">
                Collect Payments
            </a>
            <a href="collector_payment_history.php" class="navbar-item <?php echo $current_page === 'collector_payment_history.php' ? 'active' : ''; ?>">
                Payment History
            </a>
        <?php elseif ($_SESSION['role'] === 'customer'): ?>
            <a href="customer_dashboard.php" class="navbar-item <?php echo $current_page === 'customer_dashboard.php' ? 'active' : ''; ?>">
                Dashboard
            </a>
            <a href="view_loan.php" class="navbar-item <?php echo $current_page === 'view_loan.php' ? 'active' : ''; ?>">
                View Loans
            </a>
            <a href="payment_history.php" class="navbar-item <?php echo $current_page === 'payment_history.php' ? 'active' : ''; ?>">
                Payment History
            </a>
        <?php endif; ?>
    </div>
    
    <div class="navbar-end">
        <div class="navbar-item has-dropdown">
            <a class="navbar-link">
                <i class="fas fa-user me-2"></i><?php echo ucfirst($_SESSION['name'] ?? 'User'); ?>
            </a>
            <div class="navbar-dropdown">
                <a href="profile.php" class="navbar-item">
                    <i class="fas fa-user-cog me-2"></i>Profile
                </a>
                <a href="logout.php" class="navbar-item">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<style>
.navbar {
    background: #1a1a2e;
    padding: 1rem 2rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.navbar-brand {
    display: flex;
    align-items: center;
}

.navbar-logo {
    color: #ffff80;
    font-size: 1.5rem;
    font-weight: 600;
    text-decoration: none;
}

.navbar-menu {
    display: flex;
    gap: 1.5rem;
}

.navbar-item {
    color: #fff;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    transition: all 0.3s ease;
}

.navbar-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffff80;
}

.navbar-item.active {
    background: #e94560;
    color: #fff;
}

.navbar-end {
    position: relative;
}

.navbar-link {
    color: #fff;
    text-decoration: none;
    padding: 0.5rem 1rem;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.navbar-link:hover {
    background: rgba(255, 255, 255, 0.1);
}

.navbar-dropdown {
    position: absolute;
    right: 0;
    top: 100%;
    background: #1a1a2e;
    border-radius: 5px;
    padding: 0.5rem;
    min-width: 200px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: none;
}

.navbar-item.has-dropdown:hover .navbar-dropdown {
    display: block;
}

.navbar-dropdown .navbar-item {
    display: block;
    padding: 0.75rem 1rem;
    color: #fff;
    text-decoration: none;
    transition: all 0.3s ease;
}

.navbar-dropdown .navbar-item:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffff80;
}
</style> 
