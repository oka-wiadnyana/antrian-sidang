<?php

namespace App\Filament\Resources\CheckinPihaks\Pages;

use App\Filament\Resources\CheckinPihaks\CheckinPihakResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCheckinPihaks extends ListRecords
{
    protected static string $resource = CheckinPihakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
