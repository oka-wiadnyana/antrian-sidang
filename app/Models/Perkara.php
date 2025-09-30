<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PerkaraJadwalSidang;

class Perkara extends Model
{
    protected $connection = 'sipp';
    protected $table = 'perkara';
    protected $primaryKey = 'perkara_id'; // sesuaikan jika primary key bukan 'id'
    public $timestamps = false; // sesuaikan jika tidak pakai created_at/updated_at



    // Relasi ke hakim (db_lama)
    public function hakim()
    {
        return $this->hasMany(PerkaraHakimPn::class, 'perkara_id', 'perkara_id');
    }

    public function panitera()
    {
        return $this->hasMany(PerkaraPaniteraPn::class, 'perkara_id', 'perkara_id');
    }

    public function mediasi()
    {
        return $this->hasOne(PerkaraMediasi::class, 'perkara_id', 'perkara_id');
    }

    public function jadwal()
    {
        return $this->hasMany(PerkaraJadwalSidang::class, 'perkara_id', 'perkara_id');
    }
    public function jadwalMediasi()
    {
        return $this->hasManyThrough(
            PerkaraJadwalMediasi::class, // Final model
            PerkaraMediasi::class,       // Intermediate model
            'perkara_id',                // Foreign key di PerkaraMediasi → mengacu ke Perkara
            'mediasi_id',                // Foreign key di PerkaraJadwalMediasi → mengacu ke PerkaraMediasi
            'perkara_id',                // Local key di Perkara (primary key)
            'mediasi_id'                 // Local key di PerkaraMediasi (primary key)
        );
    }

    public function jadwalPk()
    {
        return $this->hasMany(PerkaraJadwalPemeriksaanPk::class, 'perkara_id', 'perkara_id');
    }

    // Relasi ke checkin_pihak (db_antrian) — tetap jalan!
    public function checkins()
    {
        return $this->hasMany(CheckinPihak::class, 'perkara_id', 'perkara_id');
    }

    // ✅ LOGIKA BISNIS: Ambil Hakim Ketua
    public function getHakimKetuaAttribute()
    {
        $ketua = $this->hakim()->where('jabatan_hakim_id', 1)->first();
        if ($ketua) {
            return $ketua->hakim_nama ?? 'Hakim Ketua';
        }

        $first = $this->hakim()->first();
        return $first ? ($first->hakim_nama ?? 'Hakim Tunggal') : 'Belum ditetapkan';
    }

    public function getPaniteraActiveAttribute()
    {

        $first = $this->panitera()->first();

        return $first ? $first->panitera_nama  : 'Belum ditetapkan';
    }

    // ✅ LOGIKA BISNIS: Cek Kelengkapan
    public function isLengkap()
    {
        // Ambil checkins dari relasi yang sudah di-load (bukan query baru!)
        $checkins = $this->relationLoaded('checkins') ? $this->checkins : collect();

        // Cek pihak1
        $p1 = $checkins->where('tipe_pihak', 'pihak1')->isNotEmpty();

        // Jika permohonan
        if ($this->jenis_perkara === 'permohonan') {
            return $p1;
        }

        // Cek pihak2
        $p2 = $checkins->where('tipe_pihak', 'pihak2')->isNotEmpty();
        if (!$p1 || !$p2) {
            return false;
        }

        // Cek pihak3 (intervensi)
        $jumlahPihak3 = $this->pihak3()->count(); // ← ini boleh, karena pihak3 di koneksi sipp
        if ($jumlahPihak3 > 0) {
            $p3Checkin = $checkins->where('tipe_pihak', 'pihak3')->count();
            if ($p3Checkin < $jumlahPihak3) {
                return false;
            }
        }

        // Cek pihak4 (turut tergugat)
        $jumlahPihak4 = $this->pihak4()->count(); // ← ini boleh, karena pihak4 di koneksi sipp
        if ($jumlahPihak4 > 0) {
            $p4Checkin = $checkins->where('tipe_pihak', 'pihak4')->count();
            if ($p4Checkin < $jumlahPihak4) {
                return false;
            }
        }

        return true;
    }
    // ✅ LOGIKA BISNIS: Waktu Mulai Sesi


    // ✅ LOGIKA BISNIS: Waktu Sidang Efektif
    public function getWaktuSidangEfektifAttribute()
    {
        if (!$this->adaCheckin()) return null; // ← PERUBAHAN: pakai adaCheckin()

        $checkins = $this->relationLoaded('checkins') ? $this->checkins : collect();
        $lastCheckin = $checkins->max('waktu_checkin'); // Carbon

        $waktuSesi = now()->setTimeFromTimeString($this->waktu_mulai_sesi); // Carbon
        // dd($waktuSesi);

        return $lastCheckin > $waktuSesi ? $lastCheckin : $waktuSesi;
    }

    public function getJenisPerkaraAttribute()
    {

        $alur = $this->alur_perkara_id;
        $jenis = $this->jenis_perkara_id;
        $jenis_sidang = $this->checkins;
        // dd($jenis_sidang);
        $perkara_id = $this->perkara_id;
        // dd($perkara_id);
        // $jenis_sidang = $this->jenis_sidang;
        // dd($jenis_sidang);


        // Permohonan: alur_perkara_id = 2

        $hasMediasi = $jenis_sidang->contains('jenis_sidang', 'mediasi');
        // dd($hasMediasi);

        $hasPk = $jenis_sidang->contains('jenis_sidang', 'pk');

        if ($hasMediasi) {
            return 'mediasi';
        }
        if ($hasPk) {
            return 'pk';
        }

        if ($alur == 8) {
            return 'gugatan_sederhana';
        }
        if ($alur == 2) {
            return 'permohonan';
        }

        // Gugatan Cerai: alur_perkara_id = 2 DAN jenis_perkara_id = 64
        if ($alur == 1 && $jenis == 64) {
            return 'gugatan_cerai';
        }

        // Gugatan Non-Cerai: alur_perkara_id = 7, atau alur=2 tapi jenis != 64 (tapi sudah ditangani di atas)
        if (($alur == 1 && $jenis != 64) || $alur == 7) {
            return 'gugatan_non_cerai';
        }

        if ($alur == 111  || $alur == 117 || $alur == 118) {
            return 'pidana';
        }


        // if ($jenis_sidang == 'pk') {
        //     return 'pk';
        // }

        // Fallback: jika tidak match, anggap gugatan non-cerai
        return 'gugatan_non_cerai';
    }

    // ✅ LOGIKA BISNIS: Waktu Mulai Sesi
    public function getWaktuMulaiSesiAttribute()
    {
        // $hearing_time = HearingTime::where('jenis_perkara', $this->jenis_perkara)->first()->toArray();

        return match ($this->jenis_perkara) {
            'permohonan' => $hearing_time['time'] ?? '09:00:00',
            'gugatan_sederhana' => $hearing_time['time'] ?? '11:00:00',
            'gugatan_cerai' => $hearing_time['time'] ??  '11:00:00',
            'gugatan_non_cerai' => $hearing_time['time'] ?? '14:00:00',
            'pidana' => $hearing_time['time'] ?? '14:00:00',
            'mediasi' => $hearing_time['time'] ?? '09:00:00',
            'pk' => $hearing_time['time'] ?? '11:00:00',
            default => '09:00:00'
        };
    }

    // Relasi ke pihak1 (penggugat/pemohon)
    public function pihak1()
    {
        return $this->hasMany(PerkaraPihak1::class, 'perkara_id', 'perkara_id');
    }

    // Relasi ke pihak2 (tergugat)
    public function pihak2()
    {
        return $this->hasMany(PerkaraPihak2::class, 'perkara_id', 'perkara_id');
    }

    public function pihak3()
    {
        return $this->hasMany(PerkaraPihak3::class, 'perkara_id', 'perkara_id');
    }

    public function pihak4()
    {
        return $this->hasMany(PerkaraPihak4::class, 'perkara_id', 'perkara_id');
    }

    public function pihak5() // biarkan jika masih dipakai, atau hapus jika tidak
    {
        return $this->hasMany(PerkaraPihak5::class, 'perkara_id', 'perkara_id');
    }
    public function pihak_pengacara() // biarkan jika masih dipakai, atau hapus jika tidak
    {
        return $this->hasMany(PerkaraPihakKuasa::class, 'perkara_id', 'perkara_id');
    }

    // Cek apakah ADA pihak yang sudah check-in
    public function adaCheckin()
    {
        $checkins = $this->relationLoaded('checkins') ? $this->checkins : collect();
        // dd($checkins);
        return $checkins->isNotEmpty();
    }

    public function isCheckedIn($perkara_id, $tanggal_sidang)
    {
        $checkedIn = CheckinPihak::where('perkara_id', '=', $perkara_id)->whereDate('waktu_checkin', now()->format('Y-m-d'))->count()
            ->groupBy('perkara_id');

        return $checkedIn->count();
    }

    // Untuk tampilkan "2/4 pihak hadir" di antrian
    // Accessor: Status Kehadiran Pihak — YANG SUDAH DIPERBAIKI
    public function getStatusKehadiranPihakAttribute()
    {
        // Ambil checkins yang sudah di-load
        $checkins = $this->relationLoaded('checkins') ? $this->checkins : collect();
        $hadir = $checkins->count();

        // Hitung total pihak yang seharusnya hadir
        $total = 0;

        // Pihak1: Penggugat/Pemohon — selalu ada
        $total += $this->pihak1()->count();

        // Untuk gugatan — tambahkan pihak lainnya
        if ($this->jenis_perkara !== 'permohonan') {
            $total += $this->pihak2()->count(); // Tergugat
            $total += $this->pihak3()->count(); // Intervensi
            $total += $this->pihak4()->count(); // Turut Tergugat
        }

        // Jika total 0 — fallback ke 1 (minimal 1 pihak)
        $total = max($total, 1);

        return "{$hadir}/{$total} pihak hadir";
    }
}
