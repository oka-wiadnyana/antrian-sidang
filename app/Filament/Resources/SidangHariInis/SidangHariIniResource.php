<?php

namespace App\Filament\Resources\SidangHariInis;

use App\Filament\Resources\SidangHariInis\Pages\CreateSidangHariIni;
use App\Filament\Resources\SidangHariInis\Pages\EditSidangHariIni;
use App\Filament\Resources\SidangHariInis\Pages\ListSidangHariInis;
use App\Filament\Resources\SidangHariInis\Schemas\SidangHariIniForm;
use App\Filament\Resources\SidangHariInis\Tables\SidangHariInisTable;
use App\Models\Perkara;
use App\Models\SidangHariIni;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SidangHariIniResource extends Resource
{
    protected static ?string $model = Perkara::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Sidang Hari Ini';

    public static function form(Schema $schema): Schema
    {
        return SidangHariIniForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SidangHariInisTable::configure($table);
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
            'index' => ListSidangHariInis::route('/'),
            'create' => CreateSidangHariIni::route('/create'),
            'edit' => EditSidangHariIni::route('/{record}/edit'),
        ];
    }
}
