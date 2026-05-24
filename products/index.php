<?php
require_once '../config.php';
session_start();

$db         = getDB();
$category   = trim($_GET['category'] ?? '');
$search     = trim($_GET['q'] ?? '');
$categories = $db->query("SELECT DISTINCT category FROM products ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);

if ($category && $search) {
    $stmt = $db->prepare("SELECT * FROM products WHERE category = ? AND name LIKE ? ORDER BY created_at DESC");
    $stmt->execute([$category, "%$search%"]);
} elseif ($category) {
    $stmt = $db->prepare("SELECT * FROM products WHERE category = ? ORDER BY created_at DESC");
    $stmt->execute([$category]);
} elseif ($search) {
    $stmt = $db->prepare("SELECT * FROM products WHERE name LIKE ? OR description LIKE ? ORDER BY created_at DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $db->query("SELECT * FROM products ORDER BY created_at DESC");
}
$products = $stmt->fetchAll();
?>
<?php include '../includes/header.php'; ?>

<style>
/* ── Hero ── */
.products-hero {
    position: relative;
    background-image: url('https://images.unsplash.com/photo-1531297484001-80022131f5a1?w=1600&q=80&fit=crop');
    background-size: cover;
    background-position: center;
    background-attachment: scroll;
    padding: 72px 40px 80px;
}
.products-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(135deg, rgba(0,0,0,0.88) 0%, rgba(0,0,0,0.70) 100%);
}
.hero-inner {
    position: relative; z-index: 1;
    max-width: 680px; margin: 0 auto;
    text-align: center;
}


/* ── Cart icon button (card footer) ── */
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

/* ── Cart badge bounce ── */
@keyframes badge-pop {
    0%   { transform: scale(1); }
    40%  { transform: scale(1.5); }
    70%  { transform: scale(0.88); }
    100% { transform: scale(1); }
}
.badge-pop { animation: badge-pop 0.4s cubic-bezier(0.34,1.56,0.64,1); }

/* ── Button ripple ── */
@keyframes th-ripple { to { transform: scale(1); opacity: 0; } }

/* ── Cart land shake ── */
@keyframes cart-land {
    0%   { transform: rotate(0)    scale(1);    }
    20%  { transform: rotate(-14deg) scale(1.22); }
    45%  { transform: rotate(9deg)  scale(1.14); }
    65%  { transform: rotate(-5deg) scale(1.07); }
    82%  { transform: rotate(2deg)  scale(1.03); }
    100% { transform: rotate(0)    scale(1);    }
}
.cart-land { animation: cart-land 0.58s cubic-bezier(0.34,1.56,0.64,1); }

/* ── Search ── */
.prod-search {
    width: 100%; max-width: 480px;
    padding: 13px 52px 13px 18px;
    border-radius: 10px;
    border: 1.5px solid rgba(255,255,255,0.14);
    background: rgba(255,255,255,0.07);
    backdrop-filter: blur(8px);
    color: #fff;
    font-family: inherit;
    font-size: 0.95rem;
    outline: none;
    transition: border-color 0.2s, background 0.2s;
}
.prod-search::placeholder { color: rgba(255,255,255,0.32); }
.prod-search:focus { border-color: rgba(255,255,255,0.45); background: rgba(255,255,255,0.1); }

/* ── Pills ── */
.pill { display:inline-block;padding:8px 18px;border-radius:99px;font-size:0.8rem;font-weight:600;
        text-decoration:none;border:1.5px solid;transition:all 0.15s; }
.pill-on  { background:#fff; color:#111827; border-color:#fff; }
.pill-off { background:transparent; color:rgba(255,255,255,0.5); border-color:rgba(255,255,255,0.18); }
.pill-off:hover { border-color:rgba(255,255,255,0.45); color:rgba(255,255,255,0.9); }

/* ── Product card ── */
.p-card {
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: box-shadow 0.28s ease, transform 0.28s ease;
}
.p-card:hover {
    box-shadow: 0 28px 60px rgba(0,0,0,0.13);
    transform: translateY(-6px);
}
.p-img-wrap {
    height: 210px; overflow: hidden;
    background: #f3f4f6; position: relative;
}
.p-img { width:100%;height:100%;object-fit:cover;transition:transform 0.45s ease; }
.p-card:hover .p-img { transform: scale(1.07); }
.p-body { padding:16px 18px 20px;flex:1;display:flex;flex-direction:column;gap:10px; }

@media (max-width: 820px) {
    .products-hero { padding: 52px 24px 60px; }
    .hero-inner { text-align: left; }
}
</style>

<!-- ═══ HERO ════════════════════════════════════════════ -->
<section class="products-hero">
    <div class="hero-inner">

        <p style="font-size:0.72rem;font-weight:700;letter-spacing:0.12em;text-transform:uppercase;
                   color:rgba(255,255,255,0.38);margin-bottom:12px;">
            TechHive Store
        </p>
        <h1 style="font-size:clamp(1.8rem,3.5vw,2.8rem);font-weight:900;color:#fff;
                    line-height:1.1;letter-spacing:-1.5px;margin-bottom:16px;">
            Find Your<br>Perfect Device.
        </h1>
        <p style="color:rgba(255,255,255,0.45);font-size:0.93rem;line-height:1.7;
                   max-width:420px;margin:0 auto 32px;">
            Genuine products, fast delivery, real warranty. M-Pesa accepted.
        </p>

        <!-- Search bar -->
        <form method="GET" action="index.php" style="position:relative;max-width:480px;margin:0 auto 24px;">
            <?php if ($category): ?>
                <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
            <?php endif; ?>
            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search products…" class="prod-search">
            <button type="submit" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);
                           background:none;border:none;cursor:pointer;padding:0;line-height:0;">
                <svg width="18" height="18" fill="none" stroke="rgba(255,255,255,0.4)" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"/>
                    <path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
            </button>
        </form>

        <!-- Category pills -->
        <div style="display:flex;flex-wrap:wrap;gap:8px;justify-content:center;">
            <a href="index.php<?= $search ? '?q='.urlencode($search) : '' ?>"
               class="pill <?= !$category ? 'pill-on' : 'pill-off' ?>">All</a>
            <?php foreach ($categories as $cat): ?>
                <a href="?category=<?= urlencode($cat) ?><?= $search ? '&q='.urlencode($search) : '' ?>"
                   class="pill <?= $category === $cat ? 'pill-on' : 'pill-off' ?>">
                    <?= htmlspecialchars($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ═══ PRODUCTS ══════════════════════════════════════ -->
<section style="background:#f9fafb;padding:52px 0 80px;">
<div style="max-width:1160px;margin:0 auto;padding:0 40px;">

    <!-- Header row -->
    <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:10px;">
        <div>
            <h2 style="font-size:1.4rem;font-weight:800;color:#111827;letter-spacing:-0.5px;">
                <?php if ($search && $category): ?>
                    "<?= htmlspecialchars($search) ?>" in <?= htmlspecialchars($category) ?>
                <?php elseif ($search): ?>
                    Results for "<?= htmlspecialchars($search) ?>"
                <?php elseif ($category): ?>
                    <?= htmlspecialchars($category) ?>
                <?php else: ?>
                    All Products
                <?php endif; ?>
            </h2>
            <p style="color:#6b7280;font-size:0.875rem;margin-top:4px;">
                <?= count($products) ?> item<?= count($products) !== 1 ? 's' : '' ?>
            </p>
        </div>
        <?php if ($category || $search): ?>
            <a href="index.php" style="font-size:0.82rem;color:#6b7280;text-decoration:none;font-weight:500;
                                       display:flex;align-items:center;gap:4px;">
                ← All products
            </a>
        <?php endif; ?>
    </div>

    <!-- Grid -->
    <?php if (empty($products)): ?>
        <div style="text-align:center;padding:80px 0;">
            <div style="font-size:3rem;margin-bottom:14px;opacity:0.3;">🔍</div>
            <h3 style="font-size:1.1rem;font-weight:700;color:#111827;margin-bottom:8px;">No products found</h3>
            <p style="color:#6b7280;margin-bottom:20px;">Try a different search or browse all products.</p>
            <a href="index.php" style="font-weight:700;color:#111827;text-decoration:none;border-bottom:2px solid #111827;padding-bottom:1px;">
                View all →
            </a>
        </div>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:20px;" id="product-grid">
            <?php foreach ($products as $p):
                $img = productImg($p['image']);
            ?>
            <div class="p-card product-card" data-name="<?= htmlspecialchars(strtolower($p['name'])) ?>">

                <!-- Image -->
                <div class="p-img-wrap">
                    <?php if ($img): ?>
                        <img class="p-img" src="<?= $img ?>"
                             alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
                    <?php else: ?>
                        <div style="width:100%;height:100%;
                                    background:linear-gradient(135deg,#1e293b,#334155);
                                    display:flex;align-items:center;justify-content:center;">
                            <span style="font-size:2.5rem;opacity:0.4;">📦</span>
                        </div>
                    <?php endif; ?>

                    <span style="position:absolute;top:12px;left:12px;
                                 background:rgba(0,0,0,0.68);color:#fff;backdrop-filter:blur(4px);
                                 font-size:0.62rem;font-weight:700;padding:3px 9px;
                                 border-radius:99px;text-transform:uppercase;letter-spacing:0.06em;">
                        <?= htmlspecialchars($p['category']) ?>
                    </span>

                    <?php if ($p['stock'] > 0 && $p['stock'] <= 3): ?>
                        <span style="position:absolute;top:12px;right:12px;background:#dc2626;
                                     color:#fff;font-size:0.62rem;font-weight:700;
                                     padding:3px 9px;border-radius:99px;">
                            <?= $p['stock'] ?> left
                        </span>
                    <?php endif; ?>

                </div>

                <!-- Body -->
                <div class="p-body">
                    <div style="flex:1;">
                        <h3 style="font-size:0.93rem;font-weight:700;color:#111827;
                                   line-height:1.4;margin-bottom:5px;">
                            <?= htmlspecialchars($p['name']) ?>
                        </h3>
                        <p style="font-size:0.77rem;color:#6b7280;line-height:1.55;
                                  display:-webkit-box;-webkit-line-clamp:2;
                                  -webkit-box-orient:vertical;overflow:hidden;">
                            <?= htmlspecialchars($p['description']) ?>
                        </p>
                    </div>

                    <div style="display:flex;align-items:center;justify-content:space-between;
                                padding-top:12px;border-top:1px solid #f3f4f6;gap:8px;">
                        <span style="font-size:1.08rem;font-weight:800;color:#111827;letter-spacing:-0.5px;">
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
    <?php endif; ?>

</div>
</section>

<?php include '../includes/footer.php'; ?>
<script src="/techhive/js/main.js"></script>
<script>
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

    // ── Ripple from click point ───────────────────────────────
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

    // ── 8-particle burst ─────────────────────────────────────
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

    // ── Bezier arc fly to cart ────────────────────────────────
    function flyArc(source) {
        const dest = document.querySelector('.cart-count, #cart-count');
        if (!dest) return;

        const s  = source.getBoundingClientRect();
        const d  = dest.getBoundingClientRect();
        const x0 = s.left + s.width  / 2,  y0 = s.top  + s.height / 2;
        const x1 = d.left + d.width  / 2,  y1 = d.top  + d.height / 2;
        const cpX = (x0 + x1) / 2 + (Math.random() - 0.5) * 80;
        const cpY = Math.min(y0, y1) - 110;

        const dot = document.createElement('div');
        Object.assign(dot.style, {
            position:'fixed', width:'12px', height:'12px',
            background:'#111827', borderRadius:'50%',
            left:x0-6+'px', top:y0-6+'px',
            zIndex:'9999', pointerEvents:'none',
            boxShadow:'0 2px 12px rgba(0,0,0,0.4)',
        });
        document.body.appendChild(dot);

        const dur = 570;
        let t0 = null;
        function step(ts) {
            if (!t0) t0 = ts;
            const raw = Math.min((ts - t0) / dur, 1);
            const e   = raw < 0.5 ? 2*raw*raw : -1+(4-2*raw)*raw; // ease-in-out
            const x   = (1-e)*(1-e)*x0 + 2*(1-e)*e*cpX + e*e*x1;
            const y   = (1-e)*(1-e)*y0 + 2*(1-e)*e*cpY + e*e*y1;
            dot.style.left      = x-6+'px';
            dot.style.top       = y-6+'px';
            dot.style.opacity   = raw > 0.72 ? String(1-(raw-0.72)/0.28) : '1';
            dot.style.transform = `scale(${1-raw*0.65})`;
            raw < 1 ? requestAnimationFrame(step) : (dot.remove(), shakeCart());
        }
        requestAnimationFrame(step);
    }

    // ── Cart icon shake on landing ────────────────────────────
    function shakeCart() {
        const el = document.querySelector('a[href*="cart"]');
        if (!el) return;
        el.classList.remove('cart-land');
        void el.offsetWidth;
        el.classList.add('cart-land');
        setTimeout(() => el.classList.remove('cart-land'), 620);
    }

    // ── Badge live update ─────────────────────────────────────
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

    // ── Toast ─────────────────────────────────────────────────
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
