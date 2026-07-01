<?php
// Called from the service worker (notificationclick) via fetch.
// The SW includes cookies by default for same-origin requests so session works.
require_once 'config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$token  = trim($input['token'] ?? '');
$action = in_array($input['action'] ?? '', ['confirmed', 'cancelled']) ? $input['action'] : 'cancelled';

if (empty($token)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing token']);
    exit;
}

$db = getDB();
$db->prepare("UPDATE payment_sessions SET status = ? WHERE token = ? AND user_id = ? AND status = 'pending'")
   ->execute([$action, $token, $_SESSION['user_id']]);

echo json_encode(['ok' => true, 'status' => $action]);
