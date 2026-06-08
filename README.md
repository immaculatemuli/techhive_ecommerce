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
3. Open phpMyAdmin and import `Project/techhive_db.sql`
4. Visit `http://localhost/techhive_ecommerce/Project/index.php`

---

## Login Credentials (ADMIN)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@techhive.com | admin123 |

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

| Week | Content |
|------|---------|
| Week 1 | XAMPP installation, localhost test, hello world HTML/PHP, DB connection |
| Week 2 | Low-fidelity wireframes, color theme, navigation structure, Figma prototype |
| Week 3 | JavaScript form validation, password strength checker, PHP basics, dynamic input |
| Week 4 | Login & register forms, session-based auth, logout, backend folder structure |
| Week 5 | Database creation, table setup, CRUD operations, PHP-MySQL connection via PDO |

---

## Weekly Folders

| Folder | Contents |
|--------|---------|
| `Week1/` | `hello.html`, `hello.php`, `db_test.php`, `techhive_db.sql`, screenshots |
| `Week2/` | Wireframe and Figma prototype screenshots |
| `Week3/` | `login.php`, `register.php`, `dynamic_input.php`, `main.js`, `db_test.php`, screenshots |
| `Week4/` | `login.php`, `register.php`, `logout.php`, `dynamic_input.php`, `header.php`, `footer.php`, screenshots |
| `Week5/` | `techhive_db.sql`, `admin/`, `config.php`, screenshots |
