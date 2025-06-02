<?php
session_start();

require_once __DIR__ . '/database/db.php';
// Підключаємо файл з обліковими даними для логіна
// Шлях змінено з '/data/credential.php' на '/database/credential.php'
$credentials = require_once __DIR__ . '/database/credential.php';

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

// --- Обробка POST-запитів для кошика та логіна ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Логіка додавання до кошика
    if (isset($_POST['add_to_cart'])) {
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

    // Логіка видалення з кошика
    if (isset($_POST['remove_item'])) {
        $productIdToRemove = filter_input(INPUT_POST, 'remove_item_id', FILTER_VALIDATE_INT);
        if ($productIdToRemove && isset($_SESSION['cart'][$productIdToRemove])) {
            unset($_SESSION['cart'][$productIdToRemove]); 
        }
        header('Location: index.php?page=cart'); 
        exit();
    }

    // Логіка очищення кошика
    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = []; 
        header('Location: index.php?page=cart'); 
        exit();
    }

    // --- Логіка логіна (обробка POST-запиту з форми логіна) ---
    if (isset($_POST['login_submit'])) {
        $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS);
        $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);

        // Перевірка облікових даних
        if ($username === $credentials['userName'] && $password === $credentials['password']) {
            $_SESSION['user_logged_in'] = true;
            $_SESSION['username'] = $username;
            $_SESSION['login_time'] = date('Y-m-d H:i:s'); // Запис часу логіна
            header('Location: index.php?page=products'); // Перенаправлення на сторінку товарів після успішного логіна
            exit();
        } else {
            $_SESSION['login_error'] = "Неправильний логін або пароль.";
            header('Location: index.php?page=login'); // Повернення на сторінку логіна з повідомленням про помилку
            exit();
        }
    }
}

// --- Логіка логаута (обробка GET-запиту) ---
if (isset($_GET['page']) && $_GET['page'] === 'logout') {
    session_unset();   // Видаляє всі змінні сесії
    session_destroy(); // Знищує сесію
    header('Location: index.php?page=home'); // Перенаправлення на головну сторінку
    exit();
}

// --- Визначення поточної сторінки та обмеження доступу ---
$page = $_GET['page'] ?? 'home';

// Сторінки, доступні без авторизації
$publicPages = ['home', 'login', 'page404']; // page404 додано до публічних

// Перевіряємо, чи користувач не авторизований і намагається отримати доступ до закритої сторінки
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    if (!in_array($page, $publicPages)) {
        // Якщо сторінка не є публічною, примусово показуємо page404 з повідомленням про логін
        $page = 'page404_access_denied'; // Використовуємо спеціальну "сторінку", яка буде оброблена в switch
    }
}

// --- Кінець глобальної логіки ---

// Підключаємо шапку сайту
require_once 'header.php';

// Логіка перемикання сторінок
switch ($page) { 
    case "home" :
        require_once ( "pages/home.php" ); 
        break; 
    case "cart" :
        require_once ( "pages/cart.php" ); 
        break; 
    case "products" :
        require_once ( "pages/products.php" ); 
        break; 
    case "login" : // Додано сторінку логіна
        require_once ( "pages/login.php" ); 
        break;
    case "profile" : // Додано сторінку профілю
        require_once ( "pages/profile.php" ); 
        break;
    case "page404_access_denied": // Обробка випадку обмеженого доступу
    case "404": // Якщо 404 запитується напряму
    default :
        require_once ( "pages/page404.php" ); 
        break ;
}

// Підключаємо підвал сайту
require_once 'footer.php';
?>