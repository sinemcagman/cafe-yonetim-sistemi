<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\DiningTable;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->role(UserRole::Yonetici)->create(['name' => 'Selin Aydın']);
    }

    public function test_only_managers_can_manage_users_and_see_activity_log(): void
    {
        $waiter = User::factory()->role(UserRole::Garson)->create();

        $this->actingAs($waiter)->get(route('users.index'))->assertForbidden();
        $this->actingAs($waiter)->get(route('activity-logs.index'))->assertForbidden();
        $this->actingAs($this->manager)->get(route('users.index'))->assertOk()->assertSee('Selin Aydın');
        $this->actingAs($this->manager)->get(route('users.create'))->assertOk();
        $this->actingAs($this->manager)->get(route('activity-logs.index'))->assertOk();
    }

    public function test_manager_adds_employee_who_can_then_log_in(): void
    {
        $this->actingAs($this->manager)->post(route('users.store'), [
            'name' => 'Can Yıldız',
            'email' => 'can@kafe.test',
            'phone' => '0532 111 22 33',
            'role' => UserRole::Kasiyer->value,
            'is_active' => 1,
            'password' => 'kahve2026',
            'password_confirmation' => 'kahve2026',
        ])->assertRedirect(route('users.index'));

        $user = User::where('email', 'can@kafe.test')->firstOrFail();
        $this->assertSame(UserRole::Kasiyer, $user->role);
        $this->assertTrue(Hash::check('kahve2026', $user->password));
        $this->assertDatabaseHas('activity_logs', ['action' => 'calisan_eklendi', 'subject_id' => $user->id]);

        auth()->logout();
        $this->post(route('login'), ['email' => 'can@kafe.test', 'password' => 'kahve2026'])->assertRedirect(route('dashboard'));
    }

    public function test_weak_password_and_duplicate_email_are_rejected(): void
    {
        $this->actingAs($this->manager)->post(route('users.store'), [
            'name' => 'Deneme',
            'email' => $this->manager->email,
            'role' => UserRole::Garson->value,
            'password' => 'kisa',
            'password_confirmation' => 'kisa',
        ])->assertSessionHasErrors(['email', 'password']);
    }

    public function test_updating_without_password_keeps_the_old_password(): void
    {
        $waiter = User::factory()->role(UserRole::Garson)->create();
        $hash = $waiter->password;

        $this->actingAs($this->manager)->put(route('users.update', $waiter), [
            'name' => 'Yeni Ad',
            'email' => $waiter->email,
            'role' => UserRole::Kasiyer->value,
            'is_active' => 1,
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect(route('users.index'));

        $waiter->refresh();
        $this->assertSame('Yeni Ad', $waiter->name);
        $this->assertSame(UserRole::Kasiyer, $waiter->role);
        $this->assertSame($hash, $waiter->password);
    }

    public function test_manager_can_not_deactivate_or_demote_self_and_last_manager_is_protected(): void
    {
        $payload = ['name' => $this->manager->name, 'email' => $this->manager->email, 'role' => UserRole::Yonetici->value, 'is_active' => 0];

        $this->actingAs($this->manager)->put(route('users.update', $this->manager), $payload)->assertSessionHas('error');
        $this->actingAs($this->manager)->put(route('users.update', $this->manager), ['role' => UserRole::Garson->value, 'is_active' => 1] + $payload)->assertSessionHas('error');
        $this->assertTrue($this->manager->fresh()->is_active);

        // Kendi hesabını silemez
        $this->actingAs($this->manager)->delete(route('users.destroy', $this->manager))->assertSessionHas('error');
        $this->assertModelExists($this->manager);

        // Başka bir aktif yönetici varken diğer yönetici pasif yapılabilir
        $other = User::factory()->role(UserRole::Yonetici)->create();
        $this->actingAs($this->manager)->put(route('users.update', $other), [
            'name' => $other->name, 'email' => $other->email, 'role' => UserRole::Yonetici->value, 'is_active' => 0,
        ])->assertSessionHas('success');
        $this->assertFalse($other->fresh()->is_active);
    }

    public function test_deactivated_employee_is_logged_out_on_next_request(): void
    {
        $waiter = User::factory()->role(UserRole::Garson)->create();

        $this->actingAs($this->manager)->put(route('users.update', $waiter), [
            'name' => $waiter->name,
            'email' => $waiter->email,
            'role' => UserRole::Garson->value,
            'is_active' => 0,
        ])->assertRedirect(route('users.index'));

        $this->assertFalse($waiter->fresh()->is_active);

        $this->actingAs($waiter->fresh())->get(route('dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_employee_with_order_history_can_not_be_deleted(): void
    {
        $waiter = User::factory()->role(UserRole::Garson)->create();
        $table = DiningTable::factory()->create();
        $this->actingAs($waiter)->post(route('tables.open-order', $table));

        $this->actingAs($this->manager)->delete(route('users.destroy', $waiter))->assertSessionHas('error');
        $this->assertModelExists($waiter);

        $newcomer = User::factory()->role(UserRole::Kasiyer)->create();
        $this->actingAs($this->manager)->delete(route('users.destroy', $newcomer))->assertSessionHas('success');
        $this->assertModelMissing($newcomer);
    }

    public function test_activity_log_can_be_filtered_by_employee_group_and_text(): void
    {
        $waiter = User::factory()->role(UserRole::Garson)->create(['name' => 'Ayşe Demir']);
        $table = DiningTable::factory()->create(['number' => 4]);
        $this->actingAs($waiter)->post(route('tables.open-order', $table));
        ActivityLog::create(['user_id' => $this->manager->id, 'action' => 'kasa_acildi', 'description' => 'Kasa 500,00 ₺ başlangıç nakdiyle açıldı.']);

        $this->actingAs($this->manager)->get(route('activity-logs.index', ['calisan' => $waiter->id]))
            ->assertOk()->assertSee('Masa 4 için')->assertDontSee('Kasa 500,00');

        $this->actingAs($this->manager)->get(route('activity-logs.index', ['grup' => 'kasa']))
            ->assertOk()->assertSee('Kasa 500,00')->assertDontSee('Masa 4 için');

        $this->actingAs($this->manager)->get(route('activity-logs.index', ['ara' => 'Masa 4', 'tarih' => today()->toDateString()]))
            ->assertOk()->assertSee('Masa 4 için')->assertSee('Siparişe git');
    }

    public function test_employee_updates_own_profile_and_password(): void
    {
        $cashier = User::factory()->role(UserRole::Kasiyer)->create(['password' => 'eskiSifre1']);

        $this->actingAs($cashier)->get(route('profile.edit'))->assertOk()->assertSee('Şifre değiştir');

        $this->actingAs($cashier)->patch(route('profile.update'), ['name' => 'Mehmet Kaya', 'email' => $cashier->email, 'phone' => '0555 000 11 22'])
            ->assertSessionHas('success');
        $this->assertSame('Mehmet Kaya', $cashier->fresh()->name);

        $this->actingAs($cashier)->put(route('profile.password'), ['current_password' => 'yanlis', 'password' => 'yeniSifre2', 'password_confirmation' => 'yeniSifre2'])
            ->assertSessionHasErrorsIn('password', 'current_password');

        $this->actingAs($cashier)->put(route('profile.password'), ['current_password' => 'eskiSifre1', 'password' => 'yeniSifre2', 'password_confirmation' => 'yeniSifre2'])
            ->assertSessionHas('success');
        $this->assertTrue(Hash::check('yeniSifre2', $cashier->fresh()->password));
    }
}
