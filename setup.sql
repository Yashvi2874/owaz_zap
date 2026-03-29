-- =============================================================
--  VulnShop — Database Setup
--  Run this in phpMyAdmin or: mysql -u root -p < setup.sql
--  WARNING: Intentionally insecure. For educational use only.
-- =============================================================

CREATE DATABASE IF NOT EXISTS vulnshop;
USE vulnshop;

-- ── Users ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,   -- stored plain-text (intentional flaw)
    email    VARCHAR(255),
    role     ENUM('user','admin') DEFAULT 'user',
    created  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password, email, role) VALUES
    ('john',  'password1', 'john@example.com',  'user'),
    ('jane',  'letmein',   'jane@example.com',  'user'),
    ('admin', 'admin123',  'admin@example.com', 'admin');

-- ── Products ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    description TEXT,
    price       DECIMAL(10,2),
    image       VARCHAR(255),
    category    VARCHAR(100)
);

INSERT INTO products (name, description, price, image, category) VALUES
    ('Leather Wallet',   'Genuine leather bifold wallet',        29.99,  'wallet.jpg',   'Accessories'),
    ('Canvas Backpack',  'Durable 30L travel backpack',          59.99,  'bag.jpg',      'Bags'),
    ('Sunglasses',       'UV400 polarised lenses',               19.99,  'shades.jpg',   'Accessories'),
    ('Running Shoes',    'Lightweight mesh running shoes',        89.99,  'shoes.jpg',    'Footwear'),
    ('Hoodie',           '100% cotton pullover hoodie',          44.99,  'hoodie.jpg',   'Clothing'),
    ('Watch',            'Stainless steel quartz watch',        149.99,  'watch.jpg',    'Accessories');

-- ── Comments ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS comments (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    username   VARCHAR(100),
    comment    TEXT,             -- stored and rendered raw (Stored XSS intentional)
    created    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

INSERT INTO comments (product_id, username, comment) VALUES
    (1, 'jane', 'Great wallet, very slim!'),
    (2, 'john', 'Perfect for my hiking trips.');

-- ── Contact messages ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS contact_messages (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(255),
    email   VARCHAR(255),
    message TEXT,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Sessions  (simple DB-backed sessions — no httponly flag, intentional) ──
CREATE TABLE IF NOT EXISTS sessions (
    token   VARCHAR(64) PRIMARY KEY,
    user_id INT,
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
