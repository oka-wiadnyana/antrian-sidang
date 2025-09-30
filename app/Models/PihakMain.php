<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PihakMain extends Model
{
    protected $connection = 'sipp';
    protected $table = 'pihak';
}
