<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Библиотечная система | Читальный зал</title>
    <link rel="stylesheet" href="<?= app()->route->getUrl('/public/assets/css/style.css') ?>">
    <link rel="shortcut icon" href="<?= app()->route->getUrl('/assets/favicon.ico') ?>" type="image/x-icon">
</head>
<body>
<header>
    <nav>
        <div style="font-weight: bold; font-size: 1.2em;">
            Библиотечная система
        </div>
        <div>
            <a href="<?= app()->route->getUrl('/library/catalog') ?>">Каталог</a>
            <a href="<?= app()->route->getUrl('/library/search') ?>">Поиск по QR</a>

            <?php if (app()->auth::check()): ?>
                <?php if (app()->auth::check() && app()->auth::user()->role === 'librarian'): ?>
                    <a href="<?= app()->route->getUrl('/library/reserve') ?>">Бронировать</a>
                    <a href="<?= app()->route->getUrl('/library/active-bookings') ?>">Выдачи</a>
                    <a href="<?= app()->route->getUrl('/library/readers') ?>">Читатели</a>
                    <a href="<?= app()->route->getUrl('/library/add-book') ?>">Добавить книгу</a>
                <?php else: ?>
                    <a href="<?= app()->route->getUrl('/profile') ?>">Личный кабинет</a>
                <?php endif; ?>
                <a href="<?= app()->route->getUrl('/logout') ?>">Выход (<?= htmlspecialchars(app()->auth::user()->name) ?>)</a>
            <?php else: ?>
                <a href="<?= app()->route->getUrl('/login') ?>">Вход</a>
                <a href="<?= app()->route->getUrl('/signup') ?>">Регистрация</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
        </div>
    </nav>
</header>
<main>
    <?= $content ?? '<div class="info">Добро пожаловать в библиотеку!</div>' ?>
</main>

<footer style="text-align: center; padding: 20px; color: white; margin-top: 40px;">
    <p>&copy; <?= date('Y') ?> Библиотечная система. Все права защищены.</p>
</footer>

<script>
    // Автоматическое скрытие сообщений через 5 секунд
    setTimeout(() => {
        document.querySelectorAll('.success, .error, .info, .warning').forEach(msg => {
            msg.style.opacity = '0';
            setTimeout(() => msg.remove(), 300);
        });
    }, 5000);
</script>
</body>
</html>