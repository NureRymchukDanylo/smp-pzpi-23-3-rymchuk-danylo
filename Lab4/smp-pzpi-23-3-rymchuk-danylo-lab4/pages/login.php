<h2>Вхід на сайт</h2>
<?php if (isset($_SESSION['login_error'])): ?>
    <p style="color: red;"><?php echo htmlspecialchars($_SESSION['login_error']); unset($_SESSION['login_error']); ?></p>
<?php endif; ?>

<form action="index.php" method="POST">
    <div>
        <label for="username">Ім'я користувача:</label>
        <input type="text" id="username" name="username" required>
    </div>
    <div>
        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit" name="login_submit">Увійти</button>
</form>