<?php

namespace App\Filament\Actions;

use Filament\Actions\BulkActionGroup;

class PersistentBulkActionGroup extends BulkActionGroup
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Tindakan');
        $this->extraAttributes([
            'class' => 'app-persistent-bulk-actions',
            'title' => 'Buka tindakan massal',
        ]);
    }

    public function getExtraDropdownAttributes(): array
    {
        return parent::getExtraAttributes();
    }
}
