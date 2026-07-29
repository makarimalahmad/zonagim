<?php

return [
    'management_schema' => [
        'actions' => [
            'label' => 'Aplikasi autentikator',
            'below_content' => 'Gunakan Google Authenticator, Microsoft Authenticator, Authy, atau aplikasi TOTP lain.',
            'messages' => [
                'enabled' => 'Aktif',
                'disabled' => 'Belum aktif',
            ],
        ],
    ],
    'login_form' => [
        'label' => 'Gunakan kode dari aplikasi autentikator',
        'code' => [
            'label' => 'Kode autentikasi 6 digit',
            'validation_attribute' => 'kode autentikasi',
            'actions' => [
                'use_recovery_code' => [
                    'label' => 'Gunakan kode pemulihan',
                ],
            ],
            'messages' => [
                'invalid' => 'Kode autentikasi tidak valid atau sudah digunakan.',
            ],
        ],
        'recovery_code' => [
            'label' => 'Kode pemulihan',
            'validation_attribute' => 'kode pemulihan',
            'messages' => [
                'invalid' => 'Kode pemulihan tidak valid atau sudah digunakan.',
            ],
        ],
    ],
];
