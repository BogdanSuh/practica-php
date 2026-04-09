<h1>Книги читателя: <?= htmlspecialchars($user->name) ?></h1>

<a href="/library/readers" class="btn">← Назад к списку</a>

<?php if ($bookings->isEmpty()): ?>
    <div class="info">У читателя нет книг</div>
<?php else: ?>
    <div class="table-responsive">
        <table>
            <thead>
            <tr>
                <th>Книга</th>
                <th>Автор</th>
                <th>Статус</th>
                <th>Дата бронирования</th>
                <th>Дата выдачи</th>
                <th>Срок возврата</th>
                <th>Дата возврата</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($bookings as $booking): ?>
                <tr>
                    <td><?= htmlspecialchars($booking->copy->book_title) ?></td>
                    <td><?= htmlspecialchars($booking->copy->author) ?></td>
                    <td>
                        <?php if ($booking->status === 'reserved'): ?>
                            <span class="status status-reserved">Забронирована</span>
                        <?php elseif ($booking->status === 'issued'): ?>
                            <span class="status status-issued">Выдана</span>
                        <?php else: ?>
                            <span class="status status-in-hall">Возвращена</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d.m.Y', strtotime($booking->booking_date)) ?></td>
                    <td><?= $booking->issue_date ? date('d.m.Y', strtotime($booking->issue_date)) : '-' ?></td>
                    <td><?= $booking->due_date ? date('d.m.Y', strtotime($booking->due_date)) : '-' ?></td>
                    <td><?= $booking->return_date ? date('d.m.Y', strtotime($booking->return_date)) : '-' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>