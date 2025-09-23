<?php

namespace App\Filament\Resources\AntrianSidangs\Pages;

use App\Filament\Resources\AntrianSidangs\AntrianSidangResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;

class ListAntrianSidangCustom extends ListRecords
{
    protected static string $resource = AntrianSidangResource::class;

    public function getTableRecords(): Collection
    {  // Ambil semua perkara hari ini
        $perkaraHariIni = static::getResource()::getEloquentQuery()
            ->whereHas('jadwal', function ($q) {
                $q->whereDate('tanggal_sidang', now()->format('Y-m-d'));
            })->get();

        // Load checkins untuk semua perkara
        $perkaraIds = $perkaraHariIni->pluck('perkara_id');
        $allCheckins = \App\Models\CheckinPihak::whereIn('perkara_id', $perkaraIds)
            ->get()
            ->groupBy('perkara_id');

        // Attach checkins ke setiap perkara
        $perkaraHariIni->each(function ($perkara) use ($allCheckins) {
            $perkara->setRelation('checkins', $allCheckins->get($perkara->perkara_id, collect()));
        });

        // Filter dan sort by waktu_sidang_efektif
        $perkaraSiap = $perkaraHariIni->filter(function ($perkara) {
            return $perkara->adaCheckin() && $perkara->waktu_sidang_efektif <= now();
        })->sortBy('waktu_sidang_efektif');

        return $perkaraSiap;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
