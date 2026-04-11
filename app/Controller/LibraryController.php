<?php
namespace Controller;

use Src\View;
use Src\Request;
use Model\Users;
use Src\Auth\Auth;
use App\Services\CatalogService;
use App\Services\BookingService;
use App\Traits\UploadTrait;

class LibraryController
{
    use UploadTrait;

    private CatalogService $catalog;
    private BookingService $booking;

    public function __construct()
    {
        $this->catalog = new CatalogService();
        $this->booking = new BookingService();
    }

    public function catalog(Request $request): string
    {
        $search = $request->get('search');
        $groupedBooks = $this->catalog->getGroupedBooks($search);

        return (new View())->render('library.catalog', [
            'groupedBooks' => $groupedBooks,
            'search' => $search
        ]);
    }

    public function searchByQr(Request $request): string
    {
        $qrCode = $request->get('qr_code');
        $copy = null;
        $booking = null;
        $message = '';

        if ($qrCode) {
            $copy = $this->catalog->findBookByQr($qrCode);
            if (!$copy) {
                $message = 'Экземпляр с таким QR-кодом не найден';
            } else {
                $booking = \Model\Book::where('copy_id', $copy->copy_id)
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

    public function reserveBook(Request $request): string
    {
        if ($request->method === 'POST') {
            $booking = $this->booking->createReservation(
                $request->get('user_id'),
                $request->get('copy_id'),
                $request->get('due_date')
            );

            if ($booking) {
                $_SESSION['success'] = 'Книга успешно забронирована';
                app()->route->redirect('/library/catalog');
                return '';
            }
            $_SESSION['error'] = 'Ошибка: книга недоступна';
        }

        $availableCopies = \Model\BookCopy::where('status', 'in_hall')->get();
        $readers = Users::where('role', 'reader')->get();

        return (new View())->render('library.reserve', [
            'availableCopies' => $availableCopies,
            'readers' => $readers
        ]);
    }

    public function issueBook(Request $request): string
    {
        $booking = $this->booking->issueBook($request->get('booking_id'));

        if ($booking) {
            $_SESSION['success'] = 'Книга выдана читателю';
        }

        app()->route->redirect('/library/active-bookings');
        return '';
    }

    public function returnBook(Request $request): string
    {
        $booking = $this->booking->returnBook($request->get('booking_id'));

        if ($booking) {
            $_SESSION['success'] = 'Книга возвращена в библиотеку';
        }

        app()->route->redirect('/library/active-bookings');
        return '';
    }

    public function activeBookings(Request $request): string
    {
        $bookings = $this->booking->getAllActive();

        return (new View())->render('library.active-bookings', [
            'bookings' => $bookings
        ]);
    }

    public function extendBook(Request $request): string
    {
        $bookingId = $request->get('booking_id');
        $days = (int)$request->get('days', 14);

        if ($days < 1) $days = 1;
        if ($days > 14) $days = 14;

        $booking = $this->booking->extendBook($bookingId, $days);

        if ($booking) {
            $_SESSION['success'] = 'Книга продлена до ' . date('d.m.Y', strtotime($booking->due_date));
        } else {
            $_SESSION['error'] = 'Невозможно продлить: есть очередь';
        }

        app()->route->redirect('/profile');
        return '';
    }

    public function profile(Request $request): string
    {
        $user = app()->auth->user();
        $active = $this->booking->getUserActive($user->user_id);
        $history = $this->booking->getUserHistory($user->user_id);

        return (new View())->render('library.profile', [
            'user' => $user,
            'active' => $active,
            'history' => $history
        ]);
    }

    public function addBook(Request $request): string
    {
        if ($request->method === 'POST') {
            $data = $request->all();
            $coverImage = $_FILES['cover_image'] ?? null;

            if ($coverImage && $coverImage['error'] === UPLOAD_ERR_OK) {
                $path = $this->uploadFile($coverImage, 'covers');
                if ($path) {
                    $data['cover_image'] = $path;
                }
            }

            $book = $this->catalog->createBook($data);

            if ($book) {
                $_SESSION['success'] = 'Книга добавлена';
                app()->route->redirect('/library/catalog');
                return '';
            }
            $_SESSION['error'] = 'Ошибка при добавлении';
        }

        return (new View())->render('library.add_book');
    }

    public function readerReserveBook(Request $request): string
    {
        $userId = app()->auth->user()->user_id;
        $booking = $this->booking->createReservation($userId, $request->get('copy_id'));

        if ($booking) {
            $_SESSION['success'] = 'Книга забронирована';
        } else {
            $_SESSION['error'] = 'Ошибка бронирования';
        }

        app()->route->redirect('/library/catalog');
        return '';
    }

    public function readersList(Request $request): string
    {
        $readers = Users::where('role', 'reader')->orderBy('name')->get();

        foreach ($readers as $reader) {
            $reader->active_books = $this->booking->getUserActive($reader->user_id)->count();
            $reader->read_books = $this->booking->getUserHistory($reader->user_id)->count();
        }

        return (new View())->render('library.readers_list', ['readers' => $readers]);
    }

    public function addReader(Request $request): string
    {
        if ($request->method === 'POST') {
            $data = $request->all();

            if (Users::where('login', $data['login'])->exists()) {
                $_SESSION['error'] = 'Логин уже существует';
                return (new View())->render('library.add_reader');
            }

            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $data['role'] = 'reader';
            $data['reader_card'] = 'RD-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

            if (Users::create($data)) {
                $_SESSION['success'] = 'Читатель добавлен';
                app()->route->redirect('/library/readers');
                return '';
            }
            $_SESSION['error'] = 'Ошибка при добавлении';
        }

        return (new View())->render('library.add_reader');
    }

    public function readerBooks(Request $request): string
    {
        $userId = $request->get('user_id');
        $user = Users::find($userId);

        if (!$user) {
            $_SESSION['error'] = 'Читатель не найден';
            app()->route->redirect('/library/readers');
            return '';
        }

        $bookings = \Model\Book::where('user_id', $userId)
            ->with('copy')
            ->orderBy('booking_date', 'DESC')
            ->get();

        return (new View())->render('library.reader_books', [
            'user' => $user,
            'bookings' => $bookings
        ]);
    }

    public function editBook(Request $request): string
    {
        $copyId = $request->get('copy_id');
        $book = \Model\BookCopy::find($copyId);

        if (!$book) {
            $_SESSION['error'] = 'Книга не найдена';
            app()->route->redirect('/library/catalog');
            return '';
        }

        if ($request->method === 'POST') {
            $data = $request->all();
            $data['has_electronic_version'] = isset($data['has_electronic_version']) ? 1 : 0;

            if ($book->update($data)) {
                $_SESSION['success'] = 'Книга обновлена';
                app()->route->redirect('/library/catalog');
                return '';
            }
            $_SESSION['error'] = 'Ошибка при обновлении';
        }

        return (new View())->render('library.edit_book', ['book' => $book]);
    }

    public function deleteBook(Request $request): string
    {
        $copyId = $request->get('copy_id');
        $book = \Model\BookCopy::find($copyId);

        if (!$book) {
            $_SESSION['error'] = 'Книга не найдена';
            app()->route->redirect('/library/catalog');
            return '';
        }

        if ($book->status !== 'in_hall') {
            $_SESSION['error'] = 'Нельзя удалить выданную книгу';
            app()->route->redirect('/library/catalog');
            return '';
        }

        if ($this->catalog->deleteBook($book)) {
            $_SESSION['success'] = 'Книга удалена';
        } else {
            $_SESSION['error'] = 'Ошибка при удалении';
        }

        app()->route->redirect('/library/catalog');
        return '';
    }
}