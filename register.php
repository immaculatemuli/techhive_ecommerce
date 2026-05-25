<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }

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
            $success = 'Account created! You can now sign in.';
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
            margin-bottom: 32px;
        }
        .auth-benefit {
            display: flex;
            align-items: center;
            gap: 11px;
            margin-bottom: 12px;
        }
        .auth-benefit-icon {
            width: 20px; height: 20px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 0.58rem; color: rgba(255,255,255,0.7);
        }
        .auth-benefit span {
            color: rgba(255,255,255,0.5);
            font-size: 0.83rem;
        }

        /* ── Right (white) ── */
        .auth-right {
            flex: 1;
            background: #fff;
            padding: 40px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow-y: auto;
        }

        @media (max-width: 640px) {
            .auth-card { flex-direction: column; border-radius: 16px; }
            .auth-left  { width: 100%; padding: 36px 28px; }
            .auth-right { padding: 32px 28px; }
        }
    </style>
</head>
<body>

<div class="auth-card">

    <!-- ── Left panel ── -->
    <div class="auth-left">
        <a href="/techhive/index.php"
           style="font-size:1.1rem;font-weight:900;color:#fff;text-decoration:none;letter-spacing:-0.4px;">
            TechHive
        </a>
        <h2>Join thousands of<br>tech shoppers.</h2>
        <p>Get exclusive deals, track your orders and checkout faster.</p>

        <div class="auth-benefit">
            <div class="auth-benefit-icon">✓</div>
            <span>Free delivery on orders above KSh 5,000</span>
        </div>
        <div class="auth-benefit">
            <div class="auth-benefit-icon">✓</div>
            <span>7-day hassle-free returns</span>
        </div>
        <div class="auth-benefit">
            <div class="auth-benefit-icon">✓</div>
            <span>Secure M-Pesa &amp; card payments</span>
        </div>
    </div>

    <!-- ── Right panel ── -->
    <div class="auth-right">

        <div style="margin-bottom:20px;">
            <h1 style="font-size:1.5rem;font-weight:800;color:#111827;letter-spacing:-0.5px;margin-bottom:5px;">
                Create account
            </h1>
    
        </div>

        <?php if ($success): ?>
            <div class="alert-success" style="margin-bottom:16px;">
                <?= $success ?>
                <a href="login.php" style="color:#15803d;font-weight:700;margin-left:6px;">Sign in →</a>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-error" style="margin-bottom:16px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form id="register-form" method="POST" novalidate>

            <div style="margin-bottom:12px;">
                <label for="username" class="label">Username</label>
                <input type="text" id="username" name="username" class="field"
                       placeholder="e.g. Imm"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                <p id="username-error" class="field-error"></p>
            </div>

            <div style="margin-bottom:12px;">
                <label for="email" class="label">Email address</label>
                <input type="email" id="email" name="email" class="field"
                       placeholder="imm@gmail.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <p id="email-error" class="field-error"></p>
            </div>

            <div style="margin-bottom:12px;">
                <label for="password" class="label">Password</label>
                <div style="position:relative;">
                    <input type="password" id="password" name="password" class="field"
                           placeholder="Min. 8 characters" style="padding-right:56px;">
                    <button type="button" id="toggle-password"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                                   background:none;border:none;color:#6b7280;font-size:0.75rem;
                                   font-weight:600;cursor:pointer;font-family:inherit;">Show</button>
                </div>
                <p id="password-error" class="field-error"></p>

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

            <div style="margin-bottom:20px;">
                <label for="confirm_password" class="label">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="field"
                       placeholder="••••••••">
                <p id="confirm-error" class="field-error"></p>
            </div>

            <button type="submit" class="btn-primary" style="width:100%;font-size:0.9rem;padding:12px;">
                Create account
            </button>

        </form>

        <p style="text-align:center;margin-top:16px;font-size:0.85rem;color:#6b7280;">
            Already have an account?
            <a href="login.php" style="color:#111827;font-weight:700;text-decoration:none;">Sign in</a>
        </p>

    </div>

</div>

<script src="/techhive/js/main.js"></script>
</body>
</html>
