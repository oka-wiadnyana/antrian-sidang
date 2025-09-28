<?php

namespace App\Filament\Resources\HearingTimes;

use App\Filament\Resources\HearingTimes\Pages\CreateHearingTime;
use App\Filament\Resources\HearingTimes\Pages\EditHearingTime;
use App\Filament\Resources\HearingTimes\Pages\ListHearingTimes;
use App\Filament\Resources\HearingTimes\Schemas\HearingTimeForm;
use App\Filament\Resources\HearingTimes\Tables\HearingTimesTable;
use App\Models\HearingTime;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class HearingTimeResource extends Resource
{
    protected static ?string $model = HearingTime::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return HearingTimeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HearingTimesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHearingTimes::route('/'),
            'create' => CreateHearingTime::route('/create'),
            'edit' => EditHearingTime::route('/{record}/edit'),
        ];
    }
}
