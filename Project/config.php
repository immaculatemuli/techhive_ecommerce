<?php
// ============================================================
// config.php — Database configuration and connection
// Uses PDO (PHP Data Objects) for secure database access
// ============================================================

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'techhive_db');

// ── Email / SMTP settings ─────────────────────────────────────
// For Gmail:
//   1. Enable 2-Step Verification on your Google account
//   2. Go to myaccount.google.com → Security → App passwords
//   3. Generate an App Password for "Mail" / "Other"
//   4. Paste the 16-character app password as MAIL_PASS below
define('MAIL_HOST',     'smtp.gmail.com');
define('MAIL_PORT',     587);               // 587 = TLS  |  465 = SSL
define('MAIL_USERNAME', 'immaculatemuli25@gmail.com');
define('MAIL_PASSWORD', 'zsxgdllkbcdybfbp');
define('MAIL_FROM',     'immaculatemuli25@gmail.com');
define('MAIL_FROM_NAME','TechHive');

// ── Google OAuth credentials ──────────────────────────────────
// To enable Google login:
// 1. Go to https://console.cloud.google.com
// 2. Create a project → APIs & Services → Credentials → OAuth 2.0 Client ID
// 3. Set Authorized redirect URI to: http://localhost/techhive/google_auth.php
// 4. Paste your Client ID and Secret below
define('GOOGLE_CLIENT_ID',     '');   // paste your Client ID here
define('GOOGLE_CLIENT_SECRET', '');   // paste your Client Secret here
define('GOOGLE_REDIRECT_URI',  'http://localhost/techhive/google_auth.php');

// ── Web Push (dummy M-Pesa prompt sent to your phone at checkout) ──
// Self-generated keys, no third-party account needed. Regenerate with:
//   cd Project/push-sender && npx web-push generate-vapid-keys --json
define('VAPID_PUBLIC_KEY',  'BB3Lqpcs9gKxDw7lEn4uFlJxfFsmxv-vK5999dMPJoryZvr-tBoEiLvd2EIW2fOit4K56Ei8cpr3wLwj9G-fxg0');
define('VAPID_PRIVATE_KEY', 'REoZgL7F8BakmI184_ljkVaFtsb2GF_s-_448hY2Dh8');
define('VAPID_SUBJECT',     'mailto:admin@techhive.test');
define('NODE_BINARY',       'C:\\Program Files\\nodejs\\node.exe');
define('PUSH_SENDER_SCRIPT', __DIR__ . '/push-sender/send-push.js');

// Sends one Web Push notification via the Node helper script.
// Returns ['ok' => bool, 'error' => string|null]
function sendPushNotification(array $subscription, array $payload): array {
    $job = json_encode([
        'subscription' => $subscription,
        'payload'      => $payload,
        'vapid'        => [
            'subject'    => VAPID_SUBJECT,
            'publicKey'  => VAPID_PUBLIC_KEY,
            'privateKey' => VAPID_PRIVATE_KEY,
        ],
    ]);

    $descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $process = proc_open([NODE_BINARY, PUSH_SENDER_SCRIPT], $descriptors, $pipes, __DIR__ . '/push-sender');
    if (!is_resource($process)) {
        return ['ok' => false, 'error' => 'Could not start push sender process'];
    }

    fwrite($pipes[0], $job);
    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    $errOut = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);

    $result = json_decode($output, true);
    if (!is_array($result)) {
        return ['ok' => false, 'error' => $errOut ?: 'No response from push sender'];
    }
    return $result;
}

// ── Currency helper ───────────────────────────────────────────
// Returns price formatted as  KSh 12,999
function ksh(float $amount): string {
    return 'KSh ' . number_format($amount, 0);
}

// ── Product image helper ──────────────────────────────────────
// Returns a usable <img> src — handles both Unsplash URLs and local files
function productImg(string $image, string $fallback = ''): string {
    if (empty($image)) return $fallback;
    if (str_starts_with($image, 'http')) return $image;          // external URL
    return '/techhive/Project/images/' . htmlspecialchars($image);  // local file
}

// ── App base URL (dynamic — works on any port/host) ──────────
if (!defined('APP_URL')) {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define('APP_URL', $scheme . '://' . $host . '/techhive/Project');
}

// ── Secure random token (hex, 32 bytes = 64 chars) ───────────
function generateToken(): string {
    return bin2hex(random_bytes(32));
}

// ── Email helper (PHPMailer + SMTP) ──────────────────────────
function sendEmail(string $to, string $subject, string $htmlBody): bool {
    $vendorDir = __DIR__ . '/vendor/phpmailer/';
    require_once $vendorDir . 'Exception.php';
    require_once $vendorDir . 'PHPMailer.php';
    require_once $vendorDir . 'SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_FROM ?: MAIL_USERNAME, MAIL_FROM_NAME);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags($htmlBody);

        $mail->send();
        return true;
    } catch (\Exception $e) {
        error_log('sendEmail failed: ' . $mail->ErrorInfo);
        return false;
    }
}

// ── TOTP (RFC 6238) — compatible with Google Authenticator ───

function totpBase32Decode(string $input): string {
    $map    = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $input  = strtoupper(rtrim($input, '='));
    $buffer = 0;
    $bits   = 0;
    $output = '';
    foreach (str_split($input) as $ch) {
        $val = strpos($map, $ch);
        if ($val === false) continue;
        $buffer = ($buffer << 5) | $val;
        $bits  += 5;
        if ($bits >= 8) {
            $bits   -= 8;
            $output .= chr(($buffer >> $bits) & 0xFF);
        }
    }
    return $output;
}

function totpGenerateSecret(int $length = 16): string {
    $chars  = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = '';
    for ($i = 0; $i < $length; $i++) {
        $secret .= $chars[random_int(0, 31)];
    }
    return $secret;
}

function totpGetCode(string $secret, int $slot = 0): string {
    $timeSlot  = floor(time() / 30) + $slot;
    $key       = totpBase32Decode($secret);
    $timestamp = pack('N*', 0) . pack('N*', $timeSlot);
    $hash      = hash_hmac('sha1', $timestamp, $key, true);
    $offset    = ord($hash[19]) & 0x0F;
    $value     = (
        ((ord($hash[$offset])     & 0x7F) << 24) |
        ((ord($hash[$offset + 1]) & 0xFF) << 16) |
        ((ord($hash[$offset + 2]) & 0xFF) << 8)  |
         (ord($hash[$offset + 3]) & 0xFF)
    );
    return str_pad($value % 1_000_000, 6, '0', STR_PAD_LEFT);
}

function totpVerify(string $secret, string $code): bool {
    $code = preg_replace('/\D/', '', $code);
    if (strlen($code) !== 6) return false;
    // Allow ±1 window (30-second grace period each side)
    for ($i = -1; $i <= 1; $i++) {
        if (hash_equals(totpGetCode($secret, $i), $code)) return true;
    }
    return false;
}

function totpQrUri(string $secret, string $email, string $issuer = 'TechHive'): string {
    $label = rawurlencode($issuer . ':' . $email);
    $params = http_build_query([
        'secret' => $secret,
        'issuer' => $issuer,
        'algorithm' => 'SHA1',
        'digits' => 6,
        'period' => 30,
    ]);
    return "otpauth://totp/{$label}?{$params}";
}

// ── Remember-Me cookie auto-login ────────────────────────────
// Called early in header.php; sets session if valid cookie found.
function checkRememberMe(): void {
    if (isset($_SESSION['user_id'])) return;
    $cookie = $_COOKIE['remember_token'] ?? '';
    if (empty($cookie)) return;

    $db   = getDB();
    $hash = hash('sha256', $cookie);
    $stmt = $db->prepare("SELECT id, username, role FROM users WHERE remember_token = ? LIMIT 1");
    $stmt->execute([$hash]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];
    } else {
        // Invalid / expired cookie — clear it
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);
    }
}

// getDB() returns a single shared PDO connection (singleton pattern)
function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";

            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // throw exceptions on error
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // return arrays by default
                PDO::ATTR_EMULATE_PREPARES   => false,                   // use real prepared statements
            ]);

        } catch (PDOException $e) {
            // Stop execution and show error (in production you would log this, not display it)
            die("Database connection failed: " . $e->getMessage());
        }
    }

    return $pdo;
}
?>
