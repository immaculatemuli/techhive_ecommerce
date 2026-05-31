<?php
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username'] ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dynamic Welcome | TechHive</title>
    <link rel="stylesheet" href="/techhive/Project/css/style.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f1f5f9;
            padding: 24px;
            margin: 0;
        }

        .wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
            width: 100%;
            max-width: 860px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 24px 80px rgba(0,0,0,0.12);
        }

        /* Left — form */
        .panel-form {
            background: #fff;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .panel-form h2 {
            font-size: 1.35rem;
            font-weight: 800;
            color: #111827;
            margin-bottom: 6px;
        }
        .panel-form p {
            color: #6b7280;
            font-size: 0.85rem;
            margin-bottom: 28px;
        }

        /* Right — response */
        .panel-response {
            background: #0f172a;
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: flex-start;
        }
        .panel-response .label-tag {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: rgba(255,255,255,0.25);
            margin-bottom: 20px;
        }
        .panel-response h1 {
            font-size: 1.6rem;
            font-weight: 900;
            color: #fff;
            line-height: 1.25;
            letter-spacing: -0.6px;
            margin-bottom: 14px;
        }
        .panel-response h1 span {
            color: #60a5fa;
        }
        .panel-response p {
            color: rgba(255,255,255,0.4);
            font-size: 0.85rem;
            line-height: 1.7;
        }
        .panel-response code {
            font-size: 0.78rem;
            color: #93c5fd;
            background: rgba(255,255,255,0.07);
            padding: 2px 7px;
            border-radius: 4px;
        }
        .placeholder {
            color: rgba(255,255,255,0.12);
            font-size: 1.1rem;
            font-weight: 700;
            font-style: italic;
        }

        @media (max-width: 600px) {
            .wrapper { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<div class="wrapper">

    <!-- LEFT: Form -->
    <div class="panel-form">
        <h2>Dynamic Welcome</h2>
        <p>Type a name and PHP will generate a greeting on the right.</p>

        <form method="POST" action="dynamic_input.php">
            <div style="margin-bottom:16px;">
                <label for="username" class="label">Your name</label>
                <input type="text" id="username" name="username"
                       class="field" placeholder="e.g. Alice"
                       value="<?= $username ?>" autofocus>
            </div>
            <button type="submit" class="btn-primary" style="width:100%;padding:11px;font-size:0.9rem;">
                Send to server
            </button>
        </form>
    </div>

    <!-- RIGHT: PHP response -->
    <div class="panel-response">
       

        <?php if ($username): ?>
            <h1>Hello,<br><span><?= $username ?></span>!</h1>
            
        <?php else: ?>
            <p class="placeholder">Your welcome message<br>will appear here…</p>
            <p style="margin-top:16px;">Submit the form and PHP will respond with a dynamic greeting.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
