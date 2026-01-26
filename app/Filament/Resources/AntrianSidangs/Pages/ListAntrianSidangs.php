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

    protected function getPerkaraWithCheckins(string $date): Collection
    {
        $perkaraHariIni = static::getResource()::getEloquentQuery()
            ->whereHas('jadwal', fn($q) => $q->whereDate('tanggal_sidang', $date))
            ->orWherehas('jadwalMediasi', fn($q) => $q->whereDate('tanggal_mediasi', $date))
            ->orWherehas('jadwalPk', fn($q) => $q->whereDate('tanggal_pemeriksaan', $date))
            ->withCount(['pihak1', 'pihak2', 'pihak3', 'pihak4'])
            ->with([
                'hakim' => fn($q) => $q->where('jabatan_hakim_id', 1),
                'mediasi',
                'jadwalMediasi' => fn($q) => $q->whereDate('tanggal_mediasi', $date),
                'jadwalPk' => fn($q) => $q->whereDate('tanggal_pemeriksaan', $date),
                'jadwal' => fn($q) => $q->whereDate('tanggal_sidang', $date),
                'pihak1',
                'pihak2',
                'pihak3',
                'pihak4',
            ])
            ->get();

        $perkaraIds = $perkaraHariIni->pluck('perkara_id');

        $allCheckins = CheckinPihak::whereIn('perkara_id', $perkaraIds)
            ->whereDate('waktu_checkin', $date)
            ->orderBy('waktu_checkin', 'asc')
            ->get()
            ->groupBy('perkara_id');

        $perkaraHariIni->each(
            fn($perkara) =>
            $perkara->setRelation('checkins', $allCheckins->get($perkara->perkara_id, collect()))
        );

        return $perkaraHariIni;
    }

    // ✅ Refactor getTabs()
    public function getTabs(): array
    {
        $now = now()->format('Y-m-d');
        // $now = "2026-01-26";

        $perkaraHariIni = $this->getPerkaraWithCheckins($now);

        // Filter hanya yang ada check-in
        $perkaraWithCheckins = $perkaraHariIni->filter(fn($p) => $p->checkins->isNotEmpty());

        $hakimKeys = collect();

        foreach ($perkaraWithCheckins as $perkara) {
            if ($perkara->checkins->contains('jenis_sidang', 'mediasi')) {
                $key = 'mediasi';
            } elseif ($perkara->checkins->contains('jenis_sidang', 'pk')) {
                $key = 'pk';
            } else {
                $key = match ($perkara->alur_perkara_id) {
                    2 => 'permohonan',
                    8 => 'gugatan_sederhana',
                    119 => 'praperadilan',
                    default => optional($perkara->hakim->first())->hakim_nama ?? 'Hakim Tidak Ditemukan',
                };
            }
            $hakimKeys->push($key);
        }

        $uniqueKeys = $hakimKeys->unique()->values();
        $tabs = collect();

        foreach ($uniqueKeys as $hakim) {
            $tab = match ($hakim) {
                'permohonan' => Tab::make('permohonan', 'Permohonan'),
                'gugatan_sederhana' => Tab::make('gugatan_sederhana', 'GS'),
                'mediasi' => Tab::make('mediasi', 'Mediasi'),
                'pk' => Tab::make('pk', 'PK'),
                'praperadilan' => Tab::make('praperadilan', 'PRAPERADILAN'),
                default => Tab::make($hakim, $hakim),
            };

            $tabs->put($hakim, $tab);
        }

        $tabs->prepend(Tab::make('semua', 'Semua'), 'semua');

        return $tabs->toArray();
    }

    // ✅ Refactor getTableRecords()
    public function getTableRecords(): Collection
    {
        $selectedTab = $this->activeTab;
        $now = now()->format('Y-m-d');
        // $now = "2026-01-26";

        // ✅ Reuse shared method
        $perkaraHariIni = $this->getPerkaraWithCheckins($now);

        // Filter berdasarkan tab
        $filteredPerkara = $perkaraHariIni->filter(function ($perkara) use ($selectedTab) {
            if ($selectedTab === null || $selectedTab === 'semua' || $selectedTab === '') {
                return true;
            } elseif ($selectedTab === 'mediasi') {
                return $perkara->checkins->contains('jenis_sidang', 'mediasi');
            } elseif ($selectedTab === 'pk') {
                return $perkara->checkins->contains('jenis_sidang', 'pk');
            } elseif ($selectedTab === 'permohonan') {
                return (int) $perkara->alur_perkara_id === 2;
            } elseif ($selectedTab === 'gugatan_sederhana') {
                return (int) $perkara->alur_perkara_id === 8;
            } elseif ($selectedTab === 'praperadilan') {
                return (int) $perkara->alur_perkara_id === 119;
            } else {
                $hakim = optional($perkara->hakim->first());
                return $hakim && $hakim->hakim_nama === $selectedTab;
            }
        });

        // Filter hanya yang ada check-in
        $filteredPerkara = $filteredPerkara->filter(fn($perkara) => $perkara->checkins->isNotEmpty());

        // Sorting
        return $filteredPerkara->sort(function ($a, $b) {
            $waktuSidangA = $a->waktu_sidang_efektif?->timestamp ?? PHP_INT_MAX;
            $waktuSidangB = $b->waktu_sidang_efektif?->timestamp ?? PHP_INT_MAX;

            $waktuSidangCompare = $waktuSidangA <=> $waktuSidangB;
            if ($waktuSidangCompare !== 0) {
                return $waktuSidangCompare;
            }

            $checkinA = $a->checkins->first()?->waktu_checkin?->timestamp ?? PHP_INT_MAX;
            $checkinB = $b->checkins->first()?->waktu_checkin?->timestamp ?? PHP_INT_MAX;

            return $checkinA <=> $checkinB;
        })->values();
    }

    public function table(Table $table): Table
    {
        return $table
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
                            $urutan = $checkin->urutan_pihak;

                            if (str_contains($checkin->tipe_pihak, 'pihak1')) {
                                $singkatanDasar = 'P';
                                $countPihak = $record->pihak1_count;
                                if ($countPihak > 1) {
                                    $singkatanDasar .= $urutan;
                                }
                            } elseif (str_contains($checkin->tipe_pihak, 'pihak2')) {
                                $singkatanDasar = 'T';
                                $countPihak = $record->pihak2_count;
                                if ($countPihak > 1) {
                                    $singkatanDasar .= $urutan;
                                }
                            } elseif (str_contains($checkin->tipe_pihak, 'pihak3')) {
                                $singkatanDasar = 'I';
                                $countPihak = $record->pihak3_count;
                                if ($countPihak > 1) {
                                    $singkatanDasar .= $urutan;
                                }
                            } else {
                                $singkatanDasar = 'TT';
                                $countPihak = $record->pihak4_count;
                                if ($countPihak > 1) {
                                    $singkatanDasar .= $urutan;
                                }
                            }

                            $prefix = ($checkin->status_kehadiran === 'kuasa') ? 'K' : '';
                            return $prefix . $singkatanDasar;
                        })->implode(', ');

                        return $stringCheckin;
                    }),

                TextColumn::make('status_sidang')
                    ->badge()
                    ->color(function (Perkara $record) {
                        $checkin = $record->checkins->first();

                        return match ($checkin?->status_sidang) {
                            'selesai' => 'info',
                            'belum_mulai' => 'warning',
                            'sedang_berlangsung' => 'success',
                            default => 'secondary',
                        };
                    })
                    ->getStateUsing(function (Perkara $record) {
                        $checkin = $record->checkins->first();

                        if (!$checkin) return 'Belum Check-in';

                        return match ($checkin->status_sidang) {
                            'sedang_berlangsung' => 'Sedang Berlangsung',
                            'selesai' => 'Selesai',
                            default => 'Belum Mulai',
                        };
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
                        } elseif ($record->jadwalPk->isNotEmpty()) {
                            return "PK";
                        }

                        $agenda = $record->jadwal->first()?->agenda;
                        return $agenda ?? 'Sidang Lanjutan (Jadwal Belum Ditetapkan)';
                    })
                    ->searchable()
                    ->label('Agenda'),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    Action::make('mulai_sidang')
                        ->label('Mulai Sidang')
                        ->icon('heroicon-m-play-circle')
                        ->color('success')
                        // ✅ FIXED: Gunakan relasi yang sudah di-load
                        ->visible(function (Perkara $record) {
                            $checkin = $record->checkins->first();
                            return $checkin?->status_sidang === 'belum_mulai';
                        })
                        ->action(function (Perkara $record) {
                            $today = now()->format('Y-m-d');

                            // ✅ FIXED: Tambahkan filter tanggal
                            \App\Models\CheckinPihak::where('perkara_id', $record->perkara_id)
                                ->whereDate('waktu_checkin', $today)
                                ->update(['status_sidang' => 'sedang_berlangsung']);

                            event(new RefreshQueuePage());

                            Notification::make()
                                ->title('Sidang Dimulai!')
                                ->body("Sidang perkara {$record->nomor_perkara} sedang berlangsung")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Mulai Sidang')
                        ->modalDescription('Apakah Anda yakin ingin memulai sidang perkara ini?')
                        ->modalSubmitActionLabel('Ya, Mulai Sidang'),

                    Action::make('selesai_sidang')
                        ->label('Selesaikan Sidang')
                        ->icon('heroicon-m-check-circle')
                        ->color('info')
                        // ✅ FIXED: Gunakan relasi yang sudah di-load
                        ->visible(function (Perkara $record) {
                            $checkin = $record->checkins->first();
                            return $checkin?->status_sidang === 'sedang_berlangsung';
                        })
                        ->action(function (Perkara $record) {
                            $today = now()->format('Y-m-d');

                            // ✅ Already correct with whereDate
                            \App\Models\CheckinPihak::where('perkara_id', $record->perkara_id)
                                ->whereDate('waktu_checkin', $today)
                                ->update(['status_sidang' => 'selesai']);

                            event(new RefreshQueuePage());

                            Notification::make()
                                ->title('Sidang Selesai!')
                                ->body("Sidang perkara {$record->nomor_perkara} telah selesai")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Konfirmasi Selesai Sidang')
                        ->modalDescription('Apakah Anda yakin sidang perkara ini selesai?')
                        ->modalSubmitActionLabel('Ya, Sidang Selesai'),

                    Action::make('detail')
                        ->label('Detail Pihak')
                        ->icon('heroicon-m-eye')
                        ->url(fn($record) => route('filament.admin.resources.checkin-pihaks.index', [
                            'tableFilterForm' => ['perkara_id' => $record->perkara_id]
                        ]))
                        ->openUrlInNewTab(),

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
                        ->action(function (Perkara $record, array $data) {
                            // Ambil data perkara dari koneksi 'sipp'
                            $data_perkara = Perkara::on('sipp')->find($record->perkara_id);

                            // Generate teks panggilan
                            $teks_panggilan = self::generateTeksPanggilan($data_perkara, $data['ruang']);

                            try {
                                // ✅ FIXED: Simplified response handling
                                $response = Http::get(env('WEBSOCKET_PANGGILAN_URL') . urlencode($teks_panggilan));

                                if ($response->successful()) {
                                    Notification::make()
                                        ->title('Perkara Dipanggil!')
                                        ->body('Panggilan sidang berhasil dikirim')
                                        ->success()
                                        ->send();

                                    // ✅ Trigger suara jika ada
                                    $this->dispatch('play-panggilan-sidang');
                                } else {
                                    Notification::make()
                                        ->title('Gagal Memanggil!')
                                        ->body('Gagal terhubung ke layanan panggilan')
                                        ->danger()
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error!')
                                    ->body('Terjadi kesalahan: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
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
