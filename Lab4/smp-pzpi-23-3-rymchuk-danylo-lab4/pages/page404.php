<h1>404 - Сторінку не знайдено</h1>
<p>На жаль, запитана сторінка не існує.</p>
<?php if (!isset($_SESSION['user_logged_in'])): ?>
    <p>Для перегляду контенту сайту, будь ласка, <a href="index.php?page=login">авторизуйтеся</a>.</p>
<?php endif; ?>