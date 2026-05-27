<?php
require_once '../config.php';
session_start();

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

if (!$id) { header('Location: index.php'); exit; }

$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) { header('Location: index.php'); exit; }

$src   = productImg($p['image']);
$stock = (int)$p['stock'];

// Related products — same category, exclude current
$related = $db->prepare("SELECT * FROM products WHERE category = ? AND id != ? AND stock > 0 ORDER BY RAND() LIMIT 4");
$related->execute([$p['category'], $p['id']]);
$related = $related->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($p['name']) ?> | TechHive</title>
    <link rel="stylesheet" href="/techhive/css/style.css">
</head>
<body>

<style>
.pd-wrap { max-width: 1140px; margin: 0 auto; padding: 36px 24px 72px; }

.breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 0.78rem; color: #9ca3af; margin-bottom: 32px; flex-wrap: wrap; }
.breadcrumb a { color: #6b7280; text-decoration: none; transition: color 0.15s; }
.breadcrumb a:hover { color: #111827; }
.breadcrumb span { color: #d1d5db; }

.pd-grid { display: grid; grid-template-columns: 1fr 480px; gap: 56px; align-items: start; }

.pd-img-panel { position: sticky; top: 80px; }
.pd-img-main {
    border-radius: 20px; overflow: hidden;
    background: #f8fafc; aspect-ratio: 1;
    position: relative; border: 1px solid #e5e7eb;
}
.pd-img-main img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
.pd-img-main:hover img { transform: scale(1.04); }

.pd-category-tag {
    display: inline-flex; align-items: center; gap: 6px;
    background: #eff6ff; color: #1e3a8a;
    font-size: 0.72rem; font-weight: 700;
    padding: 5px 12px; border-radius: 99px;
    text-transform: uppercase; letter-spacing: 0.07em;
    margin-bottom: 14px;
}
.pd-title { font-size: 2rem; font-weight: 900; color: #111827; letter-spacing: -1px; line-height: 1.15; margin-bottom: 20px; }
.pd-price-row { display: flex; align-items: center; gap: 14px; margin-bottom: 18px; flex-wrap: wrap; }
.pd-price { font-size: 2.2rem; font-weight: 900; color: #111827; letter-spacing: -1.5px; }
.pd-stock-badge { display: inline-flex; align-items: center; gap: 5px; font-size: 0.78rem; font-weight: 700; padding: 5px 12px; border-radius: 99px; }
.pd-stock-in  { background: #dcfce7; color: #15803d; }
.pd-stock-low { background: #fef3c7; color: #92400e; }
.pd-stock-out { background: #fee2e2; color: #dc2626; }
.pd-divider { border: none; border-top: 1px solid #e5e7eb; margin: 22px 0; }
.pd-desc-title { font-size: 0.8rem; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 10px; }
.pd-desc-text  { font-size: 0.92rem; color: #6b7280; line-height: 1.85; }

.pd-qty-row { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
.pd-qty-label { font-size: 0.8rem; font-weight: 600; color: #374151; }
.pd-qty-ctrl { display: flex; align-items: center; border: 1.5px solid #e5e7eb; border-radius: 9px; overflow: hidden; }
.pd-qty-btn { width: 36px; height: 36px; background: #f9fafb; border: none; font-size: 1.1rem; font-weight: 600; color: #374151; cursor: pointer; transition: background 0.15s; display: flex; align-items: center; justify-content: center; }
.pd-qty-btn:hover { background: #f3f4f6; }
.pd-qty-btn:disabled { opacity: 0.35; cursor: not-allowed; }
.pd-qty-val { min-width: 40px; text-align: center; font-size: 0.9rem; font-weight: 700; color: #111827; }

.pd-btn-cart { width: 100%; padding: 15px; font-size: 0.95rem; font-weight: 700; background: #111827; color: #fff; border: none; border-radius: 10px; cursor: pointer; transition: background 0.15s, transform 0.1s; margin-bottom: 10px; letter-spacing: -0.2px; }
.pd-btn-cart:hover { background: #1f2937; }
.pd-btn-cart:active { transform: scale(0.99); }
.pd-btn-checkout { display: block; width: 100%; padding: 15px; font-size: 0.95rem; font-weight: 700; background: #1e3a8a; color: #fff; border: none; border-radius: 10px; cursor: pointer; transition: background 0.15s; text-align: center; text-decoration: none; letter-spacing: -0.2px; }
.pd-btn-checkout:hover { background: #1e40af; }

.pd-trust { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 20px; }
.pd-trust-item { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px 10px; text-align: center; }
.pd-trust-item .t-icon  { font-size: 1.3rem; margin-bottom: 5px; }
.pd-trust-item .t-label { font-size: 0.68rem; font-weight: 700; color: #374151; line-height: 1.4; }

.related-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 18px; }
.r-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; text-decoration: none; transition: box-shadow 0.25s, transform 0.25s; display: block; }
.r-card:hover { box-shadow: 0 20px 50px rgba(0,0,0,0.1); transform: translateY(-4px); }
.r-img { height: 180px; overflow: hidden; background: #f3f4f6; }
.r-img img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
.r-card:hover .r-img img { transform: scale(1.06); }
.r-body { padding: 14px 16px; }

@media (max-width: 860px) {
    .pd-grid { grid-template-columns: 1fr; gap: 28px; }
    .pd-img-panel { position: static; }
    .pd-title { font-size: 1.55rem; }
}
@media (max-width: 480px) {
    .pd-trust { grid-template-columns: 1fr; }
    .pd-price { font-size: 1.8rem; }
}
</style>

<div class="pd-wrap">

    <nav class="breadcrumb">
        <a href="/techhive/index.php">Home</a>
        <span>›</span>
        <a href="/techhive/products/index.php">Products</a>
        <span>›</span>
        <a href="/techhive/products/index.php?category=<?= urlencode($p['category']) ?>"><?= htmlspecialchars($p['category']) ?></a>
        <span>›</span>
        <span style="color:#111827;font-weight:500;"><?= htmlspecialchars($p['name']) ?></span>
    </nav>

    <div class="pd-grid">

        <!-- Image -->
        <div class="pd-img-panel">
            <div class="pd-img-main">
                <?php if ($src): ?>
                    <img src="<?= $src ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                <?php else: ?>
                    <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:6rem;opacity:0.15;">📦</div>
                <?php endif; ?>

                <?php if ($stock === 0): ?>
                    <div style="position:absolute;inset:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;">
                        <span style="background:#dc2626;color:#fff;font-size:0.9rem;font-weight:700;padding:10px 22px;border-radius:99px;text-transform:uppercase;letter-spacing:0.05em;">Out of Stock</span>
                    </div>
                <?php endif; ?>
            </div>
            <p style="text-align:center;font-size:0.72rem;color:#d1d5db;margin-top:12px;">Product #<?= $p['id'] ?></p>
        </div>

        <!-- Details -->
        <div>

            <div class="pd-category-tag">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                <?= htmlspecialchars($p['category']) ?>
            </div>

            <h1 class="pd-title"><?= htmlspecialchars($p['name']) ?></h1>

            <div class="pd-price-row">
                <span class="pd-price"><?= ksh($p['price']) ?></span>
                <?php if ($stock === 0): ?>
                    <span class="pd-stock-badge pd-stock-out">Out of Stock</span>
                <?php elseif ($stock <= 3): ?>
                    <span class="pd-stock-badge pd-stock-low">&#9888; Only <?= $stock ?> left</span>
                <?php else: ?>
                    <span class="pd-stock-badge pd-stock-in">&#10003; In Stock</span>
                <?php endif; ?>
            </div>

            <hr class="pd-divider">

            <?php if ($p['description']): ?>
            <div style="margin-bottom:24px;">
                <p class="pd-desc-title">About this product</p>
                <p class="pd-desc-text"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
            </div>
            <hr class="pd-divider">
            <?php endif; ?>

            <?php if ($stock > 0): ?>
                <div class="pd-qty-row">
                    <span class="pd-qty-label">Quantity</span>
                    <div class="pd-qty-ctrl">
                        <button class="pd-qty-btn" id="qty-minus" type="button">&#8722;</button>
                        <span class="pd-qty-val" id="qty-display">1</span>
                        <button class="pd-qty-btn" id="qty-plus" type="button" <?= $stock <= 1 ? 'disabled' : '' ?>>&#43;</button>
                    </div>
                    <span style="font-size:0.75rem;color:#9ca3af;"><?= $stock ?> available</span>
                </div>

                <form method="POST" action="/techhive/cart.php">
                    <input type="hidden" name="action"     value="add">
                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="qty"        value="1" id="qty-input">
                    <button type="submit" class="pd-btn-cart">Add to Cart</button>
                </form>

                <a href="/techhive/checkout.php" class="pd-btn-checkout">Buy Now</a>

            <?php else: ?>
                <button disabled class="pd-btn-cart" style="background:#e5e7eb;color:#9ca3af;cursor:not-allowed;">
                    Out of Stock
                </button>
            <?php endif; ?>


        </div>
    </div>

    <?php if (!empty($related)): ?>
    <div style="margin-top:72px;">
        <h2 style="font-size:1.3rem;font-weight:800;color:#111827;letter-spacing:-0.5px;margin-bottom:6px;">More in <?= htmlspecialchars($p['category']) ?></h2>
        <p style="font-size:0.82rem;color:#9ca3af;margin-bottom:24px;">You might also like these</p>
        <div class="related-grid">
            <?php foreach ($related as $r):
                $rSrc = productImg($r['image']);
            ?>
            <a href="view.php?id=<?= $r['id'] ?>" class="r-card">
                <div class="r-img">
                    <?php if ($rSrc): ?>
                        <img src="<?= $rSrc ?>" alt="<?= htmlspecialchars($r['name']) ?>" loading="lazy">
                    <?php else: ?>
                        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2.5rem;opacity:0.2;">📦</div>
                    <?php endif; ?>
                </div>
                <div class="r-body">
                    <p style="font-size:0.7rem;font-weight:700;color:#1e3a8a;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;"><?= htmlspecialchars($r['category']) ?></p>
                    <p style="font-size:0.88rem;font-weight:700;color:#111827;line-height:1.4;margin-bottom:8px;"><?= htmlspecialchars($r['name']) ?></p>
                    <p style="font-size:1rem;font-weight:800;color:#111827;letter-spacing:-0.5px;"><?= ksh($r['price']) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
const max   = <?= $stock ?>;
let qty     = 1;
const minus = document.getElementById('qty-minus');
const plus  = document.getElementById('qty-plus');
const disp  = document.getElementById('qty-display');
const input = document.getElementById('qty-input');

function updateQty(n) {
    qty = Math.max(1, Math.min(n, max));
    disp.textContent = qty;
    if (input) input.value = qty;
    minus.disabled = qty <= 1;
    plus.disabled  = qty >= max;
}
minus?.addEventListener('click', () => updateQty(qty - 1));
plus?.addEventListener('click',  () => updateQty(qty + 1));
updateQty(1);
</script>

<?php include '../includes/footer.php'; ?>
