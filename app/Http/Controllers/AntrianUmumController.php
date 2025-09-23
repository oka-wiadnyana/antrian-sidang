<?php

namespace App\Http\Controllers;

use App\Models\CheckinPihak;
use App\Models\Perkara;
use Illuminate\Http\Request;

class AntrianUmumController extends Controller
{
    public function index()
    {
        $today = now()->format('Y-m-d');
        $perkaraHariIni = Perkara::whereHas('jadwal', function ($q) use ($today) {
            $q->whereDate('tanggal_sidang', $today);
        })->get();

        $perkaraIds = $perkaraHariIni->pluck('perkara_id');
        $allCheckins = CheckinPihak::whereIn('perkara_id', $perkaraIds)
            ->get()
            ->groupBy('perkara_id');

        $perkaraSiap = $perkaraHariIni->filter(function ($perkara) use ($allCheckins) {
            $perkara->setRelation('checkins', $allCheckins->get($perkara->perkara_id, collect()));
            return $perkara->adaCheckin() && $perkara->waktu_sidang_efektif <= now();
        })->sortBy('waktu_sidang_efektif');

        $antrian = collect();

        // 1. Kelompok khusus: Permohonan — TAMBAHKAN ->values()
        $permohonan = $perkaraSiap->filter(fn($p) => $p->jenis_perkara === 'permohonan')->values();
        if ($permohonan->count() > 0) {
            $antrian->put('PERMOHONAN', $permohonan);
        }

        // 2. Kelompok khusus: Gugatan Sederhana — TAMBAHKAN ->values()
        $gugatanSederhana = $perkaraSiap->filter(fn($p) => $p->jenis_perkara === 'gugatan_sederhana')->values();
        if ($gugatanSederhana->count() > 0) {
            $antrian->put('GUGATAN SEDERHANA', $gugatanSederhana);
        }

        // 3. Kelompok per hakim: Gugatan Cerai & Non-Cerai — values() otomatis di groupBy
        $gugatanLain = $perkaraSiap->filter(fn($p) => in_array($p->jenis_perkara, ['gugatan_cerai', 'gugatan_non_cerai', 'pidana']));
        $gugatanPerHakim = $gugatanLain->groupBy('hakim_ketua');
        foreach ($gugatanPerHakim as $hakim => $perkaraList) {
            $antrian->put($hakim, $perkaraList);
        }

        return view('antrian.umum', compact('antrian'));
    }
}
