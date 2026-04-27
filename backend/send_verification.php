<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Load PHPMailer (assumed installed via Composer or manually in /vendor)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$autoload = dirname(dirname(__FILE__)) . '/vendor/autoload.php';
if (!file_exists($autoload)) {
    jsonResponse(false, 'PHPMailer not found. Please run: composer require phpmailer/phpmailer');
}
require_once $autoload;

header('Content-Type: application/json');

// ── Rate limiting: max 3 sends per email per 10 minutes ──────────────────────
$email = trim($_POST['email'] ?? '');
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'A valid email address is required.']);
    exit;
}
$email = strtolower($email);

// Clean expired codes from session
if (isset($_SESSION['otp_data'])) {
    $otp = $_SESSION['otp_data'];
    if (time() > ($otp['expires'] ?? 0)) {
        unset($_SESSION['otp_data']);
    }
}

// Rate limiting via session
$key = 'otp_rate_' . md5($email);
if (!isset($_SESSION[$key])) {
    $_SESSION[$key] = ['count' => 0, 'window_start' => time()];
}
$rate = &$_SESSION[$key];
if (time() - $rate['window_start'] > 600) {
    // Reset window after 10 min
    $rate = ['count' => 0, 'window_start' => time()];
}
if ($rate['count'] >= 3) {
    echo json_encode(['success' => false, 'message' => 'Too many attempts. Please wait 10 minutes before requesting a new code.']);
    exit;
}
$rate['count']++;

// ── Generate 6-digit code ─────────────────────────────────────────────────────
$code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

// Store in session (expires in 10 minutes)
$_SESSION['otp_data'] = [
    'code'    => $code,
    'email'   => $email,
    'expires' => time() + 600,
    'used'    => false,
];

// ── Send via PHPMailer ────────────────────────────────────────────────────────
$mail = new PHPMailer(true);
try {
    // ── SMTP configuration — update these values ──────────────────────────
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';         // Your SMTP host
    $mail->SMTPAuth   = true;
    $mail->Username   = 'nambuildersandsupplycorpweb@gmail.com';   // Your SMTP username
    $mail->Password   = 'kscg ianq ysnk mnqs';      // Your SMTP password / App password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    // ─────────────────────────────────────────────────────────────────────

    $mail->setFrom('nambuildersandsupplycorpweb@gmail.com', 'NAM Builders and Supply Corp.');
    $mail->addAddress($email);
    $mail->isHTML(true);
    $mail->Subject = 'Your Verification Code - NAM Builders and Supply Corp.';
    $mail->Body    = '
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; background: #f4f7fb; margin: 0; padding: 0; }
    .wrap { max-width: 480px; margin: 40px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,.1); }
    .header { background: #1565C0; padding: 2rem; text-align: center; }
    .header h1 { color: #fff; margin: 0; font-size: 1.4rem; letter-spacing: .04em; }
    .body { padding: 2rem; text-align: center; }
    .body p { color: #4A5568; font-size: .95rem; line-height: 1.6; margin-bottom: 1.5rem; }
    .code { display: inline-block; background: #F0F4FA; border: 2px dashed #1565C0; border-radius: 10px; padding: 1rem 2.5rem; font-size: 2.4rem; font-weight: 700; letter-spacing: .4em; color: #1565C0; margin: 1rem 0; }
    .expire { color: #888; font-size: .8rem; margin-top: 1rem; }
    .footer { background: #f4f7fb; padding: 1rem; text-align: center; color: #aaa; font-size: .75rem; }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="header"><h1>NAM Builders & Supply Corp.</h1></div>
    <div class="body">
      <p>You requested to send a message through our website.<br>Use the code below to verify your email address:</p>
      <div class="code">' . $code . '</div>
      <p class="expire">⏱ This code expires in <strong>10 minutes</strong>.<br>If you didn\'t request this, please ignore this email.</p>
    </div>
    <div class="footer">&copy; ' . date('Y') . ' NAM Builders and Supply Corp. All rights reserved.</div>
  </div>
</body>
</html>';
    $mail->AltBody = "Your NAM Builders verification code is: $code\n\nThis code expires in 10 minutes.";

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Verification code sent to ' . htmlspecialchars($email)]);
} catch (Exception $e) {
    // Unset the OTP so the rate counter doesn't block retries on mail failure
    unset($_SESSION['otp_data']);
    $rate['count']--;
    echo json_encode(['success' => false, 'message' => 'Could not send email. Error: ' . $mail->ErrorInfo]);
}