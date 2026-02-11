<!DOCTYPE html>
<html>
<head>
    <title>Print Booking Kamar - Layout 2</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            border: 1px solid #333;
            padding: 6px 8px;
            vertical-align: top;
        }
        th {
            background: #f0f0f0;
            text-align: center;
        }
        ul { padding-left: 16px; margin: 0; }
    </style>
</head>
<body onload="window.print()">

<h3>
    LAPORAN BOOKING KAMAR <br>
    Jenjang: <?= esc($jenjang ?: 'Semua') ?> |
    Kelas: <?= esc($kelas ?: 'Semua') ?>
</h3>

<table>
    <thead>
        <tr>
            <th width="40">No</th>
            <th>Nama Siswa</th>
            <th width="100">Kelas</th>
            <th width="80">Kamar</th>
            <th>Terisi</th>
        </tr>
    </thead>
    <tbody>
        <?php $no = 1; foreach ($rows as $r): ?>
        <tr>
            <td align="center"><?= $no++ ?></td>
            <td><?= esc($r['nama_siswa']) ?></td>
            <td><?= esc($r['rombel']) ?></td>
            <td align="center"><?= esc($r['nomor_kamar']) ?></td>
            <td>
                <ul>
                    <?php foreach ($penghuniKamar[$r['kamar_id']] as $p): ?>
                        <li>
                            <?= esc($p['nama']) ?>
                            <span style="color:#555">
                                (<?= esc($p['rombel']) ?>)
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
