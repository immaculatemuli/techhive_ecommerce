<?php
require_once 'config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}

$input    = json_decode(file_get_contents('php://input'), true) ?? [];
$endpoint = trim($input['endpoint'] ?? '');
$p256dh   = trim($input['keys']['p256dh'] ?? '');
$auth     = trim($input['keys']['auth']    ?? '');

if (empty($endpoint) || empty($p256dh) || empty($auth)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing subscription fields']);
    exit;
}

$db = getDB();
$db->prepare("
    INSERT INTO push_subscriptions (user_id, endpoint, p256dh, auth)
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE p256dh = VALUES(p256dh), auth = VALUES(auth), created_at = NOW()
")->execute([$_SESSION['user_id'], $endpoint, $p256dh, $auth]);

echo json_encode(['ok' => true]);
