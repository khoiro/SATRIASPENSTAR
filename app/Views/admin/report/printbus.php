<!DOCTYPE html>
<html>
<head>
<title>Cetak Bus</title>

<style>
body{
    font-family: Arial, sans-serif;
    font-size: 12px;
    margin: 10px;
}

.container{
    display: flex;
}

.bus-area{
    width: 60%;
}

.student-list{
    width: 40%;
    padding-left: 20px;
}

/* ================= SEAT ================= */

.seat{
    width: 40px;
    height: 32px;
    border: 1.5px solid #10b981;
    margin: 4px;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:11px;
    font-weight:bold;
}

/* BOOKED */
.booked{
    background:#ef4444;
    color:white;
    border-color:#ef4444;
}

/* BLOCKED */
.locked{
    background:#6b7280;
    color:white;
    border-color:#6b7280;
}

.row-seat{
    display:flex;
    justify-content:center;
}

.supir{
    text-align:center;
    margin-bottom:10px;
    padding:6px;
    background:#eee;
    font-weight:bold;
    font-size:12px;
}

/* ================= TABEL ================= */

.passenger-table{
    width:100%;
    border-collapse: collapse;
    font-size:11px;
}

.passenger-table th{
    background:#f3f4f6;
    padding:4px;
    text-align:left;
    border:1px solid #ddd;
}

.passenger-table td{
    padding:3px 4px;
    border:1px solid #eee;
}

/* PRINT SETTING */
@media print {
    button { display:none; }
    body{
        margin:0;
    }
}
</style>
</head>

<body>

<button onclick="window.print()">Print</button>

<h3 style="margin:5px 0;">🚌 <?= esc($bus['nama_bus']) ?></h3>

<div class="container">

<!-- ================= DENAH BUS ================= -->
<div class="bus-area">

<div class="supir">SUPIR</div>

<?php
$grouped=[];
foreach($seats as $s){
    $grouped[$s['baris']][]=$s;
}
ksort($grouped);
?>

<?php foreach($grouped as $row): ?>
<div class="row-seat">
    <?php foreach($row as $seat): ?>

        <?php
            $class = '';

            if (!empty($seat['is_blocked'])) {
                $class = 'locked';
            } elseif (!empty($seat['nama'])) {
                $class = 'booked';
            }
        ?>

        <div class="seat <?= $class ?>">
            <?= $seat['nomor_kursi'] ?>
        </div>

    <?php endforeach; ?>
</div>
<?php endforeach; ?>

</div>

<!-- ================= DAFTAR PENUMPANG ================= -->
<div class="student-list">

<strong>Daftar Penumpang</strong>

<?php
// Urut berdasarkan nomor kursi
usort($seats, function($a,$b){
    return $a['nomor_kursi'] <=> $b['nomor_kursi'];
});
?>

<table class="passenger-table">
    <thead>
        <tr>
            <th style="width:25%;">No</th>
            <th>Nama / Keterangan</th>
        </tr>
    </thead>
    <tbody>

    <?php foreach($seats as $seat): ?>
        <?php if (!empty($seat['is_blocked']) || !empty($seat['nama'])): ?>
        <tr>
            <td><strong><?= $seat['nomor_kursi'] ?></strong></td>
            <td>
                <?php if (!empty($seat['is_blocked'])): ?>
                    <?= esc($seat['blocked_reason']) ?>
                <?php else: ?>
                    <?= esc($seat['nama']) ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endif; ?>
    <?php endforeach; ?>

    </tbody>
</table>

</div>

</div>

</body>
</html>