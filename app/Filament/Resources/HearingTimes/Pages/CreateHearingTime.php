<?php

namespace App\Filament\Resources\HearingTimes\Pages;

use App\Filament\Resources\HearingTimes\HearingTimeResource;
use App\Models\HearingTime;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateHearingTime extends CreateRecord
{
    protected static string $resource = HearingTimeResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $exist = HearingTime::where('jenis_perkara', $data['jenis_perkara'])->first();
        if ($exist) {
            Notification::make()
                ->danger()
                ->title('Peringatan')
                ->body('Jenis Perkara Sudah Ada') // Pesan yang lebih jelas
                ->send();
            $this->halt();
        }
        $rec = HearingTime::create($data);
        return $rec;
    }
}
