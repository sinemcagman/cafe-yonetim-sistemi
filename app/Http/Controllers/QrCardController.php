<?php

namespace App\Http\Controllers;

use App\Models\DiningTable;
use App\Support\QrCode;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Masalara konan, müşteri menüsünü açan QR kartlarının A4 çıktısı.
 */
class QrCardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $data = $request->validate([
            'masalar' => ['nullable', 'array'],
            'masalar.*' => ['integer'],
            'genel' => ['nullable', 'boolean'],
        ]);

        // Masa seçilmediyse tüm aktif masaların kartı basılır
        $tables = DiningTable::active()
            ->when($data['masalar'] ?? null, fn ($q, array $ids) => $q->whereIn('id', $ids))
            ->orderBy('number')
            ->get();

        $cards = $tables->map(fn (DiningTable $table) => [
            'title' => $table->label(),
            'subtitle' => $table->area,
            'url' => route('menu', ['masa' => $table->number]),
        ]);

        // Kasa ya da vitrin için masa numarasız kart
        if ($request->boolean('genel')) {
            $cards->prepend(['title' => 'Menümüz', 'subtitle' => null, 'url' => route('menu')]);
        }

        return view('tables.qr-cards', [
            'cards' => $cards->map(fn (array $card) => $card + ['svg' => QrCode::svg($card['url'])]),
            'localHost' => in_array($request->getHost(), ['localhost', '127.0.0.1', '::1'], true),
        ]);
    }
}
