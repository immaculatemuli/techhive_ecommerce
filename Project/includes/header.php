<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Auto-login via Remember Me cookie
if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_token'])) {
    require_once dirname(__DIR__) . '/config.php';
    checkRememberMe();
}

$isLoggedIn = isset($_SESSION['user_id']);
$username   = $isLoggedIn ? htmlspecialchars($_SESSION['username']) : '';
$role       = $isLoggedIn ? $_SESSION['role'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'TechHive' ?></title>
    <link rel="stylesheet" href="/techhive/Project/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">

        <a href="/techhive/Project/index.php" class="brand">Tech<span>Hive</span></a>

        <div class="nav-links" id="nav-links">
            <a href="/techhive/Project/index.php"          class="nav-link">Home</a>
            <a href="/techhive/Project/products/index.php" class="nav-link">Products</a>
            <a href="/techhive/Project/contact.php"         class="nav-link">Contact</a>
            <?php if ($role !== 'admin'): ?>
            <a href="/techhive/Project/cart.php"           class="nav-link" style="display:flex;align-items:center;gap:5px;">
                Cart
                <span id="cart-count" style="display:none;background:#1e3a8a;color:#fff;font-size:0.65rem;font-weight:700;border-radius:99px;min-width:18px;height:18px;padding:0 5px;align-items:center;justify-content:center;line-height:1;">0</span>
            </a>
            <?php endif; ?>
            <?php if ($isLoggedIn && $role === 'admin'): ?>
                <a href="/techhive/Project/admin/index.php" class="nav-link">Admin</a>
            <?php endif; ?>
        </div>

        <div class="nav-auth">
            <?php if ($isLoggedIn): ?>
                <a href="/techhive/Project/profile.php" class="nav-link" title="My Profile">Profile</a>
                <a href="/techhive/Project/dashboard.php" class="nav-avatar" title="<?= $username ?>">
                    <?= strtoupper(mb_substr($_SESSION['username'], 0, 1)) ?>
                </a>
                <a href="/techhive/Project/logout.php" class="nav-link">Logout</a>
            <?php else: ?>
                <a href="/techhive/Project/login.php"    class="nav-link">Sign in</a>
                <a href="/techhive/Project/register.php" class="btn-nav">Get started</a>
            <?php endif; ?>
        </div>

    </div>
</nav>

<main>
