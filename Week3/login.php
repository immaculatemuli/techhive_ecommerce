<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['user_id'])) { header('Location: dynamic_input.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim(htmlspecialchars($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            header('Location: dynamic_input.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

// Build Google OAuth URL
$googleAuthUrl = null;
if (defined('GOOGLE_CLIENT_ID') && GOOGLE_CLIENT_ID !== '') {
    $params = http_build_query([
        'client_id'     => GOOGLE_CLIENT_ID,
        'redirect_uri'  => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'access_type'   => 'online',
    ]);
    $googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . $params;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — TechHive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/techhive/css/style.css">
    <style>
        body::before { display: none; }
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 24px; }
    </style>
</head>
<body>

<div style="width:100%;max-width:380px;">

    <div style="text-align:center;margin-bottom:32px;">
        <a href="index.php" class="brand" style="font-size:1.5rem;">Tech<span>Hive</span></a>
        <p style="color:#52525b;font-size:0.875rem;margin-top:8px;">Sign in to your account</p>
    </div>

    <div class="card" style="padding:32px;">

        <?php if ($error): ?>
            <div class="alert-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Google Sign-In -->
        <?php if ($googleAuthUrl): ?>
            <a href="<?= $googleAuthUrl ?>" class="btn-google">
                <!-- Google icon SVG -->
                <svg width="18" height="18" viewBox="0 0 48 48">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                Continue with Google
            </a>
            <div class="divider">or</div>
        <?php else: ?>
            <!-- Google not yet configured — show placeholder button -->
            <button class="btn-google" onclick="alert('Google login requires OAuth credentials.\nSee config.php to set up GOOGLE_CLIENT_ID.')">
                <svg width="18" height="18" viewBox="0 0 48 48">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                Continue with Google
            </button>
            <div class="divider">or</div>
        <?php endif; ?>

        <!-- Email / password form -->
        <form id="login-form" method="POST" novalidate>

            <div style="margin-bottom:16px;">
                <label for="email" class="label">Email</label>
                <input type="email" id="email" name="email" class="field"
                    placeholder="you@example.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <p id="email-error" class="field-error"></p>
            </div>

            <div style="margin-bottom:24px;">
                <label for="password" class="label">Password</label>
                <div style="position:relative;">
                    <input type="password" id="password" name="password" class="field"
                        placeholder="••••••••" style="padding-right:56px;">
                    <button type="button" id="toggle-password"
                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                               background:none;border:none;color:#52525b;font-size:0.75rem;
                               font-weight:600;cursor:pointer;font-family:inherit;">
                        Show
                    </button>
                </div>
                <p id="password-error" class="field-error"></p>
            </div>

            <button type="submit" class="btn-primary">Sign in</button>

        </form>

    </div>

    <p style="text-align:center;margin-top:18px;font-size:0.85rem;color:#52525b;">
        No account?
        <a href="register.php" style="color:#818cf8;font-weight:500;text-decoration:none;">Create one</a>
    </p>

</div>

<script src="/techhive/js/main.js"></script>
</body>
</html>
