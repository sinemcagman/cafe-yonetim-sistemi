<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InterfaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_menu_shows_management_links_only_to_managers(): void
    {
        $waiter = User::factory()->role(UserRole::Garson)->create();
        $manager = User::factory()->role(UserRole::Yonetici)->create();

        $this->actingAs($waiter)->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('tables.index'), false)
            ->assertDontSee(route('products.index'), false)
            ->assertDontSee('Menü Yönetimi');

        $this->actingAs($manager)->get(route('dashboard'))
            ->assertOk()
            ->assertSee(route('products.index'), false)
            ->assertSee('Menü Yönetimi');
    }

    public function test_menu_badge_and_notifications_show_low_stock_products(): void
    {
        $manager = User::factory()->role(UserRole::Yonetici)->create();
        Product::factory()->create(['name' => 'Sufle', 'stock_quantity' => 2, 'low_stock_threshold' => 5]);

        $this->actingAs($manager)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Kritik stoktaki ürün sayısı')
            ->assertSee('Son 2 adet');
    }

    public function test_navbar_shows_user_role_and_logout(): void
    {
        $cashier = User::factory()->role(UserRole::Kasiyer)->create(['name' => 'Mehmet Kaya']);

        $this->actingAs($cashier)->get(route('tables.index'))
            ->assertOk()
            ->assertSee('Mehmet Kaya')
            ->assertSee('Kasiyer')
            ->assertSee(route('logout'), false);
    }

    public function test_flash_messages_are_passed_to_notifications(): void
    {
        $manager = User::factory()->role(UserRole::Yonetici)->create();

        $this->actingAs($manager)
            ->withSession(['success' => 'Kategori eklendi.'])
            ->get(route('categories.index'))
            ->assertSee('window.flashMessages', false)
            ->assertSee('Kategori eklendi.');
    }

    public function test_turkish_formatted_prices_are_accepted(): void
    {
        $manager = User::factory()->role(UserRole::Yonetici)->create();
        $category = Category::factory()->create();

        $this->actingAs($manager)->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'Kahvaltı Tabağı',
            'price' => '1.250,50',
            'stock_quantity' => 3,
            'low_stock_threshold' => 1,
            'is_active' => 1,
        ])->assertRedirect(route('products.index'));

        $this->assertSame('1250.50', Product::where('name', 'Kahvaltı Tabağı')->value('price'));
    }

    public function test_category_icon_must_be_one_of_the_presets(): void
    {
        $manager = User::factory()->role(UserRole::Yonetici)->create();

        $this->actingAs($manager)->post(route('categories.store'), [
            'name' => 'Tatlılar',
            'icon' => 'fi fi-rr-cake',
            'sort_order' => 1,
        ])->assertSessionHasErrors('icon');

        $this->actingAs($manager)->post(route('categories.store'), [
            'name' => 'Tatlılar',
            'icon' => 'tabler-cake',
            'sort_order' => 1,
        ])->assertSessionHasNoErrors();
    }

    public function test_products_can_be_filtered_by_sale_status(): void
    {
        $manager = User::factory()->role(UserRole::Yonetici)->create();
        Product::factory()->create(['name' => 'Satıştaki Çay', 'is_active' => true]);
        Product::factory()->create(['name' => 'Durdurulan Sufle', 'is_active' => false]);

        $this->actingAs($manager)->get(route('products.index', ['durum' => 'durduruldu']))
            ->assertOk()
            ->assertSee('Durdurulan Sufle')
            ->assertDontSee('Satıştaki Çay');
    }

    public function test_demo_accounts_are_hidden_outside_local_environment(): void
    {
        $this->get(route('login'))->assertOk()->assertDontSee('Demo hesaplar');
    }
}
