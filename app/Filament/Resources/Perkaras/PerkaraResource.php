<?php

namespace App\Filament\Resources\Perkaras;

use App\Filament\Resources\Perkaras\Pages\CreatePerkara;
use App\Filament\Resources\Perkaras\Pages\EditPerkara;
use App\Filament\Resources\Perkaras\Pages\ListPerkaras;
use App\Filament\Resources\Perkaras\Schemas\PerkaraForm;
use App\Filament\Resources\Perkaras\Tables\PerkarasTable;
use App\Models\Perkara;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PerkaraResource extends Resource
{
    protected static ?string $model = Perkara::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Perkara';

    public static function form(Schema $schema): Schema
    {
        return PerkaraForm::configure($schema);
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['jadwal' => function ($q) {
                // Pastikan hanya memuat jadwal hari ini
                $q->whereDate('tanggal_sidang', now());
            }])
            ->whereHas('jadwal', function ($q) {
                // Filter perkara yang punya jadwal hari ini
                $q->whereDate('tanggal_sidang', now());
            });
    }

    public static function table(Table $table): Table
    {
        return $table

            ->columns([
                TextColumn::make('nomor_perkara'),
                TextColumn::make('jenis_perkara_nama')->badge(),

                TextColumn::make('waktu_sidang_efektif')->dateTime(),
                TextColumn::make('status')
                    ->getStateUsing(fn(Perkara $p) => $p->isLengkap() ? ($p->waktu_sidang_efektif > now() ? 'tertunda' : 'siap') : 'belum')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'siap' => 'success',
                        'tertunda' => 'warning',
                        'belum' => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('jenis_perkara_nama')->options([
                    'permohonan' => 'Permohonan',
                    'gugatan_cerai' => 'Cerai',
                    'gugatan_non_cerai' => 'Non-Cerai',
                ]),
            ]);
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
            'index' => ListPerkaras::route('/'),
            'create' => CreatePerkara::route('/create'),
            'edit' => EditPerkara::route('/{record}/edit'),
        ];
    }
}
