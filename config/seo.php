<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Identitas Situs (dipakai untuk SEO, AEO, GEO)
    |--------------------------------------------------------------------------
    | URL produksi dipisah dari APP_URL agar canonical/sitemap/JSON-LD selalu
    | memakai domain asli meskipun APP_URL lokal saat development.
    */
    'brand' => env('APP_NAME', 'LapakAkunID'),

    'url' => rtrim(env('SEO_URL', 'https://lapakgim.my.id'), '/'),

    'title' => 'LapakAkunID — Jual Beli Akun Game Aman & Terpercaya',

    'description' => 'LapakAkunID adalah marketplace jual beli akun game terpercaya di Indonesia. Sistem Rekber otomatis, transaksi cepat & bergaransi untuk akun Mobile Legends, Free Fire, Valorant, PUBG Mobile, Genshin Impact, dan game populer lainnya.',

    'keywords' => 'jual beli akun game, marketplace akun game, jual akun game, beli akun game, rekber akun game, akun mobile legends, akun free fire, akun valorant, akun pubg mobile, akun genshin impact, jual akun ml murah',

    'locale' => 'id_ID',

    // Path gambar untuk Open Graph / Twitter (diubah jadi URL absolut otomatis).
    'image' => '/images/lapakakunid.png',

    'twitter' => '@lapakakunid',

    'whatsapp' => '6281234567890',

    // URL media sosial resmi (untuk schema Organization > sameAs). Isi bila ada.
    'same_as' => array_values(array_filter([
        env('SEO_INSTAGRAM'),
        env('SEO_TIKTOK'),
        env('SEO_FACEBOOK'),
        env('SEO_YOUTUBE'),
    ])),

    // Kode verifikasi webmaster (opsional). Diisi dari Search Console / Bing
    // bila memilih metode verifikasi "HTML tag". Kosongkan kalau verifikasi
    // lewat DNS.
    'google_site_verification' => env('GOOGLE_SITE_VERIFICATION'),
    'bing_site_verification' => env('BING_SITE_VERIFICATION'),
];
