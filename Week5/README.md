# Week 5 - Database Components and CRUD Operations

## Task 1 - Introduction to Databases
Relational database used: MySQL
- Stores structured data in tables with relationships
- techhive_db contains users, products, orders, cart tables
- Chosen because TechHive needs structured relational data

## Task 2 - Database Creation
**File:** techhive_db.sql
Databases created:
- techhive_db (main project database)
Tables: users, products, orders, order_items, cart

## Task 3 - Creating Tables
**File:** techhive_db.sql
Tables created with proper structure:
- users (id, username, email, password, role, created_at)
- products (id, name, description, price, category, stock, image)
- orders (id, user_id, total, status, created_at)
- cart (id, user_id, product_id, quantity)

## Task 4 - CRUD Operations
**File:** admin/index.php
Implemented:
- Create: Add new products to database
- Read: View all products in admin panel
- Update: Edit existing product details
- Delete: Remove products from database

## Task 5 - Connecting PHP to Database
**File:** config.php
- PDO connection used (more secure than mysqli)
- Singleton getDB() function
- Prepared statements throughout
- Error handling with try/catch

## Task 6 - Fetching Database Records
**File:** admin/index.php
- SELECT queries fetch all products
- Results displayed in admin table
- Dynamic content pulled from techhive_db

