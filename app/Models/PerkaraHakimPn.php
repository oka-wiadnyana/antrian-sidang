<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerkaraHakimPn extends Model
{
    protected $connection = 'sipp';
    protected $table = 'perkara_hakim_pn'; // sesuaikan nama tabel
    protected $primaryKey = 'id'; // sesuaikan
    public $timestamps = false; // sesuaikan

}
