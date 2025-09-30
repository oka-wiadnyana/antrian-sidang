<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerkaraJadwalMediasi extends Model
{
    protected $connection = 'sipp';
    protected $table = 'perkara_jadwal_mediasi'; // sesuaikan nama tabel

    // Relasi ke perkara
    public function perkara()
    {
        return $this->hasOneThrough(
            Perkara::class,          // Final model
            PerkaraMediasi::class,   // Intermediate model
            'mediasi_id',                    // Foreign key di PerkaraMediasi (yang diacu oleh mediasi_id)
            'perkara_id',                    // Foreign key di Perkara (yang diacu oleh perkara_id)
            'mediasi_id',            // Local key di PerkaraJadwalMediasi
            'perkara_id'             // Local key di PerkaraMediasi
        );
    }
}
