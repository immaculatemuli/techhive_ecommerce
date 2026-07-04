# TechHive E-Commerce

A PHP + MySQL e-commerce project built as part of the Advanced Web Design and Development unit (BIT3208).

**Student:** Immaculate Mutheu Muli  
**Admission No:** BSCCS/2024/33678  
**Lecturer:** Mr. Michael Nyoro  

---

## Setup

1. Install [XAMPP](https://www.apachefriends.org/) and start Apache + MySQL
2. Clone the repo into `htdocs`:
   ```
   git clone https://github.com/immaculatemuli/techhive_ecommerce.git
   ```
3. Open phpMyAdmin and import the base database:
   ```
   Project/techhive_db.sql
   ```
4. Run the bonus-features migration to add new columns and tables:
   ```
   Project/update_db.sql
   ```
5. Visit `http://localhost/techhive_ecommerce/Project/index.php`

---

## Login Credentials (ADMIN)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@techhive.com | admin123 |

---

## Project Files (`/Project`)

```
config.php                  — DB config, SMTP, TOTP helpers, token generator
index.php                   — Homepage (hero, categories, featured products, stats)
login.php                   — Login with Remember Me and 2FA staging
register.php                — Registration with email verification
logout.php                  — Session + cookie clear
dashboard.php               — User dashboard with quick-action cards
forgot_password.php         — Request password reset email
reset_password.php          — Set new password via token link
verify_email.php            — Confirm email address via token link
resend_verify.php           — Resend verification email
2fa_setup.php               — Enable / disable two-factor authentication
2fa_verify.php              — Enter TOTP code after login
profile.php                 — Update profile info, avatar upload, change password
google_auth.php             — Google OAuth callback
cart.php                    — Cart logic (add, remove, update)
cart_count.php              — Returns cart item count as JSON
checkout.php                — Order placement
contact.php                 — Contact form
create_admin.php            — One-time admin account creator
db_test.php                 — Database connection test
dynamic_input.php           — Dynamic input demo
hello.html
hello.php
setup_products.php          — Seed products into the database
settings.json
techhive_db.sql             — Base database dump
update_db.sql               — Migration: adds remember_token, email_verified, 2FA,
                              profile_image, phone, bio columns; creates
                              password_resets and email_verifications tables
admin/
  index.php                 — Admin dashboard
  add_product.php
  edit_product.php
  delete_product.php
  orders.php
  users.php
  admin_header.php
  admin_footer.php
products/
  index.php                 — Product listing with search and category filter
  view.php                  — Single product detail page
includes/
  header.php                — Shared nav (checkRememberMe, cart badge, profile link)
  footer.php
css/
  style.css                 — Global styles, mobile-first breakpoints, responsive images
js/
  main.js
vendor/
  phpmailer/                — PHPMailer (Exception.php, PHPMailer.php, SMTP.php)
```

---

## Logbook (`ADVANCED WEB-LOGBOOK.pdf`)

| Week | Content |
|------|---------|
| Week 1 | XAMPP installation, localhost test, hello world HTML/PHP, DB connection |
| Week 2 | Low-fidelity wireframes, color theme, navigation structure, Figma prototype |
| Week 3 | JavaScript form validation, password strength checker, PHP basics, dynamic input |
| Week 4 | Login & register forms, session-based auth, logout, backend folder structure |
| Week 5 | Database creation, table setup, CRUD operations, PHP-MySQL connection via PDO |
| Week 6 | Database integration, PDO connection, full CRUD operations for products |
| Week 7 | User authentication and session management; bonus: Password Reset, Email Verification, 2FA, Profile |
| Week 8 | Responsive Web Design and Mobile-First Development — profile page, mobile/tablet/desktop views |
| Week 9 | Servlets, JSP, sessions and cookies (separate Java/Tomcat app) — student login, session tracking, theme cookie, Remember Me |

---

## Weekly Folders

| Folder | Contents |
|--------|---------|
| `Week1/` | `hello.html`, `hello.php`, `db_test.php`, `techhive_db.sql`, screenshots |
| `Week2/` | Wireframe and Figma prototype screenshots |
| `Week3/` | `login.php`, `register.php`, `dynamic_input.php`, `main.js`, `db_test.php`, screenshots |
| `Week4/` | `login.php`, `register.php`, `logout.php`, `dynamic_input.php`, `header.php`, `footer.php`, screenshots |
| `Week5/` | `techhive_db.sql`, `admin/`, `config.php`, screenshots |
| `Week6/` | README, screenshots |
| `Week7/` | README, screenshots — registration, login, session, logout, password reset, email verification, 2FA, profile |
| `Week8/` | README, screenshots — responsive profile page, mobile view, tablet view, desktop view |
| `Week9/` | `StudentLogin/` (Servlet/JSP app for Tomcat: `login.jsp`, `LoginServlet.java`, `welcome.jsp`, `logout.jsp`), README, screenshots |
