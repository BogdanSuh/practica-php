<h1>Каталог книг</h1>
<?php if (isset($_SESSION['success'])): ?>
    <div class="success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php if (app()->auth::check() && app()->auth::user()->role === 'librarian'): ?>
    <div style="margin-bottom: 20px;">
        <a href="/library/add-book" class="btn btn-success">Добавить новую книгу</a>
    </div>
<?php endif; ?>

<form method="GET" class="search-form">
    <div class="form-group">
        <input type="text" name="search" placeholder="Поиск по названию или автору..."
               value="<?= htmlspecialchars($search ?? '') ?>">
    </div>
    <button type="submit">Найти</button>
</form>

<?php if (empty($groupedBooks)): ?>
    <div class="info">Книги не найдены</div>
<?php else: ?>
    <div class="grid">
        <?php foreach ($groupedBooks as $book): ?>
            <div class="book-card">
                <h3><?= htmlspecialchars($book['title']) ?></h3>
                <p>Автор: <?= htmlspecialchars($book['author']) ?></p>
                <p>Доступно экземпляров:
                    <strong><?= count(array_filter($book['copies'], fn($c) => $c->status === 'in_hall')) ?></strong>
                </p>

                <?php if (app()->auth::check() && app()->auth::user()->role === 'librarian'): ?>
                    <div class="flex" style="margin-top: 10px;">
                        <?php foreach ($book['copies'] as $copy): ?>
                            <small>
                                <a href="/library/edit-book?copy_id=<?= $copy->copy_id ?>">Редактировать</a> |
                                <a href="/library/delete-book?copy_id=<?= $copy->copy_id ?>"
                                   onclick="return confirm('Удалить эту книгу?')">Удалить</a>
                            </small>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (app()->auth::check() && app()->auth::user()->role === 'reader'): ?>
                    <?php
                    $availableCopy = null;
                    foreach ($book['copies'] as $copy) {
                        if ($copy->status === 'in_hall') {
                            $availableCopy = $copy;
                            break;
                        }
                    }
                    ?>
                    <?php if ($availableCopy): ?>
                        <form method="POST" action="<?= app()->route->getUrl('/library/reader-reserve') ?>">
                            <input type="hidden" name="copy_id" value="<?= $availableCopy->copy_id ?>">
                            <button type="submit" class="btn-reserve"
                                    onclick="return confirm('Забронировать книгу "<?= htmlspecialchars($book['title']) ?>"?')">
                            Забронировать
                            </button>
                        </form>
                    <?php else: ?>
                        <p class="status status-reserved">Нет доступных экземпляров</p>
                    <?php endif; ?>
                <?php endif; ?>

                <details style="margin-top: 15px;">
                    <summary>Список экземпляров (<?= count($book['copies']) ?>)</summary>
                    <ul style="margin-top: 10px; list-style: none;">
                        <?php foreach ($book['copies'] as $copy): ?>
                            <li style="padding: 8px; border-bottom: 1px solid var(--light);">
                                <strong>QR:</strong> <?= $copy->qr_code ?><br>
                                <strong>Место:</strong> <?= $copy->shelf_location ?><br>
                                <strong>Статус:</strong>
                                <span class="status status-<?= $copy->status ?>">
                                    <?= $copy->status === 'in_hall' ? 'В зале' : ($copy->status === 'reserved' ? 'Забронирована' : 'Выдана') ?>
                                </span>
                                <?php if ($copy->has_electronic_version && $copy->electronic_link): ?>
                                    <br><a href="<?= $copy->electronic_link ?>" target="_blank">Электронная версия</a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

