<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerkaraPihak5 extends Model
{
    protected $connection = 'sipp';
    protected $table = 'perkara_pihak5';
    public function pihak()
    {
        return $this->belongsTo(PihakMain::class, 'pihak_id');
    }
}
