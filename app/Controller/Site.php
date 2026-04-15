<?php
namespace Controller;

use Src\View;
use Src\Request;
use Model\Users;
use Src\Auth\Auth;
use Src\Validator\Validator;
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

            $validator = new Validator($request->all(), [
                'name' => ['required'],
                'login' => ['required', 'unique:users,login'],
                'password' => ['required']
            ], [
                'required' => 'Поле :field пусто',
                'unique' => 'Поле :field должно быть уникально'
            ]);

            if($validator->fails()){
                return new View('site.signup',
                    ['message' => json_encode($validator->errors(), JSON_UNESCAPED_UNICODE)]);
            }

            if (Users::create($request->all())) {  // <- Обратите внимание: Users, не User
                app()->route->redirect('/login');
                return false;  // <- ВОТ ЭТУ СТРОКУ НУЖНО ДОБАВИТЬ
            }
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