<?php
// =============================================================
//  contact.php — Contact Form
//
//  VULNERABILITIES DEMONSTRATED:
//    1. Reflected XSS — name & message echoed raw after submission
//    2. No CSRF token — form has no CSRF protection
// =============================================================
require_once 'db.php';
session_start();

$submitted = false;
$name      = '';
$email     = '';
$message   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = $_POST['name']    ?? '';
    $email   = $_POST['email']   ?? '';
    $message = $_POST['message'] ?? '';

    // Save to DB (escaped for SQL, but reflected into HTML below without encoding)
    $n = $conn->real_escape_string($name);
    $e = $conn->real_escape_string($email);
    $m = $conn->real_escape_string($message);
    $conn->query("INSERT INTO contact_messages (name, email, message) VALUES ('$n','$e','$m')");

    $submitted = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>VulnShop — Contact</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'nav.php'; ?>

<main class="container narrow">
  <h1>Contact Us</h1>

  <?php if ($submitted): ?>
    <!-- ─── VULNERABILITY: Reflected XSS ───────────────────
         $name and $message are echoed raw — no htmlspecialchars.
         Payload in name field: <img src=x onerror=alert(1)>
         Payload in message:    <script>alert(document.cookie)</script>
    -->
    <div class="alert success">
      Thanks <?= $name ?>, we received your message:<br>
      <em><?= $message ?></em>
    </div>
  <?php endif; ?>

  <!-- No CSRF token — intentional flaw -->
  <form method="POST" action="contact.php">
    <label>Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($name) ?>">

    <label>Email</label>
    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>">

    <label>Message</label>
    <textarea name="message" rows="5"><?= htmlspecialchars($message) ?></textarea>

    <button type="submit" class="btn btn-full">Send Message</button>
  </form>
</main>
</body>
</html>
