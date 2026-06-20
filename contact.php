<?php
/**
 * Smart Energy - Contact Form Handler
 * Optimized for Hostinger Shared Hosting
 */

// Configuration
$recipient_email = "admin@smartepro.com";
$subject_prefix = "[Smart Energy Website] ";
$success_message = "Thank you for your message. We will get back to you within 24 hours.";
$error_message = "Sorry, there was an error sending your message. Please try again or contact us directly.";

// Initialize response
$response = [
    'success' => false,
    'message' => ''
];

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Sanitize and collect form data
    $name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '';
    $subject = isset($_POST['subject']) ? sanitize_input($_POST['subject']) : 'General Inquiry';
    $message = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($message)) {
        $response['message'] = "Please fill in all required fields.";
        send_json_response($response);
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = "Please enter a valid email address.";
        send_json_response($response);
    }
    
    // Validate honeypot (anti-spam hidden field)
    if (isset($_POST['website']) && !empty($_POST['website'])) {
        $response['message'] = "Spam detected.";
        send_json_response($response);
    }
    
    // Build email subject
    $email_subject = $subject_prefix . "New Message from " . $name;
    
    // Map subject value to readable text
    $subject_labels = [
        'installation' => 'Installation Services',
        'upgrade' => 'Upgrades & Renovations',
        'maintenance' => 'Maintenance & Repair',
        'consultation' => 'General Consultation'
    ];
    
    $subject_text = isset($subject_labels[$subject]) ? $subject_labels[$subject] : 'General Inquiry';
    
    // Build email body
    $email_body = "You have received a new message from your website contact form.\n\n";
    $email_body .= "=== CONTACT DETAILS ===\n";
    $email_body .= "Name: " . $name . "\n";
    $email_body .= "Email: " . $email . "\n";
    $email_body .= "Phone: " . ($phone ? $phone : "Not provided") . "\n";
    $email_body .= "Subject: " . $subject_text . "\n\n";
    $email_body .= "=== MESSAGE ===\n";
    $email_body .= $message . "\n\n";
    $email_body .= "=== TECHNICAL INFO ===\n";
    $email_body .= "Sent from: " . $_SERVER['HTTP_HOST'] . "\n";
    $email_body .= "IP Address: " . get_client_ip() . "\n";
    $email_body .= "Date/Time: " . date('Y-m-d H:i:s') . "\n";
    
    // Build HTML version
    $html_body = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #0a1628; color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f8fafc; padding: 30px; border: 1px solid #e2e8f0; }
            .field { margin-bottom: 15px; }
            .label { font-weight: bold; color: #0a1628; display: block; margin-bottom: 5px; }
            .value { color: #334155; }
            .message-box { background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #00d4ff; margin-top: 10px; }
            .footer { background: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #64748b; border-radius: 0 0 10px 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>New Contact Form Submission</h2>
                <p>Smart Energy Electrical Installations</p>
            </div>
            <div class='content'>
                <div class='field'>
                    <span class='label'>Name:</span>
                    <span class='value'>" . htmlspecialchars($name) . "</span>
                </div>
                <div class='field'>
                    <span class='label'>Email:</span>
                    <span class='value'>" . htmlspecialchars($email) . "</span>
                </div>
                <div class='field'>
                    <span class='label'>Phone:</span>
                    <span class='value'>" . htmlspecialchars($phone ?: 'Not provided') . "</span>
                </div>
                <div class='field'>
                    <span class='label'>Service:</span>
                    <span class='value'>" . htmlspecialchars($subject_text) . "</span>
                </div>
                <div class='field'>
                    <span class='label'>Message:</span>
                    <div class='message-box'>" . nl2br(htmlspecialchars($message)) . "</div>
                </div>
            </div>
            <div class='footer'>
                <p>This email was sent from the contact form on your website.</p>
                <p>IP: " . get_client_ip() . " | Date: " . date('Y-m-d H:i:s') . "</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Email headers
    $headers = "From: Smart Energy Website <noreply@" . $_SERVER['HTTP_HOST'] . ">\r\n";
    $headers .= "Reply-To: " . $email . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
    
    // Try to send email
    $mail_sent = mail($recipient_email, $email_subject, $html_body, $headers);
    
    if ($mail_sent) {
        $response['success'] = true;
        $response['message'] = $success_message;
        
        // Also send auto-reply to sender
        send_auto_reply($email, $name);
    } else {
        $response['message'] = $error_message;
    }
    
    send_json_response($response);
    
} else {
    // Not a POST request - redirect to homepage
    header('Location: index.html');
    exit();
}

// Helper Functions

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function get_client_ip() {
    $ip = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'Unknown';
}

function send_json_response($response) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

function send_auto_reply($to_email, $name) {
    $subject = "Thank you for contacting Smart Energy";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #0a1628; color: white; padding: 30px; text-align: center; }
            .content { background: #f8fafc; padding: 30px; }
            .footer { background: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
            .highlight { color: #00d4ff; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Thank You for Reaching Out!</h2>
            </div>
            <div class='content'>
                <p>Dear " . htmlspecialchars($name) . ",</p>
                <p>Thank you for contacting <strong class='highlight'>Smart Energy Electrical Installations</strong>. We have received your message and our team will review it promptly.</p>
                <p>We typically respond to all inquiries within <strong>24 hours</strong> during business days.</p>
                <p>If your matter is urgent, please feel free to call us directly at:</p>
                <p><strong>+965 5069 9356</strong> or <strong>+965 6663 0709</strong></p>
                <br>
                <p>Best regards,</p>
                <p><strong>Smart Energy Team</strong></p>
            </div>
            <div class='footer'>
                <p>Smart Energy Electrical Installations | Kuwait</p>
                <p>admin@smartepro.com | www.smartepro.com</p>
            </div>
        </div>
    </body>
    </html>";
    
    $headers = "From: Smart Energy <noreply@smartepro.com>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    @mail($to_email, $subject, $message, $headers);
}
