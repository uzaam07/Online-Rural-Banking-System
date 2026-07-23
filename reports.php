<?php
session_start();
// require_once 'auth_check.php';
require_once 'role_check.php';

$page_title = "Reports - Banking System";
include 'includes/header.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get date range from request
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get transaction statistics (using payment_type)
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_transactions,
        SUM(CASE WHEN payment_type = 'EMI' THEN amount ELSE 0 END) as total_emi,
        SUM(CASE WHEN payment_type = 'EARLY_PAYMENT' THEN amount ELSE 0 END) as total_early,
        SUM(CASE WHEN payment_type = 'LATE_PAYMENT' THEN amount ELSE 0 END) as total_late
    FROM transactions 
    WHERE payment_date BETWEEN ? AND ?
");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get user statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
        SUM(CASE WHEN role = 'collector' THEN 1 ELSE 0 END) as collector_count,
        SUM(CASE WHEN role = 'customer' THEN 1 ELSE 0 END) as customer_count,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users
    FROM users
");
$stmt->execute();
$user_stats = $stmt->get_result()->fetch_assoc();

// Get recent transactions
$stmt = $conn->prepare("
    SELECT t.*, u.username 
    FROM transactions t
    JOIN users u ON t.customer_id = u.id
    WHERE t.payment_date BETWEEN ? AND ?
    ORDER BY t.payment_date DESC
    LIMIT 10
");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$recent_transactions = $stmt->get_result();

// Get monthly transaction data for the graph
$monthly_data = [];
$stmt = $conn->prepare("
    SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as total
    FROM transactions
    WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY month
    ORDER BY month ASC
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $monthly_data[] = $row;
}

// Get payment type distribution for pie chart
$stmt = $conn->prepare("
    SELECT 
        payment_type,
        COUNT(*) as count,
        SUM(amount) as total_amount
    FROM transactions
    WHERE payment_date BETWEEN ? AND ?
    GROUP BY payment_type
");
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$payment_distribution = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<div class="reports-container">
    <div class="reports-header">
        <h1 class="reports-title">System Reports</h1>
        <div class="date-filter">
            <form method="GET" class="date-range-form">
                <div class="input-group">
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

    <div class="row">
        <!-- Transaction Statistics -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-chart-line me-2"></i> Transaction Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($stats['total_transactions']); ?></div>
                            <div class="stat-label">Total Transactions</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">₹<?php echo number_format($stats['total_emi'], 2); ?></div>
                            <div class="stat-label">Total EMI Payments</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">₹<?php echo number_format($stats['total_early'], 2); ?></div>
                            <div class="stat-label">Total Early Payments</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">₹<?php echo number_format($stats['total_late'], 2); ?></div>
                            <div class="stat-label">Total Late Payments</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Statistics -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">
                        <i class="fas fa-users me-2"></i> User Statistics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($user_stats['total_users']); ?></div>
                            <div class="stat-label">Total Users</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($user_stats['admin_count']); ?></div>
                            <div class="stat-label">Administrators</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($user_stats['collector_count']); ?></div>
                            <div class="stat-label">Collectors</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($user_stats['customer_count']); ?></div>
                            <div class="stat-label">Customers</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value"><?php echo number_format($user_stats['active_users']); ?></div>
                            <div class="stat-label">Active Users</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Transactions and Payment Distribution -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title">
                <i class="fas fa-chart-bar me-2"></i> Transaction Analytics
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <canvas id="monthlyChart" height="300"></canvas>
                </div>
                <div class="col-md-6">
                    <canvas id="paymentDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">
                <i class="fas fa-history me-2"></i> Recent Transactions
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>User</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($transaction = $recent_transactions->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y H:i', strtotime($transaction['payment_date'])); ?></td>
                                <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $transaction['payment_type'] === 'EMI' ? 'success' : 
                                            ($transaction['payment_type'] === 'EARLY_PAYMENT' ? 'info' : 'warning'); 
                                    ?>">
                                        <?php echo ucfirst(strtolower(str_replace('_', ' ', $transaction['payment_type']))); ?>
                                    </span>
                                </td>
                                <td>₹<?php echo number_format($transaction['amount'], 2); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $transaction['status'] === 'COMPLETED' ? 'success' : 
                                            ($transaction['status'] === 'PENDING' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst(strtolower($transaction['status'])); ?>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const monthlyData = <?php echo json_encode($monthly_data); ?>;
const paymentDistribution = <?php echo json_encode($payment_distribution); ?>;

// Bar Chart
new Chart(document.getElementById('monthlyChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: monthlyData.map(item => item.month),
        datasets: [{
            label: 'Total Amount',
            data: monthlyData.map(item => parseFloat(item.total)),
            backgroundColor: 'rgba(233, 69, 96, 0.7)',
            borderColor: 'rgba(233, 69, 96, 1)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            title: { 
                display: true, 
                text: 'Monthly Transactions',
                color: '#fff',
                font: { size: 16, weight: 'bold' }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)',
                    drawBorder: true,
                    borderColor: 'rgba(255, 255, 255, 0.2)'
                },
                ticks: {
                    color: '#fff',
                    font: { weight: 'bold' },
                    callback: function(value) {
                        return '₹' + value.toLocaleString();
                    }
                }
            },
            x: {
                grid: {
                    color: 'rgba(255, 255, 255, 0.1)',
                    drawBorder: true,
                    borderColor: 'rgba(255, 255, 255, 0.2)'
                },
                ticks: {
                    color: '#fff',
                    font: { weight: 'bold' },
                    maxRotation: 45,
                    minRotation: 45
                }
            }
        }
    }
});

// Pie Chart
new Chart(document.getElementById('paymentDistributionChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: paymentDistribution.map(item => item.payment_type.replace('_', ' ')),
        datasets: [{
            data: paymentDistribution.map(item => item.total_amount),
            backgroundColor: [
                'rgba(40, 167, 69, 0.8)',   // Success green
                'rgba(255, 193, 7, 0.8)',   // Warning yellow
                'rgba(23, 162, 184, 0.8)',  // Info blue
                'rgba(220, 53, 69, 0.8)'    // Danger red
            ],
            borderColor: [
                'rgba(40, 167, 69, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(23, 162, 184, 1)',
                'rgba(220, 53, 69, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    color: '#fff',
                    font: { size: 12, weight: 'bold' },
                    padding: 20
                }
            },
            title: {
                display: true,
                text: 'Payment Type Distribution',
                color: '#fff',
                font: { size: 16, weight: 'bold' }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((value / total) * 100);
                        return `${label}: ₹${value.toLocaleString()} (${percentage}%)`;
                    }
                }
            }
        },
        animation: {
            animateScale: true,
            animateRotate: true
        }
    }
});
</script>

<style>
.reports-container {
    padding: 2rem;
}

.reports-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.reports-title {
    color: var(--text-light);
    margin: 0;
}

.date-filter {
    background: rgba(255, 255, 255, 0.1);
    padding: 1rem;
    border-radius: 10px;
    backdrop-filter: blur(10px);
}

.date-range-form .input-group {
    gap: 0.5rem;
}

.card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 15px;
    height: 100%;
    margin-bottom: 2rem;
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
    padding: 2rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
}

.stat-item {
    background: rgba(255, 255, 255, 0.05);
    padding: 1.5rem;
    border-radius: 10px;
    text-align: center;
}

.stat-value {
    color: var(--accent-color);
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: var(--text-light);
    opacity: 0.8;
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

.bg-info {
    background: rgba(23, 162, 184, 0.2) !important;
    color: #17a2b8;
}

.bg-danger {
    background: rgba(220, 53, 69, 0.2) !important;
    color: #dc3545;
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
    .reports-header {
        flex-direction: column;
        gap: 1rem;
    }

    .date-filter {
        width: 100%;
    }

    .date-range-form .input-group {
        flex-direction: column;
    }

    .stats-grid {
        grid-template-columns: 1fr;
    }
}

canvas {
    background: rgba(13, 17, 23, 0.95);  /* Dark navy background */
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
    margin-bottom: 1rem;
}
</style>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?> 
