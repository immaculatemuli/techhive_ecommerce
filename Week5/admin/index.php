<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$db       = getDB();
$products = $db->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();

$deleted = $_GET['deleted'] ?? false;
$added   = $_GET['added']   ?? false;
$updated = $_GET['updated'] ?? false;
?>
<?php include '../includes/header.php'; ?>

<div style="max-width:1160px;margin:0 auto;padding:48px 24px;">

    <!-- Header row -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:32px;">
        <div>
            <p style="font-size:0.78rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;font-weight:600;margin-bottom:4px;">Admin Panel</p>
            <h1 style="font-size:1.8rem;font-weight:800;color:var(--text);">Products</h1>
        </div>
        <a href="add_product.php" class="btn-primary" style="text-decoration:none;">+ Add Product</a>
    </div>

    <!-- Flash messages -->
    <?php if ($deleted): ?>
        <div class="alert-error" style="margin-bottom:20px;">Product deleted successfully.</div>
    <?php elseif ($added): ?>
        <div class="alert-success" style="margin-bottom:20px;">Product added successfully.</div>
    <?php elseif ($updated): ?>
        <div class="alert-success" style="margin-bottom:20px;">Product updated successfully.</div>
    <?php endif; ?>

    <!-- Products table -->
    <div class="card" style="overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);background:var(--subtle);">
                    <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">ID</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Name</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Category</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Price</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Stock</th>
                    <th style="text-align:right;padding:14px 16px;color:var(--muted);font-weight:600;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;padding:48px;color:var(--muted);">
                            No products yet. <a href="add_product.php" style="color:var(--accent);font-weight:600;">Add one →</a>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                    <tr style="border-bottom:1px solid var(--border);transition:background 0.15s;"
                        onmouseover="this.style.background='var(--subtle)'"
                        onmouseout="this.style.background='transparent'">
                        <td style="padding:14px 16px;color:var(--muted);">#<?= $p['id'] ?></td>
                        <td style="padding:14px 16px;color:var(--text);font-weight:600;"><?= htmlspecialchars($p['name']) ?></td>
                        <td style="padding:14px 16px;">
                            <span style="padding:3px 10px;background:var(--subtle);border:1px solid var(--border);border-radius:99px;font-size:0.75rem;color:var(--muted);font-weight:600;">
                                <?= htmlspecialchars($p['category']) ?>
                            </span>
                        </td>
                        <td style="padding:14px 16px;color:var(--text);font-weight:700;"><?= ksh($p['price']) ?></td>
                        <td style="padding:14px 16px;">
                            <span style="font-weight:600;font-size:0.875rem;
                                <?= $p['stock'] > 0 ? 'color:#16a34a;' : 'color:#dc2626;' ?>">
                                <?= $p['stock'] ?>
                            </span>
                        </td>
                        <td style="padding:14px 16px;text-align:right;">
                            <div style="display:flex;gap:8px;justify-content:flex-end;">
                                <a href="edit_product.php?id=<?= $p['id'] ?>"
                                    style="padding:5px 14px;background:#fff;border:1px solid var(--border);border-radius:6px;color:var(--text);font-size:0.8rem;text-decoration:none;font-weight:600;transition:border-color 0.15s;"
                                    onmouseover="this.style.borderColor='var(--accent)';this.style.color='var(--accent)'"
                                    onmouseout="this.style.borderColor='var(--border)';this.style.color='var(--text)'">
                                    Edit
                                </a>
                                <a href="delete_product.php?id=<?= $p['id'] ?>"
                                    onclick="return confirm('Delete <?= htmlspecialchars(addslashes($p['name'])) ?>?')"
                                    style="padding:5px 14px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;color:#dc2626;font-size:0.8rem;text-decoration:none;font-weight:600;transition:background 0.15s;"
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

    <p style="margin-top:16px;color:var(--muted);font-size:0.82rem;"><?= count($products) ?> product(s) total</p>

</div>

<?php include '../includes/footer.php'; ?>
