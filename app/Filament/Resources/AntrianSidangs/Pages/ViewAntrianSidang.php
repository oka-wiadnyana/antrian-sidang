<?php

namespace App\Filament\Resources\AntrianSidangs\Pages;

use App\Filament\Resources\AntrianSidangs\AntrianSidangResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAntrianSidang extends ViewRecord
{
    protected static string $resource = AntrianSidangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
