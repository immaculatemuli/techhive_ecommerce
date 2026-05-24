<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

// Load existing product
$stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim(htmlspecialchars($_POST['name']        ?? ''));
    $description = trim(htmlspecialchars($_POST['description'] ?? ''));
    $price       = $_POST['price']    ?? '';
    $category    = trim(htmlspecialchars($_POST['category']    ?? ''));
    $stock       = $_POST['stock']    ?? '';
    $imageFile   = $product['image']; // keep existing image by default

    if (empty($name) || empty($price) || empty($category) || $stock === '') {
        $error = 'Name, price, category and stock are required.';
    } elseif (!is_numeric($price) || $price < 0) {
        $error = 'Price must be a valid number.';
    } elseif (!is_numeric($stock) || $stock < 0) {
        $error = 'Stock must be a valid number.';
    } else {

        // Handle new image upload if provided
        if (!empty($_FILES['image']['name'])) {
            $allowed   = ['jpg','jpeg','png','webp'];
            $ext       = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $uploadDir = '../images/';

            if (!in_array($ext, $allowed)) {
                $error = 'Only JPG, PNG, and WEBP images are allowed.';
            } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                $error = 'Image must be under 2MB.';
            } else {
                $newFile = uniqid('product_') . '.' . $ext;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $newFile)) {
                    // Delete old image if it exists
                    if ($imageFile && file_exists($uploadDir . $imageFile)) {
                        unlink($uploadDir . $imageFile);
                    }
                    $imageFile = $newFile;
                } else {
                    $error = 'Image upload failed. Check folder permissions.';
                }
            }
        }

        if (!$error) {
            $update = $db->prepare("UPDATE products SET name=?, description=?, price=?, category=?, stock=?, image=? WHERE id=?");
            $update->execute([$name, $description, $price, $category, $stock, $imageFile, $id]);
            header('Location: index.php?updated=1');
            exit;
        }
    }

    // Re-populate $product with submitted values on error
    $product = array_merge($product, [
        'name'        => $_POST['name']        ?? $product['name'],
        'description' => $_POST['description'] ?? $product['description'],
        'price'       => $_POST['price']       ?? $product['price'],
        'category'    => $_POST['category']    ?? $product['category'],
        'stock'       => $_POST['stock']       ?? $product['stock'],
    ]);
}
?>
<?php include '../includes/header.php'; ?>

<div style="max-width:680px;margin:0 auto;padding:48px 24px;">

    <div style="margin-bottom:28px;">
        <a href="index.php" style="color:var(--muted);font-size:0.82rem;text-decoration:none;">← Back to products</a>
        <h1 style="font-size:1.8rem;font-weight:800;color:var(--text);margin-top:8px;">Edit Product</h1>
        <p style="color:var(--muted);font-size:0.875rem;margin-top:2px;">ID #<?= $id ?></p>
    </div>

    <?php if ($error): ?>
        <div class="alert-error" style="margin-bottom:20px;"><?= $error ?></div>
    <?php endif; ?>

    <div class="card" style="padding:32px;">
        <form method="POST" enctype="multipart/form-data">

            <div style="margin-bottom:16px;">
                <label class="label">Product Name</label>
                <input type="text" name="name" class="field"
                    value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div style="margin-bottom:16px;">
                <label class="label">Description</label>
                <textarea name="description" class="field" rows="3"
                    style="resize:vertical;"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                <div>
                    <label class="label">Price (KSh)</label>
                    <input type="number" name="price" class="field"
                        step="0.01" min="0"
                        value="<?= htmlspecialchars($product['price']) ?>" required>
                </div>
                <div>
                    <label class="label">Stock</label>
                    <input type="number" name="stock" class="field"
                        min="0"
                        value="<?= htmlspecialchars($product['stock']) ?>" required>
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <label class="label">Category</label>
                <select name="category" class="field">
                    <?php foreach (['Laptops','Phones','Accessories','Tablets','Gaming'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= $product['category'] === $cat ? 'selected' : '' ?>>
                            <?= $cat ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Current image preview -->
            <div style="margin-bottom:16px;">
                <label class="label">Current Image</label>
                <?php if ($product['image'] && file_exists('../images/' . $product['image'])): ?>
                    <div style="margin-bottom:8px;">
                        <img src="/techhive/images/<?= htmlspecialchars($product['image']) ?>"
                             style="height:80px;border-radius:6px;border:1px solid var(--border);">
                    </div>
                <?php else: ?>
                    <p style="font-size:0.8rem;color:var(--muted);margin-bottom:8px;">No image uploaded.</p>
                <?php endif; ?>
                <input type="file" name="image" accept="image/*" class="field" style="padding:8px 12px;cursor:pointer;">
                <p style="font-size:0.75rem;color:var(--muted);margin-top:4px;">Leave blank to keep current image</p>
            </div>

            <div style="display:flex;gap:12px;margin-top:28px;">
                <button type="submit" class="btn-primary" style="flex:1;">Save Changes</button>
                <a href="index.php" style="flex:1;text-align:center;padding:11px;background:var(--subtle);border:1px solid var(--border);border-radius:8px;color:var(--muted);text-decoration:none;font-size:0.9rem;font-weight:600;">Cancel</a>
            </div>

        </form>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
