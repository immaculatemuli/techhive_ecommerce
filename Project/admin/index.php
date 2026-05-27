<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$db = getDB();

$productCount  = (int)$db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$orderCount    = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingCount  = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$revenue       = (float)$db->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status != 'cancelled'")->fetchColumn();
$customerCount = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'customer'")->fetchColumn();

$recentOrders = $db->query("
    SELECT o.id, o.total, o.status, o.created_at, u.username,
           COUNT(oi.id) AS item_count
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id, o.total, o.status, o.created_at, u.username
    ORDER BY o.created_at DESC
    LIMIT 8
")->fetchAll();

$lowStock = $db->query("
    SELECT id, name, stock, category
    FROM products
    WHERE stock <= 5
    ORDER BY stock ASC
    LIMIT 6
")->fetchAll();

$products = $db->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();

$deleted = $_GET['deleted'] ?? false;
$added   = $_GET['added']   ?? false;
$updated = $_GET['updated'] ?? false;

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
    .stats-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px; }
    .stat-card  { background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:20px;display:flex;align-items:flex-start;gap:14px; }
    .stat-icon  { width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .stat-label { font-size:0.72rem;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px; }
    .stat-value { font-size:1.55rem;font-weight:800;color:#111827;letter-spacing:-0.5px;line-height:1; }
    .stat-sub   { font-size:0.72rem;color:#9ca3af;margin-top:4px; }
    .dash-grid  { display:grid;grid-template-columns:1fr 320px;gap:20px;margin-bottom:28px; }
    .section-title { font-size:0.9rem;font-weight:700;color:#111827;margin-bottom:14px; }
    .card       { background:#fff;border:1px solid #e5e7eb;border-radius:12px; }
    @media(max-width:1100px){ .stats-grid{grid-template-columns:repeat(2,1fr);} .dash-grid{grid-template-columns:1fr;} }
    @media(max-width:540px) { .stats-grid{grid-template-columns:1fr;} }
</style>

<?php if ($deleted): ?>
    <div class="alert-error"   style="margin-bottom:20px;">Product deleted.</div>
<?php elseif ($added): ?>
    <div class="alert-success" style="margin-bottom:20px;">Product added.</div>
<?php elseif ($updated): ?>
    <div class="alert-success" style="margin-bottom:20px;">Product updated.</div>
<?php endif; ?>

<!-- Page heading -->
<div style="margin-bottom:24px;">
    <h1 style="font-size:1.5rem;font-weight:800;color:#111827;">Overview</h1>
    <p style="font-size:0.82rem;color:#6b7280;margin-top:3px;"><?= date('l, d F Y') ?></p>
</div>

<!-- Stats row -->
<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-icon" style="background:#eff6ff;">
            <svg width="18" height="18" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7L12 3 4 7m16 0v10l-8 4m0 0L4 17V7m8 10V7"/>
            </svg>
        </div>
        <div>
            <p class="stat-label">Products</p>
            <p class="stat-value"><?= $productCount ?></p>
            <p class="stat-sub">in inventory</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background:#faf5ff;">
            <svg width="18" height="18" fill="none" stroke="#a855f7" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <div>
            <p class="stat-label">Orders</p>
            <p class="stat-value"><?= $orderCount ?></p>
            <p class="stat-sub"><?= $pendingCount ?> pending</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background:#f0fdf4;">
            <svg width="18" height="18" fill="none" stroke="#22c55e" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="stat-label">Revenue</p>
            <p class="stat-value" style="font-size:1.1rem;"><?= ksh($revenue) ?></p>
            <p class="stat-sub">excl. cancelled</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon" style="background:#fff7ed;">
            <svg width="18" height="18" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div>
            <p class="stat-label">Customers</p>
            <p class="stat-value"><?= $customerCount ?></p>
            <p class="stat-sub">registered</p>
        </div>
    </div>

</div>

<!-- Two-column section -->
<div class="dash-grid">

    <!-- Recent orders -->
    <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <p class="section-title" style="margin-bottom:0;">Recent Orders</p>
            <a href="orders.php" style="font-size:0.78rem;color:#6b7280;text-decoration:none;font-weight:600;">View all →</a>
        </div>

        <div class="card" style="overflow:hidden;">
            <?php if (empty($recentOrders)): ?>
                <p style="text-align:center;padding:40px;color:#9ca3af;font-size:0.875rem;">No orders yet.</p>
            <?php else: ?>
                <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
                    <thead>
                        <tr style="border-bottom:1px solid #f3f4f6;">
                            <th style="text-align:left;padding:12px 16px;color:#9ca3af;font-weight:600;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Order</th>
                            <th style="text-align:left;padding:12px 16px;color:#9ca3af;font-weight:600;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Customer</th>
                            <th style="text-align:left;padding:12px 16px;color:#9ca3af;font-weight:600;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Total</th>
                            <th style="text-align:left;padding:12px 16px;color:#9ca3af;font-weight:600;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Status</th>
                            <th style="text-align:left;padding:12px 16px;color:#9ca3af;font-weight:600;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentOrders as $o):
                            $sc = $statusColors[$o['status']] ?? $statusColors['pending'];
                        ?>
                        <tr style="border-bottom:1px solid #f9fafb;transition:background 0.12s;"
                            onmouseover="this.style.background='#f9fafb'"
                            onmouseout="this.style.background='transparent'">
                            <td style="padding:12px 16px;color:#6b7280;font-weight:600;">#<?= $o['id'] ?></td>
                            <td style="padding:12px 16px;font-weight:600;color:#111827;"><?= htmlspecialchars($o['username']) ?></td>
                            <td style="padding:12px 16px;font-weight:700;color:#111827;"><?= ksh($o['total']) ?></td>
                            <td style="padding:12px 16px;">
                                <span style="padding:2px 9px;background:<?= $sc['bg'] ?>;border:1px solid <?= $sc['border'] ?>;border-radius:99px;font-size:0.7rem;color:<?= $sc['text'] ?>;font-weight:600;text-transform:capitalize;">
                                    <?= $o['status'] ?>
                                </span>
                            </td>
                            <td style="padding:12px 16px;color:#9ca3af;font-size:0.76rem;"><?= date('d M', strtotime($o['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right column -->
    <div style="display:flex;flex-direction:column;gap:16px;">

        <!-- Low stock -->
        <div>
            <p class="section-title">Low Stock Alert</p>
            <div class="card" style="overflow:hidden;">
                <?php if (empty($lowStock)): ?>
                    <p style="padding:20px 16px;color:#9ca3af;font-size:0.82rem;">All products are well stocked.</p>
                <?php else: ?>
                    <?php foreach ($lowStock as $p): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:11px 16px;border-bottom:1px solid #f3f4f6;">
                        <div>
                            <p style="font-size:0.82rem;font-weight:600;color:#111827;"><?= htmlspecialchars($p['name']) ?></p>
                            <p style="font-size:0.72rem;color:#9ca3af;"><?= htmlspecialchars($p['category']) ?></p>
                        </div>
                        <span style="font-size:0.78rem;font-weight:700;
                            <?= $p['stock'] === 0 ? 'color:#dc2626;' : 'color:#f97316;' ?>">
                            <?= $p['stock'] === 0 ? 'Out of stock' : $p['stock'] . ' left' ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                    <div style="padding:10px 16px;">
                        <a href="#products" style="font-size:0.78rem;color:#6b7280;text-decoration:none;font-weight:600;">Manage products →</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick actions -->
        <div>
            <p class="section-title">Quick Actions</p>
            <div class="card" style="padding:16px;display:flex;flex-direction:column;gap:8px;">
                <a href="add_product.php"
                   style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;text-decoration:none;color:#111827;font-size:0.82rem;font-weight:600;transition:border-color 0.15s,color 0.15s;"
                   onmouseover="this.style.borderColor='#1e3a8a';this.style.color='#1e3a8a'"
                   onmouseout="this.style.borderColor='#e5e7eb';this.style.color='#111827'">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add new product
                </a>
                <a href="orders.php?status=pending"
                   style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;text-decoration:none;color:#111827;font-size:0.82rem;font-weight:600;transition:border-color 0.15s,color 0.15s;"
                   onmouseover="this.style.borderColor='#1e3a8a';this.style.color='#1e3a8a'"
                   onmouseout="this.style.borderColor='#e5e7eb';this.style.color='#111827'">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Review pending orders
                    <?php if ($pendingCount > 0): ?>
                        <span style="margin-left:auto;background:#fef3c7;color:#92400e;font-size:0.68rem;font-weight:700;padding:1px 7px;border-radius:99px;"><?= $pendingCount ?></span>
                    <?php endif; ?>
                </a>
                <a href="users.php"
                   style="display:flex;align-items:center;gap:10px;padding:10px 12px;background:#f9fafb;border:1px solid #e5e7eb;border-radius:8px;text-decoration:none;color:#111827;font-size:0.82rem;font-weight:600;transition:border-color 0.15s,color 0.15s;"
                   onmouseover="this.style.borderColor='#1e3a8a';this.style.color='#1e3a8a'"
                   onmouseout="this.style.borderColor='#e5e7eb';this.style.color='#111827'">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Manage users
                </a>
            </div>
        </div>

    </div>
</div>

<!-- Products table -->
<div id="products">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
        <p class="section-title" style="margin-bottom:0;">All Products</p>
        <a href="add_product.php" class="btn-primary" style="text-decoration:none;font-size:0.8rem;padding:8px 16px;">+ Add Product</a>
    </div>

    <div class="card" style="overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:0.82rem;">
            <thead>
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <th style="text-align:left;padding:12px 16px;color:#9ca3af;font-weight:600;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">ID</th>
                    <th style="text-align:left;padding:12px 16px;color:#9ca3af;font-weight:600;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Name</th>
                    <th style="text-align:left;padding:12px 16px;color:#9ca3af;font-weight:600;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Category</th>
                    <th style="text-align:left;padding:12px 16px;color:#9ca3af;font-weight:600;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Price</th>
                    <th style="text-align:left;padding:12px 16px;color:#9ca3af;font-weight:600;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Stock</th>
                    <th style="text-align:right;padding:12px 16px;color:#9ca3af;font-weight:600;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;padding:48px;color:#9ca3af;">
                            No products yet. <a href="add_product.php" style="color:#1e3a8a;font-weight:600;">Add one →</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                    <tr style="border-bottom:1px solid #f9fafb;transition:background 0.12s;"
                        onmouseover="this.style.background='#f9fafb'"
                        onmouseout="this.style.background='transparent'">
                        <td style="padding:13px 16px;color:#9ca3af;">#<?= $p['id'] ?></td>
                        <td style="padding:13px 16px;color:#111827;font-weight:600;"><?= htmlspecialchars($p['name']) ?></td>
                        <td style="padding:13px 16px;">
                            <span style="padding:2px 9px;background:#f3f4f6;border-radius:99px;font-size:0.72rem;color:#6b7280;font-weight:600;">
                                <?= htmlspecialchars($p['category']) ?>
                            </span>
                        </td>
                        <td style="padding:13px 16px;color:#111827;font-weight:700;"><?= ksh($p['price']) ?></td>
                        <td style="padding:13px 16px;">
                            <?php $stock = (int)$p['stock']; ?>
                            <?php if ($stock === 0): ?>
                                <span style="background:#fee2e2;color:#dc2626;font-size:0.7rem;font-weight:700;padding:3px 9px;border-radius:99px;">
                                    Out of Stock
                                </span>
                            <?php elseif ($stock <= 5): ?>
                                <span style="background:#fef3c7;color:#92400e;font-size:0.7rem;font-weight:700;padding:3px 9px;border-radius:99px;">
                                    <?= $stock ?> left
                                </span>
                            <?php else: ?>
                                <span style="background:#dcfce7;color:#15803d;font-size:0.7rem;font-weight:700;padding:3px 9px;border-radius:99px;">
                                    <?= $stock ?> in stock
                                </span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:13px 16px;text-align:right;">
                            <div style="display:flex;gap:6px;justify-content:flex-end;">
                                <a href="edit_product.php?id=<?= $p['id'] ?>"
                                    style="padding:5px 12px;background:#fff;border:1px solid #e5e7eb;border-radius:6px;color:#374151;font-size:0.78rem;text-decoration:none;font-weight:600;transition:all 0.15s;"
                                    onmouseover="this.style.borderColor='#1e3a8a';this.style.color='#1e3a8a'"
                                    onmouseout="this.style.borderColor='#e5e7eb';this.style.color='#374151'">
                                    Edit
                                </a>
                                <a href="delete_product.php?id=<?= $p['id'] ?>"
                                    onclick="return confirm('Delete <?= htmlspecialchars(addslashes($p['name'])) ?>?')"
                                    style="padding:5px 12px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;color:#dc2626;font-size:0.78rem;text-decoration:none;font-weight:600;transition:background 0.15s;"
                                    onmouseover="this.style.background='#fee2e2'"
                                    onmouseout="this.style.background='#fef2f2'">
                                    Delete
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <p style="margin-top:10px;color:#9ca3af;font-size:0.78rem;"><?= count($products) ?> product(s) total</p>
</div>

<?php include 'admin_footer.php'; ?>
