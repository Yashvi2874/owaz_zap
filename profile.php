<?php
// =============================================================
//  profile.php — User Profile
//
//  VULNERABILITIES DEMONSTRATED:
//    1. IDOR (Broken Access Control) — any visitor can view any
//       user's profile by changing ?user_id=N in the URL
//    2. SQL Injection — user_id parameter concatenated raw
//    3. Sensitive Data — plain-text password visible in profile
// =============================================================
require_once 'db.php';
session_start();

// ─── VULNERABILITY: IDOR ────────────────────────────────────
// No check that the logged-in user owns this profile.
// Visit /profile.php?user_id=1 to see admin's password.
$user_id = isset($_GET['user_id']) ? $_GET['user_id'] : ($_SESSION['user_id'] ?? 1);

$db_error = '';
$profile  = null;

// ─── VULNERABILITY: SQL Injection ──────────────────────────
$result = $conn->query("SELECT * FROM users WHERE id = $user_id");

if ($result === false) {
    $db_error = $conn->error;
} elseif ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>VulnShop — Profile</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'nav.php'; ?>

<main class="container narrow">
  <h1>User Profile</h1>

  <?php if ($db_error): ?>
    <div class="alert error">Database error: <?= $db_error ?></div>
  <?php elseif (!$profile): ?>
    <div class="alert error">User not found.</div>
  <?php else: ?>
    <div class="profile-card">
      <div class="avatar">👤</div>
      <table class="info-table">
        <tr><th>Username</th><td><?= htmlspecialchars($profile['username']) ?></td></tr>
        <tr><th>Email</th><td><?= htmlspecialchars($profile['email']) ?></td></tr>
        <tr><th>Role</th><td><?= htmlspecialchars($profile['role']) ?></td></tr>
        <!-- ─── VULNERABILITY: Sensitive Data ────────────────
             Plain-text password shown in the profile view.
             Combined with IDOR above: anyone can read anyone's password.
        -->
        <tr><th>Password</th><td class="password-leak"><?= htmlspecialchars($profile['password']) ?></td></tr>
        <tr><th>Member since</th><td><?= $profile['created'] ?></td></tr>
      </table>
    </div>

    <!-- Try other profiles: -->
    <div class="idor-demo">
      <p><strong>Try other profiles:</strong></p>
      <a class="btn btn-sm" href="profile.php?user_id=1">User #1</a>
      <a class="btn btn-sm" href="profile.php?user_id=2">User #2</a>
      <a class="btn btn-sm" href="profile.php?user_id=3">User #3 (admin)</a>
    </div>
  <?php endif; ?>
</main>
</body>
</html>
