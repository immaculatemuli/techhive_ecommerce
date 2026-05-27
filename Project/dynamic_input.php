<?php
session_start();
$name = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome — TechHive</title>
    <link rel="stylesheet" href="/techhive/css/style.css">
    <style>
        body::before { display: none; }
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; }
    </style>
</head>
<body>

<div style="text-align:center;">
   
    <h1 style="font-size:2.2rem;font-weight:800;color:#818cf8;">
        Hello, Welcome <span style="color:#818cf8;"><?= $name ?>!</span>
    </h1>
    <p style="color:#52525b;margin-top:10px;font-size:0.9rem;">You are logged in to TechHive.</p>
    <a href="logout.php" style="display:inline-block;margin-top:28px;color:#52525b;font-size:0.82rem;text-decoration:none;">Sign out</a>
</div>

</body>
</html>