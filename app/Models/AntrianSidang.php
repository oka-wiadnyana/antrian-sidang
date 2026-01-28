<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AntrianSidang extends Model
{
    protected $table = 'antrian_sidang';
    public $timestamps = false;

    protected $fillable = [
        'perkara_id',
        'nomor_perkara',
        'alur_perkara_id',
        'jenis_perkara',
        'tanggal_sidang',
        'waktu_sidang_efektif',
        'agenda',
        'jenis_sidang',
        'hakim_ketua',
        'panitera_active',
        'mediator_text',
        'jumlah_pihak1',
        'jumlah_pihak2',
        'jumlah_pihak3',
        'jumlah_pihak4',
        'pihak1_text',
        'pihak2_text',
        'status_kehadiran_pihak',
    ];

    protected $casts = [
        'waktu_sidang_efektif' => 'datetime',
        'tanggal_sidang' => 'date',
    ];

    // ✅ RELASI TANPA FILTER (filter di query level)
    public function checkins()
    {
        return $this->hasMany(CheckinPihak::class, 'perkara_id', 'perkara_id');
    }

    // Accessor untuk status kelengkapan
    public function getIsLengkapAttribute()
    {
        $checkins = $this->relationLoaded('checkins') ? $this->checkins : collect();

        $hadir = $checkins->count();
        $total = ($this->jumlah_pihak1 ?? 0) +
            ($this->jumlah_pihak2 ?? 0) +
            ($this->jumlah_pihak3 ?? 0) +
            ($this->jumlah_pihak4 ?? 0);

        return $hadir >= $total && $total > 0;
    }

    public function getCurrentStatusSidangAttribute()
    {
        // ✅ Prioritas 1: Jika relasi sudah di-load (saat render tabel)
        if ($this->relationLoaded('checkins') && $this->checkins->isNotEmpty()) {
            return $this->checkins->first()->status_sidang ?? 'belum_mulai';
        }

        // ✅ Prioritas 2: Query langsung hanya jika diperlukan (saat action)
        return \App\Models\CheckinPihak::where('perkara_id', $this->perkara_id)
            ->whereDate('waktu_checkin', $this->tanggal_sidang)
            ->orderBy('waktu_checkin', 'asc')
            ->value('status_sidang') ?? 'belum_mulai';
    }
}
