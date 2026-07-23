<?php
session_start();
require_once 'auth_check.php';
check_role('customer');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bank";

$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$stmt = $conn->prepare("SELECT t.*, u.username as collector_name FROM transactions t LEFT JOIN users u ON t.collector_id = u.id WHERE t.customer_id = ? ORDER BY t.payment_date DESC");
if (!$stmt) { die("Prepare failed: " . $conn->error); }
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$payments = $stmt->get_result();

include 'includes/header.php';
?>
<link rel="stylesheet" href="css/style.css">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg" style="background-color: #1a1a2e; color: #fff;">
                <div class="card-body">
                    <h1 class="display-5 mb-4" style="color: #ffff80;">Payment History</h1>
                    <canvas id="paymentsChart" height="100"></canvas>
                    <div class="table-responsive mt-4">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Collector</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $chartData = [];
                                while ($payment = $payments->fetch_assoc()):
                                    $chartData[] = [
                                        'date' => date('Y-m-d', strtotime($payment['payment_date'])),
                                        'amount' => $payment['amount']
                                    ]; ?>
                                    <tr>
                                        <td><?php echo date('M d, Y', strtotime($payment['payment_date'])); ?></td>
                                        <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($payment['payment_type']); ?></td>
                                        <td><span class="badge bg-<?php echo $payment['status'] === 'COMPLETED' ? 'success' : 'warning'; ?>"><?php echo ucfirst($payment['status']); ?></span></td>
                                        <td><?php echo htmlspecialchars($payment['collector_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <a href="generate_receipt.php?id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-download"></i> Download
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('paymentsChart').getContext('2d');
const chartData = <?php echo json_encode($chartData); ?>;
const labels = chartData.map(item => item.date);
const data = chartData.map(item => item.amount);
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Payments',
            data: data,
            backgroundColor: 'rgba(233, 69, 96, 0.7)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            title: { display: true, text: 'Payment History (Bar Graph)' }
        }
    }
});
</script>
<style>
.card {
    background: #22223b;
    border-radius: 12px;
    color: #fff;
}
</style>
<?php
mysqli_close($conn);
include 'includes/footer.php';
?> 
