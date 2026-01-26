<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🖥️ LAYAR ANTRIAN SIDANG — PENGADILAN</title>
    <link rel="icon" href="{{ asset('storage/img/logo_ma.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        /* === CAROUSEL SYSTEM === */
        .carousel-container {
            height: calc(100vh - 260px);
            position: relative;
            overflow: hidden;
            padding: 0 1.5rem;
        }

        .carousel-wrapper {
            display: flex;
            transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1);
            height: 100%;
        }

        .carousel-slide {
            min-width: 100%;
            height: 100%;
            overflow-y: auto;
            padding: 1rem 0;
            scrollbar-width: thin;
            scrollbar-color: #00ff00 #000;
        }

        .carousel-slide::-webkit-scrollbar {
            width: 8px;
        }

        .carousel-slide::-webkit-scrollbar-track {
            background: #000;
        }

        .carousel-slide::-webkit-scrollbar-thumb {
            background: #00ff00;
            border-radius: 4px;
        }

        /* === CAROUSEL NAVIGATION === */
        .carousel-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 255, 0, 0.1);
            border: 1px solid #00ff00;
            color: #00ff00;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 100;
            transition: all 0.3s;
            font-size: 1.5rem;
        }

        .carousel-nav:hover {
            background: rgba(0, 255, 0, 0.3);
            transform: translateY(-50%) scale(1.1);
        }

        .carousel-nav.prev {
            left: 10px;
        }

        .carousel-nav.next {
            right: 10px;
        }

        /* === CAROUSEL INDICATORS === */
        .carousel-indicators {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 12px;
            z-index: 100;
        }

        .indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            border: 2px solid #00ff00;
            cursor: pointer;
            transition: all 0.3s;
        }

        .indicator.active {
            background: #00ff00;
            width: 40px;
            border-radius: 6px;
        }

        /* === Header Majelis dengan Warna Berbeda === */
        .section-header {
            padding: 1rem;
            margin: 0 0 1.5rem 0;
            font-size: 1.8rem;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 2px;
            border: 2px solid;
            background: #111;
            border-radius: 8px;
        }

        .header-permohonan {
            color: #63b3ed;
            border-color: #3182ce;
            box-shadow: 0 0 20px rgba(49, 130, 206, 0.3);
        }

        .header-gugatan {
            color: #68d391;
            border-color: #38a169;
            box-shadow: 0 0 20px rgba(56, 161, 105, 0.3);
        }

        .header-pidana {
            color: #fc8181;
            border-color: #e53e3e;
            box-shadow: 0 0 20px rgba(229, 62, 62, 0.3);
        }

        .header-perdata {
            color: #b794f6;
            border-color: #805ad5;
            box-shadow: 0 0 20px rgba(128, 90, 213, 0.3);
        }

        .header-lain {
            color: #feb272;
            border-color: #dd6b20;
            box-shadow: 0 0 20px rgba(221, 107, 32, 0.3);
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
            animation: fadeIn 0.5s ease-in;
        }

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
            padding: 3rem 0;
            text-align: center;
            font-size: 1.5rem;
            grid-column: 1 / -1;
        }

        /* === MOBILE RESPONSIVE — DIPERBAIKI === */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.5rem;
            }

            .section-header {
                font-size: 1.3rem;
            }

            .queue-header,
            .queue-item {
                grid-template-columns: 45px 1fr 80px auto;
                font-size: 1.25rem;
                gap: 8px;
            }

            .case-number {
                font-size: 1.25rem;
            }

            .queue-header {
                display: none;
            }

            .carousel-nav {
                width: 40px;
                height: 40px;
                font-size: 1.2rem;
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
            <strong>Catatan:</strong>
            @foreach ($hearingTime as $time)
                @if ($loop->last)
                    {{ implode(' ', explode('_', $time->jenis_perkara)) }} : {{ $time->time }}
                @else
                    {{ implode(' ', explode('_', $time->jenis_perkara)) }} : {{ $time->time }} |
                @endif
            @endforeach
        </div>
    </div>

    <div class="carousel-container">
        <div class="carousel-nav prev" onclick="prevSlide()">
            <i class="fas fa-chevron-left"></i>
        </div>

        <div class="carousel-wrapper" id="carousel">
            @if ($antrian->count() > 0)
                @php
                    $majelisNo = 1;
                @endphp
                @foreach ($antrian as $kelompok => $perkaraList)
                    <div class="carousel-slide">
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
                            @elseif($kelompok === 'MEDIASI')
                                <i class="fas fa-balance-scale"></i> MEDIASI
                            @else
                                <i class="fas fa-user-tie"></i> Majelis Hakim {{ $majelisNo++ }}
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
                    </div>
                @endforeach
            @else
                <div class="carousel-slide">
                    <div class="empty-queue">
                        <i class="fas fa-exclamation-circle"></i><br>
                        TIDAK ADA PERKARA HARI INI
                    </div>
                </div>
            @endif
        </div>

        <div class="carousel-nav next" onclick="nextSlide()">
            <i class="fas fa-chevron-right"></i>
        </div>

        <div class="carousel-indicators" id="indicators"></div>
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

    <script>
        // === CAROUSEL FUNCTIONALITY ===
        let currentSlide = 0;
        const carousel = document.getElementById('carousel');
        const slides = document.querySelectorAll('.carousel-slide');
        const totalSlides = slides.length;
        const indicatorsContainer = document.getElementById('indicators');

        // Auto-rotate interval (8 detik per slide)
        const autoRotateInterval = 8000;
        let autoRotateTimer;

        // Create indicators
        for (let i = 0; i < totalSlides; i++) {
            const indicator = document.createElement('div');
            indicator.className = 'indicator';
            if (i === 0) indicator.classList.add('active');
            indicator.onclick = () => goToSlide(i);
            indicatorsContainer.appendChild(indicator);
        }

        function updateCarousel() {
            carousel.style.transform = `translateX(-${currentSlide * 100}%)`;

            // Update indicators
            document.querySelectorAll('.indicator').forEach((ind, idx) => {
                ind.classList.toggle('active', idx === currentSlide);
            });
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateCarousel();
            resetAutoRotate();
        }

        function prevSlide() {
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateCarousel();
            resetAutoRotate();
        }

        function goToSlide(index) {
            currentSlide = index;
            updateCarousel();
            resetAutoRotate();
        }

        function startAutoRotate() {
            autoRotateTimer = setInterval(nextSlide, autoRotateInterval);
        }

        function resetAutoRotate() {
            clearInterval(autoRotateTimer);
            startAutoRotate();
        }

        // Initialize
        if (totalSlides > 1) {
            startAutoRotate();
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') prevSlide();
            if (e.key === 'ArrowRight') nextSlide();
        });
    </script>
</body>

</html>
