<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$username   = $isLoggedIn ? htmlspecialchars($_SESSION['username']) : '';
$role       = $isLoggedIn ? $_SESSION['role'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechHive</title>
    <link rel="stylesheet" href="/techhive/css/style.css">
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">

        <a href="/techhive/index.php" class="brand">Tech<span>Hive</span></a>

        <div class="nav-links" id="nav-links">
            <a href="/techhive/index.php"          class="nav-link">Home</a>
            <a href="/techhive/products/index.php" class="nav-link">Products</a>
            <a href="/techhive/cart.php"           class="nav-link" style="display:flex;align-items:center;gap:5px;">
                Cart
                <span id="cart-count" style="display:none;background:#111827;color:#fff;font-size:0.65rem;font-weight:700;border-radius:99px;min-width:18px;height:18px;padding:0 5px;align-items:center;justify-content:center;line-height:1;">0</span>
            </a>

            <?php if ($isLoggedIn): ?>
                <?php if ($role === 'admin'): ?>
                    <a href="/techhive/admin/index.php" class="nav-link">Admin</a>
                <?php endif; ?>
                <a href="/techhive/dashboard.php" class="nav-link" style="color:#111827;font-weight:600;"><?= $username ?></a>
                <a href="/techhive/logout.php" class="nav-link">Logout</a>
            <?php else: ?>
                <a href="/techhive/login.php"    class="nav-link">Sign in</a>
                <a href="/techhive/register.php" class="btn-nav">Get started</a>
            <?php endif; ?>
        </div>

        <button id="menu-btn" onclick="document.getElementById('nav-links').classList.toggle('open')"
            style="background:none;border:none;cursor:pointer;color:#6b7280;padding:4px;display:flex;align-items:center;justify-content:center;">
            <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

    </div>
</nav>

<main>
