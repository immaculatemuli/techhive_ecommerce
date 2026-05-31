<?php
require_once 'config.php';
session_start();

$success = false;
$error   = '';
$fields  = [];

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['name'])) {

    // Sanitize all inputs
    $fields = [
        'name'    => trim(htmlspecialchars($_GET['name']    ?? '')),
        'email'   => trim(htmlspecialchars($_GET['email']   ?? '')),
        'subject' => trim(htmlspecialchars($_GET['subject'] ?? '')),
        'message' => trim(htmlspecialchars($_GET['message'] ?? '')),
    ];

    // Server-side validation
    if (empty($fields['name']) || empty($fields['email']) || empty($fields['subject']) || empty($fields['message'])) {
        $error = 'All fields are required.';
    } elseif (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($fields['message']) < 10) {
        $error = 'Message must be at least 10 characters.';
    } else {
        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | TechHive</title>
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

        .auth-card {
            display: flex;
            width: 100%;
            max-width: 1020px;
            min-height: 560px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 24px 80px rgba(0,0,0,0.13);
        }

        .auth-left {
            width: 44%;
            background: #0f172a;
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .auth-left h2 {
            font-size: 1.65rem;
            font-weight: 900;
            color: #fff;
            line-height: 1.2;
            letter-spacing: -0.8px;
            margin: 32px 0 12px;
        }
        .auth-left p {
            color: rgba(255,255,255,0.4);
            font-size: 0.9rem;
            line-height: 1.7;
            margin-bottom: 32px;
        }
        .contact-detail {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 16px;
        }
        .contact-detail-icon {
            width: 34px; height: 34px;
            background: rgba(255,255,255,0.07);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            font-size: 0.9rem;
        }
        .contact-detail-text strong {
            display: block;
            color: rgba(255,255,255,0.75);
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .contact-detail-text span {
            color: rgba(255,255,255,0.35);
            font-size: 0.8rem;
        }

        .auth-right {
            flex: 1;
            background: #fff;
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .char-count {
            font-size: 0.72rem;
            color: #9ca3af;
            font-weight: 500;
        }

        @media (max-width: 640px) {
            .auth-card { flex-direction: column; border-radius: 16px; }
            .auth-left  { width: 100%; padding: 36px 28px; }
            .auth-right { padding: 36px 28px; }
        }
    </style>
</head>
<body>

<div class="auth-card">

    <!-- ── Left panel ── -->
    <div class="auth-left">
        <a href="/techhive/Project/index.php"
           style="font-size:1.1rem;font-weight:900;color:#fff;text-decoration:none;letter-spacing:-0.4px;">
            TechHive
        </a>
        <h2>We'd love to<br>hear from you.</h2>
        <p>Questions, orders or feedback our team is ready to help.</p>

        <div class="contact-detail">
            <div class="contact-detail-icon">📍</div>
            <div class="contact-detail-text">
                <strong>Location</strong>
                <span>Nairobi, Kenya</span>
            </div>
        </div>
        <div class="contact-detail">
            <div class="contact-detail-icon">📧</div>
            <div class="contact-detail-text">
                <strong>Email</strong>
                <span>support@techhive.co.ke</span>
            </div>
        </div>
        <div class="contact-detail">
            <div class="contact-detail-icon">📞</div>
            <div class="contact-detail-text">
                <strong>Phone</strong>
                <span>+254 700 000 000</span>
            </div>
        </div>
        <div class="contact-detail">
            <div class="contact-detail-icon">🕐</div>
            <div class="contact-detail-text">
                <strong>Hours</strong>
                <span>Mon – Sat, 8 am – 6 pm</span>
            </div>
        </div>
    </div>

    <!-- ── Right panel ── -->
    <div class="auth-right">

        <?php if ($success): ?>

            <!-- Success state -->
            <div style="text-align:center;padding:12px 0;">
                <div style="width:56px;height:56px;background:#f0fdf4;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:1.5rem;">✓</div>
                <h2 style="font-size:1.4rem;font-weight:800;color:#111827;letter-spacing:-0.5px;margin-bottom:8px;">Message sent!</h2>
                <p style="color:#6b7280;font-size:0.875rem;margin-bottom:4px;">
                    Thanks, <strong style="color:#111827;"><?= $fields['name'] ?></strong>. We received your message.
                </p>
                <p style="color:#9ca3af;font-size:0.82rem;margin-bottom:28px;">
                    We'll reply to <strong style="color:#374151;"><?= $fields['email'] ?></strong> shortly.
                </p>

                <!-- Submitted data summary -->
                <div style="text-align:left;background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:16px 20px;margin-bottom:24px;">
                    <p style="font-size:0.72rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:10px;">Submitted via GET</p>
                    <p style="font-size:0.83rem;color:#374151;margin-bottom:5px;"><span style="color:#9ca3af;width:56px;display:inline-block;">Name</span><?= $fields['name'] ?></p>
                    <p style="font-size:0.83rem;color:#374151;margin-bottom:5px;"><span style="color:#9ca3af;width:56px;display:inline-block;">Email</span><?= $fields['email'] ?></p>
                    <p style="font-size:0.83rem;color:#374151;margin-bottom:5px;"><span style="color:#9ca3af;width:56px;display:inline-block;">Subject</span><?= $fields['subject'] ?></p>
                    <p style="font-size:0.83rem;color:#374151;"><span style="color:#9ca3af;width:56px;display:inline-block;">Message</span><?= $fields['message'] ?></p>
                </div>

                <a href="contact.php"
                   style="display:inline-block;background:#111827;color:#fff;font-size:0.85rem;font-weight:700;
                          padding:10px 24px;border-radius:8px;text-decoration:none;">
                    Send another message
                </a>
            </div>

        <?php else: ?>

            <div style="margin-bottom:24px;">
                <h1 style="font-size:1.5rem;font-weight:800;color:#111827;letter-spacing:-0.5px;margin-bottom:5px;">
                    Get in touch
                </h1>
                <p style="color:#6b7280;font-size:0.85rem;">Fill in the form and we'll get back to you.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert-error" style="margin-bottom:20px;"><?= $error ?></div>
            <?php endif; ?>

            <form id="contact-form" method="GET" novalidate>

                <!-- Name + Email row -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                    <div>
                        <label for="name" class="label">Full name</label>
                        <input type="text" id="name" name="name" class="field"
                               placeholder="John Doe"
                               value="<?= htmlspecialchars($_GET['name'] ?? '') ?>">
                        <p id="name-error" class="field-error"></p>
                    </div>
                    <div>
                        <label for="email" class="label">Email address</label>
                        <input type="email" id="email" name="email" class="field"
                               placeholder="you@example.com"
                               value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
                        <p id="email-error" class="field-error"></p>
                    </div>
                </div>

                <div style="margin-bottom:14px;">
                    <label for="subject" class="label">Subject</label>
                    <input type="text" id="subject" name="subject" class="field"
                           placeholder="How can we help?"
                           value="<?= htmlspecialchars($_GET['subject'] ?? '') ?>">
                    <p id="subject-error" class="field-error"></p>
                </div>

                <div style="margin-bottom:24px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <label for="message" class="label" style="margin-bottom:0;">Message</label>
                        <span id="char-count" class="char-count">0 / 500</span>
                    </div>
                    <textarea id="message" name="message" class="field"
                              rows="4" maxlength="500"
                              placeholder="Write your message here…"
                              style="resize:vertical;min-height:100px;"><?= htmlspecialchars($_GET['message'] ?? '') ?></textarea>
                    <p id="message-error" class="field-error"></p>
                </div>

                <button type="submit" class="btn-primary" style="width:100%;font-size:0.9rem;padding:12px;">
                    Send message
                </button>

            </form>

            <p style="text-align:center;margin-top:20px;font-size:0.85rem;color:#6b7280;">
                <a href="index.php" style="color:#111827;font-weight:700;text-decoration:none;">← Back to home</a>
            </p>

        <?php endif; ?>

    </div>

</div>

<script>
    // Character counter
    const textarea  = document.getElementById('message');
    const charCount = document.getElementById('char-count');
    if (textarea && charCount) {
        textarea.addEventListener('input', () => {
            const len = textarea.value.length;
            charCount.textContent = len + ' / 500';
            charCount.style.color = len > 450 ? '#ef4444' : '#9ca3af';
        });
    }

    // Client-side validation
    const form = document.getElementById('contact-form');
    if (form) {
        form.addEventListener('submit', e => {
            let ok = true;
            [
                { name: 'name',    errorId: 'name-error',    msg: 'Name is required.' },
                { name: 'subject', errorId: 'subject-error', msg: 'Subject is required.' },
                { name: 'message', errorId: 'message-error', msg: 'Message is required.' },
            ].forEach(f => {
                const el = form.querySelector(`[name="${f.name}"]`);
                const er = document.getElementById(f.errorId);
                if (er) { er.textContent = ''; er.style.display = 'none'; }
                if (el && !el.value.trim()) {
                    if (er) { er.textContent = f.msg; er.style.display = 'block'; }
                    ok = false;
                }
            });

            const email    = document.getElementById('email');
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
