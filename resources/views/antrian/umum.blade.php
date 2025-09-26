<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🖥️ LAYAR ANTRIAN SIDANG — PENGADILAN</title>
    <link rel="icon" href="{{ asset('storage/img/logo_ma.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- <meta http-equiv="refresh" content="180"> --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #000000;
            color: #ffffff;
            font-family: 'IBM Plex Mono', monospace;
            overflow: hidden;
            height: 100vh;
        }

        .header {
            background: #000000;
            padding: 1.2rem 2rem;
            text-align: center;
            border-bottom: 1px solid #00ff00;
            position: relative;
            z-index: 10;
        }

        .header h1 {
            font-size: 2.0rem;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .header .date {
            font-size: 1.1rem;
            color: #00ff00;
            margin-top: 0.5rem;
        }

        .clock {
            color: #ffff00;
            font-weight: 700;
            margin-top: 0.5rem;
            font-size: 1.3rem;
        }

        .notice {
            color: #aaaaaa;
            font-size: 0.95rem;
            margin-top: 0.8rem;
            line-height: 1.4;
        }

        .queue-container {
            height: calc(100vh - 180px);
            overflow: hidden;
            position: relative;
            padding: 0 1.5rem;
        }

        .queue-scroll {
            height: 100%;
            animation: scrollUp 50s linear infinite;
        }

        /* === Header Majelis dengan Warna Berbeda === */
        .section-header {
            padding: 0.8rem 0 0.8rem 1rem;
            margin: 1.4rem 0 1rem 0;
            font-size: 1.3rem;
            font-weight: 700;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-left: 4px solid;
            background: #111;
        }

        .header-permohonan {
            color: #63b3ed;
            border-left-color: #3182ce;
        }

        .header-gugatan {
            color: #68d391;
            border-left-color: #38a169;
        }

        .header-pidana {
            color: #fc8181;
            border-left-color: #e53e3e;
        }

        .header-perdata {
            color: #b794f6;
            border-left-color: #805ad5;
        }

        .header-lain {
            color: #feb272;
            border-left-color: #dd6b20;
        }

        /* === Header Kolom === */
        .queue-header {
            display: grid;
            grid-template-columns: 50px 1fr 90px 100px 180px;
            font-weight: 700;
            font-size: 1.1rem;
            color: #aaaaaa;
            padding: 0.7rem 0;
            margin: 0.8rem 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            gap: 12px;
        }

        /* === Baris Data === */
        .queue-item {
            display: grid;
            grid-template-columns: 50px 1fr 90px 100px 180px;
            align-items: center;
            padding: 0.8rem 0;
            border-bottom: 1px solid #222;
            font-size: 1.4rem;
            font-weight: 600;
            gap: 12px;
        }

        .queue-number {
            text-align: center;
            font-size: 1.4rem;
        }

        .case-number {
            color: #ffffff;
            word-break: break-all;
        }

        /* === Gaya Kolom === */
        .case-type,
        .attendance-status,
        .session-status {
            text-align: center;
            padding: 0.3rem 0.5rem;
            border-radius: 4px;
            min-width: 60px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .case-type {
            color: #00ccff;
            background: rgba(0, 204, 255, 0.1);
            border: 1px solid rgba(0, 204, 255, 0.3);
            text-transform: uppercase;
        }

        .attendance-status.status-present {
            color: #00ff00;
            background: rgba(0, 255, 0, 0.1);
            border: 1px solid rgba(0, 255, 0, 0.3);
        }

        .attendance-status.status-absent {
            color: #ff3333;
            background: rgba(255, 51, 51, 0.1);
            border: 1px solid rgba(255, 51, 51, 0.3);
        }

        .session-status.status-ongoing {
            color: #ffff00;
            background: rgba(255, 255, 0, 0.1);
            border: 1px solid rgba(255, 255, 0, 0.3);
        }

        .session-status.status-waiting {
            color: #ff9900;
            background: rgba(255, 153, 0, 0.1);
            border: 1px solid rgba(255, 153, 0, 0.3);
        }

        .session-status.status-completed {
            color: #00cc66;
            background: rgba(0, 204, 102, 0.1);
            border: 1px solid rgba(0, 204, 102, 0.3);
        }

        /* ✅ DI DESKTOP: Tampilkan teks status lengkap */
        @media (min-width: 769px) {
            .session-status {
                white-space: normal;
                overflow: visible;
                text-overflow: clip;
                min-width: 80px;
                max-width: 160px;
                word-break: break-all;
            }
        }

        .empty-queue {
            color: #888;
            padding: 1.5rem 0;
            text-align: center;
            grid-column: 1 / -1;
        }

        @keyframes scrollUp {
            0% {
                transform: translateY(100%);
            }

            100% {
                transform: translateY(-100%);
            }
        }

        .queue-scroll {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .queue-scroll::-webkit-scrollbar {
            display: none;
        }

        /* === MOBILE RESPONSIVE — DIPERBAIKI === */
        @media (max-width: 768px) {

            .queue-header,
            .queue-item {
                grid-template-columns: 45px 1fr 80px auto;
                /* Gabungkan Kehadiran + Status */
                font-size: 1.25rem;
                gap: 8px;
            }

            .section-header {
                font-size: 1.2rem;
            }

            .case-number {
                font-size: 1.25rem;
            }

            .queue-header {
                display: none;
                /* Sembunyikan header kolom di mobile */
            }

            /* Ubah layout Kehadiran & Status jadi vertikal */
            .queue-item {
                grid-template-columns: 45px 1fr 80px auto;
                gap: 8px;
            }

            .attendance-status,
            .session-status {
                display: block;
                margin: 0.2rem 0;
                padding: 0.3rem 0.5rem;
                min-width: auto;
                max-width: 100%;
                text-align: center;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }

        @media (max-width: 576px) {

            .queue-header,
            .queue-item {
                grid-template-columns: 40px 1fr 70px auto;
                font-size: 1.15rem;
                gap: 6px;
            }

            .queue-header div:nth-child(3),
            .queue-item .case-type {
                display: none;
            }

            .section-header {
                font-size: 1.1rem;
            }

            .case-number {
                font-size: 1.15rem;
            }

            .attendance-status,
            .session-status {
                margin: 0.15rem 0;
                padding: 0.25rem 0.4rem;
                font-size: 0.95rem;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <h1><i class="fas fa-gavel"></i> ANTRIAN SIDANG HARI INI</h1>
        <div class="date">{{ now()->isoFormat('dddd, D MMMM YYYY') }}</div>
        <div class="clock" id="current-time">{{ now()->format('H:i:s') }}</div>
        <div class="notice">
            <strong>Catatan:</strong> Permohonan: 09.00 | Perceraian: 11.00 | Lainnya: 14.00 WITA
        </div>
    </div>

    <div class="queue-container">
        <div class="queue-scroll" id="queue-scroll">
            <!-- Konten utama -->
            <div class="queue-content">
                @if ($antrian->count() > 0)
                    @php
                        $no = 1;
                    @endphp
                    @foreach ($antrian as $kelompok => $perkaraList)
                        <div
                            class="section-header 
                            @if ($kelompok === 'PERMOHONAN') header-permohonan
                            @elseif($kelompok === 'GUGATAN SEDERHANA') header-gugatan
                            @elseif($kelompok === 'PIDANA') header-pidana
                            @elseif($kelompok === 'PERDATA') header-perdata
                            @else header-lain @endif">
                            @if ($kelompok === 'PERMOHONAN')
                                <i class="fas fa-file-alt"></i> PERMOHONAN
                                @php
                                    $no = $no;
                                @endphp
                            @elseif($kelompok === 'GUGATAN SEDERHANA')
                                <i class="fas fa-balance-scale"></i> GUGATAN SEDERHANA
                                @php
                                    $no = $no;
                                @endphp
                            @else
                                <i class="fas fa-user-tie"></i> Majelis Hakim {{ $no++ }}
                            @endif
                        </div>

                        <div class="queue-header">
                            <div>NO</div>
                            <div>NOMOR PERKARA</div>
                            <div>JENIS</div>
                            <div id="attendance-header">KEHADIRAN</div>
                            <div>STATUS</div>
                        </div>

                        @if ($perkaraList->count() > 0)
                            @foreach ($perkaraList as $index => $p)
                                <div class="queue-item">
                                    <div class="queue-number">{{ $index + 1 }}</div>
                                    <div class="case-number">{{ $p->nomor_perkara }}</div>
                                    <div class="case-type">{{ strtoupper(substr($p->jenis_perkara, 0, 3)) }}</div>
                                    <div
                                        class="attendance-status {{ $p->status_kehadiran_pihak === 'Hadir' ? 'status-present' : 'status-absent' }}">
                                        {{ $p->status_kehadiran_pihak }}
                                    </div>
                                    <div
                                        class="session-status 
                                        @php
$sidangStatus = \App\Models\CheckinPihak::where('perkara_id', $p->perkara_id)
                                                ->whereDate('waktu_checkin', $today)
                                                ->first();
                                            $status = optional($sidangStatus)->status_sidang;
                                            if ($status === 'sedang_berlangsung') echo 'status-ongoing';
                                            elseif ($status === 'belum_mulai') echo 'status-waiting';
                                            else echo 'status-completed'; @endphp">
                                        @if ($status === 'sedang_berlangsung')
                                            SEDANG BERLANGSUNG
                                        @elseif($status === 'belum_mulai')
                                            BELUM MULAI
                                        @else
                                            SELESAI
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="empty-queue">Tidak ada perkara</div>
                        @endif
                    @endforeach
                @else
                    <div class="empty-queue">
                        <i class="fas fa-exclamation-circle"></i><br>
                        TIDAK ADA PERKARA HARI INI
                    </div>
                @endif
            </div>

            <!-- Duplikat untuk scroll seamless -->
            <div class="queue-content">
                @if ($antrian->count() > 0)
                    @foreach ($antrian as $kelompok => $perkaraList)
                        <div
                            class="section-header 
                            @if ($kelompok === 'PERMOHONAN') header-permohonan
                            @elseif($kelompok === 'GUGATAN SEDERHANA') header-gugatan
                            @elseif($kelompok === 'PIDANA') header-pidana
                            @elseif($kelompok === 'PERDATA') header-perdata
                            @else header-lain @endif">
                            @if ($kelompok === 'PERMOHONAN')
                                <i class="fas fa-file-alt"></i> PERMOHONAN
                            @elseif($kelompok === 'GUGATAN SEDERHANA')
                                <i class="fas fa-balance-scale"></i> GUGATAN SEDERHANA
                            @else
                                <i class="fas fa-user-tie"></i> {{ strtoupper($kelompok) }}
                            @endif
                        </div>

                        <div class="queue-header">
                            <div>NO</div>
                            <div>NOMOR PERKARA</div>
                            <div>JENIS</div>
                            <div>KEHADIRAN</div>
                            <div>STATUS</div>
                        </div>

                        @if ($perkaraList->count() > 0)
                            @foreach ($perkaraList as $index => $p)
                                <div class="queue-item">
                                    <div class="queue-number">{{ $index + 1 }}</div>
                                    <div class="case-number">{{ $p->nomor_perkara }}</div>
                                    <div class="case-type">{{ strtoupper(substr($p->jenis_perkara, 0, 3)) }}</div>
                                    <div
                                        class="attendance-status {{ $p->status_kehadiran_pihak === 'Hadir' ? 'status-present' : 'status-absent' }}">
                                        {{ $p->status_kehadiran_pihak }}
                                    </div>
                                    <div
                                        class="session-status 
                                        @php
$sidangStatus = \App\Models\CheckinPihak::where('perkara_id', $p->perkara_id)
                                                ->whereDate('waktu_checkin', $today)
                                                ->first();
                                            $status = optional($sidangStatus)->status_sidang;
                                            if ($status === 'sedang_berlangsung') echo 'status-ongoing';
                                            elseif ($status === 'belum_mulai') echo 'status-waiting';
                                            else echo 'status-completed'; @endphp">
                                        @if ($status === 'sedang_berlangsung')
                                            SEDANG BERLANGSUNG
                                        @elseif($status === 'belum_mulai')
                                            BELUM MULAI
                                        @else
                                            SELESAI
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    @vite(['resources/js/app.js'])
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            if (window.Echo) {
                window.Echo.channel('queue-channel')
                    .listen('RefreshQueuePage', (e) => {
                        console.log("Event diterima!", e);
                        location.reload();
                    });
            } else {
                console.error("Echo belum terdefinisi!");
            }
        });
    </script>
    <script>
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString('id-ID', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
        setInterval(updateTime, 1000);
        updateTime();
    </script>

</body>

</html>
