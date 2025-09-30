<?php

namespace App\Filament\Resources\SidangHariInis\Pages;

use App\Filament\Resources\SidangHariInis\SidangHariIniResource;
use App\Models\CheckinPihak;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class ListSidangHariInis extends ListRecords
{
    protected static string $resource = SidangHariIniResource::class;

    public function getTabs(): array
    {
        $now = now()->format('Y-m-d');
        $perkaraHariIni = static::getResource()::getEloquentQuery()
            ->whereHas('jadwal', function ($q) use ($now) {
                $q->whereDate('tanggal_sidang', $now);
            })
            ->orWherehas('jadwalMediasi', function ($q) use ($now) {
                $q->whereDate('tanggal_mediasi', $now);
            })
            ->orWherehas('jadwalPk', function ($q) use ($now) {
                $q->whereDate('tanggal_pemeriksaan', $now);
            })
            ->with(['hakim' => fn($q) => $q->where('jabatan_hakim_id', 1)])
            ->get();

        $perkaraIds = $perkaraHariIni->pluck('perkara_id');
        $allCheckins = CheckinPihak::whereIn('perkara_id', $perkaraIds)
            ->get()
            ->groupBy('perkara_id');



        $hakimKeys = collect();
        foreach ($perkaraHariIni as $perkara) {
            $perkara->setRelation(
                'checkins',
                $allCheckins->get($perkara->perkara_id, collect())
            );
            if ($perkara->checkins->contains('jenis_sidang', 'mediasi')) {
                $key = 'mediasi';
            } elseif ($perkara->checkins->contains('jenis_sidang', 'pk')) {
                $key = 'pk';
            } else {

                $key = match ($perkara->alur_perkara_id) {
                    2 => 'permohonan',
                    8 => 'gugatan_sederhana',
                    default => optional($perkara->hakim->first())->hakim_nama ?? 'Hakim Tidak Ditemukan',
                };
            }
            $hakimKeys->push($key);
        }

        $uniqueKeys = $hakimKeys->unique()->values();
        $tabs = collect();

        foreach ($uniqueKeys as $hakim) {
            if ($hakim === 'permohonan') {
                $tab = Tab::make('permohonan', 'Permohonan');
            } elseif ($hakim === 'gugatan_sederhana') {
                $tab = Tab::make('gugatan_sederhana', 'GS');
            } elseif ($hakim === 'mediasi') {
                $tab = Tab::make('mediasi', 'Mediasi');
            } elseif ($hakim === 'pk') {
                $tab = Tab::make('pk', 'PK');
            } else {
                $tab = Tab::make($hakim, $hakim);
            }

            $tabs->put($hakim, $tab);
        }

        // Gunakan prepend untuk menambahkan tab 'semua' di awal dengan kunci 'semua'
        $tabs->prepend(Tab::make('semua', 'Semua'), 'semua');

        return $tabs->toArray();
    }

    public function getTableRecords(): Collection
    {
        // ... (Langkah 1 - 4: Logika yang sudah kita perbaiki)

        $selectedTab = $this->activeTab;
        $now = now()->format('Y-m-d');

        // Ambil dan gabungkan data...
        $perkaraHariIni = static::getResource()::getEloquentQuery()
            ->whereHas('jadwal', fn($q) => $q->whereDate('tanggal_sidang', $now))
            ->orWherehas('jadwalMediasi', function ($q) use ($now) {
                $q->whereDate('tanggal_mediasi', $now);
            })
            ->orWherehas('jadwalPk', function ($q) use ($now) {
                $q->whereDate('tanggal_pemeriksaan', $now);
            })
            ->with(['hakim' => fn($q) => $q->where('jabatan_hakim_id', 1), 'mediasi', 'jadwalMediasi' => function ($q) use ($now) {
                $q->whereDate('tanggal_mediasi', $now);
            }, 'jadwalPk' => function ($q) use ($now) {
                $q->whereDate('tanggal_pemeriksaan', $now);
            }, 'jadwal' => function ($q) use ($now) {
                $q->whereDate('tanggal_sidang', $now);
            }])
            ->get();
        $perkaraIds = $perkaraHariIni->pluck('perkara_id');
        $allCheckins = CheckinPihak::whereIn('perkara_id', $perkaraIds)
            ->whereDate('waktu_checkin', $now)
            ->orderBy('waktu_checkin', 'asc')
            ->get()
            ->groupBy('perkara_id');

        $perkaraHariIni->each(fn($perkara) => $perkara->setRelation('checkins', $allCheckins->get($perkara->perkara_id, collect())));

        // Langkah 4: Terapkan filter berdasarkan tab yang aktif
        $filteredPerkara = $perkaraHariIni->filter(function ($perkara) use ($selectedTab) {
            // Logika filter tab Anda...
            if ($selectedTab === null || $selectedTab === 'semua' || $selectedTab === '') {
                return true;
            } elseif ($selectedTab === 'mediasi') {
                return (int) $perkara->checkins->contains('jenis_sidang', 'mediasi');
            } elseif ($selectedTab === 'mediasi') {
                return (int) $perkara->checkins->contains('jenis_sidang', 'pk');
            } elseif ($selectedTab === 'permohonan') {
                return (int) $perkara->alur_perkara_id === 2;
            } elseif ($selectedTab === 'gugatan_sederhana') {
                return (int) $perkara->alur_perkara_id === 8;
            } else { // Tab Hakim
                $hakim = optional($perkara->hakim->first());
                return $hakim && $hakim->hakim_nama === $selectedTab;
            }
        });
        // dd($filteredPerkara);
        // Langkah 5: Urutkan data berdasarkan waktu check-in
        return $filteredPerkara->sortBy('hakim_ketua');
    }
    public  function table(Table $table): Table
    {
        return $table
            // ->query(static::getResource()::getEloquentQuery())
            ->columns([
                TextColumn::make('nomor_perkara')
                    ->searchable()
                    ->sortable()
                    ->label('Nomor Perkara'),

                TextColumn::make('jenis_perkara')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'permohonan' => 'info',
                        'gugatan_cerai' => 'warning',
                        'gugatan_non_cerai' => 'danger',
                        'gugatan_sederhana' => 'success',
                        default => 'secondary',
                    })
                    ->label('Jenis'),

                TextColumn::make('hakim_ketua')
                    ->formatStateUsing(function ($record) {

                        $mediatorText = $record->mediasi?->mediator_text;

                        if ($mediatorText) {
                            return $record->hakim_ketua . "-mediator(" . $mediatorText . ")";
                        }
                        return $record->hakim_ketua;
                    })
                    ->searchable()
                    ->label('Hakim'),
                TextColumn::make('panitera_active')
                    // ->state(fn($state, $record) => $record->panitera_active ?? "")
                    ->searchable()
                    ->label('PP'),
                TextColumn::make('agenda')
                    ->getStateUsing(function ($record) {
                        if ($record->jadwalMediasi->isNotEmpty()) {
                            return "Mediasi";

                            // 2. Cek Relasi hasOne: PK
                        } elseif ($record->jadwalPk->isNotEmpty()) {
                            return "PK";

                            // 3. Akses Agenda dari Jadwal (Relasi HasMany)
                        }
                        // Gunakan Nullsafe (?->) setelah first()
                        // Jika jadwal kosong, $agenda akan bernilai null secara aman.
                        $agenda = $record->jadwal->first()?->agenda;

                        // Berikan nilai default jika $agenda masih null
                        return $agenda ?? 'Sidang Lanjutan (Jadwal Belum Ditetapkan)';
                    })
                    ->searchable()
                    ->label('Agenda'),

            ])
            ->filters([])
            ->recordActions([])
            ->paginated(false);
    }
    // protected function getHeaderActions(): array
    // {
    //     return [
    //         CreateAction::make(),
    //     ];
    // }
}
