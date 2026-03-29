<?php
// =============================================================
//  register.php — User Registration
//
//  VULNERABILITIES DEMONSTRATED:
//    1. Sensitive Data — password stored in plain text
//    2. No CSRF token  — sitewide CSRF vulnerability
// =============================================================
require_once 'db.php';
session_start();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];          // ← Plain text — no hashing
    $email    = $conn->real_escape_string($_POST['email']);

    $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
    if ($check->num_rows > 0) {
        $error = "Username already taken.";
    } else {
        // ─── VULNERABILITY: Sensitive Data Exposure ─────────────
        // Password is stored as plain text. A DB dump exposes all passwords.
        $conn->query("INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')");
        $success = "Account created! <a href='login.php'>Login here</a>.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>VulnShop — Register</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'nav.php'; ?>

<main class="container narrow">
  <h1>Register</h1>

  <?php if ($error):   ?><div class="alert error"><?= $error ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert success"><?= $success ?></div><?php endif; ?>

  <!-- No CSRF token — intentional -->
  <form method="POST" action="register.php">
    <label>Username</label>
    <input type="text" name="username" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <label>Email</label>
    <input type="email" name="email">

    <button type="submit" class="btn btn-full">Create Account</button>
  </form>
</main>
</body>
</html>
