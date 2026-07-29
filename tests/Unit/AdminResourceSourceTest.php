<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AdminResourceSourceTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = dirname(__DIR__, 2);
    }

    public function test_admin_resources_use_stable_internal_ids_for_record_routes(): void
    {
        foreach ([
            'app/Filament/Resources/Categories/CategoryResource.php',
            'app/Filament/Resources/Products/ProductResource.php',
        ] as $path) {
            $source = file_get_contents($this->root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path));

            $this->assertStringContainsString("protected static ?string \$recordRouteKeyName = 'id';", $source);
        }
    }

    public function test_admin_tables_expose_internal_ids_as_optional_columns(): void
    {
        foreach ([
            'app/Filament/Resources/Categories/Tables/CategoriesTable.php',
            'app/Filament/Resources/Products/Tables/ProductsTable.php',
        ] as $path) {
            $source = file_get_contents($this->root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path));

            $this->assertStringContainsString("TextColumn::make('id')", $source);
            $this->assertStringContainsString("->label('ID')", $source);
            $this->assertStringContainsString('->copyable()', $source);
            $this->assertStringContainsString('->toggleable(isToggledHiddenByDefault: true)', $source);
        }
    }

    public function test_category_deletion_is_blocked_while_products_exist(): void
    {
        $edit = file_get_contents($this->root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'Categories'.DIRECTORY_SEPARATOR.'Pages'.DIRECTORY_SEPARATOR.'EditCategory.php');
        $table = file_get_contents($this->root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'Categories'.DIRECTORY_SEPARATOR.'Tables'.DIRECTORY_SEPARATOR.'CategoriesTable.php');

        $this->assertStringContainsString('products()->exists()', $edit);
        $this->assertStringNotContainsString('checkIfRecordIsSelectableUsing', $table);
        $this->assertStringContainsString('DeleteAction::make()', $table);
        $this->assertStringContainsString('->disabled(fn (Category $record): bool => $record->products_count > 0)', $table);
        $this->assertStringContainsString("'class' => 'app-delete-action'", $table);
        $this->assertStringContainsString('PersistentBulkActionGroup::make([', $table);
        $this->assertStringContainsString('PersistentDeleteBulkAction::make()', $table);
        $this->assertStringContainsString("->authorizeIndividualRecords('delete')", $table);
    }

    public function test_admin_interface_is_configured_in_indonesian(): void
    {
        $environment = file_get_contents($this->root.DIRECTORY_SEPARATOR.'.env.example');
        $category = file_get_contents($this->root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'Categories'.DIRECTORY_SEPARATOR.'CategoryResource.php');
        $product = file_get_contents($this->root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'Products'.DIRECTORY_SEPARATOR.'ProductResource.php');
        $user = file_get_contents($this->root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'Users'.DIRECTORY_SEPARATOR.'UserResource.php');
        $stats = file_get_contents($this->root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Widgets'.DIRECTORY_SEPARATOR.'StatsOverview.php');

        $this->assertStringContainsString('APP_LOCALE=id', $environment);
        $this->assertStringContainsString("protected static ?string \$navigationLabel = 'Kategori';", $category);
        $this->assertStringContainsString("protected static ?string \$navigationLabel = 'Produk';", $product);
        $this->assertStringContainsString("protected static ?string \$navigationLabel = 'Kelola Pengguna';", $user);
        $this->assertStringContainsString("Stat::make('Total Produk'", $stats);
        $this->assertStringContainsString("Stat::make('Total Kategori'", $stats);
        $this->assertStringContainsString("Stat::make('Total Pengguna'", $stats);
    }

    public function test_product_table_groups_by_searchable_game_category(): void
    {
        $source = file_get_contents($this->root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'Products'.DIRECTORY_SEPARATOR.'Tables'.DIRECTORY_SEPARATOR.'ProductsTable.php');

        $this->assertStringContainsString("TextColumn::make('category.name')", $source);
        $this->assertStringContainsString("->label('Kategori Game')", $source);
        $this->assertStringContainsString('->searchable()', $source);
        $this->assertStringContainsString("Group::make('category.name')", $source);
        $this->assertStringContainsString('->titlePrefixedWithLabel(false)', $source);
        $this->assertStringContainsString('->collapsible()', $source);
        $this->assertStringContainsString("->defaultGroup('category.name')", $source);
        $this->assertStringContainsString('->groupingSettingsHidden()', $source);
        $this->assertStringNotContainsString('->description(fn (Product $record): string => $record->category?->name', $source);
    }

    public function test_product_and_category_bulk_actions_stay_visible_but_disabled_without_selection(): void
    {
        $group = file_get_contents($this->root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Actions'.DIRECTORY_SEPARATOR.'PersistentBulkActionGroup.php');
        $delete = file_get_contents($this->root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Actions'.DIRECTORY_SEPARATOR.'PersistentDeleteBulkAction.php');
        $category = file_get_contents($this->root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'Categories'.DIRECTORY_SEPARATOR.'Tables'.DIRECTORY_SEPARATOR.'CategoriesTable.php');
        $product = file_get_contents($this->root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'Products'.DIRECTORY_SEPARATOR.'Tables'.DIRECTORY_SEPARATOR.'ProductsTable.php');

        $this->assertStringContainsString("\$this->label('Tindakan')", $group);
        $this->assertStringNotContainsString("'x-bind:disabled'", $group);
        $this->assertStringNotContainsString("'x-show' => 'getSelectedRecordsCount()'", $group);
        $this->assertStringContainsString("'x-bind:disabled' => 'getSelectedRecordsCount() === 0'", $delete);
        $this->assertStringContainsString("unset(\$attributes['x-cloak'], \$attributes['x-show'])", $delete);
        $this->assertStringContainsString('PersistentBulkActionGroup::make([', $category);
        $this->assertStringContainsString('PersistentBulkActionGroup::make([', $product);
        $this->assertStringContainsString('PersistentDeleteBulkAction::make()', $category);
        $this->assertStringContainsString('PersistentDeleteBulkAction::make()', $product);
    }

    public function test_all_filament_delete_actions_use_shared_solid_delete_style(): void
    {
        foreach ([
            'app/Filament/Resources/Products/Pages/EditProduct.php',
            'app/Filament/Resources/Categories/Pages/EditCategory.php',
            'app/Filament/Resources/Products/Tables/ProductsTable.php',
            'app/Filament/Auth/MultiFactor/AppAuthentication.php',
        ] as $path) {
            $source = file_get_contents($this->root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path));

            $this->assertStringContainsString("'class' => 'app-delete-action'", $source, $path);
        }

        $styles = file_get_contents($this->root.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'filament'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'theme.css');
        $this->assertStringContainsString('.fi-btn.app-delete-action', $styles);
        $this->assertStringContainsString('.fi-dropdown-list-item.app-delete-action', $styles);
        $this->assertStringContainsString('background: #dc2626;', $styles);
        $this->assertStringContainsString('background: #b91c1c;', $styles);
        $this->assertStringContainsString('.filepond--action-remove-item', $styles);
    }

    public function test_product_form_uses_category_as_game_source_and_limits_uploads(): void
    {
        $source = file_get_contents($this->root.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'Filament'.DIRECTORY_SEPARATOR.'Resources'.DIRECTORY_SEPARATOR.'Products'.DIRECTORY_SEPARATOR.'Schemas'.DIRECTORY_SEPARATOR.'ProductForm.php');

        $this->assertStringContainsString('Category::find($state)?->name', $source);
        $this->assertStringContainsString('->maxFiles(8)', $source);
        $this->assertStringContainsString('->maxSize(4096)', $source);
        $this->assertStringContainsString("->prefix('+62')", $source);
        $this->assertStringContainsString("->regex('/^8[0-9]{8,12}$/')", $source);
        $this->assertStringContainsString("return '62'.ltrim(\$digits, '0');", $source);
        $this->assertStringContainsString("'oninput'", $source);
        $this->assertStringContainsString("this.value.replace(/\\D/g, '')", $source);
        $this->assertStringContainsString("replace(/\\B(?=(\\d{3})+(?!\\d))/g, '.')", $source);
        $this->assertStringContainsString("'pattern' => '[0-9.]*'", $source);
        $this->assertStringContainsString('->formatStateUsing', $source);
        $this->assertStringContainsString('->mutateStateForValidationUsing', $source);
        $this->assertStringContainsString('->dehydrateStateUsing', $source);
        $this->assertStringContainsString("->rule('integer')", $source);
        $this->assertStringContainsString("->type('text')", $source);
    }
}
