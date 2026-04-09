<h2>Регистрация нового пользователя</h2>

<?php if (!empty($message)): ?>
    <div class="error"><?= $message ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<form method="post">
    <div class="form-group">
        <label>Имя *</label>
        <input type="text" name="name" required placeholder="Введите ваше имя">
    </div>

    <div class="form-group">
        <label>Логин *</label>
        <input type="text" name="login" required placeholder="Придумайте логин">
        <small>Логин должен быть уникальным</small>
    </div>

    <div class="form-group">
        <label>Пароль *</label>
        <input type="password" name="password" required placeholder="Придумайте пароль">
        <small>Пароль должен содержать не менее 6 символов</small>
    </div>

    <button type="submit">Зарегистрироваться</button>
</form>

<div class="text-center mt-20">
    <p>Уже есть аккаунт? <a href="/login">Войти</a></p>
</div>
