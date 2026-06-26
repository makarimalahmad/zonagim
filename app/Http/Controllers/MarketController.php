<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
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
            ->map(fn($game) => [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'image' => $game->image ? asset('storage/' . $game->image) : null,
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

    public function game(\Illuminate\Http\Request $request, Category $category)
    {
        $query = $category->products();

        // 1. Filter Rentang Harga
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // 2. Sorting
        if ($request->sort === 'lowest') {
            $query->orderBy('price', 'asc');
        } elseif ($request->sort === 'highest') {
            $query->orderBy('price', 'desc');
        } else {
            $query->latest(); // Default: Terbaru
        }

        $products = $query->select([
            'id',
            'game_name',
            'price',
            'images',
            'category_id',
        ])
            ->get()
            ->map(fn($product) => [
                'id' => $product->id,
                'game_name' => $product->game_name,
                'price' => $product->price,
                'category' => $category->name,
                'image' => is_array($product->images) && count($product->images)
                    ? asset('storage/' . $product->images[0])
                    : null,
            ]);

        return Inertia::render('Market/Game', [
            'products' => $products,
            'filters' => array_filter($request->only(['sort', 'min_price', 'max_price']), fn($value) => !is_null($value) && $value !== ''),
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
                'images' => collect($product->images ?? [])
                    ->map(fn($img) => asset('storage/' . $img))
                    ->values(),
            ],
        ]);
    }
}
