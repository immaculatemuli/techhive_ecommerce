<?php
require_once 'config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit;
}

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$amount = (float)($input['amount'] ?? 0);
$method = in_array($input['method'] ?? '', ['mpesa', 'card']) ? $input['method'] : 'mpesa';

if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid amount']);
    exit;
}

$db    = getDB();
$token = bin2hex(random_bytes(32));

$db->prepare("INSERT INTO payment_sessions (token, user_id, amount, method) VALUES (?,?,?,?)")
   ->execute([$token, $_SESSION['user_id'], $amount, $method]);

// Expire sessions older than 90 seconds (cleanup)
$db->prepare("UPDATE payment_sessions SET status='expired' WHERE user_id=? AND status='pending' AND created_at < NOW() - INTERVAL 90 SECOND AND token != ?")
   ->execute([$_SESSION['user_id'], $token]);

// Look up user's push subscriptions
$subs = $db->prepare("SELECT endpoint, p256dh, auth FROM push_subscriptions WHERE user_id = ?");
$subs->execute([$_SESSION['user_id']]);
$subscriptions = $subs->fetchAll();

if (empty($subscriptions)) {
    echo json_encode(['ok' => false, 'no_subscription' => true, 'token' => $token]);
    exit;
}

$amountText  = ksh($amount);
$methodLabel = $method === 'mpesa' ? 'M-Pesa' : 'Card';
$payload = [
    'title'  => $methodLabel . ' Payment Request',
    'body'   => 'Pay ' . $amountText . ' to TechHive? Tap to confirm.',
    'token'  => $token,
    'action' => 'confirm-payment',
];

$sent = 0;
foreach ($subscriptions as $sub) {
    $result = sendPushNotification(
        ['endpoint' => $sub['endpoint'], 'keys' => ['p256dh' => $sub['p256dh'], 'auth' => $sub['auth']]],
        $payload
    );
    if ($result['ok']) $sent++;
}

echo json_encode(['ok' => true, 'token' => $token, 'sent' => $sent]);
