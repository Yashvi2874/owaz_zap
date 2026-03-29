<?php
// =============================================================
//  search.php — Product Search
//
//  VULNERABILITIES DEMONSTRATED:
//    1. SQL Injection    — search term concatenated raw into LIKE query
//    2. Reflected XSS   — search term echoed back into page without encoding
// =============================================================
require_once 'db.php';
session_start();

$query    = isset($_GET['q']) ? $_GET['q'] : '';
$products = [];
$db_error = '';

if ($query !== '') {
    // ─── VULNERABILITY: SQL Injection ──────────────────────────
    // Payload: ' UNION SELECT 1,username,password,email,role,created FROM users -- 
    // This dumps the entire users table including passwords.
    $sql    = "SELECT * FROM products WHERE name LIKE '%$query%' OR description LIKE '%$query%'";
    $result = $conn->query($sql);

    if ($result === false) {
        // ─── VULNERABILITY: Security Misconfiguration ───────────
        // Raw DB error printed — reveals table names and query structure
        $db_error = $conn->error;
    } else {
        $products = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>VulnShop — Search</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'nav.php'; ?>

<main class="container">
  <h1>Search Products</h1>

  <form method="GET" action="search.php">
    <div class="search-row">
      <input type="text" name="q" value="<?= $query /* ← XSS: unencoded */ ?>" placeholder="Search...">
      <button type="submit" class="btn">Search</button>
    </div>
  </form>

  <?php if ($db_error): ?>
    <!-- Raw MySQL error — visible to anyone -->
    <div class="alert error">Database error: <?= $db_error ?></div>
  <?php endif; ?>

  <?php if ($query !== ''): ?>
    <!-- ─── VULNERABILITY: Reflected XSS ─────────────────────
         Payload: <script>alert(document.cookie)</script>
         The search term appears verbatim inside <p> without encoding.
    -->
    <p>Results for: <?= $query ?></p>

    <?php if (empty($products) && !$db_error): ?>
      <p>No products found.</p>
    <?php endif; ?>
  <?php endif; ?>

  <div class="product-grid">
    <?php foreach ($products as $p): ?>
    <div class="product-card">
      <h3><?= htmlspecialchars($p['name']) ?></h3>
      <p><?= htmlspecialchars($p['description']) ?></p>
      <a class="btn" href="product.php?id=<?= $p['id'] ?>">View</a>
    </div>
    <?php endforeach; ?>
  </div>
</main>
</body>
</html>
