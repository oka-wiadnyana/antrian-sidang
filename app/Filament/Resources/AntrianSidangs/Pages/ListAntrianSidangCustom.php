<?php

namespace App\Filament\Resources\AntrianSidangs\Pages;

use App\Filament\Resources\AntrianSidangs\AntrianSidangResource;
use App\Models\CheckinPihak;
use App\Models\Perkara;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ListAntrianSidangCustom extends ListRecords
{
    protected static string $resource = AntrianSidangResource::class;
    public function getTabs(): array
    {

        $perkaraHariIni = static::getResource()::getEloquentQuery()
            ->whereHas('jadwal', function ($q) {
                $q->whereDate('tanggal_sidang', now()->format('Y-m-d'));
            })
            ->with(['hakim' => function ($q) {
                $q->where('jabatan_hakim_id', 1);
            }])
            ->get();

        $hakimKeys = collect();
        foreach ($perkaraHariIni as $perkara) {
            $key = match ($perkara->alur_perkara_id) {
                2 => 'permohonan',
                8 => 'gugatan_sederhana',
                default => optional($perkara->hakim->first())->hakim_nama ?? 'Hakim Tidak Ditemukan',
            };
            $hakimKeys->push($key);
        }

        $uniqueKeys = $hakimKeys->unique()->values();
        $tabs = [];

        foreach ($uniqueKeys as $hakim) {
            if ($hakim === 'permohonan') {
                $tab = Tab::make('Permohonan')->modifyQueryUsing(fn(Builder $query) => $query->where('alur_perkara_id', 2)
                    ->whereHas('checkins', fn(Builder $q) => $q->whereDate('waktu_checkin', now()->format('Y-m-d'))));
            } elseif ($hakim === 'gugatan_sederhana') {
                $tab = Tab::make('GS')->modifyQueryUsing(fn(Builder $query) => $query->where('alur_perkara_id', 8)
                    ->whereHas('checkins', fn(Builder $q) => $q->whereDate('waktu_checkin', now()->format('Y-m-d'))));
            } else {
                $tab = Tab::make($hakim)->modifyQueryUsing(fn(Builder $query) => $query->whereHas('hakim', fn(Builder $q) => $q->where('hakim_nama', $hakim))
                    ->whereHas('checkins', fn(Builder $q) => $q->whereDate('waktu_checkin', now()->format('Y-m-d'))));
            }

            $tabs[] = $tab;
        }

        // Tambahkan tab 'Semua' sebagai tab pertama
        array_unshift($tabs, Tab::make('Semua'));

        // dd($tabs);

        return $tabs;
    }
    // public function getTableRecords(): Collection
    // {  // Ambil semua perkara hari ini
    //     $perkaraHariIni = static::getResource()::getEloquentQuery()
    //         ->whereHas('jadwal', function ($q) {
    //             $q->whereDate('tanggal_sidang', now()->format('Y-m-d'));
    //         })->get();

    //     // Load checkins untuk semua perkara
    //     $perkaraIds = $perkaraHariIni->pluck('perkara_id');
    //     $allCheckins = \App\Models\CheckinPihak::whereIn('perkara_id', $perkaraIds)
    //         ->get()
    //         ->groupBy('perkara_id');

    //     // Attach checkins ke setiap perkara
    //     $perkaraHariIni->each(function ($perkara) use ($allCheckins) {
    //         $perkara->setRelation('checkins', $allCheckins->get($perkara->perkara_id, collect()));
    //     });

    //     // Filter dan sort by waktu_sidang_efektif
    //     $perkaraSiap = $perkaraHariIni->filter(function ($perkara) {
    //         return $perkara->adaCheckin() && $perkara->waktu_sidang_efektif <= now();
    //     })->sortBy('waktu_sidang_efektif');

    //     return $perkaraSiap;
    // }

    // public  function table(Table $table): Table
    // {
    //     return $table
    //         // ->query(Perkara::query()->whereHas('jadwal', function ($q) {
    //         //     $q->whereDate('tanggal_sidang', now()->format('Y-m-d'));
    //         // }))
    //         ->columns([
    //             TextColumn::make('nomor_perkara')
    //                 ->searchable()
    //                 ->sortable()
    //                 ->label('Nomor Perkara'),

    //             TextColumn::make('jenis_perkara')
    //                 ->badge()
    //                 ->color(fn($state) => match ($state) {
    //                     'permohonan' => 'info',
    //                     'gugatan_cerai' => 'warning',
    //                     'gugatan_non_cerai' => 'danger',
    //                     'gugatan_sederhana' => 'success',
    //                     default => 'secondary',
    //                 })
    //                 ->label('Jenis'),

    //             TextColumn::make('hakim_ketua')
    //                 ->searchable()
    //                 ->label('Hakim'),

    //             TextColumn::make('waktu_sidang_efektif')
    //                 ->dateTime('H:i')
    //                 ->sortable()
    //                 ->color(fn($record) => $record->waktu_sidang_efektif <= now() ? 'success' : 'warning')
    //                 ->label('Waktu Sidang'),

    //             TextColumn::make('status_kehadiran_pihak')
    //                 ->badge()
    //                 ->color(fn($state) => str_contains($state, '/') && explode('/', $state)[0] == explode('/', $state)[1] ? 'success' : 'warning')
    //                 ->label('Kehadiran'),
    //         ])
    //         ->filters([])
    //         // ->defaultSort('waktu_sidang_efektif', 'asc')
    //         ->modifyQueryUsing(function ($query) {
    //             // Tidak bisa sort di sini — karena accessor butuh relasi dari koneksi lain
    //         })
    //         ->recordAction(null) // nonaktifkan action default
    //         ->paginated(false)
    //         ->recordActions([
    //             Action::make('detail')->label('Detail Pihak')->icon('heroicon-m-eye')->url(fn($record) => route('filament.admin.resources.checkin-pihaks.index', ['tableFilterForm' => ['perkara_id' => $record->perkara_id]]))->openUrlInNewTab(),
    //             Action::make('panggil')
    //                 ->label('Panggil Sidang')
    //                 ->icon('heroicon-m-bell')
    //                 ->color('warning')
    //                 ->requiresConfirmation()
    //                 ->modalHeading('Pilih Ruang Sidang')
    //                 ->modalSubmitActionLabel('Panggil')
    //                 ->form([
    //                     Select::make('ruang')
    //                         ->label('Ruang Sidang')
    //                         ->options([
    //                             'kartika' => 'Ruang Sidang Kartika',
    //                             'cakra' => 'Ruang Sidang Cakra',
    //                             'tirta' => 'Ruang Sidang Tirta',
    //                             'anak' => 'Ruang Sidang Anak',
    //                         ])
    //                         ->required(),
    //                 ])
    //                 ->action(function (Perkara $record, $data) {
    //                     // Ambil data perkara dari koneksi 'sipp'
    //                     $data_perkara = Perkara::on('sipp')->find($record->perkara_id);

    //                     // Generate teks panggilan — COPY LOGIKA DARI CI4-MU!
    //                     $teks_panggilan = self::generateTeksPanggilan($data_perkara, $data['ruang']);

    //                     // Simpan ke log atau kirim notifikasi
    //                     try {
    //                         // Gunakan Laravel HTTP Client untuk request GET
    //                         $response = Http::get(env('WEBSOCKET_PANGGILAN_URL') . urlencode($teks_panggilan));

    //                         // Jika respons berhasil, simpan flash message dan kembalikan JSON
    //                         if ($response->successful()) {
    //                             Notification::make()
    //                                 ->title('Perkara Dipanggil!')
    //                                 ->body('Sukses')
    //                                 ->success()
    //                                 ->send();
    //                             return response()->json([
    //                                 'status' => 'success',
    //                                 'data' => $response->json() // Ambil data JSON dari respons
    //                             ]);
    //                         }

    //                         // Jika respons gagal, simpan flash message dan kembalikan JSON
    //                         Notification::make()
    //                             ->title('Perkara Dipanggil!')
    //                             ->body('Gagal')
    //                             ->danger()
    //                             ->send();
    //                         return response()->json([
    //                             'status' => 'fail',
    //                             'message' => 'Gagal terhubung ke layanan eksternal'
    //                         ], $response->status());
    //                     } catch (\Exception $e) {
    //                         // Tangani kegagalan koneksi
    //                         Notification::make()
    //                             ->title('Perkara Dipanggil!')
    //                             ->body('Gagal')
    //                             ->danger()
    //                             ->send();
    //                         return response()->json([
    //                             'status' => 'error',
    //                             'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    //                         ]);
    //                     }

    //                     // Notifikasi sukses


    //                     // Optional: Trigger suara (jika ada)
    //                     $this->dispatch('play-panggilan-sidang');
    //                 }),

    //         ]);
    // }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
