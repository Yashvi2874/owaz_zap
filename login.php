<?php
// =============================================================
//  login.php — Authentication Page
//
//  VULNERABILITIES DEMONSTRATED:
//    1. SQL Injection  — username/password concatenated raw into query
//    2. Auth Failure   — error message reveals whether username exists
//    3. Auth Failure   — failed response includes the attempted username
//    4. Auth Failure   — no rate limiting or account lockout
//    5. Sensitive Data — plain-text password stored and compared directly
// =============================================================
require_once 'db.php';
session_start();

$error = '';
$attempted_username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];   // ← No sanitisation
    $password = $_POST['password'];

    $attempted_username = $username;  // ← Information leakage: echoed back in error

    // ─── VULNERABILITY: SQL Injection ──────────────────────────
    // Attacker payload: admin' OR '1'='1' --
    // This bypasses authentication entirely.
    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";

    $result = $conn->query($sql);

    if ($result === false) {
        // ─── VULNERABILITY: Security Misconfiguration ───────────
        // Raw MySQL error shown on screen — exposes table/column names
        $error = "Query error: " . $conn->error;
    } elseif ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        // ─── VULNERABILITY: Auth Failure — Username Enumeration ─
        // "Username not found" vs "Wrong password" tells attacker
        // whether the account exists.
        $check = $conn->query("SELECT id FROM users WHERE username = '$username'");
        if ($check && $check->num_rows > 0) {
            $error = "Wrong password for user: $attempted_username";
        } else {
            $error = "Username '$attempted_username' not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>VulnShop — Login</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'nav.php'; ?>

<main class="container narrow">
  <h1>Login</h1>

  <?php if ($error): ?>
    <div class="alert error"><?= $error ?></div>
  <?php endif; ?>

  <!-- No CSRF token on this form — CSRF vulnerability intentional -->
  <form method="POST" action="login.php">
    <label>Username</label>
    <input type="text" name="username" value="<?= htmlspecialchars($attempted_username) ?>">

    <label>Password</label>
    <input type="password" name="password">

    <button type="submit" class="btn btn-full">Login</button>
  </form>

  <p>Don't have an account? <a href="register.php">Register</a></p>
</main>
</body>
</html>
