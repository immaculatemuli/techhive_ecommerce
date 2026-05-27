<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db     = getDB();
$userId = (int)$_SESSION['user_id'];

$orders = $db->prepare("
    SELECT o.id, o.total, o.status, o.created_at, COUNT(oi.id) AS item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.id, o.total, o.status, o.created_at
    ORDER BY o.created_at DESC
");
$orders->execute([$userId]);
$orders = $orders->fetchAll();

$statusColors = [
    'pending'    => ['bg'=>'#fefce8','border'=>'#fde047','text'=>'#854d0e'],
    'processing' => ['bg'=>'#eff6ff','border'=>'#93c5fd','text'=>'#1d4ed8'],
    'shipped'    => ['bg'=>'#f0fdf4','border'=>'#86efac','text'=>'#15803d'],
    'delivered'  => ['bg'=>'#f0fdf4','border'=>'#4ade80','text'=>'#166534'],
    'cancelled'  => ['bg'=>'#fef2f2','border'=>'#fca5a5','text'=>'#dc2626'],
];
?>
<?php include 'includes/header.php'; ?>

<div style="max-width:860px;margin:0 auto;padding:48px 24px;">

    <div style="margin-bottom:32px;">
        <p style="font-size:0.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;font-weight:600;margin-bottom:4px;">My Account</p>
        <h1 style="font-size:1.8rem;font-weight:800;color:var(--text);">
            Hi, <?= htmlspecialchars($_SESSION['username']) ?>
        </h1>
    </div>

    <h2 style="font-size:1.05rem;font-weight:700;color:var(--text);margin-bottom:16px;">My Orders</h2>

    <?php if (empty($orders)): ?>
        <div class="card" style="text-align:center;padding:60px 24px;">
            <p style="font-size:2rem;margin-bottom:12px;">🛍️</p>
            <h3 style="font-size:1rem;font-weight:700;color:var(--text);margin-bottom:6px;">No orders yet</h3>
            <p style="color:var(--muted);font-size:0.875rem;margin-bottom:24px;">Browse our products and place your first order.</p>
            <a href="/techhive/products/index.php" class="btn-primary">Shop Now</a>
        </div>
    <?php else: ?>
        <div class="card" style="overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border);background:var(--subtle);">
                        <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Order</th>
                        <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Items</th>
                        <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Total</th>
                        <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Status</th>
                        <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o):
                        $sc = $statusColors[$o['status']] ?? $statusColors['pending'];
                    ?>
                    <tr style="border-bottom:1px solid var(--border);">
                        <td style="padding:14px 16px;color:var(--muted);font-weight:600;">#<?= $o['id'] ?></td>
                        <td style="padding:14px 16px;color:var(--muted);"><?= $o['item_count'] ?> item<?= $o['item_count'] != 1 ? 's' : '' ?></td>
                        <td style="padding:14px 16px;font-weight:700;color:var(--text);"><?= ksh($o['total']) ?></td>
                        <td style="padding:14px 16px;">
                            <span style="padding:3px 10px;background:<?= $sc['bg'] ?>;border:1px solid <?= $sc['border'] ?>;border-radius:99px;font-size:0.72rem;color:<?= $sc['text'] ?>;font-weight:600;text-transform:capitalize;">
                                <?= $o['status'] ?>
                            </span>
                        </td>
                        <td style="padding:14px 16px;color:var(--muted);font-size:0.8rem;"><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <div style="margin-top:32px;display:flex;gap:12px;flex-wrap:wrap;">
        <a href="/techhive/products/index.php" class="btn-primary" style="text-decoration:none;">Shop Products</a>
        <a href="/techhive/logout.php"
            style="padding:10px 20px;border:1px solid var(--border);border-radius:8px;color:var(--muted);font-size:0.875rem;font-weight:600;text-decoration:none;">
            Sign out
        </a>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
