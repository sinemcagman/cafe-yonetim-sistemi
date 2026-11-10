<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_pages_send_security_headers(): void
    {
        foreach ([route('login'), route('menu')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
                ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
                ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()')
                ->assertHeader('Content-Security-Policy', "frame-ancestors 'self'; object-src 'none'; base-uri 'self'; form-action 'self'")
                ->assertHeaderMissing('Strict-Transport-Security');
        }
    }

    public function test_hsts_is_sent_only_over_https(): void
    {
        $this->get(str_replace('http://', 'https://', route('login')))
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_html_in_a_self_chosen_name_is_escaped_where_managers_see_it(): void
    {
        // Her çalışan Profilim'den adını değiştirebilir; yöneticinin ekranında HTML olarak çalışmamalı
        $payload = '<img src=x onerror=alert(1)>';
        $waiter = User::factory()->role(UserRole::Garson)->create();

        $this->actingAs($waiter)
            ->patch(route('profile.update'), ['name' => $payload, 'email' => $waiter->email])
            ->assertSessionHasNoErrors();
        $this->assertSame($payload, $waiter->fresh()->name);

        $manager = User::factory()->role(UserRole::Yonetici)->create();

        $this->actingAs($manager)
            ->get(route('users.index'))
            ->assertOk()
            ->assertDontSee($payload, false)
            ->assertSee(e($payload).' silinsin mi?', false);
    }

    public function test_login_is_rate_limited_per_email(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post(route('login'), ['email' => $user->email, 'password' => 'yanlis-sifre'])->assertSessionHasErrors('email');
        }

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
