<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db   = getDB();
$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

// Load cart products
$ids      = implode(',', array_map('intval', array_keys($cart)));
$products = $db->query("SELECT * FROM products WHERE id IN ($ids)")->fetchAll();

$items    = [];
$subtotal = 0;

foreach ($products as $p) {
    $qty  = min($cart[$p['id']] ?? 1, $p['stock']);
    $line = $p['price'] * $qty;
    $items[]   = array_merge($p, ['qty' => $qty, 'line_total' => $line]);
    $subtotal += $line;
}

$shipping = $subtotal >= 5000 ? 0 : 350;
$total    = $subtotal + $shipping;

$error   = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['full_name'] ?? '');
    $phone   = trim($_POST['phone']     ?? '');
    $address = trim($_POST['address']   ?? '');

    if (empty($name) || empty($phone) || empty($address)) {
        $error = 'Please fill in all delivery details.';
    } else {
        // Create the order
        $db->prepare("INSERT INTO orders (user_id, total, status) VALUES (?, ?, 'pending')")
           ->execute([$_SESSION['user_id'], $total]);
        $orderId = (int)$db->lastInsertId();

        // Insert order items
        $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?,?,?,?)");
        foreach ($items as $item) {
            $itemStmt->execute([$orderId, $item['id'], $item['qty'], $item['price']]);
            // Decrement stock
            $db->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")->execute([$item['qty'], $item['id']]);
        }

        // Clear session cart
        $_SESSION['cart'] = [];
        $success = $orderId;
    }
}
?>
<?php include 'includes/header.php'; ?>

<div style="max-width:960px;margin:0 auto;padding:48px 24px;">

    <h1 style="font-size:1.8rem;font-weight:800;color:var(--text);margin-bottom:6px;">Checkout</h1>
    <p style="color:var(--muted);font-size:0.875rem;margin-bottom:36px;">Review your order and enter delivery details.</p>

    <?php if ($success): ?>
        <div style="text-align:center;padding:60px 0;">
            <div style="font-size:3rem;margin-bottom:16px;">✓</div>
            <h2 style="font-size:1.4rem;font-weight:800;color:var(--text);margin-bottom:8px;">Order placed!</h2>
            <p style="color:var(--muted);font-size:0.9rem;margin-bottom:4px;">Order #<?= $success ?> has been received.</p>
            <p style="color:var(--muted);font-size:0.875rem;margin-bottom:32px;">We'll contact you soon to confirm delivery.</p>
            <a href="/techhive/products/index.php" class="btn-primary">Continue Shopping</a>
        </div>
    <?php else: ?>

        <?php if ($error): ?>
            <div class="alert-error" style="margin-bottom:20px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

            <!-- Delivery form -->
            <div>
                <div class="card" style="padding:28px;margin-bottom:20px;">
                    <h2 style="font-size:1rem;font-weight:700;color:var(--text);margin-bottom:20px;">Delivery Details</h2>
                    <form method="POST" id="checkout-form">

                        <div style="margin-bottom:14px;">
                            <label class="label">Full Name</label>
                            <input type="text" name="full_name" class="field"
                                placeholder="e.g. John Kamau"
                                value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
                        </div>

                        <div style="margin-bottom:14px;">
                            <label class="label">Phone Number</label>
                            <input type="tel" name="phone" class="field"
                                placeholder="e.g. 0712 345 678"
                                value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
                        </div>

                        <div style="margin-bottom:20px;">
                            <label class="label">Delivery Address</label>
                            <textarea name="address" class="field" rows="3"
                                placeholder="Street, estate, town..."
                                style="resize:vertical;"><?= htmlspecialchars($_POST['address'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn-primary" style="width:100%;font-size:0.9rem;padding:12px;">
                            Place Order · <?= ksh($total) ?>
                        </button>
                    </form>
                </div>

                <a href="cart.php" style="font-size:0.82rem;color:var(--muted);text-decoration:none;">← Back to cart</a>
            </div>

            <!-- Order summary -->
            <div class="card" style="padding:24px;position:sticky;top:80px;">
                <h2 style="font-size:1rem;font-weight:700;color:var(--text);margin-bottom:16px;">Order Summary</h2>

                <?php foreach ($items as $item): ?>
                <div style="display:flex;gap:12px;align-items:center;margin-bottom:14px;">
                    <div style="width:44px;height:44px;border-radius:6px;overflow:hidden;flex-shrink:0;background:var(--subtle);">
                        <?php $img = productImg($item['image']); ?>
                        <?php if ($img): ?>
                            <img src="<?= $img ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
                        <?php else: ?>
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:1.2rem;">📦</div>
                        <?php endif; ?>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <p style="font-size:0.82rem;font-weight:600;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($item['name']) ?></p>
                        <p style="font-size:0.75rem;color:var(--muted);">Qty: <?= $item['qty'] ?></p>
                    </div>
                    <p style="font-size:0.85rem;font-weight:700;color:var(--text);flex-shrink:0;"><?= ksh($item['line_total']) ?></p>
                </div>
                <?php endforeach; ?>

                <div style="border-top:1px solid var(--border);padding-top:14px;margin-top:4px;display:flex;flex-direction:column;gap:10px;font-size:0.875rem;">
                    <div style="display:flex;justify-content:space-between;color:var(--muted);">
                        <span>Subtotal</span>
                        <span style="color:var(--text);font-weight:600;"><?= ksh($subtotal) ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;color:var(--muted);">
                        <span>Shipping</span>
                        <span style="color:<?= $shipping === 0 ? '#16a34a' : 'var(--text)' ?>;font-weight:600;"><?= $shipping === 0 ? 'Free' : ksh($shipping) ?></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;border-top:1px solid var(--border);padding-top:10px;">
                        <span style="font-weight:700;color:var(--text);">Total</span>
                        <span style="font-size:1.1rem;font-weight:800;color:var(--accent);"><?= ksh($total) ?></span>
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
