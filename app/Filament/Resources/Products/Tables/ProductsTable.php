<?php

namespace App\Filament\Resources\Products\Tables;

use App\Filament\Actions\PersistentBulkActionGroup;
use App\Filament\Actions\PersistentDeleteBulkAction;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->prefix('#')
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                ImageColumn::make('images')
                    ->label('Foto')
                    ->disk('public')
                    ->square()
                    ->imageHeight(48)
                    ->limit(1),

                TextColumn::make('category.name')
                    ->label('Kategori Game')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('title')
                    ->label('Judul Akun')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('seller_name')
                    ->label('Penjual')
                    ->description(fn (Product $record): ?string => $record->seller_whatsapp)
                    ->searchable(['seller_name', 'seller_whatsapp'])
                    ->copyable(),

                TextColumn::make('slug')
                    ->label('Slug Publik')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->since()
                    ->dateTimeTooltip('d M Y, H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Kategori'),

                Filter::make('price_range')
                    ->label('Rentang Harga')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('min_price')
                                    ->integer()
                                    ->minValue(0)
                                    ->label('Harga Minimum'),
                                TextInput::make('max_price')
                                    ->integer()
                                    ->minValue(0)
                                    ->label('Harga Maksimum'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['min_price'] ?? null),
                                fn (Builder $query): Builder => $query->where('price', '>=', $data['min_price']),
                            )
                            ->when(
                                filled($data['max_price'] ?? null),
                                fn (Builder $query): Builder => $query->where('price', '<=', $data['max_price']),
                            );
                    }),
            ])
            ->groups([
                Group::make('category.name')
                    ->label('Kategori Game')
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn (Product $record): string => $record->category?->name ?? 'Tanpa kategori')
                    ->collapsible(),
            ])
            ->defaultGroup('category.name')
            ->groupingSettingsHidden()
            ->paginationPageOptions([10, 25, 50])
            ->defaultPaginationPageOption(25)
            ->recordActions([
                EditAction::make()->label('Ubah'),
            ])
            ->toolbarActions([
                PersistentBulkActionGroup::make([
                    PersistentDeleteBulkAction::make()
                        ->modalSubmitAction(fn (Action $action): Action => $action
                            ->extraAttributes(['class' => 'app-delete-action'])),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum ada produk')
            ->emptyStateDescription('Tambahkan listing akun pertama untuk mulai mengisi marketplace.');
    }
}
