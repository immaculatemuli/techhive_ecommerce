<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$email   = trim(strtolower($_GET['email'] ?? ''));
$error   = '';
$success = '';
$devLink = '';

if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $db   = getDB();
    $stmt = $db->prepare("SELECT id, username, email_verified FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && !$user['email_verified']) {
        // Delete any old tokens and issue a fresh one
        $db->prepare("DELETE FROM email_verifications WHERE user_id = ?")->execute([$user['id']]);

        $token = generateToken();
        $db->prepare("INSERT INTO email_verifications (user_id, token, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR))")
           ->execute([$user['id'], $token]);

        $verifyLink = APP_URL . '/verify_email.php?token=' . $token;
        $sent = sendEmail(
            $email,
            'Verify your TechHive email',
            "<h2>Email Verification — TechHive</h2>
             <p>Click the button below to verify your email. It expires in 24 hours.</p>
             <p style='margin:24px 0;'>
               <a href='{$verifyLink}' style='background:#1e3a8a;color:#fff;padding:12px 24px;border-radius:8px;text-decoration:none;font-weight:700;'>
                 Verify Email
               </a>
             </p>
             <p style='color:#6b7280;font-size:0.85em;'>Or copy: {$verifyLink}</p>"
        );
        if (!$sent) $devLink = $verifyLink;
        $success = 'Verification email resent successfully.';
    } else {
        // Don't reveal whether email exists or is already verified
        $success = 'If your email is unverified, a new link has been sent.';
    }
} else {
    $error = 'Invalid email address.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resend Verification | TechHive</title>
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
            padding: 56px 44px;
            text-align: center;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 24px 80px rgba(0,0,0,0.11);
        }
    </style>
</head>
<body>
<div class="verify-card">
    <h1 style="font-size:1.4rem;font-weight:800;color:#111827;margin-bottom:8px;">Resend Verification</h1>

    <?php if ($error): ?>
        <div class="alert-error" style="margin:16px 0;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert-success" style="margin:16px 0;"><?= htmlspecialchars($success) ?></div>
        <?php if ($devLink): ?>
        <div style="background:#fefce8;border:1px solid #fde047;border-radius:10px;padding:14px 16px;margin:12px 0;font-size:0.82rem;text-align:left;">
            <strong style="color:#854d0e;">Dev mode — verify link:</strong><br>
            <a href="<?= htmlspecialchars($devLink) ?>" style="color:#1e3a8a;word-break:break-all;">
                <?= htmlspecialchars($devLink) ?>
            </a>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <p style="margin-top:20px;font-size:0.85rem;color:#6b7280;">
        <a href="login.php" style="color:#111827;font-weight:700;text-decoration:none;">Back to sign in</a>
    </p>

    <p style="margin-top:28px;">
        <a href="/techhive/Project/index.php"
           style="font-size:1rem;font-weight:900;color:#0f172a;text-decoration:none;">TechHive</a>
    </p>
</div>
</body>
</html>
