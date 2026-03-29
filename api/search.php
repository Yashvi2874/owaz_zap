<?php
// =============================================================
//  api/search.php — REST Endpoint: search products
//
//  VULNERABILITIES DEMONSTRATED:
//    1. SQL Injection — q parameter concatenated raw into query
//    2. XSS in API   — search term reflected in JSON response
//                      without any encoding
// =============================================================
require_once '../db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$q = isset($_GET['q']) ? $_GET['q'] : '';

if ($q === '') {
    echo json_encode([]);
    exit;
}

// ─── VULNERABILITY: SQL Injection ──────────────────────────
$result = $conn->query("SELECT * FROM products WHERE name LIKE '%$q%'");

if ($result === false) {
    http_response_code(500);
    echo json_encode(['error' => $conn->error]);
    exit;
}

$products = $result->fetch_all(MYSQLI_ASSOC);

// ─── VULNERABILITY: XSS reflected in JSON ──────────────────
// The query term is echoed raw in the JSON response.
// If a consumer renders this value as innerHTML, XSS fires.
echo json_encode([
    'query'    => $q,          // ← raw, unencoded
    'count'    => count($products),
    'results'  => $products
]);
