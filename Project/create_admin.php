<?php
require_once 'config.php';

$db       = getDB();
$email    = 'admin@techhive.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);

// Delete existing admin if any, then re-insert fresh
$db->prepare("DELETE FROM users WHERE email = ?")->execute([$email]);
$db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')")
   ->execute(['Admin', $email, $password]);

echo "Admin account created.<br>Email: admin@techhive.com<br>Password: admin123";
?>
