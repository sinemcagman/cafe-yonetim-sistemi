<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Raporlar (yalnızca yönetici): ciro, en çok satanlar, saatlik yoğunluk ve kategori dağılımı.
 */
class ReportController extends Controller
{
    /**
     * Hazır dönem kısayolları: anahtar => [başlangıç, bitiş]
     */
    private function presets(): array
    {
        return [
            'bugun' => ['Bugün', today(), today()],
            '7gun' => ['Son 7 gün', today()->subDays(6), today()],
            '30gun' => ['Son 30 gün', today()->subDays(29), today()],
            'buay' => ['Bu ay', today()->startOfMonth(), today()],
        ];
    }

    public function index(Request $request): View
    {
        $presets = $this->presets();
        $preset = array_key_exists((string) $request->input('donem'), $presets) ? $request->input('donem') : null;

        if ($preset) {
            [, $from, $to] = $presets[$preset];
        } elseif ($range = $this->dateRange($request)) {
            [$from, $to] = $range;
        } else {
            $preset = '7gun';
            [, $from, $to] = $presets[$preset];
        }

        // Çok uzun aralıklar grafiği okunmaz yapar: en fazla 92 gün
        if ($from->diffInDays($to) > 91) {
            $from = $to->copy()->subDays(91);
        }

        $report = ReportService::forRange($from, $to);

        return view('reports.index', [
            'report' => $report,
            'preset' => $preset,
            'presets' => collect($presets)->map(fn ($p) => $p[0]),
            'summary' => $report->summary(),
            'daily' => $report->dailyRevenue(),
            'topProducts' => $report->topProducts(),
            'categories' => $report->categoryRevenue(),
            'hourly' => $report->hourlyOrders(),
        ]);
    }
}
