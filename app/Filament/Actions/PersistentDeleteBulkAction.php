<?php

namespace App\Filament\Actions;

use Filament\Actions\DeleteBulkAction;

class PersistentDeleteBulkAction extends DeleteBulkAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Hapus terpilih');
        $this->extraAttributes([
            'class' => 'app-delete-action app-persistent-delete-bulk-action',
            'x-bind:disabled' => 'getSelectedRecordsCount() === 0',
            'x-bind:aria-disabled' => 'getSelectedRecordsCount() === 0',
            'x-bind:title' => "getSelectedRecordsCount() === 0 ? 'Pilih minimal satu data' : 'Hapus data terpilih'",
        ]);
    }

    public function getExtraAttributes(): array
    {
        $attributes = parent::getExtraAttributes();
        unset($attributes['x-cloak'], $attributes['x-show']);

        return $attributes;
    }
}
