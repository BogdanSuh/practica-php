<h1>Список читателей</h1>

<?php if (isset($_SESSION['success'])): ?>
    <div class="success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>


<?php if ($readers->isEmpty()): ?>
    <div class="info">Нет зарегистрированных читателей</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="readers-table">
            <thead>
            <tr>
                <th>ID</th>
                <th>ФИО</th>
                <th>Логин</th>
                <th>Номер читательского билета</th>
                <th>Активных книг</th>
                <th>Прочитано книг</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($readers as $reader): ?>
                <tr>
                    <td data-label="ID"><?= $reader->user_id ?></td>
                    <td data-label="ФИО"><?= htmlspecialchars($reader->name) ?></td>
                    <td data-label="Логин"><?= htmlspecialchars($reader->login) ?></td>
                    <td data-label="Читательский билет"><?= htmlspecialchars($reader->reader_card ?? 'Не указан') ?></td>
                    <td data-label="Активных книг">
                        <?php if ($reader->active_books > 0): ?>
                            <span class="status status-issued"><?= $reader->active_books ?></span>
                        <?php else: ?>
                            <span class="status status-in-hall">0</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Прочитано книг"><?= $reader->read_books ?></td>
                    <td data-label="Действия">
                        <a href="/library/reader-books?user_id=<?= $reader->user_id ?>" class="btn btn-sm">Книги</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
