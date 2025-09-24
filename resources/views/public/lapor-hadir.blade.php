<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lapor Kehadiran Sidang — Pengadilan</title>
    <link rel="icon" href="{{ asset('storage/img/logo_ma.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/selectize.default.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="{{ asset('css/leaflet.css') }}" />

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: "Poppins", sans-serif;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
        }

        .selectize-control {
            width: 100% !important;
        }

        .pihak-list {
            max-height: 150px;
            overflow-y: auto;
        }

        .btn-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-weight: bold;
        }

        .leaflet-div-icon {
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            font-weight: bold;
            border: none;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0">
                    <div class="card-header bg-white text-center py-4">
                        <h3 class="mb-0 text-primary">
                            <i class="bi bi-geo-alt-fill"></i> LAPOR KEHADIRAN SIDANG
                        </h3>
                        <p class="text-muted mb-0">Hanya bisa dilakukan di area pengadilan</p>
                    </div>
                    <div class="alert alert-primary text-center">
                        <i class="bi bi-calendar-check"></i>
                        <strong>Sidang Hari Ini: {{ now()->isoFormat('dddd, D MMMM YYYY') }}</strong>
                    </div>
                    <div class="card-body p-4">

                        <!-- Step 1: Verifikasi Lokasi -->
                        <div class="step active" id="step1">
                            <div class="text-center mb-4">
                                <div class="btn btn-circle btn-primary mb-3">
                                    <i class="bi bi-1-circle"></i>
                                </div>
                                <h5>Verifikasi Lokasi Anda</h5>
                            </div>

                            <!-- 🗺️ Container Peta — BARU! -->
                            <div class="mb-4" style="height: 300px; border-radius: 10px; overflow: hidden;">
                                <div id="map" style="width: 100%; height: 100%;"></div>
                            </div>

                            <div class="alert alert-info" id="instruksi-geolocation">
                                Silakan izinkan akses lokasi untuk melanjutkan.
                            </div>
                            <div class="text-center text-muted small mb-3" id="status-lokasi">
                                Menunggu akses lokasi...
                            </div>
                            <div class="d-grid">
                                <button class="btn btn-primary" id="btn-next-step1" disabled>
                                    <i class="bi bi-arrow-right"></i> Lanjutkan
                                </button>
                            </div>
                        </div>

                        <!-- Step 2: Pilih Perkara -->
                        <div class="step" id="step2">
                            <div class="text-center mb-4">
                                <div class="btn btn-circle btn-success mb-3">
                                    <i class="bi bi-2-circle"></i>
                                </div>
                                <h5>Pilih Nomor Perkara</h5>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Cari Nomor Perkara</label>
                                <select id="select-perkara" class="form-control"
                                    placeholder="Ketik nomor perkara atau jenis perkara...">
                                    <option value="">Pilih atau ketik nomor perkara...</option>
                                </select>
                                <input type="hidden" id="perkara_id" name="perkara_id">
                            </div>
                            <div class="row" id="pihak-container" style="display:none;">
                                <!-- Pihak1 -->
                                <div class="col-md-6">
                                    <div class="border rounded p-2 bg-light">
                                        <h6 class="text-primary" id="label-pihak1"><i class="bi bi-person"></i>
                                            Penggugat / Pemohon</h6>
                                        <div class="pihak-list" id="list-pihak1"></div>
                                    </div>
                                </div>

                                <!-- Pihak2 -->
                                <div class="col-md-6">
                                    <div class="border rounded p-2 bg-light">
                                        <h6 class="text-danger" id="label-pihak2"><i class="bi bi-person"></i> Tergugat
                                            / Terbantah</h6>
                                        <div class="pihak-list" id="list-pihak2"></div>
                                    </div>
                                </div>

                                <!-- Pihak3: Intervensi (hanya gugatan) -->
                                <div class="col-md-6 mt-3" style="display: none;">
                                    <div class="border rounded p-2 bg-info">
                                        <h6 class="text-white" id="label-pihak3"><i class="bi bi-person-plus"></i> Pihak
                                            Intervensi</h6>
                                        <div class="pihak-list" id="list-pihak3"></div>
                                    </div>
                                </div>

                                <!-- Pihak4: Turut Tergugat/Terbantah (hanya gugatan) -->
                                <div class="col-md-6 mt-3" style="display: none;">
                                    <div class="border rounded p-2 bg-warning">
                                        <h6 class="text-dark" id="label-pihak4"><i class="bi bi-people"></i> Turut
                                            Tergugat / Turut Terbantah</h6>
                                        <div class="pihak-list" id="list-pihak4"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-grid mt-3">
                                <button class="btn btn-success" id="btn-next-step2" disabled>
                                    <i class="bi bi-arrow-right"></i> Pilih Pihak
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Form Lapor -->
                        <!-- Step 3 -->

                        <form method="POST" class="step" id="form-lapor">
                            @csrf
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                            <input type="hidden" name="jarak_meter" id="jarak_meter">
                            <input type="hidden" name="perkara_id" id="form-perkara-id">
                            <input type="hidden" name="tipe_pihak" id="form-tipe-pihak">
                            <input type="hidden" name="nama_yang_hadir" id="form-nama-yang-hadir">

                            <!-- Info Pihak -->
                            <div class="alert alert-info">
                                <h6><i class="bi bi-person-badge"></i> Pihak yang Dipilih:</h6>
                                <p id="info-pihak" class="mb-0">Pilih salah satu pihak di atas</p>
                            </div>

                            <!-- Status Kedaieran -->
                            <div class="mb-3">
                                <label class="form-label">Status Kedaieran</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status_kehadiran"
                                        id="status_langsung" value="pihak_langsung" checked>
                                    <label class="form-check-label" for="status_langsung">
                                        <i class="bi bi-person"></i> Pihak Langsung
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status_kehadiran"
                                        id="status_kuasa" value="kuasa">
                                    <label class="form-check-label" for="status_kuasa">
                                        <i class="bi bi-briefcase"></i> Kuasa Hukum
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex">
                                <button type="button" class="btn btn-outline-secondary" id="btn-back-step3">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </button>
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-check-circle"></i> Konfirmasi kedaieran
                                </button>
                            </div>
                        </form>


                    </div>
                    <div class="card-footer bg-white text-center py-3 flex flex-col">
                        <div style="width: 100%">

                            <a href="{{ url('antrian-umum') }}" class="btn btn-warning " style="width: 100%"
                                target="_blank">Lihat
                                Antrian</a>
                        </div>
                        <small class="text-muted">© 2025 Pengadilan Negeri Tabanan. Sistem Antrian Sidang.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ asset('js/selectize.min.js') }}"></script>
    <script src="{{ asset('js/leaflet.js') }}"></script>
    <script>
        const PENGADILAN_LAT = {{ env('PENGADILAN_LATITUDE', -6.2088) }};
        const PENGADILAN_LNG = {{ env('PENGADILAN_LONGITUDE', 106.8456) }};
        const MAX_JARAK = {{ env('MAX_JARAK_METER', 100) }};

        function calculateDistance(lat1, lng1, lat2, lng2) {
            const R = 6371e3;
            const φ1 = lat1 * Math.PI / 180;
            const φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lng2 - lng1) * Math.PI / 180;
            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            return R * c;
        }

        let map, markerUser, markerCourt, circle;
        let mapInitialized = false;

        function initMap() {
            if (mapInitialized) return;

            const mapContainer = document.getElementById('map');
            if (!mapContainer) {
                console.error('❌ Element #map tidak ditemukan!');
                return;
            }

            try {
                map = L.map('map').setView([PENGADILAN_LAT, PENGADILAN_LNG], 18);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
                }).addTo(map);

                markerCourt = L.marker([PENGADILAN_LAT, PENGADILAN_LNG], {
                    icon: L.divIcon({
                        className: 'bg-danger text-white rounded-circle d-flex align-items-center justify-content-center',
                        html: '<strong>🏛️</strong>',
                        iconSize: [40, 40],
                        iconAnchor: [20, 20]
                    })
                }).addTo(map);
                markerCourt.bindPopup("<b>Pusat Pengadilan</b>").openPopup();

                circle = L.circle([PENGADILAN_LAT, PENGADILAN_LNG], {
                    color: 'red',
                    fillColor: '#f03',
                    fillOpacity: 0.2,
                    radius: MAX_JARAK
                }).addTo(map);

                map.fitBounds(circle.getBounds());
                mapInitialized = true;
            } catch (error) {
                console.error('❌ Gagal inisialisasi peta:', error);
            }
        }

        function initGeolocation() {
            const statusDiv = $('#status-lokasi');
            const btnNext = $('#btn-next-step1');

            if (!navigator.geolocation) {
                statusDiv.html('<span class="text-danger">🌐 Geolocation tidak didukung browser ini.</span>');
                return;
            }

            statusDiv.html('📡 Meminta akses lokasi...');
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;
                    const jarak = calculateDistance(PENGADILAN_LAT, PENGADILAN_LNG, userLat, userLng);

                    $('#latitude').val(userLat);
                    $('#longitude').val(userLng);
                    $('#jarak_meter').val(jarak.toFixed(2));

                    if (!markerUser) {
                        markerUser = L.marker([userLat, userLng], {
                            icon: L.divIcon({
                                className: 'bg-primary text-white rounded-circle d-flex align-items-center justify-content-center',
                                html: '<strong>👤</strong>',
                                iconSize: [40, 40],
                                iconAnchor: [20, 20]
                            })
                        }).addTo(map);
                    } else {
                        markerUser.setLatLng([userLat, userLng]);
                    }

                    markerUser.bindPopup(`<b>Anda di sini</b><br>Jarak: ${jarak.toFixed(2)} meter`).openPopup();
                    map.setView([userLat, userLng], 18);

                    if (jarak <= MAX_JARAK) {
                        statusDiv.html(
                            `<span class="text-success">✅ Anda berada di area pengadilan (${jarak.toFixed(2)} meter)</span>`
                        );
                        btnNext.prop('disabled', false);
                        markerUser.setIcon(L.divIcon({
                            className: 'bg-success text-white rounded-circle d-flex align-items-center justify-content-center',
                            html: '<strong>✅</strong>',
                            iconSize: [40, 40],
                            iconAnchor: [20, 20]
                        }));
                    } else {
                        statusDiv.html(
                            `<span class="text-danger">🚫 Anda berada di luar area (${jarak.toFixed(2)} meter). Maks ${MAX_JARAK} meter.</span>`
                        );
                        markerUser.setIcon(L.divIcon({
                            className: 'bg-danger text-white rounded-circle d-flex align-items-center justify-content-center',
                            html: '<strong>❌</strong>',
                            iconSize: [40, 40],
                            iconAnchor: [20, 20]
                        }));
                    }
                },
                (error) => {
                    console.error(error);
                    statusDiv.html(
                        '<span class="text-warning">⚠️ Gagal mendapatkan lokasi. Izinkan akses atau coba lagi.</span>'
                    );
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        }

        function initSelectize() {
            if (typeof $.fn.selectize === 'undefined') {
                console.error('❌ Selectize.js belum dimuat!');
                return;
            }

            $('#select-perkara').selectize({
                valueField: 'id',
                labelField: 'text',
                searchField: ['nomor_perkara', 'jenis'],
                create: false,
                render: {
                    option: function(item, escape) {
                        let badge = '';
                        if (item.jenis === 'permohonan') {
                            badge = '<span class="badge bg-info ms-2">Permohonan</span>';
                        } else if (item.jenis === 'gugatan_cerai') {
                            badge = '<span class="badge bg-warning ms-2">Cerai</span>';
                        } else if (item.jenis === 'gugatan_non_cerai') {
                            badge = '<span class="badge bg-danger ms-2">Non-Cerai</span>';
                        } else {
                            badge = '<span class="badge bg-secondary ms-2">Lainnya</span>';
                        }

                        return '<div>' +
                            '<strong>' + escape(item.nomor_perkara) + '</strong> ' + badge + '<br>' +
                            '<small class="text-success">🕒 ' + escape(item.jam_sidang) + '</small>' +
                            '</div>';
                    }
                },
                load: function(query, callback) {
                    if (!query.length) {
                        return callback();
                    }
                    fetch(`/api/perkara/search?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => callback(data))
                        .catch(() => callback());
                },
                onChange: function(value) {
                    if (!value) {
                        $('#pihak-container').hide();
                        $('#btn-next-step2').prop('disabled', true);
                        return;
                    }
                    $('#perkara_id').val(value);
                    loadPihak(value);
                }
            });
        }

        function loadPihak(perkaraId) {
            $.get(`/api/perkara/${perkaraId}/pihak?t=${Date.now()}`, function(data) {
                console.log('=== DATA PIHAK ===', data);

                let html1 = '',
                    html2 = '',
                    html3 = '',
                    html4 = '';

                let labelPihak1 = 'Penggugat / Pembantah';
                let labelPihak2 = 'Tergugat / Terbantah';
                let labelPihak3 = 'Pihak Intervensi';
                let labelPihak4 = 'Turut Tergugat / Turut Terbantah';

                if (data.jenis_perkara === 'permohonan') {
                    labelPihak1 = 'Pemohon';
                }
                if (data.jenis_perkara === 'pidana') {
                    labelPihak1 = 'JPU';
                    labelPihak2 = 'Terdakwa';
                }

                // Pihak1
                if (data.pihak1.length > 0) {
                    data.pihak1.forEach(nama => {
                        const idSafe = nama.replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_]/g, '');
                        html1 += `<div class="form-check mb-2">
                        <input class="form-check-input pihak-radio" 
                               type="radio" 
                               name="tipe_pihak" 
                               value="pihak1" 
                               data-nama="${nama}"
                               id="pihak1_${idSafe}">
                        <label class="form-check-label" for="pihak1_${idSafe}">
                            <i class="bi bi-person-circle"></i> ${nama}
                        </label>
                    </div>`;
                    });
                    $('#list-pihak1').html(html1);
                    $('#label-pihak1').html(`<i class="bi bi-person"></i> ${labelPihak1}`);
                } else {
                    $('#list-pihak1').html('<div class="text-muted">Tidak ada data</div>');
                }

                // Untuk Gugatan: tampilkan pihak2,3,4
                if (data.jenis_perkara !== 'permohonan') {
                    // Pihak2
                    if (data.pihak2.length > 0) {
                        data.pihak2.forEach(nama => {
                            const idSafe = nama.replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_]/g, '');
                            html2 += `<div class="form-check mb-2">
                            <input class="form-check-input pihak-radio" 
                                   type="radio" 
                                   name="tipe_pihak" 
                                   value="pihak2" 
                                   data-nama="${nama}"
                                   id="pihak2_${idSafe}">
                            <label class="form-check-label" for="pihak2_${idSafe}">
                                <i class="bi bi-person-circle"></i> ${nama}
                            </label>
                        </div>`;
                        });
                        $('#list-pihak2').html(html2);
                        $('#label-pihak2').html(`<i class="bi bi-person"></i> ${labelPihak2}`);
                        $('#list-pihak2').closest('.col-md-6').show();
                    } else {
                        $('#list-pihak2').html('<div class="text-muted">Tidak ada data</div>');
                        $('#list-pihak2').closest('.col-md-6').hide();
                    }

                    // Pihak3
                    if (data.pihak3.length > 0) {
                        data.pihak3.forEach(nama => {
                            const idSafe = nama.replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_]/g, '');
                            html3 += `<div class="form-check mb-2">
                            <input class="form-check-input pihak-radio" 
                                   type="radio" 
                                   name="tipe_pihak" 
                                   value="pihak3" 
                                   data-nama="${nama}"
                                   id="pihak3_${idSafe}">
                            <label class="form-check-label" for="pihak3_${idSafe}">
                                <i class="bi bi-person-plus"></i> ${nama}
                            </label>
                        </div>`;
                        });
                        $('#list-pihak3').html(html3);
                        $('#label-pihak3').html(`<i class="bi bi-person-plus"></i> ${labelPihak3}`);
                        $('#list-pihak3').closest('.col-md-6').show();
                    } else {
                        $('#list-pihak3').html('<div class="text-muted">Tidak ada data</div>');
                        $('#list-pihak3').closest('.col-md-6').hide();
                    }

                    // Pihak4
                    if (data.pihak4.length > 0) {
                        data.pihak4.forEach(nama => {
                            const idSafe = nama.replace(/\s+/g, '_').replace(/[^a-zA-Z0-9_]/g, '');
                            html4 += `<div class="form-check mb-2">
                            <input class="form-check-input pihak-radio" 
                                   type="radio" 
                                   name="tipe_pihak" 
                                   value="pihak4" 
                                   data-nama="${nama}"
                                   id="pihak4_${idSafe}">
                            <label class="form-check-label" for="pihak4_${idSafe}">
                                <i class="bi bi-people"></i> ${nama}
                            </label>
                        </div>`;
                        });
                        $('#list-pihak4').html(html4);
                        $('#label-pihak4').html(`<i class="bi bi-people"></i> ${labelPihak4}`);
                        $('#list-pihak4').closest('.col-md-6').show();
                    } else {
                        $('#list-pihak4').html('<div class="text-muted">Tidak ada data</div>');
                        $('#list-pihak4').closest('.col-md-6').hide();
                    }
                } else {
                    // Untuk permohonan — sembunyikan pihak2,3,4
                    $('#list-pihak2').closest('.col-md-6').hide();
                    $('#list-pihak3').closest('.col-md-6').hide();
                    $('#list-pihak4').closest('.col-md-6').hide();
                }

                $('#pihak-container').show();

                const adaPihak = data.pihak1.length > 0 ||
                    (data.jenis_perkara !== 'permohonan' &&
                        (data.pihak2.length > 0 || data.pihak3.length > 0 || data.pihak4.length > 0));

                $('#btn-next-step2').prop('disabled', !adaPihak);
            }).fail(function() {
                console.error('❌ Gagal memuat data pihak.');
                alert('Gagal memuat data pihak. Silakan coba lagi.');
            });
        }

        // ========== EVENT LISTENER DELEGASI ==========
        $(document).ready(function() {
            setTimeout(() => {
                initMap();
                initGeolocation();
                initSelectize();
            }, 500);

            // Step 1 → Step 2
            // Step 1 → Step 2
            $(document).on('click', '#btn-next-step1', function() {
                $('#step1').removeClass('active');
                $('#step2').addClass('active');
            });

            // Step 2 → Step 3
            $(document).on('click', '#btn-next-step2', function() {
                if (!$('#form-tipe-pihak').val()) {
                    alert('Silakan pilih salah satu pihak terlebih dahulu.');
                    return;
                }
                $('#step2').removeClass('active');
                $('#form-lapor').addClass('active');
                $('html, body').animate({
                    scrollTop: 0
                }, 300);
            });

            // Step 3 → Step 2
            $(document).on('click', '#btn-back-step3', function() {
                $('#form-lapor').removeClass('active');
                $('#step2').addClass('active');
            });

            // Pilih pihak → isi hidden input
            $(document).on('change', '.pihak-radio', function() {
                const namaPihak = $(this).data('nama');
                const tipePihak = $(this).val();

                // ISI HIDDEN INPUT — INI YANG PALING PENTING!
                $('#form-tipe-pihak').val(tipePihak);
                $('#form-nama-yang-hadir').val(namaPihak);
                $('#form-perkara-id').val($('#perkara_id').val()); // ← JANGAN LUPA INI!

                // Tampilkan info
                let labelTipe = 'Pihak';
                if (tipePihak === 'pihak1') labelTipe = 'Penggugat/Pemohon';
                if (tipePihak === 'pihak2') labelTipe = 'Tergugat';
                if (tipePihak === 'pihak3') labelTipe = 'Intervensi';
                if (tipePihak === 'pihak4') labelTipe = 'Turut Tergugat';

                $('#info-pihak').html(`
        <strong>${namaPihak}</strong><br>
        <small class="text-muted">${labelTipe}</small>
    `);

                // Aktifkan tombol "Pilih Pihak"
                $('#btn-next-step2').prop('disabled', false);
            });

            // Toggle status kehadiran
            $(document).on('change', 'input[name="status_kehadiran"]', function() {
                if ($(this).val() === 'kuasa') {
                    $('#info-pihak').append('<br><span class="badge bg-warning">👤 Diwakili Kuasa</span>');
                } else {
                    // $('#info-pihak').html($('#info-pihak').html().replace(
                    //     '<br><span class="badge bg-warning">👤 Diwakili kuasa</span>', ''));
                    // remove the badge
                    $('#info-pihak').find('.badge').remove();

                }
            });

            // Form submit
            $(document).on('submit', '#form-lapor', function(e) {
                e.preventDefault();

                console.log('=== DEBUG DATA FORM ===');
                console.log('perkara_id:', $('#form-perkara-id').val());
                console.log('tipe_pihak:', $('#form-tipe-pihak').val());
                console.log('nama_yang_hadir:', $('#form-nama-yang-hadir').val());
                console.log('status_kehadiran:', $('input[name="status_kehadiran"]:checked').val());
                console.log('latitude:', $('#latitude').val());
                console.log('longitude:', $('#longitude').val());
                console.log('jarak_meter:', $('#jarak_meter').val());

                // Validasi client-side
                if (!$('#form-tipe-pihak').val()) {
                    alert('Silakan pilih pihak.');
                    return;
                }
                if (!$('input[name="status_kehadiran"]:checked').val()) {
                    alert('Silakan pilih status kehadiran.');
                    return;
                }

                const formData = {
                    perkara_id: $('#form-perkara-id').val(),
                    tipe_pihak: $('#form-tipe-pihak').val(),
                    nama_yang_hadir: $('#form-nama-yang-hadir').val(),
                    status_kehadiran: $('input[name="status_kehadiran"]:checked').val(),
                    latitude: $('#latitude').val(),
                    longitude: $('#longitude').val(),
                    jarak_meter: $('#jarak_meter').val()
                };

                // Validasi
                if (!formData.tipe_pihak) {
                    alert('Silakan pilih pihak.');
                    return;
                }
                if (!formData.status_kehadiran) {
                    alert('Silakan pilih status kehadiran.');
                    return;
                }

                // Kirim ke server
                $.ajax({
                    url: '/api/checkin',
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content') // ← INI SOLUSINYA!
                    },
                    success: function(response) {
                        alert('✅ ' + response.success);
                        location.reload();
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON?.error || 'Gagal melaporkan kehadiran.';
                        alert('❌ ' + error);
                    }
                });
            });

            // Tombol cek ulang lokasi
            $(document).on('click', '#btn-recheck-location', function() {
                initGeolocation();
            });
        });
    </script>
</body>

</html>
