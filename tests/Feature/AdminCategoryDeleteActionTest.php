<?php

namespace Tests\Feature;

use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCategoryDeleteActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_empty_category_can_be_deleted_from_table_but_used_category_cannot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $emptyCategory = Category::create(['name' => 'Game Kosong']);
        $usedCategory = Category::create(['name' => 'Game Terpakai']);
        Product::create([
            'category_id' => $usedCategory->getKey(),
            'game_name' => $usedCategory->name,
            'title' => 'Akun Game',
            'price' => 100000,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListCategories::class)
            ->assertSee('Tindakan')
            ->assertSeeHtml('title="Buka tindakan massal"')
            ->assertSeeHtml('x-bind:disabled="getSelectedRecordsCount() === 0"')
            ->assertSeeHtml("x-on:click=\"toggleSelectedRecord('{$emptyCategory->getKey()}')\"")
            ->assertSeeHtml("x-on:click=\"toggleSelectedRecord('{$usedCategory->getKey()}')\"")
            ->assertTableActionEnabled('delete', $emptyCategory)
            ->assertTableActionDisabled('delete', $usedCategory)
            ->callTableAction('delete', $emptyCategory);

        $this->assertDatabaseMissing('categories', ['id' => $emptyCategory->getKey()]);
        $this->assertDatabaseHas('categories', ['id' => $usedCategory->getKey()]);
    }

    public function test_bulk_delete_skips_selected_categories_that_still_have_products(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $emptyCategory = Category::create(['name' => 'Game Kosong']);
        $usedCategory = Category::create(['name' => 'Game Terpakai']);
        Product::create([
            'category_id' => $usedCategory->getKey(),
            'game_name' => $usedCategory->name,
            'title' => 'Akun Game',
            'price' => 100000,
        ]);

        $this->actingAs($admin);

        Livewire::test(ListCategories::class)
            ->callTableBulkAction('delete', [$emptyCategory, $usedCategory]);

        $this->assertDatabaseMissing('categories', ['id' => $emptyCategory->getKey()]);
        $this->assertDatabaseHas('categories', ['id' => $usedCategory->getKey()]);
        $this->assertDatabaseHas('products', ['category_id' => $usedCategory->getKey()]);
    }
}
