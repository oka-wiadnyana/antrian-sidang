<?php

namespace App\Http\Controllers;

use App\Models\Perkara;
use App\Models\CheckinPihak;
use App\Models\PerkaraJadwalSidang;
use App\Models\PerkaraPihak1;
use App\Models\PerkaraPihak2;
use App\Models\PerkaraPihak3;
use App\Models\PerkaraPihak4;
use Illuminate\Http\Request;

class CheckinPublicController extends Controller
{
    public function showForm()
    {
        return view('public.lapor-hadir');
    }

    public function store(Request $request)
    {
        $request->validate([
            'perkara_id' => 'required|integer',
            'tipe_pihak' => 'required|in:pihak1,pihak2,pihak3,pihak4',
            'nama_yang_hadir' => 'required|string',
            'status_kehadiran' => 'required|in:pihak_langsung,kuasa',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'jarak_meter' => 'required|numeric',
        ]);

        // Validasi geolocation
        if ($request->jarak_meter > env('MAX_JARAK_METER', 1000000)) {
            return response()->json(['error' => 'Anda harus berada di area pengadilan.'], 400);
        }

        // Cek duplikat
        if (CheckinPihak::where('perkara_id', $request->perkara_id)
            ->where('tipe_pihak', $request->tipe_pihak)
            ->exists()
        ) {
            return response()->json(['error' => 'Pihak ini sudah check-in.'], 400);
        }

        // Simpan ke database
        CheckinPihak::create([
            'perkara_id' => $request->perkara_id,
            'tipe_pihak' => $request->tipe_pihak,
            'nama_yang_hadir' => $request->nama_yang_hadir,
            'status_kehadiran' => $request->status_kehadiran,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'jarak_meter' => $request->jarak_meter,
            'ip_address' => $request->ip(),
            'waktu_checkin' => now(),
        ]);

        return response()->json(['success' => 'Laporan kehadiran berhasil!']);
    }

    public function searchPerkara(Request $request)
    {
        $q = $request->get('q');
        $today = now()->format('Y-m-d');

        // Ambil jadwal sidang hari ini — TANPA eager load perkara dulu
        $jadwalHariIni = PerkaraJadwalSidang::whereDate('tanggal_sidang', $today)
            ->with('perkara') // tetap load untuk nama & jenis
            ->get();

        // Filter berdasarkan query
        $filtered = $jadwalHariIni->filter(function ($jadwal) use ($q) {
            if (!$jadwal->perkara) return false;

            $p = $jadwal->perkara;
            return stripos($p->nomor_perkara, $q) !== false ||
                stripos($p->jenis_perkara, $q) !== false;
        })->take(10);

        // ✅ INI KUNCINYA: Kirim perkara_id sebagai "id"
        $result = $filtered->map(function ($jadwal) {
            $p = $jadwal->perkara;

            return [
                'id' => $jadwal->perkara_id, // ✅ INI YANG PENTING! — bukan $p->id, tapi $jadwal->perkara_id
                'nomor_perkara' => $p->nomor_perkara,
                'jenis' => $p->jenis_perkara ?? 'Tidak ada jenis',
                'jam_sidang' => $jadwal->jam_sidang ?? 'Belum ditentukan',
                'text' => trim("{$p->nomor_perkara} - {$p->jenis_perkara} ({$jadwal->jam_sidang})"),
            ];
        })->values(); // ✅ Pastikan jadi array [0,1,2...]

        return response()->json($result);
    }

    public function getPihak($perkara_id)
    {
        $perkara = Perkara::findOrFail($perkara_id);

        $pihak1 = PerkaraPihak1::where('perkara_id', $perkara_id)->get()->pluck('nama')->toArray();
        $pihak2 = PerkaraPihak2::where('perkara_id', $perkara_id)->get()->pluck('nama')->toArray();

        // Hanya load pihak3 & pihak4 untuk gugatan
        $pihak3 = [];
        $pihak4 = [];

        if (in_array($perkara->jenis_perkara, ['gugatan_cerai', 'gugatan_non_cerai', 'gugatan_sederhana'])) {
            $pihak3 = PerkaraPihak3::where('perkara_id', $perkara_id)->get()->pluck('nama')->toArray();
            $pihak4 = PerkaraPihak4::where('perkara_id', $perkara_id)->get()->pluck('nama')->toArray();
        }

        return response()->json([
            'pihak1' => $pihak1,
            'pihak2' => $pihak2,
            'pihak3' => $pihak3,
            'pihak4' => $pihak4,
            'jenis_perkara' => $perkara->jenis_perkara,
        ]);
    }
}
