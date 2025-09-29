<?php

namespace App\Filament\Resources\CheckinPihaks;

use App\Filament\Resources\CheckinPihaks\Pages\ListCheckinPihaks;
use App\Models\CheckinPihak;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Request;

class CheckinPihakResource extends Resource
{
    protected static ?string $model = CheckinPihak::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    public static function table(Table $table): Table
    {
        $perkaraId = Request::get('tableFilterForm')['perkara_id'] ?? null;

        return $table
            ->modifyQueryUsing(function ($query) use ($perkaraId) {

                if ($perkaraId) {

                    return $query->orderByDesc('waktu_checkin')->where('perkara_id', $perkaraId);
                } else {
                    return $query->orderByDesc('waktu_checkin');
                }
            })
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
            ->filters([])
            ->recordActions([
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCheckinPihaks::route('/'),
        ];
    }
}
