<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckinPihak extends Model
{



    protected $table = 'checkin_pihak';
    protected $fillable = [
        'perkara_id',
        'tipe_pihak',
        'nama_yang_hadir',
        'urutan_pihak',
        'status_kehadiran',
        'jenis_sidang',
        'latitude',
        'longitude',
        'jarak_meter',
        'ip_address',
        'waktu_checkin',
    ];

    protected $casts = [
        'waktu_checkin' => 'datetime',
        'latitude' => 'double',
        'longitude' => 'double',
        'jarak_meter' => 'double',
        'status_sidang' => 'string',
    ];

    public function perkara()
    {
        return $this->belongsTo(Perkara::class, 'perkara_id', 'perkara_id');
    }
}
