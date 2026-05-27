<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$db = getDB();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $allowed = ['pending','processing','shipped','delivered','cancelled'];
    $status  = in_array($_POST['status'], $allowed) ? $_POST['status'] : 'pending';
    $orderId = (int)$_POST['order_id'];
    $db->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $orderId]);
    header('Location: orders.php?updated=' . $orderId);
    exit;
}

$validStatuses = ['pending','processing','shipped','delivered','cancelled'];
$filter  = in_array($_GET['status'] ?? '', $validStatuses) ? $_GET['status'] : 'all';
$updated = $_GET['updated'] ?? false;

$sql = "
    SELECT o.id, o.total, o.status, o.created_at,
           u.username, u.email,
           COUNT(oi.id) AS item_count
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id, o.total, o.status, o.created_at, u.username, u.email
    ORDER BY o.created_at DESC
";

if ($filter !== 'all') {
    $sql = "
        SELECT o.id, o.total, o.status, o.created_at,
               u.username, u.email,
               COUNT(oi.id) AS item_count
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.status = ?
        GROUP BY o.id, o.total, o.status, o.created_at, u.username, u.email
        ORDER BY o.created_at DESC
    ";
    $stmt = $db->prepare($sql);
    $stmt->execute([$filter]);
    $orders = $stmt->fetchAll();
} else {
    $orders = $db->query($sql)->fetchAll();
}

$counts = $db->query("
    SELECT status, COUNT(*) AS n FROM orders GROUP BY status
")->fetchAll(PDO::FETCH_KEY_PAIR);

$totalOrders = array_sum($counts);

$statusColors = [
    'pending'    => ['bg'=>'#fefce8','border'=>'#fde047','text'=>'#854d0e'],
    'processing' => ['bg'=>'#eff6ff','border'=>'#93c5fd','text'=>'#1d4ed8'],
    'shipped'    => ['bg'=>'#f0fdf4','border'=>'#86efac','text'=>'#15803d'],
    'delivered'  => ['bg'=>'#f0fdf4','border'=>'#4ade80','text'=>'#166534'],
    'cancelled'  => ['bg'=>'#fef2f2','border'=>'#fca5a5','text'=>'#dc2626'],
];
?>
<?php include 'admin_header.php'; ?>

<style>
.admin-wrap  { max-width:1200px;margin:0 auto;padding:40px 24px; }
.filter-tabs { display:flex;gap:6px;flex-wrap:wrap;margin-bottom:20px; }
.filter-tab  { padding:6px 14px;border-radius:8px;font-size:0.8rem;font-weight:600;text-decoration:none;color:var(--muted);border:1px solid var(--border);background:#fff;transition:all 0.15s; }
.filter-tab:hover  { border-color:#1e3a8a;color:#1e3a8a; }
.filter-tab.active { background:#1e3a8a;color:#fff;border-color:#1e3a8a; }
</style>

<div class="admin-wrap">

    <div style="margin-bottom:28px;">
        <p style="font-size:0.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;font-weight:600;margin-bottom:4px;">Admin Panel</p>
        <h1 style="font-size:1.8rem;font-weight:800;color:var(--text);">Orders</h1>
    </div>

    <?php if ($updated): ?>
        <div class="alert-success" style="margin-bottom:20px;">Order #<?= (int)$updated ?> status updated.</div>
    <?php endif; ?>

    <!-- Filter tabs -->
    <div class="filter-tabs">
        <a href="orders.php" class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>">All (<?= $totalOrders ?>)</a>
        <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
            <a href="orders.php?status=<?= $s ?>"
               class="filter-tab <?= $filter === $s ? 'active' : '' ?>">
                <?= ucfirst($s) ?> (<?= $counts[$s] ?? 0 ?>)
            </a>
        <?php endforeach; ?>
    </div>

    <div class="card" style="overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);background:var(--subtle);">
                    <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Order</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Customer</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Items</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Total</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Status</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Date</th>
                    <th style="text-align:right;padding:14px 16px;color:var(--muted);font-weight:600;">Update</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($orders)): ?>
                    <tr>
                        <td colspan="7" style="text-align:center;padding:60px;color:var(--muted);">
                            No orders <?= $filter !== 'all' ? "with status <strong>$filter</strong>" : 'yet' ?>.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($orders as $o):
                        $sc = $statusColors[$o['status']] ?? $statusColors['pending'];
                    ?>
                    <tr style="border-bottom:1px solid var(--border);transition:background 0.15s;"
                        onmouseover="this.style.background='var(--subtle)'"
                        onmouseout="this.style.background='transparent'">
                        <td style="padding:14px 16px;color:var(--muted);font-weight:600;">#<?= $o['id'] ?></td>
                        <td style="padding:14px 16px;">
                            <p style="font-weight:600;color:var(--text);margin-bottom:1px;"><?= htmlspecialchars($o['username']) ?></p>
                            <p style="font-size:0.75rem;color:var(--muted);"><?= htmlspecialchars($o['email']) ?></p>
                        </td>
                        <td style="padding:14px 16px;color:var(--muted);"><?= $o['item_count'] ?></td>
                        <td style="padding:14px 16px;font-weight:700;color:var(--text);"><?= ksh($o['total']) ?></td>
                        <td style="padding:14px 16px;">
                            <span style="padding:3px 10px;background:<?= $sc['bg'] ?>;border:1px solid <?= $sc['border'] ?>;border-radius:99px;font-size:0.72rem;color:<?= $sc['text'] ?>;font-weight:600;text-transform:capitalize;">
                                <?= $o['status'] ?>
                            </span>
                        </td>
                        <td style="padding:14px 16px;color:var(--muted);font-size:0.8rem;"><?= date('d M Y, H:i', strtotime($o['created_at'])) ?></td>
                        <td style="padding:14px 16px;text-align:right;">
                            <form method="POST" style="display:flex;gap:6px;justify-content:flex-end;align-items:center;">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                <select name="status"
                                    style="padding:5px 8px;border:1px solid var(--border);border-radius:6px;font-size:0.8rem;color:var(--text);background:#fff;cursor:pointer;">
                                    <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                                        <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit"
                                    style="padding:5px 12px;background:#1e3a8a;color:#fff;border:none;border-radius:6px;font-size:0.8rem;font-weight:600;cursor:pointer;white-space:nowrap;">
                                    Save
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <p style="margin-top:12px;color:var(--muted);font-size:0.82rem;"><?= count($orders) ?> order(s) shown</p>

</div>

<?php include 'admin_footer.php'; ?>
