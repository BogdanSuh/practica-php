<h1>Поиск по QR-коду</h1>

<form method="POST">
    <div class="form-group">
        <label>Введите QR-код:</label>
        <input type="text" name="qr_code" placeholder="QR-001" required>
    </div>
    <button type="submit">Найти</button>
</form>

<?php if ($message): ?>
    <div class="error"><?= $message ?></div>
<?php endif; ?>

<?php if ($copy): ?>
    <div class="book-info">
        <h2>Информация об экземпляре</h2>
        <p><strong>Название:</strong> <?= htmlspecialchars($copy->book_title) ?></p>
        <p><strong>Автор:</strong> <?= htmlspecialchars($copy->author) ?></p>
        <p><strong>Год издания:</strong> <?= $copy->year ?? 'Не указан' ?></p>
        <p><strong>QR-код:</strong> <?= $copy->qr_code ?></p>
        <p><strong>Место хранения:</strong> <?= $copy->shelf_location ?></p>
        <p><strong>Статус:</strong>
            <?php if ($copy->status === 'in_hall'): ?>
                <span >В зале</span>
            <?php elseif ($copy->status === 'reserved'): ?>
                <span >Забронирована</span>
            <?php else: ?>
                <span >Выдана</span>
            <?php endif; ?>
        </p>

        <?php if ($copy->has_electronic_version && $copy->electronic_link): ?>
            <p><strong>Электронная версия:</strong> <a href="<?= $copy->electronic_link ?>" target="_blank">Скачать</a></p>
        <?php endif; ?>

        <?php if ($booking): ?>
            <div class="booking-info">
                <h3>Информация о бронировании</h3>
                <p><strong>Читатель:</strong> <?= htmlspecialchars($booking->user->name) ?></p>
                <p><strong>Статус:</strong> <?= $booking->status === 'reserved' ? 'Забронирована' : 'Выдана' ?></p>
                <p><strong>Срок возврата:</strong> <?= date('d.m.Y', strtotime($booking->due_date)) ?></p>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>