<?php
// ============================================================
// google_auth.php — Google OAuth 2.0 callback handler
// Google redirects here after the user approves access.
// ============================================================

require_once 'config.php';
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Require Google credentials to be configured
if (!defined('GOOGLE_CLIENT_ID') || GOOGLE_CLIENT_ID === '') {
    die('Google OAuth is not configured. Add your credentials to config.php.');
}

$code  = $_GET['code']  ?? '';
$error = $_GET['error'] ?? '';

// User denied access
if ($error || !$code) {
    header('Location: login.php?error=google_denied');
    exit;
}

// ── Step 1: Exchange the code for an access token ────────────
$tokenResponse = httpPost('https://oauth2.googleapis.com/token', [
    'code'          => $code,
    'client_id'     => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri'  => GOOGLE_REDIRECT_URI,
    'grant_type'    => 'authorization_code',
]);

if (!isset($tokenResponse['access_token'])) {
    header('Location: login.php?error=token_failed');
    exit;
}

// ── Step 2: Fetch the user's Google profile ──────────────────
$userInfo = httpGet(
    'https://www.googleapis.com/oauth2/v3/userinfo',
    $tokenResponse['access_token']
);

if (!isset($userInfo['email'])) {
    header('Location: login.php?error=profile_failed');
    exit;
}

$googleEmail = $userInfo['email'];
$googleName  = $userInfo['name']    ?? explode('@', $googleEmail)[0];
$googleSub   = $userInfo['sub']     ?? '';   // unique Google user ID

// ── Step 3: Find or create the user in our database ──────────
$db   = getDB();
$stmt = $db->prepare("SELECT id, username, role FROM users WHERE email = ?");
$stmt->execute([$googleEmail]);
$user = $stmt->fetch();

if (!$user) {
    // New user — create an account with a random secure password (they'll use Google to log in)
    $randomPassword = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $insert = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'customer')");
    $insert->execute([$googleName, $googleEmail, $randomPassword]);

    $userId   = $db->lastInsertId();
    $username = $googleName;
    $role     = 'customer';
} else {
    $userId   = $user['id'];
    $username = $user['username'];
    $role     = $user['role'];
}

// ── Step 4: Log the user in ───────────────────────────────────
$_SESSION['user_id']  = $userId;
$_SESSION['username'] = $username;
$_SESSION['role']     = $role;

header('Location: dashboard.php');
exit;


// ── Helper: POST request using cURL ──────────────────────────
function httpPost(string $url, array $data): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true) ?? [];
}

// ── Helper: GET request with Bearer token ────────────────────
function httpGet(string $url, string $accessToken): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => ["Authorization: Bearer $accessToken"],
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true) ?? [];
}
