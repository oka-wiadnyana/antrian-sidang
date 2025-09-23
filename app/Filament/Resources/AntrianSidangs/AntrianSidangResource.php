<?php

namespace App\Filament\Resources\AntrianSidangs;

use App\Models\Perkara;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\AntrianSidangs\Pages;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use UnitEnum;

class AntrianSidangResource extends Resource
{
    protected static ?string $model = Perkara::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null  $navigationGroup = 'Antrian Sidang';
    protected static ?int $navigationSort = 1;

    public static function table(Table $table): Table
    {
        return $table
            ->query(Perkara::query()->whereHas('jadwal', function ($q) {
                $q->whereDate('tanggal_sidang', now()->format('Y-m-d'));
            }))
            ->columns([
                Tables\Columns\TextColumn::make('nomor_perkara')
                    ->searchable()
                    ->sortable()
                    ->label('Nomor Perkara'),

                Tables\Columns\TextColumn::make('jenis_perkara')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'permohonan' => 'info',
                        'gugatan_cerai' => 'warning',
                        'gugatan_non_cerai' => 'danger',
                        'gugatan_sederhana' => 'success',
                        default => 'secondary',
                    })
                    ->label('Jenis'),

                Tables\Columns\TextColumn::make('hakim_ketua')
                    ->searchable()
                    ->label('Hakim'),

                Tables\Columns\TextColumn::make('waktu_sidang_efektif')
                    ->dateTime('H:i')
                    ->sortable()
                    ->color(fn($record) => $record->waktu_sidang_efektif <= now() ? 'success' : 'warning')
                    ->label('Waktu Sidang'),

                Tables\Columns\TextColumn::make('status_kehadiran_pihak')
                    ->badge()
                    ->color(fn($state) => str_contains($state, '/') && explode('/', $state)[0] == explode('/', $state)[1] ? 'success' : 'warning')
                    ->label('Kehadiran'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('hakim_ketua')
                    ->label('Hakim Ketua')
                    ->options(function () {
                        // Ambil nama hakim ketua dari perkara_hakim_pn
                        return DB::connection('sipp')
                            ->table('perkara_hakim_pn')
                            ->join('perkara_jadwal_sidang', 'perkara_hakim_pn.perkara_id', '=', 'perkara_jadwal_sidang.perkara_id')
                            ->where('perkara_hakim_pn.jabatan_hakim_id', '1') // hanya hakim ketua
                            ->whereDate('perkara_jadwal_sidang.tanggal_sidang', now()->format('Y-m-d'))
                            ->pluck('perkara_hakim_pn.hakim_nama', 'perkara_hakim_pn.hakim_nama')
                            ->unique()
                            ->toArray();
                    }),
                Tables\Filters\SelectFilter::make('jenis_perkara')
                    ->options([
                        'permohonan' => 'Permohonan',
                        'gugatan_cerai' => 'Cerai',
                        'gugatan_non_cerai' => 'Non-Cerai',
                        'gugatan_sederhana' => 'Sederhana',
                    ]),
            ])
            // ->defaultSort('waktu_sidang_efektif', 'asc')
            ->modifyQueryUsing(function ($query) {
                // Tidak bisa sort di sini — karena accessor butuh relasi dari koneksi lain
            })
            ->recordAction(null) // nonaktifkan action default
            ->paginated(false)
            ->recordActions([
                Action::make('detail')->label('Detail Pihak')->icon('heroicon-m-eye')->url(fn($record) => route('filament.admin.resources.checkin-pihaks.index', ['tableFilterForm' => ['perkara_id' => $record->perkara_id]]))->openUrlInNewTab(),

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\AntrianSidangs\Pages\ListAntrianSidangCustom::route('/'),
        ];
    }
}
