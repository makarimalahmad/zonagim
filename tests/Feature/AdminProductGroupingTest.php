<?php

namespace Tests\Feature;

use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProductGroupingTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_are_grouped_and_searchable_by_game_category(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $freeFire = Category::create(['name' => 'Free Fire']);
        $valorant = Category::create(['name' => 'Valorant']);
        $freeFireProduct = Product::create([
            'category_id' => $freeFire->getKey(),
            'game_name' => $freeFire->name,
            'title' => 'Akun Sultan',
            'price' => 250000,
        ]);
        $valorantProduct = Product::create([
            'category_id' => $valorant->getKey(),
            'game_name' => $valorant->name,
            'title' => 'Akun Radiant',
            'price' => 500000,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListProducts::class)
            ->assertSee('Free Fire')
            ->assertSee('Valorant')
            ->searchTable('Free Fire')
            ->assertCanSeeTableRecords([$freeFireProduct])
            ->assertCanNotSeeTableRecords([$valorantProduct]);
    }
}
