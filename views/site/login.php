<div class="profile-info" style="text-align: center;">
    <h2>Авторизация</h2>
    <p>Войдите в библиотечную систему</p>
</div>

<?php if (!empty($message)): ?>
    <div class="error"><?= $message ?></div>
<?php endif; ?>

<?php if (!app()->auth::check()): ?>
    <form method="post">
        <div class="form-group">
            <input name="csrf_token" type="hidden" value="<?= app()->auth::generateCSRF() ?>"/>
            <label>Логин</label>
            <input type="text" name="login" required placeholder="Введите ваш логин">
        </div>

        <div class="form-group">
            <label>Пароль</label>
            <input type="password" name="password" required placeholder="Введите пароль">
        </div>

        <button type="submit">Войти в систему</button>
    </form>

    <div class="text-center mt-20">
        <p>Нет аккаунта? <a href="/signup">Зарегистрироваться</a></p>
        <p style="font-size: 0.85em; color: var(--gray); margin-top: 10px;">
            Тестовые данные:<br>
            Библиотекарь: admin / admin123<br>
            Читатель: reader / reader123
        </p>
    </div>
<?php endif; ?>