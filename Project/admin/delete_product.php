<?php
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // Get image filename before deleting so we can remove the file too
    $stmt = $db->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if ($product) {
        // Delete the product from the database
        $db->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);

        // Remove the product image file from disk if it exists
        if ($product['image'] && file_exists('../images/' . $product['image'])) {
            unlink('../images/' . $product['image']);
        }
    }
}

header('Location: index.php?deleted=1');
exit;
