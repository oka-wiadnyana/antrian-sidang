<?php

namespace App\Filament\Resources\HearingTimes\Pages;

use App\Filament\Resources\HearingTimes\HearingTimeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHearingTime extends EditRecord
{
    protected static string $resource = HearingTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
