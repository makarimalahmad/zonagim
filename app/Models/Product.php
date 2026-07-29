<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'game_name',
        'title',
        'slug',
        'description',
        'images',
        'price',
        'seller_name',
        'seller_whatsapp',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if (blank($product->slug)) {
                $product->slug = self::makeUniqueSlug($product);
            }
        });

        static::updated(function (Product $product): void {
            if (! $product->wasChanged('images')) {
                return;
            }

            $oldImages = (array) $product->getOriginal('images');
            $currentImages = (array) $product->images;

            self::deleteOwnedImages(array_diff($oldImages, $currentImages));
        });

        static::deleted(function (Product $product): void {
            self::deleteOwnedImages((array) $product->images);
        });
    }

    private static function deleteOwnedImages(array $images): void
    {
        $paths = array_values(array_filter(
            $images,
            fn (mixed $path): bool => is_string($path)
                && str_starts_with($path, 'products/')
                && ! str_contains($path, '..'),
        ));

        if ($paths !== []) {
            Storage::disk('public')->delete($paths);
        }
    }

    private static function makeUniqueSlug(Product $product): string
    {
        $base = Str::slug($product->title ?: $product->game_name) ?: 'akun-game';

        do {
            $slug = $base.'-'.Str::lower(Str::random(7));
        } while (static::query()->where('slug', $slug)->exists());

        return $slug;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
