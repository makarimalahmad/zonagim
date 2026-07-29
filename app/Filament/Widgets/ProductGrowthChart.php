<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ProductGrowthChart extends ChartWidget
{
    protected ?string $heading = 'Pertumbuhan Produk';

    protected static ?int $sort = 2;

    protected ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $start = now()->subMonths(5)->startOfMonth();
        $monthExpression = match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', created_at)",
            'pgsql' => "to_char(created_at, 'YYYY-MM')",
            default => "DATE_FORMAT(created_at, '%Y-%m')",
        };

        $monthlyCounts = Product::query()
            ->selectRaw("{$monthExpression} as month_key, COUNT(*) as aggregate")
            ->where('created_at', '>=', $start)
            ->groupByRaw($monthExpression)
            ->pluck('aggregate', 'month_key');

        // Siapkan label & data untuk 6 bulan terakhir (termasuk bulan tanpa data).
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $key = $date->format('Y-m');
            $labels[] = $date->translatedFormat('M Y');
            $values[] = (int) $monthlyCounts->get($key, 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Produk Baru',
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
