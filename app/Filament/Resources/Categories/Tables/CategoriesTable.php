<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Filament\Actions\PersistentBulkActionGroup;
use App\Filament\Actions\PersistentDeleteBulkAction;
use App\Models\Category;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CategoriesTable
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

                ImageColumn::make('image')
                    ->label('Sampul')
                    ->disk('public')
                    ->square()
                    ->imageHeight(44),

                TextColumn::make('name')
                    ->label('Nama Game')
                    ->description(fn (Category $record): string => $record->slug)
                    ->searchable(['name', 'slug'])
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('products_count')
                    ->label('Jumlah Akun')
                    ->counts('products')
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'primary' : 'gray')
                    ->sortable(),

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
                SelectFilter::make('content')
                    ->label('Isi Kategori')
                    ->options([
                        'with_products' => 'Memiliki akun',
                        'empty' => 'Kosong',
                    ])
                    ->query(fn ($query, array $data) => match ($data['value'] ?? null) {
                        'with_products' => $query->has('products'),
                        'empty' => $query->doesntHave('products'),
                        default => $query,
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make()->label('Ubah'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->disabled(fn (Category $record): bool => $record->products_count > 0)
                    ->tooltip(fn (Category $record): ?string => $record->products_count > 0
                        ? 'Kategori tidak dapat dihapus selama masih memiliki produk.'
                        : 'Hapus kategori')
                    ->extraAttributes(['class' => 'app-delete-action'])
                    ->modalSubmitAction(fn (Action $action): Action => $action
                        ->label('Hapus kategori')
                        ->extraAttributes(['class' => 'app-delete-action'])),
            ])
            ->toolbarActions([
                PersistentBulkActionGroup::make([
                    PersistentDeleteBulkAction::make()
                        ->authorizeIndividualRecords('delete')
                        ->modalSubmitAction(fn (Action $action): Action => $action
                            ->extraAttributes(['class' => 'app-delete-action'])),
                ]),
            ])
            ->emptyStateHeading('Belum ada kategori')
            ->emptyStateDescription('Tambahkan game pertama untuk mulai membuat listing akun.');
    }
}
