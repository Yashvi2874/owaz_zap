<?php
// =============================================================
//  ajax-page.php — JavaScript-rendered Product List
//
//  DEMO PURPOSE:
//    This page loads product links via a JavaScript fetch() call.
//    The raw HTML source contains NO links to product pages.
//    The Standard Spider finds this page but discovers 0 children.
//    The AJAX Spider drives a real browser, JS executes, and the
//    6 product links appear — demonstrating the AJAX Spider's value.
// =============================================================
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>VulnShop — Featured Products (JS-rendered)</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include 'nav.php'; ?>

<main class="container">
  <h1>Featured Products</h1>
  <p class="subtitle">
    <em>This page loads products via JavaScript — the Standard Spider sees nothing here.</em>
  </p>

  <!-- Products injected here by JS — invisible to Standard Spider -->
  <div id="product-list" class="product-grid">
    <p>Loading products...</p>
  </div>
</main>

<script>
// This fetch() call generates the product URLs at runtime.
// The raw HTML source has no <a href="product.php?id=..."> tags.
// Only a browser-based spider (AJAX Spider) can discover these links.
fetch('api/products.php')
  .then(r => r.json())
  .then(products => {
    const container = document.getElementById('product-list');
    container.innerHTML = products.map(p => `
      <div class="product-card">
        <h3>${p.name}</h3>
        <p>$${parseFloat(p.price).toFixed(2)}</p>
        <a class="btn" href="product.php?id=${p.id}">View Product</a>
      </div>
    `).join('');
  })
  .catch(() => {
    document.getElementById('product-list').innerHTML =
      '<p class="alert error">Could not load products.</p>';
  });
</script>
</body>
</html>
