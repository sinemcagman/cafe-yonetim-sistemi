<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Her çalışanın kendi bilgilerini ve şifresini değiştirdiği "Profilim" ekranı.
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'recentActivity' => ActivityLog::where('user_id', $request->user()->id)->latest('created_at')->latest('id')->limit(8)->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validateWithBag('profile', [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9 +()-]{7,20}$/'],
        ], ['phone.regex' => 'Telefon numarası yalnızca rakam, boşluk ve + ( ) - içerebilir.'], ['name' => 'ad soyad', 'email' => 'e-posta adresi', 'phone' => 'telefon']);

        $user->update($data);

        ActivityLog::record('profil_guncellendi', "{$user->name} profil bilgilerini güncelledi.", $user);

        return back()->with('success', 'Profil bilgileriniz kaydedildi.');
    }

    /**
     * Şifre değişince bu cihaz dışındaki tüm oturumlar kapatılır.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers(), 'different:current_password'],
        ], [
            'current_password.current_password' => 'Mevcut şifre hatalı.',
            'password.different' => 'Yeni şifre mevcut şifreyle aynı olamaz.',
        ], ['current_password' => 'mevcut şifre', 'password' => 'yeni şifre']);

        $user = $request->user();
        $user->update(['password' => $data['password']]);

        DB::table('sessions')->where('user_id', $user->id)->where('id', '!=', $request->session()->getId())->delete();

        ActivityLog::record('profil_sifre', "{$user->name} şifresini değiştirdi.", $user);

        return back()->with('success', 'Şifreniz değiştirildi; diğer cihazlardaki oturumlarınız kapatıldı.');
    }
}
