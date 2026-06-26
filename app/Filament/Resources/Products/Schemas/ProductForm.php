<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

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
                        ->required()
                        ->placeholder('Pilih game')
                        ->helperText('Belum ada gamenya? Tambahkan dulu di menu Categories.'),

                    TextInput::make('game_name')
                        ->label('Nama Game')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Mobile Legends'),

                    TextInput::make('title')
                        ->label('Judul / Nickname Akun')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Contoh: Akun Mythic Glory 120 Skin')
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Deskripsi Akun')
                        ->required()
                        ->rows(5)
                        ->placeholder('Jelaskan detail akun: rank, jumlah skin, hero, win rate, bonus, dll.')
                        ->columnSpanFull(),
                ]),

            // ============================================================
            // 2. FOTO AKUN
            // ============================================================
            Section::make('Foto Akun')
                ->description('Foto pertama otomatis menjadi foto utama. Bisa upload beberapa foto dan diurutkan dengan cara di-drag.')
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
                        ->imageEditor()
                        ->required()
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
                        ->numeric()
                        ->required()
                        ->prefix('Rp')
                        ->minValue(0)
                        ->placeholder('150000'),

                    TextInput::make('seller_name')
                        ->label('Nama Penjual')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Nama penjual'),

                    TextInput::make('seller_whatsapp')
                        ->label('WhatsApp Penjual')
                        ->required()
                        ->tel()
                        ->maxLength(20)
                        ->placeholder('6281234567890')
                        ->helperText('Format internasional tanpa tanda +. Contoh: 6281234567890')
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
