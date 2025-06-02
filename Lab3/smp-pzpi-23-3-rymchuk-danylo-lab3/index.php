<?php
session_start();

require_once __DIR__ . '/database/db.php';

// --- Глобальна логіка, доступна для всіх сторінок ---
try {
    $stmt = $pdo->query("SELECT id, name, price FROM Products ORDER BY id");
    $allProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $products = [];
    foreach ($allProducts as $product) {
        $products[$product['id']] = $product;
    }
} catch (PDOException $e) {
    die("Помилка при отриманні товарів з бази даних: " . $e->getMessage());
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function getCartTotalQuantity() {
    return array_sum($_SESSION['cart']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    if ($productId && isset($products[$productId]) && $quantity > 0) {
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }
    }
    header('Location: index.php?page=products');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $productIdToRemove = filter_input(INPUT_POST, 'remove_item_id', FILTER_VALIDATE_INT);
    if ($productIdToRemove && isset($_SESSION['cart'][$productIdToRemove])) {
        unset($_SESSION['cart'][$productIdToRemove]); 
    }
    header('Location: index.php?page=cart'); 
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_cart'])) {
    $_SESSION['cart'] = []; 
    header('Location: index.php?page=cart'); 
    exit();
}
// --- Кінець глобальної логіки ---

?>
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
          <a href="index.php?page=cart">Cart (<span class="cart-info"><?php echo getCartTotalQuantity(); ?></span>)</a>
        </nav>
      </header>
      <main>
        <?php
          $page = $_GET['page'] ?? 'home';
          $contentFile = 'pages/' . $page . '.php';

          $allowedPages = ['home', 'products', 'cart']; 
          if (in_array($page, $allowedPages) && file_exists($contentFile)) {
            include $contentFile;
          } else {
            echo '<h1>404 - Сторінку не знайдено</h1>';
          }
        ?>
      </main>
      <footer>
        <nav class="footer-nav">
          <a href="index.php?page=home">Home</a> |
          <a href="index.php?page=products">Products</a> |
          <a href="index.php?page=cart">Cart</a>
        </nav>
      </footer>
    </div>
  </body>
</html>