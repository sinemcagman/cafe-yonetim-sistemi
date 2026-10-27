<?php

namespace App\Http\Controllers;

use App\Enums\TableStatus;
use App\Http\Requests\DiningTableRequest;
use App\Models\ActivityLog;
use App\Models\DiningTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DiningTableController extends Controller
{
    /**
     * Tüm çalışanların masaların boş/dolu durumunu gördüğü ekran.
     */
    public function index(): View
    {
        $tables = DiningTable::query()
            ->with('openOrder.user')
            ->when(! auth()->user()->isManager(), fn ($query) => $query->active())
            ->orderBy('number')
            ->get();

        return view('tables.index', [
            'tables' => $tables,
            'areas' => $tables->groupBy(fn (DiningTable $table) => $table->area ?: 'Diğer'),
            'availableCount' => $tables->where('is_active', true)->where('status', TableStatus::Bos)->count(),
            'occupiedCount' => $tables->where('status', TableStatus::Dolu)->count(),
        ]);
    }

    public function create(): View
    {
        return view('tables.create', [
            'table' => new DiningTable([
                'number' => (int) DiningTable::max('number') + 1,
                'capacity' => 4,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(DiningTableRequest $request): RedirectResponse
    {
        $table = DiningTable::create($request->validated() + ['status' => TableStatus::Bos]);

        ActivityLog::record('masa_eklendi', "{$table->label()} eklendi.", $table);

        return redirect()->route('tables.index')->with('success', "{$table->label()} eklendi.");
    }

    public function edit(DiningTable $table): View
    {
        return view('tables.edit', compact('table'));
    }

    public function update(DiningTableRequest $request, DiningTable $table): RedirectResponse
    {
        $data = $request->validated();

        if (! $data['is_active'] && ! $table->isAvailable()) {
            return back()->withInput()->with('error', 'Kullanımdaki bir masa pasif yapılamaz. Önce hesabı kapatın.');
        }

        $table->update($data);

        ActivityLog::record('masa_guncellendi', "{$table->label()} güncellendi.", $table, $table->getChanges());

        return redirect()->route('tables.index')->with('success', "{$table->label()} güncellendi.");
    }

    public function destroy(DiningTable $table): RedirectResponse
    {
        if ($table->orders()->exists()) {
            return back()->with('error', 'Sipariş geçmişi olan masa silinemez. Masayı pasif yapabilirsiniz.');
        }

        $table->delete();

        ActivityLog::record('masa_silindi', "{$table->label()} silindi.");

        return redirect()->route('tables.index')->with('success', "{$table->label()} silindi.");
    }
}
