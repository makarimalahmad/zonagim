<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MarketController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | MARKET - LIST GAME / CATEGORY
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        $games = Category::query()
            ->select('id', 'name', 'slug', 'image')
            ->whereHas('products')
            ->orderBy('name')
            ->get()
            ->map(fn ($game) => [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'image' => $game->image ? asset('storage/'.$game->image) : null,
            ]);

        return Inertia::render('Market/Index', [
            'games' => $games,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | MARKET - LIST AKUN PER GAME
    |--------------------------------------------------------------------------
    */

    public function game(Request $request, Category $category)
    {
        $filters = $request->validate([
            'sort' => ['nullable', Rule::in(['latest', 'lowest', 'highest'])],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0', 'gte:min_price'],
        ]);

        $query = $category->products()
            ->select([
                'id',
                'slug',
                'title',
                'game_name',
                'price',
                'images',
                'category_id',
                'created_at',
            ])
            ->when(
                isset($filters['min_price']),
                fn ($query) => $query->where('price', '>=', $filters['min_price']),
            )
            ->when(
                isset($filters['max_price']),
                fn ($query) => $query->where('price', '<=', $filters['max_price']),
            );

        match ($filters['sort'] ?? 'latest') {
            'lowest' => $query->orderBy('price')->orderByDesc('id'),
            'highest' => $query->orderByDesc('price')->orderByDesc('id'),
            default => $query->latest()->orderByDesc('id'),
        };

        $products = $query
            ->paginate(24)
            ->withQueryString()
            ->through(fn (Product $product): array => [
                'id' => $product->id,
                'slug' => $product->slug,
                'title' => $product->title,
                'game_name' => $product->game_name,
                'price' => $product->price,
                'category' => $category->name,
                'image' => filled($product->images)
                    ? asset('storage/'.$product->images[0])
                    : null,
            ]);

        return Inertia::render('Market/Game', [
            'products' => $products,
            'filters' => array_filter(
                $filters,
                fn ($value): bool => $value !== null && $value !== '',
            ),
            'activeGame' => $category->name,
            'activeGameSlug' => $category->slug,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | DETAIL AKUN
    |--------------------------------------------------------------------------
    */
    public function show(Category $category, Product $product)
    {
        // Optional: Ensure product belongs to category
        if ($product->category_id !== $category->id) {
            abort(404);
        }

        return Inertia::render('akun/Show', [
            'product' => [
                'id' => $product->id,
                'title' => $product->title,
                'game_name' => $product->game_name,
                'description' => $product->description,
                'price' => $product->price,
                'seller_name' => $product->seller_name,
                'seller_whatsapp' => $product->seller_whatsapp,
                'category' => $category->name,
                'slug' => $category->slug,
                'rekber_contact_url' => filled(config('seo.whatsapp'))
                    ? 'https://wa.me/'.config('seo.whatsapp')
                    : null,
                'images' => collect($product->images ?? [])
                    ->map(fn ($img) => asset('storage/'.$img))
                    ->values(),
            ],
        ]);
    }
}
