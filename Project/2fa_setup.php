<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db     = getDB();
$userId = (int)$_SESSION['user_id'];

$stmt = $db->prepare("SELECT email, two_fa_enabled, two_fa_secret FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$error   = '';
$success = '';
$step    = 'intro'; // intro | scan | verify | done | disable

// If already enabled, offer disable option
if ($user['two_fa_enabled']) {
    $step = 'manage';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Generate a new secret and show QR
    if ($action === 'start_setup') {
        $secret = totpGenerateSecret();
        $_SESSION['2fa_setup_secret'] = $secret;
        $step = 'scan';
    }

    // Verify code before enabling
    if ($action === 'verify_enable') {
        $code   = preg_replace('/\D/', '', $_POST['code'] ?? '');
        $secret = $_SESSION['2fa_setup_secret'] ?? '';
        if (empty($secret)) {
            $error = 'Session expired. Please restart setup.';
            $step  = 'intro';
        } elseif (!totpVerify($secret, $code)) {
            $error = 'Incorrect code. Check your authenticator app and try again.';
            $step  = 'scan';
        } else {
            $db->prepare("UPDATE users SET two_fa_enabled = 1, two_fa_secret = ? WHERE id = ?")
               ->execute([$secret, $userId]);
            unset($_SESSION['2fa_setup_secret']);
            $success = 'Two-factor authentication is now enabled on your account.';
            $user['two_fa_enabled'] = 1;
            $step = 'done';
        }
    }

    // Disable 2FA (require current password for safety)
    if ($action === 'disable_2fa') {
        $password = $_POST['password'] ?? '';
        $row = $db->prepare("SELECT password FROM users WHERE id = ?");
        $row->execute([$userId]);
        $row = $row->fetch();
        if (!password_verify($password, $row['password'])) {
            $error = 'Incorrect password.';
            $step  = 'manage';
        } else {
            $db->prepare("UPDATE users SET two_fa_enabled = 0, two_fa_secret = NULL WHERE id = ?")->execute([$userId]);
            $user['two_fa_enabled'] = 0;
            $success = 'Two-factor authentication has been disabled.';
            $step    = 'intro';
        }
    }
}

// Build QR URI if on scan step
$qrUri   = '';
$secret  = $_SESSION['2fa_setup_secret'] ?? '';
if ($step === 'scan' && $secret) {
    $qrUri  = totpQrUri($secret, $user['email']);
    $qrImg  = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($qrUri);
}

$pageTitle = 'Two-Factor Authentication | TechHive';
?>
<?php include 'includes/header.php'; ?>

<div style="max-width:600px;margin:0 auto;padding:48px 24px;">

    <div style="margin-bottom:28px;">
        <p style="font-size:0.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;font-weight:600;margin-bottom:4px;">Security</p>
        <h1 style="font-size:1.8rem;font-weight:800;color:var(--text);">Two-Factor Authentication</h1>
    </div>

    <?php if ($error): ?>
        <div class="alert-error" style="margin-bottom:20px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert-success" style="margin-bottom:20px;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- ── Intro / start ── -->
    <?php if ($step === 'intro'): ?>
    <div class="card" style="padding:32px;text-align:center;">
        <div style="font-size:3rem;margin-bottom:16px;">🔐</div>
        <h2 style="font-size:1.1rem;font-weight:800;color:var(--text);margin-bottom:10px;">Add an extra layer of security</h2>
        <p style="color:var(--muted);font-size:0.9rem;margin-bottom:28px;max-width:380px;margin-left:auto;margin-right:auto;">
            Use an authenticator app (Google Authenticator, Authy, etc.) to generate a 6-digit code each time you sign in.
        </p>
        <form method="POST">
            <input type="hidden" name="action" value="start_setup">
            <button type="submit" class="btn-primary" style="padding:12px 32px;">Set up 2FA</button>
        </form>
    </div>

    <!-- ── Already enabled ── -->
    <?php elseif ($step === 'manage'): ?>
    <div class="card" style="padding:32px;">
        <div style="display:flex;align-items:center;gap:14px;margin-bottom:24px;">
            <div style="width:48px;height:48px;border-radius:50%;background:#f0fdf4;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;">✅</div>
            <div>
                <p style="font-weight:700;color:var(--text);margin-bottom:2px;">2FA is enabled</p>
                <p style="color:var(--muted);font-size:0.85rem;">Your account requires a code from your authenticator app on each login.</p>
            </div>
        </div>
        <hr style="border:none;border-top:1px solid var(--border);margin-bottom:24px;">
        <h3 style="font-size:0.9rem;font-weight:700;color:#dc2626;margin-bottom:12px;">Disable 2FA</h3>
        <p style="color:var(--muted);font-size:0.85rem;margin-bottom:16px;">Enter your account password to turn off two-factor authentication.</p>
        <form method="POST">
            <input type="hidden" name="action" value="disable_2fa">
            <div style="margin-bottom:16px;">
                <label class="label">Account password</label>
                <input type="password" name="password" class="field" placeholder="••••••••" required>
            </div>
            <button type="submit" style="padding:10px 24px;background:#dc2626;color:#fff;border:none;border-radius:8px;font-weight:700;font-size:0.875rem;cursor:pointer;">
                Disable 2FA
            </button>
        </form>
    </div>

    <!-- ── Scan QR ── -->
    <?php elseif ($step === 'scan'): ?>
    <div class="card" style="padding:32px;">
        <h2 style="font-size:1rem;font-weight:800;color:var(--text);margin-bottom:6px;">Step 1 — Scan the QR code</h2>
        <p style="color:var(--muted);font-size:0.85rem;margin-bottom:24px;">
            Open your authenticator app and scan the QR code below. If you can't scan, enter the secret key manually.
        </p>

        <div style="text-align:center;margin-bottom:24px;">
            <img src="<?= htmlspecialchars($qrImg) ?>" alt="QR Code"
                 style="border:6px solid #fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1);"
                 width="200" height="200">
        </div>

        <div style="background:#f8fafc;border:1px solid var(--border);border-radius:10px;padding:14px 16px;margin-bottom:24px;">
            <p style="font-size:0.72rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:6px;">Manual entry key</p>
            <p style="font-family:monospace;font-size:1rem;font-weight:700;color:var(--text);letter-spacing:3px;word-break:break-all;"><?= htmlspecialchars($secret) ?></p>
        </div>

        <h2 style="font-size:1rem;font-weight:800;color:var(--text);margin-bottom:6px;">Step 2 — Enter the 6-digit code</h2>
        <p style="color:var(--muted);font-size:0.85rem;margin-bottom:16px;">Enter the code shown in your authenticator app to confirm it's working.</p>

        <form method="POST">
            <input type="hidden" name="action" value="verify_enable">
            <div style="margin-bottom:20px;">
                <label class="label">6-digit code</label>
                <input type="text" name="code" class="field"
                       placeholder="000 000"
                       maxlength="6"
                       inputmode="numeric" autocomplete="one-time-code"
                       autofocus
                       style="font-size:1.4rem;letter-spacing:8px;text-align:center;font-weight:700;">
            </div>
            <button type="submit" class="btn-primary" style="width:100%;padding:12px;">
                Verify &amp; Enable 2FA
            </button>
        </form>
    </div>

    <!-- ── Done ── -->
    <?php elseif ($step === 'done'): ?>
    <div class="card" style="padding:40px;text-align:center;">
        <div style="font-size:3rem;margin-bottom:16px;">🎉</div>
        <h2 style="font-size:1.1rem;font-weight:800;color:var(--text);margin-bottom:10px;">2FA enabled successfully!</h2>
        <p style="color:var(--muted);font-size:0.9rem;margin-bottom:28px;">You'll be asked for a code from your authenticator app each time you sign in.</p>
        <a href="dashboard.php" class="btn-primary" style="text-decoration:none;display:inline-block;padding:12px 32px;">Go to dashboard</a>
    </div>
    <?php endif; ?>

    <div style="margin-top:24px;">
        <a href="profile.php" style="color:var(--muted);font-size:0.875rem;font-weight:600;text-decoration:none;">← Back to profile</a>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
