<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$error   = '';
$success = '';
$devLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(strtolower($_POST['email'] ?? ''));

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Always show success (prevents email enumeration)
        $success = 'If that email is registered, a reset link has been sent.';

        if ($user) {
            // Delete any existing reset tokens for this email
            $db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

            $token = generateToken();
            // Use MySQL NOW() so expiry timezone always matches the comparison in reset_password.php
            $db->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))")
               ->execute([$email, $token]);

            $resetLink = APP_URL . '/reset_password.php?token=' . $token;

            $sent = sendEmail(
                $email,
                'Reset your TechHive password',
                "<h2>Password Reset — TechHive</h2>
                 <p>Hi! You requested a password reset for your TechHive account.</p>
                 <p>Click the button below to set a new password. This link expires in <strong>1 hour</strong>.</p>
                 <p style='margin:24px 0;'>
                   <a href='{$resetLink}' style='background:#1e3a8a;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:700;'>
                     Reset Password
                   </a>
                 </p>
                 <p style='color:#6b7280;font-size:0.85em;'>Or copy this link: {$resetLink}</p>
                 <p style='color:#6b7280;font-size:0.85em;'>If you didn't request this, you can safely ignore this email.</p>"
            );

            // Show link on screen only if email delivery failed (SMTP not configured)
            if (!$sent) {
                $devLink = $resetLink;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | TechHive</title>
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
            min-height: 480px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 24px 80px rgba(0,0,0,0.13);
        }
        .auth-left {
            width: 44%;
            background: #0f172a;
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .auth-left h2 { font-size:1.65rem;font-weight:900;color:#fff;line-height:1.2;letter-spacing:-0.8px;margin:32px 0 12px; }
        .auth-left p  { color:rgba(255,255,255,0.4);font-size:0.9rem;line-height:1.7; }
        .auth-right {
            flex: 1;
            background: #fff;
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        @media (max-width:640px) {
            .auth-card { flex-direction:column; border-radius:16px; }
            .auth-left  { width:100%; padding:36px 28px; }
            .auth-right { padding:36px 28px; }
        }
    </style>
</head>
<body>

<div class="auth-card">

    <div class="auth-left">
        <a href="/techhive/Project/index.php"
           style="font-size:1.1rem;font-weight:900;color:#fff;text-decoration:none;letter-spacing:-0.4px;">TechHive</a>
        <h2>Reset your<br>password.</h2>
        <p>Enter your email and we'll send a secure link to create a new password.</p>
    </div>

    <div class="auth-right">

        <div style="margin-bottom:28px;">
            <h1 style="font-size:1.5rem;font-weight:800;color:#111827;letter-spacing:-0.5px;margin-bottom:5px;">Forgot password?</h1>
            <p style="color:#6b7280;font-size:0.85rem;">We'll email you a reset link.</p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error" style="margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert-success" style="margin-bottom:16px;"><?= htmlspecialchars($success) ?></div>
            <?php if ($devLink): ?>
            <div style="background:#fefce8;border:1px solid #fde047;border-radius:10px;padding:14px 16px;margin-bottom:16px;font-size:0.82rem;">
                <strong style="color:#854d0e;">Dev mode — reset link:</strong><br>
                <a href="<?= htmlspecialchars($devLink) ?>" style="color:#1e3a8a;word-break:break-all;">
                    <?= htmlspecialchars($devLink) ?>
                </a>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" novalidate>
            <div style="margin-bottom:20px;">
                <label for="email" class="label">Email address</label>
                <input type="email" id="email" name="email" class="field"
                       placeholder="you@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            </div>
            <button type="submit" class="btn-primary" style="width:100%;font-size:0.9rem;padding:12px;">
                Send reset link
            </button>
        </form>
        <?php endif; ?>

        <p style="text-align:center;margin-top:20px;font-size:0.85rem;color:#6b7280;">
            Remembered it?
            <a href="login.php" style="color:#111827;font-weight:700;text-decoration:none;">Sign in</a>
        </p>

    </div>

</div>

</body>
</html>
