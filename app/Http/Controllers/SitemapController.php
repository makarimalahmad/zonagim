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
            ['loc' => $base.'/', 'lastmod' => $this->sourceLastModified('resources/js/Pages/Landing.jsx')],
            [
                'loc' => $base.'/market',
                'lastmod' => Category::query()
                    ->latest('updated_at')
                    ->value('updated_at')?->toAtomString(),
            ],
        ];

        // Halaman kategori (game) yang punya produk — publik & bisa di-crawl.
        Category::query()
            ->whereHas('products')
            ->orderBy('name')
            ->get(['slug', 'updated_at'])
            ->each(function (Category $category) use (&$urls, $base) {
                $urls[] = [
                    'loc' => $base.'/market/'.$category->slug,
                    'lastmod' => optional($category->updated_at)->toAtomString(),
                ];
            });

        $urls[] = [
            'loc' => $base.'/terms-of-service',
            'lastmod' => $this->sourceLastModified('resources/js/Pages/TermsOfService.jsx'),
        ];
        $urls[] = [
            'loc' => $base.'/privacy-policy',
            'lastmod' => $this->sourceLastModified('resources/js/Pages/Privacy.jsx'),
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.htmlspecialchars($url['loc'], ENT_XML1)."</loc>\n";
            if (! empty($url['lastmod'])) {
                $xml .= '    <lastmod>'.$url['lastmod']."</lastmod>\n";
            }
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Robots-Tag' => 'noindex, follow',
        ]);
    }

    private function sourceLastModified(string $path): ?string
    {
        $modifiedAt = filemtime(base_path($path));

        return $modifiedAt === false ? null : date(DATE_ATOM, $modifiedAt);
    }
}
