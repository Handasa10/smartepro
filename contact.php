<?php
/**
 * Smart Energy - Contact Form Handler
 * Optimized for Hostinger Shared Hosting
 *
 * Accepts submissions from both the English (/) and Arabic (/ar/) pages.
 * Responds with JSON to the site's JavaScript, and redirects back to the
 * page for visitors without JavaScript.
 */

date_default_timezone_set('Asia/Kuwait');

// Configuration
$recipient_email = "admin@smartepro.com";
$sender_email    = "noreply@smartepro.com";
$subject_prefix  = "[Smart Energy Website] ";

// Anti-abuse: maximum accepted submissions per visitor IP within the window
$rate_limit_max    = 5;
$rate_limit_window = 3600; // seconds

$lang = (isset($_POST['lang']) && $_POST['lang'] === 'ar') ? 'ar' : 'en';

$messages = [
    'en' => [
        'success'  => "Thank you for your message. We will get back to you within 24 hours.",
        'error'    => "Sorry, there was an error sending your message. Please try again or contact us directly.",
        'required' => "Please fill in all required fields.",
        'email'    => "Please enter a valid email address.",
        'too_long' => "Your message is too long. Please shorten it and try again.",
        'limited'  => "You have sent several messages recently. Please wait a while, or call us directly.",
    ],
    'ar' => [
        'success'  => "شكراً لتواصلك معنا. سنرد عليك خلال 24 ساعة.",
        'error'    => "عذراً، حدث خطأ أثناء إرسال رسالتك. يرجى المحاولة مرة أخرى أو التواصل معنا مباشرة.",
        'required' => "يرجى تعبئة جميع الحقول المطلوبة.",
        'email'    => "يرجى إدخال بريد إلكتروني صحيح.",
        'too_long' => "رسالتك طويلة جداً. يرجى اختصارها والمحاولة مرة أخرى.",
        'limited'  => "لقد أرسلت عدة رسائل مؤخراً. يرجى الانتظار قليلاً أو الاتصال بنا مباشرة.",
    ],
];
$text = $messages[$lang];

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Not a POST request - redirect to homepage
    header('Location: index.html');
    exit();
}

// Honeypot (hidden field real visitors never fill): pretend success so bots learn nothing
if (!empty($_POST['website'])) {
    respond(true, $text['success'], $lang);
}

// Collect form data (escaping happens once, when the email HTML is built)
$name    = clean_line($_POST['name'] ?? '');
$email   = clean_line($_POST['email'] ?? '');
$phone   = clean_line($_POST['phone'] ?? '');
$subject = clean_line($_POST['subject'] ?? '');
$message = clean_text($_POST['message'] ?? '');

// Validate required fields
if ($name === '' || $email === '' || $message === '') {
    respond(false, $text['required'], $lang);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, $text['email'], $lang);
}

if (mb_strlen($name) > 100 || mb_strlen($email) > 150 || mb_strlen($phone) > 30 || mb_strlen($message) > 5000) {
    respond(false, $text['too_long'], $lang);
}

if (!rate_limit_allows(client_ip(), $rate_limit_max, $rate_limit_window)) {
    respond(false, $text['limited'], $lang);
}

// Map subject value to readable text
$subject_labels = [
    'installation' => 'Installation Services',
    'upgrade'      => 'Upgrades & Renovations',
    'maintenance'  => 'Maintenance & Repair',
    'consultation' => 'General Consultation'
];
$subject_text = $subject_labels[$subject] ?? 'General Inquiry';

$email_subject = $subject_prefix . "New Message from " . $name;
$page_label    = $lang === 'ar' ? 'Arabic page' : 'English page';
$sent_at       = date('Y-m-d H:i:s');

$html_body = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
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
                <span class='value' dir='auto'>" . h($name) . "</span>
            </div>
            <div class='field'>
                <span class='label'>Email:</span>
                <span class='value'>" . h($email) . "</span>
            </div>
            <div class='field'>
                <span class='label'>Phone:</span>
                <span class='value'>" . h($phone !== '' ? $phone : 'Not provided') . "</span>
            </div>
            <div class='field'>
                <span class='label'>Service:</span>
                <span class='value'>" . h($subject_text) . "</span>
            </div>
            <div class='field'>
                <span class='label'>Message:</span>
                <div class='message-box' dir='auto'>" . nl2br(h($message)) . "</div>
            </div>
        </div>
        <div class='footer'>
            <p>This email was sent from the contact form on your website (" . h($page_label) . ").</p>
            <p>IP: " . h(client_ip()) . " | Date: " . $sent_at . " (Kuwait time)</p>
        </div>
    </div>
</body>
</html>";

$headers  = "From: Smart Energy Website <" . $sender_email . ">\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

$mail_sent = mail($recipient_email, encode_header($email_subject), $html_body, $headers, "-f" . $sender_email);

if (!$mail_sent) {
    respond(false, $text['error'], $lang);
}

send_auto_reply($email, $name, $lang, $sender_email);
respond(true, $text['success'], $lang);


// Helper Functions

/** Single-line field: trims and removes control characters (including line breaks). */
function clean_line($value) {
    return trim(preg_replace('/[\x00-\x1F\x7F]+/u', ' ', (string) $value));
}

/** Multi-line field: keeps line breaks and tabs, removes other control characters. */
function clean_text($value) {
    $value = str_replace(["\r\n", "\r"], "\n", (string) $value);
    return trim(preg_replace('/[\x00-\x08\x0B-\x1F\x7F]+/u', '', $value));
}

function h($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/** Encodes a header value so non-ASCII text (e.g. Arabic names) arrives intact. */
function encode_header($value) {
    return '=?UTF-8?B?' . base64_encode($value) . '?=';
}

/** Uses only the connection address, which unlike forwarded-for headers cannot be faked. */
function client_ip() {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown';
}

/** Records this submission and reports whether the IP is still under its limit. */
function rate_limit_allows($ip, $max, $window) {
    $dir = sys_get_temp_dir() . '/smartepro_contact_limits';
    if (!is_dir($dir) && !@mkdir($dir, 0700, true)) {
        return true; // never block real visitors because storage is unavailable
    }

    $file = $dir . '/' . hash('sha256', $ip) . '.json';
    $handle = @fopen($file, 'c+');
    if (!$handle) {
        return true;
    }

    flock($handle, LOCK_EX);
    $now = time();
    $times = json_decode(stream_get_contents($handle), true);
    $times = array_values(array_filter(is_array($times) ? $times : [], function ($t) use ($now, $window) {
        return is_int($t) && $t > $now - $window;
    }));

    $allowed = count($times) < $max;
    if ($allowed) {
        $times[] = $now;
        ftruncate($handle, 0);
        rewind($handle);
        fwrite($handle, json_encode($times));
    }

    flock($handle, LOCK_UN);
    fclose($handle);
    return $allowed;
}

function respond($success, $message, $lang) {
    $wants_json = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

    if ($wants_json) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Visitor without JavaScript: go back to their page
    $page = $lang === 'ar' ? 'ar/' : 'index.html';
    if ($success) {
        header('Location: ' . $page . '?sent=1#contact');
        exit();
    }

    $dir = $lang === 'ar' ? 'rtl' : 'ltr';
    $back = $lang === 'ar' ? 'العودة' : 'Go back';
    header('Content-Type: text/html; charset=UTF-8');
    echo "<!DOCTYPE html><html lang='" . $lang . "' dir='" . $dir . "'><head><meta charset='UTF-8'>"
        . "<meta name='viewport' content='width=device-width, initial-scale=1'><title>Smart Energy</title></head>"
        . "<body style='font-family:Arial,sans-serif;max-width:560px;margin:60px auto;padding:0 16px'>"
        . "<p>" . h($message) . "</p><p><a href='" . $page . "#contact'>" . $back . "</a></p></body></html>";
    exit();
}

function send_auto_reply($to_email, $name, $lang, $sender_email) {
    if ($lang === 'ar') {
        $subject = "شكراً لتواصلك مع سمارت إنرجي";
        $body = "
                <p>عزيزي/عزيزتي " . h($name) . "،</p>
                <p>شكراً لتواصلك مع <strong class='highlight'>سمارت إنرجي للتركيبات الكهربائية</strong>. لقد استلمنا رسالتك وسيقوم فريقنا بمراجعتها في أقرب وقت.</p>
                <p>نرد عادةً على جميع الاستفسارات خلال <strong>24 ساعة</strong> في أيام العمل.</p>
                <p>إذا كان طلبك عاجلاً، يمكنك الاتصال بنا مباشرة على:</p>
                <p><strong dir='ltr'>+965 5069 9356</strong> أو <strong dir='ltr'>+965 6663 0709</strong></p>
                <br>
                <p>مع أطيب التحيات،</p>
                <p><strong>فريق سمارت إنرجي</strong></p>";
        $title = "شكراً لتواصلك معنا!";
        $footer = "سمارت إنرجي للتركيبات الكهربائية | الكويت";
        $dir = 'rtl';
    } else {
        $subject = "Thank you for contacting Smart Energy";
        $body = "
                <p>Dear " . h($name) . ",</p>
                <p>Thank you for contacting <strong class='highlight'>Smart Energy Electrical Installations</strong>. We have received your message and our team will review it promptly.</p>
                <p>We typically respond to all inquiries within <strong>24 hours</strong> during business days.</p>
                <p>If your matter is urgent, please feel free to call us directly at:</p>
                <p><strong>+965 5069 9356</strong> or <strong>+965 6663 0709</strong></p>
                <br>
                <p>Best regards,</p>
                <p><strong>Smart Energy Team</strong></p>";
        $title = "Thank You for Reaching Out!";
        $footer = "Smart Energy Electrical Installations | Kuwait";
        $dir = 'ltr';
    }

    $message = "
    <!DOCTYPE html>
    <html dir='" . $dir . "'>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #0a1628; color: white; padding: 30px; text-align: center; }
            .content { background: #f8fafc; padding: 30px; }
            .footer { background: #f1f5f9; padding: 20px; text-align: center; font-size: 12px; color: #64748b; }
            .highlight { color: #00a8cc; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>" . $title . "</h2>
            </div>
            <div class='content'>" . $body . "
            </div>
            <div class='footer'>
                <p>" . $footer . "</p>
                <p>admin@smartepro.com | www.smartepro.com</p>
            </div>
        </div>
    </body>
    </html>";

    $headers  = "From: Smart Energy <" . $sender_email . ">\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    @mail($to_email, encode_header($subject), $message, $headers, "-f" . $sender_email);
}
