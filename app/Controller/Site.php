<?php
namespace Controller;

use Src\View;
use Src\Request;
use Model\Users;
use Src\Auth\Auth;

class Site
{
    // Измените метод index, чтобы он не использовал таблицу posts
    public function index(Request $request): string
    {
        // Перенаправляем на каталог библиотеки
        app()->route->redirect('/library/catalog');
        return '';
    }

    public function hello(): string
    {
        return new View('site.hello', ['message' => 'Добро пожаловать в библиотечную систему!']);
    }

    public function signup(Request $request): string
    {
        if ($request->method === 'POST') {
            $data = $request->all();

            // Проверяем, существует ли пользователь с таким логином
            if (Users::where('login', $data['login'])->exists()) {
                return new View('site.signup', ['message' => 'Пользователь с таким логином уже существует']);
            }

            // Хешируем пароль
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            $data['role'] = 'reader';
            $data['reader_card'] = 'RD-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

            // Создаем пользователя
            $user = Users::create($data);

            if ($user) {
                // Автоматически авторизуем пользователя
                Auth::login($user);

                // Перенаправляем в личный кабинет
                $_SESSION['success'] = 'Добро пожаловать в библиотеку!';
                app()->route->redirect('/profile');
                return '';
            }

            return new View('site.signup', ['message' => 'Ошибка при регистрации']);
        }

        return new View('site.signup');
    }

    public function login(Request $request): string
    {
        if ($request->method === 'GET') {
            return new View('site.login');
        }

        if (Auth::attempt($request->all())) {
            // Перенаправляем в зависимости от роли
            if (Auth::user()->role === 'librarian') {
                app()->route->redirect('/library/active-bookings');
            } else {
                app()->route->redirect('/profile');
            }
            return '';
        }

        return new View('site.login', ['message' => 'Неправильные логин или пароль']);
    }

    public function logout(): void
    {
        Auth::logout();
        app()->route->redirect('/login');
        exit;
    }
}