<?php
require_once 'config.php';
session_start();

$db       = getDB();
$products = $db->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 8")->fetchAll();
$total    = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
$users    = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechHive — Kenya's Premium Tech Store</title>
    <link rel="stylesheet" href="/techhive/css/style.css">
    <style>

        /* ── Hero ── */
        .hero {
            position: relative;
            min-height: 580px;
            display: flex;
            align-items: center;
            overflow: hidden;
        }
        .hero-bg {
            position: absolute; inset: 0;
            background-image: url('https://images.unsplash.com/photo-1531297484001-80022131f5a1?w=1600&q=80');
            background-size: cover;
            background-position: center 30%;
        }
        .hero-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(90deg, rgba(0,0,0,0.82) 0%, rgba(0,0,0,0.55) 60%, rgba(0,0,0,0.2) 100%);
        }
        .hero-content {
            position: relative; z-index: 1;
            max-width: 1200px; margin: 0 auto;
            padding: 80px 40px;
        }

        /* ── Category cards ── */
        .cat-cards {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 14px;
        }
        .cat-card {
            position: relative;
            border-radius: 12px;
            overflow: hidden;
            aspect-ratio: 3/2;
            text-decoration: none;
            display: block;
            cursor: pointer;
        }
        .cat-card img {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .cat-card:hover img { transform: scale(1.06); }
        .cat-card-overlay {
            position: absolute; inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.75) 0%, rgba(0,0,0,0.1) 60%);
            transition: background 0.3s;
        }
        .cat-card:hover .cat-card-overlay { background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.2) 60%); }
        .cat-card-name {
            position: absolute;
            bottom: 14px; left: 14px; right: 14px;
            color: #fff;
            font-size: 0.88rem;
            font-weight: 700;
            letter-spacing: -0.2px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* ── Product card ── */
        .p-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: box-shadow 0.25s, transform 0.25s;
        }
        .p-card:hover {
            box-shadow: 0 24px 56px rgba(0,0,0,0.11);
            transform: translateY(-5px);
        }
        .p-img-wrap {
            height: 210px;
            overflow: hidden;
            background: #f3f4f6;
            position: relative;
        }
        .p-img {
            width: 100%; height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        .p-card:hover .p-img { transform: scale(1.05); }
        .p-body { padding: 16px 18px 20px; flex: 1; display: flex; flex-direction: column; gap: 10px; }


        /* ── Cart icon button ── */
        .cart-icon-btn {
            width: 34px; height: 34px; border-radius: 50%;
            background: #111827; color: #fff;
            border: none; cursor: pointer; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            transition: background 0.2s, transform 0.15s;
        }
        .cart-icon-btn:hover { background: #374151; transform: scale(1.1); }
        .cart-icon-btn.loading { background: #9ca3af; cursor: not-allowed; }
        .cart-icon-btn.success { background: #15803d; }

        /* ── Toast ── */
        .th-toast {
            position: fixed; bottom: 24px; right: 24px; z-index: 9999;
            background: #111827; color: #fff;
            padding: 13px 18px; border-radius: 10px;
            font-size: 0.85rem; font-weight: 600;
            display: flex; align-items: center; gap: 9px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.22);
            transform: translateY(20px); opacity: 0;
            transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), opacity 0.3s;
            pointer-events: none;
        }
        .th-toast.in { transform: translateY(0); opacity: 1; }
        @keyframes badge-pop {
            0%   { transform: scale(1); }
            40%  { transform: scale(1.5); }
            70%  { transform: scale(0.88); }
            100% { transform: scale(1); }
        }
        .badge-pop { animation: badge-pop 0.4s cubic-bezier(0.34,1.56,0.64,1); }
        @keyframes th-ripple { to { transform: scale(1); opacity: 0; } }
        @keyframes cart-land {
            0%   { transform: rotate(0)     scale(1);    }
            20%  { transform: rotate(-14deg) scale(1.22); }
            45%  { transform: rotate(9deg)   scale(1.14); }
            65%  { transform: rotate(-5deg)  scale(1.07); }
            82%  { transform: rotate(2deg)   scale(1.03); }
            100% { transform: rotate(0)     scale(1);    }
        }
        .cart-land { animation: cart-land 0.58s cubic-bezier(0.34,1.56,0.64,1); }

        /* ── Deal banner ── */
        .deal {
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #111827;
            border-radius: 16px;
            overflow: hidden;
            min-height: 300px;
        }
        .deal-img-col { overflow: hidden; }
        .deal-img-col img { width: 100%; height: 100%; object-fit: cover; opacity: 0.85; }
        .deal-body { padding: 44px 40px; display: flex; flex-direction: column; justify-content: center; gap: 14px; }

        /* ── Stats ── */
        .stats { display: grid; grid-template-columns: repeat(4, 1fr); background: #0f172a; }
        .stat-cell { padding: 32px 24px; text-align: center; border-right: 1px solid rgba(255,255,255,0.06); }
        .stat-cell:last-child { border-right: none; }

        /* ── Trust bar ── */
        .trust { display: grid; grid-template-columns: repeat(3, 1fr); border: 1px solid #e5e7eb; border-radius: 14px; overflow: hidden; }
        .trust-item { padding: 24px 28px; display: flex; align-items: center; gap: 16px; border-right: 1px solid #e5e7eb; background: #fff; }
        .trust-item:last-child { border-right: none; }

        /* ── Section heading ── */
        .sec-row { display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 28px; }
        .sec-title { font-size: 1.5rem; font-weight: 800; color: #111827; letter-spacing: -0.5px; }

        @media (max-width: 960px) {
            .cat-cards { grid-template-columns: repeat(3, 1fr); }
            .deal { grid-template-columns: 1fr; }
            .stats { grid-template-columns: repeat(2, 1fr); }
            .trust { grid-template-columns: 1fr; }
        }
        @media (max-width: 640px) {
            .cat-cards { grid-template-columns: repeat(2, 1fr); }
            .hero-content { padding: 60px 20px; }
        }
    </style>
</head>
<body>



<?php include 'includes/header.php'; ?>

<!-- ═══ HERO ══════════════════════════════════════════════ -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-overlay"></div>
    <div class="hero-content" style="width:100%;">
        <p style="font-size:0.75rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;color:rgba(255,255,255,0.45);margin-bottom:14px;">
            Kenya's #1 Tech Store
        </p>
        <h1 style="font-size:clamp(2.4rem,5vw,4rem);font-weight:900;color:#fff;line-height:1.06;letter-spacing:-2px;margin-bottom:20px;max-width:600px;">
            Premium Tech,<br>Kenyan Prices.
        </h1>
        <p style="color:rgba(255,255,255,0.55);font-size:1rem;line-height:1.75;max-width:400px;margin-bottom:36px;">
            Genuine laptops, phones and accessories. Fast delivery..
        </p>
        <div style="display:flex;gap:14px;flex-wrap:wrap;">
            <a href="/techhive/products/index.php" class="btn-primary"
               style="background:#fff;color:#111827;padding:14px 32px;font-size:0.95rem;letter-spacing:-0.2px;">
                Shop Now
            </a>
            <a href="/techhive/products/index.php"
               style="padding:14px 32px;font-size:0.95rem;font-weight:600;color:rgba(255,255,255,0.65);
                      text-decoration:none;border:1.5px solid rgba(255,255,255,0.2);border-radius:8px;letter-spacing:-0.2px;
                      transition:all 0.2s;"
               onmouseover="this.style.color='#fff';this.style.borderColor='rgba(255,255,255,0.5)'"
               onmouseout="this.style.color='rgba(255,255,255,0.65)';this.style.borderColor='rgba(255,255,255,0.2)'">
                Browse All →
            </a>
        </div>
    </div>
</section>

<!-- ═══ CATEGORIES ════════════════════════════════════════ -->
<section style="background:#fff;padding:56px 0;">
<div style="max-width:1200px;margin:0 auto;padding:0 40px;">

    <div class="sec-row">
        <h2 class="sec-title reveal">Shop by Category</h2>
    </div>

    <div class="cat-cards">
        <?php
        $catData = [
            ['Laptops',     'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=500&q=80&fit=crop'],
            ['Phones',      'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=500&q=80&fit=crop'],
            ['Accessories', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&q=80&fit=crop'],
            ['Tablets',     'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=500&q=80&fit=crop'],
            ['Gaming',      'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500&q=80&fit=crop'],
        ];
        foreach ($catData as [$name, $img]):
        ?>
        <a href="/techhive/products/index.php?category=<?= urlencode($name) ?>" class="cat-card reveal">
            <img src="<?= $img ?>" alt="<?= $name ?>" loading="lazy">
            <div class="cat-card-overlay"></div>
            <div class="cat-card-name">
                <span><?= $name ?></span>
                <span style="font-size:1rem;opacity:0.6;">→</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
</section>

<!-- ═══ FEATURED PRODUCTS ════════════════════════════════ -->
<section style="background:#f9fafb;padding:56px 0 72px;">
<div style="max-width:1200px;margin:0 auto;padding:0 40px;">

    <div class="sec-row">
        <div>
            <h2 class="sec-title reveal">Featured Products</h2>
            <p style="color:#6b7280;font-size:0.875rem;margin-top:4px;" class="reveal reveal-d1"><?= $total ?> products in store</p>
        </div>
        <a href="/techhive/products/index.php"
           style="font-size:0.875rem;font-weight:700;color:#111827;text-decoration:none;
                  padding-bottom:2px;border-bottom:2px solid #111827;" class="reveal">
            View all →
        </a>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:20px;">
        <?php foreach ($products as $i => $p):
            $src = productImg($p['image']);
        ?>
        <div class="p-card reveal reveal-d<?= ($i % 3) + 1 ?>"
             data-name="<?= htmlspecialchars(strtolower($p['name'])) ?>">

            <div class="p-img-wrap">
                <?php if ($src): ?>
                    <img class="p-img" src="<?= $src ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                <?php else: ?>
                    <div style="width:100%;height:100%;background:linear-gradient(135deg,#1e293b,#334155);
                                display:flex;align-items:center;justify-content:center;font-size:2.5rem;opacity:0.5;">📦</div>
                <?php endif; ?>
                <span style="position:absolute;top:12px;left:12px;
                             background:rgba(0,0,0,0.68);color:#fff;backdrop-filter:blur(4px);
                             font-size:0.62rem;font-weight:700;padding:3px 9px;
                             border-radius:99px;text-transform:uppercase;letter-spacing:0.06em;">
                    <?= htmlspecialchars($p['category']) ?>
                </span>
                <?php if ($p['stock'] > 0 && $p['stock'] <= 3): ?>
                    <span style="position:absolute;top:12px;right:12px;
                                 background:#dc2626;color:#fff;font-size:0.62rem;font-weight:700;
                                 padding:3px 9px;border-radius:99px;">
                        <?= $p['stock'] ?> left
                    </span>
                <?php endif; ?>

            </div>

            <div class="p-body">
                <div style="flex:1;">
                    <h3 style="font-size:0.93rem;font-weight:700;color:#111827;line-height:1.4;margin-bottom:4px;">
                        <?= htmlspecialchars($p['name']) ?>
                    </h3>
                    <p style="font-size:0.77rem;color:#6b7280;line-height:1.55;
                              display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                        <?= htmlspecialchars($p['description']) ?>
                    </p>
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;
                            padding-top:12px;border-top:1px solid #f3f4f6;gap:8px;">
                    <span style="font-size:1.05rem;font-weight:800;color:#111827;letter-spacing:-0.5px;">
                        <?= ksh($p['price']) ?>
                    </span>
                    <div style="display:flex;align-items:center;gap:7px;">
                        <span style="font-size:0.7rem;font-weight:600;padding:3px 9px;border-radius:99px;
                            <?= $p['stock'] > 0 ? 'background:#dcfce7;color:#15803d;' : 'background:#fee2e2;color:#dc2626;' ?>">
                            <?= $p['stock'] > 0 ? 'In stock' : 'Sold out' ?>
                        </span>
                        <?php if ($p['stock'] > 0): ?>
                        <button class="cart-icon-btn" data-cart-id="<?= $p['id'] ?>" title="Add to cart">
                            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 7h11M10 21a1 1 0 100-2 1 1 0 000 2zm8 0a1 1 0 100-2 1 1 0 000 2z"/></svg>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>
</section>

<!-- ═══ STATS ═════════════════════════════════════════════ -->
<section class="stats">
    <?php
    $rows = [
        [$total, '+', 'Products in store'],
        [$users, '+', 'Happy customers'],
        [5,      '',  'Categories'],
        [24,     '/7','Customer support'],
    ];
    foreach ($rows as $r):
    ?>
    <div class="stat-cell reveal">
        <div style="font-size:2.2rem;font-weight:900;color:#fff;letter-spacing:-1.5px;line-height:1;">
            <span class="counter" data-target="<?= $r[0] ?>">0</span><?= $r[1] ?>
        </div>
        <div style="font-size:0.78rem;color:rgba(255,255,255,0.38);margin-top:8px;font-weight:500;letter-spacing:0.01em;">
            <?= $r[2] ?>
        </div>
    </div>
    <?php endforeach; ?>
</section>

<!-- ═══ DEAL OF THE WEEK ══════════════════════════════════ -->
<?php
$deal = $db->query("SELECT * FROM products ORDER BY price DESC LIMIT 1")->fetch();
$dealSrc = $deal ? productImg($deal['image']) : null;
if ($deal):
?>
<section style="background:#fff;padding:64px 0;">
<div style="max-width:1200px;margin:0 auto;padding:0 40px;">

    <div class="sec-row reveal">
        <h2 class="sec-title">Deal of the Week</h2>
        <span style="background:#111827;color:#fff;font-size:0.68rem;font-weight:700;
                     padding:5px 14px;border-radius:99px;letter-spacing:0.05em;text-transform:uppercase;">
            Limited stock
        </span>
    </div>

    <div class="deal reveal">
        <div class="deal-img-col">
            <?php if ($dealSrc): ?>
                <img src="<?= $dealSrc ?>" alt="<?= htmlspecialchars($deal['name']) ?>" loading="lazy">
            <?php else: ?>
                <div style="width:100%;height:100%;background:linear-gradient(135deg,#1e293b,#0f172a);
                            display:flex;align-items:center;justify-content:center;font-size:5rem;opacity:0.3;">📦</div>
            <?php endif; ?>
        </div>
        <div class="deal-body">
            <span style="font-size:0.68rem;font-weight:700;color:rgba(255,255,255,0.35);
                         text-transform:uppercase;letter-spacing:0.1em;">
                <?= htmlspecialchars($deal['category']) ?>
            </span>
            <h3 style="font-size:1.8rem;font-weight:900;color:#fff;line-height:1.1;letter-spacing:-1px;">
                <?= htmlspecialchars($deal['name']) ?>
            </h3>
            <p style="font-size:0.875rem;color:rgba(255,255,255,0.42);line-height:1.7;max-width:360px;">
                <?= htmlspecialchars($deal['description']) ?>
            </p>
            <p style="font-size:2rem;font-weight:900;color:#fff;letter-spacing:-1px;">
                <?= ksh($deal['price']) ?>
            </p>
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                <form method="POST" action="/techhive/cart.php">
                    <input type="hidden" name="action"     value="add">
                    <input type="hidden" name="product_id" value="<?= $deal['id'] ?>">
                    <button type="submit" class="btn-primary"
                            style="background:#fff;color:#111827;padding:13px 28px;">
                        Add to Cart
                    </button>
                </form>
                <a href="/techhive/products/index.php"
                   style="padding:13px 28px;font-size:0.9rem;font-weight:600;color:rgba(255,255,255,0.45);
                          text-decoration:none;border:1.5px solid rgba(255,255,255,0.12);border-radius:8px;
                          transition:all 0.15s;"
                   onmouseover="this.style.borderColor='rgba(255,255,255,0.35)';this.style.color='#fff'"
                   onmouseout="this.style.borderColor='rgba(255,255,255,0.12)';this.style.color='rgba(255,255,255,0.45)'">
                    Browse all →
                </a>
            </div>
        </div>
    </div>

</div>
</section>
<?php endif; ?>

<!-- ═══ TRUST BAR ══════════════════════════════════════ -->
<section style="background:#f9fafb;border-top:1px solid #e5e7eb;padding:0;">
<div style="max-width:1200px;margin:0 auto;padding:0 40px;">
    <div class="trust reveal">
        <?php foreach ([
            ['🚚', 'Free Delivery',   'On orders over KSh 5,000'],
            ['🔒', 'Secure Payment',  'M-Pesa, card & bank transfer'],
            ['↩️',  '7-Day Returns',  'Hassle-free return policy'],
        ] as [$icon, $title, $sub]): ?>
        <div class="trust-item">
            <span style="font-size:1.8rem;flex-shrink:0;"><?= $icon ?></span>
            <div>
                <p style="font-size:0.875rem;font-weight:700;color:#111827;margin-bottom:2px;"><?= $title ?></p>
                <p style="font-size:0.78rem;color:#6b7280;"><?= $sub ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
// Scroll reveal
const rObs = new IntersectionObserver(es => es.forEach(e => { if(e.isIntersecting){e.target.classList.add('visible');rObs.unobserve(e.target);}}), {threshold:0.08});
document.querySelectorAll('.reveal').forEach(el => rObs.observe(el));

// Counter
const cObs = new IntersectionObserver(es => es.forEach(e => {
    if(!e.isIntersecting) return;
    const el=e.target, target=parseInt(el.dataset.target), isLarge=target>50;
    let n=0, inc=Math.max(1,Math.ceil(target/50));
    const t=()=>{n=Math.min(n+inc,target);el.textContent=n.toLocaleString();if(n<target)requestAnimationFrame(t);};
    requestAnimationFrame(t); cObs.unobserve(el);
}),{threshold:0.5});
document.querySelectorAll('.counter').forEach(el => cObs.observe(el));

// Cart badge
fetch('/techhive/cart_count.php').then(r=>r.json()).then(d=>{
    if(d.count>0){const b=document.getElementById('cart-count');if(b){b.textContent=d.count;b.style.display='inline-flex';}}
}).catch(()=>{});

// ── AJAX Add-to-Cart with animations ──────────────────────────
(function () {
    const SPIN  = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M12 2a10 10 0 0 1 10 10"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur=".7s" repeatCount="indefinite"/></path></svg>`;
    const CHECK = `<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>`;

    document.addEventListener('click', async function (e) {
        const btn = e.target.closest('[data-cart-id]');
        if (!btn || btn.classList.contains('loading')) return;

        ripple(btn, e);
        burst(btn);

        const id       = btn.dataset.cartId;
        const origHTML = btn.innerHTML;

        btn.classList.add('loading');
        btn.innerHTML = SPIN;

        try {
            const res = await fetch('/techhive/cart.php', {
                method : 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body   : `action=add&product_id=${id}`
            });
            if (!res.ok) throw new Error();

            btn.classList.remove('loading');
            btn.classList.add('success');
            btn.innerHTML = CHECK;

            flyArc(btn);
            updateBadge();
            showToast('Added to cart!');

            setTimeout(() => { btn.classList.remove('success'); btn.innerHTML = origHTML; }, 1800);
        } catch (_) {
            btn.classList.remove('loading');
            btn.innerHTML = origHTML;
            showToast('Could not add — try again.', true);
        }
    });

    function ripple(btn, e) {
        const rect = btn.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height) * 2.5;
        const r    = document.createElement('span');
        Object.assign(r.style, {
            position:'absolute', borderRadius:'50%',
            width:size+'px', height:size+'px',
            left: (e.clientX - rect.left) - size/2 + 'px',
            top:  (e.clientY - rect.top)  - size/2 + 'px',
            background:'rgba(255,255,255,0.28)',
            transform:'scale(0)',
            animation:'th-ripple 0.55s ease-out forwards',
            pointerEvents:'none', zIndex:'10',
        });
        if (getComputedStyle(btn).position === 'static') btn.style.position = 'relative';
        btn.style.overflow = 'hidden';
        btn.appendChild(r);
        setTimeout(() => r.remove(), 600);
    }

    function burst(source) {
        const rect   = source.getBoundingClientRect();
        const cx     = rect.left + rect.width  / 2;
        const cy     = rect.top  + rect.height / 2;
        const colors = ['#111827','#374151','#1f2937','#4b5563','#6b7280','#9ca3af'];
        for (let i = 0; i < 8; i++) {
            const angle = (i / 8) * Math.PI * 2 - Math.PI / 2;
            const dist  = 32 + Math.random() * 28;
            const sz    = 4  + Math.random() * 4;
            const p     = document.createElement('div');
            Object.assign(p.style, {
                position:'fixed', borderRadius:'50%',
                width:sz+'px', height:sz+'px',
                background: colors[i % colors.length],
                left: cx - sz/2 + 'px', top: cy - sz/2 + 'px',
                zIndex:'9998', pointerEvents:'none',
                transition:'transform 0.48s cubic-bezier(0.1,0.8,0.2,1), opacity 0.48s',
            });
            document.body.appendChild(p);
            p.getBoundingClientRect();
            p.style.transform = `translate(${Math.cos(angle)*dist}px,${Math.sin(angle)*dist}px) scale(0.15)`;
            p.style.opacity   = '0';
            setTimeout(() => p.remove(), 520);
        }
    }

    function flyArc(source) {
        const dest = document.querySelector('.cart-count, #cart-count');
        if (!dest) return;
        const s  = source.getBoundingClientRect();
        const d  = dest.getBoundingClientRect();
        const x0 = s.left + s.width/2,  y0 = s.top + s.height/2;
        const x1 = d.left + d.width/2,  y1 = d.top + d.height/2;
        const cpX = (x0+x1)/2 + (Math.random()-0.5)*80;
        const cpY = Math.min(y0,y1) - 110;

        const dot = document.createElement('div');
        Object.assign(dot.style, {
            position:'fixed', width:'12px', height:'12px',
            background:'#111827', borderRadius:'50%',
            left:x0-6+'px', top:y0-6+'px',
            zIndex:'9999', pointerEvents:'none',
            boxShadow:'0 2px 12px rgba(0,0,0,0.4)',
        });
        document.body.appendChild(dot);

        const dur = 570; let t0 = null;
        function step(ts) {
            if (!t0) t0 = ts;
            const raw = Math.min((ts-t0)/dur,1);
            const e   = raw<0.5 ? 2*raw*raw : -1+(4-2*raw)*raw;
            const x   = (1-e)*(1-e)*x0 + 2*(1-e)*e*cpX + e*e*x1;
            const y   = (1-e)*(1-e)*y0 + 2*(1-e)*e*cpY + e*e*y1;
            dot.style.left      = x-6+'px';
            dot.style.top       = y-6+'px';
            dot.style.opacity   = raw>0.72 ? String(1-(raw-0.72)/0.28) : '1';
            dot.style.transform = `scale(${1-raw*0.65})`;
            raw < 1 ? requestAnimationFrame(step) : (dot.remove(), shakeCart());
        }
        requestAnimationFrame(step);
    }

    function shakeCart() {
        const el = document.querySelector('a[href*="cart"]');
        if (!el) return;
        el.classList.remove('cart-land');
        void el.offsetWidth;
        el.classList.add('cart-land');
        setTimeout(() => el.classList.remove('cart-land'), 620);
    }

    async function updateBadge() {
        try {
            const data  = await (await fetch('/techhive/cart_count.php')).json();
            const badge = document.querySelector('.cart-count, #cart-count');
            if (!badge) return;
            badge.textContent = data.count;
            badge.style.display = 'inline-flex';
            badge.classList.remove('badge-pop');
            void badge.offsetWidth;
            badge.classList.add('badge-pop');
        } catch (_) {}
    }

    function showToast(msg, err = false) {
        document.querySelectorAll('.th-toast').forEach(t => t.remove());
        const icon = err
            ? `<svg width="16" height="16" fill="none" stroke="#f87171" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>`
            : `<svg width="16" height="16" fill="none" stroke="#4ade80" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>`;
        const el = document.createElement('div');
        el.className = 'th-toast';
        el.innerHTML = `${icon}<span>${msg}</span>`;
        document.body.appendChild(el);
        requestAnimationFrame(() => requestAnimationFrame(() => el.classList.add('in')));
        setTimeout(() => { el.classList.remove('in'); setTimeout(() => el.remove(), 350); }, 2400);
    }
})();
</script>

</body>
</html>
