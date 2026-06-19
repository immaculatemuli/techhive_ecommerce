<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email      = trim($_POST['email']    ?? '');
    $password   = $_POST['password']      ?? '';
    $rememberMe = isset($_POST['remember_me']);

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id, username, password, role, email_verified, two_fa_enabled, two_fa_secret FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {

            // Block login if email not verified
            if (!$user['email_verified']) {
                $error = 'Please verify your email address before signing in. <a href="resend_verify.php?email=' . urlencode($email) . '" style="color:#dc2626;font-weight:700;">Resend verification email</a>';
            } else {
                // Handle Remember Me before 2FA so cookie is always set
                if ($rememberMe) {
                    $token = generateToken();
                    $hash  = hash('sha256', $token);
                    $db->prepare("UPDATE users SET remember_token = ? WHERE id = ?")->execute([$hash, $user['id']]);
                    setcookie('remember_token', $token, time() + 60 * 60 * 24 * 30, '/', '', false, true);
                }

                // If 2FA is enabled, stage the user and go to 2FA verify
                if ($user['two_fa_enabled'] && $user['two_fa_secret']) {
                    $_SESSION['2fa_pending_id']       = $user['id'];
                    $_SESSION['2fa_pending_username'] = $user['username'];
                    $_SESSION['2fa_pending_role']     = $user['role'];
                    header('Location: 2fa_verify.php');
                    exit;
                }

                // Normal login
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];
                header('Location: index.php');
                exit;
            }
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | TechHive</title>
    <link rel="stylesheet" href="/techhive/Project/css/style.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            padding: 24px;
            margin: 0;
        }

        .auth-card {
            display: flex;
            width: 100%;
            max-width: 1020px;
            min-height: 560px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 24px 80px rgba(0,0,0,0.13);
        }

        /* ── Left (dark blue) ── */
        .auth-left {
            width: 44%;
            background: #0f172a;
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .auth-left h2 {
            font-size: 1.65rem;
            font-weight: 900;
            color: #fff;
            line-height: 1.2;
            letter-spacing: -0.8px;
            margin: 32px 0 12px;
        }
        .auth-left p {
            color: rgba(255,255,255,0.4);
            font-size: 0.9rem;
            line-height: 1.7;
            margin-bottom: 36px;
        }
        .auth-left-img {
            border-radius: 12px;
            overflow: hidden;
        }
        .auth-left-img img {
            width: 100%; height: 160px;
            object-fit: cover;
            opacity: 0.65;
            display: block;
        }

        /* ── Right (white) ── */
        .auth-right {
            flex: 1;
            background: #fff;
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 0.83rem;
        }
        .remember-row label {
            display: flex;
            align-items: center;
            gap: 7px;
            color: #374151;
            cursor: pointer;
            user-select: none;
        }
        .remember-row input[type="checkbox"] {
            width: 15px; height: 15px;
            accent-color: #1e3a8a;
            cursor: pointer;
        }
        .remember-row a {
            color: #1e3a8a;
            font-weight: 600;
            text-decoration: none;
        }
        .remember-row a:hover { text-decoration: underline; }

        @media (max-width: 640px) {
            .auth-card { flex-direction: column; border-radius: 16px; }
            .auth-left  { width: 100%; padding: 36px 28px; }
            .auth-right { padding: 36px 28px; }
            .auth-left-img { display: none; }
        }
    </style>
</head>
<body>

<div class="auth-card">

    <!-- ── Left panel ── -->
    <div class="auth-left">
        <a href="/techhive/Project/index.php"
           style="font-size:1.1rem;font-weight:900;color:#fff;text-decoration:none;letter-spacing:-0.4px;">
            TechHive
        </a>
        <h2>Kenya's home<br>for premium tech.</h2>
        <p>Genuine laptops, phones and accessories.<br>Fast delivery, real warranty.</p>
        <div class="auth-left-img">
            <img src="https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600&q=80" alt="Laptop">
        </div>
    </div>

    <!-- ── Right panel ── -->
    <div class="auth-right">

        <div style="margin-bottom:28px;">
            <h1 style="font-size:1.5rem;font-weight:800;color:#111827;letter-spacing:-0.5px;margin-bottom:5px;">
                Sign in
            </h1>
            <p style="color:#6b7280;font-size:0.85rem;">Welcome back to TechHive</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error" style="margin-bottom:20px;"><?= $error ?></div>
        <?php endif; ?>

        <form id="login-form" method="POST" novalidate>

            <div style="margin-bottom:16px;">
                <label for="email" class="label">Email address</label>
                <input type="email" id="email" name="email" class="field"
                       placeholder="you@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <p id="email-error" class="field-error"></p>
            </div>

            <div style="margin-bottom:8px;">
                <label for="password" class="label">Password</label>
                <div style="position:relative;">
                    <input type="password" id="password" name="password" class="field"
                           placeholder="••••••••" style="padding-right:56px;">
                    <button type="button" id="toggle-password"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                   background:none;border:none;color:#6b7280;font-size:0.75rem;
                                   font-weight:600;cursor:pointer;font-family:inherit;">Show</button>
                </div>
                <p id="password-error" class="field-error"></p>
            </div>

            <!-- Remember Me + Forgot Password row -->
            <div class="remember-row">
                <label>
                    <input type="checkbox" name="remember_me" <?= isset($_POST['remember_me']) ? 'checked' : '' ?>>
                    Remember me for 30 days
                </label>
                <a href="forgot_password.php">Forgot password?</a>
            </div>

            <button type="submit" class="btn-primary" style="width:100%;font-size:0.9rem;padding:12px;">
                Sign in
            </button>

        </form>

        <p style="text-align:center;margin-top:20px;font-size:0.85rem;color:#6b7280;">
            No account?
            <a href="register.php" style="color:#111827;font-weight:700;text-decoration:none;">Create one free</a>
        </p>

    </div>

</div>

<script src="/techhive/Project/js/main.js"></script>
</body>
</html>
