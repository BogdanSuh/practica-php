<h1>Личный кабинет</h1>

<?php if (isset($_SESSION['success'])): ?>
    <div class="success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="profile-info">
    <h2>Добро пожаловать, <?= htmlspecialchars($user->name) ?>!</h2>
    <?php if ($user->reader_card): ?>
        <p>Номер читательского билета: <?= $user->reader_card ?></p>
    <?php endif; ?>
    <p>Дата регистрации: <?= date('d.m.Y') ?></p>
</div>

<h3>Активные бронирования и выдачи</h3>
<?php if ($active->isEmpty()): ?>
    <div class="info">У вас нет активных бронирований</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="active-books-table">
            <thead>
            <tr>
                <th>Книга</th>
                <th>Автор</th>
                <th>Статус</th>
                <th>Дата выдачи</th>
                <th>Срок возврата</th>
                <th>Осталось дней</th>
                <th>Действие</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($active as $item): ?>
                <?php
                $now = new DateTime();
                $dueDate = new DateTime($item->due_date);
                $daysLeft = $now->diff($dueDate)->days;
                if ($dueDate < $now) {
                    $daysLeft = -$daysLeft;
                }
                ?>
                <tr class="<?= $daysLeft < 0 ? 'overdue' : '' ?>">
                    <td data-label="Книга">
                        <strong><?= htmlspecialchars($item->copy->book_title) ?></strong>
                        <br><small>QR: <?= $item->copy->qr_code ?></small>
                    </td>
                    <td data-label="Автор"><?= htmlspecialchars($item->copy->author) ?></td>
                    <td data-label="Статус">
                        <?php if ($item->status === 'reserved'): ?>
                            <span class="status status-reserved">Забронирована (ожидайте выдачи)</span>
                        <?php else: ?>
                            <span class="status status-issued">Выдана на руки</span>
                        <?php endif; ?>
                    </td>
                    <td data-label="Дата выдачи">
                        <?= $item->issue_date ? date('d.m.Y', strtotime($item->issue_date)) : '-' ?>
                    </td>
                    <td data-label="Срок возврата">
                        <?= date('d.m.Y', strtotime($item->due_date)) ?>
                    </td>
                    <td data-label="Осталось дней">
                        <?php if ($item->status === 'issued'): ?>
                            <?php if ($daysLeft < 0): ?>
                                <span class="status status-issued">Просрочена на <?= abs($daysLeft) ?> дн.</span>
                            <?php elseif ($daysLeft <= 3): ?>
                                <span class="status status-warning"><?= $daysLeft ?> дн.</span>
                            <?php else: ?>
                                <span class="status status-in-hall"><?= $daysLeft ?> дн.</span>
                            <?php endif; ?>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td data-label="Действие">
                        <?php if ($item->status === 'issued' && $daysLeft >= 0): ?>
                            <?php
                            // Проверяем, есть ли очередь
                            $hasQueue = \Model\Book::where('copy_id', $item->copy_id)
                                ->where('status', 'reserved')
                                ->where('booking_id', '!=', $item->booking_id)
                                ->exists();
                            ?>
                            <?php if (!$hasQueue): ?>
                                <form method="POST" action="<?= app()->route->getUrl('/profile/extend') ?>" >
                                    <input type="hidden" name="booking_id" value="<?= $item->booking_id ?>">
                                    <select name="days" class="days-select">
                                        <option value="1">1 день</option>
                                        <option value="2">2 дня</option>
                                        <option value="3">3 дня</option>
                                        <option value="4">4 дня</option>
                                        <option value="5">5 дней</option>
                                        <option value="6">6 дней</option>
                                        <option value="7" selected>7 дней</option>
                                    </select>
                                    <button type="submit" class="btn-extend" onclick="return confirm('Продлить книгу на выбранное количество дней?')">
                                        Продлить
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="status status-reserved" style="cursor: help;"
                                      title="На эту книгу есть очередь, продление невозможно">
            Есть очередь
        </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<h3>История чтения</h3>
<?php if ($history->isEmpty()): ?>
    <div class="info">История пуста</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="history-table">
            <thead>
            <tr>
                <th>Книга</th>
                <th>Автор</th>
                <th>Дата выдачи</th>
                <th>Дата возврата</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($history as $item): ?>
                <tr>
                    <td data-label="Книга"><?= htmlspecialchars($item->copy->book_title) ?></td>
                    <td data-label="Автор"><?= htmlspecialchars($item->copy->author) ?></td>
                    <td data-label="Дата выдачи"><?= date('d.m.Y', strtotime($item->issue_date)) ?></td>
                    <td data-label="Дата возврата"><?= date('d.m.Y', strtotime($item->return_date)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>


<script>
    // Добавляем data-label для responsive таблицы
    document.addEventListener('DOMContentLoaded', function() {
        const tables = document.querySelectorAll('.active-books-table, .history-table');
        tables.forEach(table => {
            const headers = table.querySelectorAll('thead th');
            table.querySelectorAll('tbody tr').forEach(row => {
                row.querySelectorAll('td').forEach((cell, index) => {
                    if (headers[index]) {
                        cell.setAttribute('data-label', headers[index].textContent);
                    }
                });
            });
        });
    });
</script>