<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    // Setelah simpan, kembali ke daftar Products.
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Hanya tombol "Create" & "Cancel" (hapus "Create & create another").
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCancelFormAction(),
        ];
    }
}
