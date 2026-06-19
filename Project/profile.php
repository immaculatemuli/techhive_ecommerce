<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db     = getDB();
$userId = (int)$_SESSION['user_id'];

$stmt = $db->prepare("SELECT username, email, phone, bio, profile_image, two_fa_enabled, email_verified FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

$error   = '';
$success = '';
$section = $_GET['section'] ?? 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_info') {
        $username = trim($_POST['username'] ?? '');
        $email    = trim(strtolower($_POST['email'] ?? ''));
        $phone    = trim($_POST['phone'] ?? '');
        $bio      = trim($_POST['bio']   ?? '');

        if (empty($username) || empty($email)) {
            $error = 'Username and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email.';
        } else {
            $check = $db->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
            $check->execute([$email, $username, $userId]);
            if ($check->fetch()) {
                $error = 'That username or email is already taken.';
            } else {
                $db->prepare("UPDATE users SET username = ?, email = ?, phone = ?, bio = ? WHERE id = ?")
                   ->execute([$username, $email, $phone, $bio, $userId]);
                $_SESSION['username'] = $username;
                $user['username'] = $username;
                $user['email']    = $email;
                $user['phone']    = $phone;
                $user['bio']      = $bio;
                $success = 'Profile updated successfully.';
            }
        }
        $section = 'info';
    }

    if ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']      ?? '';
        $confirm = $_POST['confirm_password']  ?? '';

        $row = $db->prepare("SELECT password FROM users WHERE id = ?");
        $row->execute([$userId]);
        $row = $row->fetch();

        if (!password_verify($current, $row['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($new) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif (!preg_match('/[A-Z]/', $new)) {
            $error = 'New password must include an uppercase letter.';
        } elseif (!preg_match('/[a-z]/', $new)) {
            $error = 'New password must include a lowercase letter.';
        } elseif (!preg_match('/[0-9]/', $new)) {
            $error = 'New password must include a number.';
        } elseif (!preg_match('/[^A-Za-z0-9]/', $new)) {
            $error = 'New password must include a special character.';
        } elseif ($new !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $db->prepare("UPDATE users SET password = ? WHERE id = ?")
               ->execute([password_hash($new, PASSWORD_DEFAULT), $userId]);
            $db->prepare("UPDATE users SET remember_token = NULL WHERE id = ?")->execute([$userId]);
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            $success = 'Password changed successfully.';
        }
        $section = 'password';
    }

    if ($action === 'upload_avatar') {
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Please choose an image file.';
        } else {
            $file    = $_FILES['avatar'];
            $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $finfo   = finfo_open(FILEINFO_MIME_TYPE);
            $mime    = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $allowed)) {
                $error = 'Only JPG, PNG, WebP, or GIF images allowed.';
            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $error = 'Image must be under 2 MB.';
            } else {
                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
                $dest     = __DIR__ . '/images/' . $filename;
                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    if (!empty($user['profile_image']) && !str_starts_with($user['profile_image'], 'http')) {
                        $old = __DIR__ . '/images/' . $user['profile_image'];
                        if (file_exists($old)) unlink($old);
                    }
                    $db->prepare("UPDATE users SET profile_image = ? WHERE id = ?")->execute([$filename, $userId]);
                    $user['profile_image'] = $filename;
                    $success = 'Profile picture updated.';
                } else {
                    $error = 'Upload failed. Check folder permissions.';
                }
            }
        }
        $section = 'info';
    }
}

$pageTitle = 'My Profile | TechHive';
$avatarSrc = !empty($user['profile_image'])
    ? (str_starts_with($user['profile_image'], 'http')
        ? $user['profile_image']
        : '/techhive/Project/images/' . htmlspecialchars($user['profile_image']))
    : null;
?>
<?php include 'includes/header.php'; ?>

<!-- ═══════════════════════════════════════════
     Profile page — CSS
     • Flexbox for all layouts
     • Responsive profile image
     • Media queries: mobile (≤640px) & desktop (≥641px)
════════════════════════════════════════════ -->
<style>
    /* ── Page wrapper ── */
    .profile-page {
        max-width: 860px;
        margin: 0 auto;
        padding: 48px 24px;
    }

    /* ── Page heading ── */
    .profile-heading {
        margin-bottom: 32px;
    }
    .profile-heading .eyebrow {
        font-size: 0.72rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .profile-heading h1 {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--text);
    }

    /* ── Hero card — Flexbox row ── */
    .profile-hero {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 24px;
        padding: 28px;
        margin-bottom: 28px;
    }

    /* ── Avatar — responsive image ── */
    .avatar-wrap {
        flex-shrink: 0;
        position: relative;
    }
    .avatar-img {
        width: 96px;
        height: 96px;
        max-width: 100%;          /* responsive: never overflows its container */
        border-radius: 50%;
        object-fit: cover;        /* fills the circle without distortion */
        border: 3px solid var(--border);
        display: block;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .avatar-img:hover {
        transform: scale(1.06);
        box-shadow: 0 6px 24px rgba(0,0,0,0.14);
    }
    .avatar-initials {
        width: 96px;
        height: 96px;
        max-width: 100%;
        border-radius: 50%;
        background: #1e3a8a;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 900;
        color: #fff;
    }

    /* ── Info section ── */
    .profile-info {
        flex: 1;
        min-width: 0;             /* prevents flex overflow */
    }
    .profile-name {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--text);
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .profile-email {
        color: var(--muted);
        font-size: 0.875rem;
        margin-bottom: 8px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* ── Badge row ── */
    .badge-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .badge {
        font-size: 0.72rem;
        padding: 3px 10px;
        border-radius: 99px;
        font-weight: 600;
        border: 1px solid;
    }
    .badge-green { background: #f0fdf4; border-color: #86efac; color: #15803d; }
    .badge-red   { background: #fef2f2; border-color: #fca5a5; color: #dc2626; }
    .badge-blue  { background: #eff6ff; border-color: #93c5fd; color: #1d4ed8; }

    /* ── Avatar upload ── */
    .avatar-upload {
        flex-shrink: 0;
    }
    .upload-label {
        display: inline-block;
        cursor: pointer;
        font-size: 0.78rem;
        font-weight: 600;
        color: #1e3a8a;
        text-decoration: underline;
        padding: 6px 0;
    }
    .upload-label:hover { color: #1e40af; }

    /* ── Tab navigation ── */
    .profile-tabs {
        display: flex;
        gap: 0;
        margin-bottom: 24px;
        border-bottom: 1px solid var(--border);
        overflow-x: auto;         /* scrollable on very small screens */
    }
    .profile-tab {
        padding: 10px 20px;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        border-bottom: 2px solid transparent;
        color: var(--muted);
        margin-bottom: -1px;
        white-space: nowrap;
        transition: color 0.15s, border-color 0.15s;
    }
    .profile-tab:hover  { color: var(--text); }
    .profile-tab.active { border-bottom-color: #1e3a8a; color: #1e3a8a; }

    /* ── Form card ── */
    .profile-card {
        padding: 32px;
        margin-bottom: 24px;
    }
    .profile-card h2 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 20px;
    }

    /* ── Form rows — Flexbox ── */
    .form-row {
        display: flex;
        flex-direction: row;
        gap: 16px;
        margin-bottom: 16px;
    }
    .form-col {
        flex: 1;
        min-width: 0;
    }
    .form-field {
        margin-bottom: 16px;
    }

    /* ── Form actions — Flexbox ── */
    .form-actions {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 8px;
    }

    /* ── Footer actions ── */
    .profile-footer {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 28px;
    }

    /* ══════════════════════════════════════
       MEDIA QUERY — Mobile (≤ 640px)
    ══════════════════════════════════════ */
    @media (max-width: 640px) {
        .profile-page {
            padding: 24px 16px;
        }

        /* Stack hero vertically and centre-align on mobile */
        .profile-hero {
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 24px 20px;
            gap: 16px;
        }

        /* Larger avatar on mobile for better visibility */
        .avatar-img,
        .avatar-initials {
            width: 110px;
            height: 110px;
        }
        .avatar-initials {
            font-size: 2.6rem;
        }

        /* Centre badges on mobile */
        .badge-row {
            justify-content: center;
        }

        /* Stack form columns on mobile */
        .form-row {
            flex-direction: column;
            gap: 12px;
        }

        /* Full-width buttons on mobile */
        .form-actions {
            flex-direction: column;
            align-items: stretch;
        }
        .form-actions button,
        .form-actions a {
            width: 100%;
            text-align: center;
            box-sizing: border-box;
        }

        /* Card padding reduced on mobile */
        .profile-card {
            padding: 20px 16px;
        }

        .profile-heading h1 {
            font-size: 1.4rem;
        }

        .profile-footer {
            flex-direction: column;
        }
        .profile-footer a {
            text-align: center;
        }
    }

    /* ══════════════════════════════════════
       MEDIA QUERY — Desktop (≥ 641px)
    ══════════════════════════════════════ */
    @media (min-width: 641px) {
        /* Side-by-side form fields */
        .form-row {
            flex-direction: row;
        }

        /* Constrain password form width */
        .password-form {
            max-width: 480px;
        }
    }

    /* ══════════════════════════════════════
       MEDIA QUERY — Large desktop (≥ 1024px)
    ══════════════════════════════════════ */
    @media (min-width: 1024px) {
        .profile-page {
            padding: 56px 0;
        }
        .avatar-img,
        .avatar-initials {
            width: 104px;
            height: 104px;
        }
    }
</style>

<div class="profile-page">

    <!-- Page heading -->
    <div class="profile-heading">
        <p class="eyebrow">Account</p>
        <h1>My Profile</h1>
    </div>

    <!-- Hero card: avatar + info + upload -->
    <div class="card profile-hero">

        <!-- Responsive profile image -->
        <div class="avatar-wrap">
            <?php if ($avatarSrc): ?>
                <img src="<?= $avatarSrc ?>"
                     alt="Profile picture of <?= htmlspecialchars($user['username']) ?>"
                     class="avatar-img">
            <?php else: ?>
                <div class="avatar-initials">
                    <?= strtoupper(mb_substr($user['username'], 0, 1)) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- User info -->
        <div class="profile-info">
            <p class="profile-name"><?= htmlspecialchars($user['username']) ?></p>
            <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
            <div class="badge-row">
                <?php if ($user['email_verified']): ?>
                    <span class="badge badge-green">Email verified</span>
                <?php else: ?>
                    <span class="badge badge-red">Email unverified</span>
                <?php endif; ?>
                <?php if ($user['two_fa_enabled']): ?>
                    <span class="badge badge-blue">2FA on</span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Avatar upload -->
        <div class="avatar-upload">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload_avatar">
                <label class="upload-label">
                    Change photo
                    <input type="file" name="avatar" accept="image/*"
                           style="display:none;" onchange="this.form.submit()">
                </label>
            </form>
        </div>

    </div>

    <!-- Tab navigation -->
    <nav class="profile-tabs">
        <?php foreach (['info' => 'Profile Info', 'password' => 'Change Password'] as $key => $label): ?>
            <a href="?section=<?= $key ?>"
               class="profile-tab <?= $section === $key ? 'active' : '' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Alerts -->
    <?php if ($error): ?>
        <div class="alert-error" style="margin-bottom:20px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert-success" style="margin-bottom:20px;"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- ── Profile Info tab ── -->
    <?php if ($section === 'info'): ?>
    <div class="card profile-card">
        <h2>Personal information</h2>
        <form method="POST" novalidate>
            <input type="hidden" name="action" value="update_info">

            <!-- Flexbox row: Username + Email side-by-side on desktop, stacked on mobile -->
            <div class="form-row">
                <div class="form-col">
                    <label class="label">Username</label>
                    <input type="text" name="username" class="field"
                           value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                <div class="form-col">
                    <label class="label">Email address</label>
                    <input type="email" name="email" class="field"
                           value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
            </div>

            <div class="form-field">
                <label class="label">
                    Phone number
                    <span style="color:var(--muted);font-weight:400;">(optional)</span>
                </label>
                <input type="tel" name="phone" class="field"
                       placeholder="+254 7XX XXX XXX"
                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
            </div>

            <div class="form-field">
                <label class="label">
                    Bio
                    <span style="color:var(--muted);font-weight:400;">(optional)</span>
                </label>
                <textarea name="bio" class="field" rows="3"
                          placeholder="A short description about yourself..."
                          style="resize:vertical;"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
            </div>

            <!-- Flexbox actions row -->
            <div class="form-actions">
                <button type="submit" class="btn-primary" style="padding:11px 28px;">
                    Save changes
                </button>
                <a href="2fa_setup.php"
                   style="padding:10px 20px;border:1px solid var(--border);border-radius:8px;color:var(--text);font-size:0.875rem;font-weight:600;text-decoration:none;">
                    <?= $user['two_fa_enabled'] ? 'Manage 2FA' : 'Enable 2FA' ?>
                </a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- ── Change Password tab ── -->
    <?php if ($section === 'password'): ?>
    <div class="card profile-card">
        <h2>Change password</h2>
        <form method="POST" novalidate class="password-form">
            <input type="hidden" name="action" value="change_password">

            <div class="form-field">
                <label class="label">Current password</label>
                <div style="position:relative;">
                    <input type="password" name="current_password" id="cur-pw" class="field"
                           placeholder="••••••••" style="padding-right:56px;">
                    <button type="button"
                            onclick="var i=document.getElementById('cur-pw');i.type=i.type==='password'?'text':'password';this.textContent=i.type==='password'?'Show':'Hide'"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#6b7280;font-size:0.75rem;font-weight:600;cursor:pointer;">
                        Show
                    </button>
                </div>
            </div>

            <div class="form-field">
                <label class="label">New password</label>
                <div style="position:relative;">
                    <input type="password" name="new_password" id="password" class="field"
                           placeholder="Min. 8 characters" style="padding-right:56px;">
                    <button type="button" id="toggle-password"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#6b7280;font-size:0.75rem;font-weight:600;cursor:pointer;font-family:inherit;">
                        Show
                    </button>
                </div>
                <div class="strength-bars" style="margin-top:7px;">
                    <div id="bar-1" class="s-bar"></div>
                    <div id="bar-2" class="s-bar"></div>
                    <div id="bar-3" class="s-bar"></div>
                    <div id="bar-4" class="s-bar"></div>
                </div>
                <p id="strength-label" style="font-size:0.72rem;color:#9ca3af;margin-top:4px;font-weight:500;">—</p>
                <ul class="req-list" style="margin-top:6px;">
                    <li id="rule-length">At least 8 characters</li>
                    <li id="rule-upper">One uppercase letter</li>
                    <li id="rule-lower">One lowercase letter</li>
                    <li id="rule-number">One number</li>
                    <li id="rule-special">One special character</li>
                </ul>
            </div>

            <div class="form-field">
                <label class="label">Confirm new password</label>
                <input type="password" name="confirm_password" id="confirm_password"
                       class="field" placeholder="••••••••">
                <p id="confirm-error" class="field-error"></p>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary" style="padding:11px 28px;">
                    Update password
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- Footer nav -->
    <div class="profile-footer">
        <a href="dashboard.php"
           style="padding:10px 20px;border:1px solid var(--border);border-radius:8px;color:var(--muted);font-size:0.875rem;font-weight:600;text-decoration:none;">
            Back to dashboard
        </a>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
<script src="/techhive/Project/js/main.js"></script>
