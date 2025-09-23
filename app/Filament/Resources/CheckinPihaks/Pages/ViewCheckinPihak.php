<?php

namespace App\Filament\Resources\CheckinPihaks\Pages;

use App\Filament\Resources\CheckinPihaks\CheckinPihakResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCheckinPihak extends ViewRecord
{
    protected static string $resource = CheckinPihakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
