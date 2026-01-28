<?php

namespace App\Console\Commands;

use App\Models\AntrianSidang;
use App\Models\Perkara;
use App\Models\HearingTime;
use Illuminate\Console\Command;

class SyncAntrianSidang extends Command
{
    protected $signature = 'sync:antrian-sidang {tanggal?}';
    protected $description = 'Sync data perkara dari database SIPP ke tabel antrian_sidang lokal';

    public function handle()
    {
        $tanggal = $this->argument('tanggal') ?? now()->format('Y-m-d');
        $this->info("🔄 Syncing antrian sidang untuk tanggal: $tanggal");

        try {
            // Hapus data lama
            $deleted = AntrianSidang::where('tanggal_sidang', $tanggal)->delete();
            $this->info("🗑️  Deleted {$deleted} old records");

            // Load hearing times
            $hearingTimes = HearingTime::pluck('time', 'jenis_perkara')->toArray();

            // Ambil perkara dari SIPP
            $perkaraList = Perkara::on('sipp')
                ->where(function ($query) use ($tanggal) {
                    $query->whereHas('jadwal', fn($q) => $q->whereDate('tanggal_sidang', $tanggal))
                        ->orWhereHas('jadwalMediasi', fn($q) => $q->whereDate('tanggal_mediasi', $tanggal))
                        ->orWhereHas('jadwalPk', fn($q) => $q->whereDate('tanggal_pemeriksaan', $tanggal));
                })
                ->with([
                    'hakim' => fn($q) => $q->where('jabatan_hakim_id', 1),
                    'mediasi',
                    'jadwalMediasi' => fn($q) => $q->whereDate('tanggal_mediasi', $tanggal),
                    'jadwalPk' => fn($q) => $q->whereDate('tanggal_pemeriksaan', $tanggal),
                    'jadwal' => fn($q) => $q->whereDate('tanggal_sidang', $tanggal),
                ])
                ->withCount(['pihak1', 'pihak2', 'pihak3', 'pihak4'])
                ->get();

            $this->info("📋 Found {$perkaraList->count()} perkara from SIPP");
            if ($perkaraList->isEmpty()) return Command::SUCCESS;

            $bar = $this->output->createProgressBar($perkaraList->count());
            $bar->start();
            $inserted = 0;

            foreach ($perkaraList as $perkara) {
                try {
                    // Tentukan jenis sidang dari SIPP (mediasi/pk/sidang_biasa)
                    $jenisSidang = 'sidang_biasa';
                    $agenda = 'Sidang Lanjutan';

                    if ($perkara->jadwalMediasi->isNotEmpty()) {
                        $jenisSidang = 'mediasi';
                    } elseif ($perkara->jadwalPk->isNotEmpty()) {
                        $jenisSidang = 'pk';
                    } else {
                        $firstJadwal = $perkara->jadwal->first();
                        $agenda = $firstJadwal?->agenda ?? 'Sidang Lanjutan';
                    }

                    // Hitung jenis perkara dasar
                    $alur = $perkara->alur_perkara_id ?? 0;
                    $jenisId = $perkara->jenis_perkara_id ?? null;
                    $jenisPerkaraValue = match (true) {
                        $alur == 8 => 'gugatan_sederhana',
                        $alur == 2 => 'permohonan',
                        $alur == 1 && $jenisId == 64 => 'gugatan_cerai',
                        ($alur == 1 && $jenisId != 64) || $alur == 7 => 'gugatan_non_cerai',
                        in_array($alur, [111, 117, 118]) => 'pidana',
                        $alur == 119 => 'praperadilan',
                        default => 'gugatan_non_cerai'
                    };

                    // ✅ WAKTU DEFAULT dari hearing_time (berdasarkan jenis_sidang dari SIPP)
                    $hearingTimeKey = match ($jenisSidang) {
                        'mediasi' => 'mediasi',
                        'pk' => 'pk',
                        default => $jenisPerkaraValue,
                    };

                    $waktuDefault = $hearingTimes[$hearingTimeKey] ?? '09:00:00';
                    $waktuSidangEfektif = \Carbon\Carbon::createFromFormat(
                        'Y-m-d H:i:s',
                        $tanggal . ' ' . $waktuDefault
                    );

                    // Hitung kehadiran default
                    $totalPihak = ($perkara->pihak1_count ?? 0) + ($perkara->pihak2_count ?? 0) +
                        ($perkara->pihak3_count ?? 0) + ($perkara->pihak4_count ?? 0);
                    $statusKehadiran = $totalPihak > 0 ? "0/{$totalPihak}" : "0/0";

                    // Insert ke antrian_sidang
                    AntrianSidang::create([
                        'perkara_id' => $perkara->perkara_id,
                        'nomor_perkara' => $perkara->nomor_perkara ?? '',
                        'alur_perkara_id' => $perkara->alur_perkara_id,
                        'jenis_perkara' => $jenisPerkaraValue,
                        'tanggal_sidang' => $tanggal,
                        'waktu_sidang_efektif' => $waktuSidangEfektif, // ✅ Waktu default
                        'agenda' => $agenda,
                        'jenis_sidang' => $jenisSidang,
                        'hakim_ketua' => $perkara->hakim_ketua,
                        'panitera_active' => $perkara->panitera_active,
                        'mediator_text' => $perkara->mediasi?->mediator_text,
                        'jumlah_pihak1' => $perkara->pihak1_count ?? 0,
                        'jumlah_pihak2' => $perkara->pihak2_count ?? 0,
                        'jumlah_pihak3' => $perkara->pihak3_count ?? 0,
                        'jumlah_pihak4' => $perkara->pihak4_count ?? 0,
                        'pihak1_text' => $perkara->pihak1_text,
                        'pihak2_text' => $perkara->pihak2_text,
                        'status_kehadiran_pihak' => $statusKehadiran,
                    ]);

                    $inserted++;
                } catch (\Exception $e) {
                    $this->error("Error perkara {$perkara->perkara_id}: " . $e->getMessage());
                }
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("✅ Sync completed! Inserted: {$inserted}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("❌ Fatal error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
