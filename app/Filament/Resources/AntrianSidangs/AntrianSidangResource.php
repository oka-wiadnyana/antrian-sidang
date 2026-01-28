<?php

namespace App\Filament\Resources\AntrianSidangs;

use App\Models\Perkara;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use App\Filament\Resources\AntrianSidangs\Pages;
use App\Filament\Resources\AntrianSidangs\Pages\ListAntrianSidangs;
use App\Models\AntrianSidang;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use UnitEnum;

class AntrianSidangResource extends Resource
{
    protected static ?string $model = AntrianSidang::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string  $navigationLabel = 'Antrian Sidang';
    protected static ?int $navigationSort = 1;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),

        ];
    }

    // public static function table(Table $table): Table
    // {
    //     return $table
    //         // ->query(Perkara::query()->whereHas('jadwal', function ($q) {
    //         //     $q->whereDate('tanggal_sidang', now()->format('Y-m-d'));
    //         // }))
    //         ->columns([
    //             Tables\Columns\TextColumn::make('nomor_perkara')
    //                 ->searchable()
    //                 ->sortable()
    //                 ->label('Nomor Perkara'),

    //             Tables\Columns\TextColumn::make('jenis_perkara')
    //                 ->badge()
    //                 ->color(fn($state) => match ($state) {
    //                     'permohonan' => 'info',
    //                     'gugatan_cerai' => 'warning',
    //                     'gugatan_non_cerai' => 'danger',
    //                     'gugatan_sederhana' => 'success',
    //                     default => 'secondary',
    //                 })
    //                 ->label('Jenis'),

    //             Tables\Columns\TextColumn::make('hakim_ketua')
    //                 ->searchable()
    //                 ->label('Hakim'),

    //             Tables\Columns\TextColumn::make('waktu_sidang_efektif')
    //                 ->dateTime('H:i')
    //                 ->sortable()
    //                 ->color(fn($record) => $record->waktu_sidang_efektif <= now() ? 'success' : 'warning')
    //                 ->label('Waktu Sidang'),

    //             Tables\Columns\TextColumn::make('status_kehadiran_pihak')
    //                 ->badge()
    //                 ->color(fn($state) => str_contains($state, '/') && explode('/', $state)[0] == explode('/', $state)[1] ? 'success' : 'warning')
    //                 ->label('Kehadiran'),
    //         ])
    //         ->filters([
    //             Tables\Filters\SelectFilter::make('hakim_ketua')
    //                 ->label('Hakim Ketua')
    //                 ->options(function () {
    //                     // Ambil nama hakim ketua dari perkara_hakim_pn
    //                     return DB::connection('sipp')
    //                         ->table('perkara_hakim_pn')
    //                         ->join('perkara_jadwal_sidang', 'perkara_hakim_pn.perkara_id', '=', 'perkara_jadwal_sidang.perkara_id')
    //                         ->where('perkara_hakim_pn.jabatan_hakim_id', '1') // hanya hakim ketua
    //                         ->whereDate('perkara_jadwal_sidang.tanggal_sidang', now()->format('Y-m-d'))
    //                         ->pluck('perkara_hakim_pn.hakim_nama', 'perkara_hakim_pn.hakim_nama')
    //                         ->unique()
    //                         ->toArray();
    //                 })
    //                 ->query(function (Builder $query, array $data): Builder {
    //                     // Gunakan whereHas() untuk memfilter berdasarkan relasi
    //                     if (isset($data['value'])) {
    //                         $query->whereHas('hakim', function ($q) use ($data) {
    //                             $q->where('hakim_nama', $data['value']);
    //                         });
    //                     }
    //                     return $query;
    //                 }),
    //             Tables\Filters\SelectFilter::make('jenis_perkara')
    //                 ->options([
    //                     'permohonan' => 'Permohonan',
    //                     'gugatan_cerai' => 'Cerai',
    //                     'gugatan_non_cerai' => 'Non-Cerai',
    //                     'gugatan_sederhana' => 'Sederhana',
    //                 ]),
    //         ])
    //         // ->defaultSort('waktu_sidang_efektif', 'asc')
    //         ->modifyQueryUsing(function ($query) {
    //             // Tidak bisa sort di sini — karena accessor butuh relasi dari koneksi lain
    //         })
    //         ->recordAction(null) // nonaktifkan action default
    //         ->paginated(false)
    //         ->recordActions([
    //             Action::make('detail')->label('Detail Pihak')->icon('heroicon-m-eye')->url(fn($record) => route('filament.admin.resources.checkin-pihaks.index', ['tableFilterForm' => ['perkara_id' => $record->perkara_id]]))->openUrlInNewTab(),
    //             Action::make('panggil')
    //                 ->label('Panggil Sidang')
    //                 ->icon('heroicon-m-bell')
    //                 ->color('warning')
    //                 ->requiresConfirmation()
    //                 ->modalHeading('Pilih Ruang Sidang')
    //                 ->modalSubmitActionLabel('Panggil')
    //                 ->form([
    //                     Select::make('ruang')
    //                         ->label('Ruang Sidang')
    //                         ->options([
    //                             'kartika' => 'Ruang Sidang Kartika',
    //                             'cakra' => 'Ruang Sidang Cakra',
    //                             'tirta' => 'Ruang Sidang Tirta',
    //                             'anak' => 'Ruang Sidang Anak',
    //                         ])
    //                         ->required(),
    //                 ])
    //                 ->action(function (Perkara $record, $data) {
    //                     // Ambil data perkara dari koneksi 'sipp'
    //                     $data_perkara = Perkara::on('sipp')->find($record->perkara_id);

    //                     // Generate teks panggilan — COPY LOGIKA DARI CI4-MU!
    //                     $teks_panggilan = self::generateTeksPanggilan($data_perkara, $data['ruang']);

    //                     // Simpan ke log atau kirim notifikasi
    //                     try {
    //                         // Gunakan Laravel HTTP Client untuk request GET
    //                         $response = Http::get(env('WEBSOCKET_PANGGILAN_URL') . urlencode($teks_panggilan));

    //                         // Jika respons berhasil, simpan flash message dan kembalikan JSON
    //                         if ($response->successful()) {
    //                             Notification::make()
    //                                 ->title('Perkara Dipanggil!')
    //                                 ->body('Sukses')
    //                                 ->success()
    //                                 ->send();
    //                             return response()->json([
    //                                 'status' => 'success',
    //                                 'data' => $response->json() // Ambil data JSON dari respons
    //                             ]);
    //                         }

    //                         // Jika respons gagal, simpan flash message dan kembalikan JSON
    //                         Notification::make()
    //                             ->title('Perkara Dipanggil!')
    //                             ->body('Gagal')
    //                             ->danger()
    //                             ->send();
    //                         return response()->json([
    //                             'status' => 'fail',
    //                             'message' => 'Gagal terhubung ke layanan eksternal'
    //                         ], $response->status());
    //                     } catch (\Exception $e) {
    //                         // Tangani kegagalan koneksi
    //                         Notification::make()
    //                             ->title('Perkara Dipanggil!')
    //                             ->body('Gagal')
    //                             ->danger()
    //                             ->send();
    //                         return response()->json([
    //                             'status' => 'error',
    //                             'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    //                         ]);
    //                     }

    //                     // Notifikasi sukses


    //                     // Optional: Trigger suara (jika ada)
    //                     $this->dispatch('play-panggilan-sidang');
    //                 }),

    //         ]);
    // }

    private static function generateTeksPanggilan($data_perkara, $ruang)
    {
        $ruang_sidang = match ($ruang) {
            'kartika' => 'ruang sidang kartika',
            'cakra' => 'ruang sidang cakra',
            'tirta' => 'ruang sidang tirta',
            'anak' => 'ruang sidang anak',
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

    public static function getPages(): array
    {
        return [
            // 'index' => \App\Filament\Resources\AntrianSidangs\Pages\ListAntrianSidangCustom::route('/'),
            'index' => ListAntrianSidangs::route('/'),
        ];
    }
}
