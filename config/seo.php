<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Identitas Situs (dipakai untuk SEO, AEO, GEO)
    |--------------------------------------------------------------------------
    | URL produksi dipisah dari APP_URL agar canonical/sitemap/JSON-LD selalu
    | memakai domain asli meskipun APP_URL lokal saat development.
    */
    'brand' => env('APP_NAME', 'Zonagim'),

    'url' => rtrim(env('SEO_URL', 'https://zonagim.my.id'), '/'),

    'title' => 'Zonagim - Marketplace Jual Beli Akun Game',

    'description' => 'Zonagim adalah marketplace P2P akun game yang mempertemukan penjual dan pembeli serta menyediakan layanan Rekber sebagai perantara transaksi.',

    'keywords' => 'jual beli akun game, marketplace akun game, jual akun game, beli akun game, rekber akun game, akun mobile legends, akun free fire, akun valorant, akun pubg mobile, akun genshin impact, jual akun ml murah',

    'locale' => 'id_ID',

    // Path gambar untuk Open Graph / Twitter (diubah jadi URL absolut otomatis).
    'image' => '/images/zonagim.png',

    'twitter' => env('SEO_TWITTER'),

    'whatsapp' => env('SEO_WHATSAPP'),

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
