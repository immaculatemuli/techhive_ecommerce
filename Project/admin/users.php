<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$db = getDB();

// Toggle role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['role'])) {
    $allowedRoles = ['admin', 'customer'];
    $newRole = in_array($_POST['role'], $allowedRoles) ? $_POST['role'] : 'customer';
    $uid = (int)$_POST['user_id'];

    // Prevent demoting yourself
    if ($uid !== (int)$_SESSION['user_id']) {
        $db->prepare("UPDATE users SET role = ? WHERE id = ?")->execute([$newRole, $uid]);
    }
    header('Location: users.php?updated=1');
    exit;
}

$updated = $_GET['updated'] ?? false;

$users = $db->query("
    SELECT u.id, u.username, u.email, u.role, u.created_at,
           COUNT(o.id) AS order_count
    FROM users u
    LEFT JOIN orders o ON o.user_id = u.id
    GROUP BY u.id, u.username, u.email, u.role, u.created_at
    ORDER BY u.created_at DESC
")->fetchAll();
?>
<?php include 'admin_header.php'; ?>

<style>
.admin-wrap { max-width:1200px;margin:0 auto;padding:40px 24px; }
</style>

<div class="admin-wrap">

    <div style="margin-bottom:28px;">
        <p style="font-size:0.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;font-weight:600;margin-bottom:4px;">Admin Panel</p>
        <h1 style="font-size:1.8rem;font-weight:800;color:var(--text);">Users</h1>
    </div>

    <?php if ($updated): ?>
        <div class="alert-success" style="margin-bottom:20px;">User role updated.</div>
    <?php endif; ?>

    <div class="card" style="overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;font-size:0.875rem;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);background:var(--subtle);">
                    <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">ID</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">User</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Role</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Orders</th>
                    <th style="text-align:left;padding:14px 16px;color:var(--muted);font-weight:600;">Joined</th>
                    <th style="text-align:right;padding:14px 16px;color:var(--muted);font-weight:600;">Change Role</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr style="border-bottom:1px solid var(--border);transition:background 0.15s;"
                    onmouseover="this.style.background='var(--subtle)'"
                    onmouseout="this.style.background='transparent'">
                    <td style="padding:14px 16px;color:var(--muted);">#<?= $u['id'] ?></td>
                    <td style="padding:14px 16px;">
                        <p style="font-weight:600;color:var(--text);margin-bottom:1px;">
                            <?= htmlspecialchars($u['username']) ?>
                            <?php if ($u['id'] == $_SESSION['user_id']): ?>
                                <span style="font-size:0.68rem;color:var(--muted);font-weight:400;margin-left:5px;">(you)</span>
                            <?php endif; ?>
                        </p>
                        <p style="font-size:0.75rem;color:var(--muted);"><?= htmlspecialchars($u['email']) ?></p>
                    </td>
                    <td style="padding:14px 16px;">
                        <?php if ($u['role'] === 'admin'): ?>
                            <span style="padding:3px 10px;background:#eff6ff;border:1px solid #93c5fd;border-radius:99px;font-size:0.72rem;color:#1d4ed8;font-weight:600;">Admin</span>
                        <?php else: ?>
                            <span style="padding:3px 10px;background:var(--subtle);border:1px solid var(--border);border-radius:99px;font-size:0.72rem;color:var(--muted);font-weight:600;">Customer</span>
                        <?php endif; ?>
                    </td>
                    <td style="padding:14px 16px;color:var(--muted);"><?= $u['order_count'] ?></td>
                    <td style="padding:14px 16px;color:var(--muted);font-size:0.8rem;"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td style="padding:14px 16px;text-align:right;">
                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                        <form method="POST" style="display:flex;gap:6px;justify-content:flex-end;align-items:center;">
                            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                            <select name="role"
                                style="padding:5px 8px;border:1px solid var(--border);border-radius:6px;font-size:0.8rem;color:var(--text);background:#fff;cursor:pointer;">
                                <option value="customer" <?= $u['role'] === 'customer' ? 'selected' : '' ?>>Customer</option>
                                <option value="admin"    <?= $u['role'] === 'admin'    ? 'selected' : '' ?>>Admin</option>
                            </select>
                            <button type="submit"
                                style="padding:5px 12px;background:#1e3a8a;color:#fff;border:none;border-radius:6px;font-size:0.8rem;font-weight:600;cursor:pointer;">
                                Save
                            </button>
                        </form>
                        <?php else: ?>
                            <span style="font-size:0.78rem;color:var(--muted);">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p style="margin-top:12px;color:var(--muted);font-size:0.82rem;"><?= count($users) ?> user(s) total</p>

</div>

<?php include 'admin_footer.php'; ?>
