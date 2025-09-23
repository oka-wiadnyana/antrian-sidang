<?php

namespace App\Filament\Resources\Perkaras\Pages;

use App\Filament\Resources\Perkaras\PerkaraResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPerkara extends EditRecord
{
    protected static string $resource = PerkaraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
