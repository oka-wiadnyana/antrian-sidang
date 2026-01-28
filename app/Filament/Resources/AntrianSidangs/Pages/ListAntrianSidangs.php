<?php

namespace App\Filament\Resources\AntrianSidangs\Pages;

use App\Events\RefreshQueuePage;
use App\Filament\Resources\AntrianSidangs\AntrianSidangResource;
use App\Models\AntrianSidang;
use App\Models\CheckinPihak;
use App\Models\HearingTime;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Filament\Tables\Tabs\Tab as TableTab;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ListAntrianSidangs extends ListRecords
{
    protected static string $resource = AntrianSidangResource::class;

    protected function getAntrianWithCheckins(string $date): Collection
    {
        // Query antrian_sidang + eager load checkins
        return AntrianSidang::where('tanggal_sidang', $date)
            ->with(['checkins' => function ($query) use ($date) {
                $query->whereDate('waktu_checkin', $date)
                    ->orderBy('waktu_checkin', 'asc');
            }])
            ->get();
    }

    public function getTabs(): array
    {
        $now = now()->format('Y-m-d');
        $antrianHariIni = $this->getAntrianWithCheckins($now);

        $antrianWithCheckins = $antrianHariIni->filter(fn($a) => $a->checkins->isNotEmpty());

        $hakimKeys = collect();

        foreach ($antrianWithCheckins as $antrian) {
            // ✅ Baca jenis_sidang dari CHECKIN (bukan dari antrian_sidang)
            $checkins = $antrian->checkins;
            $hasMediasi = $checkins->contains('jenis_sidang', 'mediasi');
            $hasPk = $checkins->contains('jenis_sidang', 'pk');

            if ($hasMediasi) {
                $key = 'mediasi';
            } elseif ($hasPk) {
                $key = 'pk';
            } else {
                $key = match ($antrian->jenis_perkara) {
                    'permohonan' => 'permohonan',
                    'gugatan_sederhana' => 'gugatan_sederhana',
                    'praperadilan' => 'praperadilan',
                    default => $antrian->hakim_ketua ?? 'Hakim Tidak Ditemukan',
                };
            }
            $hakimKeys->push($key);
        }

        $uniqueKeys = $hakimKeys->unique()->values();
        $tabs = [];

        $tabs['semua'] = Tab::make('Semua')->icon('heroicon-o-list-bullet');

        foreach ($uniqueKeys as $hakim) {
            if ($hakim === 'semua') continue;

            $tabs[$hakim] = match ($hakim) {
                'permohonan' => Tab::make('Permohonan')->icon('heroicon-o-document-text'),
                'gugatan_sederhana' => Tab::make('GS')->icon('heroicon-o-scale'),
                'mediasi' => Tab::make('Mediasi')->icon('heroicon-o-hand-raised'),
                'pk' => Tab::make('PK')->icon('heroicon-o-arrow-path'),
                'praperadilan' => Tab::make('PRAPERADILAN')->icon('heroicon-o-gavel'),
                default => Tab::make($hakim)->icon('heroicon-o-user'),
            };
        }

        return $tabs;
    }

    public function getTableRecords(): Collection
    {
        $selectedTab = $this->activeTab;
        $now = now()->format('Y-m-d');

        $antrianHariIni = $this->getAntrianWithCheckins($now);

        // Filter berdasarkan tab (sama seperti sebelumnya)
        $filteredAntrian = $antrianHariIni->filter(function ($antrian) use ($selectedTab) {
            if ($selectedTab === null || $selectedTab === 'semua' || $selectedTab === '') {
                return true;
            }

            $checkins = $antrian->checkins;
            $hasMediasi = $checkins->contains('jenis_sidang', 'mediasi');
            $hasPk = $checkins->contains('jenis_sidang', 'pk');

            if ($selectedTab === 'mediasi') return $hasMediasi;
            if ($selectedTab === 'pk') return $hasPk;
            if ($selectedTab === 'permohonan') return !$hasMediasi && !$hasPk && $antrian->jenis_perkara === 'permohonan';
            if ($selectedTab === 'gugatan_sederhana') return !$hasMediasi && !$hasPk && $antrian->jenis_perkara === 'gugatan_sederhana';
            if ($selectedTab === 'praperadilan') return !$hasMediasi && !$hasPk && $antrian->jenis_perkara === 'praperadilan';

            return !$hasMediasi && !$hasPk && $antrian->hakim_ketua === $selectedTab;
        });

        // Filter hanya yang ada checkin
        $filteredAntrian = $filteredAntrian->filter(fn($a) => $a->checkins->isNotEmpty());

        // ✅ HITUNG ULANG SEMUA FIELD DINAMIS
        foreach ($filteredAntrian as $antrian) {
            $checkins = $antrian->checkins;

            if ($checkins->isEmpty()) continue;

            // 1. Hitung ulang waktu_sidang_efektif
            $hasMediasi = $checkins->contains('jenis_sidang', 'mediasi');
            $hasPk = $checkins->contains('jenis_sidang', 'pk');
            $hearingTimeKey = $hasMediasi ? 'mediasi' : ($hasPk ? 'pk' : $antrian->jenis_perkara);

            $waktuDefault = \App\Models\HearingTime::where('jenis_perkara', $hearingTimeKey)
                ->value('time') ?? '09:00:00';

            $waktuMinimum = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $now . ' ' . $waktuDefault);
            $checkinsAfterMinimum = $checkins->filter(fn($c) => $c->waktu_checkin >= $waktuMinimum);
            $antrian->waktu_sidang_efektif = $checkinsAfterMinimum->isNotEmpty()
                ? $checkinsAfterMinimum->min('waktu_checkin')
                : $waktuMinimum;

            // 2. ✅ HITUNG ULANG status_kehadiran_pihak
            $hadir = $checkins->count();
            $total = ($antrian->jumlah_pihak1 ?? 0) +
                ($antrian->jumlah_pihak2 ?? 0) +
                ($antrian->jumlah_pihak3 ?? 0) +
                ($antrian->jumlah_pihak4 ?? 0);
            $total = max($total, 1); // Hindari 0/0

            $antrian->status_kehadiran_pihak = "{$hadir}/{$total}";
        }

        // ✅ SORTING: Primary = waktu_sidang_efektif, Secondary = waktu checkin pertama
        return $filteredAntrian->sort(function ($a, $b) {
            $waktuA = $a->waktu_sidang_efektif?->timestamp ?? PHP_INT_MAX;
            $waktuB = $b->waktu_sidang_efektif?->timestamp ?? PHP_INT_MAX;

            if ($waktuA === $waktuB) {
                $checkinA = $a->checkins->isNotEmpty() ? $a->checkins->min('waktu_checkin')->timestamp : PHP_INT_MAX;
                $checkinB = $b->checkins->isNotEmpty() ? $b->checkins->min('waktu_checkin')->timestamp : PHP_INT_MAX;
                return $checkinA <=> $checkinB;
            }

            return $waktuA <=> $waktuB;
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
                    ->label('Nomor Perkara')
                    ->weight('bold'),

                TextColumn::make('kehadiran_detail')
                    ->label('Kehadiran Pihak')
                    ->getStateUsing(function ($record) {
                        $checkins = $record->checkins;

                        return $checkins->map(function ($checkin) use ($record) {
                            $urutan = $checkin->urutan_pihak ?? 1;

                            if (str_contains($checkin->tipe_pihak, 'pihak1')) {
                                $singkatan = 'P';
                                $count = $record->jumlah_pihak1;
                                if ($count > 1) $singkatan .= $urutan;
                            } elseif (str_contains($checkin->tipe_pihak, 'pihak2')) {
                                $singkatan = 'T';
                                $count = $record->jumlah_pihak2;
                                if ($count > 1) $singkatan .= $urutan;
                            } elseif (str_contains($checkin->tipe_pihak, 'pihak3')) {
                                $singkatan = 'I';
                                $count = $record->jumlah_pihak3;
                                if ($count > 1) $singkatan .= $urutan;
                            } else {
                                $singkatan = 'TT';
                                $count = $record->jumlah_pihak4;
                                if ($count > 1) $singkatan .= $urutan;
                            }

                            if ($checkin->status_kehadiran === 'kuasa') {
                                $singkatan = 'K' . $singkatan;
                            }

                            return $singkatan;
                        })->implode(', ');
                    }),

                TextColumn::make('status_sidang')
                    ->badge()
                    ->color(function ($record) {
                        $checkin = $record->checkins->first();
                        return match ($checkin?->status_sidang) {
                            'selesai' => 'success',
                            'sedang_berlangsung' => 'warning',
                            'belum_mulai' => 'gray',
                            default => 'secondary',
                        };
                    })
                    ->getStateUsing(function ($record) {
                        $checkin = $record->checkins->first();
                        if (!$checkin) return 'Belum Check-in';

                        return match ($checkin->status_sidang) {
                            'sedang_berlangsung' => 'Sedang Berlangsung',
                            'selesai' => 'Selesai',
                            default => 'Belum Mulai',
                        };
                    })
                    ->label('Status'),

                TextColumn::make('jenis_perkara')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'permohonan' => 'info',
                        'gugatan_cerai' => 'warning',
                        'gugatan_non_cerai' => 'danger',
                        'gugatan_sederhana' => 'success',
                        'pidana' => 'purple',
                        default => 'secondary',
                    })
                    ->label('Jenis'),

                // ✅ KOLOM WAKTU SIDANG EFEKTIF - SUDAH DIHITUNG DINAMIS DI getTableRecords()
                TextColumn::make('waktu_sidang_efektif')
                    ->dateTime('H:i')
                    ->sortable()
                    ->color(fn($record) => $record->waktu_sidang_efektif && $record->waktu_sidang_efektif <= now() ? 'success' : 'warning')
                    ->label('Waktu'),

                TextColumn::make('status_kehadiran_pihak')
                    ->badge()
                    ->color(fn($record) => $record->is_lengkap ? 'success' : 'warning')
                    ->label('Kehadiran'),

                TextColumn::make('hakim_ketua')
                    ->formatStateUsing(function ($record) {
                        if ($record->mediator_text) {
                            return "{$record->hakim_ketua} - Mediator ({$record->mediator_text})";
                        }
                        return $record->hakim_ketua ?? 'Belum ditetapkan';
                    })
                    ->searchable()
                    ->label('Hakim'),

                TextColumn::make('panitera_active')
                    ->searchable()
                    ->label('PP')
                    ->default('Belum ditetapkan'),

                TextColumn::make('agenda')
                    ->searchable()
                    ->label('Agenda'),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    Action::make('mulai_sidang')
                        ->label('Mulai')
                        ->icon('heroicon-m-play-circle')
                        ->color('success')
                        ->visible(fn($record) => $record->checkins?->first()?->status_sidang === 'belum_mulai')
                        ->action(function ($record) {

                            if (!$record) {
                                Notification::make()
                                    ->title('Error!')
                                    ->body('Data tidak ditemukan')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $perkaraId = $record->perkara_id;
                            $today = now()->format('Y-m-d');

                            CheckinPihak::where('perkara_id', $perkaraId)
                                ->whereDate('waktu_checkin', $today)
                                ->update(['status_sidang' => 'sedang_berlangsung']);

                            event(new RefreshQueuePage());

                            Notification::make()
                                ->title('Sidang Dimulai!')
                                ->body("Perkara {$record->nomor_perkara}")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Mulai Sidang?')
                        ->modalSubmitActionLabel('Ya, Mulai'),

                    Action::make('selesai_sidang')
                        ->label('Selesai')
                        ->icon('heroicon-m-check-circle')
                        ->color('info')
                        ->visible(fn($record) => $record->checkins?->first()?->status_sidang === 'sedang_berlangsung')
                        ->action(function ($record) {

                            if (!$record) {
                                Notification::make()
                                    ->title('Error!')
                                    ->body('Data tidak ditemukan')
                                    ->danger()
                                    ->send();
                                return;
                            }

                            $perkaraId = $record->perkara_id;
                            $today = now()->format('Y-m-d');

                            CheckinPihak::where('perkara_id', $perkaraId)
                                ->whereDate('waktu_checkin', $today)
                                ->update(['status_sidang' => 'selesai']);

                            event(new RefreshQueuePage());

                            Notification::make()
                                ->title('Sidang Selesai!')
                                ->body("Perkara {$record->nomor_perkara}")
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation()
                        ->modalHeading('Sidang Selesai?')
                        ->modalSubmitActionLabel('Ya, Selesai'),

                    Action::make('detail')
                        ->label('Detail')
                        ->icon('heroicon-m-eye')
                        ->url(fn($record) => route('filament.admin.resources.checkin-pihaks.index', [
                            'tableFilterForm' => ['perkara_id' => $record->perkara_id]
                        ]))
                        ->openUrlInNewTab(),

                    Action::make('panggil')
                        ->label('Panggil')
                        ->icon('heroicon-m-bell')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Panggil Sidang')
                        ->modalSubmitActionLabel('Panggil')
                        ->form([
                            \Filament\Forms\Components\Select::make('ruang')
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
                        ->action(function ($record, array $data) {
                            $teks_panggilan = $this->generateTeksPanggilan($record, $data['ruang']);

                            try {
                                $response = Http::timeout(5)->get(
                                    env('WEBSOCKET_PANGGILAN_URL') . urlencode($teks_panggilan)
                                );

                                if ($response->successful()) {
                                    Notification::make()
                                        ->title('Dipanggil!')
                                        ->body('Panggilan berhasil dikirim')
                                        ->success()
                                        ->send();
                                    $this->dispatch('play-panggilan-sidang');
                                } else {
                                    Notification::make()
                                        ->title('Gagal!')
                                        ->body('Layanan panggilan tidak merespon')
                                        ->danger()
                                        ->send();
                                }
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Error!')
                                    ->body('Error: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                ])
            ], position: RecordActionsPosition::BeforeColumns)
            ->paginated(false)
            ->defaultSort('waktu_sidang_efektif', 'asc');
    }

    private function generateTeksPanggilan($record, $ruang)
    {
        $ruang_sidang = match ($ruang) {
            'kartika' => 'ruang sidang kartika',
            'cakra' => 'ruang sidang cakra',
            'tirta' => 'ruang sidang tirta',
            'anak' => 'ruang sidang anak',
            'mediasi' => 'ruang mediasi',
            default => 'ruang sidang',
        };

        $nomorPerkara = implode('/', array_slice(explode('/', $record->nomor_perkara), 0, 3)) . ' ';

        if ($record->jenis_perkara === 'permohonan') {
            $pihak = explode('<br />', $record->pihak1_text ?? '');
            $namaPihak = count($pihak) > 1
                ? strtolower(substr($pihak[0], 2)) . ", dan kawan kawan"
                : strtolower($pihak[0] ?? '');
            return "Panggilan kepada pihak dalam perkara nomor $nomorPerkara, atas nama $namaPihak agar memasuki $ruang_sidang";
        } elseif (in_array($record->jenis_perkara, ['gugatan_cerai', 'gugatan_non_cerai', 'gugatan_sederhana'])) {
            $pihakPenggugat = explode('<br />', $record->pihak1_text ?? '');
            $namaPenggugat = count($pihakPenggugat) > 1
                ? strtolower(substr($pihakPenggugat[0], 2)) . ", dan kawan kawan"
                : strtolower($pihakPenggugat[0] ?? '');

            $pihakTergugat = explode('<br />', $record->pihak2_text ?? '');
            $namaTergugat = count($pihakTergugat) > 1
                ? strtolower(substr($pihakTergugat[0], 2)) . ", dan kawan kawan"
                : strtolower($pihakTergugat[0] ?? '');

            return "Panggilan kepada pihak dalam perkara nomor $nomorPerkara, antara $namaPenggugat lawan $namaTergugat agar memasuki $ruang_sidang";
        } elseif ($record->jenis_perkara === 'pidana') {
            $terdakwa = explode('<br />', $record->pihak2_text ?? '');
            $namaTerdakwa = count($terdakwa) > 1
                ? strtolower(substr($terdakwa[0], 2)) . ", dan kawan kawan"
                : strtolower($terdakwa[0] ?? '');
            return "Panggilan kepada pihak dalam perkara nomor $nomorPerkara, atas nama Terdakwa $namaTerdakwa agar memasuki $ruang_sidang";
        } else {
            return "Panggilan sidang perkara $nomorPerkara ke $ruang_sidang";
        }
    }
}
