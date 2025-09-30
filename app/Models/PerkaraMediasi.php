<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerkaraMediasi extends Model
{
    protected $connection = 'sipp';
    protected $table = 'perkara_mediasi'; // sesuaikan nama tabel

    // Relasi ke perkara
    public function perkara()
    {
        return $this->belongsTo(Perkara::class, 'perkara_id');
    }

    public function jadwalMediasi()
    {
        return $this->hasMany(PerkaraJadwalMediasi::class, 'mediasi_id', 'mediasi_id');
    }
}
