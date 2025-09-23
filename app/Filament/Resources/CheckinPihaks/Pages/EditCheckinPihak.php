<?php

namespace App\Filament\Resources\CheckinPihaks\Pages;

use App\Filament\Resources\CheckinPihaks\CheckinPihakResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCheckinPihak extends EditRecord
{
    protected static string $resource = CheckinPihakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
