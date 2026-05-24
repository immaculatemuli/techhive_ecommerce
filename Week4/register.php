<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['user_id'])) { header('Location: dashboard.php'); exit; }

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(htmlspecialchars($_POST['username'] ?? ''));
    $email    = trim(htmlspecialchars($_POST['email']    ?? ''));
    $password = $_POST['password']         ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $error = 'Password must include at least one uppercase letter.';
    } elseif (!preg_match('/[a-z]/', $password)) {
        $error = 'Password must include at least one lowercase letter.';
    } elseif (!preg_match('/[0-9]/', $password)) {
        $error = 'Password must include at least one number.';
    } elseif (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $error = 'Password must include at least one special character.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = 'An account with this email already exists.';
        } else {
            $insert = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'customer')");
            $insert->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT)]);
            $success = 'Account created.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register — TechHive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/techhive/css/style.css">
    <style>
        body::before { display: none; }
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 32px 24px; }
    </style>
</head>
<body>

<div style="width:100%;max-width:420px;">

    <div style="text-align:center;margin-bottom:28px;">
        <a href="index.php" class="brand" style="font-size:1.5rem;">Tech<span>Hive</span></a>
        <p style="color:#52525b;font-size:0.875rem;margin-top:8px;">Create your account</p>
    </div>

    <div class="card" style="padding:32px;">

        <?php if ($success): ?>
            <div class="alert-success">
                <?= $success ?>
                <a href="login.php" style="color:#4ade80;font-weight:600;margin-left:4px;">Sign in →</a>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert-error"><?= $error ?></div>
        <?php endif; ?>

        <form id="register-form" method="POST" novalidate>

            <!-- Username -->
            <div style="margin-bottom:14px;">
                <label for="username" class="label">Username</label>
                <input type="text" id="username" name="username" class="field"
                    placeholder="imm"
                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                <p id="username-error" class="field-error"></p>
            </div>

            <!-- Email -->
            <div style="margin-bottom:14px;">
                <label for="email" class="label">Email</label>
                <input type="email" id="email" name="email" class="field"
                    placeholder="you@gmail.com"
                    value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                <p id="email-error" class="field-error"></p>
            </div>

            <!-- Password -->
            <div style="margin-bottom:14px;">
                <label for="password" class="label">Password</label>
                <div style="position:relative;">
                    <input type="password" id="password" name="password" class="field"
                        placeholder="Min. 8 characters" style="padding-right:56px;">
                    <button type="button" id="toggle-password"
                        style="position:absolute;right:12px;top:50%;transform:translateY(-50%);
                               background:none;border:none;color:#52525b;font-size:0.75rem;
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
                <p id="strength-label" style="font-size:0.75rem;color:#3f3f46;margin-top:4px;">—</p>

              
            </div>

            <!-- Confirm password -->
            <div style="margin-bottom:24px;">
                <label for="confirm_password" class="label">Confirm password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="field"
                    placeholder="••••••••">
                <p id="confirm-error" class="field-error"></p>
            </div>

            <button type="submit" class="btn-primary">Create account</button>

        </form>

    </div>

    <p style="text-align:center;margin-top:18px;font-size:0.85rem;color:#52525b;">
        Already have an account?
        <a href="login.php" style="color:#818cf8;font-weight:500;text-decoration:none;">Sign in</a>
    </p>

</div>

<script src="/techhive/js/main.js"></script>
</body>
</html>
