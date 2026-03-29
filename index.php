<?php
// =============================================================
//  index.php — VulnShop Homepage
//  Displays a product grid pulled from the database.
//  Vulnerability: none on this page (acts as a clean entry point)
// =============================================================
require_once 'db.php';
session_start();

$result = $conn->query("SELECT * FROM products");
$products = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>VulnShop — Home</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'nav.php'; ?>

<main class="container">
  <h1>Welcome to VulnShop</h1>
  <p class="subtitle">Your one-stop intentionally vulnerable shop.</p>

  <div class="product-grid">
    <?php foreach ($products as $p): ?>
    <div class="product-card">
      <div class="product-img">🛍️</div>
      <h3><?= $p['name'] ?></h3>
      <p><?= $p['description'] ?></p>
      <div class="product-footer">
        <span class="price">$<?= number_format($p['price'], 2) ?></span>
        <a class="btn" href="product.php?id=<?= $p['id'] ?>">View</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</main>
</body>
</html>
