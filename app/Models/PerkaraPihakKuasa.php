<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerkaraPihakKuasa extends Model
{
    protected $connection = 'sipp';
    protected $table = 'perkara_pengacara';
    public function pihakDiwakili()
    {
        return $this->belongsTo(PihakMain::class, 'pihak_id');
    }
    public function pihak()
    {
        return $this->belongsTo(PihakMain::class, 'pengacara_id');
    }
}
// $telp_pihak1 = db_connect('sipp')->table('perkara_pihak1')->select('telepon,pihak.nama')->join('pihak', 'perkara_pihak1.pihak_id=pihak.id')->where('perkara_id', $pi['perkara_id'])->get()->getResultArray();
// $telp_pihak2 = db_connect('sipp')->table('perkara_pihak2')->select('telepon,pihak.nama')->join('pihak', 'perkara_pihak2.pihak_id=pihak.id')->where('perkara_id', $pi['perkara_id'])->get()->getResultArray();
// $telp_pengacara = db_connect('sipp')->table('perkara_pengacara')->select('pc.telepon,pc.nama as nama_pengacara,ph.nama as nama_pihak ')->join('pihak pc', 'perkara_pengacara.pengacara_id=pc.id')->join('pihak ph', 'perkara_pengacara.pihak_id=ph.id')->where('perkara_id', $pi['perkara_id'])->get()->getResultArray();
