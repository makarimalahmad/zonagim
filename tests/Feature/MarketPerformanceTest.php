<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MarketPerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_page_uses_bounded_queries_and_returns_a_compact_paginated_payload(): void
    {
        $category = $this->createCategoryWithProducts(30);
        $queries = [];

        DB::listen(function ($query) use (&$queries): void {
            if (str_starts_with(strtolower(ltrim($query->sql)), 'select')) {
                $queries[] = $query->sql;
            }
        });

        $response = $this->get(route('market.category', $category));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Market/Game')
                ->where('activeGame', $category->name)
                ->where('activeGameSlug', $category->slug)
                ->where('products.per_page', 24)
                ->where('products.total', 30)
                ->has('products.data', 24)
                ->where('products.data.0.category', $category->name)
                ->where('products.data.0.slug', 'akun-30-abcdefg')
                ->missing('products.data.0.description')
                ->missing('products.data.0.seller_whatsapp'));

        $this->assertLessThanOrEqual(
            3,
            count($queries),
            'Halaman kategori menjalankan terlalu banyak SELECT: '.implode(' | ', $queries),
        );
    }

    public function test_category_page_filters_and_sorts_products_on_the_server(): void
    {
        $category = Category::create(['name' => 'Dota 2']);

        foreach ([100_000, 300_000, 200_000, 50_000] as $index => $price) {
            Product::create([
                'category_id' => $category->id,
                'game_name' => $category->name,
                'price' => $price,
                'created_at' => now()->addSeconds($index),
            ]);
        }

        $response = $this->get(route('market.category', [
            'category' => $category,
            'sort' => 'highest',
            'min_price' => 150_000,
            'max_price' => 300_000,
        ]));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Market/Game')
                ->has('products.data', 2)
                ->where('products.total', 2)
                ->where('products.data.0.price', 300_000)
                ->where('products.data.1.price', 200_000)
                ->where('filters.sort', 'highest'));
    }

    public function test_product_slug_is_generated_and_stays_stable_when_title_changes(): void
    {
        $category = Category::create(['name' => 'PUBG Mobile']);
        $product = Product::create([
            'category_id' => $category->id,
            'game_name' => $category->name,
            'title' => 'PUBGM Season 2',
            'price' => 50000,
        ]);

        $this->assertMatchesRegularExpression('/^pubgm-season-2-[a-z0-9]{7}$/', $product->slug);
        $originalSlug = $product->slug;

        $product->update(['title' => 'PUBGM Season 3']);

        $this->assertSame($originalSlug, $product->fresh()->slug);
    }

    public function test_admin_product_routes_use_internal_id_while_public_routes_use_slug(): void
    {
        $category = Category::create(['name' => 'PUBG Mobile']);
        $product = Product::create([
            'category_id' => $category->id,
            'game_name' => $category->name,
            'title' => 'PUBGM Season 2',
            'price' => 50000,
        ]);

        $this->assertSame($product->id, $product->getRouteKey());
        $this->assertStringContainsString(
            '/admin/products/'.$product->id.'/edit',
            ProductResource::getUrl('edit', ['record' => $product]),
        );
        $this->assertStringContainsString(
            '/market/'.$category->slug.'/akun/'.$product->slug,
            route('akun.show', ['category' => $category, 'product' => $product->slug]),
        );
    }

    public function test_guest_product_detail_redirects_to_clean_login_url_with_server_flash(): void
    {
        $category = Category::create(['name' => 'Valorant']);
        $product = Product::create([
            'category_id' => $category->id,
            'game_name' => $category->name,
            'title' => 'Akun Ranked',
            'price' => 250_000,
        ]);
        $detailUrl = route('akun.show', [
            'category' => $category,
            'product' => $product->slug,
        ]);

        $this->get($detailUrl)
            ->assertRedirect(route('login'))
            ->assertSessionHas('status', 'Silakan masuk untuk melihat detail akun.')
            ->assertSessionHas('url.intended', $detailUrl);
    }

    public function test_product_detail_uses_scoped_category_binding(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $valorant = Category::create(['name' => 'Valorant']);
        $dota = Category::create(['name' => 'Dota 2']);

        $product = Product::create([
            'category_id' => $valorant->id,
            'game_name' => $valorant->name,
            'title' => 'Akun Ranked',
            'price' => 250_000,
        ]);

        $this->actingAs($user)
            ->get(route('akun.show', ['category' => $dota, 'product' => $product->slug]))
            ->assertNotFound();

        $this->actingAs($user)
            ->get(route('akun.show', ['category' => $valorant, 'product' => $product->slug]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('akun/Show')
                ->where('product.id', $product->id)
                ->where('product.category', $valorant->name));
    }

    private function createCategoryWithProducts(int $count): Category
    {
        $category = Category::create(['name' => 'Valorant']);
        $now = now();

        Product::query()->insert(
            collect(range(1, $count))
                ->map(fn (int $number): array => [
                    'category_id' => $category->id,
                    'game_name' => $category->name,
                    'title' => "Akun {$number}",
                    'slug' => "akun-{$number}-abcdefg",
                    'description' => "Deskripsi akun {$number}",
                    'images' => json_encode(["products/{$number}.webp"]),
                    'price' => $number * 10_000,
                    'seller_name' => "Seller {$number}",
                    'seller_whatsapp' => '628123456789',
                    'created_at' => $now->copy()->addSeconds($number),
                    'updated_at' => $now,
                ])
                ->all(),
        );

        return $category;
    }
}
