<!DOCTYPE html>
<html lang="id" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Terapkan tema tersimpan sebelum render untuk mencegah kedip (FOUC) --}}
        <script>
            (function () {
                try {
                    var t = localStorage.getItem('theme') || 'dark';
                    document.documentElement.setAttribute('data-theme', t);
                } catch (e) {}
            })();
        </script>

        @php
            $seo = config('seo');
            $base = $seo['url'];
            $brand = $seo['brand'];
            $title = $seo['title'];
            $description = $seo['description'];
            $cat = null;

            if (request()->routeIs('market')) {
                $title = 'Marketplace Akun Game — Cari & Beli Akun Aman | ' . $brand;
                $description = 'Jelajahi katalog akun game di ' . $brand . '. Pilih game favoritmu dan beli akunnya dengan aman lewat sistem Rekber otomatis & bergaransi.';
            } elseif (request()->routeIs('market.category')) {
                $cat = optional(request()->route('category'))->name;
                $title = ($cat ? "Jual Beli Akun {$cat} Murah & Aman" : 'Akun Game') . ' | ' . $brand;
                $description = $cat
                    ? "Daftar akun {$cat} terlengkap, murah, dan bergaransi di {$brand}. Transaksi aman dengan sistem Rekber otomatis."
                    : $description;
            } elseif (request()->routeIs('terms')) {
                $title = 'Syarat & Ketentuan | ' . $brand;
                $description = 'Syarat dan ketentuan penggunaan layanan marketplace ' . $brand . '.';
            } elseif (request()->routeIs('privacy')) {
                $title = 'Kebijakan Privasi | ' . $brand;
                $description = 'Kebijakan privasi dan perlindungan data pengguna di ' . $brand . '.';
            }

            $canonical = url()->current();
            $ogImage = $base . $seo['image'];

            // ============ JSON-LD (@graph) ============
            $org = [
                '@type' => 'Organization',
                '@id' => $base . '/#organization',
                'name' => $brand,
                'url' => $base . '/',
                'logo' => $ogImage,
                'description' => $seo['description'],
            ];
            if (!empty($seo['same_as'])) {
                $org['sameAs'] = $seo['same_as'];
            }

            $website = [
                '@type' => 'WebSite',
                '@id' => $base . '/#website',
                'name' => $brand,
                'url' => $base . '/',
                'inLanguage' => 'id-ID',
                'publisher' => ['@id' => $base . '/#organization'],
            ];

            $graph = [$org, $website];

            if (request()->routeIs('landing')) {
                $faqs = [
                    ['Apa itu LapakGimID?', 'LapakGimID adalah marketplace jual beli akun game di Indonesia dengan sistem Rekber (rekening bersama) otomatis untuk membuat transaksi lebih aman.'],
                    ['Apakah transaksi di LapakGimID aman?', 'Transaksi memakai sistem Rekber yang menahan dana sampai pembeli menerima data akun. Risiko pasca-transaksi seperti hack back tetap menjadi tanggung jawab pembeli.'],
                    ['Apa itu sistem Rekber?', 'Rekber (Rekening Bersama) adalah layanan perantara yang menahan pembayaran hingga pembeli menerima data akun, sehingga mengurangi risiko penipuan saat transaksi berlangsung.'],
                    ['Game apa saja yang akunnya bisa dibeli?', 'Tersedia akun untuk berbagai game populer seperti Mobile Legends, Free Fire, Valorant, PUBG Mobile, Genshin Impact, dan lainnya.'],
                    ['Bagaimana cara membeli akun game di LapakGimID?', 'Daftar akun gratis, pilih akun yang diinginkan di halaman Market, lalu lakukan pembayaran dengan aman menggunakan Rekber.'],
                ];
                $graph[] = [
                    '@type' => 'FAQPage',
                    '@id' => $base . '/#faq',
                    'mainEntity' => array_map(fn ($f) => [
                        '@type' => 'Question',
                        'name' => $f[0],
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
                    ], $faqs),
                ];
                $graph[] = [
                    '@type' => 'HowTo',
                    'name' => 'Cara Beli Akun Game di ' . $brand,
                    'step' => [
                        ['@type' => 'HowToStep', 'position' => 1, 'name' => 'Daftar Akun', 'text' => 'Buat akun gratis dalam hitungan detik menggunakan email aktif.'],
                        ['@type' => 'HowToStep', 'position' => 2, 'name' => 'Pilih Akun', 'text' => 'Cari dan pilih akun game favoritmu dari katalog yang tersedia di halaman Market.'],
                        ['@type' => 'HowToStep', 'position' => 3, 'name' => 'Bayar & Main', 'text' => 'Lakukan pembayaran dengan aman lewat Rekber dan terima data akun untuk langsung dimainkan.'],
                    ],
                ];
            }

            if (request()->routeIs('market') || request()->routeIs('market.category')) {
                $crumbs = [
                    ['name' => 'Beranda', 'item' => $base . '/'],
                    ['name' => 'Market', 'item' => $base . '/market'],
                ];
                if (request()->routeIs('market.category') && $cat) {
                    $crumbs[] = ['name' => $cat, 'item' => $canonical];
                }
                $graph[] = [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => array_map(fn ($c, $i) => [
                        '@type' => 'ListItem',
                        'position' => $i + 1,
                        'name' => $c['name'],
                        'item' => $c['item'],
                    ], $crumbs, array_keys($crumbs)),
                ];
            }

            $jsonLd = json_encode(
                ['@context' => 'https://schema.org', '@graph' => $graph],
                JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            );
        @endphp

        {{-- Primary Meta --}}
        <title inertia>{{ $title }}</title>
        <meta name="description" content="{{ $description }}">
        <meta name="keywords" content="{{ $seo['keywords'] }}">
        <meta name="author" content="{{ $brand }}">
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <link rel="canonical" href="{{ $canonical }}">
        <meta name="theme-color" content="#0b1221">

        {{-- Verifikasi Webmaster (opsional, via HTML tag) --}}
        @if (config('seo.google_site_verification'))
            <meta name="google-site-verification" content="{{ config('seo.google_site_verification') }}">
        @endif
        @if (config('seo.bing_site_verification'))
            <meta name="msvalidate.01" content="{{ config('seo.bing_site_verification') }}">
        @endif

        {{-- Favicon --}}
        <link rel="icon" href="/images/lapakgimid.png" type="image/png">
        <link rel="apple-touch-icon" href="/images/lapakgimid.png">

        {{-- Open Graph --}}
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $brand }}">
        <meta property="og:title" content="{{ $title }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ $canonical }}">
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:locale" content="{{ $seo['locale'] }}">

        {{-- Twitter Card --}}
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $title }}">
        <meta name="twitter:description" content="{{ $description }}">
        <meta name="twitter:image" content="{{ $ogImage }}">

        {{-- Structured Data (SEO / AEO / GEO) --}}
        <script type="application/ld+json">{!! $jsonLd !!}</script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
