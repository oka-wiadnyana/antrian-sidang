<?php

namespace App\Filament\Resources\SidangHariInis\Pages;

use App\Filament\Resources\SidangHariInis\SidangHariIniResource;
use App\Models\CheckinPihak;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
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
            ->with([
                'hakim' => fn($q) => $q->where('jabatan_hakim_id', 1),
                'mediasi',
                'jadwalMediasi' => function ($q) use ($now) {
                    $q->whereDate('tanggal_mediasi', $now);
                },
                'jadwalPk' => function ($q) use ($now) {
                    $q->whereDate('tanggal_pemeriksaan', $now);
                },
                'jadwal' => function ($q) use ($now) {
                    $q->whereDate('tanggal_sidang', $now);
                },
                'pihak1',
                'pihak2',
                'pihak_pengacara'
            ])
            ->get();
        // dd($perkaraHariIni->pluck('pihak1')->first()->pihak()->telepon);
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
            ->toolbarActions([])
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
                    }),
                // TextColumn::make('pihak1_telepon') // Beri nama kolom yang unik
                //     ->label('Kontak Pihak 1')
                //     ->html() // Penting: agar kode HTML Anda dirender
                //     ->getStateUsing(function ($record) {
                //         $html = '';

                //         // Memeriksa apakah relasi pihak1 ada dan tidak kosong
                //         if ($record->pihak1->isNotEmpty()) {
                //             foreach ($record->pihak1 as $pihak1Item) {
                //                 // Pastikan atribut 'telepon' ada dan tidak kosong
                //                 if (!empty($pihak1Item->pihak->telepon)) {
                //                     // dd($pihak1Item->pihak->telepon);
                //                     // Logika preg_replace('/^0/', '62', $t['telepon'])
                //                     $cleanNumber = preg_replace('/^0/', '62', $pihak1Item->pihak->telepon);
                //                     $whatsappLink = "https://wa.me/{$cleanNumber}";

                //                     // Membuat HTML yang diinginkan
                //                     $nama = $pihak1Item->nama ?? 'Nomor Telepon';

                //                     // Menggunakan Filament/Tailwind CSS class untuk styling
                //                     // Anda bisa menyesuaikan styling ini
                //                     $html .= "
                //                 <a href='{$whatsappLink}' target='_blank' class='text-decoration-none'>
                //                     <div class='mb-1 inline-flex items-center space-x-2 px-3 py-1 bg-white border border-green-500 text-green-700 rounded-full shadow-sm hover:bg-gray-50 transition text-xs font-semibold'>


                //                         <svg class='w-4 h-4' xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor'>
                //                             <path stroke-linecap='round' stroke-linejoin='round' d='M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9 0h2.25V7.5M3.75 6h16.5' />
                //                         </svg>

                //                         <span>{$nama}</span>
                //                     </div>
                //                 </a>
                //             ";
                //                 }
                //             }
                //         }

                //         if ($record->pihak2->isNotEmpty()) {
                //             foreach ($record->pihak2 as $pihak2Item) {
                //                 // Pastikan atribut 'telepon' ada dan tidak kosong
                //                 if (!empty($pihak2Item->pihak->telepon)) {
                //                     // dd($pihak2Item->pihak->telepon);
                //                     // Logika preg_replace('/^0/', '62', $t['telepon'])
                //                     $cleanNumber = preg_replace('/^0/', '62', $pihak2Item->pihak->telepon);
                //                     $whatsappLink = "https://wa.me/{$cleanNumber}";

                //                     // Membuat HTML yang diinginkan
                //                     $nama = $pihak2Item->nama ?? 'Nomor Telepon';

                //                     // Menggunakan Filament/Tailwind CSS class untuk styling
                //                     // Anda bisa menyesuaikan styling ini
                //                     $html .= "
                //                     <a href='{$whatsappLink}' target='_blank' class='text-decoration-none'>
                //                         <div class='mb-1 inline-flex items-center space-x-2 px-3 py-1 bg-white border border-green-500 text-green-700 rounded-full shadow-sm hover:bg-gray-50 transition text-xs font-semibold'>


                //                             <svg class='w-4 h-4' xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor'>
                //                                 <path stroke-linecap='round' stroke-linejoin='round' d='M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9 0h2.25V7.5M3.75 6h16.5' />
                //                             </svg>

                //                             <span>{$nama}</span>
                //                         </div>
                //                     </a>
                //                 ";
                //                 }
                //             }
                //         }
                //         return $html;
                //     }),


                ViewColumn::make('kontak')
                    ->label('Kontak')
                    ->view('tables.columns.kontak'),


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
