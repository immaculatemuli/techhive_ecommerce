<?php
require_once 'config.php';
session_start();

$success = false;
$error   = '';
$fields  = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Sanitize all inputs
    $fields = [
        'name'    => trim(htmlspecialchars($_POST['name']    ?? '')),
        'email'   => trim(htmlspecialchars($_POST['email']   ?? '')),
        'subject' => trim(htmlspecialchars($_POST['subject'] ?? '')),
        'message' => trim(htmlspecialchars($_POST['message'] ?? '')),
    ];

    // Server-side validation
    if (empty($fields['name']) || empty($fields['email']) || empty($fields['subject']) || empty($fields['message'])) {
        $error = 'All fields are required.';
    } elseif (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($fields['message']) < 10) {
        $error = 'Message must be at least 10 characters.';
    } else {
        // In a real project this would be saved to DB or emailed
        // For Week 4 demonstration we just show the success state
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact — TechHive</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/techhive/css/style.css">
    <style>
        body::before { display: none; }
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 40px 24px; }
    </style>
</head>
<body>

<div style="width:100%;max-width:480px;">

    <!-- Brand -->
    <div style="text-align:center;margin-bottom:28px;">
        <a href="index.php" class="brand" style="font-size:1.5rem;">Tech<span>Hive</span></a>
        <p style="color:#52525b;font-size:0.875rem;margin-top:6px;">Get in touch with us</p>
    </div>

    <div class="card" style="padding:32px;">

        <?php if ($success): ?>

            <!-- Success state — shown after valid submission -->
            <div style="text-align:center;padding:16px 0;">
                <div style="font-size:2.8rem;margin-bottom:16px;">✅</div>
                <h2 style="font-size:1.4rem;font-weight:800;color:#f4f4f5;margin-bottom:8px;">Message Sent!</h2>
                <p style="color:#71717a;font-size:0.875rem;margin-bottom:4px;">
                    Thanks, <span style="color:#818cf8;font-weight:600;"><?= $fields['name'] ?></span>. We received your message.
                </p>
                <p style="color:#52525b;font-size:0.82rem;">We'll reply to <strong style="color:#a1a1aa;"><?= $fields['email'] ?></strong> shortly.</p>

                <!-- Show submitted data — demonstrates PHP reading POST data -->
                <div style="margin-top:24px;text-align:left;background:#0d0d12;border:1px solid #1e1e26;border-radius:8px;padding:16px;">
                    <p style="font-size:0.75rem;font-weight:600;color:#52525b;text-transform:uppercase;letter-spacing:0.06em;margin-bottom:10px;">Submitted Data</p>
                    <p style="font-size:0.82rem;color:#a1a1aa;margin-bottom:4px;"><span style="color:#52525b;">Name:</span> <?= $fields['name'] ?></p>
                    <p style="font-size:0.82rem;color:#a1a1aa;margin-bottom:4px;"><span style="color:#52525b;">Email:</span> <?= $fields['email'] ?></p>
                    <p style="font-size:0.82rem;color:#a1a1aa;margin-bottom:4px;"><span style="color:#52525b;">Subject:</span> <?= $fields['subject'] ?></p>
                    <p style="font-size:0.82rem;color:#a1a1aa;"><span style="color:#52525b;">Message:</span> <?= $fields['message'] ?></p>
                </div>

                <a href="contact.php" style="display:inline-block;margin-top:20px;color:#818cf8;font-size:0.85rem;text-decoration:none;font-weight:500;">Send another message</a>
            </div>

        <?php else: ?>

            <?php if ($error): ?>
                <div class="alert-error"><?= $error ?></div>
            <?php endif; ?>

            <!-- Contact form -->
            <form id="contact-form" method="POST" novalidate>

                <!-- Two columns: name + email -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label class="label">Full Name</label>
                        <input type="text" name="name" class="field"
                            placeholder="John Doe"
                            value="<?= htmlspecialchars($fields['name'] ?? '') ?>">
                        <p id="name-error" class="field-error"></p>
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input type="email" name="email" id="email" class="field"
                            placeholder="you@example.com"
                            value="<?= htmlspecialchars($fields['email'] ?? '') ?>">
                        <p id="email-error" class="field-error"></p>
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label class="label">Subject</label>
                    <input type="text" name="subject" class="field"
                        placeholder="How can we help?"
                        value="<?= htmlspecialchars($fields['subject'] ?? '') ?>">
                    <p id="subject-error" class="field-error"></p>
                </div>

                <div style="margin-bottom:24px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <label class="label" style="margin-bottom:0;">Message</label>
                        <span id="char-count" style="font-size:0.75rem;color:#3f3f46;">0 / 500</span>
                    </div>
                    <textarea name="message" id="message" class="field"
                        rows="4" maxlength="500"
                        placeholder="Write your message here..."
                        style="resize:vertical;min-height:100px;"><?= htmlspecialchars($fields['message'] ?? '') ?></textarea>
                    <p id="message-error" class="field-error"></p>
                </div>

                <button type="submit" class="btn-primary">Send Message</button>

            </form>

        <?php endif; ?>

    </div>

    <p style="text-align:center;margin-top:18px;font-size:0.82rem;color:#52525b;">
        <a href="index.php" style="color:#52525b;text-decoration:none;">← Back to home</a>
    </p>

</div>

<script>
    // Live character counter for message textarea (Dynamic User Input Handling)
    const textarea  = document.getElementById('message');
    const charCount = document.getElementById('char-count');

    if (textarea && charCount) {
        textarea.addEventListener('input', () => {
            const len = textarea.value.length;
            charCount.textContent = len + ' / 500';
            charCount.style.color = len > 450 ? '#f87171' : '#3f3f46';
        });
    }

    // Contact form validation
    const form = document.getElementById('contact-form');
    if (form) {
        form.addEventListener('submit', e => {
            let ok = true;

            const fields = [
                { name: 'name',    errorId: 'name-error',    msg: 'Name is required.' },
                { name: 'subject', errorId: 'subject-error', msg: 'Subject is required.' },
                { name: 'message', errorId: 'message-error', msg: 'Message is required.' },
            ];

            fields.forEach(f => {
                const el = form.querySelector(`[name="${f.name}"]`);
                const er = document.getElementById(f.errorId);
                if (er) { er.textContent = ''; er.style.display = 'none'; }
                if (el && !el.value.trim()) {
                    if (er) { er.textContent = f.msg; er.style.display = 'block'; }
                    ok = false;
                }
            });

            // Email check
            const email = document.getElementById('email');
            const emailErr = document.getElementById('email-error');
            if (emailErr) { emailErr.textContent = ''; emailErr.style.display = 'none'; }
            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
                if (emailErr) { emailErr.textContent = 'Enter a valid email.'; emailErr.style.display = 'block'; }
                ok = false;
            }

            if (!ok) e.preventDefault();
        });
    }
</script>

</body>
</html>
