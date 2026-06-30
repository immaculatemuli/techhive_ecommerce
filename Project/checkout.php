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
$paidVia = '';

$paymentMethods = [
    'mpesa' => 'M-Pesa',
    'card'  => 'Card',
    'cod'   => 'Cash on Delivery',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['full_name'] ?? '');
    $phone   = trim($_POST['phone']     ?? '');
    $address = trim($_POST['address']   ?? '');
    $method  = array_key_exists($_POST['payment_method'] ?? '', $paymentMethods) ? $_POST['payment_method'] : 'cod';

    if (empty($name) || empty($phone) || empty($address)) {
        $error = 'Please fill in all delivery details.';
    } else {
        // Card/M-Pesa are confirmed client-side via the dummy payment prompt before this submits
        $paymentStatus = $method === 'cod' ? 'pending' : 'paid';

        // Create the order
        $db->prepare("INSERT INTO orders (user_id, total, status, payment_method, payment_status) VALUES (?, ?, 'pending', ?, ?)")
           ->execute([$_SESSION['user_id'], $total, $method, $paymentStatus]);
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
        $paidVia = $method;
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
            <?php if ($paidVia === 'cod'): ?>
                <p style="color:var(--muted);font-size:0.875rem;margin-bottom:18px;">You chose Cash on Delivery — pay when your order arrives.</p>
            <?php else: ?>
                <div style="display:inline-flex;align-items:center;gap:6px;background:#f0fdf4;border:1px solid #bbf7d0;color:#15803d;border-radius:99px;padding:5px 14px;font-size:0.8rem;font-weight:700;margin-bottom:18px;">
                    ✓ Paid via <?= htmlspecialchars($paymentMethods[$paidVia]) ?>
                </div>
            <?php endif; ?>
            <p style="color:var(--muted);font-size:0.875rem;margin-bottom:32px;">We'll contact you soon to confirm delivery.</p>
            <a href="/techhive/Project/products/index.php" class="btn-primary">Continue Shopping</a>
        </div>
    <?php else: ?>

        <?php if ($error): ?>
            <div class="alert-error" style="margin-bottom:20px;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="checkout-grid" style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">

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

                        <div style="margin-bottom:20px;">
                            <label class="label">Payment Method</label>
                            <div class="pay-options">
                                <label class="pay-option">
                                    <input type="radio" name="payment_method" value="mpesa" checked>
                                    <span class="pay-option-icon">📱</span>
                                    <span class="pay-option-label">M-Pesa</span>
                                </label>
                                <label class="pay-option">
                                    <input type="radio" name="payment_method" value="card">
                                    <span class="pay-option-icon">💳</span>
                                    <span class="pay-option-label">Card</span>
                                </label>
                                <label class="pay-option">
                                    <input type="radio" name="payment_method" value="cod">
                                    <span class="pay-option-icon">💵</span>
                                    <span class="pay-option-label">Cash on Delivery</span>
                                </label>
                            </div>

                            <div id="pay-panel-card" class="pay-panel" style="display:none;">
                                <p style="font-size:0.78rem;color:var(--muted);margin-bottom:10px;">Demo checkout — no real card is charged.</p>
                                <input type="text" class="field" placeholder="Card number" maxlength="19" autocomplete="off" inputmode="numeric" id="card-number" style="margin-bottom:10px;">
                                <div style="display:flex;gap:10px;">
                                    <input type="text" class="field" placeholder="MM/YY" maxlength="5" autocomplete="off" id="card-expiry">
                                    <input type="text" class="field" placeholder="CVV" maxlength="3" autocomplete="off" inputmode="numeric" id="card-cvv">
                                </div>
                            </div>

                            <div id="pay-panel-mpesa" class="pay-panel">
                                <p style="font-size:0.78rem;color:var(--muted);">Demo checkout — we'll simulate an M-Pesa prompt confirming payment from the phone number above. No real payment is made.</p>
                            </div>
                        </div>

                        <button type="submit" id="checkout-submit" class="btn-primary" style="width:100%;font-size:0.9rem;padding:12px;">
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

        <!-- Dummy payment confirmation modal -->
        <div id="pay-modal-overlay" class="pay-modal-overlay">
            <div class="pay-modal">

                <div id="pay-step-mpesa-confirm" class="pay-step">
                    <div class="pay-modal-icon mpesa">📱</div>
                    <h3 class="pay-modal-title">Confirm M-Pesa payment</h3>
                    <p class="pay-modal-text">Pay <strong><?= ksh($total) ?></strong> to TechHive from <strong id="pay-modal-phone">your phone</strong>?</p>
                    <button type="button" id="pay-mpesa-ok" class="btn-primary" style="width:100%;">OK, Pay <?= ksh($total) ?></button>
                </div>

                <div id="pay-step-card-confirm" class="pay-step" style="display:none;">
                    <div class="pay-modal-icon card">💳</div>
                    <h3 class="pay-modal-title">Confirm card payment</h3>
                    <p class="pay-modal-text">Charge <strong><?= ksh($total) ?></strong> to your card?</p>
                    <button type="button" id="pay-card-ok" class="btn-primary" style="width:100%;">OK, Pay <?= ksh($total) ?></button>
                </div>

                <div id="pay-step-mpesa-wait" class="pay-step" style="display:none;">
                    <div class="pay-modal-icon mpesa">📱</div>
                    <h3 class="pay-modal-title">Confirming payment</h3>
                    <p class="pay-modal-text">Talking to M-Pesa…</p>
                    <div class="pay-spinner"></div>
                </div>

                <div id="pay-step-card-wait" class="pay-step" style="display:none;">
                    <div class="pay-modal-icon card">💳</div>
                    <h3 class="pay-modal-title">Processing payment</h3>
                    <p class="pay-modal-text">Charging <strong><?= ksh($total) ?></strong> to your card…</p>
                    <div class="pay-spinner"></div>
                </div>

                <div id="pay-step-success" class="pay-step" style="display:none;">
                    <div class="pay-modal-icon success">✓</div>
                    <h3 class="pay-modal-title">Payment successful</h3>
                    <p class="pay-modal-text"><strong><?= ksh($total) ?></strong> received. Placing your order…</p>
                </div>

            </div>
        </div>

    <?php endif; ?>
</div>

<style>
@media (max-width: 760px) {
    .checkout-grid { grid-template-columns: 1fr !important; }
    .checkout-grid > div[style*="sticky"] { position: static !important; }
}
.pay-options { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; margin-bottom:12px; }
.pay-option {
    display:flex; flex-direction:column; align-items:center; gap:6px;
    border:1.5px solid var(--border); border-radius:8px; padding:12px 6px;
    cursor:pointer; transition:border-color 0.15s, background 0.15s;
}
.pay-option:hover { border-color:#9ca3af; }
.pay-option input { position:absolute; opacity:0; pointer-events:none; }
.pay-option:has(input:checked) { border-color:var(--accent); background:var(--subtle); }
.pay-option-icon  { font-size:1.3rem; line-height:1; }
.pay-option-label { font-size:0.74rem; font-weight:600; color:var(--text); text-align:center; }
.pay-panel { background:var(--subtle); border:1px solid var(--border); border-radius:8px; padding:14px; }

.pay-modal-overlay {
    display:none; position:fixed; inset:0; background:rgba(17,24,39,0.55);
    align-items:center; justify-content:center; z-index:1000; padding:16px;
}
.pay-modal-overlay.open { display:flex; }
.pay-modal {
    background:#fff; border-radius:14px; max-width:360px; width:100%;
    padding:36px 28px; text-align:center; box-shadow:0 20px 50px rgba(0,0,0,0.25);
}
.pay-modal-icon { font-size:2.4rem; margin-bottom:14px; }
.pay-modal-icon.success { color:#16a34a; }
.pay-modal-title { font-size:1.05rem; font-weight:800; color:var(--text); margin-bottom:8px; }
.pay-modal-text  { font-size:0.85rem; color:var(--muted); line-height:1.5; margin-bottom:16px; }
.pay-spinner {
    width:32px; height:32px; border:3px solid var(--border); border-top-color:var(--accent);
    border-radius:50%; margin:0 auto; animation:pay-spin 0.8s linear infinite;
}
@keyframes pay-spin { to { transform:rotate(360deg); } }
</style>

<script>
(function () {
    var form = document.getElementById('checkout-form');
    if (!form) return;

    var cardPanel  = document.getElementById('pay-panel-card');
    var mpesaPanel = document.getElementById('pay-panel-mpesa');
    var radios     = form.querySelectorAll('input[name="payment_method"]');

    function syncPanel() {
        var method = form.querySelector('input[name="payment_method"]:checked').value;
        cardPanel.style.display  = method === 'card'  ? 'block' : 'none';
        mpesaPanel.style.display = method === 'mpesa' ? 'block' : 'none';
    }
    radios.forEach(function (r) { r.addEventListener('change', syncPanel); });
    syncPanel();

    var overlay = document.getElementById('pay-modal-overlay');
    var allSteps = overlay.querySelectorAll('.pay-step');
    var stepMpesaConfirm = document.getElementById('pay-step-mpesa-confirm');
    var stepCardConfirm  = document.getElementById('pay-step-card-confirm');
    var stepMpesaWait    = document.getElementById('pay-step-mpesa-wait');
    var stepCardWait     = document.getElementById('pay-step-card-wait');
    var stepSuccess      = document.getElementById('pay-step-success');
    var phoneSpan        = document.getElementById('pay-modal-phone');
    var okMpesaBtn        = document.getElementById('pay-mpesa-ok');
    var okCardBtn         = document.getElementById('pay-card-ok');
    var submitted         = false;

    function showStep(step) {
        allSteps.forEach(function (s) { s.style.display = 'none'; });
        step.style.display = 'block';
    }

    function finishPayment(waitStep) {
        showStep(waitStep);
        setTimeout(function () {
            showStep(stepSuccess);
            setTimeout(function () {
                submitted = true;
                form.submit();
            }, 900);
        }, 1100);
    }

    okMpesaBtn.addEventListener('click', function () { finishPayment(stepMpesaWait); });
    okCardBtn.addEventListener('click',  function () { finishPayment(stepCardWait); });

    form.addEventListener('submit', function (e) {
        if (submitted) return; // the real submit triggered by finishPayment() — let it through

        var method = form.querySelector('input[name="payment_method"]:checked').value;
        if (method === 'cod') return; // no simulation needed

        e.preventDefault();

        if (method === 'mpesa') {
            var phone = (form.phone.value || '').trim();
            phoneSpan.textContent = phone || 'your phone';
            showStep(stepMpesaConfirm);
        } else {
            showStep(stepCardConfirm);
        }

        overlay.classList.add('open');
    });
})();
</script>

<?php include 'includes/footer.php'; ?>
