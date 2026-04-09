<h1>Бронирование книги</h1>

<?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<form method="POST">
    <div class="form-group">
        <label>Выберите книгу:</label>
        <select name="copy_id" required>
            <option value="">-- Выберите экземпляр --</option>
            <?php foreach ($availableCopies as $copy): ?>
                <option value="<?= $copy->copy_id ?>">
                    <?= htmlspecialchars($copy->book_title) ?> - <?= $copy->author ?>
                    (QR: <?= $copy->qr_code ?>, Место: <?= $copy->shelf_location ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Выберите читателя:</label>
        <select name="user_id" required>
            <option value="">-- Выберите читателя --</option>
            <?php foreach ($readers as $reader): ?>
                <option value="<?= $reader->user_id ?>">
                    <?= htmlspecialchars($reader->name) ?> (<?= $reader->login ?>)
                    <?= $reader->reader_card ? ' - Карта: ' . $reader->reader_card : '' ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label>Дата возврата:</label>
        <input type="date" name="due_date" required
               min="<?= date('Y-m-d', strtotime('+7 days')) ?>"
               max="<?= date('Y-m-d', strtotime('+30 days')) ?>">
    </div>

    <button type="submit">Забронировать</button>
</form>