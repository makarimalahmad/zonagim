<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
    ];

    // 🔥 AUTO SLUG
    protected static function booted()
    {
        static::saving(function ($category) {
            if ($category->isDirty('name')) {
                $baseSlug = Str::slug($category->name);
                $slug = $baseSlug;
                $count = 1;

                while (static::where('slug', $slug)
                    ->where('id', '!=', $category->id)
                    ->exists()
                ) {
                    $slug = $baseSlug.'-'.$count++;
                }

                $category->slug = $slug;
            }
        });

        static::updated(function (Category $category): void {
            if (! $category->wasChanged('image')) {
                return;
            }

            self::deleteOwnedImage($category->getPrevious()['image'] ?? null);
        });

        static::deleted(function (Category $category): void {
            self::deleteOwnedImage($category->image);
        });
    }

    private static function deleteOwnedImage(mixed $path): void
    {
        if (
            ! is_string($path)
            || ! str_starts_with($path, 'categories/')
            || str_contains($path, '..')
        ) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
