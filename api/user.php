<?php
// =============================================================
//  api/user.php — REST Endpoint: get user by ID
//
//  VULNERABILITIES DEMONSTRATED:
//    1. Sensitive Data Exposure — returns password field with NO auth
//    2. SQL Injection           — id parameter concatenated raw
//    3. Broken Access Control   — no authentication required
//
//  Call: GET /api/user.php?id=1  →  returns admin credentials
//        GET /api/user.php?id=1 OR 1=1 --  →  SQL injection demo
// =============================================================
require_once '../db.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$id = isset($_GET['id']) ? $_GET['id'] : '1';

// ─── VULNERABILITY: SQL Injection ──────────────────────────
$result = $conn->query("SELECT * FROM users WHERE id = $id");

if ($result === false) {
    // Raw error returned in JSON response
    http_response_code(500);
    echo json_encode(['error' => $conn->error]);
    exit;
}

$user = $result->fetch_assoc();

if (!$user) {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
    exit;
}

// ─── VULNERABILITY: Sensitive Data Exposure ────────────────
// Entire user object returned — including password field.
// No authentication check. No field filtering.
// Anyone can call: /api/user.php?id=3 and get admin:admin123
echo json_encode($user);
