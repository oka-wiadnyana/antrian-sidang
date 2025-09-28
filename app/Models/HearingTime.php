<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HearingTime extends Model
{
    protected $table = 'hearing_time';
    protected $fillable = [
        'jenis_perkara',
        'time',

    ];
}
