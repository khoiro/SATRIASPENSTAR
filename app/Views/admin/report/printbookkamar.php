<!DOCTYPE html>
<html>
<head>
    <title>Print Book Kamar</title>
    <style>
        body { font-family: Arial; font-size: 12px; }
        h3 { margin-bottom: 5px; }
        .kamar {
            border: 1px solid #000;
            margin-bottom: 15px;
            padding: 10px;
        }
        ul { padding-left: 18px; margin: 0; }
    </style>
</head>
<body onload="window.print()">

<h2>Report Book Kamar</h2>
<p>
    Jenjang: <strong><?= esc($jenjang ?: 'Semua') ?></strong><br>
    Kelas: <strong><?= esc($kelas ?: 'Semua') ?></strong>
</p>

<hr>

<h3>Rekap Siswa</h3>
<p><strong>Total Siswa: <?= $totalSiswa ?></strong></p>

<ul>
    <?php foreach ($rekapKelas as $kls => $jumlah): ?>
        <li><?= esc($kls) ?> : <?= $jumlah ?> siswa</li>
    <?php endforeach; ?>
</ul>

<hr>

<?php foreach ($dataKamar as $kamar): ?>
    <div class="kamar">
        <h3>
            <?= esc($kamar['nama_kamar']) ?>
            (<?= count($kamar['penghuni']) ?>/<?= $kamar['kapasitas'] ?>)
        </h3>

        <ul>
            <?php foreach ($kamar['penghuni'] as $p): ?>
                <li>
                    <?= esc($p['nama_siswa']) ?> 
                    (<?= esc($p['rombel']) ?>) 
                    (<?= esc($p['telp_siswa']) ?>)
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endforeach; ?>

</body>
</html>