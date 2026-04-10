<?php

use Src\Route;
use Controller\LibraryController;
use Controller\Site;

// Регистрируем маршруты
Route::add('GET', '/', [Site::class, 'index']);
Route::add('GET', '/go', [Site::class, 'index']);
Route::add(['GET', 'POST'], '/login', [Site::class, 'login']);
Route::add(['GET', 'POST'], '/signup', [Site::class, 'signup']);
Route::add('GET', '/logout', [Site::class, 'logout']);
Route::add('GET', '/hello', [Site::class, 'hello']);

// Каталог и поиск
Route::add(['GET', 'POST'], '/library/catalog', [LibraryController::class, 'catalog']);
Route::add(['GET', 'POST'], '/library/search', [LibraryController::class, 'searchByQr']);

// Маршруты библиотекаря
Route::add(['GET', 'POST'], '/library/reserve', [LibraryController::class, 'reserveBook']);
Route::add(['GET', 'POST'], '/library/issue', [LibraryController::class, 'issueBook']);
Route::add(['GET', 'POST'], '/library/return', [LibraryController::class, 'returnBook']);
Route::add('GET', '/library/active-bookings', [LibraryController::class, 'activeBookings']);
Route::add(['GET', 'POST'], '/library/add-book', [LibraryController::class, 'addBook']);
Route::add(['GET', 'POST'], '/library/edit-book', [LibraryController::class, 'editBook']);  // ДОБАВЛЕНО
Route::add('GET', '/library/delete-book', [LibraryController::class, 'deleteBook']);        // ДОБАВЛЕНО
Route::add('GET', '/library/readers', [LibraryController::class, 'readersList']);
Route::add(['GET', 'POST'], '/library/add-reader', [LibraryController::class, 'addReader']);
Route::add('GET', '/library/reader-books', [LibraryController::class, 'readerBooks']);      // ДОБАВЛЕНО

// Бронирование книги читателем
Route::add(['GET', 'POST'], '/library/reader-reserve', [LibraryController::class, 'readerReserveBook']);

// Маршруты читателя
Route::add(['GET', 'POST'], '/profile', [LibraryController::class, 'profile']);
Route::add(['GET', 'POST'], '/profile/extend', [LibraryController::class, 'extendBook']);