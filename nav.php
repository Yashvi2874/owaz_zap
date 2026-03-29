<?php
// nav.php — Shared navigation bar (included by all pages)
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<nav class="navbar">
  <a class="brand" href="index.php">🛍️ VulnShop</a>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="search.php">Search</a>
    <a href="ajax-page.php">Featured</a>
    <a href="contact.php">Contact</a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="dashboard.php">Dashboard</a>
      <?php if ($_SESSION['role'] === 'admin'): ?>
        <a href="admin/">Admin</a>
      <?php endif; ?>
      <a href="logout.php">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
    <?php else: ?>
      <a href="login.php">Login</a>
      <a href="register.php">Register</a>
    <?php endif; ?>
  </div>
</nav>
