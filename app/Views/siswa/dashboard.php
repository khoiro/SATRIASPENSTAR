<!DOCTYPE html>
<html lang="en">
<style>
  #map {
    height: 240px;
    width: 100%;
  }

  #video,
  #snapshot {
    transition: opacity 0.5s ease, transform 0.5s ease;
  }

  .fade-out {
    opacity: 0;
    transform: scale(0.95);
    pointer-events: none;
  }

  .fade-in {
    opacity: 1;
    transform: scale(1);
  }

  .d-none {
    display: none !important;
  }
  #video, #snapshot {
    max-height: 240px;
    object-fit: cover;
    width: 100%;
    border-radius: 8px;
  }

</style>
<?= view('shared/head') ?>
<body>
<div class="wrapper">
  <?= view('siswa/navbar'); ?>
  <div class="content-wrapper p-4">
    <div class="container-fluid mt-4">
      
      <!-- Card Wrapper -->
      <div class="card shadow">
        <!-- Title Bar -->
       <div class="card-header bg-primary text-white d-flex align-items-center">
          <i class="fas fa-calendar-check me-2"></i>
          <h5 class="mb-0">&nbsp;Absensi Hari Ini</h5>
       </div>


        <!-- Card Body -->
        <div class="card-body">
          <div class="row gy-4 align-items-start">
            
            <!-- Preview Kamera & Tombol -->
            <div class="col-12 col-md-3 text-center mb-3 mb-md-0">
              <video id="video" autoplay playsinline style="width: 100%; max-height: 240px; border-radius: 8px;"></video>
              <img id="snapshot" class="img-fluid rounded mt-2 d-none" alt="Hasil Gambar">
              <input type="hidden" id="latitude_input">
              <input type="hidden" id="longitude_input">
              <input type="hidden" id="sudah_masuk" value="<?= $jam_masuk ? '1' : '0' ?>">
              <input type="hidden" id="sudah_keluar" value="<?= $jam_keluar ? '1' : '0' ?>">


              <!-- Tombol: Ambil Gambar -->
              <button id="btnAmbil" class="btn btn-primary btn-block mt-2" onclick="ambilGambar()">Ambil Gambar</button>

              <!-- Tombol: Ambil Ulang & Presensi Keluar (disembunyikan awalnya) -->
              <div id="afterCaptureButtons" class="d-none mt-2">
                <button class="btn btn-danger btn-block mb-2" onclick="ambilUlang()">Ambil Ulang</button>
                <button id="btnPresensi" class="btn btn-info btn-block" onclick="presensi()">Absensi</button>
              </div>
            </div>

            <!-- Info Absensi -->
            <div class="col-12 col-md-4 mb-3 mb-md-0">
              <div class="card text-white" style="background: linear-gradient(90deg, #2196f3, #9c27b0);">
                <div class="card-body">
                  <h2 class="mb-3"><?= $tanggal_hari_ini; ?></h2>
                  <p><strong>Masuk:</strong> <?= $jam_masuk ? $jam_masuk : '-'; ?></p>
                  <p><strong>Keluar:</strong> <?= $jam_keluar ? $jam_keluar : '-'; ?></p>
                  <a href="#" class="btn btn-outline-light mt-3">Lihat Riwayat <i class="fas fa-arrow-right"></i></a>
                </div>
              </div>
            </div>

            


            <!-- Peta -->
            <div class="col-12 col-md-5">
              <div id="map" style="height: 240px;"></div>
            </div>

          </div>
        </div>
      </div>
      <!-- End Card -->

    </div>
  </div>
</div>


<script>
    // --- Variabel Global dan Konfigurasi ---
    const LOKASI_PUSAT = {
        lat: -7.44710975382454,
        lon: 112.52221433900381
    };
    const RADIUS_ABSENSI = 100; // dalam meter

    // Fungsi menghitung jarak dalam meter (Haversine formula)
    function getDistanceFromLatLonInMeters(lat1, lon1, lat2, lon2) {
        const R = 6371e3;
        const toRad = angle => angle * Math.PI / 180;
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a =
            Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    // --- Inisialisasi Aplikasi Setelah DOM Siap ---
    $(document).ready(function () {
        const $video = $("#video");
        const videoElement = $video[0];
        const $btnPresensi = $("#btnPresensi");
        let statusPresensi = "selesai";

        // 1. Tentukan Status Presensi Hari Ini
        const sudahMasuk = $("#sudah_masuk").val() === "1";
        const sudahKeluar = $("#sudah_keluar").val() === "1";

        if (!sudahMasuk) {
            statusPresensi = "masuk";
            $btnPresensi.text("Absensi Masuk");
        } else if (!sudahKeluar) {
            statusPresensi = "keluar";
            $btnPresensi.text("Absensi Keluar");
        } else {
            $btnPresensi.text("Sudah Absen Hari Ini");
            $btnPresensi.prop("disabled", true);
        }

        // Simpan status ke window agar bisa diakses di fungsi lain
        window.statusPresensi = statusPresensi;

        // 2. Akses Kamera
        if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } }) // Preferensikan kamera depan
                .then(function (stream) {
                    videoElement.srcObject = stream;
                })
                .catch(function (err) {
                    alert("Gagal mengakses kamera: " + err.message);
                });
        } else {
            alert("Browser tidak mendukung akses kamera.");
        }

        // 3. Akses Geolocation dan Inisialisasi Peta
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                $("#latitude_input").val(lat);
                $("#longitude_input").val(lon);

                // Hitung jarak pengguna ke lokasi pusat
                const userDistance = getDistanceFromLatLonInMeters(lat, lon, LOKASI_PUSAT.lat, LOKASI_PUSAT.lon);

                // Validasi jarak
                if (userDistance > RADIUS_ABSENSI) {
                    $btnPresensi.prop("disabled", true).text("Di Luar Radius");
                } 
                // Catatan: Jika statusPresensi sudah 'selesai', tombol tetap disabled dari cek di atas.

                // --- Inisialisasi Peta Leaflet (Hanya setelah lokasi didapat) ---
                const map = L.map('map').setView([lat, lon], 18);
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);

                // Marker dan Circle Lokasi Pusat
                L.circle([LOKASI_PUSAT.lat, LOKASI_PUSAT.lon], {
                    radius: RADIUS_ABSENSI, 
                    color: 'blue',
                    fillColor: '#30a1ff',
                    fillOpacity: 0.3
                }).addTo(map);
                
                L.marker([LOKASI_PUSAT.lat, LOKASI_PUSAT.lon])
                    .addTo(map)
                    .bindPopup("SMPN 1 TARIK");

                // Marker Lokasi Pengguna
                L.marker([lat, lon]).addTo(map)
                    .bindPopup("Lokasi Anda Saat Ini")
                    .openPopup();
                
                // **Penting: Memastikan peta me-render ulang (Solusi masalah intermiten)**
                setTimeout(function() {
                    map.invalidateSize();
                }, 100); 

            }, function (error) {
                // Fungsi error Geolocation
                alert("Gagal mendapatkan lokasi: " + error.message + ". Presensi tidak bisa dilakukan.");
                $btnPresensi.prop("disabled", true); // Nonaktifkan presensi jika lokasi gagal
            });
        } else {
            alert("Geolocation tidak didukung oleh browser ini. Presensi tidak bisa dilakukan.");
            $btnPresensi.prop("disabled", true);
        }
    });

    // --- Fungsi Ambil Gambar (Capture) ---
    function ambilGambar() {
        const $video = $("#video");
        const videoElement = $video[0];
        const canvas = document.createElement("canvas");
        const $snapshot = $("#snapshot");
        const $btnAmbil = $("#btnAmbil");
        const $afterCaptureButtons = $("#afterCaptureButtons");

        // Set ukuran canvas sesuai video
        canvas.width = videoElement.videoWidth;
        canvas.height = videoElement.videoHeight;
        const context = canvas.getContext("2d");
        context.drawImage(videoElement, 0, 0, canvas.width, canvas.height);

        const dataUrl = canvas.toDataURL("image/png");
        $snapshot.attr("src", dataUrl);

        // Transisi dari video ke gambar dengan efek fade
        $video.addClass("fade-out");
        setTimeout(() => {
            $video.addClass("d-none");
            $snapshot.removeClass("d-none").addClass("fade-in");
            $btnAmbil.addClass("d-none");
            $afterCaptureButtons.removeClass("d-none");

            // Atur ulang teks tombol sesuai status (jika tombol tidak disabled karena radius)
            const $btnPresensi = $("#btnPresensi");
            const status = window.statusPresensi;
            if (!$btnPresensi.prop("disabled") || status === "selesai") {
                if (status === "masuk") {
                    $btnPresensi.text("Absensi Masuk");
                } else if (status === "keluar") {
                    $btnPresensi.text("Absensi Keluar");
                } else {
                    $btnPresensi.text("Sudah Absen Hari Ini").prop("disabled", true);
                }
            }

        }, 500);
    }


    // --- Fungsi Ambil Ulang (Retake) ---
    function ambilUlang() {
        const $video = $("#video");
        const $snapshot = $("#snapshot");
        const $btnAmbil = $("#btnAmbil");
        const $afterCaptureButtons = $("#afterCaptureButtons");

        $snapshot.removeClass("fade-in").addClass("fade-out");

        setTimeout(() => {
            $snapshot.addClass("d-none");
            $video.removeClass("d-none fade-out").addClass("fade-in");

            $btnAmbil.removeClass("d-none");
            $afterCaptureButtons.addClass("d-none");
        }, 500);
    }

    // --- Fungsi Presensi (Submit) ---
    function presensi() {
        const $snapshot = $("#snapshot");
        const imageData = $snapshot.attr("src");

        // Pastikan pengguna telah mengambil gambar
        if (!imageData || imageData.length < 100) {
             alert("Silakan ambil gambar (selfie) terlebih dahulu.");
             return;
        }

        const latitude = $("#latitude_input").val();
        const longitude = $("#longitude_input").val();
        const status = window.statusPresensi; // 'masuk' atau 'keluar'

        $.ajax({
            url: "/siswa/absensi", // Ganti dengan endpoint API Anda
            method: "POST",
            data: {
                image: imageData,
                latitude: latitude,
                longitude: longitude,
                status_presensi: status, // Kirim status ke backend
            },
            beforeSend: function() {
                // Tampilkan loading/disable tombol sebelum kirim
                $("#btnPresensi").prop("disabled", true).text("Memproses...");
            },
            success: function(response) {
                alert("Data presensi " + status + " berhasil dikirim!");
                
                // Update tampilan info absensi
                if (status === 'masuk' && response.jam_masuk) {
                     // Asumsi Anda punya elemen dengan ID yang benar untuk menampilkan jam masuk/keluar
                     // Jika tidak ada ID yang benar, gunakan penamaan yang sesuai
                     $('#jam_masuk').text(response.jam_masuk); 
                     $("#sudah_masuk").val('1'); // Update status
                     window.statusPresensi = 'keluar'; // Pindah ke status keluar
                     $("#btnPresensi").text("Absensi Keluar").prop("disabled", false);

                } else if (status === 'keluar' && response.jam_keluar) {
                    $('#jam_keluar').text(response.jam_keluar); 
                    $("#sudah_keluar").val('1'); // Update status
                    window.statusPresensi = 'selesai'; // Pindah ke status selesai
                    $("#btnPresensi").text("Sudah Absen Hari Ini").prop("disabled", true);
                } else {
                    // Handle jika backend sukses tapi tidak mengembalikan data yang diharapkan
                    alert("Presensi berhasil, tapi data update tidak ditemukan.");
                }

                ambilUlang(); // Kembali ke tampilan video setelah sukses

            },
            error: function(xhr, status, error) {
                alert("Gagal mengirim presensi: " + xhr.responseJSON.message || error);
                console.error(xhr.responseText);
                // Kembalikan tombol ke keadaan sebelum kirim agar bisa coba lagi
                $("#btnPresensi").prop("disabled", false).text(status === 'masuk' ? "Absensi Masuk" : "Absensi Keluar"); 
            }
        });
    }
</script>

</body>

</html>