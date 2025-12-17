<!DOCTYPE html>
<html lang="en">
<?= view('shared/head') ?>

<style>
    #map { height: 240px; width: 100% }
    #video, #snapshot { max-height: 240px; width: 100%; object-fit: cover; border-radius: 8px }
    .d-none { display: none !important }
    .fade-out { opacity: 0; transform: scale(.95) }
    .fade-in { opacity: 1 }
</style>

<body>
    <div class="wrapper">
        <?= view('siswa/navbar'); ?>

        <div class="content-wrapper p-4">
            <div class="container-fluid mt-4">

                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-calendar-check"></i> Absensi Hari Ini
                    </div>

                    <div class="card-body">
                        <div class="row gy-4">

                            <div class="col-lg-4 text-center">

                                <input type="hidden" id="status_absensi" value="<?= $status_absensi ?? 'BELUM' ?>">
                                <input type="hidden" id="latitude_input">
                                <input type="hidden" id="longitude_input">

                                <video id="video" autoplay playsinline></video>
                                <img id="snapshot" class="d-none mt-2">

                                <button id="btnAmbil" class="btn btn-primary w-100 mt-2" disabled onclick="ambilGambar()">
                                    <span class="spinner-border spinner-border-sm"></span> Memuat Lokasi...
                                </button>

                                <div id="afterCaptureButtons" class="d-none mt-2">
                                    <button class="btn btn-danger w-100 mb-2" onclick="ambilUlang()">Ambil Ulang</button>
                                    <button id="btnPresensi" class="btn btn-info w-100" onclick="presensi()">
                                        Absensi
                                    </button>
                                </div>

                                <div id="izinSakitButtons" class="mt-3">
                                    <button class="btn btn-warning w-100 mb-2" onclick="izinSakit('izin')">IZIN</button>
                                    <button class="btn btn-danger w-100" onclick="izinSakit('sakit')">SAKIT</button>
                                </div>

                                <div id="formIzinSakit" class="d-none mt-3">
                                    <textarea id="keteranganIzinSakit" class="form-control mb-2" placeholder="Keterangan"></textarea>
                                    <input type="file" id="fotoIzinSakit" class="form-control mb-2">
                                    <button class="btn btn-primary w-100" onclick="kirimIzinSakit()">Kirim</button>
                                </div>

                            </div>

                            <div class="col-lg-4">
                                <div class="card text-white" style="background:linear-gradient(90deg,#2196f3,#9c27b0)">
                                    <div class="card-body">

                                        <h4><?= $tanggal_hari_ini ?></h4>

                                        <p>
                                            <strong>Masuk:</strong>
                                            <span id="display_jam_masuk">
                                                <?php if (in_array($status_absensi, ['IZIN','SAKIT'])): ?>
                                                    <?= $status_absensi ?>
                                                <?php else: ?>
                                                    <?= $jam_masuk ?? '-' ?>
                                                <?php endif ?>
                                            </span>
                                        </p>

                                        <p>
                                            <strong>Keluar:</strong>
                                            <span id="display_jam_keluar"><?= $jam_keluar ?? '-' ?></span>
                                        </p>

                                        <!-- ✅ TOMBOL RIWAYAT -->
                                        <a href="<?= site_url('siswa/report') ?>" class="btn btn-outline-light w-100 mt-3">
                                            Lihat Riwayat <i class="fas fa-arrow-right"></i>
                                        </a>

                                    </div>
                                </div>

                            </div>

                            <div class="col-lg-4">
                                <div id="map"></div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>

        <script>
            const LOKASI_PUSAT = { lat: <?= $lokasi_absensi['lat'] ?>, lon: <?= $lokasi_absensi['lon'] ?> };
            const RADIUS = <?= $lokasi_absensi['radius'] ?>;
            let modeIzinSakit = null;

            // ===== INIT STATUS =====
            $(document).ready(function() {
                const status = $("#status_absensi").val();
                const btn = $("#btnPresensi");

                switch (status) {
                    case 'BELUM':
                        window.statusPresensi = 'masuk';
                        btn.text('Absensi Masuk');
                        break;

                    case 'MASUK':
                        window.statusPresensi = 'keluar';
                        btn.text('Absensi Keluar');
                        $("#izinSakitButtons").hide();
                        break;

                    case 'SELESAI':
                    case 'IZIN':
                    case 'SAKIT':
                        lockUI(status);
                        break;
                }

                // Kamera
                navigator.mediaDevices.getUserMedia({ video: true })
                    .then(s => video.srcObject = s);

                // Lokasi
                navigator.geolocation.getCurrentPosition(p => {
                    $("#latitude_input").val(p.coords.latitude);
                    $("#longitude_input").val(p.coords.longitude);
                    $("#btnAmbil").prop('disabled', false).text('Ambil Gambar');

                    const map = L.map('map').setView([p.coords.latitude, p.coords.longitude], 18);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                    L.circle([LOKASI_PUSAT.lat, LOKASI_PUSAT.lon], { radius: RADIUS }).addTo(map);
                    L.marker([p.coords.latitude, p.coords.longitude]).addTo(map);
                });
            });

            // ===== LOCK UI =====
            function lockUI(status) {
                $("#btnAmbil,#btnPresensi").prop('disabled', true);
                $("#video,#map,#izinSakitButtons,#afterCaptureButtons").addClass('d-none');
                $("#display_jam_masuk").text(status);
            }

            // ===== CAMERA =====
            function ambilGambar() {
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                $("#snapshot").attr('src', canvas.toDataURL()).removeClass('d-none');
                $("#video,#btnAmbil").addClass('d-none');
                $("#afterCaptureButtons").removeClass('d-none');
            }

            function ambilUlang() {
                $("#snapshot").addClass('d-none');
                $("#video,#btnAmbil").removeClass('d-none');
                $("#afterCaptureButtons").addClass('d-none');
            }

            let btnPresensiText = '';
            function setLoadingPresensi(isLoading = true) {
                const btn = $("#btnPresensi");
                if (isLoading) {
                    btnPresensiText = btn.html();
                    btn.prop('disabled', true).html(`
                        <span class="spinner-border spinner-border-sm me-2"></span>
                        Memproses...
                    `);
                } else {
                    btn.prop('disabled', false).html(btnPresensiText);
                }
            }

            // ===== ABSENSI =====
            function presensi() {
                const status = window.statusPresensi;

                // 🔄 AKTIFKAN SPINNER
                setLoadingPresensi(true);

                $.post('/siswa/absensi', {
                    image: $("#snapshot").attr('src'),
                    latitude: $("#latitude_input").val(),
                    longitude: $("#longitude_input").val(),
                    status: 'HADIR',
                    status_presensi: status
                })
                .done(res => {

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message
                    });

                    if (status === 'masuk' && res.jam_masuk) {
                        $("#display_jam_masuk").text(res.jam_masuk);
                        window.statusPresensi = 'keluar';
                        $("#btnPresensi").text('Absensi Keluar');
                        $("#izinSakitButtons").hide();
                    }

                    else if (status === 'keluar' && res.jam_keluar) {
                        $("#display_jam_keluar").text(res.jam_keluar);
                        $("#btnPresensi")
                            .prop('disabled', true)
                            .removeClass('btn-info')
                            .addClass('btn-secondary')
                            .text('Sudah Absen');
                    }

                    ambilUlang();
                })
                .fail(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: err.responseJSON?.message ?? 'Terjadi kesalahan'
                    });
                })
                .always(() => {
                    // 🔚 MATIKAN SPINNER (kecuali sudah selesai)
                    if (window.statusPresensi !== 'selesai') {
                        setLoadingPresensi(false);
                    }
                });
            }



            // ===== IZIN / SAKIT =====
            function izinSakit(mode) {
                modeIzinSakit = mode;
                $("#video,#map,#izinSakitButtons").addClass('d-none');
                $("#formIzinSakit").removeClass('d-none');
            }

            function kirimIzinSakit() {
                const fd = new FormData();
                fd.append('status', modeIzinSakit);
                fd.append('keterangan', $("#keteranganIzinSakit").val());
                fd.append('foto', $("#fotoIzinSakit")[0].files[0]);

                Swal.fire({
                    title: 'Mengirim...',
                    text: 'Mohon tunggu',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: '/siswa/izin-sakit',
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,

                    success: r => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: r.message,
                            confirmButtonText: 'OK'
                        });

                        // 🔒 Kunci UI & update tampilan
                        lockUI(modeIzinSakit.toUpperCase());
                        $("#display_jam_masuk").text(modeIzinSakit.toUpperCase());
                        $("#display_jam_keluar").text('-');
                        $("#formIzinSakit").addClass('d-none');
                    },

                    error: e => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: e.responseJSON?.message ?? 'Terjadi kesalahan'
                        });
                    }
                });
            }


        </script>
</body>
</html>