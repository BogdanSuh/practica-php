<h1>Добавление нового читателя</h1>

<?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
        <label>ФИО читателя *</label>
        <input type="text" name="name" required placeholder="Иванов Иван Иванович">
    </div>

    <div class="form-group">
        <label>Логин *</label>
        <input type="text" name="login" required placeholder="ivanov">
        <small>Уникальный логин для входа в систему</small>
    </div>

    <div class="form-group">
        <label>Пароль *</label>
        <input type="password" name="password" required placeholder="********">
    </div>

    <div class="flex">
        <button type="submit">Добавить читателя</button>
        <a href="/library/readers" class="btn">Отмена</a>
    </div>
</form>