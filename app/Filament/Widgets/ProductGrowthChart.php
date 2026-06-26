<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProductGrowthChart extends ChartWidget
{
    protected ?string $heading = 'Product Growth';

    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Ambil produk 6 bulan terakhir lalu kelompokkan per bulan di PHP.
        // Dibuat database-agnostic (aman untuk SQLite maupun MySQL) —
        // sebelumnya pakai DATE_FORMAT() yang khusus MySQL dan error di SQLite.
        $start = now()->subMonths(5)->startOfMonth();

        $monthlyCounts = Product::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at'])
            ->groupBy(fn (Product $product) => $product->created_at->format('Y-m'))
            ->map->count();

        // Siapkan label & data untuk 6 bulan terakhir (termasuk bulan tanpa data).
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');
            $labels[] = $date->format('M Y');
            $values[] = $monthlyCounts->get($key, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'New Products',
                    'data' => $values,
                    'backgroundColor' => 'rgba(234, 179, 8, 0.2)',
                    'borderColor' => '#eab308',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
