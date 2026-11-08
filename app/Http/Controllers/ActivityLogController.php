<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * İşlem geçmişi (yalnızca yönetici): kim, ne zaman, ne yaptı.
 */
class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->when($request->filled('calisan'), fn ($q) => $q->where('user_id', $request->integer('calisan')))
            ->when(array_key_exists((string) $request->input('grup'), ActivityLog::GROUPS), fn ($q) => $q->inGroup($request->input('grup')))
            ->when($request->filled('ara'), fn ($q) => $q->where('description', 'like', '%'.$request->string('ara').'%'))
            ->when($this->dateRange($request), fn ($q, array $range) => $q->whereBetween('created_at', $range))
            ->latest('created_at')
            ->latest('id')
            ->paginate($this->perPage($request))
            ->withQueryString();

        return view('activity-logs.index', [
            'logs' => $logs,
            'users' => User::orderBy('name')->get(['id', 'name', 'role']),
            'groups' => ActivityLog::GROUPS,
        ]);
    }
}
