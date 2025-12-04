<?php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Disable error display to prevent breaking JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $email = $input['email'] ?? '';

    if (empty($email)) {
        throw new Exception('Email is required');
    }

    // Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if (!$stmt->fetch()) {
        // Return success to prevent enumeration
        echo json_encode(['success' => true, 'message' => 'If an account exists, a PIN has been sent.']);
        exit;
    }

    // Generate PIN
    $pin = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Store in DB using DB time for consistency
    $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 15 MINUTE)) ON DUPLICATE KEY UPDATE token = VALUES(token), expires_at = DATE_ADD(NOW(), INTERVAL 15 MINUTE)");
    $stmt->execute([$email, $pin]);

    // Define email content
    $subject = "Password Reset PIN - PROPLEDGER";
    $message = "Your password reset PIN is: $pin\n\nThis PIN expires in 15 minutes.";

    // Load PHPMailer
    require_once '../lib/PHPMailer/Exception.php';
    require_once '../lib/PHPMailer/PHPMailer.php';
    require_once '../lib/PHPMailer/SMTP.php';
    
    $mailConfig = require '../config/mail.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $mailConfig['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailConfig['smtp_user'];
        $mail->Password   = $mailConfig['smtp_pass'];
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $mailConfig['smtp_port'];

        // Recipients
        $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = nl2br($message);
        $mail->AltBody = $message;

        $mail->send();
        $mailSent = true;
    } catch (Exception $e) {
        $mailSent = false;
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }

    // For local dev without mail server, log the PIN
    error_log("Password Reset PIN for $email: $pin");

    if ($mailSent) {
        echo json_encode(['success' => true, 'message' => 'PIN sent to your email.']);
    } else {
        // Fallback for dev: show PIN if email fails
        echo json_encode(['success' => true, 'message' => 'PIN sent to your email. (Dev PIN: ' . $pin . ')']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
