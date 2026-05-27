<?php
require_once 'config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$db = getDB();

// ── Cart actions (add / remove / update / clear) ──────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action']     ?? '';
    $product_id = (int)($_POST['product_id'] ?? 0);

    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    if ($action === 'add' && $product_id > 0) {
        $_SESSION['cart'][$product_id] = ($_SESSION['cart'][$product_id] ?? 0) + 1;
        // Redirect back to referrer (products page) or cart
        $back = $_SERVER['HTTP_REFERER'] ?? '/techhive/products/index.php';
        header('Location: ' . $back);
        exit;
    }

    if ($action === 'remove' && $product_id > 0) {
        unset($_SESSION['cart'][$product_id]);
    }

    if ($action === 'update' && $product_id > 0) {
        $qty = (int)($_POST['qty'] ?? 1);
        if ($qty <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id] = $qty;
        }
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
    }

    header('Location: cart.php');
    exit;
}

// ── Load cart items from DB ───────────────────────────────────
$cart      = $_SESSION['cart'] ?? [];
$items     = [];
$subtotal  = 0;

if (!empty($cart)) {
    $ids         = implode(',', array_map('intval', array_keys($cart)));
    $products    = $db->query("SELECT * FROM products WHERE id IN ($ids)")->fetchAll();

    foreach ($products as $p) {
        $qty  = $cart[$p['id']] ?? 1;
        // Cap qty to available stock
        $qty  = min($qty, $p['stock']);
        $line = $p['price'] * $qty;

        $items[]   = array_merge($p, ['qty' => $qty, 'line_total' => $line]);
        $subtotal += $line;
    }
}

$shipping = $subtotal >= 5000 ? 0 : 350;
$total    = $subtotal + $shipping;
?>
<?php include 'includes/header.php'; ?>

<div style="max-width:1000px;margin:0 auto;padding:48px 24px;">

    <h1 style="font-size:1.8rem;font-weight:800;color:var(--text);margin-bottom:6px;">Your Cart</h1>
    <p style="color:var(--muted);font-size:0.875rem;margin-bottom:32px;">
        <?= count($items) ?> item<?= count($items) !== 1 ? 's' : '' ?>
    </p>

    <?php if (empty($items)): ?>
        <!-- Empty state -->
        <div style="text-align:center;padding:80px 0;">
            <div style="font-size:3.5rem;margin-bottom:16px;">🛒</div>
            <h2 style="font-size:1.2rem;font-weight:700;color:var(--text);margin-bottom:8px;">Your cart is empty</h2>
            <p style="color:var(--muted);font-size:0.875rem;margin-bottom:28px;">
                Browse our products and add something you like.
            </p>
            <a href="/techhive/products/index.php" class="btn-primary">Shop Now</a>
        </div>

    <?php else: ?>
        <div style="display:grid;grid-template-columns:1fr 320px;gap:24px;align-items:start;">

            <!-- ── Cart items ── -->
            <div>
                <?php foreach ($items as $item): ?>
                    <div class="card" style="display:flex;gap:16px;padding:20px;margin-bottom:12px;align-items:center;">

                        <!-- Thumbnail -->
                        <div style="width:80px;height:80px;border-radius:8px;overflow:hidden;flex-shrink:0;background:var(--subtle);">
                            <?php $img = productImg($item['image']); ?>
                            <?php if ($img): ?>
                                <img src="<?= $img ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                                     style="width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1.8rem;">📦</div>
                            <?php endif; ?>
                        </div>

                        <!-- Info -->
                        <div style="flex:1;min-width:0;">
                            <p style="font-size:0.7rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:2px;">
                                <?= htmlspecialchars($item['category']) ?>
                            </p>
                            <h3 style="font-size:0.95rem;font-weight:700;color:var(--text);margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?= htmlspecialchars($item['name']) ?>
                            </h3>
                            <p style="font-size:0.9rem;font-weight:700;color:var(--accent);">
                                <?= ksh($item['price']) ?>
                            </p>
                        </div>

                        <!-- Qty controls -->
                        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action"     value="update">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="qty"        value="<?= $item['qty'] - 1 ?>">
                                <button type="submit"
                                    style="width:28px;height:28px;border-radius:6px;border:1px solid var(--border);background:var(--subtle);color:var(--text);font-size:1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;">−</button>
                            </form>

                            <span style="min-width:24px;text-align:center;font-size:0.9rem;font-weight:600;color:var(--text);">
                                <?= $item['qty'] ?>
                            </span>

                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action"     value="update">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="hidden" name="qty"        value="<?= $item['qty'] + 1 ?>">
                                <button type="submit"
                                    <?= $item['qty'] >= $item['stock'] ? 'disabled' : '' ?>
                                    style="width:28px;height:28px;border-radius:6px;border:1px solid var(--border);background:var(--subtle);color:var(--text);font-size:1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;<?= $item['qty'] >= $item['stock'] ? 'opacity:0.3;cursor:not-allowed;' : '' ?>">+</button>
                            </form>
                        </div>

                        <!-- Line total -->
                        <div style="min-width:100px;text-align:right;flex-shrink:0;">
                            <p style="font-size:0.95rem;font-weight:800;color:var(--text);">
                                <?= ksh($item['line_total']) ?>
                            </p>
                        </div>

                        <!-- Remove -->
                        <form method="POST">
                            <input type="hidden" name="action"     value="remove">
                            <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                            <button type="submit"
                                style="background:none;border:none;color:var(--muted);cursor:pointer;padding:4px;border-radius:4px;transition:color 0.15s;"
                                onmouseover="this.style.color='#f87171'"
                                onmouseout="this.style.color='var(--muted)'"
                                title="Remove">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </form>

                    </div>
                <?php endforeach; ?>

                <!-- Clear cart -->
                <form method="POST" style="margin-top:8px;">
                    <input type="hidden" name="action" value="clear">
                    <button type="submit"
                        style="background:none;border:none;color:var(--muted);font-size:0.8rem;cursor:pointer;text-decoration:underline;padding:0;"
                        onclick="return confirm('Clear all items from cart?')">
                        Clear cart
                    </button>
                </form>
            </div>

            <!-- ── Order summary ── -->
            <div class="card" style="padding:24px;position:sticky;top:80px;">
                <h2 style="font-size:1rem;font-weight:700;color:var(--text);margin-bottom:20px;">Order Summary</h2>

                <div style="display:flex;flex-direction:column;gap:12px;font-size:0.875rem;">
                    <div style="display:flex;justify-content:space-between;color:var(--muted);">
                        <span>Subtotal</span>
                        <span style="color:var(--text);font-weight:600;"><?= ksh($subtotal) ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;color:var(--muted);">
                        <span>Shipping</span>
                        <span style="color:<?= $shipping === 0 ? '#4ade80' : 'var(--text)' ?>;font-weight:600;">
                            <?= $shipping === 0 ? 'Free' : ksh($shipping) ?>
                        </span>
                    </div>
                    <?php if ($shipping > 0): ?>
                        <p style="font-size:0.75rem;color:var(--muted);background:var(--subtle);padding:8px 10px;border-radius:6px;">
                            Add <?= ksh(5000 - $subtotal) ?> more for free delivery
                        </p>
                    <?php endif; ?>

                    <div style="border-top:1px solid var(--border);padding-top:12px;margin-top:4px;display:flex;justify-content:space-between;">
                        <span style="font-weight:700;color:var(--text);">Total</span>
                        <span style="font-size:1.1rem;font-weight:800;color:var(--accent);"><?= ksh($total) ?></span>
                    </div>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/techhive/checkout.php" class="btn-primary" style="display:block;width:100%;margin-top:20px;text-align:center;">
                        Proceed to Checkout
                    </a>
                <?php else: ?>
                    <a href="/techhive/login.php" class="btn-primary" style="display:block;width:100%;margin-top:20px;text-align:center;">
                        Sign in to Checkout
                    </a>
                    <p style="font-size:0.75rem;color:var(--muted);text-align:center;margin-top:8px;">
                        or <a href="/techhive/register.php" style="color:var(--accent);text-decoration:none;">create an account</a>
                    </p>
                <?php endif; ?>

                <a href="/techhive/products/index.php"
                    style="display:block;text-align:center;margin-top:12px;font-size:0.8rem;color:var(--muted);text-decoration:none;">
                    ← Continue shopping
                </a>
            </div>

        </div>
    <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>
