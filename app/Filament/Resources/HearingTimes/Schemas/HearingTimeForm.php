<?php

namespace App\Filament\Resources\HearingTimes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class HearingTimeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('jenis_perkara')
                    ->label('Jenis Perkara')
                    ->options([
                        'permohonan' => 'permohonan',
                        'gugatan_sederhana' => 'gugatan_sederhana',
                        'gugatan_cerai' => 'gugatan_cerai',
                        'gugatan_non_cerai' => 'gugatan_non_cerai',
                        'pidana' => 'pidana',
                    ])
                    ->required(),
                TimePicker::make('time')
                    ->required(),
            ]);
    }
}
