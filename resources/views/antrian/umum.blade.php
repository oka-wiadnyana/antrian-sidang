<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🖥️ LAYAR ANTRIAN SIDANG — PENGADILAN</title>
    <link rel="icon" href="{{ asset('storage/img/logo_ma.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <meta http-equiv="refresh" content="60">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            color: #e2e8f0;
            font-family: "Poppins", sans-serif;
            padding: 0;
            margin: 0;
            overflow-x: auto;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            padding-left: 10px;
            padding-right: 10px;
        }

        .header {
            background: rgba(30, 41, 59, 0.95);
            backdrop-filter: blur(10px);
            padding: 1.2rem 2rem;
            text-align: center;
            border-bottom: 3px solid #3b82f6;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .header h1 {
            font-size: 2.4rem;
            margin: 0;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.2rem;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .header .date {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-top: 0.5rem;
            font-weight: 500;
        }

        .container {
            padding: 2rem 2.5rem;
            display: flex;
            gap: 2rem;
            min-width: fit-content;
            padding-bottom: 3rem;
            padding-right: 80px;
            align-items: flex-start;
            overflow-x: auto;
            max-width: 100vw;
            scrollbar-width: thin;
            scrollbar-color: #334155 #1e293b;
        }

        .majelis-card {
            background: rgba(30, 41, 59, 0.8);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            width: 380px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(51, 65, 85, 0.5);
            flex: 0 0 auto;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            flex-shrink: 0;
            transition: all 0.3s ease;
            transform: translateZ(0);
        }

        .majelis-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        }

        .majelis-header {
            background: rgba(51, 65, 85, 0.9);
            color: white;
            padding: 1rem 1.2rem;
            border-radius: 16px 16px 0 0;
            font-size: 1.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: center;
            letter-spacing: 0.5px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        }

        .majelis-header i {
            font-size: 1.4rem;
            min-width: 24px;
            text-align: center;
        }

        .antrian-list {
            flex: 1;
            overflow-y: auto;
            padding: 0.8rem 0;
            scrollbar-width: thin;
            scrollbar-color: #334155 #1e293b;
        }

        .perkara-item {
            padding: 1.2rem 1.2rem;
            border-bottom: 1px solid rgba(51, 65, 85, 0.3);
            display: flex;
            align-items: center;
            gap: 1.2rem;
            transition: all 0.2s ease;
        }

        .perkara-item:last-child {
            border-bottom: none;
        }

        .perkara-item:hover {
            background: rgba(51, 65, 85, 0.2);
        }

        .nomor-antrian {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            width: 55px;
            height: 55px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.6rem;
            flex: 0 0 55px;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .perkara-info {
            flex: 1;
            min-width: 0;
        }

        .nomor-perkara {
            font-weight: 800;
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #f8fafc;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            gap: 0.8rem;
        }

        .jenis-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            min-width: 45px;
            text-align: center;
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.8rem;
            min-width: 80px;
            text-align: center;
        }

        .waktu-display {
            font-weight: 800;
            font-size: 1.4rem;
            color: #10b981;
            min-width: 70px;
            text-align: right;
            text-shadow: 0 0 8px rgba(16, 185, 129, 0.3);
            letter-spacing: 0.5px;
        }

        .empty-majelis {
            padding: 2.5rem 1.5rem;
            text-align: center;
            color: #94a3b8;
            font-size: 1rem;
        }

        .empty-majelis i {
            opacity: 0.7;
            margin-bottom: 1rem;
        }

        .clock {
            font-size: 1.4rem;
            font-weight: 700;
            background: rgba(51, 65, 85, 0.8);
            backdrop-filter: blur(10px);
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            display: inline-block;
            margin-left: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        /* Background colors for badges */
        .bg-blue-500 {
            background-color: #3b82f6;
        }

        .bg-yellow-500 {
            background-color: #eab308;
        }

        .bg-red-500 {
            background-color: #ef4444;
        }

        .bg-green-500 {
            background-color: #22c55e;
        }

        /* Scrollbar styling */
        .antrian-list::-webkit-scrollbar {
            width: 8px;
        }

        .antrian-list::-webkit-scrollbar-track {
            background: rgba(30, 41, 59, 0.5);
            border-radius: 10px;
        }

        .antrian-list::-webkit-scrollbar-thumb {
            background: rgba(51, 65, 85, 0.8);
            border-radius: 10px;
            border: 2px solid rgba(30, 41, 59, 0.5);
        }

        .antrian-list::-webkit-scrollbar-thumb:hover {
            background: rgba(51, 65, 85, 1);
        }

        /* Responsive */
        @media (max-width: 1400px) {
            .container {
                padding: 1.5rem 2rem;
                gap: 1.5rem;
            }

            .majelis-card {
                width: 360px;
            }
        }

        @media (max-width: 1200px) {
            .container {
                padding: 1.2rem 1.5rem;
                gap: 1.2rem;
            }

            .majelis-card {
                width: 340px;
            }

            .nomor-antrian {
                width: 50px;
                height: 50px;
                font-size: 1.4rem;
            }

            .nomor-perkara {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 992px) {
            .header h1 {
                font-size: 2rem;
            }

            .majelis-card {
                width: 320px;
            }
        }

        /* FIXED: Mobile layout - cards stack vertically */
        @media (max-width: 768px) {
            .header {
                padding: 1rem 1.5rem;
            }

            .header h1 {
                font-size: 1.8rem;
                gap: 0.8rem;
                flex-direction: column;
            }

            .clock {
                margin-left: 0;
                margin-top: 0.5rem;
                font-size: 1.2rem;
            }

            .container {
                padding: 1rem;
                gap: 1rem;
                /* CHANGE: Make container vertical on mobile */
                flex-direction: column;
                align-items: stretch;
                /* CHANGE: Remove horizontal scroll on mobile */
                overflow-x: visible;
                padding-right: 1rem;
            }

            .majelis-card {
                /* CHANGE: Full width on mobile instead of fixed width */
                width: 100%;
                max-width: none;
                border-radius: 12px;
                /* CHANGE: Remove flex shrink to allow natural height */
                flex-shrink: 1;
                /* CHANGE: Allow more height on mobile since we stack */
                max-height: none;
            }

            .majelis-header {
                padding: 0.8rem 1rem;
                font-size: 1.1rem;
            }

            .perkara-item {
                padding: 1rem;
                gap: 1rem;
            }

            .nomor-antrian {
                width: 45px;
                height: 45px;
                font-size: 1.3rem;
            }

            .nomor-perkara {
                font-size: 1.1rem;
            }

            .waktu-display {
                font-size: 1.2rem;
                min-width: 60px;
            }
        }

        @media (max-width: 576px) {
            .header h1 {
                font-size: 1.6rem;
            }

            .header .date {
                font-size: 1rem;
            }

            /* Additional note styling for mobile */
            .header div:last-child {
                font-size: 0.9rem !important;
                margin-top: 0.5rem;
            }

            .nomor-antrian {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
            }

            .nomor-perkara {
                font-size: 1rem;
            }

            .info-row {
                flex-direction: column;
                gap: 0.5rem;
                align-items: flex-start;
            }

            .waktu-display {
                font-size: 1.1rem;
            }

            .jenis-badge,
            .status-badge {
                font-size: 0.7rem;
                padding: 0.3rem 0.6rem;
            }

            /* Status badge on mobile */
            .info-row span[style*="border"] {
                font-size: 1rem !important;
                padding: 3px 8px !important;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .majelis-card {
            animation: fadeIn 0.5s ease-out;
        }

        /* Scrollbar for container - only for desktop */
        @media (min-width: 769px) {
            .container::-webkit-scrollbar {
                height: 8px;
            }

            .container::-webkit-scrollbar-track {
                background: rgba(30, 41, 59, 0.5);
                border-radius: 10px;
            }

            .container::-webkit-scrollbar-thumb {
                background: rgba(51, 65, 85, 0.8);
                border-radius: 10px;
                border: 2px solid rgba(30, 41, 59, 0.5);
            }

            .container::-webkit-scrollbar-thumb:hover {
                background: rgba(51, 65, 85, 1);
            }
        }

        @media (max-width: 768px) {
            .container {
                overflow-x: hidden !important;
                flex-wrap: wrap;
            }

            .majelis-card {
                min-width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box;
            }

            /* Nonaktifkan efek hover yang tidak perlu di mobile */
            .majelis-card:hover {
                transform: none !important;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3) !important;
            }
        }

        @media (min-width: 769px) {
            .container {
                display: flex;
                gap: 2rem;
                overflow-x: auto;
                scroll-snap-type: x mandatory;
                -webkit-overflow-scrolling: touch;
                /* smooth di iOS */
                padding-bottom: 2rem;
                scrollbar-width: thin;
                scrollbar-color: #334155 #1e293b;
            }

            .majelis-card {
                scroll-snap-align: start;
                flex: 0 0 auto;
                /* JANGAN biarkan shrink atau grow */
            }
        }
    </style>
</head>


<body>
    <div class="header">
        <div class="d-flex justify-content-center align-items-center">
            <h1><i class="fas fa-gavel"></i> ANTRIAN SIDANG HARI INI</h1>
            <div class="clock" id="current-time">
                {{ now()->format('H:i:s') }}
            </div>
        </div>
        <div class="date">
            {{ now()->isoFormat('dddd, D MMMM YYYY') }}
        </div>
        <div
            style="font-size: 0.95rem; line-height: 1.4; margin-top: 0.8rem; max-width: 90vw; overflow-wrap: break-word;">
            <strong>Catatan:</strong> Persidangan Permohonan dimulai pukul 09.00 WITA, Gugatan Perceraian: 11.00 WITA,
            dan Gugatan Non Perceraian/Bantahan/Pidana pukul 14.00 WITA
        </div>
    </div>

    <div class="container">
        @if ($antrian->count() > 0)
            @php
                $urutan_majelis = 0;
            @endphp
            @foreach ($antrian as $kelompok => $perkaraList)
                @php
                    if ($kelompok !== 'PERMOHONAN' && $kelompok !== 'GUGATAN SEDERHANA') {
                        $urutan_majelis++;
                    }
                @endphp
                <div class="majelis-card">
                    <div class="majelis-header">
                        @if ($kelompok === 'PERMOHONAN')
                            <i class="fas fa-file-alt"></i>
                        @elseif($kelompok === 'GUGATAN SEDERHANA')
                            <i class="fas fa-balance-scale"></i>
                        @else
                            <i class="fas fa-user-tie"></i>
                        @endif
                        {{ $perkaraList[0]->jenis_perkara == 'permohonan' || $perkaraList[0]->jenis_perkara == 'gugatan_sederhana' ? strtoupper(implode(' ', explode('_', $kelompok))) : 'Majelis Hakim ' . $urutan_majelis }}
                    </div>

                    <div class="antrian-list" data-auto-scroll="true">
                        @if ($perkaraList->count() > 0)
                            @foreach ($perkaraList as $index => $p)
                                <div class="perkara-item">
                                    <div class="nomor-antrian">{{ $index + 1 }}</div>
                                    <div class="perkara-info">
                                        <div class="nomor-perkara">{{ $p->nomor_perkara }}</div>
                                        <div class="info-row">
                                            <span class="jenis-badge ">
                                                {{ strtoupper(substr($p->jenis_perkara, 0, 3)) }}
                                            </span>
                                            <span class="status-badge ">
                                                {{ $p->status_kehadiran_pihak }}
                                            </span>
                                            @php
                                                $sidangStatus = \App\Models\CheckinPihak::where(
                                                    'perkara_id',
                                                    $p->perkara_id,
                                                )
                                                    ->whereDate('waktu_checkin', $today)
                                                    ->first();
                                                $status = optional($sidangStatus)->status_sidang;
                                                $border =
                                                    optional($sidangStatus)->status_sidang == 'sedang_berlangsung'
                                                        ? '#e7fd21'
                                                        : (optional($sidangStatus)->status_sidang == 'belum_mulai'
                                                            ? '#f80000'
                                                            : '#0063f8');
                                            @endphp

                                            <span class=""
                                                style="border: {{ $border }} 1px solid; color: #fcfdfd; border-radius: 5px; padding: 2px 5px; font-size: 1.3rem; font-weight: 600;">

                                                {{-- <i class="fas fa-play-circle"></i> --}}
                                                {{ optional($sidangStatus)->status_sidang == 'sedang_berlangsung' ? 'Sedang Berlangsung' : (optional($sidangStatus)->status_sidang == 'belum_mulai' ? 'Belum Sidang' : 'Selesai') }}
                                            </span>

                                        </div>
                                    </div>
                                    {{-- <div class="waktu-display">
                                        {{ $p->waktu_sidang_efektif->format('H:i') }}
                                    </div> --}}
                                </div>
                            @endforeach
                        @else
                            <div class="empty-majelis">
                                <i class="fas fa-hourglass-half fa-2x mb-2"></i>
                                <div>Tidak ada perkara</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div style="margin: auto; text-align: center; padding: 4rem 2rem;">
                <i class="fas fa-hourglass-half fa-4x mb-3" style="color: #94a3b8;"></i>
                <h3>Belum ada perkara yang siap sidang</h3>
                <p style="color: #94a3b8; margin-top: 1rem;">Silakan cek kembali nanti</p>
            </div>
        @endif
    </div>

    <script>
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('current-time').textContent = timeString;
        }

        // Auto scroll horizontal UNTUK DESKTOP SAJA
        function autoScrollHorizontal() {
            if (window.innerWidth <= 768) return;

            const container = document.querySelector('.container');
            if (!container) return;

            const cards = Array.from(document.querySelectorAll('.majelis-card'));
            if (cards.length <= 1) return;

            // Cek apakah semua card sudah muat di layar → jangan scroll otomatis!
            const totalCardsWidth = cards.reduce((sum, card) => sum + card.offsetWidth, 0);
            const totalGap = (cards.length - 1) * parseFloat(getComputedStyle(container).gap || '32px');
            const containerWidth = container.clientWidth;

            if (totalCardsWidth + totalGap <= containerWidth) {
                console.log("Semua card muat di layar, auto-scroll dinonaktifkan.");
                return;
            }

            let currentCardIndex = 0;
            let scrollInterval;

            function scrollToCard(index) {
                if (index >= cards.length) index = 0;
                currentCardIndex = index;

                const card = cards[index];
                if (!card) return;

                // Hitung posisi scroll yang TEPAT — termasuk gap!
                let scrollLeft = 0;
                for (let i = 0; i < index; i++) {
                    scrollLeft += cards[i].offsetWidth + parseFloat(getComputedStyle(container).gap || '32px');
                }

                // Jangan scroll melebihi batas
                const maxScroll = container.scrollWidth - container.clientWidth;
                scrollLeft = Math.min(scrollLeft, maxScroll);

                container.scrollTo({
                    left: scrollLeft,
                    behavior: 'smooth'
                });

                // Highlight card aktif
                cards.forEach(c => {
                    c.style.opacity = '0.6';
                    c.style.transform = 'scale(0.98)';
                });
                card.style.opacity = '1';
                card.style.transform = 'scale(1)';
            }

            function startAutoScroll() {
                if (scrollInterval) clearInterval(scrollInterval);

                scrollInterval = setInterval(() => {
                    currentCardIndex = (currentCardIndex + 1) % cards.length;
                    scrollToCard(currentCardIndex);
                }, 8000); // Setiap 8 detik ganti card
            }

            // Mulai dari card pertama
            scrollToCard(0);

            // Mulai auto-scroll setelah jeda
            setTimeout(startAutoScroll, 3000);

            window.horizontalScrollInterval = scrollInterval;
        }

        // Auto scroll vertical untuk tiap majelis (semua device)
        function autoScrollVertical() {
            document.querySelectorAll('.antrian-list').forEach(list => {
                const scrollHeight = list.scrollHeight;
                const clientHeight = list.clientHeight;

                // Hanya scroll jika konten lebih tinggi dari container
                if (scrollHeight > clientHeight) {
                    let scrollPos = 0;
                    const scrollStep = 1; // kecepatan scroll
                    const scrollDelay = 60; // delay antar step (semakin kecil, semakin cepat)
                    let direction = 1; // 1 = turun, -1 = naik

                    const scrollInterval = setInterval(() => {
                        // Jika sampai bawah
                        if (scrollPos >= scrollHeight - clientHeight - 10) {
                            direction = -1; // mulai scroll ke atas
                            clearInterval(scrollInterval);
                            // Tahan 3 detik di bawah
                            setTimeout(() => {
                                const upwardInterval = setInterval(() => {
                                    scrollPos -= scrollStep;
                                    if (scrollPos <= 0) {
                                        clearInterval(upwardInterval);
                                        scrollPos = 0;
                                        // Tahan 3 detik di atas
                                        setTimeout(() => {
                                            autoScrollVertical
                                                (); // restart scroll dari atas
                                        }, 3000);
                                    }
                                    list.scrollTop = scrollPos;
                                }, scrollDelay);
                            }, 3000);
                        }
                        // Jika sampai atas
                        else if (scrollPos <= 0) {
                            direction = 1;
                        }

                        scrollPos += scrollStep * direction;
                        list.scrollTop = scrollPos;
                    }, scrollDelay);

                    list.dataset.scrollInterval = scrollInterval;
                }
            });
        }

        // Jalankan fungsi setelah halaman siap
        document.addEventListener('DOMContentLoaded', function() {
            updateTime();
            setInterval(updateTime, 1000);

            // Tunggu render selesai
            setTimeout(() => {
                autoScrollVertical();
                autoScrollHorizontal();
            }, 2000);
        });

        // Cleanup sebelum halaman ditutup
        window.addEventListener('beforeunload', function() {
            if (window.horizontalScrollInterval) {
                clearInterval(window.horizontalScrollInterval);
            }
            document.querySelectorAll('.antrian-list').forEach(list => {
                if (list.dataset.scrollInterval) {
                    clearInterval(list.dataset.scrollInterval);
                }
            });
        });

        // Handle resize — restart auto scroll horizontal jika perlu
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                // Bersihkan horizontal scroll lama
                if (window.horizontalScrollInterval) {
                    clearInterval(window.horizontalScrollInterval);
                    delete window.horizontalScrollInterval;
                }

                // Bersihkan vertical scroll
                document.querySelectorAll('.antrian-list').forEach(list => {
                    if (list.dataset.scrollInterval) {
                        clearInterval(list.dataset.scrollInterval);
                    }
                });

                // Restart
                autoScrollHorizontal();
                autoScrollVertical();
            }, 250);
        });
    </script>
</body>

</html>
