<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Продовольчий магазин весна</title>
    <link rel="stylesheet" href="assets/css/style.css">
  </head>
  <body>
    <div id="main-container">
      <header>
        <nav class="header-nav">
          <a href="index.php?page=home">Home</a> |
          <a href="index.php?page=products">Products</a> |
          <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true): ?>
              <a href="index.php?page=cart">Cart (<span class="cart-info"><?php echo getCartTotalQuantity(); ?></span>)</a> |
              <a href="index.php?page=profile">Profile</a> |
              <a href="index.php?page=logout">Logout</a>
          <?php else: ?>
              <a href="index.php?page=login">Login</a>
          <?php endif; ?>
        </nav>
      </header>
      <main>