<?php

namespace App\Filament\Resources\SidangHariInis\Pages;

use App\Filament\Resources\SidangHariInis\SidangHariIniResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSidangHariIni extends EditRecord
{
    protected static string $resource = SidangHariIniResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
