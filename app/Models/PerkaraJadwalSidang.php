<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerkaraJadwalSidang extends Model
{
    protected $connection = 'sipp';
    protected $table = 'perkara_jadwal_sidang'; // sesuaikan nama tabel

    // Relasi ke perkara
    public function perkara()
    {
        return $this->belongsTo(Perkara::class, 'perkara_id');
    }
}
