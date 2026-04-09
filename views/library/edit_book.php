<h1>Редактирование книги</h1>

<?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
        <label>Название книги</label>
        <input type="text" name="book_title" value="<?= htmlspecialchars($book->book_title) ?>" required>
    </div>

    <div class="form-group">
        <label>Автор</label>
        <input type="text" name="author" value="<?= htmlspecialchars($book->author) ?>" required>
    </div>

    <div class="form-group">
        <label>Год издания</label>
        <input type="number" name="year" value="<?= $book->year ?>">
    </div>

    <div class="form-group">
        <label>QR-код</label>
        <input type="text" name="qr_code" value="<?= htmlspecialchars($book->qr_code) ?>" required>
    </div>

    <div class="form-group">
        <label>Место хранения</label>
        <input type="text" name="shelf_location" value="<?= htmlspecialchars($book->shelf_location) ?>" required>
    </div>

    <div class="form-group">
        <label>
            <input type="checkbox" name="has_electronic_version" value="1" <?= $book->has_electronic_version ? 'checked' : '' ?>>
            Есть электронная версия
        </label>
    </div>

    <div class="form-group">
        <label>Ссылка на электронную версию</label>
        <input type="url" name="electronic_link" value="<?= htmlspecialchars($book->electronic_link) ?>">
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-success">Сохранить</button>
        <a href="/library/catalog" class="btn">Отмена</a>
    </div>
</form>