<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\UserRequest;
use App\Models\ActivityLog;
use App\Models\CashSession;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Çalışan yönetimi (yalnızca yönetici).
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->addSelect(['last_login_at' => ActivityLog::select('created_at')
                ->whereColumn('user_id', 'users.id')
                ->where('action', 'giris')
                ->latest('created_at')
                ->limit(1)])
            ->withCasts(['last_login_at' => 'datetime'])
            ->when($request->filled('ara'), fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', '%'.$request->string('ara').'%')
                ->orWhere('email', 'like', '%'.$request->string('ara').'%')))
            ->when($request->filled('rol'), fn ($q) => $q->where('role', $request->string('rol')))
            ->when($request->filled('durum'), fn ($q) => $q->where('is_active', $request->input('durum') === 'aktif'))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate($this->perPage($request))
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => UserRole::cases(),
            'roleCounts' => User::query()->where('is_active', true)->selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role'),
            'inactiveCount' => User::where('is_active', false)->count(),
        ]);
    }

    public function create(): View
    {
        return view('users.create', ['user' => new User(['role' => UserRole::Garson, 'is_active' => true])]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create($request->validated());

        ActivityLog::record('calisan_eklendi', "{$user->name} ({$user->role->label()}) çalışan olarak eklendi.", $user);

        return redirect()->route('users.index')->with('success', "{$user->name} eklendi. Giriş bilgilerini kendisine iletebilirsiniz.");
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $role = UserRole::from($data['role']);
        $isSelf = $user->is($request->user());

        if ($isSelf && (! $data['is_active'] || $role !== UserRole::Yonetici)) {
            return back()->withInput()->with('error', 'Kendi hesabınızı pasif yapamaz ya da yöneticilikten çıkaramazsınız.');
        }

        if ($this->wouldRemoveLastManager($user, $role, $data['is_active'])) {
            return back()->withInput()->with('error', 'Sistemde en az bir aktif yönetici kalmalıdır.');
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        // Pasif yapılan çalışanın açık oturumlarını hemen sonlandır
        if (! $user->is_active) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        $changes = collect($user->getChanges())->except(['password', 'remember_token', 'updated_at']);
        ActivityLog::record('calisan_guncellendi', "{$user->name} bilgileri güncellendi".(isset($data['password']) ? ' (şifre değiştirildi)' : '').'.', $user, $changes->all());

        return redirect()->route('users.index')->with('success', "{$user->name} güncellendi.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Kendi hesabınızı silemezsiniz.');
        }

        $hasHistory = $user->orders()->exists()
            || OrderItem::where('user_id', $user->id)->exists()
            || Payment::where('user_id', $user->id)->exists()
            || CashSession::where('opened_by', $user->id)->exists();

        if ($hasHistory) {
            return back()->with('error', "{$user->name} sipariş ya da kasa kayıtlarında geçtiği için silinemez. Hesabı pasif yapabilirsiniz.");
        }

        if ($this->wouldRemoveLastManager($user, UserRole::Garson, false)) {
            return back()->with('error', 'Sistemde en az bir aktif yönetici kalmalıdır.');
        }

        DB::table('sessions')->where('user_id', $user->id)->delete();
        $user->delete();

        ActivityLog::record('calisan_silindi', "{$user->name} çalışan kaydı silindi.");

        return redirect()->route('users.index')->with('success', "{$user->name} silindi.");
    }

    private function wouldRemoveLastManager(User $user, UserRole $newRole, bool $stillActive): bool
    {
        $isActiveManager = $user->is_active && $user->role === UserRole::Yonetici;
        $staysManager = $stillActive && $newRole === UserRole::Yonetici;

        return $isActiveManager && ! $staysManager
            && ! User::where('role', UserRole::Yonetici)->where('is_active', true)->whereKeyNot($user->id)->exists();
    }
}
