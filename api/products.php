<?php
// =============================================================
//  api/products.php — REST Endpoint: list all products
//  Used by ajax-page.php to load products via JS fetch().
//  No vulnerabilities on this endpoint — clean control.
// =============================================================
require_once '../db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$result   = $conn->query("SELECT id, name, description, price, category FROM products");
$products = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($products);
