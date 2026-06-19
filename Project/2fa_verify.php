<?php
require_once 'config.php';
session_start();

// Must have a pending 2FA login staged
if (!isset($_SESSION['2fa_pending_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = preg_replace('/\D/', '', $_POST['code'] ?? '');

    $db   = getDB();
    $stmt = $db->prepare("SELECT two_fa_secret FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['2fa_pending_id']]);
    $user = $stmt->fetch();

    if (!$user || !$user['two_fa_secret']) {
        $error = 'Authentication error. Please sign in again.';
    } elseif (!totpVerify($user['two_fa_secret'], $code)) {
        $error = 'Incorrect code. Check your authenticator app and try again.';
    } else {
        // Promote the staged login to a real session
        $_SESSION['user_id']  = $_SESSION['2fa_pending_id'];
        $_SESSION['username'] = $_SESSION['2fa_pending_username'];
        $_SESSION['role']     = $_SESSION['2fa_pending_role'];
        unset($_SESSION['2fa_pending_id'], $_SESSION['2fa_pending_username'], $_SESSION['2fa_pending_role']);

        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Two-Factor Authentication | TechHive</title>
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
        .twofa-card {
            background: #fff;
            border-radius: 20px;
            padding: 56px 48px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 24px 80px rgba(0,0,0,0.11);
            text-align: center;
        }
    </style>
</head>
<body>

<div class="twofa-card">

    <div style="font-size:2.6rem;margin-bottom:20px;">🔐</div>

    <h1 style="font-size:1.4rem;font-weight:800;color:#111827;letter-spacing:-0.5px;margin-bottom:8px;">
        Two-factor verification
    </h1>
    <p style="color:#6b7280;font-size:0.875rem;margin-bottom:28px;">
        Open your authenticator app and enter the 6-digit code.
    </p>

    <?php if ($error): ?>
        <div class="alert-error" style="margin-bottom:20px;text-align:left;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div style="margin-bottom:24px;">
            <input type="text" name="code" class="field"
                   placeholder="000 000"
                   maxlength="6"
                   inputmode="numeric" autocomplete="one-time-code"
                   autofocus
                   style="font-size:1.8rem;letter-spacing:10px;text-align:center;font-weight:800;padding:16px 0;">
        </div>

        <button type="submit" class="btn-primary" style="width:100%;padding:13px;font-size:1rem;">
            Verify
        </button>
    </form>

    <p style="margin-top:24px;font-size:0.82rem;color:#9ca3af;">
        Lost access to your app?
        <a href="logout.php" style="color:#6b7280;font-weight:600;text-decoration:none;">Cancel sign in</a>
    </p>

    <p style="margin-top:32px;">
        <a href="/techhive/Project/index.php"
           style="font-size:1rem;font-weight:900;color:#0f172a;text-decoration:none;">TechHive</a>
    </p>

</div>

</body>
</html>
