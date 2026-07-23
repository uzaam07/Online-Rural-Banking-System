<?php
session_start();
require_once 'auth_check.php';

// Check if user is customer
if ($_SESSION['role'] !== 'customer') {
    header("Location: dashboard.php");
    exit();
}

$page_title = "Apply for Loan - Banking System";
include 'includes/header.php';

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loan_amount = $_POST['loan_amount'];
    $term_months = $_POST['term_months'];
    $purpose = $_POST['purpose'];
    $interest_rate = 12; // Fixed interest rate for now
    
    // Calculate EMI
    $principal = $loan_amount;
    $rate = $interest_rate / 12 / 100; // Monthly interest rate
    $emi = $principal * $rate * pow(1 + $rate, $term_months) / (pow(1 + $rate, $term_months) - 1);
    
    // Insert loan application
    $stmt = $conn->prepare("INSERT INTO MyGuest (user_id, loan, term_months, interest_rate, emi, purpose, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("ididss", $_SESSION['user_id'], $loan_amount, $term_months, $interest_rate, $emi, $purpose);
    
    if ($stmt->execute()) {
        $success_message = "Loan application submitted successfully. We will review your application and get back to you soon.";
        
        // Send email notification to admin
        $admin_query = "SELECT email FROM users WHERE role = 'admin' LIMIT 1";
        $admin_result = $conn->query($admin_query);
        if ($admin = $admin_result->fetch_assoc()) {
            $to = $admin['email'];
            $subject = "New Loan Application";
            $message = "A new loan application has been submitted:\n\n";
            $message .= "Customer: " . ($_SESSION['name'] ?? 'User') . "\n";
            $message .= "Amount: ₹" . number_format($loan_amount, 2) . "\n";
            $message .= "Term: " . $term_months . " months\n";
            $message .= "Purpose: " . $purpose . "\n\n";
            $message .= "Please review the application in the admin dashboard.";
            $headers = "From: noreply@bankingsystem.com";
            
            mail($to, $subject, $message, $headers);
        }
    } else {
        $error_message = "Error submitting loan application: " . $conn->error;
    }
}
?>

<div class="dashboard-container">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Apply for Loan</h1>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success"><?php echo $success_message; ?></div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" class="form">
            <div class="form-group">
                <label for="loan_amount" class="form-label">Loan Amount (₹)</label>
                <input type="number" id="loan_amount" name="loan_amount" class="form-input" min="1000" max="1000000" step="1000" required>
                <div class="form-hint">Minimum: ₹1,000, Maximum: ₹1,000,000</div>
            </div>
            
            <div class="form-group">
                <label for="term_months" class="form-label">Loan Term (Months)</label>
                <select id="term_months" name="term_months" class="form-input" required>
                    <option value="3">3 Months</option>
                    <option value="6">6 Months</option>
                    <option value="12">12 Months</option>
                    <option value="24">24 Months</option>
                    <option value="36">36 Months</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="purpose" class="form-label">Loan Purpose</label>
                <textarea id="purpose" name="purpose" class="form-input" rows="4" required></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Interest Rate</label>
                <div class="form-static">12% per annum</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Estimated Monthly EMI</label>
                <div class="form-static" id="emi_preview">₹0.00</div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Submit Application</button>
                <a href="customer_dashboard.php" class="btn">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loanAmount = document.getElementById('loan_amount');
    const termMonths = document.getElementById('term_months');
    const emiPreview = document.getElementById('emi_preview');
    
    function calculateEMI() {
        const principal = parseFloat(loanAmount.value) || 0;
        const term = parseInt(termMonths.value) || 0;
        const rate = 12 / 12 / 100; // Monthly interest rate
        
        if (principal > 0 && term > 0) {
            const emi = principal * rate * Math.pow(1 + rate, term) / (Math.pow(1 + rate, term) - 1);
            emiPreview.textContent = '₹' + emi.toFixed(2);
        } else {
            emiPreview.textContent = '₹0.00';
        }
    }
    
    loanAmount.addEventListener('input', calculateEMI);
    termMonths.addEventListener('change', calculateEMI);
});
</script>

<?php
mysqli_close($conn);
include 'includes/footer.php';
?> 
