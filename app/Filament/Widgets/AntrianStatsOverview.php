<?php

namespace App\Filament\Widgets;

use App\Models\Perkara;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AntrianStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $all = Perkara::all();
        $siap = $all->filter(fn($p) => $p->isLengkap() && $p->waktu_sidang_efektif <= now())->count();
        $tertunda = $all->filter(fn($p) => $p->isLengkap() && $p->waktu_sidang_efektif > now())->count();
        $belum = $all->filter(fn($p) => !$p->isLengkap())->count();

        return [
            Stat::make('Siap Sidang', $siap)->color('success'),
            Stat::make('Tertunda Waktu', $tertunda)->color('warning'),
            Stat::make('Belum Lengkap', $belum)->color('danger'),
        ];
    }
}
