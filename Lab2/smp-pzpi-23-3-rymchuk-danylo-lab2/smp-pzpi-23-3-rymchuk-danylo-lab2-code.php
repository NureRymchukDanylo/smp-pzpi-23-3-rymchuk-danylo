<?php

// === КОНСТАНТИ ===
const MIN_AGE = 7;
const MAX_AGE = 150;
const MAX_QTY = 99;

// === КАТАЛОГ ТОВАРІВ ===
$productCatalog = [
    1 => ["name" => "Кава зернова", "price" => 145],
    2 => ["name" => "Чай зелений", "price" => 98],
    3 => ["name" => "Мед липовий", "price" => 210],
    4 => ["name" => "Цукор білий", "price" => 34],
    5 => ["name" => "Олія соняшникова", "price" => 69],
    6 => ["name" => "Борошно пшеничне", "price" => 27],
    7 => ["name" => "Макарони", "price" => 36]
];

$basket = [];
$userProfile = ["name" => "", "age" => 0];

// === УТИЛІТИ ===
if (!function_exists('mb_str_pad')) {
    function mb_str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT) {
        $diff = $pad_length - preg_match_all('/./us', $input);
        if ($diff > 0) {
            if ($pad_type === STR_PAD_RIGHT) {
                return $input . str_repeat($pad_string, $diff);
            } elseif ($pad_type === STR_PAD_LEFT) {
                return str_repeat($pad_string, $diff) . $input;
            } else {
                $left = floor($diff / 2);
                $right = $diff - $left;
                return str_repeat($pad_string, $left) . $input . str_repeat($pad_string, $right);
            }
        }
        return $input;
    }
}

function printLine($text = "") {
    echo $text . "\n";
}

function getMaxNameLength($productCatalog) {
    return max(array_map(fn($p) => preg_match_all('/./us', $p['name']), $productCatalog));
}

// === ВИВІД МЕНЮ ТА ТОВАРІВ ===
function showMainMenu() {
    printLine("################################");
    printLine("# ПРОДОВОЛЬЧИЙ МАГАЗИН \"ВЕСНА\" #");
    printLine("################################");
    printLine("1 Вибрати товари");
    printLine("2 Отримати пiдсумковий рахунок");
    printLine("3 Налаштувати свiй профiль");
    printLine("0 Вийти з програми");
    printLine("Введiть команду: ");
}

function showProductList($productCatalog) {
    $maxNameLength = getMaxNameLength($productCatalog) + 2;
    printLine("№  " . mb_str_pad("НАЗВА", $maxNameLength) . "ЦІНА");

    foreach ($productCatalog as $id => $product) {
        printLine(mb_str_pad($id, 3) . mb_str_pad($product['name'], $maxNameLength) . mb_str_pad($product['price'], 6));
    }

    printLine("   " . str_repeat("-", 11));
    printLine("0  ПОВЕРНУТИСЯ");
    printLine("Виберiть товар: ");
}

// === ПАРАМЕТРИ КОРИСТУВАЧА ===
function setupUserProfile(&$userProfile) {
    do {
        printLine("Ваше iм'я: ");
        $name = trim(readline());

        if (!preg_match('/^[a-zA-Zа-яА-ЯіІїЇєЄ][a-zA-Zа-яА-ЯіІїЇєЄ\'\- ]*$/u', $name)) {
            printLine("ПОМИЛКА! Імʼя має складатися хоча б з однієї літери та може містити лише літери, апостроф «'», дефіс «-», пробіл.\n");
        }
    } while (!preg_match('/^[a-zA-Zа-яА-ЯіІїЇєЄ][a-zA-Zа-яА-ЯіІїЇєЄ\'\- ]*$/u', $name));

    do {
        printLine("Ваш вiк: ");
        $age = (int)trim(fgets(STDIN));
        if ($age < MIN_AGE || $age > MAX_AGE) {
            printLine("ПОМИЛКА! Вік повинен бути від " . MIN_AGE . " до " . MAX_AGE . "\n");
        }
    } while ($age < MIN_AGE || $age > MAX_AGE);

    $userProfile['name'] = $name;
    $userProfile['age'] = $age;

    printLine("\nВаше імʼя: $name");
    printLine("Ваш вік: $age");
}

// === ДІЇ З КОШИКОМ ===
function showBasket($basket, $productCatalog) {
    if (empty($basket)) {
        printLine("КОШИК ПОРОЖНІЙ");
        return;
    }

    $maxNameLength = getMaxNameLength($productCatalog) + 2;
    printLine("У КОШИКУ:");
    printLine(mb_str_pad("НАЗВА", $maxNameLength) . "КІЛЬКІСТЬ");

    foreach ($basket as $id => $qty) {
        printLine(mb_str_pad($productCatalog[$id]['name'], $maxNameLength) . $qty);
    }

    printLine();
}

function chooseProduct($productCatalog, &$basket) {
    while (true) {
        showProductList($productCatalog);
        $choice = trim(fgets(STDIN));

        if ($choice === '0') break;

        $choice = (int)$choice;

        if (!isset($productCatalog[$choice])) {
            printLine("ПОМИЛКА! ВКАЗАНО НЕПРАВИЛЬНИЙ НОМЕР ТОВАРУ\n");
            continue;
        }

        printLine("\nВибрано: " . $productCatalog[$choice]['name']);
        printLine("Введiть кiлькiсть, штук: ");
        $qty = trim(fgets(STDIN));

        if (!is_numeric($qty) || (int)$qty < 0 || (int)$qty > MAX_QTY) {
            printLine("ПОМИЛКА! Кiлькiсть повинна бути вiд 0 до " . MAX_QTY . "\n");
            showBasket($basket, $productCatalog);
            continue;
        }

        $qty = (int)$qty;

        if ($qty === 0) {
            if (isset($basket[$choice])) {
                printLine("ВИДАЛЯЮ З КОШИКА");
                unset($basket[$choice]);
            }
        } else {
            $basket[$choice] = $qty;
        }

        printLine();
        showBasket($basket, $productCatalog);
    }
}

function getFinalBill($productCatalog, $basket) {
    if (empty($basket)) {
        printLine("КОШИК ПОРОЖНІЙ");
        return;
    }

    $relevantProducts = array_intersect_key($productCatalog, $basket);
    $maxNameLength = getMaxNameLength($relevantProducts) + 2;

    printLine("№  " . mb_str_pad("НАЗВА", $maxNameLength) . "ЦІНА  КІЛЬКІСТЬ  ВАРТІСТЬ");

    $total = 0;
    $i = 1;

    foreach ($basket as $id => $qty) {
        $name = $productCatalog[$id]['name'];
        $price = $productCatalog[$id]['price'];
        $cost = $price * $qty;

        printLine(
            mb_str_pad($i++, 3) .
            mb_str_pad($name, $maxNameLength) .
            mb_str_pad($price, 6) .
            mb_str_pad($qty, 11) .
            $cost
        );

        $total += $cost;
    }

    printLine("РАЗОМ ДО СПЛАТИ: $total");
}

// === ГОЛОВНИЙ ЦИКЛ ===
while (true) {
    showMainMenu();
    $command = trim(fgets(STDIN));
    printLine();

    switch ($command) {
        case '0':
            exit;
        case '1':
            chooseProduct($productCatalog, $basket);
            break;
        case '2':
            getFinalBill($productCatalog, $basket);
            break;
        case '3':
            setupUserProfile($userProfile);
            break;
        default:
            printLine("ПОМИЛКА! Введiть правильну команду\n");
    }

    printLine();
}
?>
