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

// ── Google OAuth credentials ──────────────────────────────────
// To enable Google login:
// 1. Go to https://console.cloud.google.com
// 2. Create a project → APIs & Services → Credentials → OAuth 2.0 Client ID
// 3. Set Authorized redirect URI to: http://localhost/techhive/google_auth.php
// 4. Paste your Client ID and Secret below
define('GOOGLE_CLIENT_ID',     '');   // paste your Client ID here
define('GOOGLE_CLIENT_SECRET', '');   // paste your Client Secret here
define('GOOGLE_REDIRECT_URI',  'http://localhost/techhive/google_auth.php');

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
    return '/techhive/images/' . htmlspecialchars($image);        // local file
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
