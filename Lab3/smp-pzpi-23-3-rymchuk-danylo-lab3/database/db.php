<?php
$dbFile = __DIR__ . '/../data/shop.sqlite'; 

$dataDir = dirname($dbFile);
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true); 
}

$initDb = !file_exists($dbFile); 

try {
    $pdo = new PDO("sqlite:$dbFile"); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 

    if ($initDb) {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS Products (
                id INTEGER PRIMARY KEY,
                name TEXT NOT NULL,
                price REAL NOT NULL CHECK(price >= 0)
            );
        ");

            $products_initial_data = [
                ['id' => 101, "name" => "Круасан з мигдалем", "price" => 45.00],
                ['id' => 102, "name" => "Капучино", "price" => 60.50],
                ['id' => 103, "name" => "Фруктовий смузі", "price" => 75.00],
                ['id' => 104, "name" => "Торт Наполеон (шматочок)", "price" => 90.00],
                ['id' => 105, "name" => "Сендвіч з куркою", "price" => 80.75],
                ['id' => 106, "name" => "Латте з карамеллю", "price" => 68.00],
                ['id' => 107, "name" => "Морозиво Пломбір", "price" => 35.50],
                ['id' => 108, "name" => "Чізкейк класичний", "price" => 85.00],
                ['id' => 109, "name" => "Свіжовичавлений апельсиновий сік", "price" => 95.00],
                ['id' => 110, "name" => "Вегетаріанський салат", "price" => 120.00]
            ];

        $stmt = $pdo->prepare("INSERT INTO Products (id, name, price) VALUES (:id, :name, :price)");

        foreach ($products_initial_data as $product) {
            $stmt->execute([
                ':id' => $product['id'],
                ':name' => $product['name'],
                ':price' => $product['price']
            ]);
        }
    }
} catch (PDOException $e) {
    die("Помилка підключення або ініціалізації бази даних: " . $e->getMessage());
}