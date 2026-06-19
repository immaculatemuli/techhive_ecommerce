<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

$token   = trim($_GET['token'] ?? $_POST['token'] ?? '');
$error   = '';
$success = '';
$valid   = false;
$email   = '';

if (empty($token)) {
    $error = 'No reset token provided.';
} else {
    $db   = getDB();
    $stmt = $db->prepare("SELECT email FROM password_resets WHERE token = ? AND expires_at > NOW() LIMIT 1");
    $stmt->execute([$token]);
    $row = $stmt->fetch();
    if ($row) {
        $valid = true;
        $email = $row['email'];
    } else {
        $error = 'This reset link is invalid or has expired. <a href="forgot_password.php" style="color:#dc2626;font-weight:700;">Request a new one</a>.';
    }
}

if ($valid && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password']         ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must include an uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = 'Password must include a lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must include a number.';
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $error = 'Password must include a special character.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db = getDB();
        $db->prepare("UPDATE users SET password = ? WHERE email = ?")
           ->execute([password_hash($password, PASSWORD_DEFAULT), $email]);

        // Invalidate all reset tokens for this email
        $db->prepare("DELETE FROM password_resets WHERE email = ?")->execute([$email]);

        // Clear any remember-me cookie / token
        $db->prepare("UPDATE users SET remember_token = NULL WHERE email = ?")->execute([$email]);
        setcookie('remember_token', '', time() - 3600, '/', '', false, true);

        $success = 'Your password has been updated. You can now sign in.';
        $valid   = false;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | TechHive</title>
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
            min-height: 520px;
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
        <h2>Create a new<br>password.</h2>
        <p>Choose a strong password with uppercase, lowercase, a number and a special character.</p>
    </div>

    <div class="auth-right">

        <div style="margin-bottom:28px;">
            <h1 style="font-size:1.5rem;font-weight:800;color:#111827;letter-spacing:-0.5px;margin-bottom:5px;">New password</h1>
            <p style="color:#6b7280;font-size:0.85rem;">
                <?= $email ? 'Resetting for <strong>' . htmlspecialchars($email) . '</strong>' : '' ?>
            </p>
        </div>

        <?php if ($error): ?>
            <div class="alert-error" style="margin-bottom:16px;"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert-success" style="margin-bottom:16px;"><?= htmlspecialchars($success) ?></div>
            <a href="login.php" class="btn-primary" style="display:inline-block;text-decoration:none;text-align:center;padding:12px;width:100%;box-sizing:border-box;">
                Sign in
            </a>
        <?php elseif ($valid): ?>
        <form method="POST" novalidate>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div style="margin-bottom:14px;">
                <label for="password" class="label">New password</label>
                <div style="position:relative;">
                    <input type="password" id="password" name="password" class="field"
                           placeholder="Min. 8 characters" style="padding-right:56px;">
                    <button type="button" id="toggle-password"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                   background:none;border:none;color:#6b7280;font-size:0.75rem;
                                   font-weight:600;cursor:pointer;font-family:inherit;">Show</button>
                </div>

                <!-- Strength bars -->
                <div class="strength-bars" style="margin-top:7px;">
                    <div id="bar-1" class="s-bar"></div>
                    <div id="bar-2" class="s-bar"></div>
                    <div id="bar-3" class="s-bar"></div>
                    <div id="bar-4" class="s-bar"></div>
                </div>
                <p id="strength-label" style="font-size:0.72rem;color:#9ca3af;margin-top:4px;font-weight:500;">—</p>

                <ul class="req-list" style="margin-top:8px;">
                    <li id="rule-length">At least 8 characters</li>
                    <li id="rule-upper">One uppercase letter</li>
                    <li id="rule-lower">One lowercase letter</li>
                    <li id="rule-number">One number</li>
                    <li id="rule-special">One special character</li>
                </ul>
            </div>

            <div style="margin-bottom:24px;">
                <label for="confirm_password" class="label">Confirm new password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="field"
                       placeholder="••••••••">
                <p id="confirm-error" class="field-error"></p>
            </div>

            <button type="submit" class="btn-primary" style="width:100%;font-size:0.9rem;padding:12px;">
                Update password
            </button>
        </form>
        <?php endif; ?>

        <?php if (!$success): ?>
        <p style="text-align:center;margin-top:20px;font-size:0.85rem;color:#6b7280;">
            <a href="login.php" style="color:#111827;font-weight:700;text-decoration:none;">Back to sign in</a>
        </p>
        <?php endif; ?>

    </div>

</div>

<script src="/techhive/Project/js/main.js"></script>
</body>
</html>
