<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product; // Import Product model
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // 🖼️ Image
                ImageColumn::make('images')
                    ->label('Image')
                    ->disk('public') // Explicitly use public disk
                    ->square()
                    ->imageHeight(40)
                    ->stacked()
                    ->limit(1),

                // 🏷️ Category
                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                // 📝 Nickname / Title (Searchable, Hidden by default)
                TextColumn::make('title')
                    ->label('Nickname / Title')
                    ->searchable()
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                // 🎮 Game Info
                TextColumn::make('game_name')
                    ->label('Game')
                    ->searchable()
                    ->weight('bold')
                    ->wrap()
                    ->description(fn(Product $record): string => Str::limit($record->title ?? '', 40)),

                // 💰 Price
                TextColumn::make('price')
                    ->money('IDR', locale: 'id')
                    ->sortable()
                    ->weight('bold')
                    ->color('success'),

                // 👤 Seller Info (Hidden by default for cleaner look)
                TextColumn::make('seller_name')
                    ->label('Seller')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('seller_whatsapp')
                    ->label('WhatsApp')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Filter by Category'),

                Filter::make('price_range')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('min_price')
                                    ->numeric()
                                    ->label('Min Price'),
                                TextInput::make('max_price')
                                    ->numeric()
                                    ->label('Max Price'),
                            ]),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_price'],
                                fn(Builder $query, $date): Builder => $query->where('price', '>=', $date),
                            )
                            ->when(
                                $data['max_price'],
                                fn(Builder $query, $date): Builder => $query->where('price', '<=', $date),
                            );
                    })
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
