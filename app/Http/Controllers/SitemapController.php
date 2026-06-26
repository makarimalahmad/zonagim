<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $base = rtrim(config('seo.url'), '/');

        $urls = [
            ['loc' => $base . '/', 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => $base . '/market', 'changefreq' => 'daily', 'priority' => '0.9'],
        ];

        // Halaman kategori (game) yang punya produk — publik & bisa di-crawl.
        Category::query()
            ->whereHas('products')
            ->orderBy('name')
            ->get(['slug', 'updated_at'])
            ->each(function (Category $category) use (&$urls, $base) {
                $urls[] = [
                    'loc' => $base . '/market/' . $category->slug,
                    'lastmod' => optional($category->updated_at)->toAtomString(),
                    'changefreq' => 'daily',
                    'priority' => '0.8',
                ];
            });

        $urls[] = ['loc' => $base . '/terms-of-service', 'changefreq' => 'yearly', 'priority' => '0.3'];
        $urls[] = ['loc' => $base . '/privacy-policy', 'changefreq' => 'yearly', 'priority' => '0.3'];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1) . "</loc>\n";
            if (!empty($url['lastmod'])) {
                $xml .= '    <lastmod>' . $url['lastmod'] . "</lastmod>\n";
            }
            $xml .= '    <changefreq>' . $url['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $url['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
