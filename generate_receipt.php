<?php
session_start();
require_once 'auth_check.php';

// Check if user is admin or collector
if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'collector') {
    header('Location: dashboard.php');
    exit();
}

// Database connection
$conn = mysqli_connect("localhost", "root", "", "bank");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get transaction ID
$transaction_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get transaction details
$stmt = $conn->prepare("
    SELECT t.*, l.loan_id, l.amount as loan_amount, l.interest_rate, l.term,
           u.name as customer_name, u.email as customer_email,
           c.name as collector_name
    FROM transactions t
    JOIN MyGuest l ON t.loan_id = l.loan_id
    JOIN users u ON l.user_id = u.id
    LEFT JOIN users c ON l.collector_id = c.id
    WHERE t.id = ? AND (l.user_id = ? OR l.collector_id = ?)
");
$stmt->bind_param("iii", $transaction_id, $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$transaction = $stmt->get_result()->fetch_assoc();

if (!$transaction) {
    die("Transaction not found or unauthorized access");
}

// Calculate remaining balance
$stmt = $conn->prepare("
    SELECT SUM(amount) as total_paid
    FROM transactions
    WHERE loan_id = ? AND status = 'completed'
");
$stmt->bind_param("i", $transaction['loan_id']);
$stmt->execute();
$total_paid = $stmt->get_result()->fetch_assoc()['total_paid'] ?? 0;
$remaining_balance = $transaction['loan_amount'] - $total_paid;

// Generate receipt number
$receipt_number = 'RCP-' . str_pad($transaction_id, 6, '0', STR_PAD_LEFT);

// Set headers for PDF download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="receipt_' . $receipt_number . '.pdf"');

// Include TCPDF library
require_once('tcpdf/tcpdf.php');

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Banking System');
$pdf->SetTitle('Payment Receipt - ' . $receipt_number);

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(15, 15, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Company logo and details
$pdf->Image('images/bank-logo.png', 15, 15, 30);
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Payment Receipt', 0, 1, 'R');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 5, '123 Banking Street', 0, 1, 'R');
$pdf->Cell(0, 5, 'City, State 12345', 0, 1, 'R');
$pdf->Cell(0, 5, 'Phone: (123) 456-7890', 0, 1, 'R');
$pdf->Ln(10);

// Receipt details
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 10, 'Receipt Number:', 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, $receipt_number, 0, 1);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 10, 'Date:', 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, date('F d, Y', strtotime($transaction['payment_date'])), 0, 1);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 10, 'Customer:', 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, $transaction['customer_name'], 0, 1);

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 10, 'Email:', 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, $transaction['customer_email'], 0, 1);

$pdf->Ln(10);

// Payment details
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Payment Details', 0, 1);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(60, 8, 'Description', 1);
$pdf->Cell(40, 8, 'Amount', 1, 1);

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(60, 8, 'Loan Payment', 1);
$pdf->Cell(40, 8, '$' . number_format($transaction['amount'], 2), 1, 1);

$pdf->Ln(5);

// Loan details
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 10, 'Loan Details', 0, 1);

$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(60, 8, 'Description', 1);
$pdf->Cell(40, 8, 'Amount', 1, 1);

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(60, 8, 'Original Loan Amount', 1);
$pdf->Cell(40, 8, '$' . number_format($transaction['loan_amount'], 2), 1, 1);

$pdf->Cell(60, 8, 'Interest Rate', 1);
$pdf->Cell(40, 8, $transaction['interest_rate'] . '%', 1, 1);

$pdf->Cell(60, 8, 'Term', 1);
$pdf->Cell(40, 8, $transaction['term'] . ' months', 1, 1);

$pdf->Cell(60, 8, 'Remaining Balance', 1);
$pdf->Cell(40, 8, '$' . number_format($remaining_balance, 2), 1, 1);

$pdf->Ln(10);

// Collector details
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(40, 10, 'Collected By:', 0);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 10, $transaction['collector_name'], 0, 1);

// Terms and conditions
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->MultiCell(0, 5, 'This receipt is computer generated and does not require a signature. Please keep this receipt for your records.', 0, 'L');

// Output the PDF
$pdf->Output('receipt_' . $receipt_number . '.pdf', 'D');

mysqli_close($conn);
?> 
