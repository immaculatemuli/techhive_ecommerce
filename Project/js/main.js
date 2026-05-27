// ============================================================
// TechHive — main.js
// ============================================================


// ── Mobile menu ──────────────────────────────────────────────
const menuBtn = document.getElementById('menu-btn');
if (menuBtn) {
    menuBtn.addEventListener('click', () => {
        document.getElementById('nav-links')?.classList.toggle('open');
    });
}


// ── Show / hide password toggle ───────────────────────────────
const toggleBtn     = document.getElementById('toggle-password');
const passwordInput = document.getElementById('password');

if (toggleBtn && passwordInput) {
    toggleBtn.addEventListener('click', () => {
        const hidden = passwordInput.type === 'password';
        passwordInput.type    = hidden ? 'text' : 'password';
        toggleBtn.textContent = hidden ? 'Hide' : 'Show';
    });
}


// ── Password strength checker ─────────────────────────────────
// Rules: 8+ chars, uppercase, lowercase, number, special char
const rules = [
    { id: 'rule-length',  test: v => v.length >= 8,                label: 'At least 8 characters' },
    { id: 'rule-upper',   test: v => /[A-Z]/.test(v),              label: 'One uppercase letter' },
    { id: 'rule-lower',   test: v => /[a-z]/.test(v),              label: 'One lowercase letter' },
    { id: 'rule-number',  test: v => /[0-9]/.test(v),              label: 'One number' },
    { id: 'rule-special', test: v => /[^A-Za-z0-9]/.test(v),       label: 'One special character (!@#$…)' },
];

if (passwordInput) {
    passwordInput.addEventListener('input', () => {
        const val   = passwordInput.value;
        const score = rules.filter(r => r.test(val)).length;

        // Update strength bars
        updateBars(score);

        // Update live checklist items
        rules.forEach(rule => {
            const el = document.getElementById(rule.id);
            if (!el) return;
            const passed = rule.test(val);
            el.classList.toggle('passed', passed);
            el.classList.toggle('failed', val.length > 0 && !passed);
        });
    });
}

function updateBars(score) {
    const bars  = [1,2,3,4].map(i => document.getElementById(`bar-${i}`));
    const label = document.getElementById('strength-label');
    if (!bars[0]) return;

    const colours = ['', '#ef4444', '#f97316', '#eab308', '#22c55e'];
    const texts   = ['', 'Too weak', 'Fair', 'Good', 'Strong'];

    bars.forEach((b, i) => {
        if (!b) return;
        b.style.background = i < score ? colours[score] : '';
    });

    if (label) {
        label.textContent  = score === 0 ? '—' : texts[score];
        label.style.color  = colours[score] || '#3f3f46';
    }
}


// ── LOGIN form validation ─────────────────────────────────────
const loginForm = document.getElementById('login-form');
if (loginForm) {
    loginForm.addEventListener('submit', e => {
        let ok = true;
        clearAllErrors();

        const email = document.getElementById('email');
        const pass  = document.getElementById('password');

        if (!email.value.trim()) {
            showError('email-error', 'Email is required.'); ok = false;
        } else if (!validEmail(email.value)) {
            showError('email-error', 'Enter a valid email address.'); ok = false;
        }

        if (!pass.value) {
            showError('password-error', 'Password is required.'); ok = false;
        }

        if (!ok) e.preventDefault();
    });

    // Live email format feedback on blur
    document.getElementById('email')?.addEventListener('blur', function () {
        if (this.value && !validEmail(this.value)) {
            showError('email-error', 'Enter a valid email address.');
        } else {
            clearError('email-error');
        }
    });
}


// ── REGISTER form validation ──────────────────────────────────
const registerForm = document.getElementById('register-form');
if (registerForm) {
    registerForm.addEventListener('submit', e => {
        let ok = true;
        clearAllErrors();

        const username = document.getElementById('username');
        const email    = document.getElementById('email');
        const password = document.getElementById('password');
        const confirm  = document.getElementById('confirm_password');

        if (!username?.value.trim()) {
            showError('username-error', 'Username is required.'); ok = false;
        }
        if (!email?.value.trim()) {
            showError('email-error', 'Email is required.'); ok = false;
        } else if (!validEmail(email.value)) {
            showError('email-error', 'Enter a valid email address.'); ok = false;
        }
        if (!password?.value) {
            showError('password-error', 'Password is required.'); ok = false;
        } else {
            const unmet = rules.filter(r => !r.test(password.value));
            if (unmet.length > 0) {
                showError('password-error', 'Password does not meet all requirements.'); ok = false;
            }
        }
        if (!confirm?.value) {
            showError('confirm-error', 'Please confirm your password.'); ok = false;
        } else if (confirm.value !== password?.value) {
            showError('confirm-error', 'Passwords do not match.'); ok = false;
        }

        if (!ok) e.preventDefault();
    });

    // Live confirm match feedback
    document.getElementById('confirm_password')?.addEventListener('input', function () {
        const pw = document.getElementById('password')?.value;
        if (this.value && this.value !== pw) {
            showError('confirm-error', 'Passwords do not match.');
        } else {
            clearError('confirm-error');
        }
    });

    // Live email format on blur
    document.getElementById('email')?.addEventListener('blur', function () {
        if (this.value && !validEmail(this.value)) {
            showError('email-error', 'Enter a valid email address.');
        } else {
            clearError('email-error');
        }
    });
}


// ── Live product search ───────────────────────────────────────
const searchInput  = document.getElementById('search-input');
const productCards = document.querySelectorAll('.product-card');

if (searchInput && productCards.length) {
    searchInput.addEventListener('input', () => {
        const q = searchInput.value.toLowerCase();
        productCards.forEach(card => {
            const name = (card.dataset.name || '').toLowerCase();
            card.style.display = name.includes(q) ? '' : 'none';
        });
    });
}


// ── Cart count badge ──────────────────────────────────────────
function updateCartCount() {
    const badge = document.getElementById('cart-count');
    if (!badge) return;
    fetch('/techhive/cart_count.php')
        .then(r => r.json())
        .then(d => {
            if (d.count > 0) {
                badge.textContent = d.count;
                badge.style.display = 'inline-flex';
            }
        })
        .catch(() => {});
}
updateCartCount();


// ── Utilities ─────────────────────────────────────────────────
function showError(id, msg) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg;
    el.style.display = 'block';
}
function clearError(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.textContent = '';
    el.style.display = 'none';
}
function clearAllErrors() {
    document.querySelectorAll('.field-error').forEach(el => {
        el.textContent = '';
        el.style.display = 'none';
    });
}
function validEmail(v) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim());
}
