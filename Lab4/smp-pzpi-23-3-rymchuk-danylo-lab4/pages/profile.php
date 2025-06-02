<?php
// Шлях до файлу, де зберігаються дані профілю.
// Зверніть увагу: '__DIR__' - це папка 'pages',
// '/../database/user_profile.php' - це крок вгору до кореня, потім до 'database'.
$profile_data_file = __DIR__ . '/../database/user_profile.php';

// Ініціалізуємо змінну для даних профілю
$profile_data = [];

// Перевіряємо, чи існує файл з даними профілю, і якщо так, завантажуємо їх.
// 'require' тут використовується, тому що user_profile.php повертає масив.
if (file_exists($profile_data_file)) {
    $profile_data = require $profile_data_file;
} else {
    // Якщо файл ще не існує, ініціалізуємо його дефолтними значеннями
    // та створюємо файл, щоб уникнути помилок при першому завантаженні.
    $profile_data = [
        'first_name' => '',
        'last_name' => '',
        'dob' => '', // Date of Birth (YYYY-MM-DD)
        'bio' => '', // Biography or brief info
        'profile_picture' => 'uploads/default.png' // Шлях до дефолтного фото
    ];
    // Створюємо файл з початковими даними
    file_put_contents($profile_data_file, '<?php return ' . var_export($profile_data, true) . ';');
}

$errors = [];
$messages = [];

// --- Обробка POST-запиту для збереження профілю ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    // Отримання та фільтрація даних з форми
    $first_name = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS);
    $last_name = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS);
    $dob = filter_input(INPUT_POST, 'dob', FILTER_SANITIZE_SPECIAL_CHARS);
    $bio = filter_input(INPUT_POST, 'bio', FILTER_SANITIZE_SPECIAL_CHARS);

    // --- Валідація полів ---
    if (empty($first_name) || empty($last_name) || empty($dob) || empty($bio)) {
        $errors[] = "Усі поля повинні бути заповнені.";
    }

    if (mb_strlen($first_name) < 2) {
        $errors[] = "Ім'я повинно містити принаймні 2 символи.";
    }
    if (mb_strlen($last_name) < 2) {
        $errors[] = "Прізвище повинно містити принаймні 2 символи.";
    }

    // Валідація віку (не менше 16 років)
    if (!empty($dob)) {
        try {
            $birthDate = new DateTime($dob);
            $today = new DateTime();
            $age = $today->diff($birthDate)->y;
            if ($age < 16) {
                $errors[] = "Вам має бути не менше 16 років.";
            }
        } catch (Exception $e) {
            $errors[] = "Невірний формат дати народження.";
        }
    } else {
        $errors[] = "Дата народження недійсна.";
    }

    if (mb_strlen($bio) < 50) {
        $errors[] = "Стисла інформація повинна містити не менше 50 символів.";
    }

    // --- Обробка завантаження фото ---
    // Поточний шлях до фото або дефолтний
    $uploaded_file_path = $profile_data['profile_picture']; 
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_picture'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5 MB

        // Перевірка типу файлу
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Недопустимий тип файлу. Дозволено тільки JPG, PNG, GIF.";
        }
        // Перевірка розміру файлу
        if ($file['size'] > $max_size) {
            $errors[] = "Розмір файлу перевищує 5 МБ.";
        }
        // Перевірка на реальне зображення (можна додати більш глибоку перевірку)
        $image_info = getimagesize($file['tmp_name']);
        if ($image_info === false) {
            $errors[] = "Завантажений файл не є дійсним зображенням.";
        }


        if (empty($errors)) {
            // Шлях для завантаження файлу.
            // 'uploads/' знаходиться в корені проекту (поруч з index.php).
            $upload_dir = 'uploads/'; 
            $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $new_file_name = uniqid('profile_') . '.' . $file_extension; // Унікальне ім'я файлу
            $destination = $upload_dir . $new_file_name;

            // Переміщення завантаженого файлу з тимчасового розташування на постійне
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $uploaded_file_path = $destination;
                $messages[] = "Фото успішно завантажено!";
                
                // Видаляємо старе фото, якщо воно не дефолтне і існує
                if (isset($profile_data['profile_picture']) && $profile_data['profile_picture'] !== 'uploads/default.png' && file_exists($profile_data['profile_picture'])) {
                    unlink($profile_data['profile_picture']);
                }
            } else {
                $errors[] = "Помилка при завантаженні фото. Перевірте права на запис в папку 'uploads'.";
            }
        }
    }

    // --- Збереження даних профілю, якщо немає помилок валідації ---
    if (empty($errors)) {
        // Оновлюємо дані профілю для збереження
        $profile_data['first_name'] = $first_name;
        $profile_data['last_name'] = $last_name;
        $profile_data['dob'] = $dob;
        $profile_data['bio'] = $bio;
        $profile_data['profile_picture'] = $uploaded_file_path;

        // Зберігаємо оновлений масив у файл database/user_profile.php
        // var_export() дозволяє зберегти масив як валідний PHP-код.
        $file_content = '<?php return ' . var_export($profile_data, true) . ';';
        
        if (file_put_contents($profile_data_file, $file_content) !== false) {
            $messages[] = "Профіль успішно оновлено!";
        } else {
            $errors[] = "Помилка при збереженні даних профілю. Перевірте права на запис до файлу.";
        }
    }
}

// --- Відображення форми профілю ---
?>

<h2>Мій Профіль</h2>

<?php
// Виводимо повідомлення про помилки (якщо є)
foreach ($errors as $error) {
    echo '<p style="color: red;">' . htmlspecialchars($error) . '</p>';
}
// Виводимо повідомлення про успіх (якщо є)
foreach ($messages as $msg) {
    echo '<p style="color: green;">' . htmlspecialchars($msg) . '</p>';
}
?>

<div style="text-align: center; margin-bottom: 20px;">
    <img src="<?php echo htmlspecialchars($profile_data['profile_picture']); ?>" alt="Profile Picture" style="max-width: 200px; border-radius: 50%; border: 2px solid #a678b2;">
</div>

<form action="index.php?page=profile" method="POST" enctype="multipart/form-data">
    <div>
        <label for="first_name">Ім'я:</label>
        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($profile_data['first_name'] ?? ''); ?>" required>
    </div>
    <div>
        <label for="last_name">Прізвище:</label>
        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($profile_data['last_name'] ?? ''); ?>" required>
    </div>
    <div>
        <label for="dob">Дата народження:</label>
        <input type="date" id="dob" name="dob" value="<?php echo htmlspecialchars($profile_data['dob'] ?? ''); ?>" required>
    </div>
    <div>
        <label for="bio">Коротка інформація:</label>
        <textarea id="bio" name="bio" rows="5" required><?php echo htmlspecialchars($profile_data['bio'] ?? ''); ?></textarea>
    </div>
    <div>
        <label for="profile_picture">Фото профілю:</label>
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
    </div>
    <button type="submit" name="save_profile">Зберегти</button>
</form>
