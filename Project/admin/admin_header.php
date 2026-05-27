<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$cur = basename($_SERVER['PHP_SELF'], '.php');
$pageLabels = [
    'index'        => 'Dashboard',
    'orders'       => 'Orders',
    'users'        => 'Users',
    'add_product'  => 'Add Product',
    'edit_product' => 'Edit Product',
];
$pageLabel = $pageLabels[$cur] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageLabel ?> | TechHive Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="/techhive/css/style.css">
    <style>
        .a-sidebar, .a-main, .a-topbar, .a-body, .a-sidebar * {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif !important;
        }
    </style>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            display: flex;
            min-height: 100vh;
            background: #f1f5f9;
            font-family: inherit;
        }

        /* ── Sidebar ─────────────────────────────────────────── */
        .a-sidebar {
            width: 230px;
            min-height: 100vh;
            background: #0f172a;
            position: fixed;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            z-index: 200;
        }

        .a-brand {
            padding: 22px 20px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .a-brand-name {
            font-size: 1.1rem;
            font-weight: 900;
            color: #fff;
            text-decoration: none;
            letter-spacing: -0.4px;
            display: block;
            margin-bottom: 4px;
        }
        .a-brand-name span { color: #818cf8; }
        .a-brand-badge {
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.3);
        }

        .a-nav { padding: 14px 10px; flex: 1; }

        .a-nav-section {
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.09em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.22);
            padding: 14px 10px 6px;
        }

        .a-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 12px;
            border-radius: 8px;
            text-decoration: none;
            color: rgba(255,255,255,0.5);
            font-size: 0.855rem;
            font-weight: 500;
            margin-bottom: 1px;
            transition: background 0.15s, color 0.15s;
        }
        .a-link:hover  { background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.9); }
        .a-link.active { background: rgba(129,140,248,0.18); color: #c7d2fe; }
        .a-link.active svg { color: #818cf8; }
        .a-link svg { flex-shrink: 0; opacity: 0.7; }
        .a-link.active svg { opacity: 1; }

        .a-badge {
            margin-left: auto;
            background: #dc2626;
            color: #fff;
            font-size: 0.6rem;
            font-weight: 700;
            border-radius: 99px;
            padding: 1px 6px;
            min-width: 18px;
            text-align: center;
        }

        .a-sidebar-bottom {
            padding: 14px 10px;
            border-top: 1px solid rgba(255,255,255,0.06);
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        /* ── Main content ────────────────────────────────────── */
        .a-main { margin-left: 230px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

        .a-topbar {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 0 32px;
            height: 58px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .a-topbar-title {
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
        }
        .a-topbar-meta {
            display: flex;
            align-items: center;
            gap: 16px;
            font-size: 0.8rem;
            color: #6b7280;
        }
        .a-avatar {
            width: 30px; height: 30px;
            border-radius: 50%;
            background: #111827;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.03em;
        }

        .a-body { padding: 32px; flex: 1; }

        @media (max-width: 860px) {
            .a-sidebar { display: none; }
            .a-main    { margin-left: 0; }
            .a-body    { padding: 20px; }
        }
    </style>
</head>
<body>

<!-- ── Sidebar ─────────────────────────────────────────────── -->
<aside class="a-sidebar">

    <div class="a-brand">
        <a href="/techhive/index.php" class="a-brand-name">Tech<span>Hive</span></a>
        <span class="a-brand-badge">Admin Panel</span>
    </div>

    <nav class="a-nav">
        <span class="a-nav-section">Main</span>

        <a href="/techhive/admin/index.php"
           class="a-link <?= $cur === 'index' ? 'active' : '' ?>">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/>
                <rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>
            </svg>
            Dashboard
        </a>

        <a href="/techhive/admin/orders.php"
           class="a-link <?= $cur === 'orders' ? 'active' : '' ?>">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            Orders
            <?php
            // Show pending count badge
            if (function_exists('getDB')) {
                try {
                    $db = getDB();
                    $pendingCount = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
                    if ($pendingCount > 0) echo "<span class='a-badge'>$pendingCount</span>";
                } catch (Exception $e) {}
            }
            ?>
        </a>

        <a href="/techhive/admin/users.php"
           class="a-link <?= $cur === 'users' ? 'active' : '' ?>">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Users
        </a>

        <span class="a-nav-section">Catalog</span>

        <a href="/techhive/admin/add_product.php"
           class="a-link <?= $cur === 'add_product' ? 'active' : '' ?>">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Add Product
        </a>

        <a href="/techhive/admin/index.php#products"
           class="a-link <?= $cur === 'index' ? '' : '' ?>">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7L12 3 4 7m16 0v10l-8 4m0 0L4 17V7m8 10V7"/>
            </svg>
            Products
        </a>

    </nav>

    <div class="a-sidebar-bottom">
        <a href="/techhive/index.php" class="a-link">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            View Store
        </a>
        <a href="/techhive/logout.php" class="a-link">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            Sign Out
        </a>
    </div>

</aside>

<!-- ── Main ───────────────────────────────────────────────── -->
<div class="a-main">

    <div class="a-topbar">
        <div>
            <span style="font-size:0.75rem;color:#9ca3af;">Admin /</span>
            <span class="a-topbar-title" style="margin-left:6px;"><?= $pageLabel ?></span>
        </div>
        <div class="a-topbar-meta">
            <span><?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
            <div class="a-avatar"><?= strtoupper(substr($_SESSION['username'] ?? 'A', 0, 2)) ?></div>
        </div>
    </div>

    <div class="a-body">
