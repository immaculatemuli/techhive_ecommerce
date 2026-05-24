<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['user_id'])) { header('Location: dynamic_input.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']      ?? '';

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
    <link rel="stylesheet" href="/techhive/css/style.css">
    <style>
        body { display: flex; min-height: 100vh; }
        .auth-left {
            flex: 1;
            background: #0f172a;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 64px 56px;
        }
        .auth-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 24px;
            background: #fff;
        }
        @media (max-width: 768px) {
            .auth-left { display: none; }
            .auth-right { padding: 48px 20px; }
        }
    </style>
</head>
<body>

<!-- Left panel -->
<div class="auth-left">
    <a href="/techhive/index.php" style="font-size:1.3rem;font-weight:900;color:#fff;text-decoration:none;letter-spacing:-0.5px;margin-bottom:48px;display:block;">
        TechHive
    </a>
    <h2 style="font-size:2rem;font-weight:900;color:#fff;line-height:1.15;letter-spacing:-1px;margin-bottom:16px;">
        Kenya's home<br>for premium tech.
    </h2>
    <p style="color:rgba(255,255,255,0.4);font-size:0.95rem;line-height:1.7;">
        Genuine laptops, phones and accessories. Fast delivery, real warranty.
    </p>

    <!-- Floating product image -->
    <div style="margin-top:48px;border-radius:16px;overflow:hidden;max-width:340px;">
        <img src="https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600&q=80"
             alt="Laptop" style="width:100%;height:200px;object-fit:cover;opacity:0.7;">
    </div>
</div>

<!-- Right panel: form -->
<div class="auth-right">
<div style="width:100%;max-width:360px;">

    <div style="margin-bottom:32px;">
        <h1 style="font-size:1.6rem;font-weight:800;color:#111827;letter-spacing:-0.5px;margin-bottom:6px;">Sign in</h1>
        <p style="color:#6b7280;font-size:0.875rem;">Welcome back to TechHive</p>
    </div>

    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Google -->
    <?php if ($googleAuthUrl): ?>
        <a href="<?= $googleAuthUrl ?>" class="btn-google" style="margin-bottom:20px;">
            <svg width="18" height="18" viewBox="0 0 48 48">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
            </svg>
            Continue with Google
        </a>
    <?php else: ?>
        <button class="btn-google" style="margin-bottom:20px;"
            onclick="alert('Set up GOOGLE_CLIENT_ID in config.php to enable Google login.')">
            <svg width="18" height="18" viewBox="0 0 48 48">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
            </svg>
            Continue with Google
        </button>
    <?php endif; ?>

    <div class="divider">or</div>

    <form id="login-form" method="POST" novalidate>

        <div style="margin-bottom:16px;">
            <label for="email" class="label">Email address</label>
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
                           background:none;border:none;color:#6b7280;font-size:0.75rem;
                           font-weight:600;cursor:pointer;font-family:inherit;">
                    Show
                </button>
            </div>
            <p id="password-error" class="field-error"></p>
        </div>

        <button type="submit" class="btn-primary" style="width:100%;font-size:0.925rem;padding:12px;">
            Sign in
        </button>

    </form>

    <p style="text-align:center;margin-top:20px;font-size:0.85rem;color:#6b7280;">
        No account?
        <a href="register.php" style="color:#111827;font-weight:700;text-decoration:none;">Create one free</a>
    </p>

</div>
</div>

<script src="/techhive/js/main.js"></script>
</body>
</html>
