<?php
require_once 'config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}

$token = trim($_GET['token'] ?? '');
if (empty($token)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing token']);
    exit;
}

$db   = getDB();
$stmt = $db->prepare("SELECT status FROM payment_sessions WHERE token = ? AND user_id = ?");
$stmt->execute([$token, $_SESSION['user_id']]);
$row  = $stmt->fetch();

if (!$row) {
    echo json_encode(['status' => 'expired']);
    exit;
}

// Auto-expire after 90 seconds
$db->prepare("UPDATE payment_sessions SET status='expired' WHERE token=? AND status='pending' AND created_at < NOW() - INTERVAL 90 SECOND")
   ->execute([$token]);

if ($row['status'] === 'pending') {
    $db->prepare("SELECT status FROM payment_sessions WHERE token=?")->execute([$token]);
    // Re-check after possible expiry update
    $recheck = $db->prepare("SELECT status FROM payment_sessions WHERE token = ?");
    $recheck->execute([$token]);
    $row = $recheck->fetch();
}

echo json_encode(['status' => $row['status'] ?? 'expired']);
