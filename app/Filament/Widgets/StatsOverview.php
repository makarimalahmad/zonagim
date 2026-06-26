<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Product;
use App\Models\Category;
use App\Models\User;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Products', Product::count())
                ->description('7 new products this week')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),

            Stat::make('Total Categories', Category::count())
                ->description('Stable growth')
                ->descriptionIcon('heroicon-m-minus')
                ->chart([3, 5, 3, 4, 5, 6, 6])
                ->color('primary'),

            Stat::make('Total Users', User::count())
                ->description('3 new users joined')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([2, 5, 8, 3, 5, 6, 10])
                ->color('warning'),
        ];
    }
}
