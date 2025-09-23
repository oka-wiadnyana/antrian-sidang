<?php

namespace App\Filament\Resources\AntrianSidangs\Pages;

use App\Filament\Resources\AntrianSidangs\AntrianSidangResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditAntrianSidang extends EditRecord
{
    protected static string $resource = AntrianSidangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
