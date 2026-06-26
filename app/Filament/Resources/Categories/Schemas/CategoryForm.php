<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Data Game / Kategori')
                ->description('Game ini akan tampil di halaman Market untuk pembeli.')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Game')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Mobile Legends')
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $state, callable $set) {
                            $set('slug', Str::slug($state));
                        }),

                    TextInput::make('slug')
                        ->label('Slug (URL)')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->helperText('Dibuat otomatis dari nama game.'),

                    FileUpload::make('image')
                        ->label('Logo / Cover Game')
                        ->image()
                        ->disk('public')
                        ->directory('categories')
                        ->imageEditor()
                        ->maxSize(2048)
                        ->helperText('Format gambar, maksimal 2MB. Tampil sebagai cover di halaman Market.')
                        ->nullable()
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
