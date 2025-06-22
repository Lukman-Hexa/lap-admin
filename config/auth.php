<?php

return [

    'defaults' => [
        'guard' => 'web', // Ini mungkin tetap 'web' untuk login admin
        'passwords' => 'users',
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],
        // Tambahkan atau pastikan guard 'sanctum' ini ada dan benar
        'sanctum' => [
            'driver' => 'sanctum', // Driver untuk Sanctum
            'provider' => 'konsumens', // Menggunakan provider 'konsumens'
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],
        // Tambahkan provider untuk model Konsumen Anda
        'konsumens' => [
            'driver' => 'eloquent',
            'model' => App\Models\Konsumen::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => 10800,

];
