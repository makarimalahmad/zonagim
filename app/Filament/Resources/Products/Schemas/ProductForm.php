<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\Category;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            // ============================================================
            // 1. INFORMASI AKUN
            // ============================================================
            Section::make('Informasi Akun')
                ->description('Detail utama akun game yang akan dijual.')
                ->columns(2)
                ->schema([
                    Select::make('category_id')
                        ->label('Game / Kategori')
                        ->relationship('category', 'name')
                        ->searchable()
                        ->preload()
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set): void {
                            $set('game_name', Category::find($state)?->name);
                        })
                        ->required()
                        ->placeholder('Pilih game')
                        ->helperText('Nama game diambil otomatis dari kategori.'),

                    TextInput::make('game_name')
                        ->label('Nama Game')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->maxLength(255)
                        ->helperText('Terisi otomatis sesuai kategori.'),

                    TextInput::make('title')
                        ->label('Judul / Nama Panggilan Akun')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Akun Mythic Glory 120 Skin')
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Deskripsi Akun')
                        ->required()
                        ->maxLength(5000)
                        ->rows(5)
                        ->placeholder('Jelaskan detail akun: peringkat, jumlah kostum, hero, rasio kemenangan, bonus, dan lainnya.')
                        ->columnSpanFull(),
                ]),

            // ============================================================
            // 2. FOTO AKUN
            // ============================================================
            Section::make('Foto Akun')
                ->description('Foto pertama otomatis menjadi foto utama. Beberapa foto dapat diunggah dan diurutkan dengan cara diseret.')
                ->schema([
                    FileUpload::make('images')
                        ->label('Foto Akun')
                        ->image()
                        ->multiple()
                        ->reorderable()
                        ->panelLayout('grid')
                        ->directory('products')
                        ->disk('public')
                        ->visibility('public')
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
                        ->minFiles(1)
                        ->maxFiles(8)
                        ->maxSize(4096)
                        ->imageEditor()
                        ->required()
                        ->helperText('1–8 foto JPG, PNG, atau WebP. Maksimal 4 MB per foto.')
                        ->columnSpanFull(),
                ]),

            // ============================================================
            // 3. HARGA & KONTAK PENJUAL
            // ============================================================
            Section::make('Harga & Kontak Penjual')
                ->columns(2)
                ->schema([
                    TextInput::make('price')
                        ->label('Harga')
                        ->required()
                        ->prefix('Rp')
                        ->inputMode('numeric')
                        ->type('text')
                        ->extraInputAttributes([
                            'oninput' => <<<'JS'
                                const digits = this.value.replace(/\D/g, '');
                                this.value = digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                            JS,
                            'pattern' => '[0-9.]*',
                            'autocomplete' => 'off',
                        ])
                        ->formatStateUsing(function ($state): string {
                            $digits = preg_replace('/\D/', '', (string) ($state ?? '')) ?? '';

                            return $digits === '' ? '' : number_format((int) $digits, 0, ',', '.');
                        })
                        ->mutateStateForValidationUsing(function ($state): int {
                            $digits = preg_replace('/\D/', '', (string) ($state ?? '')) ?? '';

                            return (int) $digits;
                        })
                        ->dehydrateStateUsing(function ($state): int {
                            $digits = preg_replace('/\D/', '', (string) ($state ?? '')) ?? '';

                            return (int) $digits;
                        })
                        ->rule('integer')
                        ->minValue(0)
                        ->placeholder('150.000'),

                    TextInput::make('seller_name')
                        ->label('Nama Penjual')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Nama penjual'),

                    TextInput::make('seller_whatsapp')
                        ->label('WhatsApp Penjual')
                        ->required()
                        ->tel()
                        ->inputMode('numeric')
                        ->prefix('+62')
                        ->formatStateUsing(function (?string $state): string {
                            $digits = preg_replace('/\D/', '', $state ?? '') ?? '';

                            return str_starts_with($digits, '62')
                                ? substr($digits, 2)
                                : ltrim($digits, '0');
                        })
                        ->dehydrateStateUsing(function (?string $state): string {
                            $digits = preg_replace('/\D/', '', $state ?? '') ?? '';

                            return '62'.ltrim($digits, '0');
                        })
                        ->regex('/^8[0-9]{8,12}$/')
                        ->minLength(9)
                        ->maxLength(13)
                        ->placeholder('81234567890')
                        ->helperText('Masukkan nomor tanpa angka 0 atau +62. Contoh: 81234567890')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
