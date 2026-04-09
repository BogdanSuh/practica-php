<?php
namespace Middlewares;

use Src\Auth\Auth;
use Src\Request;

class RoleMiddleware
{
    public function handle(Request $request, string $role)
    {
        if (!Auth::check() || Auth::user()->role !== $role) {
            app()->route->redirect('/login');
        }
    }
}