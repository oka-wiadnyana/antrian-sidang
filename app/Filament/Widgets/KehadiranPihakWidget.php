<?php

namespace App\Filament\Widgets;

use App\Models\CheckinPihak;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KehadiranPihakWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $totalCheckin = CheckinPihak::whereBetween('waktu_checkin', [$todayStart, $todayEnd])->count();
        $langsung = CheckinPihak::whereBetween('waktu_checkin', [$todayStart, $todayEnd])
            ->where('status_kehadiran', 'pihak_langsung')->count();
        $kuasa = CheckinPihak::whereBetween('waktu_checkin', [$todayStart, $todayEnd])
            ->where('status_kehadiran', 'kuasa')->count();

        return [
            Stat::make('Total Kehadiran', $totalCheckin)
                ->description('Hari ini')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),

            Stat::make('Pihak Langsung', $langsung)
                ->description('Hadir sendiri')
                ->descriptionIcon('heroicon-m-user')
                ->color('success'),

            Stat::make('Kuasa Hukum', $kuasa)
                ->description('Diwakili')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('warning'),
        ];
    }
}
