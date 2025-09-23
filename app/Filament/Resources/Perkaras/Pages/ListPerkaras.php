<?php

namespace App\Filament\Resources\Perkaras\Pages;

use App\Filament\Resources\Perkaras\PerkaraResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPerkaras extends ListRecords
{
    protected static string $resource = PerkaraResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
