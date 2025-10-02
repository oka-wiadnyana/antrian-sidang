<?php

namespace App\Filament\Resources\AntrianSidangs\Pages;

use App\Events\RefreshQueuePage;
use App\Filament\Resources\AntrianSidangs\AntrianSidangResource;
use App\Models\CheckinPihak;
use App\Models\Perkara;
use App\Models\PerkaraPihak1;
use App\Models\PerkaraPihak2;
use App\Models\PerkaraPihak3;
use App\Models\PerkaraPihak4;
use Dom\Text;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Mockery\Matcher\Not;

class ListAntrianSidangs extends ListRecords
{
    protected static string $resource = AntrianSidangResource::class;

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

                'pihak3',
                'pihak4',
            ])
            ->get();

        $perkaraIds = $perkaraHariIni->pluck('perkara_id');
        $allCheckins = CheckinPihak::whereIn('perkara_id', $perkaraIds)
            ->whereDate('waktu_checkin', $now)
            ->orderBy('waktu_checkin', 'asc')
            ->get()
            ->groupBy('perkara_id');
        // dd($allCheckins);

        $perkaraHariIni->each(fn($perkara) => $perkara->setRelation('checkins', $allCheckins->get($perkara->perkara_id, collect())));

        // dd($perkaraHariIni->pluck('checkins', 'nomor_perkara'));

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
        // dd($filteredPerkara->pluck('jadwalMediasi', 'nomor_perkara'));

        // --- DI SINI ANDA MENAMBAHKAN FILTER TAMBAHAN ---
        // Filter untuk hanya menampilkan perkara yang memiliki check-in
        $filteredPerkara = $filteredPerkara->filter(fn($perkara) => $perkara->checkins->isNotEmpty());


        // Langkah 5: Urutkan data berdasarkan waktu check-in
        return $filteredPerkara->sortBy(function ($perkara) {
            // Ambil waktu check-in dari model CheckinPihak yang pertama
            // Jika ada lebih dari satu, Anda bisa memilih logika yang berbeda (misalnya, yang paling awal)
            $checkin = $perkara->checkins->first();

            // Pastikan ada data checkin sebelum mencoba mengakses propertinya
            return $checkin ? $checkin->waktu_checkin : null;
        })->sortBy('hakim_ketua');

        // Langkah 5: Urutkan data yang sudah difilter
        // return $filteredPerkara;
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




                TextColumn::make('kehadiran_pihak_custom')
                    ->label('Kehadiran Pihak')
                    ->getStateUsing(function ($record) {
                        $dataCheckin = $record->checkins;




                        $stringCheckin = $dataCheckin->map(function ($checkin) use ($record) {
                            // Ambil nomor urut pihak (P1, P2, P3, T1, T2, dst.)
                            $urutan = $checkin->urutan_pihak;


                            // Tentukan singkatan dasar (P, T, I, dst.)
                            if (str_contains($checkin->tipe_pihak, 'pihak1')) {
                                $singkatanDasar = 'P';
                                // 💡 OPTIMASI: Gunakan count() pada Collection yang sudah dimuat
                                $countPihak = $record->pihak1->count();
                                if ($countPihak > 1) {
                                    $singkatanDasar .=  $urutan;
                                }
                            } elseif (str_contains($checkin->tipe_pihak, 'pihak2')) {
                                // if ($record->nomor_perkara == '343/Pdt.G/2025/PN Tab') {
                                //     dd('tes', $record->pihak2->count());
                                // }
                                $singkatanDasar = 'T';
                                $countPihak = $record->pihak2->count();
                                if ($countPihak > 1) {

                                    $singkatanDasar .= $urutan;
                                }
                            } elseif (str_contains($checkin->tipe_pihak, 'pihak3')) {
                                $singkatanDasar = 'I';
                                $countPihak = $record->pihak3->count();
                                if ($countPihak > 1) {
                                    $singkatanDasar .= $urutan;
                                }
                            } else { // Asumsi pihak4 atau lainnya
                                $singkatanDasar = 'TT';
                                // Pastikan Anda memiliki relasi pihak4 di model Perkara
                                $countPihak = $record->pihak4->count();
                                if ($countPihak > 1) {
                                    $singkatanDasar .= $urutan;
                                }
                            }

                            // Tentukan prefix berdasarkan status kehadiran
                            $prefix = ($checkin->status_kehadiran === 'kuasa') ? 'K' : '';

                            // $hasilSingkatan = $singkatanDasar;

                            // // Aturan: Jika jumlah pihak lebih dari 1, tambahkan nomor urut
                            // // (Aturan yang Anda buat sedikit membingungkan, saya gunakan logika yang lebih umum)
                            // // Logika Anda: Jika > 1, tambahkan $urutan. Jika = 1, jangan tambah $urutan.
                            // if ($countPihak > 1) {
                            //     $hasilSingkatan .= $urutan;
                            // } else {
                            //     // Jika hanya satu pihak, gunakan singkatan dasar tanpa nomor urut
                            //     // Contoh: P (bukan P1)
                            //     $hasilSingkatan;
                            // }


                            // Gabungkan: [Prefix][Hasil Singkatan]
                            return $prefix . $singkatanDasar;
                        })->implode(', ');
                        return $stringCheckin;
                    }),

                TextColumn::make('status_sidang')
                    ->badge()
                    ->color(function (Perkara $record) {
                        $checkin = \App\Models\CheckinPihak::where('perkara_id', $record->perkara_id)
                            ->whereDate('waktu_checkin', now()->format('Y-m-d'))
                            ->first();
                        return match ($checkin->status_sidang) {
                            'selesai' => 'info',
                            'belum_mulai' => 'warning',

                            'sedang_berlangsung' => 'success',
                            default => 'secondary',
                        };
                    })
                    ->getStateUsing(function (Perkara $record) {
                        $checkin = \App\Models\CheckinPihak::where('perkara_id', $record->perkara_id)
                            ->whereDate('waktu_checkin', now()->format('Y-m-d'))
                            ->first();

                        return $checkin->status_sidang == 'sedang_berlangsung' ? 'Sedang Berlangsung' : ($checkin->status_sidang == 'selesai' ? 'Selesai' : 'Belum Mulai');
                    })
                    ->label('Status Sidang'),
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
                TextColumn::make('waktu_sidang_efektif')
                    ->dateTime('H:i')
                    ->sortable()
                    ->color(fn($record) => $record->waktu_sidang_efektif <= now() ? 'success' : 'warning')
                    ->label('Waktu Sidang'),

                TextColumn::make('status_kehadiran_pihak')
                    ->badge()
                    ->color(fn($state) => str_contains($state, '/') && explode('/', $state)[0] == explode('/', $state)[1] ? 'success' : 'warning')
                    ->label('Kehadiran'),

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

                // TextColumn::make('nomor_perkara_end')
                //     ->getStateUsing(function ($record) {
                //         return $record->nomor_perkara;
                //     })
                //     ->searchable()
                //     ->sortable()
                //     ->label('Nomor Perkara'),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    Action::make('mulai_sidang')
                        ->label('Mulai Sidang')
                        ->icon('heroicon-m-play-circle')
                        ->color('success')
                        ->visible(function (Perkara $record) {
                            $checkin = \App\Models\CheckinPihak::where('perkara_id', $record->perkara_id)
                                ->whereDate('waktu_checkin', now()->format('Y-m-d'))
                                ->first();
                            $status = optional($checkin)->status_sidang;

                            // Kembalikan boolean (true/false) dari perbandingan
                            return $status === 'belum_mulai';
                        })
                        ->action(function (Perkara $record) {
                            // Update semua checkin pihak untuk perkara ini
                            \App\Models\CheckinPihak::where('perkara_id', $record->perkara_id)
                                ->update(['status_sidang' => 'sedang_berlangsung']);
                            event(new RefreshQueuePage());

                            Notification::make()
                                ->title('Sidang Dimulai!')
                                ->body("Sidang perkara {$record->nomor_perkara} sedang berlangsung")
                                ->success()
                                ->send();

                            // Optional: Trigger notifikasi suara
                            // $this->dispatch('play-mulai-sidang');
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Mulai Sidang')
                        ->modalDescription('Apakah Anda yakin ingin memulai sidang perkara ini?')
                        ->modalSubmitActionLabel('Ya, Mulai Sidang'),
                    Action::make('selesai_sidang')
                        ->label('Selesaikan Sidang')
                        ->icon('heroicon-m-play-circle')
                        ->color('info')
                        ->visible(function (Perkara $record) {
                            $checkin = \App\Models\CheckinPihak::where('perkara_id', $record->perkara_id)
                                ->whereDate('waktu_checkin', now()->format('Y-m-d'))
                                ->first();
                            $status = optional($checkin)->status_sidang;

                            // Kembalikan boolean (true/false) dari perbandingan
                            return $status === 'sedang_berlangsung';
                        })
                        ->action(function (Perkara $record) {
                            // Update semua checkin pihak untuk perkara ini
                            \App\Models\CheckinPihak::where('perkara_id', $record->perkara_id)
                                ->whereDate('waktu_checkin', now()->format('Y-m-d'))
                                ->update(['status_sidang' => 'selesai']);
                            event(new RefreshQueuePage());
                            Notification::make()
                                ->title('Sidang Selesai!')
                                ->body("Sidang perkara {$record->nomor_perkara} telah selesai")
                                ->success()
                                ->send();

                            // Optional: Trigger notifikasi suara
                            // $this->dispatch('play-mulai-sidang');
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Selesai Sidang')
                        ->modalDescription('Apakah Anda yakin sidang perkara ini selesai?')
                        ->modalSubmitActionLabel('Ya, Sidang Selesai'),


                    Action::make('detail')->label('Detail Pihak')->icon('heroicon-m-eye')->url(fn($record) => route('filament.admin.resources.checkin-pihaks.index', ['tableFilterForm' => ['perkara_id' => $record->perkara_id]]))->openUrlInNewTab(),
                    Action::make('panggil')
                        ->label('Panggil Sidang')
                        ->icon('heroicon-m-bell')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Pilih Ruang Sidang')
                        ->modalSubmitActionLabel('Panggil')
                        ->form([
                            Select::make('ruang')
                                ->label('Ruang Sidang')
                                ->options([
                                    'kartika' => 'Ruang Sidang Kartika',
                                    'cakra' => 'Ruang Sidang Cakra',
                                    'tirta' => 'Ruang Sidang Tirta',
                                    'anak' => 'Ruang Sidang Anak',
                                    'mediasi' => 'Ruang Mediasi',
                                ])
                                ->required(),
                        ])
                        ->action(function (Perkara $record, $data) {
                            // Ambil data perkara dari koneksi 'sipp'
                            $data_perkara = Perkara::on('sipp')->find($record->perkara_id);

                            // Generate teks panggilan — COPY LOGIKA DARI CI4-MU!
                            $teks_panggilan = self::generateTeksPanggilan($data_perkara, $data['ruang']);

                            // Simpan ke log atau kirim notifikasi
                            try {
                                // Gunakan Laravel HTTP Client untuk request GET
                                $response = Http::get(env('WEBSOCKET_PANGGILAN_URL') . urlencode($teks_panggilan));

                                // Jika respons berhasil, simpan flash message dan kembalikan JSON
                                if ($response->successful()) {
                                    Notification::make()
                                        ->title('Perkara Dipanggil!')
                                        ->body('Sukses')
                                        ->success()
                                        ->send();
                                    return response()->json([
                                        'status' => 'success',
                                        'data' => $response->json() // Ambil data JSON dari respons
                                    ]);
                                }

                                // Jika respons gagal, simpan flash message dan kembalikan JSON
                                Notification::make()
                                    ->title('Perkara Dipanggil!')
                                    ->body('Gagal')
                                    ->danger()
                                    ->send();
                                return response()->json([
                                    'status' => 'fail',
                                    'message' => 'Gagal terhubung ke layanan eksternal'
                                ], $response->status());
                            } catch (\Exception $e) {
                                // Tangani kegagalan koneksi
                                Notification::make()
                                    ->title('Perkara Dipanggil!')
                                    ->body('Gagal')
                                    ->danger()
                                    ->send();
                                return response()->json([
                                    'status' => 'error',
                                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                                ]);
                            }

                            // Notifikasi sukses


                            // Optional: Trigger suara (jika ada)
                            $this->dispatch('play-panggilan-sidang');
                        }),
                ])




            ], position: RecordActionsPosition::BeforeColumns)
            ->paginated(false);
    }
    private static function generateTeksPanggilan($data_perkara, $ruang)
    {
        $ruang_sidang = match ($ruang) {
            'kartika' => 'ruang sidang kartika',
            'cakra' => 'ruang sidang cakra',
            'tirta' => 'ruang sidang tirta',
            'anak' => 'ruang sidang anak',
            'mediasi' => 'ruang mediasi',
            default => 'ruang sidang',
        };

        if ($data_perkara->alur_perkara_id == 2) {
            // Permohonan
            $pihak = explode('<br />', $data_perkara->pihak1_text ?? '');
            $namaPihak = count($pihak) > 1 ? strtolower(substr($pihak[0], 2)) . ", dan kawan kawan" : strtolower($pihak[0] ?? '');
            $nomorPerkara = implode('/', array_slice(explode('/', $data_perkara->nomor_perkara), 0, 3)) . ' ';
            return "Panggilan kepada pihak dalam perkara nomor $nomorPerkara, atas nama $namaPihak agar memasuki $ruang_sidang";
        } elseif (in_array($data_perkara->alur_perkara_id, [1, 7, 8])) {
            // Gugatan
            $pihakPenggugat = explode('<br />', $data_perkara->pihak1_text ?? '');
            $namaPihakPenggugat = count($pihakPenggugat) > 1 ? strtolower(substr($pihakPenggugat[0], 2)) . ", dan kawan kawan" : strtolower($pihakPenggugat[0] ?? '');
            $pihakTergugat = explode('<br />', $data_perkara->pihak2_text ?? '');
            $namaPihakTergugat = count($pihakTergugat) > 1 ? strtolower(substr($pihakTergugat[0], 2)) . ", dan kawan kawan" : strtolower($pihakTergugat[0] ?? '');
            $nomorPerkara = implode('/', array_slice(explode('/', $data_perkara->nomor_perkara), 0, 3)) . ' ';
            return "Panggilan kepada pihak dalam perkara nomor $nomorPerkara, antara $namaPihakPenggugat lawan $namaPihakTergugat agar memasuki $ruang_sidang";
        } else {
            // Pidana
            $terdakwa = explode('<br />', $data_perkara->pihak2_text ?? '');
            $namaTerdakwa = count($terdakwa) > 1 ? strtolower(substr($terdakwa[0], 2)) . ", dan kawan kawan" : strtolower($terdakwa[0] ?? '');
            $nomorPerkara = implode('/', array_slice(explode('/', $data_perkara->nomor_perkara), 0, 3)) . ' ';
            return "Panggilan kepada pihak dalam perkara nomor $nomorPerkara, atas nama Terdakwa $namaTerdakwa agar memasuki $ruang_sidang";
        }
    }
    // protected function getHeaderActions(): array
    // {
    //     return [
    //         CreateAction::make(),
    //     ];
    // }
}
