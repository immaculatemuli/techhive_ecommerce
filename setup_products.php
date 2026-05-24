<?php
// ── One-time setup script — visit http://localhost/techhive/setup_products.php
// Replaces all product records with real images + realistic KSh prices.
// DELETE this file after running it.
require_once 'config.php';
$db = getDB();

$confirmed = ($_GET['go'] ?? '') === 'yes';

if (!$confirmed): ?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Setup Products</title>
<style>body{font-family:system-ui;max-width:560px;margin:80px auto;padding:0 24px;color:#111}
.btn{background:#111827;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:700;font-size:0.95rem;display:inline-block;margin-top:20px;}
.warn{background:#fef2f2;border:1px solid #fecaca;padding:14px 16px;border-radius:8px;color:#991b1b;margin-bottom:16px;font-size:0.875rem;}</style>
</head><body>
<h1 style="font-size:1.6rem;font-weight:800;margin-bottom:8px;">TechHive — Product Setup</h1>
<p style="color:#6b7280;margin-bottom:20px;">This will replace all product records with 10 real products, Unsplash images, and realistic Kenyan prices.</p>
<div class="warn">⚠️ This will DELETE all existing products and insert fresh data. Only run this once.</div>
<a href="?go=yes" class="btn">Yes, set up products →</a>
</body></html>
<?php exit; endif;

// ── Clear and re-seed ─────────────────────────────────────────
$db->exec("SET FOREIGN_KEY_CHECKS = 0");
$db->exec("TRUNCATE TABLE order_items");
$db->exec("TRUNCATE TABLE cart");
$db->exec("TRUNCATE TABLE products");
$db->exec("SET FOREIGN_KEY_CHECKS = 1");

$products = [
    [
        'name'        => 'MacBook Air M2',
        'description' => '13.6" Liquid Retina display, Apple M2 chip, 8GB RAM, 256GB SSD. Fanless, ultra-thin design with all-day battery life. Perfect for students and creatives.',
        'price'       => 159000,
        'category'    => 'Laptops',
        'stock'       => 8,
        'image'       => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=700&q=80&fit=crop',
    ],
    [
        'name'        => 'Dell XPS 15',
        'description' => '15.6" 4K OLED touch, Intel Core i7-13700H, 16GB DDR5 RAM, 512GB SSD. The ultimate Windows ultrabook for power users and developers.',
        'price'       => 145000,
        'category'    => 'Laptops',
        'stock'       => 10,
        'image'       => 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=700&q=80&fit=crop',
    ],
    [
        'name'        => 'HP Pavilion 15',
        'description' => '15.6" FHD IPS, Intel Core i5-1235U, 8GB RAM, 256GB SSD, Windows 11. Great value everyday laptop — ideal for students and home use.',
        'price'       => 58000,
        'category'    => 'Laptops',
        'stock'       => 22,
        'image'       => 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=700&q=80&fit=crop',
    ],
    [
        'name'        => 'iPhone 15 Pro',
        'description' => '6.1" Super Retina XDR, A17 Pro chip, 128GB, 48MP triple camera, Titanium design. Apple\'s most advanced iPhone with Action Button.',
        'price'       => 175000,
        'category'    => 'Phones',
        'stock'       => 12,
        'image'       => 'https://images.unsplash.com/photo-1695048133142-1a20484d2569?w=700&q=80&fit=crop',
    ],
    [
        'name'        => 'Samsung Galaxy S24',
        'description' => '6.2" Dynamic AMOLED 2X, Snapdragon 8 Gen 3, 128GB, 50MP camera, 7 years of Android updates. Compact Android flagship with Galaxy AI.',
        'price'       => 89000,
        'category'    => 'Phones',
        'stock'       => 18,
        'image'       => 'https://images.unsplash.com/photo-1610945264803-c22b62d2a7b3?w=700&q=80&fit=crop',
    ],
    [
        'name'        => 'Sony WH-1000XM5',
        'description' => 'Industry-leading noise cancellation, 30-hour battery, 8 microphones for clear calls, multipoint connection. The world\'s best wireless headphones.',
        'price'       => 32000,
        'category'    => 'Accessories',
        'stock'       => 25,
        'image'       => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=700&q=80&fit=crop',
    ],
    [
        'name'        => 'Apple AirPods Pro 2',
        'description' => 'Active Noise Cancellation, Transparency mode, Adaptive Audio, Personalized Spatial Audio, MagSafe charging case. Premium wireless earbuds.',
        'price'       => 22000,
        'category'    => 'Accessories',
        'stock'       => 30,
        'image'       => 'https://images.unsplash.com/photo-1606220588913-b3aacb4d2f46?w=700&q=80&fit=crop',
    ],
    [
        'name'        => 'iPad Air M2',
        'description' => '11" Liquid Retina, Apple M2 chip, 64GB, Wi-Fi 6E, USB-C, supports Apple Pencil Pro and Magic Keyboard. Perfect for creativity on the go.',
        'price'       => 79000,
        'category'    => 'Tablets',
        'stock'       => 15,
        'image'       => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=700&q=80&fit=crop',
    ],
    [
        'name'        => 'Samsung Galaxy Tab S9',
        'description' => '11" Dynamic AMOLED 2X, Snapdragon 8 Gen 2, 128GB, IP68 waterproof, S Pen included. Android tablet at its absolute best.',
        'price'       => 88000,
        'category'    => 'Tablets',
        'stock'       => 10,
        'image'       => 'https://images.unsplash.com/photo-1561154464-82e9adf32764?w=700&q=80&fit=crop',
    ],
    [
        'name'        => 'Razer BlackWidow V4 Pro',
        'description' => 'Mechanical gaming keyboard, Razer Yellow linear switches, per-key RGB Chroma, wrist rest, wireless Bluetooth & 2.4GHz. Built for serious gamers.',
        'price'       => 13500,
        'category'    => 'Gaming',
        'stock'       => 20,
        'image'       => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=700&q=80&fit=crop',
    ],
];

$stmt = $db->prepare(
    "INSERT INTO products (name, description, price, category, stock, image) VALUES (?,?,?,?,?,?)"
);
$count = 0;
foreach ($products as $p) {
    $stmt->execute([$p['name'], $p['description'], $p['price'], $p['category'], $p['stock'], $p['image']]);
    $count++;
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Setup Done</title>
<style>body{font-family:system-ui;max-width:560px;margin:80px auto;padding:0 24px;color:#111}
.ok{background:#f0fdf4;border:1px solid #bbf7d0;padding:14px 16px;border-radius:8px;color:#14532d;margin-bottom:20px;}
.btn{background:#111827;color:#fff;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:700;font-size:0.95rem;display:inline-block;margin-right:10px;}</style>
</head><body>
<h1 style="font-size:1.6rem;font-weight:800;margin-bottom:12px;">✓ Setup complete</h1>
<div class="ok"><?= $count ?> products inserted with Unsplash images and realistic KSh prices.</div>
<p style="color:#6b7280;margin-bottom:20px;font-size:0.875rem;">
    ⚠️ Delete <strong>setup_products.php</strong> from your project now — it's no longer needed.
</p>
<a href="/techhive/index.php" class="btn">View Homepage</a>
<a href="/techhive/products/index.php" class="btn" style="background:#fff;color:#111827;border:1px solid #e5e7eb;">View Products</a>
</body></html>
