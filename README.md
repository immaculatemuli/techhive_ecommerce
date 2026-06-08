# TechHive E-Commerce

A PHP + MySQL e-commerce project built as part of the Advanced Web Design and Development unit (BIT3208).

**Student:** Immaculate Mutheu Muli  
**Admission No:** BSCCS/2024/33678  
**Lecturer:** Mr. Michael Nyoro  

---

## Login Credentials (ADMIN)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@techhive.com | admin123 |

---

## Setup

1. Install [XAMPP](https://www.apachefriends.org/) and start Apache + MySQL
2. Clone the repo into `htdocs`:
   ```
   git clone https://github.com/immaculatemuli/techhive_ecommerce.git
   ```
3. Open phpMyAdmin and import `Project/techhive_db.sql`
4. Visit `http://localhost/techhive_ecommerce/Project/index.php`

---

## Project Files (`/Project`)

```
config.php
index.php
login.php
register.php
logout.php
google_auth.php
dashboard.php
cart.php
cart_count.php
checkout.php
contact.php
create_admin.php
db_test.php
dynamic_input.php
hello.html
hello.php
setup_products.php
settings.json
techhive_db.sql
admin/
  index.php
  add_product.php
  edit_product.php
  delete_product.php
  orders.php
  users.php
  admin_header.php
  admin_footer.php
products/
  index.php
  view.php
includes/
  header.php
  footer.php
css/
  style.css
js/
  main.js
```

---

## Logbook (`ADVANCED WEB-LOGBOOK.docx`)

- Week 1: Local Environment Setup
- Week 2: Wireframes and GUI Design
- Week 3: JavaScript and PHP Basics
- Week 4: Authentication System
- Week 5: Database Components and CRUD Operations

---

## Weekly Folders

- `Week1/` — Environment setup, hello world, DB connection test
- `Week2/` — Wireframes and Figma prototypes
- `Week3/` — JS validation, PHP basics, dynamic input
- `Week4/` — Login, register, sessions, folder structure
- `Week5/` — MySQL schema, PDO connection, admin CRUD
