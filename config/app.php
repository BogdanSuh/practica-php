<?php
return [
    'auth' => \Src\Auth\Auth::class,
    'identity' => \Model\Users::class,
    'routeMiddleware' => [
        'auth' => \Middlewares\AuthMiddleware::class,
        'role' => \Middlewares\RoleMiddleware::class,
    ],
    'validators' => [
        'required' => \Validators\RequiredValidator::class,
        'unique' => \Validators\UniqueValidator::class,
        'qr_code' => \Validators\QrCodeValidator::class,
        'book_status' => \Validators\BookStatusValidator::class,
        'file' => \Validators\FileValidator::class,
        'year' => \Validators\YearValidator::class,
    ]
];