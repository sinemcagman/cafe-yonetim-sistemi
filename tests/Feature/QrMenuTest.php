<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Product;
use App\Models\User;
use App\Support\QrCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrMenuTest extends TestCase
{
    use RefreshDatabase;

    private Category $drinks;

    protected function setUp(): void
    {
        parent::setUp();

        $this->drinks = Category::factory()->create(['name' => 'Sıcak İçecekler', 'sort_order' => 1]);
    }

    public function test_guest_sees_active_products_with_prices_and_sold_out_state(): void
    {
        Product::factory()->for($this->drinks)->create(['name' => 'Türk Kahvesi', 'price' => 60, 'stock_quantity' => 20, 'description' => 'Közde, lokumla']);
        Product::factory()->for($this->drinks)->create(['name' => 'Salep', 'price' => 70, 'stock_quantity' => 0]);
        Product::factory()->for($this->drinks)->create(['name' => 'Gizli Ürün', 'is_active' => false]);

        $this->get(route('menu'))
            ->assertOk()
            ->assertSeeInOrder(['Sıcak İçecekler', 'Salep', 'Tükendi', '70,00 ₺', 'Türk Kahvesi', 'Közde, lokumla', '60,00 ₺'])
            ->assertDontSee('Gizli Ürün')
            ->assertDontSee('20 adet');
    }

    public function test_inactive_and_empty_categories_are_hidden(): void
    {
        Product::factory()->for($this->drinks)->create(['name' => 'Çay']);
        Category::factory()->create(['name' => 'Boş Kategori']);
        $hidden = Category::factory()->create(['name' => 'Kapalı Kategori', 'is_active' => false]);
        Product::factory()->for($hidden)->create(['name' => 'Görünmeyen Tatlı']);

        $this->get(route('menu'))
            ->assertOk()
            ->assertSee('Çay')
            ->assertDontSee('Boş Kategori')
            ->assertDontSee('Görünmeyen Tatlı');
    }

    public function test_table_number_from_qr_is_greeted_only_for_active_tables(): void
    {
        Product::factory()->for($this->drinks)->create();
        DiningTable::factory()->create(['number' => 4]);
        DiningTable::factory()->create(['number' => 8, 'is_active' => false]);

        $this->get(route('menu', ['masa' => 4]))->assertOk()->assertSee('Hoş geldiniz · Masa 4');
        $this->get(route('menu', ['masa' => 8]))->assertOk()->assertDontSee('Hoş geldiniz');
        $this->get(route('menu', ['masa' => 'abc']))->assertOk()->assertDontSee('Hoş geldiniz');
    }

    public function test_logged_in_staff_can_open_the_menu_too(): void
    {
        $waiter = User::factory()->role(UserRole::Garson)->create();

        $this->actingAs($waiter)->get(route('menu'))->assertOk()->assertSee('Menü hazırlanıyor');
    }

    public function test_qr_code_is_an_inline_svg(): void
    {
        $svg = QrCode::svg('http://localhost/menu?masa=4');

        $this->assertStringStartsWith('<svg role="img" aria-label="QR kod" ', $svg);
        $this->assertStringNotContainsString('<?xml', $svg);
    }

    public function test_manager_prints_cards_for_selected_or_all_active_tables(): void
    {
        $manager = User::factory()->role(UserRole::Yonetici)->create();
        $four = DiningTable::factory()->create(['number' => 4, 'area' => 'Bahçe']);
        DiningTable::factory()->create(['number' => 5]);
        DiningTable::factory()->create(['number' => 9, 'is_active' => false]);

        $this->actingAs($manager)
            ->get(route('tables.qr-cards'))
            ->assertOk()
            ->assertSeeInOrder(['Masa 4', 'Bahçe', 'Masa 5'])
            ->assertDontSee('Masa 9')
            ->assertDontSee('Menümüz')
            ->assertSee('aria-label="QR kod"', false);

        $this->actingAs($manager)
            ->get(route('tables.qr-cards', ['masalar' => [$four->id], 'genel' => 1]))
            ->assertOk()
            ->assertSeeInOrder(['Menümüz', 'Masa 4'])
            ->assertDontSee('Masa 5')
            // Test istekleri localhost'tan gelir: telefonların erişemeyeceği adres uyarısı
            ->assertSee('yalnızca bu bilgisayardan ulaşılır');

        $this->actingAs($manager)->get(route('tables.index'))->assertOk()->assertSee('QR Menü Kartları');
    }

    public function test_only_managers_can_print_cards(): void
    {
        $waiter = User::factory()->role(UserRole::Garson)->create();
        DiningTable::factory()->create();

        $this->get(route('tables.qr-cards'))->assertRedirect(route('login'));
        $this->actingAs($waiter)->get(route('tables.qr-cards'))->assertForbidden();
        $this->actingAs($waiter)->get(route('tables.index'))->assertOk()->assertDontSee('QR Menü Kartları');
    }
}
