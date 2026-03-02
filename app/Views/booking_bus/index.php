<?= view('shared/head') ?>

<style>
.bus-card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    transition: .2s;
    background: #fff;
}

.bus-card:hover {
    box-shadow: 0 8px 20px rgba(0,0,0,.08);
}

.bus-body {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
}

.bus-row-normal {
    width: 100%;
    max-width: 420px;
    display: flex;
    justify-content: space-between;
}

.bus-row {
    display: flex;
    justify-content: center;
    align-items: center;
    margin-bottom: 10px;
    gap: 20px;
}

.seat-side {
    display: flex;
    gap: 8px;
}

.aisle {
    width: 30px;
}

.back-row {
    justify-content: center;
    gap: 6px;
}

.seat {
    width: 48px;
    height: 40px;
    font-size: 13px;
    font-weight: 600;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.seat-empty {
    background: #d1fae5;
    border: 1px solid #10b981;
    color: #065f46;
    cursor: pointer;
}

.seat-empty:hover {
    background: #10b981;
    color: #fff;
}

.seat-booked {
    background: #fecaca;
    border: 1px solid #ef4444;
    color: #7f1d1d;
    cursor: not-allowed;
}

.seat-own {
    background: #bfdbfe;
    border: 1px solid #2563eb;
    color: #1e3a8a;
}

/* 🔒 LOCKED */
.seat-locked {
    background: #e5e7eb;
    border: 1px solid #9ca3af;
    color: #374151;
    cursor: not-allowed;
}

.seat-pair {
    display: flex;
    gap: 4px;
}

.bus-row-back {
    display: flex;
    gap: 4px;
    justify-content: center;
}

.driver-area {
    background: #f3f4f6;
    padding: 8px;
    text-align: center;
    font-weight: bold;
    border-radius: 6px;
    margin-bottom: 15px;
}


@media (max-width: 576px) {

    .bus-row-normal {
        max-width: 100%;
    }

    .seat {
        width: 36px;
        height: 34px;
        font-size: 11px;
    }

    .seat-pair {
        gap: 4px;
    }

    .bus-body {
        gap: 8px;
    }

    .driver-area {
        font-size: 12px;
        padding: 6px;
    }

}

</style>

<body>
<div class="wrapper">
<?= view('siswa/navbar'); ?>

<div class="content-wrapper p-4">
<div class="container-fluid">

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('success') ?>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<div class="card shadow">
<div class="card-header bg-primary text-white">
    <i class="fas fa-bus"></i> Booking Kursi Bus
</div>

<div class="card-body">
<div class="mb-3 text-center">
    <span class="badge" style="background:#c77dff">Zona Perempuan (1-24)</span>
    <span class="badge" style="background:#4dabf7">Zona Laki-laki (25-50)</span>
</div>
<?php if (!empty($sudahBooking)): ?>
    <div class="alert alert-info">
        Anda sudah memilih:
        <strong>
            <?= esc($sudahBooking['nama_bus']) ?> -
            Kursi <?= esc($sudahBooking['nomor_kursi']) ?>
        </strong>
    </div>
<?php endif; ?>

<div class="row">

<?php if (!empty($busList)): ?>
<?php foreach ($busList as $b): ?>

<div class="col-md-6 mb-4">
<div class="bus-card p-3">

<h5 class="mb-3">
    🚌 <?= esc($b['nama_bus']) ?>
    <span class="badge bg-success float-end">
        <?= $b['terisi_final'] ?>/<?= $b['kapasitas'] ?>
    </span>
</h5>

<div class="driver-area">
    👨‍✈️ SUPIR
</div>

<div class="bus-body">

<?php
$grouped = [];
foreach ($b['seats'] as $seat) {
    $grouped[$seat['baris']][] = $seat;
}
ksort($grouped);
?>

<?php foreach ($grouped as $baris => $seats): ?>

    <?php
    $left = [];
    $right = [];
    $back = [];

    foreach ($seats as $s) {
        if (in_array($s['posisi'], ['L1','L2'])) {
            $left[] = $s;
        } elseif (in_array($s['posisi'], ['R1','R2'])) {
            $right[] = $s;
        } else {
            $back[] = $s;
        }
    }
    ?>

    <?php if (!empty($back)): ?>
        <!-- BARIS BELAKANG -->
        <div class="bus-row-back">
            <?php foreach ($back as $seat): ?>
                <?= view('booking_bus/_seat', [
                    'seat' => $seat,
                    'sudahBooking' => $sudahBooking,
                    'isLocked' => !empty($seat['is_blocked'])
                ]) ?>
            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <!-- BARIS NORMAL -->
        <div class="bus-row-normal">
            <div class="seat-pair">
                <?php foreach ($left as $seat): ?>
                    <?= view('booking_bus/_seat', [
                        'seat' => $seat,
                        'sudahBooking' => $sudahBooking,
                        'isLocked' => !empty($seat['is_blocked'])
                    ]) ?>
                <?php endforeach; ?>
            </div>

            <div class="seat-pair">
                <?php foreach ($right as $seat): ?>
                    <?= view('booking_bus/_seat', [
                        'seat' => $seat,
                        'sudahBooking' => $sudahBooking,
                        'isLocked' => !empty($seat['is_blocked'])
                    ]) ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

<?php endforeach; ?>

</div>
</div>
</div>

<?php endforeach; ?>
<?php endif; ?>

</div>
</div>
</div>
</div>
</div>

<script>
function lihatBlokir(reason, nomor) {
    Swal.fire({
        icon: 'warning',
        title: 'Kursi Diblokir',
        html: `
            <div style="text-align:left">
                <b>Kursi ${nomor}</b><br><br>
                Keterangan:<br>
                <b>${reason}</b>
            </div>
        `,
        confirmButtonText: 'OK'
    });
}
function lihatBooking(nama, kelas, rombel, nomor) {
    Swal.fire({
        icon: 'info',
        title: 'Kursi Sudah Dibooking',
        html: `
            <div style="text-align:left">
                <b>Kursi ${nomor}</b><br><br>
                Dibooking oleh:<br>
                <b>${nama}</b><br>
                Kelas ${kelas} - ${rombel}
            </div>
        `,
        confirmButtonText: 'OK'
    });
}
function pilihKursi(nomorKursi, busId, seatId) {

    let jenisSiswa = "<?= $siswa->jenis ?>"; // L atau P
    let nomor = parseInt(nomorKursi);

    let zonaPerempuan = nomor >= 1 && nomor <= 24;
    let zonaLaki = nomor >= 25 && nomor <= 50;

    let perluPassword = false;
    let pesanZona = "";

    // 🔥 CEK ZONA
    if (zonaPerempuan && jenisSiswa === 'L') {
        perluPassword = true;
        pesanZona = "Kursi ini khusus siswa perempuan.";
    }

    if (zonaLaki && jenisSiswa === 'P') {
        perluPassword = true;
        pesanZona = "Kursi ini khusus siswa laki-laki.";
    }

    if (perluPassword) {

        Swal.fire({
            title: 'Zona Kursi Khusus',
            html: `
                <div style="text-align:left">
                    ${pesanZona}<br><br>
                    Masukkan password admin untuk melanjutkan:
                    <input type="password" id="adminPass" class="swal2-input" placeholder="Password Admin">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Lanjutkan',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                return document.getElementById('adminPass').value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                kirimBooking(busId, seatId, result.value);
            }
        });

    } else {
        kirimBooking(busId, seatId, null);
    }
}
function kirimBooking(busId, seatId, adminPass = null) {

    fetch('<?= base_url('siswa/booking/simpan') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
        },
        body: JSON.stringify({
            bus_id: busId,
            seat_id: seatId,
            admin_pass: adminPass
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            Swal.fire('Berhasil!', data.message, 'success')
                .then(() => location.reload());
        } else {
            Swal.fire('Gagal!', data.message, 'error');
        }
    });
}
</script>

</body>
</html>