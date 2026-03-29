<?php
// =============================================================
//  dashboard.php — Authenticated user dashboard
//  Only reachable after login — used by ZAP to verify auth.
//  ZAP's "Logged-in indicator" regex: Welcome back
// =============================================================
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>VulnShop — Dashboard</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'nav.php'; ?>

<main class="container">
  <h1>Welcome back, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
  <p>You are logged in as role: <strong><?= htmlspecialchars($_SESSION['role']) ?></strong></p>

  <div class="dashboard-links">
    <a class="btn" href="profile.php?user_id=<?= $_SESSION['user_id'] ?>">My Profile</a>
    <a class="btn" href="profile.php?user_id=1">View User #1 (IDOR demo)</a>
    <?php if ($_SESSION['role'] === 'admin'): ?>
      <a class="btn btn-danger" href="admin/">Admin Panel</a>
    <?php endif; ?>
    <a class="btn btn-secondary" href="logout.php">Logout</a>
  </div>
</main>
</body>
</html>
