<?php

namespace App\Http\Controllers;

use App\Enums\TableStatus;
use App\Models\ActivityLog;
use App\Models\DiningTable;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $tables = DiningTable::active()->orderBy('number')->get();
        $isManager = $request->user()->isManager();

        return view('dashboard', [
            'tables' => $tables,
            'stats' => [
                'available_tables' => $tables->where('status', TableStatus::Bos)->count(),
                'total_tables' => $tables->count(),
                'open_orders' => Order::open()->count(),
                'today_revenue' => Payment::whereDate('paid_at', today())->sum('amount'),
                'low_stock' => Product::active()->lowStock()->count(),
            ],
            'lowStockProducts' => $isManager
                ? Product::active()->lowStock()->with('category')->orderBy('stock_quantity')->limit(6)->get()
                : collect(),
            'recentActivities' => $isManager
                ? ActivityLog::with('user')->latest('created_at')->latest('id')->limit(8)->get()
                : collect(),
        ]);
    }
}
