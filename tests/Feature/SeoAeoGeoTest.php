<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAeoGeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['seo.url' => 'https://zonagim.my.id']);
    }

    public function test_public_pages_use_production_canonical_and_indexable_robots(): void
    {
        $category = Category::create(['name' => 'Mobile Legends']);
        Product::create([
            'category_id' => $category->id,
            'game_name' => $category->name,
            'price' => 100000,
        ]);

        foreach ([
            '/' => 'https://zonagim.my.id/',
            '/market' => 'https://zonagim.my.id/market',
            '/market/'.$category->slug.'?sort=lowest' => 'https://zonagim.my.id/market/'.$category->slug,
            '/terms-of-service' => 'https://zonagim.my.id/terms-of-service',
            '/privacy-policy' => 'https://zonagim.my.id/privacy-policy',
        ] as $path => $canonical) {
            $content = $this->get($path)->assertOk()->getContent();

            $this->assertStringContainsString('<link rel="canonical" href="'.$canonical.'">', $content);
            $this->assertStringContainsString('<meta property="og:url" content="'.$canonical.'">', $content);
            $this->assertStringContainsString('<meta name="robots" content="index, follow,', $content);
            $this->assertStringNotContainsString('<link rel="canonical" href="http://127.0.0.1', $content);
            $this->assertStringNotContainsString('<meta property="og:url" content="http://127.0.0.1', $content);
        }
    }

    public function test_public_pages_declare_google_compatible_favicons(): void
    {
        $content = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<link rel="icon" href="/favicon.ico" sizes="any">', $content);
        $this->assertStringContainsString(
            '<link rel="icon" href="/favicon-48x48.png" type="image/png" sizes="48x48">',
            $content,
        );
        $this->assertFileExists(public_path('favicon-48x48.png'));
        $this->assertSame([48, 48], array_slice(getimagesize(public_path('favicon-48x48.png')), 0, 2));
    }

    public function test_private_page_is_noindex(): void
    {
        $user = User::factory()->create();
        $content = $this->actingAs($user)->get('/profile')->assertOk()->getContent();

        $this->assertStringContainsString(
            '<meta name="robots" content="noindex, nofollow, noarchive">',
            $content,
        );
    }

    public function test_landing_structured_data_describes_p2p_rekber_model(): void
    {
        $content = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('FAQPage', $content);
        $this->assertStringContainsString('HowTo', $content);
        $this->assertStringContainsString('marketplace P2P akun game', $content);
        $this->assertStringContainsString('Seller asli mengirim kredensial langsung kepada buyer', $content);
        $this->assertStringNotContainsString('Rekber Otomatis', $content);
        $this->assertStringNotContainsString('verifikasi identitas (KYC)', $content);
    }

    public function test_sitemap_uses_production_urls_and_only_indexable_pages(): void
    {
        $category = Category::create(['name' => 'Valorant']);
        Product::create([
            'category_id' => $category->id,
            'game_name' => $category->name,
            'price' => 200000,
        ]);

        $response = $this->get('/sitemap.xml')->assertOk();
        $content = $response->getContent();

        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertHeader('X-Robots-Tag', 'noindex, follow');
        $this->assertStringContainsString('https://zonagim.my.id/market/'.$category->slug, $content);
        $this->assertStringContainsString('<lastmod>', $content);
        $this->assertStringNotContainsString('<priority>', $content);
        $this->assertStringNotContainsString('<changefreq>', $content);
        $this->assertStringNotContainsString('/profile', $content);
        $this->assertStringNotContainsString('/login', $content);
    }

    public function test_robots_and_llms_expose_consistent_public_policy(): void
    {
        $root = dirname(__DIR__, 2);
        $robots = file_get_contents($root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'robots.txt');
        $llms = file_get_contents($root.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'llms.txt');

        $this->assertStringContainsString('Sitemap: https://zonagim.my.id/sitemap.xml', $robots);
        $this->assertStringContainsString('User-agent: OAI-SearchBot', $robots);
        $this->assertStringNotContainsString('User-agent: GPTBot', $robots);
        $this->assertStringNotContainsString('User-agent: CCBot', $robots);
        $this->assertStringContainsString('marketplace P2P akun game', $llms);
        $this->assertStringContainsString('Seller asli mengirim kredensial langsung kepada buyer', $llms);
        $this->assertStringNotContainsString('Verifikasi Penjual', $llms);
        $this->assertStringNotContainsString('KYC', $llms);
    }
}
