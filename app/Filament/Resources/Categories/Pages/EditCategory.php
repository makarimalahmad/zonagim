<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    // Setelah simpan, kembali ke daftar Categories.
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label('Hapus kategori')
                ->extraAttributes(['class' => 'app-delete-action'])
                ->modalSubmitAction(fn (Action $action): Action => $action
                    ->extraAttributes(['class' => 'app-delete-action']))
                ->disabled(fn (): bool => $this->record->products()->exists())
                ->tooltip(fn (): ?string => $this->record->products()->exists()
                    ? 'Kategori tidak dapat dihapus selama masih memiliki produk.'
                    : null),
        ];
    }
}
