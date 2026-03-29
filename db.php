<?php
// =============================================================
//  db.php — Database connection
//  Shared by all VulnShop pages via require_once
// =============================================================

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP default — no password
define('DB_NAME', 'vulnshop');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    // Raw error message intentionally printed — Security Misconfiguration flaw
    die("Connection failed: " . $conn->connect_error);
}
