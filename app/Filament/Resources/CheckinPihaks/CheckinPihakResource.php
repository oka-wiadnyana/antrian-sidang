<?php

namespace App\Filament\Resources;

use App\Models\CheckinPihak;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use BackedEnum;


class CheckinPihakResource extends Resource
{
    protected static ?string $model = CheckinPihak::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('perkara.nomor_perkara')->searchable(),
                Tables\Columns\TextColumn::make('tipe_pihak')->badge(),
                Tables\Columns\TextColumn::make('nama_yang_hadir'),
                Tables\Columns\TextColumn::make('status_kehadiran')
                    ->badge()
                    ->color(fn($state) => $state === 'kuasa' ? 'warning' : 'success'),
                Tables\Columns\TextColumn::make('waktu_checkin')->dateTime(),
                Tables\Columns\TextColumn::make('jarak_meter')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_kehadiran')->options([
                    'pihak_langsung' => 'Pihak Langsung',
                    'kuasa' => 'Kuasa',
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\CheckinPihakResource\Pages\ListCheckinPihaks::route('/'),
        ];
    }
}
