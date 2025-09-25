<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerkaraPaniteraPn extends Model
{
    protected $connection = 'sipp';
    protected $table = 'perkara_panitera_pn'; // sesuaikan nama tabel
    protected $primaryKey = 'id'; // sesuaikan
    public $timestamps = false; // sesuaikan
}
