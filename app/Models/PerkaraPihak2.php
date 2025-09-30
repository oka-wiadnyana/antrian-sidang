<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerkaraPihak2 extends Model
{
    protected $connection = 'sipp';
    protected $table = 'perkara_pihak2'; // sesuaikan
    public function pihak()
    {
        return $this->belongsTo(PihakMain::class, 'pihak_id');
    }
}
