<?php

namespace App\Filament\Widgets;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Produk', Product::count())
                ->description('Produk terdaftar')
                ->color('success'),

            Stat::make('Total Kategori', Category::count())
                ->description('Kategori tersedia')
                ->color('primary'),

            Stat::make('Total Pengguna', User::where('role', 'user')->count())
                ->description('Pengguna terdaftar')
                ->color('warning'),
        ];
    }
}
