<?php
require_once 'config.php';
session_start();

$token   = trim($_GET['token'] ?? '');
$error   = '';
$success = '';

if (empty($token)) {
    $error = 'No verification token provided.';
} else {
    $db   = getDB();
    $stmt = $db->prepare("SELECT user_id FROM email_verifications WHERE token = ? AND expires_at > NOW() LIMIT 1");
    $stmt->execute([$token]);
    $row = $stmt->fetch();

    if ($row) {
        $db->prepare("UPDATE users SET email_verified = 1 WHERE id = ?")->execute([$row['user_id']]);
        $db->prepare("DELETE FROM email_verifications WHERE token = ?")->execute([$token]);
        $success = 'Your email has been verified! You can now sign in.';
    } else {
        $error = 'This verification link is invalid or has expired.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email | TechHive</title>
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
        .verify-card {
            background: #fff;
            border-radius: 20px;
            padding: 64px 48px;
            text-align: center;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 24px 80px rgba(0,0,0,0.11);
        }
        .verify-icon {
            width: 72px; height: 72px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 24px;
        }
    </style>
</head>
<body>

<div class="verify-card">

    <?php if ($success): ?>
        <div class="verify-icon" style="background:#f0fdf4;">✅</div>
        <h1 style="font-size:1.4rem;font-weight:800;color:#111827;margin-bottom:8px;">Email verified!</h1>
        <p style="color:#6b7280;font-size:0.9rem;margin-bottom:28px;"><?= htmlspecialchars($success) ?></p>
        <a href="login.php" class="btn-primary" style="text-decoration:none;display:inline-block;padding:12px 32px;">
            Sign in now
        </a>
    <?php else: ?>
        <div class="verify-icon" style="background:#fef2f2;">❌</div>
        <h1 style="font-size:1.4rem;font-weight:800;color:#111827;margin-bottom:8px;">Verification failed</h1>
        <p style="color:#6b7280;font-size:0.9rem;margin-bottom:28px;"><?= $error ?></p>
        <a href="register.php" style="color:#1e3a8a;font-weight:700;text-decoration:none;">Register again</a>
        &nbsp;·&nbsp;
        <a href="login.php" style="color:#6b7280;font-weight:600;text-decoration:none;">Sign in</a>
    <?php endif; ?>

    <p style="margin-top:32px;">
        <a href="/techhive/Project/index.php"
           style="font-size:1rem;font-weight:900;color:#0f172a;text-decoration:none;letter-spacing:-0.4px;">TechHive</a>
    </p>

</div>

</body>
</html>
