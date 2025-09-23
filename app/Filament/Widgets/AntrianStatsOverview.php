<?php

namespace App\Filament\Widgets;

use App\Models\CheckinPihak;
use App\Models\Perkara;
use App\Models\PerkaraJadwalSidang;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AntrianStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->format('Y-m-d');
        $perkaraIdsYangHadir = CheckinPihak::query()
            ->whereDate('waktu_checkin', now())
            ->whereNotNull('waktu_checkin')
            ->pluck('perkara_id')
            ->unique();

        // Langkah 2: Ambil semua PerkaraJadwalSidang berdasarkan ID yang sudah difilter
        if (now()->hour >= 9) {
            $perkaraPermohonanHadir = PerkaraJadwalSidang::query()
                ->whereIn('perkara_id', $perkaraIdsYangHadir)
                ->where('tanggal_sidang', $today)
                ->whereHas('perkara', function ($q) {
                    $q->where('alur_perkara_id', 2);
                })
                // Gunakan with() untuk eager load relasi yang sudah difilter
                ->with('perkara')
                ->get();
        } else {
            $perkaraPermohonanHadir = new Collection();
        }
        if (now()->hour >= 9) {
            $perkaraGsHadir = PerkaraJadwalSidang::query()
                ->whereIn('perkara_id', $perkaraIdsYangHadir)
                ->where('tanggal_sidang', $today)
                ->whereHas('perkara', function ($q) {
                    $q->where('alur_perkara_id', 8);
                })
                // Gunakan with() untuk eager load relasi yang sudah difilter
                ->with('perkara')
                ->get();
        } else {
            $perkaraGsHadir = new Collection();
        }

        if (now()->hour >= 11) {

            $perkaraCeraihadir = PerkaraJadwalSidang::query()
                ->whereIn('perkara_id', $perkaraIdsYangHadir)
                ->where('tanggal_sidang', $today)
                ->whereHas('perkara', function ($q) {
                    $q->where('alur_perkara_id', 1);
                    $q->where('jenis_perkara_id', 64);
                })
                // Gunakan with() untuk eager load relasi yang sudah difilter
                ->with('perkara')
                ->get();
        } else {
            $perkaraCeraihadir = new Collection();
        }

        if (now()->hour >= 14) {

            $perkaraNonCeraihadir = PerkaraJadwalSidang::query()
                ->whereIn('perkara_id', $perkaraIdsYangHadir)
                ->where('tanggal_sidang', $today)
                ->whereHas('perkara', function ($q) {
                    $q->where(function ($q) {
                        $q->where('alur_perkara_id', 1);
                        $q->orWhere('alur_perkara_id', 7);
                    });
                    $q->where('jenis_perkara_id', '!=', 64);
                })
                // Gunakan with() untuk eager load relasi yang sudah difilter
                ->with('perkara')
                ->get();
        } else {
            $perkaraNonCeraihadir = new Collection();
        }

        if (now()->hour >= 14) {
            $perkaraPidanahadir = PerkaraJadwalSidang::query()
                ->whereIn('perkara_id', $perkaraIdsYangHadir)
                ->where('tanggal_sidang', $today)
                ->whereHas('perkara', function ($q) {

                    $q->where('alur_perkara_id', 111);
                    $q->orWhere('alur_perkara_id', 117);
                    $q->orWhere('alur_perkara_id', 118);
                })
                // Gunakan with() untuk eager load relasi yang sudah difilter
                ->with('perkara')
                ->get();
        } else {
            $perkaraPidanahadir = new Collection();
        }
        // // Langkah 3: Lakukan eager loading 'checkins' secara manual
        // // Ambil semua checkins yang relevan dari database terpisah
        // $checkins = CheckinPihak::query()
        //     ->whereIn('perkara_id', $perkaraIdsYangHadir)
        //     ->get()
        //     ->groupBy('perkara_id'); // Kelompokkan berdasarkan perkara_id

        // // Pasangkan checkins ke model PerkaraJadwalSidang
        // $perkaraPermohonanHadir->each(function ($perkara) use ($checkins) {
        //     // Set relasi 'checkins' pada setiap model
        //     $perkara->setRelation('checkins', $checkins->get($perkara->perkara_id, collect()));
        // });




        // $permohonan = $perkaraHariIni->filter(function ($p) {
        //     return $p->jenis_perkara === 'permohonan' && $p->adaCheckin();
        // })->count();
        // dd($permohonan);


        return [
            Stat::make('Permohonan', $perkaraPermohonanHadir->count())
                ->description('Siap sidang hari ini')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Cerai', $perkaraCeraihadir->count())
                ->description('Siap sidang hari ini')
                ->descriptionIcon('heroicon-m-heart')
                ->color('warning')
                ->chart([3, 8, 5, 12, 7, 16, 9]),

            Stat::make('Non-Cerai', $perkaraNonCeraihadir->count())
                ->description('Siap sidang hari ini')
                ->descriptionIcon('heroicon-m-scale')
                ->color('danger')
                ->chart([5, 12, 8, 18, 10, 22, 14]),

            Stat::make('Sederhana', $perkaraGsHadir->count())
                ->description('Siap sidang hari ini')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('success')
                ->chart([2, 5, 3, 8, 6, 11, 7]),
            Stat::make('Pidana', $perkaraPidanahadir->count())
                ->description('Siap sidang hari ini')
                ->descriptionIcon('heroicon-m-sparkles')
                ->color('danger')
                ->chart([2, 5, 3, 8, 6, 11, 7]),
        ];
    }
}
