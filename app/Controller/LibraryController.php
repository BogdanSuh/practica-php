<?php
namespace Controller;

use Src\View;
use Src\Request;
use Model\BookCopy;
use Model\Book;
use Model\Users;
use Src\Auth\Auth;

class LibraryController
{
    // Каталог книг (все экземпляры)
    public function catalog(Request $request): string
    {
        $search = $request->get('search');
        $query = BookCopy::query();

        if ($search) {
            $query->where('book_title', 'LIKE', "%{$search}%")
                ->orWhere('author', 'LIKE', "%{$search}%");
        }

        $books = $query->get();

        // Группируем по названию книг
        $groupedBooks = [];
        foreach ($books as $book) {
            $key = $book->book_title . '|' . $book->author;
            if (!isset($groupedBooks[$key])) {
                $groupedBooks[$key] = [
                    'title' => $book->book_title,
                    'author' => $book->author,
                    'copies' => []
                ];
            }
            $groupedBooks[$key]['copies'][] = $book;
        }

        return (new View())->render('library.catalog', [
            'groupedBooks' => $groupedBooks,
            'search' => $search
        ]);
    }

    // Поиск по QR-коду
    public function searchByQr(Request $request): string
    {
        $qrCode = $request->get('qr_code');
        $copy = null;
        $booking = null;
        $message = '';

        if ($qrCode) {
            $copy = BookCopy::where('qr_code', $qrCode)->first();

            if (!$copy) {
                $message = 'Экземпляр с таким QR-кодом не найден';
            } else {
                $booking = Book::where('copy_id', $copy->copy_id)
                    ->whereIn('status', ['reserved', 'issued'])
                    ->with('user')
                    ->first();
            }
        }

        return (new View())->render('library.search', [
            'copy' => $copy,
            'booking' => $booking,
            'message' => $message
        ]);
    }

    // Бронирование книги (для библиотекаря)
    public function reserveBook(Request $request): string
    {
        if ($request->method === 'POST') {
            $copyId = $request->get('copy_id');
            $userId = $request->get('user_id');
            $dueDate = $request->get('due_date');

            $copy = BookCopy::find($copyId);
            $user = Users::find($userId);

            if ($copy && $user && $copy->isAvailable()) {
                // Создаем бронирование
                Book::create([
                    'user_id' => $userId,
                    'copy_id' => $copyId,
                    'booking_date' => date('Y-m-d H:i:s'),
                    'due_date' => $dueDate,
                    'status' => 'reserved'
                ]);

                // Обновляем статус экземпляра
                $copy->status = 'reserved';
                $copy->save();

                $_SESSION['success'] = 'Книга успешно забронирована для читателя ' . $user->name;
                app()->route->redirect('/library/catalog');
                return '';
            } else {
                $_SESSION['error'] = 'Ошибка: книга недоступна или читатель не найден';
            }
        }

        // Доступные книги
        $availableCopies = BookCopy::where('status', 'in_hall')->get();
        // Все читатели
        $readers = Users::where('role', 'reader')->get();

        return (new View())->render('library.reserve', [
            'availableCopies' => $availableCopies,
            'readers' => $readers
        ]);
    }

    // Выдача книги (из бронирования)
    public function issueBook(Request $request): string
    {
        $bookingId = $request->get('booking_id');
        $booking = Book::find($bookingId);

        if ($booking && $booking->status === 'reserved') {
            $booking->issue_date = date('Y-m-d H:i:s');
            $booking->status = 'issued';
            $booking->save();

            // Обновляем статус экземпляра
            $copy = BookCopy::find($booking->copy_id);
            $copy->status = 'issued';
            $copy->save();

            $_SESSION['success'] = 'Книга выдана читателю';
        }

        app()->route->redirect('/library/active-bookings');
        return '';
    }

    // Возврат книги
    public function returnBook(Request $request): string
    {
        $bookingId = $request->get('booking_id');
        $booking = Book::find($bookingId);

        if ($booking && $booking->status === 'issued') {
            $booking->return_date = date('Y-m-d H:i:s');
            $booking->status = 'returned';
            $booking->save();

            // Обновляем статус экземпляра
            $copy = BookCopy::find($booking->copy_id);
            $copy->status = 'in_hall';
            $copy->save();

            $_SESSION['success'] = 'Книга возвращена в библиотеку';
        }

        app()->route->redirect('/library/active-bookings');
        return '';
    }

    // Активные бронирования и выдачи (для библиотекаря)
    public function activeBookings(Request $request): string
    {
        $bookings = Book::whereIn('status', ['reserved', 'issued'])
            ->with(['user', 'copy'])
            ->orderBy('booking_date', 'DESC')
            ->get();

        return (new View())->render('library.active-bookings', [
            'bookings' => $bookings
        ]);
    }

    // Продление книги (для читателя)
    // Продление книги (для читателя)
    public function extendBook(Request $request): string
    {
        $bookingId = $request->get('booking_id');
        $days = (int)$request->get('days', 7); // По умолчанию 7 дней

        // Ограничиваем количество дней от 1 до 7
        if ($days < 1) $days = 1;
        if ($days > 7) $days = 7;

        $booking = Book::find($bookingId);

        if ($booking && $booking->user_id === app()->auth->user()->user_id) {
            // Проверяем, есть ли очередь на эту книгу
            $hasQueue = Book::where('copy_id', $booking->copy_id)
                ->where('status', 'reserved')
                ->where('booking_id', '!=', $bookingId)
                ->exists();

            if (!$hasQueue && $booking->status === 'issued') {
                // Продлеваем книгу на выбранное количество дней
                $booking->extend($days);
                $_SESSION['success'] = 'Книга успешно продлена на ' . $days . ' дн. Новый срок возврата: ' . date('d.m.Y', strtotime($booking->due_date));
            } else {
                $_SESSION['error'] = 'Невозможно продлить: есть очередь на эту книгу';
            }
        }

        app()->route->redirect('/profile');
        return '';
    }

    // Личный кабинет читателя
    public function profile(Request $request): string
    {
        $user = app()->auth->user();

        // Активные выдачи и бронирования
        $active = Book::where('user_id', $user->user_id)
            ->whereIn('status', ['reserved', 'issued'])
            ->with('copy')
            ->orderBy('booking_date', 'DESC')
            ->get();

        // История
        $history = Book::where('user_id', $user->user_id)
            ->where('status', 'returned')
            ->with('copy')
            ->orderBy('return_date', 'DESC')
            ->get();

        return (new View())->render('library.profile', [
            'user' => $user,
            'active' => $active,
            'history' => $history
        ]);
    }

    // Список читателей (для библиотекаря)
    // Список читателей (для библиотекаря)
    // Книги конкретного читателя (для библиотекаря)
    public function readerBooks(Request $request): string
    {
        $userId = $request->get('user_id');
        $user = Users::find($userId);

        if (!$user) {
            $_SESSION['error'] = 'Читатель не найден';
            app()->route->redirect('/library/readers');
            return '';
        }

        $bookings = Book::where('user_id', $userId)
            ->with('copy')
            ->orderBy('booking_date', 'DESC')
            ->get();

        return (new View())->render('library.reader_books', [
            'user' => $user,
            'bookings' => $bookings
        ]);
    }

    // Добавление нового читателя библиотекарем
    public function addReader(Request $request): string
    {
        if ($request->method === 'POST') {
            $data = $request->all();

            // Проверка на существование логина
            if (Users::where('login', $data['login'])->exists()) {
                $_SESSION['error'] = 'Пользователь с таким логином уже существует';
                return (new View())->render('library.add_reader');
            }

            // Хешируем пароль
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $data['role'] = 'reader';

            // Генерируем номер читательского билета
            $data['reader_card'] = 'RD-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

            if (Users::create($data)) {
                $_SESSION['success'] = 'Читатель успешно добавлен';
                app()->route->redirect('/library/readers');
                return '';
            }

            $_SESSION['error'] = 'Ошибка при добавлении читателя';
        }

        return (new View())->render('library.add_reader');
    }
    // Добавление новой книги (для библиотекаря)
    public function addBook(Request $request): string
    {
        if ($request->method === 'POST') {
            $data = $request->all();

            // Валидация обязательных полей
            if (empty($data['book_title']) || empty($data['author']) || empty($data['shelf_location'])) {
                $_SESSION['error'] = 'Заполните все обязательные поля';
                return (new View())->render('library.add_book');
            }

            // Генерация QR-кода если не указан
            if (empty($data['qr_code'])) {
                $data['qr_code'] = 'QR-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
            }

            // Проверка на уникальность QR-кода
            if (BookCopy::where('qr_code', $data['qr_code'])->exists()) {
                $_SESSION['error'] = 'Книга с таким QR-кодом уже существует';
                return (new View())->render('library.add_book', ['book' => $data]);
            }

            // Обработка чекбокса электронной версии
            $data['has_electronic_version'] = isset($data['has_electronic_version']) ? 1 : 0;

            // Если нет электронной версии, очищаем ссылку
            if ($data['has_electronic_version'] == 0) {
                $data['electronic_link'] = null;
            }

            // Статус новой книги - "в зале"
            $data['status'] = 'in_hall';

            // Создаем книгу
            if (BookCopy::create($data)) {
                $_SESSION['success'] = 'Книга "' . $data['book_title'] . '" успешно добавлена в каталог';
                app()->route->redirect('/library/catalog');
                return '';
            }

            $_SESSION['error'] = 'Ошибка при добавлении книги';
        }

        return (new View())->render('library.add_book');
    }

// Бронирование книги читателем
    public function readerReserveBook(Request $request): string
    {
        // Отладка
        echo "Метод readerReserveBook вызван<br>";
        echo "Пользователь: " . (app()->auth::user()->name ?? 'Не авторизован') . "<br>";
        echo "copy_id: " . $request->get('copy_id') . "<br>";

        if (!app()->auth::check()) {
            echo "Пользователь не авторизован!<br>";
            exit;
        }
        $copyId = $request->get('copy_id');
        $copy = BookCopy::find($copyId);

        if (!$copy) {
            $_SESSION['error'] = 'Книга не найдена';
            app()->route->redirect('/library/catalog');
            return '';
        }

        // Проверяем, доступна ли книга
        if ($copy->status !== 'in_hall') {
            $_SESSION['error'] = 'Эта книга уже забронирована или выдана';
            app()->route->redirect('/library/catalog');
            return '';
        }

        // Проверяем, не забронировал ли уже этот пользователь эту книгу
        $existingBooking = Book::where('copy_id', $copyId)
            ->where('user_id', app()->auth->user()->user_id)
            ->whereIn('status', ['reserved', 'issued'])
            ->exists();

        if ($existingBooking) {
            $_SESSION['error'] = 'Вы уже забронировали или взяли эту книгу';
            app()->route->redirect('/library/catalog');
            return '';
        }

        // Создаем бронирование
        $booking = Book::create([
            'user_id' => app()->auth->user()->user_id,
            'copy_id' => $copyId,
            'booking_date' => date('Y-m-d H:i:s'),
            'due_date' => date('Y-m-d H:i:s', strtotime('+14 days')),
            'status' => 'reserved'
        ]);

        if ($booking) {
            // Обновляем статус экземпляра
            $copy->status = 'reserved';
            $copy->save();

            $_SESSION['success'] = 'Книга "' . $copy->book_title . '" успешно забронирована! Ожидайте выдачи у библиотекаря.';
        } else {
            $_SESSION['error'] = 'Ошибка при бронировании книги';
        }

        app()->route->redirect('/library/catalog');
        return '';
    }

    // Редактирование книги
    public function editBook(Request $request): string
    {
        $copyId = $request->get('copy_id');
        $book = BookCopy::find($copyId);

        if (!$book) {
            $_SESSION['error'] = 'Книга не найдена';
            app()->route->redirect('/library/catalog');
            return '';
        }

        if ($request->method === 'POST') {
            $data = $request->all();
            $data['has_electronic_version'] = isset($data['has_electronic_version']) ? 1 : 0;

            if ($book->update($data)) {
                $_SESSION['success'] = 'Книга успешно обновлена';
                app()->route->redirect('/library/catalog');
                return '';
            }

            $_SESSION['error'] = 'Ошибка при обновлении книги';
        }

        return (new View())->render('library.edit_book', ['book' => $book]);
    }

// Удаление книги
    public function deleteBook(Request $request): string
    {
        $copyId = $request->get('copy_id');
        $book = BookCopy::find($copyId);

        if (!$book) {
            $_SESSION['error'] = 'Книга не найдена';
            app()->route->redirect('/library/catalog');
            return '';
        }

        if ($book->status !== 'in_hall') {
            $_SESSION['error'] = 'Нельзя удалить книгу, которая выдана или забронирована';
            app()->route->redirect('/library/catalog');
            return '';
        }

        $book->delete();
        $_SESSION['success'] = 'Книга удалена из каталога';
        app()->route->redirect('/library/catalog');
        return '';
    }

}