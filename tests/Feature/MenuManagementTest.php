<?php

namespace Tests\Feature;

use App\Enums\StockMovementType;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->manager = User::factory()->role(UserRole::Yonetici)->create();
    }

    public function test_only_managers_can_access_menu_management(): void
    {
        $waiter = User::factory()->role(UserRole::Garson)->create();

        $this->actingAs($waiter)->get(route('products.index'))->assertForbidden();
        $this->actingAs($waiter)->get(route('categories.index'))->assertForbidden();
        $this->actingAs($this->manager)->get(route('products.index'))->assertOk();
        $this->actingAs($this->manager)->get(route('categories.index'))->assertOk();
        $this->actingAs($this->manager)->get(route('stock-movements.index'))->assertOk();
    }

    public function test_menu_forms_can_be_rendered(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->manager)->get(route('categories.create'))->assertOk();
        $this->actingAs($this->manager)->get(route('categories.edit', $product->category))->assertOk();
        $this->actingAs($this->manager)->get(route('products.create'))->assertOk();
        $this->actingAs($this->manager)->get(route('products.edit', $product))->assertOk()->assertSee($product->name);
    }

    public function test_manager_can_create_category(): void
    {
        $this->actingAs($this->manager)->post(route('categories.store'), [
            'name' => 'İçecekler',
            'sort_order' => 1,
            'is_active' => 1,
        ])->assertRedirect(route('categories.index'));

        $this->assertDatabaseHas('categories', ['name' => 'İçecekler', 'is_active' => true]);
    }

    public function test_category_with_products_can_not_be_deleted(): void
    {
        $product = Product::factory()->create();

        $this->actingAs($this->manager)
            ->delete(route('categories.destroy', $product->category))
            ->assertSessionHas('error');

        $this->assertModelExists($product->category);
    }

    public function test_creating_product_records_initial_stock_movement(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->manager)->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'Çay',
            'price' => '15,50',
            'stock_quantity' => 100,
            'low_stock_threshold' => 10,
            'is_active' => 1,
        ])->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Çay')->firstOrFail();

        $this->assertSame('15.50', $product->price);
        $this->assertSame(100, $product->stock_quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => StockMovementType::Giris->value,
            'quantity' => 100,
            'quantity_before' => 0,
            'quantity_after' => 100,
        ]);
    }

    public function test_updating_product_does_not_change_stock_quantity(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 20]);

        $this->actingAs($this->manager)->put(route('products.update', $product), [
            'category_id' => $product->category_id,
            'name' => 'Yeni Ad',
            'price' => 30,
            'stock_quantity' => 999,
            'low_stock_threshold' => 5,
            'is_active' => 0,
        ])->assertRedirect(route('products.index'));

        $product->refresh();

        $this->assertSame('Yeni Ad', $product->name);
        $this->assertSame(20, $product->stock_quantity);
        $this->assertFalse($product->is_active);
    }

    public function test_stock_entry_increases_quantity_with_reason(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $this->actingAs($this->manager)->post(route('products.stock', $product), [
            'type' => StockMovementType::Giris->value,
            'quantity' => 50,
            'reason' => 'Tedarikçiden ürün geldi',
        ])->assertSessionHas('success');

        $this->assertSame(55, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'quantity' => 50,
            'reason' => 'Tedarikçiden ürün geldi',
            'user_id' => $this->manager->id,
        ]);
    }

    public function test_stock_entry_requires_positive_quantity(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5]);

        $this->actingAs($this->manager)->post(route('products.stock', $product), [
            'type' => StockMovementType::Giris->value,
            'quantity' => -3,
            'reason' => 'Hatalı giriş',
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(5, $product->fresh()->stock_quantity);
    }

    public function test_stock_correction_can_not_drop_below_zero(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 2]);

        $this->actingAs($this->manager)->post(route('products.stock', $product), [
            'type' => StockMovementType::Duzeltme->value,
            'quantity' => -5,
            'reason' => 'Fire',
        ])->assertSessionHas('error');

        $this->assertSame(2, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('stock_movements', 0);
    }
}
