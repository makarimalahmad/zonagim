<!DOCTYPE html>
<html lang="id" data-theme="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Terapkan tema tersimpan sebelum render untuk mencegah kedip (FOUC) --}}
        <script>
            (function () {
                try {
                    var t = localStorage.getItem('user-theme') || 'dark';
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
            $errorStatus = $errorStatus ?? null;
            $isErrorPage = in_array($errorStatus, [403, 404], true);

            if ($errorStatus === 403) {
                $title = '403 — Akses Ditolak | ' . $brand;
                $description = 'Anda tidak memiliki izin untuk membuka halaman ini. Kembali ke Market untuk melanjutkan.';
            } elseif ($errorStatus === 404) {
                $title = '404 — Halaman Tidak Ditemukan | ' . $brand;
                $description = 'Halaman yang Anda cari tidak ditemukan. Kembali ke Market untuk melihat listing akun game.';
            } elseif (request()->routeIs('market')) {
                $title = 'Marketplace Akun Game — Cari & Beli Akun | ' . $brand;
                $description = 'Jelajahi listing seller akun game di ' . $brand . ' dan gunakan opsi Rekber sebagai perantara transaksi.';
            } elseif (request()->routeIs('market.category')) {
                $cat = optional(request()->route('category'))->name;
                $title = ($cat ? "Jual Beli Akun {$cat}" : 'Akun Game') . ' | ' . $brand;
                $description = $cat
                    ? "Lihat listing seller akun {$cat} yang tersedia di {$brand} dan pilih berdasarkan informasi akun serta harga."
                    : $description;
            } elseif (request()->routeIs('terms')) {
                $title = 'Syarat & Ketentuan | ' . $brand;
                $description = 'Syarat dan ketentuan penggunaan layanan marketplace ' . $brand . '.';
            } elseif (request()->routeIs('privacy')) {
                $title = 'Kebijakan Privasi | ' . $brand;
                $description = 'Kebijakan privasi dan perlindungan data pengguna di ' . $brand . '.';
            }

            $routeName = request()->route()?->getName();
            $publicRoutes = ['landing', 'market', 'market.category', 'terms', 'privacy'];
            $isIndexable = ! $isErrorPage && in_array($routeName, $publicRoutes, true);
            $canonicalPath = match ($routeName) {
                'landing' => '/',
                'market' => '/market',
                'market.category' => '/market/' . optional(request()->route('category'))->slug,
                'terms' => '/terms-of-service',
                'privacy' => '/privacy-policy',
                default => request()->getPathInfo(),
            };
            $canonical = $base . ($canonicalPath === '/' ? '/' : '/' . ltrim($canonicalPath, '/'));
            $robots = $isIndexable
                ? 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1'
                : 'noindex, nofollow, noarchive';
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

            $graph = $isIndexable ? [$org, $website] : [];

            if (request()->routeIs('landing')) {
                $faqs = [
                    ['Apa itu Zonagim?', 'Zonagim adalah marketplace P2P akun game yang mempertemukan seller dan buyer serta menyediakan opsi Rekber sebagai perantara transaksi.'],
                    ['Siapa yang mengirim kredensial akun?', 'Seller asli mengirim kredensial langsung kepada buyer. Zonagim tidak mengumpulkan password, OTP, atau recovery code.'],
                    ['Apa itu sistem Rekber?', 'Rekber adalah layanan perantara yang membantu alur serah terima dana dan data selama transaksi, bukan garansi akun setelah transaksi selesai.'],
                    ['Game apa saja yang akunnya bisa dibeli?', 'Listing dapat tersedia untuk game seperti Mobile Legends, Free Fire, Valorant, PUBG Mobile, Genshin Impact, dan lainnya.'],
                    ['Bagaimana cara membeli akun game di Zonagim?', 'Daftar akun, pilih listing di halaman Market, lalu ikuti alur transaksi langsung atau arahan Rekber yang tersedia.'],
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
                        ['@type' => 'HowToStep', 'position' => 1, 'name' => 'Daftar Akun', 'text' => 'Buat akun menggunakan email aktif.'],
                        ['@type' => 'HowToStep', 'position' => 2, 'name' => 'Pilih Akun', 'text' => 'Cari dan pilih listing akun game yang tersedia di halaman Market.'],
                        ['@type' => 'HowToStep', 'position' => 3, 'name' => 'Pilih Alur Transaksi', 'text' => 'Hubungi seller untuk transaksi langsung atau ikuti arahan Admin jika menggunakan opsi Rekber.'],
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
                JSON_UNESCAPED_SLASHES
                    | JSON_UNESCAPED_UNICODE
                    | JSON_HEX_TAG
                    | JSON_HEX_AMP
                    | JSON_HEX_APOS
                    | JSON_HEX_QUOT
            );
        @endphp

        {{-- Primary Meta --}}
        <title inertia>{{ $title }}</title>
        <meta name="description" content="{{ $description }}">
        <meta name="keywords" content="{{ $seo['keywords'] }}">
        <meta name="author" content="{{ $brand }}">
        <meta name="robots" content="{{ $robots }}">
        @if (! $isErrorPage)
            <link rel="canonical" href="{{ $canonical }}">
        @endif
        <meta name="theme-color" content="#0b1221">

        {{-- Verifikasi Webmaster (opsional, via HTML tag) --}}
        @if (config('seo.google_site_verification'))
            <meta name="google-site-verification" content="{{ config('seo.google_site_verification') }}">
        @endif
        @if (config('seo.bing_site_verification'))
            <meta name="msvalidate.01" content="{{ config('seo.bing_site_verification') }}">
        @endif

        {{-- Favicon --}}
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon-48x48.png" type="image/png" sizes="48x48">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png" sizes="180x180">

        {{-- Open Graph --}}
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $brand }}">
        <meta property="og:title" content="{{ $title }}">
        <meta property="og:description" content="{{ $description }}">
        @if (! $isErrorPage)
            <meta property="og:url" content="{{ $canonical }}">
        @endif
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:alt" content="Logo {{ $brand }}">
        <meta property="og:locale" content="{{ $seo['locale'] }}">

        {{-- Twitter Card --}}
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $title }}">
        <meta name="twitter:description" content="{{ $description }}">
        <meta name="twitter:image" content="{{ $ogImage }}">
        <meta name="twitter:image:alt" content="Logo {{ $brand }}">
        @if (!empty($seo['twitter']))
            <meta name="twitter:site" content="{{ $seo['twitter'] }}">
        @endif

        {{-- Structured Data (SEO / AEO / GEO) --}}
        <script type="application/ld+json">{!! $jsonLd !!}</script>

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite(['resources/js/app.jsx', "resources/js/Pages/{$page['component']}.jsx"])
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
