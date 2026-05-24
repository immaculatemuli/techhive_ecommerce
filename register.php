<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['password']         ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 8) {
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
        $db   = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'customer')")
               ->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $success = 'Account created!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — TechHive</title>
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
            overflow-y: auto;
        }
        @media (max-width: 768px) {
            .auth-left { display: none; }
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
        Join thousands of<br>tech shoppers.
    </h2>
    <p style="color:rgba(255,255,255,0.4);font-size:0.95rem;line-height:1.7;margin-bottom:40px;">
        Get exclusive deals, track your orders, and checkout faster.
    </p>

    <div style="display:flex;flex-direction:column;gap:14px;">
        <?php foreach (['Free delivery on orders above KSh 5,000','7-day hassle-free returns','Secure M-Pesa & card payments'] as $b): ?>
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="width:20px;height:20px;background:rgba(255,255,255,0.08);border-radius:50%;
                         display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:0.65rem;">✓</span>
            <span style="color:rgba(255,255,255,0.55);font-size:0.875rem;"><?= $b ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Right panel: form -->
<div class="auth-right">
<div style="width:100%;max-width:380px;">

    <div style="margin-bottom:28px;">
        <h1 style="font-size:1.6rem;font-weight:800;color:#111827;letter-spacing:-0.5px;margin-bottom:6px;">Create account</h1>
        <p style="color:#6b7280;font-size:0.875rem;">Free — takes 30 seconds</p>
    </div>

    <?php if ($success): ?>
        <div class="alert-success">
            <?= $success ?>
            <a href="login.php" style="color:#15803d;font-weight:700;margin-left:6px;">Sign in →</a>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form id="register-form" method="POST" novalidate>

        <div style="margin-bottom:14px;">
            <label for="username" class="label">Username</label>
            <input type="text" id="username" name="username" class="field"
                placeholder="e.g. immaculate"
                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
            <p id="username-error" class="field-error"></p>
        </div>

        <div style="margin-bottom:14px;">
            <label for="email" class="label">Email address</label>
            <input type="email" id="email" name="email" class="field"
                placeholder="you@gmail.com"
                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <p id="email-error" class="field-error"></p>
        </div>

        <div style="margin-bottom:14px;">
            <label for="password" class="label">Password</label>
            <div style="position:relative;">
                <input type="password" id="password" name="password" class="field"
                    placeholder="Min. 8 characters" style="padding-right:56px;">
                <button type="button" id="toggle-password"
                    style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                           background:none;border:none;color:#6b7280;font-size:0.75rem;
                           font-weight:600;cursor:pointer;font-family:inherit;">
                    Show
                </button>
            </div>
            <p id="password-error" class="field-error"></p>
            <!-- Strength bars -->
            <div class="strength-bars" style="margin-top:8px;">
                <div id="bar-1" class="s-bar"></div>
                <div id="bar-2" class="s-bar"></div>
                <div id="bar-3" class="s-bar"></div>
                <div id="bar-4" class="s-bar"></div>
            </div>
            <p id="strength-label" style="font-size:0.73rem;color:#9ca3af;margin-top:5px;font-weight:500;">—</p>
            <!-- Requirements -->
            <ul class="req-list" style="margin-top:10px;">
                <li id="rule-length">At least 8 characters</li>
                <li id="rule-upper">One uppercase letter</li>
                <li id="rule-lower">One lowercase letter</li>
                <li id="rule-number">One number</li>
                <li id="rule-special">One special character</li>
            </ul>
        </div>

        <div style="margin-bottom:24px;">
            <label for="confirm_password" class="label">Confirm password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="field"
                placeholder="••••••••">
            <p id="confirm-error" class="field-error"></p>
        </div>

        <button type="submit" class="btn-primary" style="width:100%;font-size:0.925rem;padding:12px;">
            Create account
        </button>

    </form>

    <p style="text-align:center;margin-top:20px;font-size:0.85rem;color:#6b7280;">
        Already have an account?
        <a href="login.php" style="color:#111827;font-weight:700;text-decoration:none;">Sign in</a>
    </p>

</div>
</div>

<script src="/techhive/js/main.js"></script>
</body>
</html>
