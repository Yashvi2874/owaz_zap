<?php
// =============================================================
//  logout.php — Session destruction
//  EXCLUDED from ZAP scope to prevent mid-scan logout.
//  ZAP context exclude pattern: .*/logout\.php
// =============================================================
session_start();
session_destroy();
header("Location: login.php");
exit;
