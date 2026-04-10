<h1>Редактирование книги</h1>

<?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<form method="POST" class="edit-book-form">
    <div class="form-group">
        <label>Название книги <span class="required">*</span></label>
        <input type="text" name="book_title" value="<?= htmlspecialchars($book->book_title) ?>" required>
    </div>

    <div class="form-group">
        <label>Автор <span class="required">*</span></label>
        <input type="text" name="author" value="<?= htmlspecialchars($book->author) ?>" required>
    </div>

    <div class="form-group">
        <label>Год издания</label>
        <input type="number" name="year" value="<?= $book->year ?>" min="1000" max="<?= date('Y') ?>">
    </div>

    <div class="form-group">
        <label>QR-код <span class="required">*</span></label>
        <input type="text" name="qr_code" value="<?= htmlspecialchars($book->qr_code) ?>" required>
        <small class="hint">Уникальный идентификатор экземпляра</small>
    </div>

    <div class="form-group">
        <label>Место хранения <span class="required">*</span></label>
        <input type="text" name="shelf_location" value="<?= htmlspecialchars($book->shelf_location) ?>" required>
        <small class="hint">Например: Стеллаж А-1, Зал №2</small>
    </div>

    <div class="form-group">
        <label>Статус</label>
        <select name="status">
            <option value="in_hall" <?= $book->status === 'in_hall' ? 'selected' : '' ?>>В зале</option>
            <option value="reserved" <?= $book->status === 'reserved' ? 'selected' : '' ?>>Забронирована</option>
            <option value="issued" <?= $book->status === 'issued' ? 'selected' : '' ?>>Выдана</option>
        </select>
    </div>

    <div class="form-group">
        <label class="checkbox-label">
            <input type="checkbox" name="has_electronic_version" value="1" <?= $book->has_electronic_version ? 'checked' : '' ?>>
            Есть электронная версия
        </label>
    </div>

    <div class="form-group electronic-link-group" style="display: <?= $book->has_electronic_version ? 'block' : 'none' ?>;">
        <label>Ссылка на электронную версию</label>
        <input type="url" name="electronic_link" value="<?= htmlspecialchars($book->electronic_link) ?>" placeholder="https://example.com/book.pdf">
        <small class="hint">Ссылка на PDF файл или страницу с электронной версией</small>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn-success">Сохранить изменения</button>
        <a href="/library/catalog" class="btn">Отмена</a>
    </div>
</form>

<style>
    .required {
        color: var(--danger);
        font-weight: bold;
    }

    .hint {
        font-size: 0.85em;
        color: var(--gray);
        margin-top: 5px;
        display: block;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-weight: normal;
    }

    .checkbox-label input {
        width: auto;
        margin: 0;
    }

    .form-actions {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }

    @media (max-width: 768px) {
        .form-actions {
            flex-direction: column;
        }

        .form-actions button, .form-actions a {
            width: 100%;
        }
    }
</style>

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