<h1>Добавление новой книги</h1>

<?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="add-book-form">
    <div class="form-group">
        <label>Обложка книги</label>
        <input type="file" name="cover_image" accept="image/jpeg,image/png,image/gif">
        <small class="hint">JPG, PNG, GIF до 2MB</small>
    </div>
    <div class="form-group">
        <label>Название книги <span class="required">*</span></label>
        <input type="text" name="book_title" required
               placeholder="Введите название книги"
               value="<?= htmlspecialchars($book['book_title'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Автор <span class="required">*</span></label>
        <input type="text" name="author" required
               placeholder="ФИО автора"
               value="<?= htmlspecialchars($book['author'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>Год издания</label>
        <input type="number" name="year"
               placeholder="Год издания (например, 2020)"
               value="<?= htmlspecialchars($book['year'] ?? '') ?>"
               min="1000" max="<?= date('Y') ?>">
    </div>

    <div class="form-group">
        <label>QR-код</label>
        <input type="text" name="qr_code"
               placeholder="Оставьте пустым для автоматической генерации"
               value="<?= htmlspecialchars($book['qr_code'] ?? '') ?>">
        <small class="hint">Если оставить пустым, QR-код сгенерируется автоматически</small>
    </div>

    <div class="form-group">
        <label>Место хранения <span class="required">*</span></label>
        <input type="text" name="shelf_location" required
               placeholder="Стеллаж А-1, Зал №2, полка 3"
               value="<?= htmlspecialchars($book['shelf_location'] ?? '') ?>">
        <small class="hint">Укажите точное местонахождение книги</small>
    </div>

    <div class="form-group">
        <label class="checkbox-label">
            <input type="checkbox" name="has_electronic_version" value="1"
                <?= isset($book['has_electronic_version']) && $book['has_electronic_version'] ? 'checked' : '' ?>>
            Есть электронная версия
        </label>
    </div>

    <div class="form-group electronic-link-group" style="display: none;">
        <label>Ссылка на электронную версию</label>
        <input type="url" name="electronic_link"
               placeholder="https://example.com/book.pdf или /ebooks/book.pdf"
               value="<?= htmlspecialchars($book['electronic_link'] ?? '') ?>">
        <small class="hint">Ссылка на PDF файл или страницу с электронной версией</small>
    </div>

    <div class="form-group">
        <label>Обложка книги</label>
        <input type="file" name="cover_image" accept="image/jpeg,image/png,image/gif">
        <small class="hint">Рекомендуемый размер: до 2MB. Форматы: JPG, PNG, GIF</small>
    </div>

    <div class="form-actions">
        <form method="POST" enctype="multipart/form-data" class="add-book-form">
        <button type="submit" class="btn-success">Добавить книгу</button>
        <a href="/library/catalog" class="btn">Отмена</a>
    </div>
</form>

<script>
    // Показываем/скрываем поле для ссылки на электронную версию
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.querySelector('input[name="has_electronic_version"]');
        const electronicGroup = document.querySelector('.electronic-link-group');

        function toggleElectronicLink() {
            if (checkbox.checked) {
                electronicGroup.style.display = 'block';
            } else {
                electronicGroup.style.display = 'none';
            }
        }

        if (checkbox) {
            checkbox.addEventListener('change', toggleElectronicLink);
            toggleElectronicLink();
        }
    });
</script>