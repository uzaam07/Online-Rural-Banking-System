<?php
require_once 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmailNotification($to, $subject, $body, $attachments = []) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com'; // TODO: Replace with your Gmail address
        $mail->Password = 'your-app-password'; // TODO: Replace with your Gmail App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your-email@gmail.com', 'Banking System'); // TODO: Replace with your Gmail address
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        // Add attachments if any
        foreach ($attachments as $attachment) {
            if (file_exists($attachment)) {
                $mail->addAttachment($attachment);
            }
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

function getEmailTemplate($type, $data) {
    $template = '';
    
    switch ($type) {
        case 'loan_approved':
            $template = "
                <h2>Loan Application Approved</h2>
                <p>Dear {$data['customer_name']},</p>
                <p>We are pleased to inform you that your loan application has been approved.</p>
                <h3>Loan Details:</h3>
                <ul>
                    <li>Loan Amount: ₹" . number_format($data['amount'], 2) . "</li>
                    <li>Interest Rate: {$data['interest_rate']}%</li>
                    <li>Term: {$data['term']} months</li>
                    <li>Monthly EMI: ₹" . number_format($data['emi'], 2) . "</li>
                </ul>
                <p>Please log in to your account to view more details and accept the loan terms.</p>
                <p>Best regards,<br>Banking System Team</p>
            ";
            break;

        case 'loan_rejected':
            $template = "
                <h2>Loan Application Status</h2>
                <p>Dear {$data['customer_name']},</p>
                <p>We regret to inform you that your loan application has not been approved at this time.</p>
                <p>Reason: {$data['reason']}</p>
                <p>You may reapply after addressing the concerns mentioned above.</p>
                <p>Best regards,<br>Banking System Team</p>
            ";
            break;

        case 'payment_due':
            $template = "
                <h2>Payment Reminder</h2>
                <p>Dear {$data['customer_name']},</p>
                <p>This is a reminder that your loan payment of ₹" . number_format($data['amount'], 2) . " is due on {$data['due_date']}.</p>
                <p>Please ensure timely payment to avoid any late fees or penalties.</p>
                <p>Best regards,<br>Banking System Team</p>
            ";
            break;

        case 'payment_received':
            $template = "
                <h2>Payment Confirmation</h2>
                <p>Dear {$data['customer_name']},</p>
                <p>We have received your payment of ₹" . number_format($data['amount'], 2) . " on {$data['payment_date']}.</p>
                <p>Your remaining balance is ₹" . number_format($data['remaining_balance'], 2) . ".</p>
                <p>Thank you for your prompt payment.</p>
                <p>Best regards,<br>Banking System Team</p>
            ";
            break;

        case 'collector_assigned':
            $template = "
                <h2>Collector Assignment</h2>
                <p>Dear {$data['collector_name']},</p>
                <p>You have been assigned to collect payments for the following loan:</p>
                <ul>
                    <li>Customer: {$data['customer_name']}</li>
                    <li>Loan Amount: ₹" . number_format($data['amount'], 2) . "</li>
                    <li>Monthly EMI: ₹" . number_format($data['emi'], 2) . "</li>
                </ul>
                <p>Please contact the customer and begin the collection process.</p>
                <p>Best regards,<br>Banking System Team</p>
            ";
            break;

        case 'password_reset':
            $template = "
                <h2>Password Reset Request</h2>
                <p>Dear {$data['name']},</p>
                <p>We received a request to reset your password. Click the link below to reset your password:</p>
                <p><a href='{$data['reset_link']}'>Reset Password</a></p>
                <p>This link will expire in 1 hour.</p>
                <p>If you did not request a password reset, please ignore this email.</p>
                <p>Best regards,<br>Banking System Team</p>
            ";
            break;
    }

    return $template;
}

function sendNotification($type, $data) {
    // Get email template
    $body = getEmailTemplate($type, $data);
    
    // Set subject based on type
    $subject = '';
    switch ($type) {
        case 'loan_approved':
            $subject = 'Loan Application Approved';
            break;
        case 'loan_rejected':
            $subject = 'Loan Application Status Update';
            break;
        case 'payment_due':
            $subject = 'Payment Reminder';
            break;
        case 'payment_received':
            $subject = 'Payment Confirmation';
            break;
        case 'collector_assigned':
            $subject = 'New Collection Assignment';
            break;
        case 'password_reset':
            $subject = 'Password Reset Request';
            break;
    }

    // Send email
    return sendEmailNotification($data['email'], $subject, $body);
}
?> 
