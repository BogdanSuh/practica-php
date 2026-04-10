<h1>Управление выдачами</h1>

<?php if (isset($_SESSION['success'])): ?>
    <div class="success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php if ($bookings->isEmpty()): ?>
    <div class="info">Нет активных бронирований и выдач</div>
<?php else: ?>
    <div class="table-responsive">
        <table border="1" cellpadding="10">
            <thead>
            <tr >
                <th>Книга</th>
                <th>Читатель</th>
                <th>Статус</th>
                <th>Дата бронирования</th>
                <th>Дата выдачи</th>
                <th>Срок возврата</th>
                <th>Действие</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($booking->copy->book_title) ?></strong><br>
                        <small>QR: <?= $booking->copy->qr_code ?></small>
                    </td>
                    <td><?= htmlspecialchars($booking->user->name) ?></td>
                    <td>
                            <span class="status status-<?= $booking->status ?>">
                                <?= $booking->status === 'reserved' ? 'Забронирована' : 'Выдана' ?>
                            </span>
                    </td>
                    <td><?= date('d.m.Y H:i', strtotime($booking->booking_date)) ?></td>
                    <td><?= $booking->issue_date ? date('d.m.Y', strtotime($booking->issue_date)) : '-' ?></td>
                    <td>
                        <?= date('d.m.Y', strtotime($booking->due_date)) ?>
                        <?php if ($booking->isOverdue()): ?>
                            <span class="status status-issued">Просрочена!</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($booking->status === 'reserved'): ?>
                            <form method="POST" action="/practica-php/library/issue" style="display: inline;">
                                <input type="hidden" name="booking_id" value="<?= $booking->booking_id ?>">
                                <button type="submit" class="btn-success">Выдать</button>
                            </form>
                        <?php elseif ($booking->status === 'issued'): ?>
                            <form method="POST" action="/practica-php/library/return" style="display: inline;">
                                <input type="hidden" name="booking_id" value="<?= $booking->booking_id ?>">
                                <button type="submit" class="btn-warning">Вернуть</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>