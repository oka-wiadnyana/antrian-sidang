<?php

namespace App\Filament\Resources\AntrianSidangs\Pages;

use App\Filament\Resources\AntrianSidangs\AntrianSidangResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAntrianSidangs extends ListRecords
{
    protected static string $resource = AntrianSidangResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
