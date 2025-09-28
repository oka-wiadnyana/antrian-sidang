<?php

namespace App\Filament\Resources\HearingTimes\Pages;

use App\Filament\Resources\HearingTimes\HearingTimeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHearingTimes extends ListRecords
{
    protected static string $resource = HearingTimeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
