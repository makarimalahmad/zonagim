<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Data Game / Kategori')
                ->description('Game ini akan tampil di halaman pasar untuk pembeli.')
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
                        ->label('Logo / Sampul Game')
                        ->image()
                        ->disk('public')
                        ->directory('categories')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->getUploadedFileNameForStorageUsing(
                            fn (TemporaryUploadedFile $file): string => Str::ulid().match ($file->getMimeType()) {
                                'image/jpeg' => '.jpg',
                                'image/png' => '.png',
                                'image/webp' => '.webp',
                                default => throw new \RuntimeException('Tipe gambar tidak diizinkan.'),
                            },
                        )
                        ->preventFilePathTampering()
                        ->imageEditor()
                        ->maxSize(2048)
                        ->helperText('Format gambar, maksimal 2 MB. Tampil sebagai sampul di halaman pasar.')
                        ->nullable()
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
