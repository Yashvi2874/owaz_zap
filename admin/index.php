<?php
// =============================================================
//  admin/index.php — Admin Panel
//
//  VULNERABILITIES DEMONSTRATED:
//    1. Broken Access Control — role check is client-side only.
//       Any logged-in user can access /admin/ directly.
//    2. Sensitive Data — full users table with passwords dumped.
//
//  ZAP discovers this via robots.txt Disallow: /admin
// =============================================================
require_once '../db.php';
session_start();

// ─── VULNERABILITY: Broken Access Control ──────────────────
// Should check: $_SESSION['role'] === 'admin'
// Instead, just redirects unauthenticated visitors — any
// authenticated user can access admin functions.
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
// Missing: role check — any user reaches this panel

$users    = $conn->query("SELECT * FROM users")->fetch_all(MYSQLI_ASSOC);
$products = $conn->query("SELECT * FROM products")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>VulnShop — Admin Panel</title>
<link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include '../nav.php'; ?>

<main class="container">
  <h1>⚠️ Admin Panel</h1>
  <p class="alert error">
    <strong>IDOR Demo:</strong> This panel has no role enforcement.
    Any authenticated user can reach it by visiting /admin/ directly.
  </p>

  <h2>All Users (including plain-text passwords)</h2>
  <!-- ─── VULNERABILITY: Sensitive Data ────────────────────
       Entire users table including passwords dumped on screen.
  -->
  <table class="data-table">
    <tr><th>ID</th><th>Username</th><th>Email</th><th>Password</th><th>Role</th></tr>
    <?php foreach ($users as $u): ?>
    <tr>
      <td><?= $u['id'] ?></td>
      <td><?= htmlspecialchars($u['username']) ?></td>
      <td><?= htmlspecialchars($u['email']) ?></td>
      <td class="password-leak"><?= htmlspecialchars($u['password']) ?></td>
      <td><?= htmlspecialchars($u['role']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>

  <h2>Products</h2>
  <table class="data-table">
    <tr><th>ID</th><th>Name</th><th>Price</th><th>Category</th></tr>
    <?php foreach ($products as $p): ?>
    <tr>
      <td><?= $p['id'] ?></td>
      <td><?= htmlspecialchars($p['name']) ?></td>
      <td>$<?= number_format($p['price'], 2) ?></td>
      <td><?= htmlspecialchars($p['category']) ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</main>
</body>
</html>
