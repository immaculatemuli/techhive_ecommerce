-- ============================================================
-- TechHive Database
-- BIT3208 Project
-- ============================================================

CREATE DATABASE IF NOT EXISTS techhive_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techhive_db;

-- ============================================================
-- TABLES
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)  NOT NULL UNIQUE,
    email      VARCHAR(100) NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL,
    role       ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(150)   NOT NULL,
    description TEXT,
    price       DECIMAL(10,2)  NOT NULL,
    category    VARCHAR(50)    NOT NULL,
    stock       INT            NOT NULL DEFAULT 0,
    image       VARCHAR(255),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS orders (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT           NOT NULL,
    total      DECIMAL(10,2) NOT NULL,
    status     ENUM('pending','processing','shipped','delivered','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_items (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    order_id   INT           NOT NULL,
    product_id INT           NOT NULL,
    quantity   INT           NOT NULL,
    price      DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS cart (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    product_id INT NOT NULL,
    quantity   INT NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ============================================================
-- SAMPLE USERS
-- Plain text password for all: password123
-- ============================================================

INSERT INTO users (username, email, password, role) VALUES
('admin',       'admin@techhive.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('superadmin',  'super@techhive.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('john_doe',    'john@example.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('jane_smith',  'jane@example.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer'),
('mike_jones',  'mike@example.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer');

-- ============================================================
-- SAMPLE PRODUCTS
-- ============================================================

INSERT INTO products (name, description, price, category, stock, image) VALUES
('Dell XPS 15',            'High-performance laptop with 15.6" OLED display, Intel Core i7, 16GB RAM, 512GB SSD.',  1299.99, 'Laptops',     15, 'dell_xps15.jpg'),
('MacBook Air M2',         'Apple MacBook Air with M2 chip, 13.6" Liquid Retina display, 8GB RAM, 256GB SSD.',       1099.99, 'Laptops',     10, 'macbook_air.jpg'),
('Lenovo ThinkPad X1',     'Business ultrabook with Intel Core i5, 14" FHD display, 16GB RAM, 512GB SSD.',           999.99,  'Laptops',     8,  'thinkpad_x1.jpg'),
('Samsung Galaxy S24',     'Flagship Android phone, 6.2" Dynamic AMOLED, 8GB RAM, 128GB storage, 50MP camera.',     799.99,  'Phones',      25, 'galaxy_s24.jpg'),
('iPhone 15 Pro',          'Apple iPhone 15 Pro with A17 Pro chip, 6.1" Super Retina XDR, 256GB storage.',           1199.99, 'Phones',      20, 'iphone15pro.jpg'),
('Google Pixel 8',         'Google Pixel 8 with Tensor G3 chip, 6.2" OLED display, 8GB RAM, 128GB storage.',         699.99,  'Phones',      18, 'pixel8.jpg'),
('Sony WH-1000XM5',        'Industry-leading noise cancelling wireless headphones with 30-hour battery life.',        349.99,  'Accessories', 30, 'sony_wh1000xm5.jpg'),
('Logitech MX Master 3S',  'Advanced wireless mouse with 8K DPI sensor, quiet clicks, multi-device support.',        99.99,   'Accessories', 50, 'mx_master3s.jpg'),
('Samsung 27" 4K Monitor', '27-inch 4K UHD IPS monitor, 144Hz refresh rate, HDR400, USB-C connectivity.',           499.99,  'Accessories', 12, 'samsung_monitor.jpg'),
('Anker 65W GaN Charger',  'Compact 65W GaN fast charger with 3 ports (2x USB-C, 1x USB-A), foldable plug.',        49.99,   'Accessories', 60, 'anker_charger.jpg');
