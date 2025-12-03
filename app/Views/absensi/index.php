<!DOCTYPE html>
<html lang="en">
<?= view('shared/head') ?>

<body>
  <div class="wrapper">
    <?=view('user/navbar');?>
    <h2>Presensi <?= $user['nama'] ?></h2>

    <div style="display:flex;">
        <div>
            <img src="<?= $user['foto'] ?>" alt="Foto" width="200">
            <button>Ambil Gambar</button>
        </div>

        <div style="margin-left:20px;">
            <select>
                <option>Alasan di luar lokasi kantor</option>
                <option>WFH</option>
                <option>Dinas Luar</option>
            </select>

            <div style="background: linear-gradient(to right, #00f, #80f); color:white; padding:10px;">
                <h1><?= $presensi['tanggal'] ?></h1>
                <p>Masuk: <?= $presensi['masuk'] ?></p>
                <p>Keluar: <?= $presensi['keluar'] ?></p>
                <a href="#">Lihat Riwayat</a>
            </div>
        </div>

        <div id="map" style="width: 400px; height: 300px; margin-left:20px;"></div>
    </div>

    <script>
        const map = L.map('map').setView([<?= $user['lokasi']['lat'] ?>, <?= $user['lokasi']['lng'] ?>], 18);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        L.marker([<?= $user['lokasi']['lat'] ?>, <?= $user['lokasi']['lng'] ?>]).addTo(map);
    </script>
</body>
</html>
