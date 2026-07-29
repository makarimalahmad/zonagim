<?php

return [
    'title' => 'Login Admin',
    'heading' => 'Masuk',
    'actions' => [
        'request_password_reset' => [
            'label' => 'Lupa kata sandi?',
        ],
    ],
    'form' => [
        'email' => [
            'label' => 'Alamat email',
        ],
        'password' => [
            'label' => 'Kata sandi',
        ],
        'remember' => [
            'label' => 'Ingat saya',
        ],
        'actions' => [
            'authenticate' => [
                'label' => 'Masuk',
            ],
        ],
    ],
    'multi_factor' => [
        'heading' => 'Verifikasi tambahan',
        'subheading' => 'Masukkan kode dari aplikasi autentikator untuk menyelesaikan proses masuk.',
        'form' => [
            'provider' => [
                'label' => 'Pilih metode verifikasi',
            ],
            'actions' => [
                'authenticate' => [
                    'label' => 'Verifikasi dan masuk',
                ],
            ],
        ],
    ],
    'messages' => [
        'failed' => 'Email atau kata sandi tidak sesuai.',
    ],
    'notifications' => [
        'throttled' => [
            'title' => 'Terlalu banyak percobaan',
            'body' => 'Silakan coba lagi dalam :seconds detik.',
        ],
    ],
];
