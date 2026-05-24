<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name        = trim(htmlspecialchars($_POST['name']        ?? ''));
    $description = trim(htmlspecialchars($_POST['description'] ?? ''));
    $price       = $_POST['price']    ?? '';
    $category    = trim(htmlspecialchars($_POST['category']    ?? ''));
    $stock       = $_POST['stock']    ?? '';
    $imageFile   = '';

    // Validation
    if (empty($name) || empty($price) || empty($category) || $stock === '') {
        $error = 'Name, price, category and stock are required.';
    } elseif (!is_numeric($price) || $price < 0) {
        $error = 'Price must be a valid number.';
    } elseif (!is_numeric($stock) || $stock < 0) {
        $error = 'Stock must be a valid number.';
    } else {

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $allowed    = ['jpg','jpeg','png','webp'];
            $ext        = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $uploadDir  = '../images/';

            if (!in_array($ext, $allowed)) {
                $error = 'Only JPG, PNG, and WEBP images are allowed.';
            } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                $error = 'Image must be under 2MB.';
            } else {
                $imageFile = uniqid('product_') . '.' . $ext;
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageFile)) {
                    $error = 'Image upload failed. Check folder permissions.';
                    $imageFile = '';
                }
            }
        }

        if (!$error) {
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO products (name, description, price, category, stock, image) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $category, $stock, $imageFile]);
            header('Location: index.php?added=1');
            exit;
        }
    }
}
?>
<?php include '../includes/header.php'; ?>

<div style="max-width:680px;margin:0 auto;padding:48px 24px;">

    <div style="margin-bottom:28px;">
        <a href="index.php" style="color:var(--muted);font-size:0.82rem;text-decoration:none;">← Back to products</a>
        <h1 style="font-size:1.8rem;font-weight:800;color:var(--text);margin-top:8px;">Add Product</h1>
    </div>

    <?php if ($error): ?>
        <div class="alert-error" style="margin-bottom:20px;"><?= $error ?></div>
    <?php endif; ?>

    <div class="card" style="padding:32px;">
        <form method="POST" enctype="multipart/form-data">

            <!-- Name -->
            <div style="margin-bottom:16px;">
                <label class="label">Product Name</label>
                <input type="text" name="name" class="field"
                    placeholder="e.g. Dell XPS 15"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
            </div>

            <!-- Description -->
            <div style="margin-bottom:16px;">
                <label class="label">Description</label>
                <textarea name="description" class="field" rows="3"
                    placeholder="Short product description..."
                    style="resize:vertical;"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <!-- Price + Stock side by side -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
                <div>
                    <label class="label">Price (KSh)</label>
                    <input type="number" name="price" class="field"
                        placeholder="0.00" step="0.01" min="0"
                        value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
                </div>
                <div>
                    <label class="label">Stock</label>
                    <input type="number" name="stock" class="field"
                        placeholder="0" min="0"
                        value="<?= htmlspecialchars($_POST['stock'] ?? '') ?>" required>
                </div>
            </div>

            <!-- Category -->
            <div style="margin-bottom:16px;">
                <label class="label">Category</label>
                <select name="category" class="field">
                    <option value="">Select category...</option>
                    <?php foreach (['Laptops','Phones','Accessories','Tablets','Gaming'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($_POST['category'] ?? '') === $cat ? 'selected' : '' ?>>
                            <?= $cat ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Image upload -->
            <div style="margin-bottom:28px;">
                <label class="label">Product Image</label>
                <input type="file" name="image" accept="image/*" class="field"
                    style="padding:8px 12px;cursor:pointer;">
                <p style="font-size:0.75rem;color:var(--muted);margin-top:4px;">JPG, PNG or WEBP — max 2MB</p>
            </div>

            <div style="display:flex;gap:12px;">
                <button type="submit" class="btn-primary" style="flex:1;">Add Product</button>
                <a href="index.php" style="flex:1;text-align:center;padding:11px;background:var(--subtle);border:1px solid var(--border);border-radius:8px;color:var(--muted);text-decoration:none;font-size:0.9rem;font-weight:600;">Cancel</a>
            </div>

        </form>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
